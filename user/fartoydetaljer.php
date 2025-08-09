<?php
require_once __DIR__ . '/../includes/bootstrap.php'; // gir $conn (mysqli)

// -- Midlertidig debug (kan kommenteres ut når alt er grønt)
ini_set('display_errors', '1');
error_reporting(E_ALL);

// Fallback helpers (om ikke definert via includes)
if (!function_exists('is_valid_id')) {
    function is_valid_id($v) { return is_numeric($v) && (int)$v > 0; }
}
if (!function_exists('h')) {
    function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!is_valid_id($id)) {
    http_response_code(400);
    die('Ugyldig id');
}

/** 1) Hent hovedobjekt + type + verftnavn (leverandør/skrog) + siste tidslinje (nå-status) + nasjon + gjeldende navn */
$sqlMain = "
WITH latest_tid AS (
  SELECT t.*,
         ROW_NUMBER() OVER (
           PARTITION BY t.FartObj_ID
           ORDER BY t.YearTid DESC, t.MndTid DESC, t.FartTid_ID DESC
         ) rn
  FROM tblFartTid t
)
SELECT 
  o.*,
  ft.TypeFork, ft.Type AS FartType,
  lev.VerftNavn  AS LeverandorNavn,
  sk.VerftNavn   AS SkrogNavn,
  lt.YearTid, lt.MndTid, lt.Rederi, lt.Nasjon_ID, lt.RegHavn, lt.Kallesignal, lt.MMSI, lt.FartNavn_ID AS LtFartNavn_ID,
  n.Nasjon,
  fn.FartNavn AS LtFartNavn
FROM tblFartObj o
LEFT JOIN tblzFartType ft ON ft.FartType_ID = o.FartType_ID
LEFT JOIN tblVerft lev    ON lev.Verft_ID   = o.LeverID
LEFT JOIN tblVerft sk     ON sk.Verft_ID    = o.SkrogID
LEFT JOIN latest_tid lt   ON lt.FartObj_ID  = o.FartObj_ID AND lt.rn = 1
LEFT JOIN tblzNasjon n    ON n.Nasjon_ID    = lt.Nasjon_ID
LEFT JOIN tblFartNavn fn  ON fn.FartNavn_ID = lt.FartNavn_ID
WHERE o.FartObj_ID = ?
";
$stmt = $conn->prepare($sqlMain);
if (!$stmt) { die('Prepare (main) feilet: ' . $conn->error); }
$stmt->bind_param('i', $id);
if (!$stmt->execute()) { die('Execute (main) feilet: ' . $stmt->error); }
$mainRes = $stmt->get_result();
$obj = $mainRes ? $mainRes->fetch_assoc() : null;
$stmt->close();

if (!$obj) {
    http_response_code(404);
    die('Fartøy ikke funnet');
}

// Fallback navn hvis vi ikke fant gjeldende fra lt.FartNavn_ID
$currentName = $obj['LtFartNavn'] ?? null;
if ($currentName === null || $currentName === '') {
    $stmt = $conn->prepare("
        SELECT FartNavn 
        FROM tblFartNavn 
        WHERE FartObj_ID=? 
        ORDER BY FartNavn_ID DESC 
        LIMIT 1
    ");
    if ($stmt) {
        $stmt->bind_param('i', $id);
        if ($stmt->execute()) {
            $r = $stmt->get_result()->fetch_assoc();
            if ($r) $currentName = $r['FartNavn'];
        }
        $stmt->close();
    }
}
if (!$currentName) $currentName = $obj['NavnObj']; // siste fallback

/** 2) Siste tekniske spes */
$sqlSpes = "
SELECT
  s.*,
  v.VerftNavn AS VerftSpesNavn,
  sk.TypeSkrog,
  rg.RiggFork, rg.RiggDetalj,
  mt.MotorFork, mt.MotorDetalj AS MotorTypeDetalj
FROM tblFartSpes s
LEFT JOIN tblVerft       v  ON v.Verft_ID       = s.Verft_ID
LEFT JOIN tblzFartSkrog  sk ON sk.FartSkrog_ID  = s.FartSkrog_ID
LEFT JOIN tblzFartRigg   rg ON rg.FartRigg_ID   = s.FartRigg_ID
LEFT JOIN tblzFartMotor  mt ON mt.FartMotor_ID  = s.FartMotor_ID
WHERE s.FartObj_ID = ?
ORDER BY s.YearSpes DESC, s.MndSpes DESC, s.FartSpes_ID DESC
LIMIT 1
";
$stmt = $conn->prepare($sqlSpes);
$spes = null;
if ($stmt) {
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        $spes = $stmt->get_result()->fetch_assoc() ?: null;
    }
    $stmt->close();
}

