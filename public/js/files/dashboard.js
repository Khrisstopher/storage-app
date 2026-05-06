// #### Funcionalidad para listar archivos ####

let container = null;
let emptyState = null;

document.addEventListener('DOMContentLoaded', async () => {
    container = document.getElementById('files-container');
    emptyState = document.getElementById('empty-state');

    try {
        const response = await fetch(BASE_URL + 'files/list');
        const responseData = await response.json();

        if (responseData.status) {
            if (!responseData.data ||!responseData.data.length) {
                emptyState.classList.remove('d-none');
                return;
            }

            emptyState.classList.add('d-none');

            responseData.data.forEach(file => {
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
            Toast.fire({
                icon: 'error',
                title: responseData.msg
            });
        }

    } catch (err) {
        Toast.fire({
            icon: 'error',
            title: 'Ocurrió un error al cargar tus archivos. Por favor, inténtalo de nuevo.'
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

        const responseData = await response.json();

        if (!responseData.status) {
            Toast.fire({
                icon: 'error',
                title: responseData.msg
            });
            return;
        }

        Toast.fire({
            icon: 'success',
            title: responseData.msg
        });
        
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
                <button class="btn btn-sm btn-outline-danger btn-delete" data-id="${responseData.data.id}">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        `;

        // Agregar al contenedor en la primera posición
        container.prepend(card);
        
        emptyState.classList.add('d-none');
        console.error("ID del nuevo archivo:", responseData.data.id);
        
    } catch (err) {
        Toast.fire({
            icon: 'error',
            title: 'Error al subir archivo'
        });
    } finally {
        // Limpiar el input para permitir subir el mismo archivo nuevamente si se desea
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

    const result = await Swal.fire({
        title: '¿Eliminar archivo?',
        text: 'Esta acción no se puede deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    });

    if (!result.isConfirmed) return;

    const formData = new FormData();
    formData.append('id', id);

    try {
        const response = await fetch(BASE_URL + 'files/delete', {
            method: 'POST',
            body: formData
        });

        const text = await response.text();

        let responseData;
        try {
            responseData = JSON.parse(text);
        } catch {
            throw new Error('Respuesta inválida del servidor');
        }

        if (!responseData.status) throw new Error(responseData.msg);

        const card = btn.closest('.file-card');
        if (card) card.remove();

        if (!container.children.length) {
            emptyState.classList.remove('d-none');
        }

        Toast.fire({
            icon: 'success',
            title: responseData.msg
        });

    } catch (err) {

        console.error(err);

        Toast.fire({
            icon: 'error',
            title: 'Error al eliminar archivo'
        });
    }
});