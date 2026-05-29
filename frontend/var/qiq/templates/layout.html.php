<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->h($this->page_title ?? 'Himatsudo') ?> | Himatsudo</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Hiragino Kaku Gothic ProN', 'Noto Sans JP', sans-serif; color: #333; background: #f9f9f9; line-height: 1.7; }
        a { color: #2563eb; text-decoration: none; } a:hover { text-decoration: underline; }
        img { max-width: 100%; height: auto; display: block; }
        .container { max-width: 1100px; margin: 0 auto; padding: 0 1.25rem; }
        .site-header { background: #1e293b; color: #fff; padding: 1rem 0; }
        .site-header .container { display: flex; justify-content: space-between; align-items: center; }
        .site-header a { color: #fff; }
        .site-title { font-size: 1.5rem; font-weight: 700; letter-spacing: .05em; }
        .site-nav { display: flex; gap: 1.5rem; list-style: none; }
        .site-footer { background: #1e293b; color: #94a3b8; text-align: center; padding: 2rem 0; margin-top: 4rem; font-size: .875rem; }
        .main-content { padding: 2.5rem 0; }
        .page-title { font-size: 1.75rem; font-weight: 700; margin-bottom: 1.5rem; padding-bottom: .75rem; border-bottom: 2px solid #e2e8f0; }
        .card { background: #fff; border-radius: .5rem; box-shadow: 0 1px 3px rgba(0,0,0,.1); overflow: hidden; transition: transform .2s; }
        .card:hover { transform: translateY(-3px); }
        .card-img { width: 100%; height: 180px; object-fit: cover; }
        .card-body { padding: 1rem; }
        .card-title { font-size: 1rem; font-weight: 600; margin-bottom: .5rem; }
        .card-meta { font-size: .8rem; color: #64748b; }
        .badge { display: inline-block; padding: .2em .6em; border-radius: .25rem; font-size: .75rem; font-weight: 600; background: #dbeafe; color: #1e40af; }
        .badge.youtube { background: #fee2e2; color: #991b1b; }
        .badge.blog { background: #dcfce7; color: #166534; }
        .articles-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem; }
        .pagination { display: flex; gap: .5rem; justify-content: center; margin-top: 2rem; }
        .pagination a, .pagination span { padding: .4rem .8rem; border: 1px solid #e2e8f0; border-radius: .25rem; }
        .pagination .current { background: #2563eb; color: #fff; border-color: #2563eb; }
    </style>
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