/** 3) Navnehistorikk (nyeste først) */
$sqlNavn = "
SELECT FartNavn_ID, FartNavn, TidlNavn, PennantTiln
FROM tblFartNavn
WHERE FartObj_ID = ?
ORDER BY FartNavn_ID DESC
";
$stmt = $conn->prepare($sqlNavn);
$navnRows = [];
if ($stmt) {
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        $navnRows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    $stmt->close();
}

/** 4) Historikk (seneste 50) */
$sqlTid = "
SELECT t.*, n.Nasjon
FROM tblFartTid t
LEFT JOIN tblzNasjon n ON n.Nasjon_ID = t.Nasjon_ID
WHERE t.FartObj_ID = ?
ORDER BY t.YearTid DESC, t.MndTid DESC, t.FartTid_ID DESC
LIMIT 50
";
$stmt = $conn->prepare($sqlTid);
$tidRows = [];
if ($stmt) {
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        $tidRows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    $stmt->close();
}

/** 5) Lenker */
$sqlLink = "
SELECT FartLk_ID, LinkType, LinkInnh, Link
FROM tblxFartLink
WHERE FartID = ?
ORDER BY SerNo
";
$stmt = $conn->prepare($sqlLink);
$linkRows = [];
if ($stmt) {
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        $linkRows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    $stmt->close();
}

// ---- RENDER ----
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/menu.php'; ?>

