const form = document.getElementById('login-form');

form.addEventListener('submit', async (e) => {
    e.preventDefault();

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
});