<?php
// includes/header.php - top of every PUBLIC page. Set $page_title and $active before including.
require_once __DIR__ . '/auth.php';
$page_title = $page_title ?? 'AgriChain Rwanda';
$active = $active ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($page_title) ?> | AgriChain Rwanda</title>
<link rel="stylesheet" href="<?= url('css/style.css') ?>">
</head>
<body>
<?php include __DIR__ . '/navbar.php'; ?>