<style>
  .wrap { padding: 12px 16px; }
  .grid { display: grid; grid-template-columns: 1.2fr 1fr; gap: 14px; }
  .card { border: 1px solid #ddd; border-radius: 10px; padding: 12px; background: #fff; }
  .muted { color:#666; }
  table.meta { border-collapse: collapse; width: 100%; }
  table.meta th { text-align:left; width: 30%; vertical-align: top; padding: 6px 8px; }
  table.meta td { padding: 6px 8px; }
  table.list { border-collapse: collapse; width: 100%; }
  table.list th, table.list td { border-bottom:1px solid #eee; padding: 6px 8px; text-align:left; }
  h1, h2, h3 { margin: 6px 0; }
  .pill { display:inline-block; font-size: 12px; padding: 2px 8px; border:1px solid #ddd; border-radius: 999px; margin-right:6px; }
</style>

<div class="wrap">
  <h1 style="margin-bottom:4px;">
    <?php if (!empty($obj['TypeFork'])): ?>
      <span><?php echo h($obj['TypeFork']); ?></span>
    <?php endif; ?>
    <?php echo ' ' . h($currentName ?: '(uten navn)'); ?>
  </h1>

  <div class="muted" style="margin:6px 0 12px 0;">
    <?php if (!empty($obj['RegHavn'])): ?>
      <span class="pill">Reg.havn: <?php echo h($obj['RegHavn']); ?></span>
    <?php endif; ?>
    <?php if (!empty($obj['Kallesignal'])): ?>
      <span class="pill">Kallesignal: <?php echo h($obj['Kallesignal']); ?></span>
    <?php endif; ?>
    <span class="pill">ID: <?php echo (int)$obj['FartObj_ID']; ?></span>
  </div>

  <div class="grid">
    <!-- VENSTRE: Grunndata + Nå-status -->
    <div class="card">
      <h2>Grunndata</h2>
      <table class="meta">
        <?php if (!empty($obj['IMO'])): ?>
        <tr><th>IMO</th><td><?php echo h($obj['IMO']); ?></td></tr>
        <?php endif; ?>
        <?php if (!empty($obj['Kontrahert'])): ?>
        <tr><th>Kontrahert</th><td><?php echo h($obj['Kontrahert']); ?></td></tr>
        <?php endif; ?>
        <?php if (!empty($obj['Kjolstrukket'])): ?>
        <tr><th>Kjølstrukket</th><td><?php echo h($obj['Kjolstrukket']); ?></td></tr>
        <?php endif; ?>
        <?php if (!empty($obj['Sjosatt'])): ?>
        <tr><th>Sjøsatt</th><td><?php echo h($obj['Sjosatt']); ?></td></tr>
        <?php endif; ?>
        <?php if (!empty($obj['Levert'])): ?>
        <tr><th>Levert</th><td><?php echo h($obj['Levert']); ?></td></tr>
        <?php endif; ?>
        <?php if (!empty($obj['Bygget'])): ?>
        <tr><th>Bygget</th><td><?php echo h($obj['Bygget']); ?></td></tr>
        <?php endif; ?>
        <?php if (!empty($obj['LeverandorNavn'])): ?>
        <tr><th>Leverandør (verft)</th><td><?php echo h($obj['LeverandorNavn']); ?></td></tr>
        <?php endif; ?>
        <?php if (!empty($obj['SkrogNavn'])): ?>
        <tr><th>Skrog (verft)</th><td><?php echo h($obj['SkrogNavn']); ?></td></tr>
        <?php endif; ?>
        <?php if (!empty($obj['BnrSkrog'])): ?>
        <tr><th>Byggenr (skrog)</th><td><?php echo h($obj['BnrSkrog']); ?></td></tr>
        <?php endif; ?>
        <?php if (!empty($obj['StroketYear'])): ?>
        <tr><th>Strøket år</th><td><?php echo h($obj['StroketYear']); ?></td></tr>
        <?php endif; ?>
        <?php if (!empty($obj['ObjNotater'])): ?>
        <tr><th>Notater</th><td><?php echo nl2br(h($obj['ObjNotater'])); ?></td></tr>
        <?php endif; ?>
      </table>

      <h3 style="margin-top:16px;">Nå‑status</h3>
      <?php if (!empty($obj['YearTid']) || !empty($obj['Rederi']) || !empty($obj['Nasjon']) || !empty($obj['MMSI'])): ?>
      <table class="meta">
        <?php if (!empty($obj['YearTid'])): ?>
        <tr><th>År/Mnd</th><td><?php echo h($obj['YearTid']); ?><?php if (!empty($obj['MndTid'])) echo '/' . h($obj['MndTid']); ?></td></tr>
        <?php endif; ?>
        <?php if (!empty($obj['Nasjon'])): ?>
        <tr><th>Nasjon</th><td><?php echo h($obj['Nasjon']); ?></td></tr>
        <?php endif; ?>
        <?php if (!empty($obj['Rederi'])): ?>
        <tr><th>Rederi</th><td><?php echo h($obj['Rederi']); ?></td></tr>
        <?php endif; ?>
        <?php if (!empty($obj['MMSI'])): ?>
        <tr><th>MMSI</th><td><?php echo h($obj['MMSI']); ?></td></tr>
        <?php endif; ?>
      </table>
    <?php else: ?>
      <div class="muted">Ingen status registrert i historikk.</div>
    <?php endif; ?>
    </div>

    <!-- HØYRE: Siste tekniske spes -->
    <div class="card">
      <h2>Byggedata</h2>
      <?php if ($spes): ?>
      <table class="meta">
        <tr><th>År/Mnd</th><td><?php echo h($spes['YearSpes'] ?? ''); ?><?php if (!empty($spes['MndSpes'])) echo '/' . h($spes['MndSpes']); ?></td></tr>
        <?php if (!empty($spes['VerftSpesNavn'])): ?>
        <tr><th>Verft</th><td><?php echo h($spes['VerftSpesNavn']); ?></td></tr>
        <?php endif; ?>
        <?php if (!empty($spes['Byggenr'])): ?>
        <tr><th>Byggenr</th><td><?php echo h($spes['Byggenr']); ?></td></tr>
        <?php endif; ?>
        <?php if (!empty($spes['Materiale'])): ?>
        <tr><th>Materiale</th><td><?php echo h($spes['Materiale']); ?></td></tr>
        <?php endif; ?>
        <?php if (!empty($spes['MotorDetalj']) || !empty($spes['MotorEff'])): ?>
        <tr><th>Motor</th><td><?php echo h($spes['MotorDetalj'] ?? ''); ?> <?php echo h($spes['MotorEff'] ?? ''); ?></td></tr>
        <?php endif; ?>
        <?php if (!empty($spes['Lengde']) || !empty($spes['Bredde']) || !empty($spes['Dypg'])): ?>
        <tr><th>Dimensjoner</th><td><?php echo h($spes['Lengde'] ?? ''); ?> × <?php echo h($spes['Bredde'] ?? ''); ?> × <?php echo h($spes['Dypg'] ?? ''); ?></td></tr>
        <?php endif; ?>
        <?php if (!empty($spes['Tonnasje']) || !empty($spes['Drektigh'])): ?>
        <tr><th>Tonnasje</th><td><?php echo h($spes['Tonnasje'] ?? ''); ?> <?php echo h($spes['Drektigh'] ?? ''); ?></td></tr>
        <?php endif; ?>
        <?php if (!empty($spes['Fartklasse'])): ?>
        <tr><th>Klasse</th><td><?php echo h($spes['Fartklasse']); ?></td></tr>
        <?php endif; ?>
        <?php if (!empty($spes['FunkDetalj'])): ?>
        <tr><th>Funksjon</th><td><?php echo h($spes['FunkDetalj']); ?></td></tr>
        <?php endif; ?>
        <?php if (!empty($spes['TeknDetalj'])): ?>
        <tr><th>Teknisk</th><td><?php echo h($spes['TeknDetalj']); ?></td></tr>
        <?php endif; ?>
      </table>
      <?php else: ?>
        <div class="muted">Ingen spesifikasjon registrert.</div>
      <?php endif; ?>
    </div>
  </div>

  <div class="card">
    <h2>Klasse / Rigg / Motor</h2>
    <?php if ($spes): ?>
      <table class="meta">
        <?php if (!empty($spes['Fartklasse'])): ?>
        <tr><th>Klasse (tekst)</th><td><?php echo h($spes['Fartklasse']); ?></td></tr>
        <?php endif; ?>
        <?php if (!empty($spes['FartKlasse_ID'])): ?>
        <tr><th>Klasse (ID)</th><td><?php echo (int)$spes['FartKlasse_ID']; ?></td></tr>
        <?php endif; ?>

        <?php if (!empty($spes['TypeSkrog'])): ?>
        <tr><th>Skrogtype</th><td><?php echo h($spes['TypeSkrog']); ?></td></tr>
        <?php elseif (!empty($spes['FartSkrog_ID'])): ?>
        <tr><th>Skrogtype (ID)</th><td><?php echo (int)$spes['FartSkrog_ID']; ?></td></tr>
        <?php endif; ?>

        <?php if (!empty($spes['RiggDetalj']) || !empty($spes['RiggFork'])): ?>
        <tr><th>Rigg</th><td>
          <?php echo h($spes['RiggFork'] ?? ''); ?>
          <?php if (!empty($spes['RiggDetalj'])) echo ' — ' . h($spes['RiggDetalj']); ?>
        </td></tr>
        <?php elseif (!empty($spes['FartRigg_ID'])): ?>
        <tr><th>Rigg (ID)</th><td><?php echo (int)$spes['FartRigg_ID']; ?></td></tr>
        <?php endif; ?>

        <?php if (!empty($spes['MotorTypeDetalj']) || !empty($spes['MotorFork'])): ?>
        <tr><th>Motor (type)</th><td>
          <?php echo h($spes['MotorFork'] ?? ''); ?>
          <?php if (!empty($spes['MotorTypeDetalj'])) echo ' — ' . h($spes['MotorTypeDetalj']); ?>
        </td></tr>
        <?php elseif (!empty($spes['FartMotor_ID'])): ?>
        <tr><th>Motor (ID)</th><td><?php echo (int)$spes['FartMotor_ID']; ?></td></tr>
        <?php endif; ?>

        <?php if (!empty($spes['MotorDetalj']) || !empty($spes['MotorEff'])): ?>
        <tr><th>Motor (detalj)</th><td>
          <?php echo h($spes['MotorDetalj'] ?? ''); ?>
          <?php if (!empty($spes['MotorEff'])) echo ' — ' . h($spes['MotorEff']); ?>
        </td></tr>
        <?php endif; ?>
      </table>
    <?php else: ?>
      <div class="muted">Ingen spesifikasjon registrert.</div>
    <?php endif; ?>
  </div>

  <div class="grid" style="margin-top:14px;">
    <!-- VENSTRE: Navnehistorikk -->
    <div class="card">
      <h2>Navnehistorikk</h2>
      <?php if ($navnRows): ?>
        <table class="list">
          <tr><th>Navn</th><th>Tidligere navn</th><th>Pennant</th></tr>
          <?php foreach ($navnRows as $n): ?>
          <tr>
            <td><?php echo h($n['FartNavn'] ?? ''); ?></td>
            <td><?php echo h($n['TidlNavn'] ?? ''); ?></td>
            <td><?php echo h($n['PennantTiln'] ?? ''); ?></td>
          </tr>
          <?php endforeach; ?>
        </table>
      <?php else: ?>
        <div class="muted">Ingen navneoppføringer.</div>
      <?php endif; ?>
    </div>

    <!-- HØYRE: Eksterne lenker -->
    <div class="card">
      <h2>Lenker</h2>
      <?php if ($linkRows): ?>
        <ul>
          <?php foreach ($linkRows as $lk): ?>
          <li>
            <?php echo h($lk['LinkType'] ?? ''); ?> — 
            <?php echo h($lk['LinkInnh'] ?? ''); ?>
            <?php if (!empty($lk['Link'])): ?>
              : <a href="<?php echo h($lk['Link']); ?>" target="_blank" rel="noopener">Åpne</a>
            <?php endif; ?>
          </li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <div class="muted">Ingen lenker registrert.</div>
      <?php endif; ?>
    </div>
  </div>

  <div class="card" style="margin-top:14px;">
    <h2>Historikk (seneste 50)</h2>
    <?php if ($tidRows): ?>
      <table class="list">
        <tr>
          <th>År</th><th>Mnd</th><th>Nasjon</th><th>Rederi</th><th>Reg.havn</th><th>Kallesignal</th><th>Hendelse</th>
        </tr>
        <?php foreach ($tidRows as $t): ?>
        <tr>
          <td><?php echo h($t['YearTid'] ?? ''); ?></td>
          <td><?php echo h($t['MndTid'] ?? ''); ?></td>
          <td><?php echo h($t['Nasjon'] ?? ''); ?></td>
          <td><?php echo h($t['Rederi'] ?? ''); ?></td>
          <td><?php echo h($t['RegHavn'] ?? ''); ?></td>
          <td><?php echo h($t['Kallesignal'] ?? ''); ?></td>
          <td><?php echo h($t['Hendelse'] ?? ''); ?></td>
        </tr>
        <?php endforeach; ?>
      </table>
    <?php else: ?>
      <div class="muted">Ingen historikk funnet.</div>
    <?php endif; ?>
  </div>

  <p style="margin-top:10px;"><a href="javascript:history.back()">← Tilbake</a></p>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
