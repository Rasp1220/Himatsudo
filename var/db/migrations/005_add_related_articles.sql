-- migration: 005 add related_article_ids column to articles
ALTER TABLE articles ADD COLUMN related_article_ids JSON NULL AFTER youtube_thumbnail;
