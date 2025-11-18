<?php

require __DIR__ . '/CompetenceService.php';

class ScanService
{
    private string $rawDir;
    private string $tikaPath;

    public function __construct()
    {
        $config = require __DIR__ . '/../config.php';
        foreach ($config['cv_paths'] as $p) {
        if (is_dir($p)) {
            $this->rawDir = rtrim($p, '/');
            break;
        }
    }

    if (empty($this->rawDir)) {
        throw new RuntimeException('Aucun dossier CV trouvé');
    }
        $this->tikaPath = $config['tika_path'];

        if (!file_exists($this->tikaPath)) {
            throw new RuntimeException('Tika introuvable : ' . $this->tikaPath);
        }

    }

    public function scanAll(): array
    {
        $results = [];

        foreach (scandir($this->rawDir) as $personDir) {
            if ($personDir === '.' || $personDir === '..') {
                continue;
            }

            $fullPath = $this->rawDir . '/' . $personDir;
            if (!is_dir($fullPath)) {
                continue;
            }

            $cvFiles = $this->findCvFiles($fullPath);
if (empty($cvFiles)) {
    $results[$personDir] = 'Aucun CV trouvé';
    continue;
}

$combinedText = '';

foreach ($cvFiles as $cvFile) {
    $texte = $this->extractText($cvFile);
    if ($texte !== null && $texte !== '') {
        $combinedText .= $texte . "\n\n";
    }
}

$combinedText = trim($combinedText);

if ($combinedText === '') {
    $results[$personDir] = 'Erreur extraction';
    continue;
}

// upsert intervenant + compétences avec le texte concaténé
$intervenantId = $this->upsertIntervenant($personDir, $combinedText);

$competenceService = new CompetenceService();
$competenceService->analyzeAndSave($intervenantId, $combinedText);

$results[$personDir] = 'OK (' . count($cvFiles) . ' fichier(s) CV)';


            // upsert intervenant
            $intervenantId = $this->upsertIntervenant($personDir, $texte);

            // classification compétences
            $competenceService = new CompetenceService();
            $competenceService->analyzeAndSave($intervenantId, $texte);

            $results[$personDir] = 'OK';
        }

        return $results;
    }

    private function findCvFiles(string $personDir): array
{
    $allowedExt = ['pdf', 'doc', 'docx'];
    $files = [];

    if (!is_dir($personDir)) {
        return [];
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(
            $personDir,
            FilesystemIterator::SKIP_DOTS
        )
    );

    foreach ($iterator as $fileInfo) {
        /** @var SplFileInfo $fileInfo */
        if (!$fileInfo->isFile()) {
            continue;
        }

        $path = $fileInfo->getPathname();
        $filename = $fileInfo->getFilename();
        $ext = strtolower($fileInfo->getExtension());

        // extension ok ?
        if (!in_array($ext, $allowedExt, true)) {
            continue;
        }

        // nom de fichier doit contenir "cv" (insensible à la casse)
        if (stripos($filename, 'cv') === false) {
            continue;
        }

        // chemin doit contenir 20.CONTRACTUEL et DOC RH
        if (stripos($path, '20. CONTRACTUEL') === false || stripos($path, 'DOC RH') === false) {
            continue;
        }

        $files[] = $path;
    }

    return $files;
}



    private function extractText(string $filePath): ?string
    {
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if (in_array($ext, ['pdf', 'doc', 'docx'], true)) {
            return $this->extractWithTika($filePath);
        }

        return null;
    }

    private function extractWithTika(string $filePath): ?string
{
    $pdo = Database::getConnection(); // si besoin ailleurs, sinon à virer

    // adapte le chemin si nécessaire, par ex. `/usr/bin/java` ou résultat de `which java`
    $javaBin = 'java';

    $cmd = escapeshellcmd($javaBin)
         . ' -jar ' . escapeshellarg($this->tikaPath)
         . ' -t ' . escapeshellarg($filePath)
         . ' 2>&1';

    $output = shell_exec($cmd);

    if ($output === null) {
        return null;
    }

    // si Tika crashe, on évite de stocker la stacktrace dans texte_cv
    if (stripos($output, 'Exception in thread') !== false || stripos($output, 'java.net.') !== false) {
        return null;
    }

    $text = preg_replace('/\s+/', ' ', $output);
    $text = trim($text);

    return $text === '' ? null : $text;
}



    private function upsertIntervenant(string $slug, string $texte): int
{
    $pdo = Database::getConnection();

    // slug = "NOM-prenom avec éventuellement des espaces"
    $nom = null;
    $prenom = null;

    if (strpos($slug, '-') !== false) {
        // on coupe en 2 : tout avant le premier "-", tout le reste après
        [$partNom, $partPrenom] = explode('-', $slug, 2);

        $nom = trim(str_replace('_', ' ', $partNom));
        $prenom = trim(str_replace('_', ' ', $partPrenom));

        // capitalisation simple
        if ($nom !== '') {
            $nom = mb_convert_case($nom, MB_CASE_TITLE, 'UTF-8');
        }
        if ($prenom !== '') {
            $prenom = mb_convert_case($prenom, MB_CASE_TITLE, 'UTF-8');
        }
    }

    // chercher si l'intervenant existe déjà via le slug
    $stmt = $pdo->prepare('SELECT id FROM intervenants WHERE slug = ? LIMIT 1');
    $stmt->execute([$slug]);
    $row = $stmt->fetch();

    if ($row) {
        $id = (int)$row['id'];

        $stmt = $pdo->prepare('
            UPDATE intervenants
            SET prenom    = COALESCE(?, prenom),
                nom       = COALESCE(?, nom),
                texte_cv  = ?,
                updated_at = NOW()
            WHERE id = ?
        ');
        $stmt->execute([$prenom, $nom, $texte, $id]);

        return $id;
    }

    // création
    $stmt = $pdo->prepare('
        INSERT INTO intervenants (slug, prenom, nom, texte_cv, updated_at)
        VALUES (?, ?, ?, ?, NOW())
    ');
    $stmt->execute([$slug, $prenom, $nom, $texte]);

    return (int)$pdo->lastInsertId();
}

}
