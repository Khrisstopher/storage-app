/**
 * Lógica de la vista de usuarios en el panel de administración
 * @author Khrisstopher
 * @link https://www.linkedin.com/in/khrisstopher/
 */

let editUserModalInstance;

document.addEventListener('DOMContentLoaded', async () => {
    // #### Inicializar el Modal de Edición de Usuarios ####
    const modalElement = document.getElementById('editUserModal');
    if (modalElement) {
        editUserModalInstance = new bootstrap.Modal(modalElement);
    }

    // #### Cargar listado de usuarios inmediatamente ####
    await loadUsers();

    const tableBody = document.getElementById('usersTableBody');
    if (tableBody) {
        tableBody.addEventListener('click', (e) => {
            const editBtn = e.target.closest('.edit-user-btn');
            if (editBtn) {
                openEditUserModal(editBtn.dataset);
                return;
            }
            const deleteBtn = e.target.closest('.delete-user-btn');
            if (deleteBtn) {
                confirmDeleteUser(deleteBtn.dataset.id, deleteBtn.dataset.username);
            }
        });
    }
});

// ##### Funciones de lógica de usuarios ####

/**
 * Obtiene los usuarios del sistema y los renderiza en la tabla
 */
async function loadUsers() {
    const tableBody = document.getElementById('usersTableBody');
    const template = document.getElementById('userRowTemplate');

    if (!tableBody || !template) return;

    try {
        tableBody.innerHTML = ''; // Limpiar la tabla antes de cargar

        // Asumiendo que tu enrutador y API responderán en 'users/list' o similar
        const response = await fetch(window.BASE_URL + 'users/list');
        const result = await response.json().catch(() => {
            throw new Error('Respuesta no válida del servidor');
        });

        if (!result.status) throw new Error(result.message);

        const users = result.data || [];
        console.error('Usuarios obtenidos:', users); // Debug: Ver qué usuarios se recibieron

        if (users.length === 0) {
            tableBody.innerHTML = `<tr><td colspan="4" class="text-center text-muted py-4 small">No hay usuarios registrados en el sistema.</td></tr>`;
            return;
        }

        users.forEach(user => {
            const clone = template.content.cloneNode(true);

            // Inyectar Nombre de usuario y Email
            clone.querySelector('.user-item-username').textContent = user.username;
            clone.querySelector('.user-item-email').textContent = user.email;

            // Grupo asignado (Validar si viene nulo de la DB)
            const groupBadge = clone.querySelector('.user-item-group');
            if (user.group_name) {
                groupBadge.textContent = user.group_name;
                groupBadge.classList.replace('bg-secondary', 'bg-primary'); // Cambia color si tiene grupo
            }

            // Cuota específica del usuario
            const quotaBadge = clone.querySelector('.user-item-quota');
            if (user.custom_quota_mb) {
                quotaBadge.textContent = `${user.custom_quota_mb} MB`;
                quotaBadge.classList.replace('border-warning', 'border-danger');
                quotaBadge.classList.replace('text-warning', 'text-danger');
            } else {
                quotaBadge.textContent = 'Usa heredada';
            }

            // Guardar datasets en los botones para usarlos más adelante
            const editBtn = clone.querySelector('.edit-user-btn');
            editBtn.dataset.id = user.id;
            editBtn.dataset.username = user.username;
            editBtn.dataset.groupId = user.group_id || '';
            editBtn.dataset.quota = user.custom_quota_mb || '';

            const deleteBtn = clone.querySelector('.delete-user-btn');
            deleteBtn.dataset.id = user.id;
            deleteBtn.dataset.username = user.username;

            tableBody.appendChild(clone);
        });

    } catch (err) {
        tableBody.innerHTML = `<tr><td colspan="4" class="text-center text-danger py-3 small"><i class="bi bi-exclamation-triangle-fill me-1"></i> Error al cargar usuarios.</td></tr>`;
        Toast.fire({ icon: 'error', title: err.message || 'Error al conectar con el servidor' });
    }
}

/**
 * Prepara el modal con los datos actuales y lo abre
 */
function openEditUserModal(dataset) {
    document.getElementById('editUserId').value = dataset.id;
    document.getElementById('editUserGroup').value = dataset.groupId;
    document.getElementById('editUserQuota').value = dataset.quota;

    editUserModalInstance.show();
}

/**
 * Confirmación previa a la eliminación de un usuario
 * @param {string} id - ID del usuario a eliminar
 * @param {string} username - Nombre de usuario (para mostrar en la confirmación)
 */
async function confirmDeleteUser(id, username) {
    if (!id || !username) {
        Toast.fire({ icon: 'error', title: 'Datos de usuario incompletos' });
        return;
    }

    const confirm = await Swal.fire({
        title: `¿Eliminar el grupo "${username}"?`,
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    });

    if (!confirm.isConfirmed) return;
    try {
        const response = await fetch(window.BASE_URL + `users/delete/`, 
            { 
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
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
    } catch (err) {
        Toast.fire({
            icon: 'error',
            title: err.message || 'Error inesperado'
        });
    }
}