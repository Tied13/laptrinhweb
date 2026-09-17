-- Run once for databases created before the quantity column was added.
ALTER TABLE products ADD COLUMN quantity INT NOT NULL DEFAULT 0 AFTER price;
