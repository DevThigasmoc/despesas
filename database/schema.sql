CREATE DATABASE IF NOT EXISTS despesas_viagem CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE despesas_viagem;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS advances (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    note VARCHAR(255) NULL,
    user_id INT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_advances_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS expenses (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    note VARCHAR(255) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    receipt_path VARCHAR(255) NOT NULL,
    receipt_mime VARCHAR(100) NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_expenses_category FOREIGN KEY (category_id) REFERENCES categories(id),
    CONSTRAINT fk_expenses_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO users (email, password_hash)
VALUES ('admin@exemplo.com', '$2y$12$C2lr39j3jmT9VvNEFX0HxuUKK4DQaPqjkfYw6K8oZjKInWgWSvbiC')
ON DUPLICATE KEY UPDATE email = email;

INSERT INTO categories (name)
VALUES
('Alimentação'),
('Transporte'),
('Combustível'),
('Hospedagem'),
('Pedágio'),
('Outros')
ON DUPLICATE KEY UPDATE name = VALUES(name);
