<form method="get" action="">
    <label>Navn: <input type="text" name="navn" value="<?= htmlspecialchars(\$name) ?>"></label>
    <label>IMO:  <input type="text" name="imo"  value="<?= htmlspecialchars(\$imo)  ?>"></label>
    <label>Funksjon:
        <select name="funksjon">
            <option value="">--Alle--</option>
            <?php foreach (\$functions as \$fn): ?>
                <option value="<?= \$fn['FartFunk_ID'] ?>" <?= \$funcId == \$fn['FartFunk_ID'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars(\$fn['Funksjon']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>
    <button type="submit">Søk</button>
</form>