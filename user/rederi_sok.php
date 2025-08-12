<?php
/**
 * user/rederi_sok.php
 * - Fritekstsøk mot rederi (tblFartTid.Rederi, fritekst)
 * - Valg av ett rederi (eksakt) snevrer inn listen
 * - Sortering (navn/byggeår, asc/desc), default: navn ASC
 * - Antall per side (20/50/100)
 * - Klikk på fartøy -> user/fartoydetaljer.php?id=<FartNavn_ID>
 * - CSV-eksport via ?export=csv (eksporterer alle treff, ignorerer paging)
 */
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/auth.php'; // ingen require_admin()

$q       = trim($_GET['q'] ?? '');
$sel     = trim($_GET['rederi'] ?? ''); // valgt rederi (eksakt match)

// sortering (default navn ASC)
$sort     = strtolower(trim($_GET['sort'] ?? 'navn'));
$dir      = strtolower(trim($_GET['dir'] ?? 'asc'));
$sortMap  = ['navn' => 'fn.FartNavn', 'bygget' => 'fo.Bygget'];
$col      = $sortMap[$sort] ?? $sortMap['navn'];
$direction= ($dir === 'desc') ? 'DESC' : 'ASC';
$orderBy  = $col . ' ' . $direction . ', fn.FartNavn ASC';

// per page
$ppAllowed = [20,50,100];
$perPage   = (int)($_GET['pp'] ?? 20);
if (!in_array($perPage, $ppAllowed, true)) $perPage = 20;

$page   = max(1, (int)($_GET['p'] ?? 1));
$offset = ($page - 1) * $perPage;

$rederiList = [];
$total = 0;
$rows  = [];

/* 1) Foreslå rederinavn (distinct) ved fritekst */
if ($q !== '') {
    $stmt = $conn->prepare("
        SELECT DISTINCT TRIM(Rederi) AS Rederi
        FROM tblFartTid
        WHERE Rederi IS NOT NULL AND Rederi <> '' AND Rederi LIKE ?
        ORDER BY Rederi
        LIMIT 200
    ");
    $like = '%'.$q.'%';
    $stmt->bind_param('s', $like);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) { $rederiList[] = $r['Rederi']; }
    $stmt->close();
}

/* 2) Filter-subquery for FartObj_ID basert på valgt/tekst */
$filterSQL  = '';
$filterType = '';
$filterVal  = null;

if ($sel !== '') {
    $filterSQL  = "SELECT DISTINCT FartObj_ID FROM tblFartTid WHERE Rederi = ?";
    $filterType = 's';
    $filterVal  = $sel;
} elseif ($q !== '') {
    $filterSQL  = "SELECT DISTINCT FartObj_ID FROM tblFartTid WHERE Rederi LIKE ?";
    $filterType = 's';
    $filterVal  = '%'.$q.'%';
}

/* 3) CSV-eksport (tidlig exit, respekterer sortering) */
if (isset($_GET['export']) && $_GET['export'] === 'csv' && $filterSQL !== '') {
    $exportSql = "
        SELECT fo.FartObj_ID,
               fn.FartNavn,
               zt.TypeFork,
               fo.Bygget,
               lft.RegHavn,
               zn.Nasjon,
               lft.Kallesignal,
               lft.Rederi,
               fs.FartSpes_ID,
               fn.FartNavn_ID
        FROM tblFartObj fo
        JOIN ($filterSQL) f ON f.FartObj_ID = fo.FartObj_ID
        LEFT JOIN tblFartSpes fs ON fs.FartObj_ID = fo.FartObj_ID
        LEFT JOIN tblFartNavn fn ON fn.FartObj_ID = fo.FartObj_ID
        LEFT JOIN tblzFartType zt ON zt.FartType_ID = fn.FartType_ID
        LEFT JOIN (
            SELECT ft.*
            FROM tblFartTid ft
            JOIN (
               SELECT FartObj_ID, MAX(FartTid_ID) AS maxid
               FROM tblFartTid GROUP BY FartObj_ID
            ) m ON m.FartObj_ID = ft.FartObj_ID AND m.maxid = ft.FartTid_ID
        ) lft ON lft.FartObj_ID = fo.FartObj_ID
        LEFT JOIN tblzNasjon zn ON zn.Nasjon_ID = lft.Nasjon_ID
        ORDER BY $orderBy
    ";
    $stmt = $conn->prepare($exportSql);
    $stmt->bind_param($filterType, $filterVal);
    $stmt->execute();
    $res = $stmt->get_result();

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="rederi_sok.csv"');

    $out = fopen('php://output', 'w');
    fputcsv($out, ['FartObj_ID','FartNavn','Type','Bygget','RegHavn','Nasjon','Kallesignal','Rederi','FartSpes_ID','FartNavn_ID']);
    while ($r = $res->fetch_assoc()) {
        fputcsv($out, [
            $r['FartObj_ID'], $r['FartNavn'], $r['TypeFork'], $r['Bygget'],
            $r['RegHavn'], $r['Nasjon'], $r['Kallesignal'], $r['Rederi'],
            $r['FartSpes_ID'], $r['FartNavn_ID']
        ]);
    }
    fclose($out);
    exit;
}

