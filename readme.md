# TODO

MAJ chemin cf arbo

# Install openJDK et Tika

curl -L -o tika-app.jar https://archive.apache.org/dist/tika/2.9.2/tika-app-2.9.2.jar
brew install openjdk
java -jar tika-app.jar --version


# Schema BDD

CREATE TABLE intervenants (
  id INT AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(255) NOT NULL UNIQUE,  -- ex: "jean-dupont" = nom du dossier
  prenom VARCHAR(100) NULL,
  nom VARCHAR(100) NULL,
  texte_cv MEDIUMTEXT NULL,
  updated_at DATETIME NOT NULL
);

CREATE TABLE competences (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(150) NOT NULL,
  domaine VARCHAR(100) NULL,
  sous_domaine VARCHAR(150) NULL,
  UNIQUE KEY uniq_competence (nom)
);

CREATE TABLE intervenant_competence (
  intervenant_id INT NOT NULL,
  competence_id INT NOT NULL,
  score TINYINT NULL,
  PRIMARY KEY (intervenant_id, competence_id),
  FOREIGN KEY (intervenant_id) REFERENCES intervenants(id) ON DELETE CASCADE,
  FOREIGN KEY (competence_id) REFERENCES competences(id) ON DELETE CASCADE
);
