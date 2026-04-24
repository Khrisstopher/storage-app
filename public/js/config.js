/* #### Configuración global para la aplicación #### */

// Configuración de SweetAlert2 para notificaciones tipo toast
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,

    didOpen: (toast) => {
        toast.onmouseenter = Swal.stopTimer;
        toast.onmouseleave = Swal.resumeTimer;
    }
});

// URL base de la aplicación (ajusta según tu configuración)
const BASE_URL = '/storage-app/public/';