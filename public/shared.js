// shared.js — Funciones compartidas por todas las páginas del sistema
// URL base de tu servidor en Railway
const BASE_URL = 'https://tallermecanico-production-e069.up.railway.app';

function toggleMenu() {
  document.querySelector('.sidebar').classList.toggle('abierto');
}

function toggleUserMenu() {
  const menu = document.getElementById('userMenu');
  menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
}

function cerrarSesion() {
  localStorage.clear();
  location.href = '/login.php';
}

// ── Token JWT ────────────────────────────────────────────────────────────────
function getToken() {
  return localStorage.getItem('token') || '';
}

// Fetch autenticado — agrega el token automáticamente a todas las peticiones
// Fetch autenticado — agrega el token automáticamente a todas las peticiones
function apiFetch(url, options = {}) {
  if (url.startsWith('http://localhost:3000')) {
    url = url.replace('http://localhost:3000', BASE_URL);
  }
  options.headers = options.headers || {};
  options.headers['Authorization'] = 'Bearer ' + getToken();
  options.headers['Content-Type'] = options.headers['Content-Type'] || 'application/json';
  return fetch(url, options);
}

// ── Protección de rutas ──────────────────────────────────────────────────────
function verificarSesion() {
  const token = getToken();
  if (!token) {
    location.href = '/login.php';
    return false;
  }
  // Verificar que el token no haya expirado (decodificamos el payload sin librería)
  try {
    const payload = JSON.parse(atob(token.split('.')[1]));
    if (payload.exp && Date.now() / 1000 > payload.exp) {
      localStorage.clear();
      location.href = '/login.php';
      return false;
    }
  } catch (e) {
    localStorage.clear();
    location.href = '/login.php';
    return false;
  }
  return true;
}

// Relaciona cada elemento del menú con su "módulo" de permisos
const MODULOS_MENU = {
  navDashboard: 'dashboard', navClientes: 'clientes', navVehiculos: 'vehiculos',
  navOrdenes: 'ordenes', navInventario: 'inventario', navReportes: 'reportes',
  navUsuarios: 'usuarios', menuPerfil: 'perfil'
};

function cargarUsuario() {
  // Verificar sesión antes de mostrar cualquier contenido
  if (!verificarSesion()) return;

  const nombre = localStorage.getItem('usuario') || 'Admin';
  const rol = localStorage.getItem('rol') || 'Gerente';
  document.getElementById('userName').textContent = nombre;
  document.getElementById('userRole').textContent = rol;
  document.getElementById('userAv').textContent = nombre.charAt(0).toUpperCase();

  aplicarPermisosMenu(rol);
  cargarDatosSidebar();
}

async function aplicarPermisosMenu(rol) {
  const cacheKey = `permisos_${rol}`;
  const cache = localStorage.getItem(cacheKey);
  if (cache) {
    try { mostrarPermisosEnMenu(JSON.parse(cache)); } catch (e) {}
  }

  let permisos;
  try {
    const data = await apiFetch(`${BASE_URL}/permisos/${encodeURIComponent(rol)}`).then(r => r.json());
    permisos = data.permisos;
    localStorage.setItem(cacheKey, JSON.stringify(permisos));
  } catch (e) {
    if (cache) return;
    const esAdmin = ['gerente','admin','administrador'].includes((rol||'').toLowerCase());
    permisos = esAdmin
      ? ['dashboard','clientes','vehiculos','ordenes','inventario','reportes','usuarios','perfil']
      : ['dashboard','clientes','vehiculos','ordenes','inventario'];
  }

  mostrarPermisosEnMenu(permisos);
}

function mostrarPermisosEnMenu(permisos) {
  Object.entries(MODULOS_MENU).forEach(([id, modulo]) => {
    const el = document.getElementById(id);
    if (!el) return;
    el.style.display = permisos.includes(modulo) ? 'flex' : 'none';
  });
}

async function cargarDatosSidebar() {
  try {
    const datos = await apiFetch(`${BASE_URL}/perfil`).then(r => r.json());
    const elNombre = document.getElementById('sidebarNombre');
    const elCiudad = document.getElementById('sidebarCiudad');
    if (elNombre && datos.nombre) elNombre.textContent = datos.nombre;
    if (elCiudad && datos.ciudad) elCiudad.textContent = datos.ciudad;
  } catch (e) {}
}

// ── Validación de formularios ───────────────────────────────────────────────
function marcarError(id, mensaje) {
  const campo = document.getElementById(id);
  if (!campo) return;
  campo.classList.add('campo-error');
  let msg = campo.parentElement.querySelector('.campo-error-msg');
  if (!msg) {
    msg = document.createElement('span');
    msg.className = 'campo-error-msg';
    campo.parentElement.appendChild(msg);
  }
  msg.textContent = mensaje;
}

function limpiarErrores(ids) {
  ids.forEach(id => {
    const campo = document.getElementById(id);
    if (!campo) return;
    campo.classList.remove('campo-error');
    const msg = campo.parentElement.querySelector('.campo-error-msg');
    if (msg) msg.remove();
  });
}