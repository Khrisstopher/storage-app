<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Ruta base -->
    <base href="/storage-app/public/">

    <title><?= $title ?? 'Storage App' ?></title>

    <link rel="shortcut icon" href="img/KHRISM.ico">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <?php if (!empty($styles)): ?>
        <link rel="stylesheet" href="<?= $styles ?>">
    <?php endif; ?>
</head>

<body class="<?= $bodyClass ?? '' ?>">

    <?= $content ?> <!-- Aquí se inyecta la vista específica -->

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="js/config.js"></script>

    <?php if (!empty($scripts)): ?>
        <script src="<?= $scripts ?>"></script>
    <?php endif; ?>

</body>
</html>