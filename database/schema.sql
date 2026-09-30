CREATE TABLE vehicle_types (
    id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE vehicles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    model_name VARCHAR(120) NOT NULL,
    type_id TINYINT UNSIGNED NOT NULL,
    doors TINYINT UNSIGNED NOT NULL,
    transmission ENUM('manual', 'automatic') NOT NULL,
    fuel ENUM('petrol', 'diesel', 'hybrid', 'electric') NOT NULL,
    price DECIMAL(10, 2) UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT chk_vehicles_doors CHECK (doors BETWEEN 1 AND 255),
    CONSTRAINT fk_vehicles_type
        FOREIGN KEY (type_id) REFERENCES vehicle_types(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    INDEX idx_vehicles_model_name (model_name),
    INDEX idx_vehicles_transmission (transmission),
    INDEX idx_vehicles_price (price)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
