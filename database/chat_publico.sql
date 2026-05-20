CREATE TABLE IF NOT EXISTS chat_salas (
  id INT(11) NOT NULL AUTO_INCREMENT,
  nome VARCHAR(100) NOT NULL,
  slug VARCHAR(100) NOT NULL,
  icone VARCHAR(50) DEFAULT NULL,
  status TINYINT(1) NOT NULL DEFAULT 1,
  criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS chat_visitantes (
  id INT(11) NOT NULL AUTO_INCREMENT,
  sala_id INT(11) NOT NULL,
  nome_temp VARCHAR(80) NOT NULL,
  token VARCHAR(80) NOT NULL,
  ip VARCHAR(45) DEFAULT NULL,
  user_agent VARCHAR(255) DEFAULT NULL,
  entrou_em DATETIME DEFAULT CURRENT_TIMESTAMP,
  last_activity DATETIME DEFAULT CURRENT_TIMESTAMP,
  status TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  UNIQUE KEY token (token),
  KEY sala_id (sala_id),
  KEY last_activity (last_activity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS chat_mensagens (
  id INT(11) NOT NULL AUTO_INCREMENT,
  sala_id INT(11) NOT NULL,
  visitante_id INT(11) DEFAULT NULL,
  nome_temp VARCHAR(80) NOT NULL,
  mensagem TEXT NOT NULL,
  criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
  status TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  KEY sala_id (sala_id),
  KEY visitante_id (visitante_id),
  KEY criado_em (criado_em)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT IGNORE INTO chat_salas (nome, slug, icone, status) VALUES
('Geral Love', 'geral-love', 'fire', 1),
('Amizades', 'amizades', 'comment', 1),
('LGBTQIA+', 'lgbtqia', 'rainbow', 1),
('Café e Papo', 'cafe-e-papo', 'coffee', 1);
