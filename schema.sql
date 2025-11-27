CREATE TABLE
IF NOT EXISTS intervenants
(
  id INT AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR
(255) NOT NULL UNIQUE,
  prenom VARCHAR
(100) NULL,
  nom VARCHAR
(100) NULL,
  texte_cv MEDIUMTEXT NULL,
  updated_at DATETIME NOT NULL
);

CREATE TABLE
IF NOT EXISTS competences
(
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR
(150) NOT NULL,
  domaine VARCHAR
(100) NULL,
  sous_domaine VARCHAR
(150) NULL,
  UNIQUE KEY uniq_competence
(nom)
);

CREATE TABLE
IF NOT EXISTS intervenant_competence
(
  intervenant_id INT NOT NULL,
  competence_id INT NOT NULL,
  score TINYINT NULL,
  PRIMARY KEY
(intervenant_id, competence_id),
  CONSTRAINT fk_ic_i FOREIGN KEY
(intervenant_id) REFERENCES intervenants
(id) ON
DELETE CASCADE,
  CONSTRAINT fk_ic_c FOREIGN KEY
(competence_id) REFERENCES competences
(id) ON
DELETE CASCADE
);
