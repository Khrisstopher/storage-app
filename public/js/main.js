/**
 * Configuración global JS para la aplicación.
 * @author Khrisstopher
 * @link https://www.linkedin.com/in/khrisstopher/
 */

// Configuración de SweetAlert2 para notificaciones tipo toast
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 2000,
    timerProgressBar: true,

    didOpen: (toast) => {
        toast.onmouseenter = Swal.stopTimer;
        toast.onmouseleave = Swal.resumeTimer;
    }
});

// Para mostrar los iconos de los archivos según su extensión
function getFileIcon(extension) {

    if (!extension) return 'bi-file-earmark text-muted';

    extension = extension.toLowerCase();

    const map = {
        pdf: 'bi-file-earmark-pdf text-danger',
        doc: 'bi-file-earmark-word text-primary',
        docx: 'bi-file-earmark-word text-primary',
        xls: 'bi-file-earmark-excel text-success',
        xlsx: 'bi-file-earmark-excel text-success',
        png: 'bi-file-earmark-image text-info',
        jpg: 'bi-file-earmark-image text-info',
        jpeg: 'bi-file-earmark-image text-info',
        gif: 'bi-file-earmark-image text-info',
        zip: 'bi-file-earmark-zip text-warning',
        txt: 'bi-file-earmark-text text-secondary',
        csv: 'bi-file-earmark-spreadsheet text-success',
        json: 'bi-file-earmark-code text-warning',
        xml: 'bi-file-earmark-code text-warning'
    };
    return map[extension] || 'bi-file-earmark-fill text-secondary';
}

/**
 * Maneja los eventos globales de la aplicación, como el logout del usuario.
 * Se ejecuta una vez que el DOM está completamente cargado.
 */
document.addEventListener('DOMContentLoaded', () => {

    const btn = document.getElementById('btn-logout');

    if (btn) {
        btn.addEventListener('click', handleLogout);
    }
});

/**
 * Maneja el logout del usuario
 * @param {Event} e 
 * @returns 
 */
async function handleLogout(e) {
    e.preventDefault();
    const btn = e.currentTarget;

    const confirmation = await Swal.fire({
        title: '¿Cerrar sesión?',
        text: '¿Estás seguro de que deseas cerrar sesión?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        confirmButtonText: 'Sí, salir',
        cancelButtonText: 'Cancelar'
    });

    if (!confirmation.isConfirmed) return;

    try {
        const response = await fetch(window.BASE_URL + 'auth/logout', {
            method: 'POST'
        });

        const result = await response.json().catch(() => {
            throw new Error('Respuesta no válida del servidor');
        });

        if (!result.status) throw new Error(result.message);

        // Redirigir al login
        window.location.href = window.BASE_URL + 'login';

    } catch (err) {
        Toast.fire({
            icon: 'error',
            title: err.message || 'Error de conexión con el servidor'
        });
    }
}