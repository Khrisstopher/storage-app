/* #### Configuración global para la aplicación #### */

// Configuración de SweetAlert2 para notificaciones tipo toast
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 1500,
    timerProgressBar: true,

    didOpen: (toast) => {
        toast.onmouseenter = Swal.stopTimer;
        toast.onmouseleave = Swal.resumeTimer;
    }
});

// URL base de la aplicación (ajusta según tu configuración)
const BASE_URL = '/storage-app/public/';


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