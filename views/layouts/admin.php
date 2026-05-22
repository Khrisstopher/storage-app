<div class="container py-4">

    <div class="mb-4">
        <h2 class="fw-bold text-white">Panel de Administración</h2>
        <p class="text-light">
            Gestiona las configuraciones, límites, usuarios y grupos del sistema.
        </p>
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