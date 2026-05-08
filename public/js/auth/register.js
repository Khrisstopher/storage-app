/**
 * Archivo: public/js/auth/register.js
 * Descripción: Lógica para el formulario de registro de usuarios.
 * Autor: @KhrisstopherTube
 * Fecha: 08-05-2026
 */
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('register-form');

    if (form) {
        form.addEventListener('submit', handleRegister);
    }
});

/**
 * Maneja el envío del formulario de registro
 * @param {Event} e 
 * @returns 
 */
async function handleRegister(e) {
    e.preventDefault();
    const form = e.currentTarget;

    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const formData = {
        name: form.name.value,
        email: form.email.value,
        password: form.password.value
    };

    try {
        const response = await fetch(BASE_URL + 'auth/register', {
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

        window.location.href = BASE_URL + 'login';

    } catch (err) {
        Toast.fire({
            icon: 'error',
            title: err.message || 'Error de conexión con el servidor'
        });
    }
}