/* 4) Tell og hent rader når vi har et filter */
if ($filterSQL !== '') {
    // COUNT
    $countSql = "
        SELECT COUNT(*) AS c FROM (
            SELECT DISTINCT fo.FartObj_ID
            FROM tblFartObj fo
            JOIN ($filterSQL) f ON f.FartObj_ID = fo.FartObj_ID
        ) x";
    $stmt = $conn->prepare($countSql);
    $stmt->bind_param($filterType, $filterVal);
    $stmt->execute();
    $total = (int)($stmt->get_result()->fetch_assoc()['c'] ?? 0);
    $stmt->close();

    // ROWS
    $rowsSql = "
        SELECT fo.FartObj_ID,
               fn.FartNavn,
               zt.TypeFork,
               fo.Bygget,
               lft.RegHavn,
               zn.Nasjon,
               lft.Kallesignal,
               lft.Rederi,
               fs.FartSpes_ID,
               fn.FartNavn_ID
        FROM tblFartObj fo
        JOIN ($filterSQL) f ON f.FartObj_ID = fo.FartObj_ID
        LEFT JOIN tblFartSpes fs ON fs.FartObj_ID = fo.FartObj_ID
        LEFT JOIN tblFartNavn fn ON fn.FartObj_ID = fo.FartObj_ID
        LEFT JOIN tblzFartType zt ON zt.FartType_ID = fn.FartType_ID
        LEFT JOIN (
            SELECT ft.*
            FROM tblFartTid ft
            JOIN (
               SELECT FartObj_ID, MAX(FartTid_ID) AS maxid
               FROM tblFartTid
               GROUP BY FartObj_ID
            ) m ON m.FartObj_ID = ft.FartObj_ID AND m.maxid = ft.FartTid_ID
        ) lft ON lft.FartObj_ID = fo.FartObj_ID
        LEFT JOIN tblzNasjon zn ON zn.Nasjon_ID = lft.Nasjon_ID
        ORDER BY $orderBy
        LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($rowsSql);
    $stmt->bind_param($filterType.'ii', $filterVal, $perPage, $offset);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) { $rows[] = $r; }
    $stmt->close();
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/menu.php';
?>
<div class="container mt-4">
  <div class="card">
    <form method="get" class="grid cols-5" style="gap:1rem;">
      <div>
        <label for="q">Fritekstsøk (rederinavn)</label>
        <input id="q" class="input" type="text" name="q" value="<?= e($q) ?>" placeholder="F.eks. Hurtigruten" aria-label="Fritekstsøk etter rederi">
      </div>
      <div>
        <label for="rederi">Begrens til rederi</label>
        <select id="rederi" name="rederi" class="input" aria-label="Velg et spesifikt rederi">
          <option value="">— Alle treff —</option>
          <?php foreach ($rederiList as $name): ?>
            <option value="<?= e($name) ?>" <?= $sel===$name?'selected':''; ?>><?= e($name) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label for="sort">Sorter etter</label>
        <select id="sort" name="sort" class="input" aria-label="Sorter etter">
          <option value="navn"   <?= $sort==='navn'?'selected':''; ?>>Navn</option>
          <option value="bygget" <?= $sort==='bygget'?'selected':''; ?>>Byggeår</option>
        </select>
      </div>
      <div>
        <label for="dir">Rekkefølge</label>
        <select id="dir" name="dir" class="input" aria-label="Sorteringsrekkefølge">
          <option value="asc"  <?= $dir==='asc'?'selected':'';  ?>>Stigende</option>
          <option value="desc" <?= $dir==='desc'?'selected':''; ?>>Synkende</option>
        </select>
      </div>
      <div>
        <label for="pp">Antall per side</label>
        <select id="pp" name="pp" class="input" aria-label="Antall resultater per side">
          <?php foreach ([20,50,100] as $pp): ?>
            <option value="<?= $pp ?>" <?= $perPage===$pp?'selected':''; ?>><?= $pp ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="self-end" style="grid-column: 1 / -1;">
        <button class="btn primary" type="submit">Søk</button>
        <a class="btn" href="<?= url('user/rederi_sok.php') ?>">Nullstill</a>
      </div>
    </form>
  </div>

  <?php
  /* MINI-HEADER: valgt rederi, ellers «chips» */
  if ($sel !== '') {
      $stmt = $conn->prepare("SELECT COUNT(*) AS Perioder FROM tblFartTid WHERE Rederi = ?");
      $stmt->bind_param('s', $sel);
      $stmt->execute();
      $perioder = (int)($stmt->get_result()->fetch_assoc()['Perioder'] ?? 0);
      $stmt->close();

      $clearQs = http_build_query(array_filter([
        'q' => $q !== '' ? $q : null,
        'sort' => $sort !== 'navn' ? $sort : null,
        'dir'  => $dir  !== 'asc'  ? $dir  : null,
        'pp'   => $perPage!==20 ? $perPage : null
      ]));
      $clearHref = url('user/rederi_sok.php') . ($clearQs ? ('?' . $clearQs) : '');
      ?>
      <div class="card mt-3" style="border-left:4px solid #2c3e50;">
        <div style="display:flex;justify-content:space-between;gap:1rem;align-items:center;">
          <div>
            <div style="font-size:1.05rem;font-weight:600;"><?= e($sel) ?></div>
            <div class="muted">
              Fartøy (distinkte): <?= (int)$total ?> • Perioder i historikk: <?= (int)$perioder ?>
            </div>
          </div>
          <div><a class="btn" href="<?= $clearHref ?>">Fjern filter</a></div>
        </div>
      </div>
      <?php
  } elseif (!empty($rederiList)) {
      $chips = array_slice($rederiList, 0, 12);
      ?>
      <div class="card mt-3">
        <div class="muted" style="margin-bottom:.5rem;">Treff på rederi:</div>
        <div style="display:flex;flex-wrap:wrap;gap:.5rem;">
          <?php foreach ($chips as $name):
              $qs = http_build_query(array_filter([
                  'q' => $q !== '' ? $q : null,
                  'rederi' => $name,
                  'sort' => $sort !== 'navn' ? $sort : null,
                  'dir'  => $dir  !== 'asc'  ? $dir  : null,
                  'pp'   => $perPage!==20 ? $perPage : null
              ]));
              $href = '?' . $qs;
          ?>
            <a class="btn" href="<?= $href ?>" style="padding:.25rem .6rem;border-radius:999px;"><?= e($name) ?></a>
          <?php endforeach; ?>
        </div>
      </div>
      <?php
  }
  ?>

  <?php if ($filterSQL === '' && ($q !== '' || $sel !== '')): ?>
    <div class="card mt-3" role="status" aria-live="polite">
      <strong>Ingen treff.</strong>
      <div class="muted">Tips: skriv deler av navnet (f.eks. “Hurtig” for Hurtigruten).</div>
    </div>
  <?php endif; ?>

  <?php if ($filterSQL !== ''): ?>
    <div class="card mt-3" role="status" aria-live="polite">
      <strong><?= $total ?></strong> fartøy funnet
      <?php if ($sel!==''): ?> for valgt rederi «<?= e($sel) ?>»
      <?php elseif ($q!==''): ?> for søk «<?= e($q) ?>»<?php endif; ?>.
      <div class="mt-2">
        <?php
          $qsExport = http_build_query(array_filter([
            'q' => $q!=='' ? $q : null,
            'rederi' => $sel !== '' ? $sel : null,
            'sort' => $sort !== 'navn' ? $sort : null,
            'dir'  => $dir  !== 'asc'  ? $dir  : null,
            'pp'   => $perPage!==20 ? $perPage : null,
            'export' => 'csv'
          ]));
        ?>
        <a class="btn" href="?<?= $qsExport ?>" aria-label="Eksporter resultatlisten til CSV">Eksporter CSV</a>
      </div>
    </div>

    <?php foreach ($rows as $r): ?>
      <div class="card mt-2">
        <div><small><?= e($r['TypeFork'] ?? '') ?></small></div>
        <div style="font-weight:600; font-size:1.05rem;">
          <a href="<?= url('user/fartoydetaljer.php') ?>?id=<?= (int)$r['FartNavn_ID'] ?>">
            <?= e($r['FartNavn'] ?? '(uten navn)') ?>
          </a>
        </div>
        <div class="muted">
          <?= e($r['RegHavn'] ?? '') ?> <?= !empty($r['Nasjon'])? '• '.e($r['Nasjon']) : '' ?>
          <?= !empty($r['Bygget'])? ' • Bygget: '.e($r['Bygget']) : '' ?>
          <?= !empty($r['Kallesignal'])? ' • C/S: '.e($r['Kallesignal']) : '' ?>
          <?= !empty($r['Rederi'])? ' • Rederi: '.e($r['Rederi']) : '' ?>
          <?php if (!empty($r['FartSpes_ID'])): ?>
            • <a href="<?= url('user/fartoy_spes.php') ?>?spes_id=<?= (int)$r['FartSpes_ID'] ?>">Tekniske data</a>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>

    <?php if ($total > $perPage): ?>
      <div class="mt-3" aria-label="Paginering">
        <?php
          $baseQs = http_build_query(array_filter([
            'q' => $q!=='' ? $q : null,
            'rederi' => $sel !== '' ? $sel : null,
            'sort' => $sort !== 'navn' ? $sort : null,
            'dir'  => $dir  !== 'asc'  ? $dir  : null,
            'pp'   => $perPage!==20 ? $perPage : null
          ]));
          $prev = $page > 1 ? $page - 1 : null;
          $next = ($offset + $perPage) < $total ? $page + 1 : null;
        ?>
        <?php if ($prev): ?>
          <a class="btn" href="?<?= $baseQs . ($baseQs?'&':'') . 'p='.$prev ?>" aria-label="Forrige side">« Forrige</a>
        <?php endif; ?>
        <?php if ($next): ?>
          <a class="btn" href="?<?= $baseQs . ($baseQs?'&':'') . 'p='.$next ?>" aria-label="Neste side">Neste »</a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
