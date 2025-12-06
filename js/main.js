document.addEventListener('DOMContentLoaded', () => {
    // === Inicializa AOS ===
    AOS.init({
        duration: 800,
        once: true,
        mirror: false,
    });

    // === Desplazamiento suave para enlaces de anclaje ===
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        const href = anchor.getAttribute('href');
        if (href && href !== '#') {
            anchor.addEventListener('click', e => {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            });
        }
    });

    // --- Lógica de Login y Registro ---
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    const authMessage = document.getElementById('authMessage');
    const loginFormWrapper = document.getElementById('login-form-wrapper');
    const registerFormWrapper = document.getElementById('register-form-wrapper');
    const showRegisterLink = document.getElementById('showRegister');
    const showLoginLink = document.getElementById('showLogin');

    /**
     * Muestra un mensaje de autenticación al usuario.
     * @param {string} msg - El mensaje a mostrar.
     * @param {string} type - El tipo de mensaje ('success' o 'error').
     */
    const showAuthMessage = (msg, type = 'success') => {
        if (authMessage) {
            authMessage.className = 'auth-message-custom';
            authMessage.textContent = msg;
            authMessage.classList.add(type);
            authMessage.style.display = 'block';

            setTimeout(() => authMessage.style.display = 'none', 5000);
        }
    };

    // === Alternar entre formularios de registro y login ===
    if (showRegisterLink && loginFormWrapper && registerFormWrapper && authMessage) {
        showRegisterLink.addEventListener('click', (e) => {
            e.preventDefault();
            loginFormWrapper.classList.add('hidden');
            registerFormWrapper.classList.remove('hidden');
            authMessage.style.display = 'none';
        });
    }

    if (showLoginLink && loginFormWrapper && registerFormWrapper && authMessage) {
        showLoginLink.addEventListener('click', (e) => {
            e.preventDefault();
            registerFormWrapper.classList.add('hidden');
            loginFormWrapper.classList.remove('hidden');
            authMessage.style.display = 'none';
        });
    }

    // === Manejo del formulario de Registro ===
    if (registerForm) {
        registerForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const submitButton = registerForm.querySelector('button[type="submit"]');
            if (submitButton) submitButton.disabled = true;

            const username = registerForm.username.value.trim();
            const email = registerForm.email.value.trim();
            const password = registerForm.password.value;

            if (!username || !email || !password) {
                showAuthMessage('Todos los campos son obligatorios.', 'error');
                if (submitButton) submitButton.disabled = false;
                return;
            }

            try {
                // RUTA CORREGIDA DEFINITIVA: auth.php está en la raíz, un nivel arriba de js/
                const res = await fetch('../auth.php', { // RUTA CORREGIDA AHORA
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'register', username, email, password })
                });
                const result = await res.json();

                if (result.success) {
                    registerForm.reset();
                    showAuthMessage('Registro exitoso. Redireccionando...', 'success');
                    // seleccionar_rol.php está en vistas_php/, así que esta ruta relativa se mantiene desde js/
                    setTimeout(() => {
                        window.location.href = '../seleccionar_rol.php';
                    }, 1000);
                } else {
                    showAuthMessage(result.message, 'error');
                }
            } catch (err) {
                console.error('Error al registrar:', err);
                showAuthMessage('Error en el servidor. Inténtalo de nuevo.', 'error');
            } finally {
                if (submitButton) submitButton.disabled = false;
            }
        });
    }

    // === Manejo del formulario de Login ===
    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const submitButton = loginForm.querySelector('button[type="submit"]');
            if (submitButton) submitButton.disabled = true;

            const identifier = loginForm.identifier.value.trim();
            const password = loginForm.password.value;

            if (!identifier || !password) {
                showAuthMessage('Usuario/Email y contraseña son obligatorios.', 'error');
                if (submitButton) submitButton.disabled = false;
                return;
            }

            try {
                // RUTA CORREGIDA DEFINITIVA: auth.php está en la raíz, un nivel arriba de js/
                const res = await fetch('../auth.php', { // RUTA CORREGIDA AHORA
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'login', identifier, password })
                });
                const result = await res.json();

                if (result.success) {
                    loginForm.reset();
                    showAuthMessage('Inicio de sesión exitoso. Redireccionando...', 'success');
                    // seleccionar_rol.php está en vistas_php/, así que esta ruta relativa se mantiene desde js/
                    setTimeout(() => {
                        window.location.href = '../seleccionar_rol.php';
                    }, 1000);
                } else {
                    showAuthMessage(result.message, 'error');
                }
            } catch (err) {
                console.error('Error al iniciar sesión:', err);
                showAuthMessage('Error en el servidor. Inténtalo de nuevo.', 'error');
            } finally {
                if (submitButton) submitButton.disabled = false;
            }
        });
    }
});