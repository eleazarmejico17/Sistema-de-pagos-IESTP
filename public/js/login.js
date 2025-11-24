function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    if (!toast) return;
    toast.textContent = message;
    toast.className = `toast show ${type}`;
    setTimeout(() => {
        toast.className = '';
        toast.textContent = '';
    }, 3000);
}

// Credenciales quemadas por rol
const usuarios = {
    "admin@institutocajas.edu.pe": { password: "admin123", rol: "admin" },
    "bienestar@institutocajas.edu.pe": { password: "bien123", rol: "bienestar" },
    "direccion@institutocajas.edu.pe": { password: "dir123", rol: "direccion" },
    "usuario@institutocajas.edu.pe": { password: "user123", rol: "usuario" }
};

// Simula login sin BD
async function loginSimulado(email, password) {
    const user = usuarios[email.trim().toLowerCase()];
    if (!user) return { success: false, message: "Usuario no encontrado" };
    if (user.password !== password) return { success: false, message: "Contraseña incorrecta" };
    return { success: true, rol: user.rol };
}

// Redirige según rol
function redirigirPorRol(rol) {
    const rutas = {
        admin: "../../views/dashboard-admin.php",
        bienestar: "../../views/dashboard-bienestar.php",
        direccion: "../../views/dashboard-direccion.php",
        usuario: "../../views/dashboard-usuario.php"
    };
    return rutas[rol] || "views/dashboard-usuario.php";
}

// Simula conexión sin BD
async function checkConnection() {
    const statusEl = document.getElementById('db-status');
    if (!statusEl) return;

    try {
        const res = await fetch('check-connection.php');
        if (!res.ok) throw new Error("HTTP error");
        const data = await res.json();
        if (data.success) {
            statusEl.textContent = 'Conexión: OK';
            statusEl.className = 'text-green-300 text-center';
        } else {
            statusEl.textContent = 'Conexión: Fallida';
            statusEl.className = 'text-red-300 text-center';
        }
    } catch (err) {
        statusEl.textContent = 'Conexión: Error';
        statusEl.className = 'text-red-300 text-center';
    }
}

// Inicio
document.addEventListener('DOMContentLoaded', () => {
    checkConnection();

    const form = document.getElementById('loginForm');
    if (!form) return;

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        const email = document.getElementById('email')?.value.trim();
        const password = document.getElementById('password')?.value;
        const loginBtn = document.getElementById('loginBtn');

        if (!email || !password || !loginBtn) return;

        loginBtn.disabled = true;
        loginBtn.textContent = 'Cargando...';

        // 🔍 TRAZAS
        console.log('📩 email:', email);
        console.log('🔑 password:', password);

        const resultado = await loginSimulado(email, password);
        console.log('📡 resultado:', resultado);

        if (resultado.success) {
            showToast('¡Bienvenido! Redirigiendo...', 'success');

            const rol = resultado.rol;
            console.log('🧭 rol recibido:', rol);

            const url = redirigirPorRol(rol);
            console.log('🎯 URL a la que voy:', url);

            // 🚀 REDIRIGIR INMEDIATAMENTE
            window.location.href = url;
        } else {
            showToast(resultado.message || 'Credenciales incorrectas', 'error');
            loginBtn.disabled = false;
            loginBtn.textContent = 'Iniciar Sesión';
        }
    });
});