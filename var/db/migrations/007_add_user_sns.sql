-- migration: 007 add SNS link columns to users
ALTER TABLE users ADD COLUMN IF NOT EXISTS instagram_url VARCHAR(500) NULL AFTER bio;
ALTER TABLE users ADD COLUMN IF NOT EXISTS twitter_url VARCHAR(500) NULL AFTER instagram_url;
ALTER TABLE users ADD COLUMN IF NOT EXISTS tiktok_url VARCHAR(500) NULL AFTER twitter_url;
