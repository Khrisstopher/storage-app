<div class="dashboard-container">

    <!-- Header -->
    <div class="dashboard-header">
        <h1 class="dashboard-title">Gestión de archivos</h1>
        <input type="file" id="file-input" class="d-none">
        <button id="btn-upload" class="btn-upload">Subir archivo</button>
        <button id="btn-logout" class="btn btn-outline-light btn-sm">
            <i class="bi bi-box-arrow-right"></i> Salir
        </button>
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
        <div id="files-container" class="files-grid d-grid gap-3"></div>
            <!-- JS renderiza aquí -->
        </div>

    </div>

</div>