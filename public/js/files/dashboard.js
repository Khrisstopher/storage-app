/**
 * Archivo: public/js/files/dashboard.js
 * Descripción: Lógica para la vista de gestión de archivos.
 * Autor: @KhrisstopherTube
 * Fecha: 08-05-2026
 */
let container, emptyState, fileInput;

// Configuración de rutas
const URL_UPLOAD = `${window.BASE_URL}files/upload`;
const URL_DELETE = `${window.BASE_URL}files/delete`;
const URL_LIST   = `${window.BASE_URL}files/list`;

document.addEventListener('DOMContentLoaded', () => {
    container = document.getElementById('files-container');
    emptyState = document.getElementById('empty-state');
    fileInput = document.getElementById('file-input');
    
    const btnUpload = document.getElementById('btn-upload');

    btnUpload?.addEventListener('click', () => fileInput.click());
    fileInput?.addEventListener('change', handleUpload);
    container?.addEventListener('click', handleDelete);

    loadFiles();
});

// Subir archivo
async function handleUpload() {
    const file = fileInput.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('file', file);

    try {
        const response = await fetch(URL_UPLOAD, { method: 'POST', body: formData });
        const result = await response.json().catch(() => {
            throw new Error('La respuesta del servidor no es un JSON válido');
        });

        if (!result.status) {
            throw new Error(result.message);
        }

        const newCard = createFileCard({
            id: result.data.id,
            name: result.data.name,
            size: file.size
        });

        container.prepend(newCard);
        emptyState.classList.add('d-none');

        Toast.fire({
            icon: 'success',
            title: result.message // Mensaje de éxito del servidor
        });

    } catch (err) {
        Toast.fire({
            icon: 'error',
            title: err.message || 'Error de conexión'
        });
    } finally {
        fileInput.value = '';
    }
}
// Eliminar el archivo
async function handleDelete(e) {
    const btn = e.target.closest('.btn-delete');
    if (!btn) return;

    const id = btn.dataset.id;
    const confirmation = await Swal.fire({
        title: '¿Eliminar archivo?',
        text: 'Esta acción no se puede deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    });

    if (!confirmation.isConfirmed) return;

    const formData = new FormData();
    formData.append('id', id);

    try {
        const response = await fetch(URL_DELETE, { method: 'POST', body: formData });
        const result = await response.json().catch(() => {
            throw new Error('Error al procesar respuesta del servidor');
        });

        if (!result.status) throw new Error(result.message);

        btn.closest('.file-card').remove();
        if (!container.children.length) emptyState.classList.remove('d-none');

        Toast.fire({
            icon: 'success',
            title: result.message
        });

    } catch (err) {
        Toast.fire({
            icon: 'error',
            title: err.message
        });
    }
}

async function loadFiles() {
    try {
        const response = await fetch(URL_LIST);
        const result = await response.json().catch(() => {
            throw new Error('Error al procesar respuesta del servidor');
        });
        if(!result.status) { throw new Error (result.message)};

        if (result.status && result.data.length) {
            emptyState.classList.add('d-none');
            result.data.forEach(file => container.appendChild(createFileCard(file)));
        } else {
            emptyState.classList.remove('d-none');
        }
    } catch (err) {
        Toast.fire({
            icon: 'error',
            title: err.message
        });
    }
}

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
            <a href="${window.BASE_URL}files/download?id=${file.id}" class="btn btn-sm btn-outline-primary me-2">
                <i class="bi bi-download"></i>
            </a>
            <button class="btn btn-sm btn-outline-danger btn-delete" data-id="${file.id}">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    `;
    return card;
}

