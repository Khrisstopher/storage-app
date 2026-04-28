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

        const responseData = await response.json();

        if (responseData.status) {
            await Toast.fire({
                icon: 'success',
                title: responseData.msg
            });
            window.location.href = BASE_URL + 'dashboard';
        } else {
            Toast.fire({
                icon: 'error',
                title: responseData.msg
            });
        }

    } catch (err) {
        Toast.fire({
            icon: 'error',
            title: 'Ocurrió un error al procesar tu solicitud. Por favor, inténtalo de nuevo.'
        });
    }
});