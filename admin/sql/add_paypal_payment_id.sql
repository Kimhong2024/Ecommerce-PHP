ALTER TABLE orders ADD COLUMN paypal_payment_id VARCHAR(100) DEFAULT NULL;
ALTER TABLE orders ADD COLUMN payment_verified_at DATETIME DEFAULT NULL; 