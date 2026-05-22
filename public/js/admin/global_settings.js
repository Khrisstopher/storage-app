/**
 * Lógica para administración de restricciones y configuraciones.
 * @author Khrisstopher
 * @link https://www.linkedin.com/in/khrisstopher/
 */
document.addEventListener('DOMContentLoaded', async () => {

    // #### Restricciones de archivos ####
    const fileRestrictionsForm = document.getElementById('fileRestrictionsForm');
    const blockedExtensions = document.getElementById('blockedExtensions');
    
    if (fileRestrictionsForm) {
        fileRestrictionsForm.addEventListener('submit', saveFileRestrictions);
    }
    if (blockedExtensions) {
        loadFileRestrictions(blockedExtensions);
    }

    // #### Límite de cuota global de almacenamiento por usuario ####

    const globalQuotaForm = document.getElementById('globalQuotaForm');
    const globalQuota = document.getElementById('globalQuota');

    if (globalQuotaForm) {
        globalQuotaForm.addEventListener('submit', saveQuotaGlobalLimit);
    }

    if (globalQuota) {
        loadQuotaGlobalLimit(globalQuota);
    }
});


// ##### Funciones de lógica  de configuración Global ####

/**
 * Obtiene las restricciones desde el servidor y las pinta en el textarea
 */
async function loadFileRestrictions(inputElement) {
    try {
        const response = await fetch(window.BASE_URL + 'global/listFileRestrictions');

        const result = await response.json().catch(() => {
            throw new Error('Respuesta no válida del servidor');
        });

        if (!result.status) throw new Error(result.message);

        // Pintamos los datos
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

        const response = await fetch(window.BASE_URL + 'global/saveFileRestrictions',
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
        blockedExtensions.value = '';

        setTimeout(() => { // Esto es para notar el cambio
            blockedExtensions.value = result.data.join(', ');
        }, 50);

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

/**
 * Cargar el límite global de cuota de almacenamiento para todos los usuarios
 */
async function loadQuotaGlobalLimit(globalQuota) {
    try {
        const response = await fetch(window.BASE_URL + 'global/listQuotaGlobalLimit');
        const result = await response.json().catch(() => {
            throw new Error('Respuesta no válida del servidor');
        });

        if (!result.status) throw new Error(result.message);

        globalQuota.value = result.data
    } catch (err) {
        Toast.fire({
            icon: 'error',
            title: err.message || 'Error al cargar el límite global de cuota'
        });
    }
}

/**
 * Guarda el nuevo límite global de cuota de almacenamiento por usuario
 * @param {*} e 
 */
async function saveQuotaGlobalLimit(e) {
    e.preventDefault();

    const form = e.currentTarget;
    const newLimit = form.globalQuota ? form.globalQuota.value.trim() : '';
    if (!newLimit) return;

    try {
        const response = await fetch(window.BASE_URL + 'global/saveQuotaGlobalLimit',
            {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    limit: newLimit
                })
            }
        );

        const result = await response.json().catch(() => {
            throw new Error('Respuesta no válida del servidor');
        });

        if (!result.status) {
            throw new Error(result.message);
        }

        globalQuota.value = '';

        setTimeout(() => { // Esto es para notar el cambio
            globalQuota.value = newLimit;
        }, 50);

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