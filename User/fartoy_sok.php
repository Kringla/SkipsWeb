<?php
require_once __DIR__ . '/../includes/bootstrap.php';

// Hent filtre fra GET
\$name   = trim(\$_GET['navn'] ?? '');
\$imo    = trim(\$_GET['imo']  ?? '');\$funcId = trim(\$_GET['funksjon'] ?? '');

// Hent dropdown-alternativer
\$functions = \$pdo
    ->query('SELECT FartFunk_ID, Funksjon FROM tblzFartFunk ORDER BY Funksjon')
    ->fetchAll();

// Bygg WHERE
\$conds = [];
\$params = [];
if (\$name !== '') {
    \$conds[] = 'n.Navn LIKE :navn';
    \$params[':navn'] = "%{\$name}%";
}
if (\$imo !== '') {
    \$conds[] = 'o.IMO LIKE :imo';
    \$params[':imo'] = "%{\$imo}%";
}
if (\$funcId !== '') {
    \$conds[] = 'latestSpec.FartFunk_ID = :funksjon';
    \$params[':funksjon'] = \$funcId;
}
\$where = \$conds ? 'WHERE ' . implode(' AND ', \$conds) : '';

// Hovedspørring
\$sql = <<<SQL
SELECT n.Navn, o.IMO, f.Funksjon AS FartoyType, o.Bygget, o.FartObj_ID
FROM tblFartNavn n
JOIN tblFartObj o USING (FartObj_ID)
LEFT JOIN (
    SELECT fs1.FartObj_ID, fs1.FartFunk_ID
    FROM tblFartSpes fs1
    JOIN (
        SELECT FartObj_ID, MAX(Dato) AS MaxDato
        FROM tblFartSpes
        GROUP BY FartObj_ID
    ) latest ON fs1.FartObj_ID = latest.FartObj_ID AND fs1.Dato = latest.MaxDato
) latestSpec USING (FartObj_ID)
LEFT JOIN tblzFartFunk f USING (FartFunk_ID)
\$where
ORDER BY n.Navn
SQL;

// Kjør spørring
\$stmt = \$pdo->prepare(\$sql);
\$stmt->execute(\$params);
\$results = \$stmt->fetchAll();

// Vis view
require __DIR__ . '/views/fartoy_sok_form.php';
require __DIR__ . '/views/fartoy_sok_results.php';