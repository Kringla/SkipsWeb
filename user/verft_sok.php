<?php
/**
 * user/verft_sok.php
 * - Fritekstsøk mot verft (navn/sted) -> list fartøy bygget ved treffene
 * - Valg av ett verft snevrer inn listen
 * - Sortering (navn/byggeår, asc/desc), default: byggeår DESC
 * - Antall per side (20/50/100)
 * - Klikk på fartøy -> user/fartoydetaljer.php?id=<FartNavn_ID>
 * - CSV-eksport via ?export=csv (eksporterer alle treff, ignorerer paging)
 */
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/auth.php'; // ingen require_admin()

$q        = trim($_GET['q'] ?? '');
$verft_id = isset($_GET['verft_id']) ? (int)$_GET['verft_id'] : 0;

// sortering (default byggeår DESC)
$sort     = strtolower(trim($_GET['sort'] ?? 'bygget'));
$dir      = strtolower(trim($_GET['dir'] ?? 'desc'));
$sortMap  = ['navn' => 'fn.FartNavn', 'bygget' => 'fo.Bygget'];
$col      = $sortMap[$sort] ?? $sortMap['bygget'];
$direction= ($dir === 'desc') ? 'DESC' : 'ASC';
$orderBy  = $col . ' ' . $direction . ', fn.FartNavn ASC'; // sekundær for stabilitet

// per page
$ppAllowed = [20,50,100];
$perPage   = (int)($_GET['pp'] ?? 20);
if (!in_array($perPage, $ppAllowed, true)) $perPage = 20;

$page   = max(1, (int)($_GET['p'] ?? 1));
$offset = ($page - 1) * $perPage;

$verftList = [];
$verftIDs  = [];

/* 1) Hent verft-treff (for drop-down og chips) */
if ($q !== '' || $verft_id > 0) {
    $sql = "SELECT v.Verft_ID, v.VerftNavn, v.Sted, zn.Nasjon
            FROM tblVerft v
            LEFT JOIN tblzNasjon zn ON zn.Nasjon_ID = v.Nasjon_ID
            WHERE 1=1 ";
    $params = [];
    $types  = '';

    if ($q !== '') {
        $sql .= "AND (v.VerftNavn LIKE ? OR v.Sted LIKE ?) ";
        $like = '%'.$q.'%';
        $params[] = $like; $params[] = $like; $types .= 'ss';
    }
    if ($verft_id > 0) {
        $sql .= "AND v.Verft_ID = ? ";
        $params[] = $verft_id; $types .= 'i';
    }
    $sql .= "ORDER BY v.VerftNavn LIMIT 200";

    $stmt = $conn->prepare($sql);
    if ($types !== '') $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) {
        $verftList[] = $r;
        $verftIDs[]  = (int)$r['Verft_ID'];
    }
    $stmt->close();
}

/* 2) Bygg IN-liste for verft-IDer */
$inList = '';
if ($verft_id > 0) {
    $inList = (string)$verft_id;
} elseif (!empty($verftIDs)) {
    $inList = implode(',', array_map('intval', $verftIDs));
}

/* 3) CSV-eksport (tidlig exit, respekterer sortering) */
if (isset($_GET['export']) && $_GET['export'] === 'csv' && $inList !== '') {
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
        FROM tblFartSpes fs
        JOIN tblFartObj fo ON fo.FartObj_ID = fs.FartObj_ID
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
        WHERE fs.Verft_ID IN ($inList)
        ORDER BY $orderBy
    ";
    $res = $conn->query($exportSql);

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="verft_sok.csv"');

    $out = fopen('php://output', 'w');
    fputcsv($out, ['FartObj_ID','FartNavn','Type','Bygget','RegHavn','Nasjon','Kallesignal','Rederi','FartSpes_ID','FartNavn_ID']);
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            fputcsv($out, [
                $r['FartObj_ID'], $r['FartNavn'], $r['TypeFork'], $r['Bygget'],
                $r['RegHavn'], $r['Nasjon'], $r['Kallesignal'], $r['Rederi'],
                $r['FartSpes_ID'], $r['FartNavn_ID']
            ]);
        }
    }
    fclose($out);
    exit;
}

/* 4) Paging + resultater */
$total = 0;
$rows  = [];

