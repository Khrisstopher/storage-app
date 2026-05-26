<div class="row g-4">
    <div class="col-md-4">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body">
                <h5 class="fw-bold mb-3">Crear Nuevo Grupo</h5>
                <form id="createGroupForm">
                    <div class="mb-3">
                        <label for="groupName" class="form-label small fw-semibold">Nombre del Grupo</label>
                        <input type="text" class="form-control" id="groupName" required placeholder="Ej. Marketing">
                    </div>
                    <div class="mb-3">
                        <label for="groupQuota" class="form-label small fw-semibold">Cuota del Grupo (MB)</label>
                        <input type="number" class="form-control" id="groupQuota" min="1" placeholder="Ej. 50">
                        <div class="form-text">Aplica a los miembros de este grupo.</div>
                    </div>
                    <button type="submit" class="btn btn-dark w-100 rounded-pill">Crear Grupo</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body">
                <h5 class="fw-bold mb-3">Grupos Existentes</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Nombre</th>
                                <th>Cuota Asignada</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="groupsTableBody" class="table-dark"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editGroupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow bg-dark text-white">
            <div class="modal-header border-bottom border-secondary">
                <h5 class="modal-title fw-bold" id="editGroupModalLabel">Modificar Grupo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editGroupForm">
                <div class="modal-body">
                    <input type="hidden" id="editGroupId">
                    <div class="mb-3">
                        <label for="editGroupName" class="form-label small fw-semibold">Nombre del Grupo</label>
                        <input type="text" class="form-control" id="editGroupName" required>
                    </div>
                    <div class="mb-3">
                        <label for="editGroupQuota" class="form-label small fw-semibold">Cuota del Grupo (MB)</label>
                        <input type="number" class="form-control" id="editGroupQuota" min="1">
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Template para filas de grupos -->
<template id="groupRowTemplate">
    <tr class="align-middle admin-table-row">
        <td>
            <div class="fw-semibold text-white group-item-name"></div>
            <small class="text-muted-custom d-block mt-0.5 group-item-description"></small>
        </td>
        <td>
            <span class="badge badge-quota border px-3 py-2 rounded-pill fw-medium group-item-quota"></span>
        </td>
        <td>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-action-edit rounded-pill px-3 edit-group-btn">
                    <i class="bi bi-pencil-square me-1"></i> Editar
                </button>
                <button class="btn btn-sm btn-action-delete rounded-pill px-3 delete-group-btn">
                    <i class="bi bi-trash me-1"></i> Eliminar
                </button>
            </div>
        </td>
    </tr>
</template>