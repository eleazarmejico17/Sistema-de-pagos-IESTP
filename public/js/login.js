function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    toast.textContent = message;
    toast.className = `toast show ${type}`;
    setTimeout(() => {
        toast.className = '';
        toast.textContent = '';
    }, 3000);
}

async function checkConnection() {
    try {
        const res = await fetch('auth.php', { method: 'GET' });
        const data = await res.json();
        const statusEl = document.getElementById('db-status');
        if (data.success) {
            statusEl.textContent = 'Conexión: OK';
            statusEl.className = 'text-green-300 text-center';
        } else {
            statusEl.textContent = 'Conexión: Fallida';
            statusEl.className = 'text-red-300 text-center';
        }
    } catch (err) {
        const statusEl = document.getElementById('db-status');
        statusEl.textContent = 'Conexión: Error';
        statusEl.className = 'text-red-300 text-center';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    checkConnection();

    document.getElementById('loginForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        const loginBtn = document.getElementById('loginBtn');

        loginBtn.disabled = true;
        loginBtn.textContent = 'Cargando...';

        try {
            const res = await fetch('auth.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ usuario: email, contrasena: password })
            });

            const data = await res.json();

            if (res.ok && data.success) {
                showToast('¡Bienvenido! Redirigiendo...', 'success');
                setTimeout(() => {
                    window.location.href = data.redirect || 'views/dashboard-usuario.php';
                }, 800);
            } else {
                showToast(data.message || 'Credenciales incorrectas', 'error');
                loginBtn.disabled = false;
                loginBtn.textContent = 'Iniciar Sesión';
            }
        } catch (err) {
            showToast('Error de red. Intente de nuevo.', 'error');
            loginBtn.disabled = false;
            loginBtn.textContent = 'Iniciar Sesión';
        }
    });
});