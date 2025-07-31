<?php if (\$results): ?>
<table>
    <thead>
        <tr><th>Navn</th><th>IMO</th><th>Type</th><th>Byggeår</th><th>Detaljer</th></tr>
    </thead>
    <tbody>
    <?php foreach (\$results as \$row): ?>
        <tr>
            <td><?= htmlspecialchars(\$row['Navn']) ?></td>
            <td><?= htmlspecialchars(\$row['IMO'] ?? '') ?></td>
            <td><?= htmlspecialchars(\$row['FartoyType'] ?? '') ?></td>
            <td><?= htmlspecialchars(\$row['Bygget'] ?? '') ?></td>
            <td><a href="fartoy_vis.php?obj_id=<?= \$row['FartObj_ID'] ?>">Detaljer</a></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php else: ?>
<p>Ingen treff.</p>
<?php endif; ?>