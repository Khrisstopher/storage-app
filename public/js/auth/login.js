const form = document.getElementById('login-form');
const alertError = document.getElementById('alert-error');
const btnSubmit = document.getElementById('btn-submit');

form.addEventListener('submit', async (e) => {
    e.preventDefault();

    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const formData = new FormData(form);
    formData.append('action', 'login');

    try {

        const response = await fetch(BASE_URL + 'auth/login', {
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
            title: data.msg
        });
    }
});