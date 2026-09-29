-- Run this in phpMyAdmin's SQL tab on your existing kinmel_db.
-- Adds support for Google/Facebook login and email OTP password reset.

ALTER TABLE users
    MODIFY gender  ENUM('Male','Female','Other') NULL,
    MODIFY phone   VARCHAR(20) NULL,
    MODIFY country VARCHAR(50) NULL,
    MODIFY address VARCHAR(255) NULL,
    ADD COLUMN oauth_provider VARCHAR(20) DEFAULT NULL,   -- 'google' or 'facebook', NULL for normal accounts
    ADD COLUMN oauth_id VARCHAR(191) DEFAULT NULL,        -- the unique ID Google/Facebook gives that account
    ADD COLUMN reset_otp VARCHAR(6) DEFAULT NULL,         -- current password-reset OTP, if any
    ADD COLUMN reset_otp_expires DATETIME DEFAULT NULL,   -- when that OTP expires
    ADD UNIQUE KEY unique_oauth (oauth_provider, oauth_id);
