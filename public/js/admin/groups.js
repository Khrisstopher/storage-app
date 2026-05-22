/**
 * Lógica de la vista de grupos de usuarios en el panel de administración
 * @author Khrisstopher
 * @link https://www.linkedin.com/in/khrisstopher/
 */

// Variable global para controlar el modal de Bootstrap de forma nativa
let editModalInstance;

document.addEventListener('DOMContentLoaded', async () => {

    // #### Cargar grupos inmediatamente al entrar a la vista ####
    await loadGroups();

    // Crear nuevo grupo
    const createGroupForm = document.getElementById('createGroupForm');
    const groupNameInput = document.getElementById('groupName');
    const groupQuotaInput = document.getElementById('groupQuota');

    if (createGroupForm && groupNameInput && groupQuotaInput) {
        createGroupForm.addEventListener('submit', createGroup);
    }

    // #### Inicializar el Modal de Edición ####
    const modalElement = document.getElementById('editGroupModal');
    if (modalElement) {
        editModalInstance = new bootstrap.Modal(modalElement);
    }
    // Escuchar el envío del formulario de edición dentro del modal
    const editGroupForm = document.getElementById('editGroupForm');
    if (editGroupForm) {
        editGroupForm.addEventListener('submit', saveEditGroup);
    }

    const tableBody = document.getElementById('groupsTableBody');
    if (tableBody) {
        tableBody.addEventListener('click', (e) => {
            // Buscamos si el clic fue en el botón de editar o dentro de su ícono
            const editBtn = e.target.closest('.edit-group-btn');
            if (editBtn) {
                openEditModal(editBtn.dataset);
                return;
            }

            // Buscamos si el clic fue en el botón de eliminar
            const deleteBtn = e.target.closest('.delete-group-btn');
            if (deleteBtn) {
                deleteGroup(deleteBtn.dataset.id, deleteBtn.dataset.name);
            }
        });
    }
});

// ##### Funciones de lógica de grupos ####

/**
 * Obtiene la lista de grupos desde el servidor y los renderiza usando la plantilla HTML
 */
async function loadGroups() {
    const tableBody = document.getElementById('groupsTableBody');
    const template = document.getElementById('groupRowTemplate');
    
    if (!tableBody || !template) return;

    try {
        tableBody.innerHTML = ''; // Limpiar la tabla

        const response = await fetch(window.BASE_URL + 'groups/list');
        const result = await response.json().catch(() => {
            throw new Error('Respuesta no válida del servidor');
        });

        if (!result.status) throw new Error(result.message);

        const groups = result.data || [];

        if (groups.length === 0) {
            tableBody.innerHTML = `<tr><td colspan="3" class="text-center text-muted py-4 small">No hay grupos registrados todavía.</td></tr>`;
            return;
        }

        // Iteramos e inyectamos usando el clon del Template HTML
        groups.forEach(group => {
            // Clonamos el contenido de la plantilla de la vista
            const clone = template.content.cloneNode(true);

            // Rellenamos los datos usando clases asignadas en el HTML Helper
            clone.querySelector('.group-item-name').textContent = group.name;
            
            const descElem = clone.querySelector('.group-item-description');
            if (group.description) {
                descElem.textContent = group.description;
            } else {
                descElem.remove(); // Si no hay descripción, la quitamos del DOM
            }

            clone.querySelector('.group-item-quota').textContent = group.quota_mb ? `${group.quota_mb} MB` : 'No definida';

            // Configuramos los datasets en los botones de acción directamente
            const editBtn = clone.querySelector('.edit-group-btn');
            editBtn.dataset.id = group.id;
            editBtn.dataset.name = group.name;
            editBtn.dataset.quota = group.quota_mb || '';
            editBtn.dataset.description = group.description || '';

            const deleteBtn = clone.querySelector('.delete-group-btn');
            deleteBtn.dataset.id = group.id;
            deleteBtn.dataset.name = group.name;

            // Insertamos el fragmento de HTML ya listo en el cuerpo de la tabla
            tableBody.appendChild(clone);
        });

    } catch (err) {
        tableBody.innerHTML = `<tr><td colspan="3" class="text-center text-danger py-3 small"><i class="bi bi-exclamation-triangle-fill me-1"></i> Error al cargar datos.</td></tr>`;
        Toast.fire({
            icon: 'error',
            title: err.message || 'Error al cargar los grupos'
        });
    }
}

