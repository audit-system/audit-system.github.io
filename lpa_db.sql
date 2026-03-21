-- ================================================
-- LPA System - Hirschmann Automotive Oujda
-- Importer : mysql -u root -p < lpa_db.sql
-- ================================================

CREATE DATABASE IF NOT EXISTS lpa_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE lpa_db;

CREATE TABLE users (
    id        INT AUTO_INCREMENT PRIMARY KEY,
    username  VARCHAR(50)  NOT NULL UNIQUE,
    password  VARCHAR(50)  NOT NULL,
    nom       VARCHAR(100) NOT NULL,
    niveau    TINYINT      NOT NULL,  -- 1, 2 ou 3
    role      VARCHAR(80)  NOT NULL,
    zone      VARCHAR(80)  NOT NULL DEFAULT 'Tous'
);

CREATE TABLE soumissions (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    date_audit    DATE         NOT NULL,
    date_saisie   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    niveau        TINYINT      NOT NULL,
    username      VARCHAR(50)  NOT NULL,
    nom_auditeur  VARCHAR(100) NOT NULL,
    zone          VARCHAR(80),
    shift         VARCHAR(40),
    semaine       VARCHAR(20),
    mois          VARCHAR(30),
    reponses      TEXT         NOT NULL,
    observations  TEXT,
    conformes     SMALLINT     NOT NULL DEFAULT 0,
    non_conformes SMALLINT     NOT NULL DEFAULT 0,
    score         TINYINT      NOT NULL DEFAULT 0,
    INDEX idx_niveau   (niveau),
    INDEX idx_username (username),
    INDEX idx_date     (date_audit DESC)
);

-- Utilisateurs
INSERT INTO users (username, password, nom, niveau, role, zone) VALUES
('shift_leader_1',   'sl1pass',   'Youssef Alami',        3, 'Shift Leader',         'Zone A'),
('shift_leader_2',   'sl2pass',   'Mehdi Ouali',          3, 'Shift Leader',         'Zone B'),
('segment_leader_1', 'segl1pass', 'Karima Benali',        2, 'Segment Leader',       'Segment 1'),
('segment_leader_2', 'segl2pass', 'Hassan Rachidi',       2, 'Segment Leader',       'Segment 2'),
('directeur_prod',   'dirpass',   'Directeur Production', 1, 'Directeur Production', 'Tous');

-- Données de démo
INSERT INTO soumissions (date_audit, niveau, username, nom_auditeur, zone, shift, reponses, observations, conformes, non_conformes, score) VALUES
('2026-03-01', 3, 'shift_leader_1',   'Youssef Alami',        'Zone A',    'Shift Bleu',
 '{"0":"O","1":"O","2":"X","3":"O","4":"O","5":"O","6":"O","7":"X","8":"O","9":"O","10":"O","11":"O","12":"O","13":"X"}',
 'RAS', 11, 3, 79),
('2026-03-03', 2, 'segment_leader_1', 'Karima Benali',        'Segment 1', NULL,
 '{"0":"O","1":"O","2":"O","3":"O","4":"O","5":"O","6":"O","7":"X","8":"O","9":"O","10":"O","11":"O","12":"X","13":"O"}',
 'MAP à revoir', 12, 2, 86),
('2026-03-05', 1, 'directeur_prod',   'Directeur Production', 'Tous',      NULL,
 '{"0":"O","1":"O","2":"O","3":"O","4":"O","5":"O","6":"O","7":"X","8":"O","9":"O","10":"O","11":"O","12":"O","13":"O","14":"O","15":"O","16":"X"}',
 'Escalade OK', 15, 2, 88),
('2026-03-07', 3, 'shift_leader_1',   'Youssef Alami',        'Zone A',    'Shift Vert',
 '{"0":"O","1":"X","2":"O","3":"O","4":"O","5":"X","6":"O","7":"X","8":"O","9":"O","10":"O","11":"O","12":"X","13":"O"}',
 '5S insuffisant', 10, 4, 71);
