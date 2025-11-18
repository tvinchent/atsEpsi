<?php

class CompetenceService
{
    private array $dictionnaire = [
        // =========================
        // Développement Web Frontend
        // =========================
        'javascript'  => ['domaine' => 'Web Frontend', 'sous_domaine' => 'Langage'],
        'typescript'  => ['domaine' => 'Web Frontend', 'sous_domaine' => 'Langage'],
        'html'        => ['domaine' => 'Web Frontend', 'sous_domaine' => 'Markup'],
        'css'         => ['domaine' => 'Web Frontend', 'sous_domaine' => 'Style'],
        'vue.js'      => ['domaine' => 'Web Frontend', 'sous_domaine' => 'Framework'],
        'vuejs'       => ['domaine' => 'Web Frontend', 'sous_domaine' => 'Framework'],
        'react'       => ['domaine' => 'Web Frontend', 'sous_domaine' => 'Framework'],
        'angular'     => ['domaine' => 'Web Frontend', 'sous_domaine' => 'Framework'],
        'sass'        => ['domaine' => 'Web Frontend', 'sous_domaine' => 'Preprocesseur'],
        'tailwind'    => ['domaine' => 'Web Frontend', 'sous_domaine' => 'CSS'],

        // =========================
        // Développement Web Backend
        // =========================
        'php'         => ['domaine' => 'Web Backend', 'sous_domaine' => 'Langage'],
        'symfony'     => ['domaine' => 'Web Backend', 'sous_domaine' => 'Framework'],
        'laravel'     => ['domaine' => 'Web Backend', 'sous_domaine' => 'Framework'],
        'node.js'     => ['domaine' => 'Web Backend', 'sous_domaine' => 'Runtime JS'],
        'nodejs'      => ['domaine' => 'Web Backend', 'sous_domaine' => 'Runtime JS'],
        'python'      => ['domaine' => 'Web Backend', 'sous_domaine' => 'Langage'],
        'django'      => ['domaine' => 'Web Backend', 'sous_domaine' => 'Framework'],
        'flask'       => ['domaine' => 'Web Backend', 'sous_domaine' => 'Framework'],
        'java'        => ['domaine' => 'Web Backend', 'sous_domaine' => 'Langage'],
        'spring'      => ['domaine' => 'Web Backend', 'sous_domaine' => 'Framework'],
        'c#'          => ['domaine' => 'Web Backend', 'sous_domaine' => '.NET'],
        '.net'        => ['domaine' => 'Web Backend', 'sous_domaine' => 'Framework'],
        'asp.net'     => ['domaine' => 'Web Backend', 'sous_domaine' => 'Framework'],
        'nestjs'      => ['domaine' => 'Web Backend', 'sous_domaine' => 'Framework'],

        // =========================
        // Data Engineering / Bases de données
        // =========================
        'mysql'       => ['domaine' => 'Data Engineering', 'sous_domaine' => 'SQL'],
        'postgresql'  => ['domaine' => 'Data Engineering', 'sous_domaine' => 'SQL'],
        'postgres'    => ['domaine' => 'Data Engineering', 'sous_domaine' => 'SQL'],
        'oracle'      => ['domaine' => 'Data Engineering', 'sous_domaine' => 'SQL'],
        'mongodb'     => ['domaine' => 'Data Engineering', 'sous_domaine' => 'NoSQL'],
        'redis'       => ['domaine' => 'Data Engineering', 'sous_domaine' => 'Cache'],
        'sqlserver'   => ['domaine' => 'Data Engineering', 'sous_domaine' => 'SQL'],
        'sql server'  => ['domaine' => 'Data Engineering', 'sous_domaine' => 'SQL'],
        'sql'         => ['domaine' => 'Data Engineering', 'sous_domaine' => 'Langage'],
        'bigquery'    => ['domaine' => 'Data Engineering', 'sous_domaine' => 'Cloud Data'],
        'hadoop'      => ['domaine' => 'Data Engineering', 'sous_domaine' => 'Big Data'],
        'spark'       => ['domaine' => 'Data Engineering', 'sous_domaine' => 'Big Data'],
        'airflow'     => ['domaine' => 'Data Engineering', 'sous_domaine' => 'Orchestration'],

        // =========================
        // Data Science / IA / ML
        // =========================
        'numpy'          => ['domaine' => 'Data Science', 'sous_domaine' => 'Maths'],
        'pandas'         => ['domaine' => 'Data Science', 'sous_domaine' => 'Dataframe'],
        'scikit-learn'   => ['domaine' => 'Machine Learning', 'sous_domaine' => 'ML'],
        'sklearn'        => ['domaine' => 'Machine Learning', 'sous_domaine' => 'ML'],
        'tensorflow'     => ['domaine' => 'Deep Learning', 'sous_domaine' => 'Framework'],
        'pytorch'        => ['domaine' => 'Deep Learning', 'sous_domaine' => 'Framework'],
        'keras'          => ['domaine' => 'Deep Learning', 'sous_domaine' => 'High-level'],
        'transformers'   => ['domaine' => 'IA', 'sous_domaine' => 'NLP'],
        'huggingface'    => ['domaine' => 'IA', 'sous_domaine' => 'NLP'],
        'nlp'            => ['domaine' => 'IA', 'sous_domaine' => 'Traitement du langage'],
        'computer vision'=> ['domaine' => 'IA', 'sous_domaine' => 'Vision'],
        'machine learning'=> ['domaine' => 'IA', 'sous_domaine' => 'ML'],
        'deep learning'  => ['domaine' => 'IA', 'sous_domaine' => 'DL'],

        // =========================
        // Cyber Sécurité
        // =========================
        'pentest'     => ['domaine' => 'Cybersecurity', 'sous_domaine' => 'Test d’intrusion'],
        'owasp'       => ['domaine' => 'Cybersecurity', 'sous_domaine' => 'Standards'],
        'nessus'      => ['domaine' => 'Cybersecurity', 'sous_domaine' => 'Scan'],
        'burp'        => ['domaine' => 'Cybersecurity', 'sous_domaine' => 'Proxy'],
        'kali'        => ['domaine' => 'Cybersecurity', 'sous_domaine' => 'OS'],
        'forensic'    => ['domaine' => 'Cybersecurity', 'sous_domaine' => 'Analyse'],
        'siem'        => ['domaine' => 'Cybersecurity', 'sous_domaine' => 'Logs'],
        'elk'         => ['domaine' => 'Cybersecurity', 'sous_domaine' => 'Monitoring'],
        'soc'         => ['domaine' => 'Cybersecurity', 'sous_domaine' => 'Opérations'],
        'iso 27001'   => ['domaine' => 'Cybersecurity', 'sous_domaine' => 'Standard'],

        // =========================
        // Réseaux & Infrastructure
        // =========================
        'tcp/ip'        => ['domaine' => 'Réseau', 'sous_domaine' => 'Protocoles'],
        'tcp ip'        => ['domaine' => 'Réseau', 'sous_domaine' => 'Protocoles'],
        'dns'           => ['domaine' => 'Réseau', 'sous_domaine' => 'Services'],
        'dhcp'          => ['domaine' => 'Réseau', 'sous_domaine' => 'Services'],
        'cisco'         => ['domaine' => 'Réseau', 'sous_domaine' => 'Matériel'],
        'vpn'           => ['domaine' => 'Réseau', 'sous_domaine' => 'Sécurité'],
        'firewall'      => ['domaine' => 'Réseau', 'sous_domaine' => 'Sécurité'],
        'load balancer' => ['domaine' => 'Réseau', 'sous_domaine' => 'HA'],
        'wifi'          => ['domaine' => 'Réseau', 'sous_domaine' => 'Sans fil'],

        // =========================
        // Cloud & DevOps
        // =========================
        'docker'        => ['domaine' => 'DevOps', 'sous_domaine' => 'Conteneurs'],
        'kubernetes'    => ['domaine' => 'DevOps', 'sous_domaine' => 'Orchestration'],
        'k8s'           => ['domaine' => 'DevOps', 'sous_domaine' => 'Orchestration'],
        'terraform'     => ['domaine' => 'DevOps', 'sous_domaine' => 'Infra as Code'],
        'ansible'       => ['domaine' => 'DevOps', 'sous_domaine' => 'Automatisation'],
        'gitlab ci'     => ['domaine' => 'DevOps', 'sous_domaine' => 'CI/CD'],
        'github actions'=> ['domaine' => 'DevOps', 'sous_domaine' => 'CI/CD'],
        'jenkins'       => ['domaine' => 'DevOps', 'sous_domaine' => 'CI/CD'],
        'aws'           => ['domaine' => 'Cloud', 'sous_domaine' => 'IaaS/PaaS'],
        'amazon web services' => ['domaine' => 'Cloud', 'sous_domaine' => 'IaaS/PaaS'],
        'azure'         => ['domaine' => 'Cloud', 'sous_domaine' => 'IaaS/PaaS'],
        'gcp'           => ['domaine' => 'Cloud', 'sous_domaine' => 'IaaS/PaaS'],
        'google cloud'  => ['domaine' => 'Cloud', 'sous_domaine' => 'IaaS/PaaS'],
        'ci/cd'         => ['domaine' => 'DevOps', 'sous_domaine' => 'Pipeline'],

        // =========================
        // Systèmes
        // =========================
        'linux'         => ['domaine' => 'Système', 'sous_domaine' => 'Administration'],
        'ubuntu'        => ['domaine' => 'Système', 'sous_domaine' => 'Linux'],
        'debian'        => ['domaine' => 'Système', 'sous_domaine' => 'Linux'],
        'centos'        => ['domaine' => 'Système', 'sous_domaine' => 'Linux'],
        'red hat'       => ['domaine' => 'Système', 'sous_domaine' => 'Linux'],
        'windows server'=> ['domaine' => 'Système', 'sous_domaine' => 'Microsoft'],
        'active directory' => ['domaine' => 'Système', 'sous_domaine' => 'Annuaire'],
        'virtualisation'=> ['domaine' => 'Système', 'sous_domaine' => 'Concept'],
        'hyper-v'       => ['domaine' => 'Système', 'sous_domaine' => 'Virtualisation'],
        'vmware'        => ['domaine' => 'Système', 'sous_domaine' => 'Virtualisation'],

        // =========================
        // Gestion de projet
        // =========================
        'agile'         => ['domaine' => 'Gestion de projet', 'sous_domaine' => 'Méthodologie'],
        'scrum'         => ['domaine' => 'Gestion de projet', 'sous_domaine' => 'Méthodologie'],
        'kanban'        => ['domaine' => 'Gestion de projet', 'sous_domaine' => 'Méthodologie'],
        'jira'          => ['domaine' => 'Gestion de projet', 'sous_domaine' => 'Outil'],
        'confluence'    => ['domaine' => 'Gestion de projet', 'sous_domaine' => 'Documentation'],
        'uml'           => ['domaine' => 'Gestion de projet', 'sous_domaine' => 'Modélisation'],
        'bpmn'          => ['domaine' => 'Gestion de projet', 'sous_domaine' => 'Processus'],
        'product owner' => ['domaine' => 'Gestion de projet', 'sous_domaine' => 'Rôle'],
        'scrum master'  => ['domaine' => 'Gestion de projet', 'sous_domaine' => 'Rôle'],

        // =========================
        // Bureautique & Logiciels métiers
        // =========================
        'excel'         => ['domaine' => 'Bureautique', 'sous_domaine' => 'Tableur'],
        'word'          => ['domaine' => 'Bureautique', 'sous_domaine' => 'Texte'],
        'powerpoint'    => ['domaine' => 'Bureautique', 'sous_domaine' => 'Présentation'],
        'sap'           => ['domaine' => 'Logiciels métiers', 'sous_domaine' => 'ERP'],
        'sage'          => ['domaine' => 'Logiciels métiers', 'sous_domaine' => 'Comptabilité'],
        'salesforce'    => ['domaine' => 'Logiciels métiers', 'sous_domaine' => 'CRM'],
    ];

