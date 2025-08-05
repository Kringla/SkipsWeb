<?php
// /user/search_fartoy.php
// Søkeskjema og resultatvisning for S1‐Fartøy

// 1) Last inn DB‐tilkobling
require_once __DIR__ . '/../config/config.php'; // gir $mysqli

// 2) Hent input-parametre
$navn_frag = isset($_GET['navn_frag']) ? trim($_GET['navn_frag']) : '';
$flaggstat = isset($_GET['flaggstat']) ? $_GET['flaggstat'] : 'ALL';
$page      = max(1, (int)($_GET['page'] ?? 1));
$perPage   = 20;
$offset    = ($page - 1) * $perPage;

// 3) Bygg WHERE‐betingelser
$where   = [];
$params  = [];
$types   = '';

// Del‐av‐navn
if ($navn_frag !== '') {
    $where[] = "n.FartNavn LIKE ?";
    $params[] = '%' . $navn_frag . '%';
    $types   .= 's';
}

// Flaggstat
if ($flaggstat !== 'ALL') {
    $where[] = "t.Nasjon_ID = ?";
    $params[] = $flaggstat;
    $types   .= 'i';
}

$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// 4) Tell totalt antall treff
$count_sql = "
    SELECT COUNT(*)
    FROM tblFartNavn n
    JOIN tblFartObj o   ON o.FartObj_ID   = n.FartObj_ID
    JOIN tblFartTid t   ON t.FartNavn_ID  = n.FartNavn_ID
    JOIN tblzNasjon z   ON z.Nasjon_ID    = t.Nasjon_ID
    $where_sql
";
$stmt = $mysqli->prepare($count_sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$stmt->bind_result($total);
$stmt->fetch();
$stmt->close();

// 5) Hent én side med data
$data_sql = "
    SELECT 
      COALESCE(f.TypeFork, '') AS type,
      n.FartNavn            AS navn,
      t.RegHavn             AS registerhavn,
      z.Nasjon              AS flaggstat,
      o.Bygget              AS bygget,
      t.Kallesignal         AS kallesignal,
      t.Rederi              AS rederi,
      n.FartNavn_ID         AS id
    FROM tblFartNavn n
    JOIN tblFartObj o   ON o.FartObj_ID  = n.FartObj_ID
    JOIN tblFartTid t   ON t.FartNavn_ID = n.FartNavn_ID
    JOIN tblzNasjon z   ON z.Nasjon_ID   = t.Nasjon_ID
    LEFT JOIN tblzFartType f ON f.FartType_ID = n.FartType_ID
    $where_sql
    ORDER BY n.FartNavn ASC
    LIMIT ?, ?
";
$stmt = $mysqli->prepare($data_sql);

// bind input‐params + offset/limit
$bindTypes = $types . 'ii';
$bindParams = array_merge($params, [ $offset, $perPage ]);
$stmt->bind_param($bindTypes, ...$bindParams);
$stmt->execute();
$stmt->bind_result($type, $navn, $havn, $flag, $bygget, $kall, $rdr, $id);

// 6) Hent dropdown‐liste for flaggstat
$ns = $mysqli->query("
    SELECT Nasjon_ID AS value, Nasjon AS label
    FROM tblzNasjon
    ORDER BY Nasjon
");

?><!DOCTYPE html>
<html lang="no">
<head>
  <meta charset="utf-8">
  <title>Finn fartøy</title>
  <link rel="stylesheet" href="../style.css">
</head>
<body>
  <h1>Finn fartøy</h1>
  <form method="get" action="search_fartoy.php">
    <label>
      Søk på del av navn:
      <input type="text" name="navn_frag" value="<?= htmlspecialchars($navn_frag) ?>">
    </label>
    <label>
      Flaggstat:
      <select name="flaggstat">
        <option value="ALL" <?= $flaggstat === 'ALL' ? 'selected' : '' ?>>Alle</option>
      <?php while($row = $ns->fetch_object()): ?>
        <option value="<?= $row->value ?>"
          <?= $row->value == $flaggstat ? 'selected' : '' ?>>
          <?= htmlspecialchars($row->label) ?>
        </option>
      <?php endwhile; ?>
      </select>
    </label>
    <button type="submit">Søk</button>
  </form>

  <p>Antall funnet: <?= $total ?></p>

  <table border="1" cellpadding="4" cellspacing="0">
    <thead>
      <tr>
        <th>Type</th>
        <th>Navn</th>
        <th>Registerhavn</th>
        <th>Flaggstat</th>
        <th>Byggeår</th>
        <th>Kallesignal</th>
        <th>Rederi/Eier</th>
      </tr>
    </thead>
    <tbody>
    <?php while($stmt->fetch()): ?>
      <tr ondblclick="location.href='Fartoysdetaljer.php?FartNavn_ID=<?= $id ?>';">
        <td align="center"><?= htmlspecialchars($type) ?></td>
        <td><?= htmlspecialchars($navn) ?></td>
        <td><?= htmlspecialchars($havn) ?></td>
        <td><?= htmlspecialchars($flag) ?></td>
        <td align="center"><?= htmlspecialchars($bygget) ?></td>
        <td align="center"><?= htmlspecialchars($kall) ?></td>
        <td><?= htmlspecialchars($rdr) ?></td>
      </tr>
    <?php endwhile; ?>
    </tbody>
  </table>

  <div class="paging">
  <?php
    $pages = ceil($total / $perPage);
    for ($i=1; $i <= $pages; $i++):
      $qs = http_build_query([
        'navn_frag' => $navn_frag,
        'flaggstat' => $flaggstat,
        'page'      => $i
      ]);
  ?>
    <a href="?<?= $qs ?>"<?= $i === $page ? ' class="current"' : '' ?>>
      <?= $i ?>
    </a>
  <?php endfor; ?>
  </div>
</body>
</html>

