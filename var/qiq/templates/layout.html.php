<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->h($this->page_title ?? 'Himatsudo') ?> | Himatsudo</title>
    <link rel="stylesheet" href="/css/main.build.css">
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
