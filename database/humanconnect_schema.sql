CREATE DATABASE IF NOT EXISTS humanconnect_php53 DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
USE humanconnect_php53;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(160) NOT NULL,
    password_hash CHAR(64) NOT NULL,
    password_salt VARCHAR(64) NOT NULL,
    birth_date DATE DEFAULT NULL,
    gender VARCHAR(30) DEFAULT '',
    city VARCHAR(80) DEFAULT '',
    country VARCHAR(80) DEFAULT '',
    bio TEXT,
    avatar VARCHAR(180) DEFAULT '',
    role VARCHAR(20) NOT NULL DEFAULT 'user',
    is_verified TINYINT(1) NOT NULL DEFAULT 0,
    dm_mode ENUM('open','matches','closed') NOT NULL DEFAULT 'open',
    status ENUM('active','banned','deleted') NOT NULL DEFAULT 'active',
    last_seen DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email),
    KEY idx_users_status (status),
    KEY idx_users_city (city),
    KEY idx_users_last_seen (last_seen)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS profile_preferences (
    user_id INT UNSIGNED NOT NULL,
    looking_for VARCHAR(30) NOT NULL DEFAULT 'all',
    min_age INT NOT NULL DEFAULT 18,
    max_age INT NOT NULL DEFAULT 80,
    city VARCHAR(80) DEFAULT '',
    verified_only TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (user_id),
    CONSTRAINT fk_pref_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS identity_verifications (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    selfie_path VARCHAR(180) DEFAULT '',
    notes TEXT,
    created_at DATETIME NOT NULL,
    reviewed_at DATETIME DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_verifications_user (user_id),
    CONSTRAINT fk_verification_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS swipes (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    swiper_id INT UNSIGNED NOT NULL,
    target_id INT UNSIGNED NOT NULL,
    action ENUM('like','pass') NOT NULL,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_swipe_pair (swiper_id, target_id),
    KEY idx_swipes_target (target_id),
    CONSTRAINT fk_swipe_swiper FOREIGN KEY (swiper_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_swipe_target FOREIGN KEY (target_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS matches (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_one_id INT UNSIGNED NOT NULL,
    user_two_id INT UNSIGNED NOT NULL,
    status ENUM('active','closed') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_match_pair (user_one_id, user_two_id),
    KEY idx_matches_two (user_two_id),
    CONSTRAINT fk_match_one FOREIGN KEY (user_one_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_match_two FOREIGN KEY (user_two_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS posts (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    body TEXT NOT NULL,
    image_path VARCHAR(180) DEFAULT '',
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_posts_user (user_id),
    KEY idx_posts_created (created_at),
    CONSTRAINT fk_posts_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS post_comments (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    post_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    body VARCHAR(500) NOT NULL,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_comments_post (post_id),
    KEY idx_comments_user (user_id),
    CONSTRAINT fk_comment_post FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    CONSTRAINT fk_comment_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS post_reactions (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    post_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    reaction VARCHAR(30) NOT NULL DEFAULT 'like',
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_post_reaction (post_id, user_id),
    CONSTRAINT fk_reaction_post FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    CONSTRAINT fk_reaction_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS conversations (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    type ENUM('match','free_dm','feed_reply','support') NOT NULL DEFAULT 'free_dm',
    user_one_id INT UNSIGNED NOT NULL,
    user_two_id INT UNSIGNED NOT NULL,
    match_id INT UNSIGNED DEFAULT NULL,
    post_id INT UNSIGNED DEFAULT NULL,
    created_by INT UNSIGNED NOT NULL,
    status ENUM('active','blocked','closed') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_conversations_users (user_one_id, user_two_id),
    KEY idx_conversations_updated (updated_at),
    KEY idx_conversations_match (match_id),
    CONSTRAINT fk_conv_one FOREIGN KEY (user_one_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_conv_two FOREIGN KEY (user_two_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_conv_creator FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_conv_match FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE SET NULL,
    CONSTRAINT fk_conv_post FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS messages (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    conversation_id INT UNSIGNED NOT NULL,
    sender_id INT UNSIGNED NOT NULL,
    body TEXT NOT NULL,
    attachment_path VARCHAR(180) DEFAULT '',
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    is_edited TINYINT(1) NOT NULL DEFAULT 0,
    is_deleted TINYINT(1) NOT NULL DEFAULT 0,
    is_reported TINYINT(1) NOT NULL DEFAULT 0,
    status VARCHAR(30) NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL,
    updated_at DATETIME DEFAULT NULL,
    edited_at DATETIME DEFAULT NULL,
    deleted_at DATETIME DEFAULT NULL,
    deleted_by INT UNSIGNED DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_messages_conversation (conversation_id, id),
    KEY idx_messages_sender (sender_id),
    CONSTRAINT fk_message_conversation FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    CONSTRAINT fk_message_sender FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS message_reactions (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    message_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    reaction VARCHAR(30) NOT NULL,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_message_reaction (message_id, user_id, reaction),
    CONSTRAINT fk_msg_reaction_message FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE,
    CONSTRAINT fk_msg_reaction_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS blocks (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    blocker_id INT UNSIGNED NOT NULL,
    blocked_id INT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_block_pair (blocker_id, blocked_id),
    CONSTRAINT fk_block_blocker FOREIGN KEY (blocker_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_block_blocked FOREIGN KEY (blocked_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS reports (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    reporter_id INT UNSIGNED NOT NULL,
    reported_id INT UNSIGNED NOT NULL,
    post_id INT UNSIGNED DEFAULT NULL,
    message_id INT UNSIGNED DEFAULT NULL,
    conversation_id INT UNSIGNED DEFAULT NULL,
    reason VARCHAR(180) NOT NULL,
    details TEXT,
    snapshot_body TEXT,
    status ENUM('open','reviewed','dismissed','actioned') NOT NULL DEFAULT 'open',
    created_at DATETIME NOT NULL,
    reviewed_by INT UNSIGNED DEFAULT NULL,
    reviewed_at DATETIME DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_reports_status (status),
    CONSTRAINT fk_report_reporter FOREIGN KEY (reporter_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_report_reported FOREIGN KEY (reported_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_report_post FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS dm_limits (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    target_id INT UNSIGNED NOT NULL,
    conversation_id INT UNSIGNED NOT NULL,
    day_date DATE NOT NULL,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_dm_limit_user_day (user_id, day_date),
    CONSTRAINT fk_dm_limit_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_dm_limit_target FOREIGN KEY (target_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_dm_limit_conversation FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
