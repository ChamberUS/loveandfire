CREATE TABLE byx_payment_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    loja_id INT NOT NULL,
    byx_request_id VARCHAR(64) NULL,
    amount_microbyx BIGINT NOT NULL,
    memo VARCHAR(255) NULL,
    status VARCHAR(64) NOT NULL DEFAULT 'created',
    txhash VARCHAR(128) NULL,
    raw_response MEDIUMTEXT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NULL
);
