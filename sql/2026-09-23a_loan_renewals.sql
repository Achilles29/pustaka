ALTER TABLE loan_transaction_items
    ADD COLUMN IF NOT EXISTS local_due_date DATETIME NULL AFTER due_date,
    ADD COLUMN IF NOT EXISTS renewal_count INT NOT NULL DEFAULT 0 AFTER local_due_date,
    ADD COLUMN IF NOT EXISTS last_renewed_at DATETIME NULL AFTER renewal_count,
    ADD COLUMN IF NOT EXISTS last_renewed_by BIGINT UNSIGNED NULL AFTER last_renewed_at,
    ADD INDEX IF NOT EXISTS idx_loan_item_local_due (local_due_date);

CREATE TABLE IF NOT EXISTS loan_renewal_logs (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    loan_transaction_item_id BIGINT UNSIGNED NOT NULL,
    old_due_date DATETIME NULL,
    new_due_date DATETIME NOT NULL,
    renewed_by BIGINT UNSIGNED NULL,
    note VARCHAR(500) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_loan_renewal_item (loan_transaction_item_id),
    KEY idx_loan_renewal_user (renewed_by),
    CONSTRAINT fk_loan_renewal_item FOREIGN KEY (loan_transaction_item_id) REFERENCES loan_transaction_items(id) ON DELETE CASCADE,
    CONSTRAINT fk_loan_renewal_user FOREIGN KEY (renewed_by) REFERENCES auth_user(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
