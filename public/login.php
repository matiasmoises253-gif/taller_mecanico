<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Multiservicios Cárdenas — Iniciar Sesión</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
    body { display: flex; height: 100vh; }
    .left { background: #003d5c; width: 45%; display: flex; flex-direction: column; justify-content: center; padding: 50px; color: white; }
    .logo { display: flex; align-items: center; gap: 12px; margin-bottom: 40px; }
    .logo-icon { background: #00a8cc; border-radius: 10px; width: 45px; height: 45px; display: flex; align-items: center; justify-content: center; font-size: 22px; font-weight: bold; }
    .logo-name { font-size: 20px; font-weight: 700; }
    .logo-sub { font-size: 11px; color: #aaa; letter-spacing: 1px; }
    .left h1 { font-size: 32px; font-weight: 800; margin-bottom: 16px; line-height: 1.2; }
    .left p { color: #aab; font-size: 14px; margin-bottom: 40px; }
    .features { display: flex; gap: 16px; flex-wrap: wrap; }
    .feature { background: #004d6e; border-radius: 10px; padding: 14px 18px; font-size: 12px; color: #ccd; }
    .right { width: 55%; display: flex; align-items: center; justify-content: center; background: #f5f6fa; }
    .form-box { background: white; border-radius: 16px; padding: 40px; width: 380px; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
    .form-box h2 { font-size: 24px; font-weight: 700; color: #003d5c; margin-bottom: 6px; }
    .form-box p { color: #888; font-size: 13px; margin-bottom: 28px; }
    .form-group { margin-bottom: 18px; }
    .form-group label { display: block; font-size: 12px; font-weight: 600; color: #555; margin-bottom: 6px; }
    .form-group input { width: 100%; border: 1px solid #e5e7eb; border-radius: 8px; padding: 11px 14px; font-size: 14px; outline: none; color: #333; }
    .form-group input:focus { border-color: #00a8cc; }
    .btn-login { width: 100%; background: #00a8cc; color: white; border: none; border-radius: 8px; padding: 13px; font-size: 15px; font-weight: 700; cursor: pointer; margin-top: 6px; }
    .btn-login:hover { background: #c05f10; }
    .error { color: #dc2626; font-size: 13px; margin-top: 10px; display: none; }
    .footer { text-align: center; margin-top: 16px; font-size: 12px; color: #aaa; }
    @media (max-width: 768px) {
      body { flex-direction: column; height: auto; min-height: 100vh; }
      .left { width: 100%; padding: 30px 24px; }
      .left h1 { font-size: 22px; }
      .right { width: 100%; padding: 24px; justify-content: flex-start; }
      .form-box { width: 100%; box-shadow: none; padding: 24px 0; }
    }
  </style>
</head>
<body>
  <div class="left">
    <div class="logo">
      <img src="logo.png" style="width:45px;height:45px;border-radius:10px;object-fit:cover">
      <div>
        <div class="logo-name">Multiservicios Cárdenas</div>
        <div class="logo-sub">ADVANCED MANAGEMENT</div>
      </div>
    </div>
    <h1>Optimiza tu al servicio de tu vehículo.</h1>
    <p>La plataforma integral para el control de inventario, órdenes de servicio y gestión de clientes.</p>
    <div class="features">
      <div class="feature">🚗 Seguimiento de Vehículos</div>
      <div class="feature">📦 Control de Stock</div>
    </div>
  </div>

  <div class="right">
    <div class="form-box">
      <h2>Bienvenido de nuevo</h2>
      <p>Ingresa tus credenciales para acceder al panel de control.</p>
      <div class="form-group">
        <label>Usuario</label>
        <input type="text" id="usuario" placeholder="admin">
      </div>
      <div class="form-group">
        <label>Contraseña</label>
        <input type="password" id="password" placeholder="••••••••">
      </div>
      <button class="btn-login" onclick="login()">Ingresar →</button>
      <div class="error" id="error">Usuario o contraseña incorrectos</div>
      <div class="footer">© <span id="anio"></span> Multiservicios Cárdenas — Conexión segura SSL</div>
    </div>
  </div>

  <script>
    // Año automático
    const anio = new Date().getFullYear();
    document.getElementById('anio').textContent = anio > 2026 ? '2026 - ' + anio : '2026';

    async function login() {
      const usuario = document.getElementById('usuario').value;
      const password = document.getElementById('password').value;

      const res = await fetch('https://tallermecanico-production-e069.up.railway.app/api/login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ usuario, password })
      });

      const data = await res.json();
      if (data.ok) {
        localStorage.setItem('usuario', data.nombre);
        localStorage.setItem('rol', data.rol);
        localStorage.setItem('token', data.token);
        window.location.href = 'http://localhost/taller-mecanico/public/dashboard.php';
      } else {
        document.getElementById('error').style.display = 'block';
      }
    }

    // Permitir Enter para login
    document.addEventListener('keypress', function(e) {
      if (e.key === 'Enter') login();
    });
  </script>
</body>
</html>
