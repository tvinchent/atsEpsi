<?php

require __DIR__ . '/CompetenceService.php';

class ScanService
{
    private string $tikaPath;
    /** @var string[] */
    private array $roots = []; // <= IMPORTANT

    public function __construct()
    {
        $config = require __DIR__ . '/config.php';

        $this->tikaPath = $config['tika_path'] ?? '';
        if (!$this->tikaPath || !file_exists($this->tikaPath)) {
            throw new RuntimeException('Tika introuvable: ' . $this->tikaPath);
        }

        // Liste de chemins (ex: /cv1,/cv2) passée via .env -> CV_PATHS
        $paths = $config['cv_paths'] ?? [];
        if (!is_array($paths)) $paths = [];
        // Ne garder que les dossiers existants
        foreach ($paths as $p) {
            if (is_dir($p)) {
                $this->roots[] = rtrim($p, '/\\');
            }
        }
        if (empty($this->roots)) {
            throw new RuntimeException('Aucun dossier CV valide dans config CV_PATHS.');
        }
    }

    public function scanAll(): array
    {
        $results = [];

        foreach ($this->roots as $root) {

            // 1) Dossiers NOM-prenom
            foreach (scandir($root) as $entry) {
                if ($entry === '.' || $entry === '..') continue;

                $fullPath = $root . DIRECTORY_SEPARATOR . $entry;

                if (is_dir($fullPath)) {
                    $cvFiles = $this->findCvFiles($fullPath);

                    if (empty($cvFiles)) {
                        $results[$entry] = 'Aucun CV trouvé';
                        continue;
                    }

                    $combinedText = '';
                    foreach ($cvFiles as $cv) {
                        $t = $this->extractText($cv);
                        if ($t) $combinedText .= $t . "\n\n";
                    }
                    $combinedText = trim($combinedText);

                    if ($combinedText === '') {
                        $results[$entry] = 'Erreur extraction';
                        continue;
                    }

                    $id = $this->upsertIntervenant($entry, $combinedText);
                    (new CompetenceService())->analyzeAndSave($id, $combinedText);

                    $results[$entry] = 'OK (' . count($cvFiles) . ' fichier(s) CV)';
                }
            }

            // 2) Fichiers CV à la racine
            foreach (scandir($root) as $entry) {
                if ($entry === '.' || $entry === '..') continue;

                $fullPath = $root . DIRECTORY_SEPARATOR . $entry;

                if (is_file($fullPath) && $this->isCvFilename($entry)) {
                    // slug = nom de fichier sans extension
                    $slug = pathinfo($entry, PATHINFO_FILENAME);

                    $text = $this->extractText($fullPath);
                    if (!$text) {
                        $results[$entry] = 'Erreur extraction';
                        continue;
                    }

                    $id = $this->upsertIntervenant($slug, $text);
                    (new CompetenceService())->analyzeAndSave($id, $text);

                    $results[$entry] = 'OK (CV racine)';
                }
            }
        }

        return $results;
    }

    private function isCvFilename(string $filename): bool
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (!in_array($ext, ['pdf', 'doc', 'docx'], true)) return false;
        return stripos($filename, 'cv') !== false; // contient "cv"
    }

    /**
     * Cherche en récursif les CV dans nom-prenom/20.CONTRACTUEL/DOC RH
     */
    private function findCvFiles(string $personDir): array
    {
        $allowedExt = ['pdf', 'doc', 'docx'];
        $files = [];

        if (!is_dir($personDir)) return [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $personDir,
                FilesystemIterator::SKIP_DOTS
            )
        );

        foreach ($iterator as $fileInfo) {
            /** @var SplFileInfo $fileInfo */
            if (!$fileInfo->isFile()) continue;

            $path = $fileInfo->getPathname();
            $filename = $fileInfo->getFilename();
            $ext = strtolower($fileInfo->getExtension());

            if (!in_array($ext, $allowedExt, true)) continue;
            if (stripos($filename, 'cv') === false) continue;

            // exiger les segments de chemin cibles
            $pathLower = str_replace('\\', '/', strtolower($path));
            if (strpos($pathLower, '20.contractuel') === false || strpos($pathLower, 'doc rh') === false) {
                continue;
            }

            $files[] = $path;
        }

        return $files;
    }

    private function extractText(string $filePath): ?string
    {
        $javaBin = 'java';
        $cmd = escapeshellcmd($javaBin)
             . ' -jar ' . escapeshellarg($this->tikaPath)
             . ' -t ' . escapeshellarg($filePath)
             . ' 2>&1';

        $output = shell_exec($cmd);
        if ($output === null) return null;

        // si Tika renvoie une stacktrace, on considère échec
        if (stripos($output, 'Exception in thread') !== false || stripos($output, 'java.net.') !== false) {
            return null;
        }

        $text = trim(preg_replace('/\s+/', ' ', $output));
        return $text === '' ? null : $text;
    }

    private function upsertIntervenant(string $slug, string $texte): int
    {
        $pdo = Database::getConnection();

        // slug = "NOM-prenom avec espaces" ou nom de fichier si CV racine
        $nom = null;
        $prenom = null;

        if (strpos($slug, '-') !== false) {
            [$partNom, $partPrenom] = explode('-', $slug, 2);
            $nom = mb_convert_case(trim(str_replace('_', ' ', $partNom)), MB_CASE_TITLE, 'UTF-8');
            $prenom = mb_convert_case(trim(str_replace('_', ' ', $partPrenom)), MB_CASE_TITLE, 'UTF-8');
        }

        // existe ?
        $stmt = $pdo->prepare('SELECT id FROM intervenants WHERE slug = ? LIMIT 1');
        $stmt->execute([$slug]);
        $row = $stmt->fetch();

        if ($row) {
            $id = (int)$row['id'];
            $stmt = $pdo->prepare('
                UPDATE intervenants
                SET prenom = COALESCE(?, prenom),
                    nom = COALESCE(?, nom),
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
