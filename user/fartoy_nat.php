<?php
require_once __DIR__ . '/../includes/bootstrap.php';

ini_set('display_errors', '1');
error_reporting(E_ALL);

$nasjonId = isset($_GET['nasjon_id']) ? (int)$_GET['nasjon_id'] : 0; // 0 = alle
$q        = isset($_GET['q']) ? trim($_GET['q']) : '';

/** Hent nasjoner til dropdown */
$nasjoner = [];
$res = $conn->query("SELECT Nasjon_ID, Nasjon FROM tblzNasjon ORDER BY Nasjon");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $nasjoner[] = $row;
    }
    $res->free();
}
// Input
$nasjonId = isset($_GET['nasjon_id']) ? (int)$_GET['nasjon_id'] : 0; // 0 = alle
$q        = isset($_GET['q']) ? trim($_GET['q']) : '';

// Ny spørring: nyeste status pr. fartøy + ønskede felter
$sql = "
WITH latest_tid AS (
  SELECT t.*,
         ROW_NUMBER() OVER (
           PARTITION BY t.FartObj_ID
           ORDER BY t.YearTid DESC, t.MndTid DESC, t.FartTid_ID DESC
         ) AS rn
  FROM tblFartTid t
),
latest_name AS (
  /* Foretrekk navnet som matcher nyeste tidslinje hvis tilgjengelig,
     ellers fall-back til høyeste FartNavn_ID for objektet */
  SELECT fn.FartObj_ID,
         COALESCE(
           fn2.FartNavn, 
           (SELECT fn3.FartNavn 
            FROM tblFartNavn fn3 
            WHERE fn3.FartObj_ID = fn.FartObj_ID 
            ORDER BY fn3.FartNavn_ID DESC LIMIT 1)
         ) AS FartNavn
  FROM tblFartNavn fn
  LEFT JOIN latest_tid lt2 
         ON lt2.FartObj_ID = fn.FartObj_ID AND lt2.rn = 1 AND lt2.FartNavn_ID = fn.FartNavn_ID
  LEFT JOIN tblFartNavn fn2
         ON fn2.FartNavn_ID = lt2.FartNavn_ID
  GROUP BY fn.FartObj_ID
)
SELECT
  o.FartObj_ID,
  ft.TypeFork,                                     -- TypeFork
  ln.FartNavn,                                     -- FartNavn (nyeste hvis mulig)
  lt.RegHavn,                                      -- RegHavn (nyeste)
  n.Nasjon,                                        -- Nasjon (nyeste)
  o.Bygget,                                        -- Bygget
  lt.Kallesignal,                                  -- Kallesignal (nyeste)
  lt.Rederi                                         -- Rederi (nyeste)
FROM tblFartObj o
LEFT JOIN latest_tid lt      ON lt.FartObj_ID = o.FartObj_ID AND lt.rn = 1
LEFT JOIN tblzNasjon n       ON n.Nasjon_ID   = lt.Nasjon_ID
LEFT JOIN tblzFartType ft    ON ft.FartType_ID= o.FartType_ID
LEFT JOIN latest_name ln     ON ln.FartObj_ID = o.FartObj_ID
WHERE (? = 0 OR lt.Nasjon_ID = ?)
  AND (? = '' OR ln.FartNavn LIKE CONCAT('%', ?, '%') OR o.NavnObj LIKE CONCAT('%', ?, '%'))
ORDER BY COALESCE(ln.FartNavn, o.NavnObj), o.FartObj_ID
LIMIT 200
";

$stmt = $conn->prepare($sql);
if (!$stmt) { die("Prepare feilet: " . $conn->error); }

// 2 ints (nasjonId, nasjonId) + 3 strings (q, q, q)
$stmt->bind_param('iisss', $nasjonId, $nasjonId, $q, $q, $q);
$stmt->execute();
$result = $stmt->get_result();
$rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
$stmt->close();

?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/menu.php'; ?>

<div class="search-page">
  <h2>Søk fartøy pr. nasjon</h2>

  <form method="get" action="">
    <label for="nasjon_id">Nasjon:</label>
    <select name="nasjon_id" id="nasjon_id">
      <option value="0"<?php echo $nasjonId===0?' selected':''; ?>>Alle</option>
      <?php foreach ($nasjoner as $n): ?>
        <option value="<?php echo (int)$n['Nasjon_ID']; ?>"<?php echo ($nasjonId==(int)$n['Nasjon_ID'])?' selected':''; ?>>
          <?php echo htmlspecialchars($n['Nasjon']); ?>
        </option>
      <?php endforeach; ?>
    </select>

    <label for="q">Navn (delstreng):</label>
    <input type="text" id="q" name="q" value="<?php echo htmlspecialchars($q); ?>" placeholder="F.eks. 'fjord'">

    <button type="submit">Søk</button>
  </form>

  <?php if ($rows): ?>
  <p>Fant <?php echo count($rows); ?> fartøy (viser maks 200).</p>
  <table border="1" cellpadding="6" cellspacing="0">
    <thead>
      <tr>
        <th>Type</th>
        <th>Fartøynavn</th>
        <th>Reg.havn</th>
        <th>Nasjon</th>
        <th>Bygget</th>
        <th>Kallesignal</th>
        <th>Rederi</th>
        <th>Detalj</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><?php echo htmlspecialchars($r['TypeFork'] ?? ''); ?></td>
        <td><?php echo htmlspecialchars($r['FartNavn'] ?? ''); ?></td>
        <td><?php echo htmlspecialchars($r['RegHavn'] ?? ''); ?></td>
        <td><?php echo htmlspecialchars($r['Nasjon'] ?? ''); ?></td>
        <td><?php echo htmlspecialchars($r['Bygget'] ?? ''); ?></td>
        <td><?php echo htmlspecialchars($r['Kallesignal'] ?? ''); ?></td>
        <td><?php echo htmlspecialchars($r['Rederi'] ?? ''); ?></td>
        <td><a href="fartoydetaljer.php?id=<?php echo (int)$r['FartObj_ID']; ?>">Vis</a></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
<?php elseif ($_GET): ?>
  <p>Ingen treff.</p>
<?php else: ?>
  <p>Velg nasjon og/eller skriv del av navn for å søke.</p>
<?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
