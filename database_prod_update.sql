-- Mise en production PA / KAESKIN
-- A lancer sur la base de production apres sauvegarde.

-- 1. Produits du shop
CREATE TABLE IF NOT EXISTS PRODUIT (
    id_produit INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(150) NULL,
    prix DECIMAL(10,2) NULL,
    stock INT NULL
);

ALTER TABLE PRODUIT
    ADD COLUMN IF NOT EXISTS nom VARCHAR(150) NULL,
    ADD COLUMN IF NOT EXISTS type_produit VARCHAR(100) NULL,
    ADD COLUMN IF NOT EXISTS subtitle VARCHAR(255) NULL,
    ADD COLUMN IF NOT EXISTS description TEXT NULL,
    ADD COLUMN IF NOT EXISTS benefits VARCHAR(255) NULL,
    ADD COLUMN IF NOT EXISTS usage_text VARCHAR(255) NULL,
    ADD COLUMN IF NOT EXISTS badge VARCHAR(100) NULL,
    ADD COLUMN IF NOT EXISTS image VARCHAR(255) NULL;

CREATE UNIQUE INDEX IF NOT EXISTS unique_nom ON PRODUIT (nom);

INSERT INTO PRODUIT (nom, type_produit, prix, stock, subtitle, description, benefits, usage_text, badge, image) VALUES
('Velvet Cream', 'Crème hydratante', 36, 50, 'Hydratation souple et fini velouté.', 'Une crème confortable pour nourrir la peau au quotidien et lui redonner douceur et équilibre.', 'Confort immédiat, peau souple, routine quotidienne.', 'Appliquer matin et soir sur peau propre.', 'Iconique', '../assets/images/shop/hd-cream.jpg'),
('Silk Cleanser', 'Gel nettoyant', 28, 50, 'Nettoyage doux pour une peau fraîche et nette.', 'Ce nettoyant élimine les impuretés sans agresser la peau et prépare idéalement la suite de la routine.', 'Peau nette, toucher doux, confort après rinçage.', 'Masser sur peau humide puis rincer à l’eau tiède.', 'Essentiel', '../assets/images/shop/hd-cleanser.jpg'),
('Glow Ritual', 'Soin éclat', 42, 50, 'Un soin lumineux pour raviver le teint.', 'Formulé pour réveiller l’éclat naturel de la peau, ce soin accompagne les teints ternes et fatigués.', 'Teint plus lumineux, grain de peau visuellement lissé.', 'Appliquer en fine couche avant la crème de jour.', 'Best-seller', '../assets/images/shop/hd-glow.jpg'),
('Pure Balance', 'Sérum équilibrant', 39, 50, 'Texture légère pour une peau plus harmonieuse.', 'Un sérum pensé pour aider à équilibrer la peau et apporter une sensation de fraîcheur durable.', 'Équilibre, confort, fini léger.', 'Déposer quelques gouttes avant votre crème.', 'Routine jour', '../assets/images/shop/hd-balance.jpg'),
('Soft Veil', 'Crème cocon', 44, 50, 'Une formule enveloppante pour les peaux en quête de confort.', 'Sa texture riche procure un effet cocon et aide la peau à conserver sa souplesse tout au long de la journée.', 'Nutrition, souplesse, sensation apaisante.', 'Appliquer sur le visage et le cou selon les besoins.', 'Peaux sèches', '../assets/images/shop/hd-soft-veil.jpg'),
('Zen Drop', 'Huile soin', 48, 50, 'Un rituel nourrissant pour terminer la routine.', 'Cette huile soin apporte une sensation de confort et laisse la peau plus souple au réveil.', 'Nutrition, éclat, confort nocturne.', 'Réchauffer quelques gouttes entre les mains puis masser.', 'Rituel nuit', '../assets/images/shop/hd-zen-drop.jpg')
ON DUPLICATE KEY UPDATE
    type_produit = VALUES(type_produit),
    prix = VALUES(prix),
    stock = VALUES(stock),
    subtitle = VALUES(subtitle),
    description = VALUES(description),
    benefits = VALUES(benefits),
    usage_text = VALUES(usage_text),
    badge = VALUES(badge),
    image = VALUES(image);

-- 2. Rendez-vous
CREATE TABLE IF NOT EXISTS RENDEZ_VOUS (
    id_rdv INT AUTO_INCREMENT PRIMARY KEY,
    id_client INT NOT NULL,
    id_expert INT NOT NULL,
    service VARCHAR(120) NOT NULL,
    date_rdv DATE NOT NULL,
    heure TIME NOT NULL,
    duree VARCHAR(30) NOT NULL,
    prix DECIMAL(10,2) NOT NULL,
    mode_paiement VARCHAR(30) NOT NULL,
    statut VARCHAR(30) NOT NULL DEFAULT 'confirme',
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX index_creneau_expert (id_expert, date_rdv, heure),
    CONSTRAINT fk_rdv_client FOREIGN KEY (id_client) REFERENCES UTILISATEUR(id_user) ON DELETE CASCADE,
    CONSTRAINT fk_rdv_expert FOREIGN KEY (id_expert) REFERENCES UTILISATEUR(id_user) ON DELETE CASCADE
);

-- Si une ancienne version a cree un index unique sur les creneaux,
-- il faut le remplacer par un index simple pour permettre de reserver
-- a nouveau un creneau apres annulation.
CREATE INDEX IF NOT EXISTS index_creneau_expert ON RENDEZ_VOUS (id_expert, date_rdv, heure);
DROP INDEX IF EXISTS unique_creneau_expert ON RENDEZ_VOUS;

-- 3. Chat client/expert
CREATE TABLE IF NOT EXISTS MESSAGE_CHAT (
    id_message INT AUTO_INCREMENT PRIMARY KEY,
    id_rdv INT NOT NULL,
    id_expediteur INT NOT NULL,
    id_destinataire INT NOT NULL,
    message TEXT NOT NULL,
    date_message DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_message_rdv FOREIGN KEY (id_rdv) REFERENCES RENDEZ_VOUS(id_rdv) ON DELETE CASCADE,
    CONSTRAINT fk_message_expediteur FOREIGN KEY (id_expediteur) REFERENCES UTILISATEUR(id_user) ON DELETE CASCADE,
    CONSTRAINT fk_message_destinataire FOREIGN KEY (id_destinataire) REFERENCES UTILISATEUR(id_user) ON DELETE CASCADE
);

-- 4. Likes et commentaires du blog
CREATE TABLE IF NOT EXISTS BLOG_LIKE (
    id_like INT AUTO_INCREMENT PRIMARY KEY,
    article_slug VARCHAR(150) NOT NULL,
    id_user INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_blog_like (article_slug, id_user),
    CONSTRAINT fk_blog_like_user FOREIGN KEY (id_user) REFERENCES UTILISATEUR(id_user) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS BLOG_COMMENT (
    id_comment INT AUTO_INCREMENT PRIMARY KEY,
    article_slug VARCHAR(150) NOT NULL,
    id_user INT NOT NULL,
    content TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_blog_comment_user FOREIGN KEY (id_user) REFERENCES UTILISATEUR(id_user) ON DELETE CASCADE
);

-- 5. Comptes experts
-- Le role expert utilise la colonne UTILISATEUR.role deja existante.
-- Exemple a adapter en prod si besoin :
-- UPDATE UTILISATEUR SET role = 'expert' WHERE email = 'expert@example.com';
