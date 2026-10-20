-- Janus - tabela de vagas
-- Campos correspondentes ao formulário views/cadastro_vagas.php
-- Selecione o banco do Janus antes de executar (phpMyAdmin > banco > aba SQL).

CREATE TABLE IF NOT EXISTS vagas (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    -- Informações básicas
    titulo              VARCHAR(120)  NOT NULL,
    quantidade          SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    empresa             VARCHAR(120)  NOT NULL,
    area_atuacao        VARCHAR(80)   NOT NULL,

    -- Contrato e local
    tipo_contrato       VARCHAR(30)   NOT NULL,
    nivel               VARCHAR(20)   NOT NULL,
    modalidade          VARCHAR(20)   NOT NULL,
    cidade              VARCHAR(80)   NULL,
    uf                  CHAR(2)       NULL,

    -- Remuneração
    salario_min         DECIMAL(10,2) NULL,
    salario_max         DECIMAL(10,2) NULL,
    salario_a_combinar  TINYINT(1)    NOT NULL DEFAULT 0,

    -- Detalhes
    descricao           TEXT          NOT NULL,
    requisitos          TEXT          NULL,
    beneficios          TEXT          NULL,

    -- Contato e prazo
    email_contato       VARCHAR(150)  NOT NULL,
    data_limite         DATE          NULL,

    -- Controle
    status              ENUM('aberta','encerrada') NOT NULL DEFAULT 'aberta',
    criado_em           TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_area_status (area_atuacao, status),
    INDEX idx_prazo (data_limite)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;