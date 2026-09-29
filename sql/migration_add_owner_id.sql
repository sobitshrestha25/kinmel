-- Run this ONLY if you already have kinmel_db set up and don't want to
-- drop/re-import everything. It adds the owner_id column used to track
-- which customer added which product (NULL = admin/catalog product).

ALTER TABLE products
    ADD COLUMN owner_id INT DEFAULT NULL AFTER category_id,
    ADD CONSTRAINT fk_products_owner FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE;
