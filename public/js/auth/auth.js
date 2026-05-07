document.addEventListener('DOMContentLoaded', () => {

    const btnLogout = document.getElementById('btn-logout');

    if (btnLogout) {
        btnLogout.addEventListener('click', async () => {

            const confirmation = await Swal.fire({
                title: '¿Cerrar sesión?',
                text: '¿Estás seguro de que deseas cerrar sesión?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, salir',
                cancelButtonText: 'Cancelar'
            });

            if (!confirmation.isConfirmed) return;

            try {
                const response = await fetch(BASE_URL + 'auth/logout', {
                    method: 'POST'
                });

                const result = await response.json().catch(() => {
                    throw new Error('Respuesta no válida del servidor');
                });

                if (!result.status) throw new Error(result.message);

                // Redirigir al login
                window.location.href = BASE_URL + 'login';

            } catch (err) {
                Toast.fire({
                    icon: 'error',
                    title: err.message || 'Error de conexión con el servidor'
                });
            }
        });
    }
});