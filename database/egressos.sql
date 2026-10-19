-- Tabelas do egresso (cadastro, login e perfil).
-- Rode este arquivo uma vez no MySQL / phpMyAdmin, depois do database.sql.

USE janus;

CREATE TABLE IF NOT EXISTS egressos
(
    id            INT AUTO_INCREMENT PRIMARY KEY,
    nome          VARCHAR(120) NOT NULL,
    email         VARCHAR(150) NOT NULL UNIQUE,
    senha_hash    VARCHAR(255) NOT NULL,
    telefone      VARCHAR(11)  NOT NULL,
    curso         VARCHAR(120) NOT NULL,
    area_atuacao  VARCHAR(120) NOT NULL,
    criado_em     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Texto "Sobre você" do perfil (1 linha por egresso)
CREATE TABLE IF NOT EXISTS perfis_egresso
(
    id            INT AUTO_INCREMENT PRIMARY KEY,
    egresso_id    INT      NOT NULL UNIQUE,
    bio           TEXT     NULL,
    atualizado_em DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (egresso_id) REFERENCES egressos (id) ON DELETE CASCADE
);

-- Soft skills e hard skills do egresso (uma linha por habilidade)
CREATE TABLE IF NOT EXISTS egresso_habilidades
(
    id         INT AUTO_INCREMENT PRIMARY KEY,
    egresso_id INT                   NOT NULL,
    tipo       ENUM ('soft', 'hard') NOT NULL,
    nome       VARCHAR(80)           NOT NULL,
    FOREIGN KEY (egresso_id) REFERENCES egressos (id) ON DELETE CASCADE
);

-- Formações do egresso (uma linha por formação)
CREATE TABLE IF NOT EXISTS egresso_formacoes
(
    id          INT AUTO_INCREMENT PRIMARY KEY,
    egresso_id  INT          NOT NULL,
    curso       VARCHAR(120) NOT NULL,
    instituicao VARCHAR(120) NOT NULL,
    ano         VARCHAR(4)   NULL,
    FOREIGN KEY (egresso_id) REFERENCES egressos (id) ON DELETE CASCADE
);
