<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Multiservicios Cárdenas - Perfil del Taller</title>
  <link rel="stylesheet" href="global.css">
  <style>
    .perfil-card { background:white; border-radius:16px; border:1px solid #e5e7eb; padding:32px; max-width:600px; }
    .perfil-logo-wrap { display:flex; align-items:center; gap:20px; margin-bottom:28px; }
    .perfil-logo-img { width:80px; height:80px; border-radius:12px; object-fit:cover; border:2px solid #e5e7eb; }
    .perfil-logo-placeholder { width:80px; height:80px; border-radius:12px; background:#f3f4f6; display:flex; align-items:center; justify-content:center; font-size:32px; border:2px dashed #d1d5db; cursor:pointer; }
    .perfil-logo-label { font-size:12px; color:#888; margin-top:6px; }
    .btn-logo { background:#f3f4f6; border:1px solid #e5e7eb; border-radius:8px; padding:7px 14px; font-size:12px; cursor:pointer; margin-top:6px; }
    .btn-logo:hover { background:#e5e7eb; }
    .msg-ok-perfil { background:#f0fdf4; color:#15803d; border:1px solid #bbf7d0; border-radius:8px; padding:10px 14px; font-size:13px; margin-bottom:14px; display:none; }
  </style>
</head>
<body>
<?php $paginaActiva = 'perfil'; include 'sidebar.php'; ?>

  <div class="main">
    <div class="topbar">
      <button class="menu-btn" onclick="toggleMenu()">☰</button>
      <span style="font-size:14px;color:#555">Perfil del Taller</span>
    </div>
    <div class="content">
      <h1>Perfil del Taller</h1>
      <p class="subtitle">Configura los datos de tu taller mecánico.</p>

      <div class="perfil-card">
        <div class="msg-ok-perfil" id="msgOk">✅ Datos actualizados correctamente</div>

        <!-- Logo -->
        <div class="perfil-logo-wrap">
          <div>
            <img id="logoPreview" src="logo.png" class="perfil-logo-img" onerror="this.style.display='none';document.getElementById('logoPlaceholder').style.display='flex'">
            <div id="logoPlaceholder" class="perfil-logo-placeholder" style="display:none" onclick="document.getElementById('inputLogo').click()">🏪</div>
            <div class="perfil-logo-label">Logo del taller</div>
          </div>
          <div>
            <div style="font-weight:700;font-size:15px" id="nombrePreview">Taller</div>
            <div style="font-size:12px;color:#888;margin-top:2px" id="ciudadPreview">—</div>
            <input type="file" id="inputLogo" accept="image/*" style="display:none" onchange="previewLogo(event)">
            <button class="btn-logo" onclick="document.getElementById('inputLogo').click()">📷 Cambiar logo</button>
          </div>
        </div>

        <!-- Datos -->
        <div class="form-group"><label>NOMBRE DEL TALLER</label><input type="text" id="fNombre" placeholder="Multiservicios Cárdenas"></div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
          <div class="form-group"><label>TELÉFONO</label><input type="text" id="fTelefono" placeholder="+51 987 654 321"></div>
          <div class="form-group"><label>EMAIL DE CONTACTO</label><input type="email" id="fEmail" placeholder="taller@correo.com"></div>
        </div>
        <div class="form-group"><label>DIRECCIÓN</label><input type="text" id="fDireccion" placeholder="Av. Principal 123, Chincha Alta"></div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
          <div class="form-group"><label>CIUDAD</label><input type="text" id="fCiudad" placeholder="Chincha Alta"></div>
          <div class="form-group"><label>RUC / IDENTIFICACIÓN</label><input type="text" id="fRuc" placeholder="20123456789"></div>
        </div>
        <div class="form-group"><label>HORARIO DE ATENCIÓN</label><input type="text" id="fHorario" placeholder="Lun-Vie 8:00-18:00, Sab 8:00-13:00"></div>

        <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:8px">
          <button class="btn-cancel" onclick="cargarPerfil()">Descartar cambios</button>
          <button class="btn-primary" onclick="guardarPerfil()">💾 Guardar cambios</button>
        </div>
      </div>
    </div>
  </div>

  <script src="shared.js"></script>
  <script>
    cargarUsuario();

    async function cargarPerfil() {
      const datos = await apiFetch('http://localhost:3000/perfil').then(r => r.json());
      document.getElementById('fNombre').value = datos.nombre || '';
      document.getElementById('fTelefono').value = datos.telefono || '';
      document.getElementById('fEmail').value = datos.email || '';
      document.getElementById('fDireccion').value = datos.direccion || '';
      document.getElementById('fCiudad').value = datos.ciudad || '';
      document.getElementById('fRuc').value = datos.ruc || '';
      document.getElementById('fHorario').value = datos.horario || '';
      document.getElementById('nombrePreview').textContent = datos.nombre || 'Taller';
      document.getElementById('ciudadPreview').textContent = datos.ciudad || '—';
    }

    async function guardarPerfil() {
      const body = {
        nombre: document.getElementById('fNombre').value,
        telefono: document.getElementById('fTelefono').value,
        email: document.getElementById('fEmail').value,
        direccion: document.getElementById('fDireccion').value,
        ciudad: document.getElementById('fCiudad').value,
        ruc: document.getElementById('fRuc').value,
        horario: document.getElementById('fHorario').value,
      };
      const res = await apiFetch('http://localhost:3000/perfil', {
        method: 'PUT', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(body)
      });
      const data = await res.json();
      if (data.ok) {
        document.getElementById('msgOk').style.display = 'block';
        // Actualizar preview dentro de la card
        document.getElementById('nombrePreview').textContent = body.nombre || 'Taller';
        document.getElementById('ciudadPreview').textContent = body.ciudad || '—';
        // Actualizar sidebar en vivo
        const sidebarNombre = document.getElementById('sidebarNombre');
        const sidebarCiudad = document.getElementById('sidebarCiudad');
        if (sidebarNombre) sidebarNombre.textContent = body.nombre || '';
        if (sidebarCiudad) sidebarCiudad.textContent = body.ciudad || '';
        setTimeout(() => document.getElementById('msgOk').style.display = 'none', 3000);
      }
    }

    function previewLogo(e) {
      const file = e.target.files[0];
      if (!file) return;
      const reader = new FileReader();
      reader.onload = ev => {
        document.getElementById('logoPreview').src = ev.target.result;
        document.getElementById('logoPreview').style.display = '';
        document.getElementById('logoPlaceholder').style.display = 'none';
        // Actualizar logo del sidebar en vivo
        const sidebarLogo = document.getElementById('sidebarLogo');
        if (sidebarLogo) sidebarLogo.src = ev.target.result;
      };
      reader.readAsDataURL(file);
    }

    cargarPerfil();
  </script>
</body>
</html>
