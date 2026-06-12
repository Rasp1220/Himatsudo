-- migration: 007 add SNS link columns to users
ALTER TABLE users ADD COLUMN instagram_url VARCHAR(500) NULL AFTER bio;
ALTER TABLE users ADD COLUMN twitter_url VARCHAR(500) NULL AFTER instagram_url;
ALTER TABLE users ADD COLUMN tiktok_url VARCHAR(500) NULL AFTER twitter_url;
