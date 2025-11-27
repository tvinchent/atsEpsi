# CV-Scanner --- Guide d'installation

Ce projet permet d'extraire automatiquement le texte de CV (PDF, DOC,
DOCX), de classifier les compétences et de stocker les résultats en base
MySQL, via un environnement Docker.

## 🔧 Prérequis

- Windows 10 ou 11\
- **Docker Desktop** installé :\
  https://www.docker.com/products/docker-desktop/

Après installation, ouvrir Docker Desktop au moins **une fois** pour
finaliser la configuration.

Vérification :

    docker info

## 📁 Installation du projet

Copier le dossier complet du projet dans :

    C:\CV-App\

Il doit contenir :

    src/
    docker/
    docker-compose.yml
    lancer_application_cv.bat

## 📂 Configuration des dossiers CV

Les CV doivent être rangés dans OneDrive :

    C:\Users\<NomUtilisateur>\OneDrive\CV

Créer `compose.override.yml` dans `C:\CV-App\` :

```yaml
version: "3.9"
services:
  app:
    volumes:
      - ./src:/var/www/html
      - "C:/Users/<NomUtilisateur>/OneDrive/CV:/cv1"
```

Créer `.env` :

    CV_PATHS=/cv1

## ▶️ Lancement de l'application

Dans PowerShell :

    cd C:\CV-App
    docker compose -f docker-compose.yml -f compose.override.yml up --build -d

Ouvrir :

    http://localhost:8081

## 🗄️ Initialisation de la base de données

    docker compose exec -T db sh -lc "cat << 'SQL' | mysql -uroot -proot cvdb
    CREATE TABLE IF NOT EXISTS intervenants (
      id INT AUTO_INCREMENT PRIMARY KEY,
      slug VARCHAR(255) NOT NULL UNIQUE,
      prenom VARCHAR(100) NULL,
      nom VARCHAR(100) NULL,
      texte_cv MEDIUMTEXT NULL,
      updated_at DATETIME NOT NULL
    );
    CREATE TABLE IF NOT EXISTS competences (
      id INT AUTO_INCREMENT PRIMARY KEY,
      nom VARCHAR(150) NOT NULL,
      domaine VARCHAR(100) NULL,
      sous_domaine VARCHAR(150) NULL,
      UNIQUE KEY uniq_competence (nom)
    );
    CREATE TABLE IF NOT EXISTS intervenant_competence (
      intervenant_id INT NOT NULL,
      competence_id INT NOT NULL,
      score TINYINT NULL,
      PRIMARY KEY (intervenant_id, competence_id)
    );
    SQL"

Vérification :

    docker compose exec -T db mysql -ucvuser -pcvpass -e "SHOW TABLES FROM cvdb;"

## 🖱️ Utilisation quotidienne

### Méthode simple

1.  Ouvrir Docker Desktop\
2.  Lancer :

```{=html}
<!-- -->
```

    C:\CV-App\lancer_application_cv.bat

### Méthode manuelle

    docker compose -f docker-compose.yml -f compose.override.yml up -d

## 🔍 Scan des CV

Cliquer **Scan CV** dans l'application.

## 🛑 Arrêt

    docker compose down