if ($inList !== '') {
    // Tell antall distinkte fartøy
    $countSql = "SELECT COUNT(DISTINCT fo.FartObj_ID) AS c
                 FROM tblFartSpes fs
                 JOIN tblFartObj fo ON fo.FartObj_ID = fs.FartObj_ID
                 WHERE fs.Verft_ID IN ($inList)";
    $countRes = $conn->query($countSql);
    $total = (int)($countRes->fetch_assoc()['c'] ?? 0);

    // Hent rader
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
        FROM tblFartSpes fs
        JOIN tblFartObj fo ON fo.FartObj_ID = fs.FartObj_ID
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
        WHERE fs.Verft_ID IN ($inList)
        ORDER BY $orderBy
        LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($rowsSql);
    $stmt->bind_param('ii', $perPage, $offset);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) { $rows[] = $r; }
    $stmt->close();
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/menu.php';
$BASE = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
?>
<div class="container mt-4">
  <div class="card">
    <form method="get" class="grid cols-5" style="gap:1rem;" aria-labelledby="verft-sok-legend">
      <div>
        <label for="q">Fritekstsøk (verft/sted)</label>
        <input id="q" class="input" type="text" name="q" value="<?= e($q) ?>" placeholder="F.eks. Ulstein, Bergen" aria-label="Fritekstsøk etter verft eller sted">
      </div>
      <div>
        <label for="verft_id">Begrens til verft</label>
        <select id="verft_id" name="verft_id" class="input" aria-label="Velg et spesifikt verft">
          <option value="0">— Alle treff —</option>
          <?php foreach ($verftList as $v): ?>
            <option value="<?= (int)$v['Verft_ID'] ?>" <?= $verft_id===(int)$v['Verft_ID']?'selected':''; ?>>
              <?= e($v['VerftNavn'] . ($v['Sted']?', '.$v['Sted']:'') . (!empty($v['Nasjon'])?' • '.$v['Nasjon']:'')) ?>
            </option>
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
          <?php foreach ($ppAllowed as $pp): ?>
            <option value="<?= $pp ?>" <?= $perPage===$pp?'selected':''; ?>><?= $pp ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="self-end" style="grid-column: 1 / -1;">
        <button class="btn primary" type="submit">Søk</button>
        <a class="btn" href="<?= url('user/verft_sok.php') ?>">Nullstill</a>
      </div>
    </form>
  </div>

  <?php
  /* MINI-HEADER: valgt verft, ellers «chips» */
  if ($verft_id > 0) {
      $stmt = $conn->prepare("
          SELECT v.Verft_ID, v.VerftNavn, v.Sted, zn.Nasjon,
                 COUNT(DISTINCT fs.FartObj_ID) AS AntFartoy
          FROM tblVerft v
          LEFT JOIN tblzNasjon zn ON zn.Nasjon_ID = v.Nasjon_ID
          LEFT JOIN tblFartSpes fs ON fs.Verft_ID = v.Verft_ID
          WHERE v.Verft_ID = ?
          GROUP BY v.Verft_ID, v.VerftNavn, v.Sted, zn.Nasjon
          LIMIT 1
      ");
      $stmt->bind_param('i', $verft_id);
      $stmt->execute();
      $verftInfo = $stmt->get_result()->fetch_assoc();
      $stmt->close();

      if ($verftInfo) {
          $clearQs = http_build_query(array_filter([
            'q' => $q !== '' ? $q : null,
            'sort' => $sort !== 'bygget' ? $sort : null,
            'dir'  => $dir  !== 'desc'   ? $dir  : null,
            'pp'   => $perPage!==20 ? $perPage : null
          ]));
          $clearHref = url('user/verft_sok.php') . ($clearQs ? ('?' . $clearQs) : '');
          ?>
          <div class="card mt-3" style="border-left:4px solid #2c3e50;">
            <div style="display:flex;justify-content:space-between;gap:1rem;align-items:center;">
              <div>
                <div style="font-size:1.05rem;font-weight:600;"><?= e($verftInfo['VerftNavn']) ?></div>
                <div class="muted">
                  <?= e($verftInfo['Sted'] ?: '') ?>
                  <?= !empty($verftInfo['Nasjon']) ? ' • ' . e($verftInfo['Nasjon']) : '' ?>
                  <?php if (isset($verftInfo['AntFartoy'])): ?> • Fartøy bygget: <?= (int)$verftInfo['AntFartoy'] ?><?php endif; ?>
                </div>
              </div>
              <div><a class="btn" href="<?= $clearHref ?>">Fjern filter</a></div>
            </div>
          </div>
          <?php
      }
  } elseif (!empty($verftList)) {
      $chips = array_slice($verftList, 0, 12);
      ?>
      <div class="card mt-3">
        <div class="muted" style="margin-bottom:.5rem;">Treff på verft:</div>
        <div style="display:flex;flex-wrap:wrap;gap:.5rem;">
          <?php foreach ($chips as $v):
              $qs = http_build_query(array_filter([
                  'q' => $q !== '' ? $q : null,
                  'verft_id' => (int)$v['Verft_ID'],
                  'sort' => $sort !== 'bygget' ? $sort : null,
                  'dir'  => $dir  !== 'desc'   ? $dir  : null,
                  'pp'   => $perPage!==20 ? $perPage : null
              ]));
              $href = '?' . $qs;
              $label = $v['VerftNavn']
                     . ($v['Sted'] ? ' • ' . $v['Sted'] : '')
                     . (!empty($v['Nasjon']) ? ' • ' . $v['Nasjon'] : '');
          ?>
            <a class="btn" href="<?= $href ?>" style="padding:.25rem .6rem;border-radius:999px;"><?= e($label) ?></a>
          <?php endforeach; ?>
        </div>
      </div>
      <?php
  }
  ?>

  <?php if ($inList === '' && ($q !== '' || $verft_id > 0)): ?>
    <div class="card mt-3" role="status" aria-live="polite">
      <strong>Ingen treff.</strong>
      <div class="muted">Tips: prøv kortere søkestreng (f.eks. bare kommunenavn) eller fjern filteret.</div>
    </div>
  <?php endif; ?>

  <?php if ($inList !== ''): ?>
    <div class="card mt-3" role="status" aria-live="polite">
      <strong><?= $total ?></strong> fartøy funnet
      <?php if ($verft_id>0): ?> for valgt verft<?php elseif ($q!==''): ?> for søk «<?= e($q) ?>»<?php endif; ?>.
      <div class="mt-2">
        <?php
          $qsExport = http_build_query(array_filter([
            'q' => $q!=='' ? $q : null,
            'verft_id' => $verft_id ?: null,
            'sort' => $sort !== 'bygget' ? $sort : null,
            'dir'  => $dir  !== 'desc'   ? $dir  : null,
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
            'verft_id' => $verft_id ?: null,
            'sort' => $sort !== 'bygget' ? $sort : null,
            'dir'  => $dir  !== 'desc'   ? $dir  : null,
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
