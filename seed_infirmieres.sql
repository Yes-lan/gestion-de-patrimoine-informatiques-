-- Création de la table infirmiere si elle n'existe pas
CREATE TABLE IF NOT EXISTS infirmiere (
    id INT AUTO_INCREMENT NOT NULL,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(180) NOT NULL,
    password VARCHAR(255) NOT NULL,
    UNIQUE INDEX UNIQ_D438839FE7927C74 (email),
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;

-- Insertion des données de test
-- Mot de passe pour toutes: Test1234!
INSERT INTO infirmiere (nom, prenom, email, password) VALUES
('Martin', 'Claire', 'claire.martin.infirmiere@test.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Bernard', 'Sophie', 'sophie.bernard.infirmiere@test.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Dubois', 'Nadia', 'nadia.dubois.infirmiere@test.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Roux', 'Lea', 'lea.roux.infirmiere@test.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Petit', 'Camille', 'camille.petit.infirmiere@test.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi')
ON DUPLICATE KEY UPDATE nom=VALUES(nom);