/** * Crea un nuevo grupo con la información proporcionada por el admin
 * @param {*} e 
 */
async function createGroup(e) {
    e.preventDefault();
    const form = e.currentTarget;
    const groupName = form.groupName ? form.groupName.value.trim() : '';
    const groupQuota = form.groupQuota ? form.groupQuota.value.trim() : '';

    if (!groupName || !groupQuota) {
        Toast.fire({
            icon: 'warning',
            title: 'Por favor completa todos los campos'
        });
        return;
    }

    try {
        const response = await fetch(window.BASE_URL + 'groups/create', 
            {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    name: groupName,
                    quota_mb: groupQuota
                })
            }
        );

        const result = await response.json().catch(() => {
            throw new Error('Respuesta no válida del servidor');
        });

        if (!result.status) {
            throw new Error(result.message);
        }
        form.reset();
        Toast.fire({
            icon: 'success',
            title: result.message
        });
        await loadGroups(); // Recargamos la lista de grupos para mostrar el nuevo grupo creado
    } catch (err) {
        Toast.fire({
            icon: 'error',
            title: err.message || 'Error inesperado'
        });
    }
}

/**
 * Pasa los datos del botón al formulario del modal y lo abre
 * @param {DOMStringMap} dataset Datos del grupo (id, name, quota)
 */
function openEditModal(dataset) {
    document.getElementById('editGroupId').value = dataset.id;
    document.getElementById('editGroupName').value = dataset.name;
    document.getElementById('editGroupQuota').value = dataset.quota;
    
    // Mostramos el modal de forma nativa
    editModalInstance.show();
}

/**
 * Procesa el envío del formulario de edición (POST a la API)
 * @param {Event} e 
 */
async function saveEditGroup(e) {
    e.preventDefault();

    const form = e.currentTarget;
    const groupId = form.editGroupId ? form.editGroupId.value : '';
    const groupName = form.editGroupName ? form.editGroupName.value.trim() : '';
    const groupQuota = form.editGroupQuota ? form.editGroupQuota.value.trim() : '';

    if (!groupId || !groupName || !groupQuota) {
        Toast.fire({
            icon: 'warning',
            title: 'Por favor completa todos los campos'
        });
        return;
    }

    try {
        const response = await fetch(window.BASE_URL + 'groups/update', 
            {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id: groupId,
                    name: groupName,
                    quota_mb: groupQuota
                })
            }
        );

        const result = await response.json().catch(() => {
            throw new Error('Respuesta no válida del servidor');
        });

        if (!result.status) {
            throw new Error(result.message);
        }

        Toast.fire({
            icon: 'success',
            title: result.message
        });
        editModalInstance.hide();
        await loadGroups();
    } catch (err) {
        Toast.fire({
            icon: 'error',
            title: err.message || 'Error inesperado'
        });
    }
}

/**
 * Procesa la eliminación de un grupo
 * @param {string} id ID del grupo
 * @param {string} name Nombre del grupo para confirmación
 */
async function deleteGroup(id, name) {
    if (!id || !name) {
        Toast.fire({icon: 'error', title: 'ID o nombre del grupo no proporcionado'});
        return;
    }

    const confirm = await Swal.fire({
        title: `¿Eliminar el grupo "${name}"?`,
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    });

    if (!confirm.isConfirmed) return;

    try {
        const response = await fetch(window.BASE_URL + 'groups/delete', 
            {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id })
            }
        );
        const result = await response.json().catch(() => {
            throw new Error('Respuesta no válida del servidor');
        });

        if (!result.status) {
            throw new Error(result.message);
        }
        Toast.fire({
            icon: 'success',
            title: result.message
        });
        await loadGroups();
        
    }catch (err) {
        Toast.fire({
            icon: 'error',
            title: err.message || 'Error inesperado'
        });
    }
}