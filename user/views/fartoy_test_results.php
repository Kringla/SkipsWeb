<?php if ($errorMsg): ?>
    <p><?php echo htmlspecialchars($errorMsg); ?></p>
<?php else: ?>
    <?php if ($mode === 'search'): ?>
        <?php if (empty($ships)): ?>
            <p>Ingen treff funnet.</p>
        <?php else: ?>
            <h3>Søkeresultat</h3>
            <table>
                <tr><th>ID</th><th>Navn</th><th>Type</th></tr>
                <?php foreach ($ships as $ship): ?>
                <tr>
                    <td><?php echo htmlspecialchars($ship['FartObj_ID']); ?></td>
                    <td><a href="fartoy_test.php?id=<?php echo urlencode($ship['FartObj_ID']); ?>">
                        <?php echo htmlspecialchars($ship['NavnObj']); ?></a></td>
                    <td><?php echo htmlspecialchars($ship['TypeNavn'] ?? ''); ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    <?php elseif ($mode === 'detail'): ?>
        <h3>Detaljer for fartøy ID <?php echo htmlspecialchars($shipDetails['FartObj_ID']); ?>: 
            <?php echo htmlspecialchars($shipDetails['NavnObj']); ?></h3>
        <p>Type: <?php echo htmlspecialchars($shipDetails['TypeNavn'] ?? ''); ?></p>
        <?php if (!empty($shipDetails['Leverandor'])): ?>
            <p>Byggeverft: <?php echo htmlspecialchars($shipDetails['Leverandor']); ?> 
               (<?php echo htmlspecialchars($shipDetails['LeverSted']); ?>)</p>
        <?php endif; ?>
        <?php if (!empty($shipDetails['Skrog'])): ?>
            <p>Skrogverft: <?php echo htmlspecialchars($shipDetails['Skrog']); ?> 
               (<?php echo htmlspecialchars($shipDetails['SkrogSted']); ?>)
               <?php if (!empty($shipDetails['BnrSkrog'])): ?>, Byggenr: 
               <?php echo htmlspecialchars($shipDetails['BnrSkrog']); ?><?php endif; ?></p>
        <?php endif; ?>
        <?php if (!empty($specs)): ?>
            <?php if (!empty($specs['Lengde']) && !empty($specs['Bredde']) && !empty($specs['Dypg'])): ?>
                <p>Dimensjoner (LxBxD): <?php echo htmlspecialchars($specs['Lengde']); ?> m × 
                   <?php echo htmlspecialchars($specs['Bredde']); ?> m × 
                   <?php echo htmlspecialchars($specs['Dypg']); ?> m</p>
            <?php endif; ?>
            <?php if (!empty($specs['Tonnasje'])): ?>
                <p>Tonnasje: <?php echo htmlspecialchars($specs['Tonnasje']); ?></p>
            <?php endif; ?>
            <?php if (!empty($specs['MotorDetalj'])): ?>
                <p>Hovedmotor: <?php echo htmlspecialchars($specs['MotorDetalj']); ?> 
                   (<?php echo htmlspecialchars($specs['MotorEff']); ?>)</p>
            <?php endif; ?>
        <?php endif; ?>
        <?php if (!empty($nameHistory)):
            $oldNames = array_filter($nameHistory, function($nm) use ($shipDetails) {
                return $nm !== $shipDetails['NavnObj'];
            });
            if (!empty($oldNames)): ?>
            <p>Tidligere navn:</p>
            <ul>
                <?php foreach ($oldNames as $nm): ?>
                <li><?php echo htmlspecialchars($nm); ?></li>
                <?php endforeach; ?>
            </ul>
            <?php endif;
        endif; ?>
    <?php endif; ?>
<?php endif; ?>
