<?php
// Archivo para obtener datos filtrados por fecha (PDD-17)
session_start();
if(!isset($_SESSION['usuario'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

require_once "../config/conexion.php";

$fecha_inicio = isset($_GET['fecha_inicio']) ? mysqli_real_escape_string($conexion, $_GET['fecha_inicio']) : date('Y-m-d');
$fecha_fin = isset($_GET['fecha_fin']) ? mysqli_real_escape_string($conexion, $_GET['fecha_fin']) : date('Y-m-d');
$tipo_reporte = isset($_GET['tipo']) ? mysqli_real_escape_string($conexion, $_GET['tipo']) : 'general';

header('Content-Type: application/json');

$respuesta = array();

switch($tipo_reporte) {
    case 'por_hora':
        // Obtener ventas por hora en el rango de fechas
        $sql = "SELECT 
                    HOUR(horaCompra) as hora,
                    DATE(fechaCompra) as fecha,
                    COUNT(*) as total_ventas,
                    SUM(CASE WHEN id_edad = 1 THEN 1 ELSE 0 END) as ninos,
                    SUM(CASE WHEN id_edad = 2 THEN 1 ELSE 0 END) as adultos,
                    SUM(CASE WHEN id_edad = 3 THEN 1 ELSE 0 END) as adultos_mayores
                FROM ventas 
                WHERE fechaCompra >= '$fecha_inicio' 
                AND fechaCompra <= '$fecha_fin' 
                AND horaCompra IS NOT NULL
                GROUP BY HOUR(horaCompra), DATE(fechaCompra)
                ORDER BY fecha, hora";
        break;
        
    case 'por_edad':
        // Obtener ventas por grupo de edad
        $sql = "SELECT 
                    e.nombre as edad,
                    COUNT(*) as total_ventas,
                    SUM(v.precio) as total_ingresos
                FROM ventas v
                JOIN edades e ON v.id_edad = e.id_edad
                WHERE v.fechaCompra >= '$fecha_inicio' 
                AND v.fechaCompra <= '$fecha_fin'
                GROUP BY v.id_edad
                ORDER BY total_ventas DESC";
        break;
        
    case 'fechas_pico':
        // Obtener fechas con mayor volumen ordenadas
        $sql = "SELECT 
                    fechaCompra,
                    COUNT(*) as total_ventas,
                    SUM(precio) as total_ingresos
                FROM ventas
                WHERE fechaCompra >= '$fecha_inicio' 
                AND fechaCompra <= '$fecha_fin'
                GROUP BY fechaCompra
                ORDER BY total_ventas DESC
                LIMIT 20";
        break;
        
    case 'por_ticket':
        // Obtener ventas por tipo de ticket ordenadas (PDD-35)
        $sql = "SELECT 
                    t.nombre as ticket,
                    COUNT(*) as total_vendidos,
                    SUM(v.precio) as total_ingresos
                FROM ventas v
                JOIN tickets t ON v.id_ticket = t.id_ticket
                WHERE v.fechaCompra >= '$fecha_inicio' 
                AND v.fechaCompra <= '$fecha_fin'
                GROUP BY v.id_ticket
                ORDER BY total_vendidos DESC";
        break;
        
    default:
        // Reporte general
        $sql = "SELECT 
                    DATE(fechaCompra) as fecha,
                    COUNT(*) as total_ventas,
                    SUM(precio) as total_ingresos,
                    SUM(CASE WHEN id_edad = 1 THEN 1 ELSE 0 END) as ninos,
                    SUM(CASE WHEN id_edad = 2 THEN 1 ELSE 0 END) as adultos,
                    SUM(CASE WHEN id_edad = 3 THEN 1 ELSE 0 END) as adultos_mayores
                FROM ventas 
                WHERE fechaCompra >= '$fecha_inicio' 
                AND fechaCompra <= '$fecha_fin'
                GROUP BY DATE(fechaCompra)
                ORDER BY fecha";
}

$result = mysqli_query($conexion, $sql);
$datos = array();

while($row = mysqli_fetch_assoc($result)) {
    $datos[] = $row;
}

$respuesta['success'] = true;
$respuesta['datos'] = $datos;
$respuesta['fecha_inicio'] = $fecha_inicio;
$respuesta['fecha_fin'] = $fecha_fin;
$respuesta['tipo'] = $tipo_reporte;
$respuesta['total_registros'] = count($datos);

echo json_encode($respuesta);
?>
