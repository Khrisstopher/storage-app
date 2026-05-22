<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Ruta base -->
    <base href="<?= BASE_URL ?>">
    <title><?= $title ?? 'Storage App' ?></title>
    <link rel="shortcut icon" href="img/KHRISM.ico">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <?php if (!empty($styles)): ?>
        <link rel="stylesheet" href="<?= $styles ?>">
    <?php endif; ?>
</head>

<body class="<?= $bodyClass ?? '' ?>">

    <?= $content ?> <!-- Aquí se inyecta la vista específica -->

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script> window.BASE_URL = "<?= BASE_URL ?>"; </script>
    <script src="js/main.js"></script>

    <?php if (!empty($scripts)): ?>
        <?php if (is_array($scripts)): ?>
            <?php foreach ($scripts as $script): ?>
                <script src="<?= $script ?>" defer></script>
            <?php endforeach; ?>
        <?php else: ?>
            <script src="<?= $scripts ?>" defer></script>
        <?php endif; ?>
    <?php endif; ?>

</body>
</html>