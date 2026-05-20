CREATE TABLE IF NOT EXISTS lf_diamond_orders (
    id INT NOT NULL AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    pacote VARCHAR(100) NOT NULL,
    diamantes INT NOT NULL,
    bonus INT NOT NULL DEFAULT 0,
    total_diamantes INT NOT NULL,
    total_byx DECIMAL(20,8) NOT NULL DEFAULT 0.00000000,
    valor_reais DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    status VARCHAR(30) NOT NULL DEFAULT 'pending',
    payment_provider VARCHAR(50) DEFAULT NULL,
    payment_reference VARCHAR(120) DEFAULT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_lf_diamond_orders_usuario (usuario_id),
    KEY idx_lf_diamond_orders_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
