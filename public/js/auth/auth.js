document.getElementById('btn-logout').addEventListener('click', async () => {

    const result = await Swal.fire({
        title: '¿Cerrar sesión?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, salir',
        cancelButtonText: 'Cancelar'
    });

    if (!result.isConfirmed) return;

    try {
        const response = await fetch(BASE_URL + 'auth/logout', {
            method: 'POST'
        });

        const data = await response.json();

        if (!data.status) throw new Error(data.msg);

        // Redirigir al login
        window.location.href = BASE_URL + 'login';

    } catch (err) {
        Toast.fire({
            icon: 'error',
            title: 'Error al cerrar sesión'
        });
    }
});