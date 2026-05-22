
<div class="login-card">
    <div class="login-card-body">

        <img src="img/KHRISM.png" alt="Logo de Storage App" class="login-logo mb-3">

        <h1 class="login-title mb-1">Bienvenido a <span>Storage App</span></h1>
        <p class="login-subtitle mb-4">
            Inicia sesión en <span>Storage App</span> para gestionar tus archivos de manera eficiente y segura.
        </p>
        <div id="alert-error" class="alert alert-danger d-none" role="alert"></div>

        <form id="login-form" novalidate> <!-- No validación del navegador -->

            <div class="mb-3">
                <label for="email" class="form-label email-label">Correo electrónico</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-control email-input"
                    placeholder="correo@ejemplo.com"
                    required
                    autofocus>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label password-label">Contraseña</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-control password-input"
                    placeholder="••••••••"
                    required>
            </div>
            <button type="submit" id="btn-submit" class="btn-login w-100">Iniciar sesión</button>

            <div class="text-center mt-3">
                <span class="register-extra-text">¿No tienes cuenta?</span>
                <a href="register" class="register-link">Regístrate</a>
            </div>
        </form>
    </div>
</div>