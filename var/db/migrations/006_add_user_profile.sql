-- migration: 006 add profile columns (avatar, bio) to users
ALTER TABLE users ADD COLUMN IF NOT EXISTS avatar VARCHAR(1000) NULL AFTER role;
ALTER TABLE users ADD COLUMN IF NOT EXISTS bio TEXT NULL AFTER avatar;
