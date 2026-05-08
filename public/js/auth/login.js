/**
 * Archivo: public/js/auth/login.js
 * Descripción: Lógica para el formulario de login de usuarios.
 * Autor: @KhrisstopherTube
 * Fecha: 08-05-2026
 */
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('login-form');
    if (form){
        form.addEventListener('submit', handleLogin);
    }
});

/**
 * Maneja el envío del formulario de login
 * @param {Event} e 
 * @returns 
 */
async function handleLogin(e) {
    e.preventDefault();
    const form = e.currentTarget;

    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const formData = {
        email: form.email.value,
        password: form.password.value
    }

    try {

        const response = await fetch(BASE_URL + 'auth/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });

        const result = await response.json().catch(() => {
            throw new Error('Respuesta no válida del servidor');
        });

        if (!result.status) {
            throw new Error(result.message);
        }
        window.location.href = BASE_URL + 'dashboard';

    } catch (err) {
        Toast.fire({
            icon: 'error',
            title: err.message || 'Error de conexión con el servidor'
        });
    }
}