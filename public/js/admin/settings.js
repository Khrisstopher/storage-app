/**
 * Archivo: public/js/admin/settings.js
 * Descripción: Lógica para administración de restricciones y configuraciones.
 * Autor: @KhrisstopherTube
 * Fecha: 08-05-2026
 */
document.addEventListener('DOMContentLoaded', async () => {
    const fileRestrictionsForm = document.getElementById('fileRestrictionsForm');
    const blockedExtensions = document.getElementById('blockedExtensions');
    
    // Guardar restricciones de archivos
    if (fileRestrictionsForm) {
        fileRestrictionsForm.addEventListener('submit', saveFileRestrictions);
    }

    // Cargar restricciones actuales
    if (blockedExtensions) {
        loadFileRestrictions(blockedExtensions);
    }
});


// ##### Funciones de lógica ####

/**
 * Obtiene las restricciones desde el servidor y las pinta en el textarea
 */
async function loadFileRestrictions(inputElement) {
    try {
        const response = await fetch(BASE_URL + 'settings/file-restrictions');

        const result = await response.json().catch(() => {
            throw new Error('Respuesta no válida del servidor');
        });

        if (!result.status) throw new Error(result.message);

        // Pintamos los datos (asumiendo que vienen como array)
        inputElement.value = result.data.join(', ');

    } catch (err) {
        Toast.fire({
            icon: 'error',
            title: err.message || 'Error al cargar las restricciones'
        });
    }
}

/**
 * Guarda las restricciones de archivos enviadas por el admin
 * @param {*} e 
 */
async function saveFileRestrictions(e) {
    e.preventDefault();

    const form = e.currentTarget;
    const extensions = form.blockedExtensions ? form.blockedExtensions.value.trim() : '';

    try {

        const response = await fetch(
            BASE_URL + 'settings/file-restrictions/save',
            {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    extensions: extensions
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

    } catch (err) {
        Toast.fire({
            icon: 'error',
            title: err.message || 'Error inesperado'
        });
    }
}