    public function analyzeAndSave(int $intervenantId, string $texte): void
    {
        $pdo = Database::getConnection();
        $texteLower = mb_strtolower($texte);
        $scores = [];

        foreach ($this->dictionnaire as $motCle => $meta) {
            $count = substr_count($texteLower, mb_strtolower($motCle));
            if ($count > 0) {
                // chercher ou créer la compétence
                $stmt = $pdo->prepare('SELECT id FROM competences WHERE nom = ? LIMIT 1');
                $stmt->execute([$motCle]);
                $row = $stmt->fetch();

                if ($row) {
                    $compId = (int)$row['id'];
                } else {
                    $stmtInsert = $pdo->prepare('
                        INSERT INTO competences (nom, domaine, sous_domaine)
                        VALUES (?, ?, ?)
                    ');
                    $stmtInsert->execute([$motCle, $meta['domaine'], $meta['sous_domaine']]);
                    $compId = (int)$pdo->lastInsertId();
                }

                $scores[$compId] = ($scores[$compId] ?? 0) + $count;
            }
        }

        foreach ($scores as $compId => $score) {
            $score = min($score, 10); // normalisation basique

            $stmt = $pdo->prepare('
                INSERT INTO intervenant_competence (intervenant_id, competence_id, score)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE score = VALUES(score)
            ');
            $stmt->execute([$intervenantId, $compId, $score]);
        }
    }
}
