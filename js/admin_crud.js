document.addEventListener('DOMContentLoaded', () => {
    const usersTableBody = document.getElementById('usersTableBody');
    const addUserForm = document.getElementById('addUserForm');
    const editUserForm = document.getElementById('editUserForm');
    const addFormMessage = document.getElementById('addFormMessage');
    const editFormMessage = document.getElementById('editFormMessage');

    /**
     * Muestra un mensaje en un div específico.
     * @param {HTMLElement} messageDiv 
     * @param {string} msg 
     * @param {string} type }
     */
    const showMessage = (messageDiv, msg, type = 'success') => {
        if (messageDiv) {
            messageDiv.textContent = msg;
            messageDiv.className = `alert alert-${type}`;
            messageDiv.style.display = 'block';
            setTimeout(() => messageDiv.style.display = 'none', 5000);
        }
    };

    /**
     * Añade una nueva fila a la tabla de usuarios.
     * @param {Object} userData - Objeto con los datos del nuevo usuario.
     */
    const addRowToTable = (userData) => {
        if (!usersTableBody) return;

        const newRow = document.createElement('tr');
        newRow.setAttribute('data-id', userData.id);
        newRow.innerHTML = `
            <th scope="row">${userData.id}</th>
            <td data-field="nombre_usuario">${userData.username}</td>
            <td data-field="correo">${userData.email}</td>
            <td data-field="rol">${userData.rol}</td>
            <td>
                <button type="button" class="btn btn-warning btn-sm edit-user-btn"
                    data-bs-toggle="modal" data-bs-target="#editUserModal"
                    data-id="${userData.id}"
                    data-username="${userData.username}"
                    data-email="${userData.email}"
                    data-rol="${userData.rol}">
                    Editar
                </button>
                <button type="button" class="btn btn-danger btn-sm delete-user-btn" data-id="${userData.id}">
                    Eliminar
                </button>
            </td>
        `;
        usersTableBody.appendChild(newRow);
    };

    // --- Añadir Usuario ---
    if (addUserForm) {
        addUserForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const submitButton = addUserForm.querySelector('button[type="submit"]');
            if (submitButton) submitButton.disabled = true;

            const formData = new FormData(addUserForm);
            const data = Object.fromEntries(formData.entries());
            data.action = 'add_user';

            if (!data.username || !data.email || !data.password || !data.rol) {
                showMessage(addFormMessage, 'Todos los campos son obligatorios.', 'danger');
                if (submitButton) submitButton.disabled = false;
                return;
            }

            try {
            
                const res = await fetch('../api_admin.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const result = await res.json();

                if (result.success) {
                    showMessage(addFormMessage, result.message);
                    addUserForm.reset();
                    if (result.user_data) {
                        addRowToTable(result.user_data);
                    } else {
                        setTimeout(() => location.reload(), 1500);
                    }
                } else {
                    showMessage(addFormMessage, result.message, 'danger');
                }
            } catch (error) {
                console.error('Error al añadir usuario:', error);
                showMessage(addFormMessage, 'Error de conexión al servidor.', 'danger');
            } finally {
                if (submitButton) submitButton.disabled = false;
            }
        });
    }

    // Editar Usuario
    if (usersTableBody) {
        usersTableBody.addEventListener('click', (e) => {
            if (e.target.classList.contains('edit-user-btn')) {
                const button = e.target;
                const editUserId = document.getElementById('editUserId');
                const editUsername = document.getElementById('editUsername');
                const editEmail = document.getElementById('editEmail');
                const editRol = document.getElementById('editRol');
                const editPassword = document.getElementById('editPassword');

                if (editUserId) editUserId.value = button.dataset.id;
                if (editUsername) editUsername.value = button.dataset.username;
                if (editEmail) editEmail.value = button.dataset.email;
                if (editRol) editRol.value = button.dataset.rol;
                if (editPassword) editPassword.value = '';

                if (editFormMessage) editFormMessage.style.display = 'none';
            }
        });
    }

    // Editar Usuario - Enviar Formulario
    if (editUserForm) {
        editUserForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const submitButton = editUserForm.querySelector('button[type="submit"]');
            if (submitButton) submitButton.disabled = true;

            const formData = new FormData(editUserForm);
            const data = Object.fromEntries(formData.entries());
            data.action = 'edit_user';

            if (data.password === '') {
                delete data.password;
            }

            if (!data.id || !data.username || !data.email || !data.rol) {
                showMessage(editFormMessage, 'Todos los campos obligatorios deben ser completados.', 'danger');
                if (submitButton) submitButton.disabled = false;
                return;
            }

            try {
                const res = await fetch('../api_admin.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const result = await res.json();

                if (result.success) {
                    showMessage(editFormMessage, result.message);
                    const row = usersTableBody.querySelector(`tr[data-id="${data.id}"]`);
                    if (row) {
                        const usernameField = row.querySelector('[data-field="nombre_usuario"]');
                        const emailField = row.querySelector('[data-field="correo"]');
                        const rolField = row.querySelector('[data-field="rol"]');
                        if (usernameField) usernameField.textContent = data.username;
                        if (emailField) emailField.textContent = data.email;
                        if (rolField) rolField.textContent = data.rol;

                        const editBtn = row.querySelector('.edit-user-btn');
                        if (editBtn) {
                            editBtn.dataset.username = data.username;
                            editBtn.dataset.email = data.email;
                            editBtn.dataset.rol = data.rol;
                        }
                    }
                    setTimeout(() => {
                        const editModal = bootstrap.Modal.getInstance(document.getElementById('editUserModal'));
                        if (editModal) editModal.hide();
                    }, 1500);
                } else {
                    showMessage(editFormMessage, result.message, 'danger');
                }
            } catch (error) {
                console.error('Error al editar usuario:', error);
                showMessage(editFormMessage, 'Error de conexión al servidor.', 'danger');
            } finally {
                if (submitButton) submitButton.disabled = false;
            }
        });
    }

    // --- Eliminar Usuario ---
    if (usersTableBody) {
        usersTableBody.addEventListener('click', async (e) => {
            if (e.target.classList.contains('delete-user-btn')) {
                const userId = e.target.dataset.id;

                const deleteButton = e.target;
                deleteButton.disabled = true;

                if (confirm(`¿Estás seguro de que quieres eliminar al usuario con ID ${userId}? Esta acción es irreversible.`)) {
                    try {
                        const res = await fetch('../api_admin.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ action: 'delete_user', id: userId })
                        });
                        const result = await res.json();

                        if (result.success) {
                            showMessage(addFormMessage, result.message);
                            const rowToRemove = usersTableBody.querySelector(`tr[data-id="${userId}"]`);
                            if (rowToRemove) {
                                rowToRemove.remove();
                            }
                        } else {
                            showMessage(addFormMessage, result.message, 'danger');
                        }
                    } catch (error) {
                        console.error('Error al eliminar usuario:', error);
                        showMessage(addFormMessage, 'Error de conexión al servidor.', 'danger');
                    }
                }
                deleteButton.disabled = false;
            }
        });
    }

    // --- Añadir Director ---
    const addDirectorForm = document.getElementById('addDirectorForm');
    const addDirectorMessage = document.getElementById('addDirectorMessage');
    if (addDirectorForm) {
        addDirectorForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitButton = addDirectorForm.querySelector('button[type="submit"]');
            if (submitButton) submitButton.disabled = true;

            const formData = new FormData(addDirectorForm);
            const data = Object.fromEntries(formData.entries());
            data.action = 'add_director';

            try {
                const res = await fetch('../api_admin.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const result = await res.json();
                if (result.success) {
                    showMessage(addDirectorMessage, result.message);
                    addDirectorForm.reset();
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showMessage(addDirectorMessage, result.message, 'danger');
                }
            } catch (error) {
                showMessage(addDirectorMessage, 'Error de conexión al servidor.', 'danger');
            } finally {
                if (submitButton) submitButton.disabled = false;
            }
        });
    }

    // --- Añadir Institución ---
    const addInstitutionForm = document.getElementById('addInstitutionForm');
    const addInstitutionMessage = document.getElementById('addInstitutionMessage');
    if (addInstitutionForm) {
        addInstitutionForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitButton = addInstitutionForm.querySelector('button[type="submit"]');
            if (submitButton) submitButton.disabled = true;

            const formData = new FormData(addInstitutionForm);
            const data = Object.fromEntries(formData.entries());
            data.action = 'add_institution';

            try {
                const res = await fetch('../api_admin.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const result = await res.json();
                if (result.success) {
                    showMessage(addInstitutionMessage, result.message);
                    addInstitutionForm.reset();
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showMessage(addInstitutionMessage, result.message, 'danger');
                }
            } catch (error) {
                showMessage(addInstitutionMessage, 'Error de conexión al servidor.', 'danger');
            } finally {
                if (submitButton) submitButton.disabled = false;
            }
        });
    }
});