<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$searchQuery = '';
$ships = [];
$shipDetails = null;
$nameHistory = [];
$specs = [];
$errorMsg = null;
$mode = 'form';  // 'form' = ingen søk enda, 'search' = søkeresultat, 'detail' = detaljvisning

// Hvis søkestreng er angitt
if (isset($_GET['q'])) {
    $searchQuery = trim($_GET['q']);
    $mode = 'search';
    if ($searchQuery === '') {
        $errorMsg = 'Vennligst skriv inn et søkeord eller ID.';
    } else {
        if (ctype_digit($searchQuery)) {
            // Søk på ID (eksakt)
            $idVal = (int)$searchQuery;
            $stmt = $conn->prepare(
                "SELECT f.FartObj_ID, f.NavnObj, t.Type AS TypeNavn
                 FROM tblFartObj f
                 LEFT JOIN tblzFartType t ON f.FartType_ID = t.FartType_ID
                 WHERE f.FartObj_ID = ?"
            );
            $stmt->bind_param("i", $idVal);
        } else {
            // Søk på delstreng i navn
            $like = '%' . $searchQuery . '%';
            $stmt = $conn->prepare(
                "SELECT f.FartObj_ID, f.NavnObj, t.Type AS TypeNavn
                 FROM tblFartObj f
                 LEFT JOIN tblzFartType t ON f.FartType_ID = t.FartType_ID
                 WHERE f.NavnObj LIKE ?"
            );
            $stmt->bind_param("s", $like);
        }
        if ($stmt) {
            $stmt->execute();
            $res = $stmt->get_result();
            $ships = $res->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        }
    }
}

// Hvis detalj-ID er angitt (klikket på et resultat)
if (isset($_GET['id'])) {
    $idParam = $_GET['id'];
    $mode = 'detail';
    if (!is_valid_id($idParam)) {
        $errorMsg = 'Ingen data';
    } else {
        $idVal = intval($idParam);
        // Hent hovedinformasjon om fartøyet (inkl. type og verft)
        if ($stmt = $conn->prepare(
            "SELECT f.FartObj_ID, f.NavnObj, f.IMO, f.Bygget, f.Kontrahert, f.Kjolstrukket, f.Sjosatt, f.Levert, f.BnrSkrog,
                    t.Type AS TypeNavn,
                    v1.VerftNavn AS Leverandor, v1.Sted AS LeverSted,
                    v2.VerftNavn AS Skrog, v2.Sted AS SkrogSted
             FROM tblFartObj f
             LEFT JOIN tblzFartType t ON f.FartType_ID = t.FartType_ID
             LEFT JOIN tblVerft v1 ON f.LeverID = v1.Verft_ID
             LEFT JOIN tblVerft v2 ON f.SkrogID = v2.Verft_ID
             WHERE f.FartObj_ID = ?"
        )) {
            $stmt->bind_param("i", $idVal);
            $stmt->execute();
            $res = $stmt->get_result();
            $shipDetails = $res->fetch_assoc();
            $stmt->close();
        }
        if (!$shipDetails) {
            $errorMsg = 'Ingen data';
        } else {
            // Hent navnehistorikk
            if ($stmt = $conn->prepare(
                "SELECT FartNavn, TidlNavn FROM tblFartNavn WHERE FartObj_ID = ?"
            )) {
                $stmt->bind_param("i", $idVal);
                $stmt->execute();
                $res = $stmt->get_result();
                $nameRows = $res->fetch_all(MYSQLI_ASSOC);
                $stmt->close();
                if ($nameRows) {
                    // Bygg navne-kjedeliste (fra første navn til siste)
                    $lookup = [];
                    foreach ($nameRows as $row) {
                        $currName = $row['FartNavn'];
                        $prevName = $row['TidlNavn'];
                        if ($prevName !== null && $prevName !== '') {
                            $lookup[$prevName] = $currName;
                        } else {
                            // Første registrerte navn (har ingen forrige)
                            $nameHistory[] = $currName;
                        }
                    }
                    if (empty($nameHistory)) {
                        $nameHistory[] = $shipDetails['NavnObj'];
                    }
                    // Sett sammen historikken i korrekt rekkefølge
                    $currentName = end($nameHistory);
                    while (isset($lookup[$currentName])) {
                        $nextName = $lookup[$currentName];
                        $nameHistory[] = $nextName;
                        $currentName = $nextName;
                    }
                }
            }
            // Hent nyeste spesifikasjonsrad (tekniske detaljer)
            if ($stmt = $conn->prepare(
                "SELECT YearSpes, MndSpes, Materiale, FunkDetalj, TeknDetalj, Fartklasse, Kapasitet,
                        Rigg, MotorDetalj, MotorEff, MaxFart, Lengde, Bredde, Dypg, Tonnasje, Drektigh
                 FROM tblFartSpes 
                 WHERE FartObj_ID = ?
                 ORDER BY FartSpes_ID DESC LIMIT 1"
            )) {
                $stmt->bind_param("i", $idVal);
                $stmt->execute();
                $res = $stmt->get_result();
                $specs = $res->fetch_assoc();
                $stmt->close();
            }
        }
    }
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/menu.php'; ?>
<div class="search-page">
    <h2>Søk i fartøyregister</h2>
    <?php include __DIR__ . '/views/fartoy_sok_form.php'; ?>
    <?php if ($mode !== 'form') {
        include __DIR__ . '/views/fartoy_sok_results.php';
    } ?>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
