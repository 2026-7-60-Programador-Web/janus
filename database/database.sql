CREATE DATABASE IF NOT EXISTS janus CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

USE janus;

DROP TABLE IF EXISTS usuarios;

CREATE TABLE usuarios
(
    id            INT AUTO_INCREMENT PRIMARY KEY,
    nome          VARCHAR(120) NOT NULL,
    email         VARCHAR(150) NOT NULL UNIQUE,
    senha         VARCHAR(255) NOT NULL,
    tipo          ENUM('administrador','egresso') NOT NULL DEFAULT 'egresso',
    curso         VARCHAR(150) NULL,
    area_atuacao  VARCHAR(100) NULL,
    habilidades   VARCHAR(300) NULL,
    telefone      VARCHAR(11)  NULL,
    criado_em     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Usuários de teste (senha de todos: 12345abc)
INSERT INTO usuarios (nome, email, senha, tipo, curso, area_atuacao, habilidades, telefone) VALUES
('Cauan Carvalho', 'cauan@email.com', '12345abc', 'administrador', 'Desenvolvimento de Sistemas', 'Desenvolvimento Web', 'PHP, HTML, CSS, JavaScript', '99991111111');