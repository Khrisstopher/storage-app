<div class="register-card">
    <div class="register-card-body">

        <img src="img/KHRISM.png" alt="Logo de Storage App" class="register-logo mb-3">

        <h1 class="register-title mb-1">Crear Cuenta <span>Storage App</span></h1>
        <p class="register-subtitle mb-4">
            Regístrate en <span>Storage App</span> y comienza a gestionar tus archivos fácilmente.
        </p>

        <div id="alert-error" class="alert alert-danger d-none" role="alert"></div>

        <form id="register-form" novalidate>

            <div class="mb-3">
                <label for="name" class="form-label email-label">Nombre completo</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    class="form-control email-input"
                    placeholder="Tu nombre completo"
                    required>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label email-label">Correo electrónico</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-control email-input"
                    placeholder="correo@ejemplo.com"
                    required>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label password-label">Contraseña</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-control password-input"
                    placeholder="••••••••"
                    required
                    minlength="6">
            </div>

            <button type="submit" id="btn-submit" class="btn-register w-100">
                Crear cuenta
            </button>

            <div class="text-center mt-3">
                <span class="login-extra-text">¿Ya tienes cuenta?</span>
                <a href="login" class="login-link">Inicia sesión</a>
            </div>

        </form>
    </div>
</div>