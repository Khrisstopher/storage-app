// #### Funcionalidad para listar archivos ####

let container = null;
let emptyState = null;

document.addEventListener('DOMContentLoaded', async () => {
    container = document.getElementById('files-container');
    emptyState = document.getElementById('empty-state');

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
                const card = document.createElement('div');
                const ext = file.name.split('.').pop().toLowerCase();
                const icon = getFileIcon(ext);

                card.className = 'file-card';

                card.innerHTML = `
                    <div class="d-flex align-items-center gap-3 mb-2">

                        <i class="bi ${icon} fs-3"></i>

                        <div class="flex-grow-1 overflow-hidden">
                            <div class="fw-semibold text-truncate">${file.name}</div>
                            <div class="small file-size">${file.size}</div>
                        </div>

                    </div>

                    <div class="d-flex justify-content-end">
                        <button class="btn btn-sm btn-outline-danger btn-delete" data-id="${file.id}">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                `;
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

// #### Funcionalidad para subir archivos ####
const btnUpload = document.getElementById('btn-upload');
const fileInput = document.getElementById('file-input');

// Abrir selector de archivos
btnUpload.addEventListener('click', () => {
    fileInput.click();
});

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
        console.error("Respuesta del servidor:", result);
        
        // Mostrar el nuevo archivo en la lista
        const card = document.createElement('div');
        const ext = file.name.split('.').pop().toLowerCase();
        const icon = getFileIcon(ext);

        card.className = 'file-card';

        card.innerHTML = `
            <div class="d-flex align-items-center gap-3 mb-2">

                <i class="bi ${icon} fs-3"></i>

                <div class="flex-grow-1 overflow-hidden">
                    <div class="fw-semibold text-truncate">${file.name}</div>
                    <div class="small file-size">${file.size} KB</div>
                </div>

            </div>

            <div class="d-flex justify-content-end">
                <button class="btn btn-sm btn-outline-danger btn-delete" data-id="${result.data.id}">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        `;

        // Agregar al contenedor en la primera posición
        container.prepend(card);
        
        emptyState.classList.add('d-none');
        console.error("ID del nuevo archivo:", result.data.id);
        
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
document.addEventListener('click', async (e) => {

    const btn = e.target.closest('.btn-delete');
    if (!btn) return;

    const id = btn.dataset.id;

    if (!id) {
        console.error('ID no encontrado');
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