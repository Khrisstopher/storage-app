const form = document.getElementById('register-form');
const alertError = document.getElementById('alert-error');
const btnSubmit = document.getElementById('btn-submit');

form.addEventListener('submit', async (e) => {
    e.preventDefault();

    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const formData = new FormData(form);
    formData.append('action', 'register');

    try {

        const response = await fetch(BASE_URL + 'auth/register', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.status) {
            Toast.fire({
                icon: 'success',
                title: data.msg
            });
        } else {
            Toast.fire({
                icon: 'error',
                title: data.msg
            });
        }

    } catch (err) {
        Toast.fire({
            icon: 'error',
            title: 'Ocurrió un error al procesar tu solicitud. Por favor, inténtalo de nuevo.'
        });
    }
});