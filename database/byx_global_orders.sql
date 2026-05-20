CREATE TABLE IF NOT EXISTS lf_orders (
    id INT NOT NULL AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    referencia VARCHAR(120) NOT NULL,
    tipo VARCHAR(60) NOT NULL,
    item_id INT DEFAULT NULL,
    descricao VARCHAR(255) NOT NULL,
    valor_byx DECIMAL(20,8) NOT NULL DEFAULT 0.00000000,
    valor_reais DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    status VARCHAR(30) NOT NULL DEFAULT 'pending',
    payment_provider VARCHAR(60) DEFAULT NULL,
    payment_reference VARCHAR(160) DEFAULT NULL,
    metadata TEXT,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_lf_orders_referencia (referencia),
    KEY idx_lf_orders_usuario (usuario_id),
    KEY idx_lf_orders_status (status),
    KEY idx_lf_orders_tipo (tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS lf_order_logs (
    id INT NOT NULL AUTO_INCREMENT,
    order_id INT NOT NULL,
    usuario_id INT NOT NULL,
    status VARCHAR(30) NOT NULL,
    observacao TEXT,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_lf_order_logs_order (order_id),
    KEY idx_lf_order_logs_usuario (usuario_id),
    KEY idx_lf_order_logs_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
