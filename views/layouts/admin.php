<div class="container py-4">

    <!-- Encabezado con título a la izquierda y botón a la derecha -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-3">
            <img src="img/KHRISM.png" alt="Logo de Storage App" style="width: 45px; height: 45px; border-radius: 50%; object-fit: cover;" class="admin-logo">
            <div>
                <h2 class="fw-bold text-white mb-1">Panel de Administración</h2>
                <p class="text-light mb-0">
                    Gestiona las configuraciones, límites, usuarios y grupos del sistema.
                </p>
            </div>
        </div>
        
        <a href="dashboard" class="btn btn-dark border-secondary btn-sm fw-bold text-white">
            <i class="bi bi-speedometer2 me-1"></i> Ir al Dashboard
        </a>
    </div>

    <ul class="nav nav-tabs mb-4" id="adminTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link <?= $activeTab === 'global' ? 'active fw-bold' : '' ?>" href="admin/settings">
                Configuraciones Globales
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $activeTab === 'groups' ? 'active fw-bold' : '' ?>" href="admin/groups">
                Gestión de Grupos
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $activeTab === 'users' ? 'active fw-bold' : '' ?>" href="admin/users">
                Administración de Usuarios
            </a>
        </li>
    </ul>

    <div class="tab-content" id="adminTabsContent">
        <div class="tab-pane fade show active" role="tabpanel">
            <?php 
                // Cargamos dinámicamente la sub-vista específica enviada por el controlador
                require __DIR__ . '/../' . $subView . '.html'; 
            ?>
        </div>
    </div>
</div>