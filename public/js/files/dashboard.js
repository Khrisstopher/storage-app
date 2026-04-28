// #### Funcionalidad para listar archivos ####
document.addEventListener('DOMContentLoaded', async () => {
    const container = document.getElementById('files-container');
    const emptyState = document.getElementById('empty-state');

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
                card.className = 'file-card';
                card.innerHTML = `
                    <div class="file-name">${file.name}</div>
                `;
                container.appendChild(card);
            });
        } else {
            toast.fire({
                icon: 'error',
                title: responseData.msg
            });
        }

    } catch (err) {
        toast.fire({
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
            throw new Error(responseData.msg);
        }

        toast.fire({
            icon: 'success',
            title: responseData.msg
        });
        
        // Mostrar el nuevo archivo en la lista
        const card = document.createElement('div');
        card.className = 'file-card';
        card.innerHTML = `
            <div class="file-name">${file.name}</div>
        `;

        // Agregar al contenedor
        container.prepend(card); // mejor arriba (tipo "nuevo primero")
        
        emptyState.classList.add('d-none');
        
    } catch (err) {
        toast.fire({
            icon: 'error',
            title: 'Error al subir archivo'
        });
    }
});