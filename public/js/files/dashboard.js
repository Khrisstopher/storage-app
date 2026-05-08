/**
 * Crea el elemento HTML para una tarjeta de archivo
 * @param {Object} file - Objeto con id, name y size
 * @returns {HTMLElement}
 */
function createFileCard(file) {
    const card = document.createElement('div');
    card.className = 'file-card';

    const ext = file.name.split('.').pop().toLowerCase();
    const icon = getFileIcon(ext);
    
    // Si el tamaño es un número (bytes del input), lo formateamos. 
    // Si ya es un string (del servidor), lo dejamos igual.
    const displaySize = typeof file.size === 'number' 
        ? (file.size / 1024).toFixed(2) + ' KB' 
        : file.size;

    card.innerHTML = `
        <div class="d-flex align-items-center gap-3 mb-2">
            <i class="bi ${icon} fs-3"></i>
            <div class="flex-grow-1 overflow-hidden">
                <div class="fw-semibold text-truncate">${file.name}</div>
                <div class="small file-size">${displaySize}</div>
            </div>
        </div>
        <div class="d-flex justify-content-end">
            <a href="${BASE_URL}files/download?id=${file.id}" class="btn btn-sm btn-outline-primary me-2">
                <i class="bi bi-download"></i>
            </a>
            <button class="btn btn-sm btn-outline-danger btn-delete" data-id="${file.id}">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    `;
    return card;
}

// #### Contenedor principal ####
let container, emptyState;

document.addEventListener('DOMContentLoaded', async () => {
    container = document.getElementById('files-container');
    emptyState = document.getElementById('empty-state');

    // #### Funcionalidad para subir archivos ####
    const btnUpload = document.getElementById('btn-upload');
    const fileInput = document.getElementById('file-input');

    btnUpload.addEventListener('click', () => fileInput.click());
   
    fileInput.addEventListener('change', async () => {

        const file = fileInput.files[0];

        if (!file) return;

        const formData = new FormData();
        formData.append('file', file);

        try {
            const response = await fetch(BASE_URL + 'files/upload', {
                method: 'POST',
                body: formData
            });

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
            const nuewCard = createFileCard({
                id: result.data.id,
                name: file.name,
                size: file.size
            });

            // Agregar al contenedor en la primera posición
            container.prepend(nuewCard);
            
            emptyState.classList.add('d-none');
            
        } catch (err) {
            Toast.fire({
                icon: 'error',
                title: err.message || 'Error de conexión con el servidor'
            });
        } finally {
            fileInput.value = '';
        }
    });

    // #### Funcionalidad para eliminar archivos ####
    container.addEventListener('click', async (e) => {

        const btn = e.target.closest('.btn-delete');
        if (!btn) return;

        const id = btn.dataset.id;

        if (!id) {
            Toast.fire({
                icon: 'error',
                title: 'ID no encontrado'
            });
            return;
        }

        const confirmation = await Swal.fire({
            title: '¿Eliminar archivo?',
            text: 'Esta acción no se puede deshacer',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        });

        if (!confirmation.isConfirmed) return;

        const formData = new FormData();
        formData.append('id', id);

        try {
            const response = await fetch(BASE_URL + 'files/delete', {
                method: 'POST',
                body: formData
            });

            const result = await response.json().catch(() => { 
                throw new Error('Respuesta no válida del servidor');
            });

            if (!result.status) throw new Error(result.message);

            const card = btn.closest('.file-card');
            if (card) card.remove();

            if (!container.children.length) {
                emptyState.classList.remove('d-none');
            }

            Toast.fire({
                icon: 'success',
                title: result.message
            });

        } catch (err) {
            Toast.fire({
                icon: 'error',
                title: err.message || 'Error en la conexión con el servidor'
            });
        }
    });

    // #### Funcionalidad para listar archivos ####
    try {
        const response = await fetch(BASE_URL + 'files/list');
        
        const result = await response.json().catch(() => {
            throw new Error('Respuesta no válida del servidor');
        });

        if (result.status) {
            if (!result.data || !result.data.length) {
                emptyState.classList.remove('d-none');
                return;
            }

            emptyState.classList.add('d-none');

            result.data.forEach(file => {
                const card = createFileCard(file);
                container.appendChild(card);
            });
        } else {
            throw new Error(result.message);
        }

    } catch (err) {
        Toast.fire({
            icon: 'error',
            title: err.message || 'Error de conexión con el servidor'
        });
    }
});

