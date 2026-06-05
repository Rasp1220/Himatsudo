<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->h($this->page_title ?? 'ひまつど') ?> | ひまつど</title>
    <link rel="stylesheet" href="/css/main.build.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
</head>
<body>
<?php $this->setLayout(null) // prevent recursion ?>
<?= $this->render('components/header') ?>
<main class="main-content">
    <div class="container">
        <?= $this->getContent() ?>
    </div>
</main>
<?= $this->render('components/footer') ?>
</body>
</html>
