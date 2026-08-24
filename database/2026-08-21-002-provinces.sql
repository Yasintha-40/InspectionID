CREATE TABLE IF NOT EXISTS provinces (
  id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  display_order TINYINT UNSIGNED NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6),
  PRIMARY KEY (id),
  UNIQUE KEY uq_provinces_name (name),
  KEY ix_provinces_active_order (is_active, display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO provinces (name, display_order) VALUES
  ('Western', 1), ('Central', 2), ('Southern', 3),
  ('Northern', 4), ('Eastern', 5), ('North Western', 6),
  ('North Central', 7), ('Uva', 8), ('Sabaragamuwa', 9)
ON DUPLICATE KEY UPDATE display_order = VALUES(display_order), is_active = 1;

INSERT INTO schema_migrations (version, description)
VALUES ('2026-08-21-002', 'Add database-managed Sri Lankan provinces')
ON DUPLICATE KEY UPDATE description = VALUES(description);
