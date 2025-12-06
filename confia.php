<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confía+: Iniciar Sesión / Registrarse</title>
    <link rel="stylesheet" href="css/estilos_generales.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="css/login-page.css">
    <link type="icon" rel="icono/png" href="img/logo.png">
    </head>
<body>

    <div class="container-fluid login-container d-flex align-items-center justify-content-center min-vh-100">
        <div class="row w-100 justify-content-center">
            <div class="col-lg-6 d-flex align-items-center justify-content-center order-lg-1 order-2">
                <div class="text-center logo-section">
                    <img src="img/logo.png" alt="Logo Confía+" class="img-fluid mb-4" data-aos="fade-right" data-aos-duration="1000">
                    <h1 class="display-4 fw-bold text-primary-custom" data-aos="fade-right" data-aos-delay="200">Confía+</h1>
                    <p class="lead text-secondary-custom" data-aos="fade-right" data-aos-delay="400">Tu Apoyo en Salud Mental para Estudiantes Universitarios</p>
                </div>
            </div>

            <div class="col-lg-6 d-flex align-items-center justify-content-center order-lg-2 order-1">
                <div class="auth-forms-container-custom p-4 p-md-5 rounded shadow bg-white" data-aos="fade-left" data-aos-duration="1000">
                    <h2 class="text-center mb-4 text-secondary-custom">Accede a Confía+</h2>

                    <div id="login-form-wrapper" class="auth-form-wrapper-custom">
                        <h3 class="text-center mb-4 text-primary-custom">Iniciar Sesión</h3>
                        <form id="loginForm">
                            <div class="mb-3">
                                <label for="loginIdentifier" class="form-label">Usuario o Correo:</label>
                                <input type="text" class="form-control" id="loginIdentifier" name="identifier" required>
                            </div>
                            <div class="mb-3">
                                <label for="loginPassword" class="form-label">Contraseña:</label>
                                <input type="password" class="form-control" id="loginPassword" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 mt-3 btn-lg btn-primary-custom">Entrar</button>
                        </form>
                        <p class="text-center mt-3 auth-toggle-text-custom">¿No tienes cuenta? <a href="#" id="showRegister" class="text-secondary-custom fw-bold">Regístrate aquí</a></p>
                    </div>

                    <div id="register-form-wrapper" class="auth-form-wrapper-custom hidden">
                        <h3 class="text-center mb-4 text-primary-custom">Registrarse</h3>
                        <form id="registerForm">
                            <div class="mb-3">
                                <label for="registerUsername" class="form-label">Nombre de Usuario:</label>
                                <input type="text" class="form-control" id="registerUsername" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="registerEmail" class="form-label">Correo Electrónico:</label>
                                <input type="email" class="form-control" id="registerEmail" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="registerPassword" class="form-label">Contraseña:</label>
                                <input type="password" class="form-control" id="registerPassword" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 mt-3 btn-lg btn-primary-custom">Crear Cuenta</button>
                        </form>
                        <p class="text-center mt-3 auth-toggle-text-custom">¿Ya tienes cuenta? <a href="#" id="showLogin" class="text-secondary-custom fw-bold">Iniciar Sesión</a></p>
                    </div>

                    <div id="authMessage" class="auth-message-custom mt-4" style="display: none;"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="js/main.js"></script>
    <script>
        AOS.init({
            duration: 800,
            once: true,
            mirror: false,
        });
    </script>
</body>
</html>