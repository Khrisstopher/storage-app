<div class="dashboard-container">

    <!-- Header -->
    <div class="dashboard-header">

        <div class="d-flex align-items-center gap-3">
            <img src="img/KHRISM.png" alt="Logo de Storage App" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;" class="dashboard-logo">
            <h1 class="dashboard-title mb-0">Gestión de archivos</h1>
        </div>

        <input type="file" id="file-input" class="d-none">
        <button id="btn-upload" class="btn-upload">Subir archivo</button>

        <!-- Bloque de acciones de navegación/salida -->
        <div class="header-actions d-inline-flex gap-2 align-items-center">

            <?php if (isset($role_id) && $role_id == 1): ?>
                <a href="admin/settings" id="btn-admin" class="btn btn-light btn-sm fw-bold">
                    <i class="bi bi-shield-lock"></i> Panel Administración
                </a>
            <?php endif; ?>

            <button id="btn-logout" class="btn btn-outline-light btn-sm">
                <i class="bi bi-box-arrow-right"></i> Salir
            </button>
        </div>
    </div>

    <!-- Info usuario -->
    <div class="dashboard-user">
        <span>Bienvenido</span>
        <strong id="user-name"><?= htmlspecialchars($user_name) ?></strong>
    </div>

    <!-- Zona de archivos -->
    <div class="files-section">

        <!-- Estado vacío -->
        <div id="empty-state" class="empty-state d-none">
            <p>No tienes archivos aún</p>
        </div>

        <!-- Lista de archivos -->
        <div id="files-container" class="files-grid d-grid gap-3">
            <!-- JS renderiza aquí -->
        </div>

    </div>

</div>