ALTER TABLE iplm_periods ADD COLUMN IF NOT EXISTS population_mode ENUM('auto','manual') NOT NULL DEFAULT 'auto';
ALTER TABLE iplm_periods ADD COLUMN IF NOT EXISTS population_registry_count INT UNSIGNED NULL;
