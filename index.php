<?php
require __DIR__ . '/src/Database.php';
require __DIR__ . '/src/ScanService.php';

$pdo = Database::getConnection();

$action = $_GET['action'] ?? null;
$messageScan = null;

// déclenchement du scan
if ($action === 'scan') {
    $scanService = new ScanService();
    $results = $scanService->scanAll();
    $messageScan = $results;
}

// recherche
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$resultsSearch = [];

if ($q !== '') {
    $stmt = $pdo->prepare('
        SELECT id, slug, prenom, nom,
               SUBSTRING(texte_cv, LOCATE(:q, LOWER(texte_cv)), 200) AS extrait
        FROM intervenants
        WHERE LOWER(texte_cv) LIKE CONCAT("%", LOWER(:q), "%")
        ORDER BY updated_at DESC
        LIMIT 100
    ');
    $stmt->execute([':q' => $q]);
    $resultsSearch = $stmt->fetchAll();
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Base CV intervenants</title>
    <style>
        body { font-family: sans-serif; margin: 20px; }
        .btn { padding: 6px 12px; border: 1px solid #333; background: #eee; text-decoration: none; }
        .btn:hover { background: #ddd; }
        .scan-result { font-size: 0.9em; margin-top: 10px; }
        table { border-collapse: collapse; width: 100%; margin-top: 15px; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        .extrait { font-size: 0.9em; color: #555; }
    </style>
</head>
<body>

    <h1>Base CV intervenants</h1>

    <section>
        <h2>1. Mettre à jour la base à partir des dossiers</h2>
        <p>Dossiers scannés dans <code>storage/cv_raw/</code> (un dossier par intervenant, ex : <code>jean-dupont</code>).</p>
        <a class="btn" href="?action=scan">Scanner les CV</a>

        <?php if ($messageScan !== null): ?>
            <div class="scan-result">
                <h3>Résultat du scan :</h3>
                <ul>
                    <?php foreach ($messageScan as $slug => $status): ?>
                        <li><strong><?= htmlspecialchars($slug) ?></strong> : <?= htmlspecialchars($status) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </section>

    <hr>

    <section>
        <h2>2. Recherche par terme dans les CV</h2>
        <form method="get" action="">
            <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="ex : vue.js, docker, pédagogie..." size="40">
            <button type="submit" class="btn">Rechercher</button>
        </form>

        <?php if ($q !== ''): ?>
            <h3>Résultats pour « <?= htmlspecialchars($q) ?> »</h3>
            <?php if (empty($resultsSearch)): ?>
                <p>Aucun intervenant trouvé.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Intervenant</th>
                            <th>Slug</th>
                            <th>Extrait du CV</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($resultsSearch as $row): ?>
                            <tr>
                                <td>
                                    <?= htmlspecialchars(trim(($row['prenom'] ?? '') . ' ' . ($row['nom'] ?? ''))) ?: '(non renseigné)' ?>
                                </td>
                                <td><?= htmlspecialchars($row['slug']) ?></td>
                                <td class="extrait">
                                    <?= htmlspecialchars($row['extrait'] ?? '') ?>
                                    ...
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        <?php endif; ?>
    </section>

</body>
</html>
