<div class="container py-4">

    <!-- Header -->
    <div class="mb-4">
        <h2 class="fw-bold">Panel de Administración</h2>
        <p class="mb-0">
            Gestiona las configuraciones globales del sistema de almacenamiento.
        </p>
    </div>

    <div class="row g-4">

        <!-- Restricción de extensiones -->
        <div class="col-12">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body">

                    <div class="d-flex align-items-center mb-3">
                        <div>
                            <h4 class="mb-1">Restricción de tipos de archivo</h4>
                            <p class="mb-0">
                                Configura las extensiones de archivo que estarán prohibidas para todos los usuarios.
                            </p>
                        </div>
                    </div>

                    <form id="fileRestrictionsForm">

                        <div class="mb-3">
                            <label for="blockedExtensions" class="form-label fw-semibold">
                                Extensiones prohibidas 
                            </label>

                            <textarea 
                                class="form-control"
                                id="blockedExtensions"
                                rows="4"
                                placeholder="Ejemplo: exe, bat, php, js, sh"
                            ></textarea>

                            <div class="form-text">
                                Separa cada extensión usando comas.
                            </div>
                        </div>

                        <button type="submit" class="btn btn-dark rounded-pill px-4">
                            Guardar restricciones
                        </button>

                    </form>

                </div>
            </div>
        </div>

        <!-- Cuota global -->
        <div class="col-md-6">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-body">

                    <h4 class="mb-3">Límite global de almacenamiento</h4>

                    <p class="mb-0">
                        Define el límite de almacenamiento predeterminado para todos los usuarios.
                    </p>

                    <form id="globalQuotaForm">

                        <div class="mb-3">
                            <label for="globalQuota" class="form-label fw-semibold">
                                Límite global (MB)
                            </label>

                            <input 
                                type="number"
                                class="form-control"
                                id="globalQuota"
                                placeholder="Ejemplo: 10"
                                min="1"
                            >
                        </div>

                        <button type="submit" class="btn btn-primary rounded-pill px-4">
                            Guardar límite
                        </button>

                    </form>

                </div>
            </div>
        </div>

        <!-- Validación ZIP -->
        <div class="col-md-6">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-body">

                    <h4 class="mb-3">Análisis de archivos ZIP</h4>

                    <p class="mb-0">
                        Activa la inspección automática del contenido de archivos comprimidos (.zip) al subirlos.
                    </p>

                    <form id="zipValidationForm">

                        <div class="form-check form-switch mb-4">
                            <input 
                                class="form-check-input"
                                type="checkbox"
                                id="zipValidation"
                                checked
                            >

                            <label class="form-check-label" for="zipValidation">
                                Analizar archivos ZIP automáticamente
                            </label>
                        </div>

                        <button type="submit" class="btn btn-success rounded-pill px-4">
                            Guardar configuración
                        </button>

                    </form>

                </div>
            </div>
        </div>

        <!-- Próximamente -->
        <div class="col-12">
            <div class="card border-0 bg-light rounded-4">
                <div class="card-body">

                    <h5 class="mb-2">Próximamente</h5>

                    <ul class="mb-0">
                        <li>Gestión de grupos</li>
                        <li>Asignación de cuotas por grupo</li>
                        <li>Asignación de cuotas por usuario</li>
                        <li>Administración de usuarios</li>
                    </ul>

                </div>
            </div>
        </div>

    </div>

</div>