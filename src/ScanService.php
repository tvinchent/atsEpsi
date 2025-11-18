<?php

require __DIR__ . '/CompetenceService.php';

class ScanService
{
    private string $rawDir;
    private string $tikaPath;

    public function __construct()
    {
        $config = require __DIR__ . '/../config.php';
        $this->rawDir   = $config['cv_raw_dir'];
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

            $cvFile = $this->findCvFile($fullPath);
            if (!$cvFile) {
                $results[$personDir] = 'Aucun CV trouvé';
                continue;
            }

            $texte = $this->extractText($cvFile);
            if ($texte === null || $texte === '') {
                $results[$personDir] = 'Erreur extraction';
                continue;
            }

            // upsert intervenant
            $intervenantId = $this->upsertIntervenant($personDir, $texte);

            // classification compétences
            $competenceService = new CompetenceService();
            $competenceService->analyzeAndSave($intervenantId, $texte);

            $results[$personDir] = 'OK';
        }

        return $results;
    }

    private function findCvFile(string $dir): ?string
    {
        $allowed = ['pdf', 'doc', 'docx'];
        foreach (scandir($dir) as $file) {
            if ($file === '.' || $file === '..') continue;
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($ext, $allowed, true)) {
                return $dir . '/' . $file;
            }
        }
        return null;
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

        // découper prenom-nom depuis le slug
        $prenom = null;
        $nom    = null;
        if (strpos($slug, '-') !== false) {
            [$p, $n] = explode('-', $slug, 2);
            $prenom = ucfirst(str_replace('_', ' ', $p));
            $nom    = ucfirst(str_replace('_', ' ', $n));
        }

        // existe déjà ?
        $stmt = $pdo->prepare('SELECT id FROM intervenants WHERE slug = ? LIMIT 1');
        $stmt->execute([$slug]);
        $row = $stmt->fetch();

        if ($row) {
            $id = (int)$row['id'];
            $stmt = $pdo->prepare('
                UPDATE intervenants
                SET prenom = COALESCE(?, prenom),
                    nom    = COALESCE(?, nom),
                    texte_cv = ?,
                    updated_at = NOW()
                WHERE id = ?
            ');
            $stmt->execute([$prenom, $nom, $texte, $id]);
            return $id;
        }

        $stmt = $pdo->prepare('
            INSERT INTO intervenants (slug, prenom, nom, texte_cv, updated_at)
            VALUES (?, ?, ?, ?, NOW())
        ');
        $stmt->execute([$slug, $prenom, $nom, $texte]);
        return (int)$pdo->lastInsertId();
    }
}
