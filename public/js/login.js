// Mock users database (will be replaced with real backend)
const mockUsers = [
    {
        id: '1',
        name: 'Usuario Demo',
        email: 'demo@example.com',
        password: 'demo123'
    }
];

function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    toast.textContent = message;
    toast.className = `toast show ${type}`;
    
    setTimeout(() => {
        toast.className = 'toast';
    }, 3000);
}

function mockLogin(email, password) {
    const user = mockUsers.find(u => u.email === email && u.password === password);
    
    if (user) {
        const { password, ...userWithoutPassword } = user;
        return {
            success: true,
            token: 'mock-jwt-token-' + Date.now(),
            user: userWithoutPassword
        };
    }
    
    return {
        success: false,
        message: 'Email o contraseña incorrectos'
    };
}

document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const loginBtn = document.getElementById('loginBtn');
    
    loginBtn.disabled = true;
    loginBtn.textContent = 'Cargando...';
    
    setTimeout(() => {
        const result = mockLogin(email, password);
        
        if (result.success) {
            localStorage.setItem('token', result.token);
            localStorage.setItem('user', JSON.stringify(result.user));
            
            showToast('¡Bienvenido! Has iniciado sesión correctamente', 'success');
            
            setTimeout(() => {
                window.location.href = 'panel-admin.html';
            }, 1000);
        } else {
            showToast(result.message, 'error');
            loginBtn.disabled = false;
            loginBtn.textContent = 'Iniciar Sesión';
        }
    }, 800);
});