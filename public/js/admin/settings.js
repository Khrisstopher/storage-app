// Restricción de extensiones

const fileRestrictionsForm = document.getElementById('fileRestrictionsForm');
const blockedExtensions = document.getElementById('blockedExtensions');

// Cargar extensiones bloqueadas

document.addEventListener('DOMContentLoaded', async () => {

    try {

        const response = await fetch(
            BASE_URL + 'settings/file-restrictions',
            {
                method: 'GET'
            }
        );

        const result = await response.json().catch(() => {
            throw new Error('Respuesta no válida del servidor');
        });

        if (!result.status) {
            throw new Error(result.message);
        }

        blockedExtensions.value = result.data.join(', ');

    } catch (err) {
        Toast.fire({
            icon: 'error',
            title: err.message || 'Error de conexión con el servidor'
        });
    }
});

// Guardar restricciones

fileRestrictionsForm.addEventListener('submit', async (e) => {

    e.preventDefault();

    const extensions = blockedExtensions.value.trim();

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
});