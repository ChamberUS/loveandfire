CREATE TABLE IF NOT EXISTS lf_wallets (
    id INT NOT NULL AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    saldo_byx DECIMAL(18,8) NOT NULL DEFAULT 0.00000000,
    saldo_bloqueado DECIMAL(18,8) NOT NULL DEFAULT 0.00000000,
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_lf_wallets_usuario (usuario_id),
    KEY idx_lf_wallets_usuario (usuario_id),
    KEY idx_lf_wallets_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS lf_wallet_transacoes (
    id INT NOT NULL AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    tipo VARCHAR(40) NOT NULL,
    direcao VARCHAR(10) NOT NULL,
    valor DECIMAL(18,8) NOT NULL,
    saldo_antes DECIMAL(18,8) NOT NULL DEFAULT 0.00000000,
    saldo_depois DECIMAL(18,8) NOT NULL DEFAULT 0.00000000,
    referencia_tipo VARCHAR(60) DEFAULT NULL,
    referencia_id INT DEFAULT NULL,
    descricao VARCHAR(255) DEFAULT NULL,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_lf_wallet_transacoes_usuario (usuario_id),
    KEY idx_lf_wallet_transacoes_tipo (tipo),
    KEY idx_lf_wallet_transacoes_referencia_tipo (referencia_tipo),
    KEY idx_lf_wallet_transacoes_referencia_id (referencia_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS lf_pet_blocks (
    id INT NOT NULL AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    mundo VARCHAR(30) NOT NULL DEFAULT 'pets',
    codigo_publico VARCHAR(80) NOT NULL,
    valor_inicial DECIMAL(18,8) NOT NULL DEFAULT 10.00000000,
    valor_atual DECIMAL(18,8) NOT NULL DEFAULT 10.00000000,
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_lf_pet_blocks_usuario_mundo (usuario_id, mundo),
    KEY idx_lf_pet_blocks_usuario (usuario_id),
    KEY idx_lf_pet_blocks_mundo (mundo),
    KEY idx_lf_pet_blocks_status (status),
    KEY idx_lf_pet_blocks_valor_atual (valor_atual)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS lf_pet_ownership (
    id INT NOT NULL AUTO_INCREMENT,
    pet_block_id INT NOT NULL,
    usuario_original_id INT NOT NULL,
    dono_atual_id INT DEFAULT NULL,
    valor_atual DECIMAL(18,8) NOT NULL DEFAULT 10.00000000,
    ultima_compra_at DATETIME DEFAULT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_lf_pet_ownership_pet_block (pet_block_id),
    KEY idx_lf_pet_ownership_original (usuario_original_id),
    KEY idx_lf_pet_ownership_dono_atual (dono_atual_id),
    KEY idx_lf_pet_ownership_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS lf_pet_transacoes (
    id INT NOT NULL AUTO_INCREMENT,
    pet_block_id INT NOT NULL,
    usuario_original_id INT NOT NULL,
    comprador_id INT NOT NULL,
    vendedor_id INT DEFAULT NULL,
    valor_anterior DECIMAL(18,8) NOT NULL DEFAULT 0.00000000,
    valor_compra DECIMAL(18,8) NOT NULL DEFAULT 0.00000000,
    novo_valor DECIMAL(18,8) NOT NULL DEFAULT 0.00000000,
    lucro_vendedor DECIMAL(18,8) NOT NULL DEFAULT 0.00000000,
    bonus_usuario_original DECIMAL(18,8) NOT NULL DEFAULT 0.00000000,
    taxa_plataforma DECIMAL(18,8) NOT NULL DEFAULT 0.00000000,
    taxa_reserva DECIMAL(18,8) NOT NULL DEFAULT 0.00000000,
    percentual_aumento DECIMAL(8,4) NOT NULL DEFAULT 0.0000,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_lf_pet_transacoes_pet_block (pet_block_id),
    KEY idx_lf_pet_transacoes_comprador (comprador_id),
    KEY idx_lf_pet_transacoes_vendedor (vendedor_id),
    KEY idx_lf_pet_transacoes_original (usuario_original_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS lf_pet_config (
    id INT NOT NULL AUTO_INCREMENT,
    chave VARCHAR(80) NOT NULL,
    valor VARCHAR(120) NOT NULL,
    descricao VARCHAR(255) DEFAULT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_lf_pet_config_chave (chave)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO lf_pet_config (chave, valor, descricao, updated_at)
VALUES
('valor_inicial_pet', '10.00000000', 'Valor inicial base por pet/card', NOW()),
('taxa_dono_original', '3', 'Percentual pago ao dono real do perfil', NOW()),
('taxa_plataforma', '5', 'Percentual da plataforma', NOW()),
('taxa_reserva', '1', 'Percentual de reserva/queima simbolica', NOW()),
('percentual_aumento_baixo', '3', 'Aumento para valores <= 100 BYX', NOW()),
('percentual_aumento_medio', '2', 'Aumento para valores <= 1000 BYX', NOW()),
('percentual_aumento_alto', '1', 'Aumento para valores <= 10000 BYX', NOW()),
('percentual_aumento_milionario', '0.25', 'Aumento para valores acima de 1000000 BYX', NOW())
ON DUPLICATE KEY UPDATE valor = VALUES(valor), descricao = VALUES(descricao), updated_at = VALUES(updated_at);

CREATE TABLE IF NOT EXISTS lf_pet_notificacoes (
    id INT NOT NULL AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    titulo VARCHAR(160) NOT NULL,
    mensagem TEXT,
    link VARCHAR(255) DEFAULT NULL,
    lida TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_lf_pet_notificacoes_usuario (usuario_id),
    KEY idx_lf_pet_notificacoes_tipo (tipo),
    KEY idx_lf_pet_notificacoes_lida (lida)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
