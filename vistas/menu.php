<?php 
// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once "dependencias.php"; 
?>

<!DOCTYPE html>
<html>
<head>
  <title>Menu Principal</title>
  <style>
    .navbar-brand {
      position: absolute;
      left: 15px;
      top: 0;
      z-index: 1000;
    }
    .logo {
      height: 70px;
      width: auto;
    }
    
    /* CRITICAL FIX: Force menu items to stay inline */
    @media (min-width: 768px) {
      .navbar-collapse {
        display: flex !important;
        justify-content: flex-end !important;
      }
      
      .navbar-nav.navbar-right {
        display: flex !important;
        flex-direction: row !important;
        margin: 0 !important;
        float: none !important;
      }
      
      .navbar-nav.navbar-right > li {
        display: flex !important;
        align-items: center !important;
        float: none !important;
      }
      
      .user-menu {
        white-space: nowrap !important;
      }
    }
  </style>
</head>
<body>
  <div id="nav">
    <div class="navbar navbar-inverse navbar-fixed-top dynamic-nav" data-spy="affix" data-offset-top="100">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>

        </div>
        <div id="navbar" class="collapse navbar-collapse">
          <ul class="nav navbar-nav navbar-right nav-animated">
            <?php 
            $currentPage = basename($_SERVER['PHP_SELF']);
            ?>
            <li class="nav-item <?php echo ($currentPage == 'inicio.php') ? 'active' : ''; ?>">
              <a href="inicio.php"><span class="glyphicon glyphicon-home"></span> Inicio</a>
            </li>
            
            <li class="dropdown nav-item <?php echo ($currentPage == 'categorias.php' || $currentPage == 'tickets.php') ? 'active' : ''; ?>">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                <span class="glyphicon glyphicon-list-alt"></span> Gestionar <span class="caret"></span>
              </a>              <ul class="dropdown-menu animated-dropdown">
                <li><a href="categorias.php">Categorias</a></li>
                <li><a href="tickets.php">Tickets</a></li>
              </ul>            </li>

            <?php if(isset($_SESSION['rol']) && $_SESSION['rol'] == "administrador"): ?>
            <li class="nav-item <?php echo ($currentPage == 'dashboard.php') ? 'active' : ''; ?>">
              <a href="dashboard.php"><span class="glyphicon glyphicon-dashboard"></span> Dashboard</a>
            </li>
            <?php endif; ?>

            <?php if(isset($_SESSION['rol']) && $_SESSION['rol'] == "administrador"): ?>
            <li class="nav-item <?php echo ($currentPage == 'reportes_avanzados.php') ? 'active' : ''; ?>">
              <a href="reportes_avanzados.php"><span class="glyphicon glyphicon-stats"></span> Reportes Avanzados</a>
            </li>
            <?php endif; ?>

            <?php if(isset($_SESSION['rol']) && $_SESSION['rol'] == "administrador"): ?>
              <li class="nav-item">
                <a href="usuarios.php"><span class="glyphicon glyphicon-user"></span> Administrar usuarios</a>
              </li>
            <?php endif; ?>

            <?php if(isset($_SESSION['rol']) && $_SESSION['rol'] == "administrador"): ?>
            <li class="nav-item">
              <a href="edades.php"><span class="glyphicon glyphicon-user"></span> Edades</a>
            </li>
            <?php endif; ?>
            <li class="nav-item">
              <a href="ventas.php"><span class="glyphicon glyphicon-usd"></span> Seccion Ventas</a>
            </li>
            
            <li class="dropdown nav-item">              <a href="#" class="dropdown-toggle user-menu" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                <span class="glyphicon glyphicon-user"></span> 
                Usuario: <?php echo $_SESSION['usuario']; ?> 
                <?php if(isset($_SESSION['rol'])): ?>
                  (<?php echo ucfirst($_SESSION['rol']); ?>)
                <?php endif; ?>
                <span class="caret"></span>
              </a>
              <ul class="dropdown-menu animated-dropdown">
                <li>
                  <a href="../procesos/salir.php">
                    <span class="glyphicon glyphicon-off"></span> Salir
                  </a>
                </li>
              </ul>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</body>
</html>

<script type="text/javascript">
$(document).ready(function() {
  // Remove active link highlighting code since we're now handling it with PHP
  
  // Add hover effect for nav items
  $('.nav-item').hover(
    function() {
      $(this).find('a').addClass('nav-item-hover');
    },
    function() {
      $(this).find('a').removeClass('nav-item-hover');
    }
  );
});
</script>