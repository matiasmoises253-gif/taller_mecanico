<?php
// sidebar.php — Se incluye en todas las páginas del sistema
// $paginaActiva se define en cada página antes de incluir este archivo
?>
<div class="sidebar">
  <div class="brand">
    <img src="logo.png" id="sidebarLogo" onclick="location.href='http://localhost/taller-mecanico/public/dashboard.php'" style="width:40px;height:40px;border-radius:8px;cursor:pointer;object-fit:cover">
    <div>
      <div class="brand-name" id="sidebarNombre">Multiservicios Cárdenas</div>
      <div class="brand-sub" id="sidebarCiudad">Chincha Alta, Ica</div>
    </div>
  </div>
  <nav>
    <div class="nav-item <?php echo ($paginaActiva === 'dashboard') ? 'active' : ''; ?>" id="navDashboard" onclick="location.href='http://localhost/taller-mecanico/public/dashboard.php'">📊 Panel</div>
    <div class="nav-item <?php echo ($paginaActiva === 'clientes') ? 'active' : ''; ?>" id="navClientes" onclick="location.href='http://localhost/taller-mecanico/public/clientes.php'">👥 Clientes</div>
    <div class="nav-item <?php echo ($paginaActiva === 'vehiculos') ? 'active' : ''; ?>" id="navVehiculos" onclick="location.href='http://localhost/taller-mecanico/public/vehiculos.php'">🚗 Vehículos</div>
    <div class="nav-item <?php echo ($paginaActiva === 'ordenes') ? 'active' : ''; ?>" id="navOrdenes" onclick="location.href='http://localhost/taller-mecanico/public/ordenes.php'">📋 Órdenes de Trabajo</div>
    <div class="nav-item <?php echo ($paginaActiva === 'inventario') ? 'active' : ''; ?>" id="navInventario" onclick="location.href='http://localhost/taller-mecanico/public/inventario.php'">📦 Inventario</div>
    <div class="nav-item <?php echo ($paginaActiva === 'reportes') ? 'active' : ''; ?>" id="navReportes" onclick="location.href='http://localhost/taller-mecanico/public/reportes.php'">📈 Reportes</div>
    <!-- Estos dos se muestran u ocultan según los permisos configurados para el rol del usuario -->
    <div class="nav-item <?php echo ($paginaActiva === 'usuarios') ? 'active' : ''; ?>" id="navUsuarios" onclick="location.href='http://localhost/taller-mecanico/public/usuarios.php'" style="display:none">👤 Perfil de Usuarios</div>
  </nav>
  <div class="sidebar-user">
    <div class="sidebar-user-inner" onclick="toggleUserMenu()">
      <div class="user-av" id="userAv">AU</div>
      <div style="flex:1">
        <div class="user-name" id="userName">Admin</div>
        <div class="user-role" id="userRole">Gerente</div>
      </div>
      <span style="color:#8899bb;font-size:10px">▲</span>
    </div>
    <div class="user-menu" id="userMenu">
      <div class="user-menu-header">Sesión activa</div>
      <div class="user-menu-item" id="menuPerfil" onclick="location.href='http://localhost/taller-mecanico/public/perfil.php'" style="display:none">⚙️ Perfil del Taller</div>
      <div class="user-menu-item" onclick="cerrarSesion()">🚪 Cerrar Sesión</div>
    </div>
  </div>
</div>
