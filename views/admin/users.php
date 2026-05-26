<div class="card shadow-sm border-0 rounded-4">
    <div class="card-body">
        <h5 class="fw-bold mb-3">Listado de Usuarios del Sistema</h5>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Usuario / Email</th>
                        <th>Grupo Asignado</th>
                        <th>Cuota Específica (MB)</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="usersTableBody" class="table-dark"></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal para editar usuario -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-custom-dark rounded-4 shadow-lg">
            
            <div class="modal-header border-bottom border-secondary border-opacity-25">
                <h5 class="modal-title fw-bold text-white" id="editUserModalLabel">
                    Modificar <span class="text-gradient-custom">Usuario</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="editUserForm">
                <div class="modal-body">
                    <input type="hidden" id="editUserId">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-light-custom">Asignar a Grupo</label>
                        <select class="form-select select-custom-dark text-white" id="editUserGroup"></select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-light-custom">Cuota Exclusiva de Usuario (MB)</label>
                        <input type="number" class="form-control input-custom-dark text-white" id="editUserQuota" min="0" placeholder="Dejar en blanco para usar cuota de grupo/global">
                        <div class="form-text text-danger-custom small mt-1">
                            <i class="bi bi-exclamation-circle-fill me-1"></i> Este límite tiene la máxima prioridad en el sistema.
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-cancel-custom rounded-pill px-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-home-style rounded-pill px-4">Guardar Cambios</button>
                </div>
            </form>
            
        </div>
    </div>
</div>

<!-- Template para fila de usuario -->
<template id="userRowTemplate">
    <tr class="align-middle admin-table-row">
        <td>
            <div class="fw-semibold text-white user-item-username"></div>
            <small class="text-muted-custom d-block mt-0.5 user-item-email"></small>
        </td>
        <td>
            <span class="badge bg-secondary px-3 py-2 rounded-pill fw-medium user-item-group">Sin Grupo</span>
        </td>
        <td>
            <span class="badge border border-warning text-warning px-3 py-2 rounded-pill fw-medium user-item-quota">Por Defecto</span>
        </td>
        <td>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-action-edit rounded-pill px-3 edit-user-btn">
                    <i class="bi bi-pencil-square me-1"></i> Editar
                </button>
                <button class="btn btn-sm btn-action-delete rounded-pill px-3 delete-user-btn">
                    <i class="bi bi-trash me-1"></i> Eliminar
                </button>
            </div>
        </td>
    </tr>
</template>