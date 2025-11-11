<?php 
    session_start();
    if(isset($_SESSION['usuario']) and isset($_SESSION['rol']) and $_SESSION['rol']=='administrador'){
        require_once "../config/conexion.php";
        $fecha_actual = date('Y-m-d');
        
        // Query para obtener total de tickets vendidos hoy
        $sql = "SELECT COUNT(*) as total_ventas FROM ventas WHERE fechaCompra = '$fecha_actual'";
        $result = mysqli_query($conexion, $sql);
        $row = mysqli_fetch_assoc($result);
        $total_ventas = $row['total_ventas'];
        
        // Query para obtener el total de dinero recaudado hoy
        $sql_revenue = "SELECT SUM(precio) as total_dinero FROM ventas WHERE fechaCompra = '$fecha_actual'";
        $result_revenue = mysqli_query($conexion, $sql_revenue);
        $row_revenue = mysqli_fetch_assoc($result_revenue);
        $total_dinero = $row_revenue['total_dinero'];
        
        // Query para obtener total de tickets vendidos por edades
        $sql_children = "SELECT COUNT(*) as total_ninos FROM ventas WHERE id_edad = 1 AND fechaCompra = '$fecha_actual'";
        $result_children = mysqli_query($conexion, $sql_children);
        $row_children = mysqli_fetch_assoc($result_children);
        $total_ninos = $row_children['total_ninos'];
        
        $sql_adults = "SELECT COUNT(*) as total_adultos FROM ventas WHERE id_edad = 2 AND fechaCompra = '$fecha_actual'";
        $result_adults = mysqli_query($conexion, $sql_adults);
        $row_adults = mysqli_fetch_assoc($result_adults);
        $total_adultos = $row_adults['total_adultos'];
        
        $sql_seniors = "SELECT COUNT(*) as total_adultos_mayores FROM ventas WHERE id_edad = 3 AND fechaCompra = '$fecha_actual'";
        $result_seniors = mysqli_query($conexion, $sql_seniors);
        $row_seniors = mysqli_fetch_assoc($result_seniors);
        $total_adultos_mayores = $row_seniors['total_adultos_mayores'];

        // Queries para totales por período
        // Total semana
        $sql_semana = "SELECT COUNT(*) as total_semana, SUM(precio) as dinero_semana 
                       FROM ventas 
                       WHERE fechaCompra >= DATE_SUB('$fecha_actual', INTERVAL 7 DAY)";
        $result_semana = mysqli_query($conexion, $sql_semana);
        $row_semana = mysqli_fetch_assoc($result_semana);
        $total_semana = $row_semana['total_semana'];
        $dinero_semana = $row_semana['dinero_semana'];

        // Total mes
        $sql_mes = "SELECT COUNT(*) as total_mes, SUM(precio) as dinero_mes 
                    FROM ventas 
                    WHERE MONTH(fechaCompra) = MONTH('$fecha_actual') 
                    AND YEAR(fechaCompra) = YEAR('$fecha_actual')";
        $result_mes = mysqli_query($conexion, $sql_mes);
        $row_mes = mysqli_fetch_assoc($result_mes);
        $total_mes = $row_mes['total_mes'];
        $dinero_mes = $row_mes['dinero_mes'];

        $capacidad_total = 10000;
        $tickets_disponibles = $capacidad_total - $total_ventas;

        // Query para obtener los tickets más vendidos
        $sql_top_tickets = "SELECT t.nombre, COUNT(*) as total_vendidos
                            FROM ventas v
                            JOIN tickets t ON v.id_ticket = t.id_ticket
                            GROUP BY t.id_ticket
                            ORDER BY total_vendidos DESC
                            LIMIT 5";
        $result_top_tickets = mysqli_query($conexion, $sql_top_tickets);
        $top_tickets = array();
        $ticket_names = array();
        $ticket_counts = array();
        
        while($row = mysqli_fetch_assoc($result_top_tickets)) {
            $ticket_names[] = $row['nombre'];
            $ticket_counts[] = $row['total_vendidos'];
        }

        // Query para obtener ventas por hora del día (PDD-54)
        $sql_por_hora = "SELECT 
                            HOUR(horaCompra) as hora,
                            COUNT(*) as total_ventas,
                            SUM(CASE WHEN id_edad = 1 THEN 1 ELSE 0 END) as ninos,
                            SUM(CASE WHEN id_edad = 2 THEN 1 ELSE 0 END) as adultos,
                            SUM(CASE WHEN id_edad = 3 THEN 1 ELSE 0 END) as adultos_mayores
                         FROM ventas 
                         WHERE fechaCompra = '$fecha_actual' AND horaCompra IS NOT NULL
                         GROUP BY HOUR(horaCompra)
                         ORDER BY hora";
        $result_por_hora = mysqli_query($conexion, $sql_por_hora);
        $ventas_por_hora = array();
        while($row = mysqli_fetch_assoc($result_por_hora)) {
            $ventas_por_hora[] = $row;
        }

        // Query para obtener fechas con mayor volumen de ventas (PDD-36, PDD-55)
        $sql_fechas_pico = "SELECT 
                                fechaCompra,
                                COUNT(*) as total_ventas,
                                SUM(precio) as total_ingresos
                            FROM ventas
                            WHERE fechaCompra >= DATE_SUB('$fecha_actual', INTERVAL 30 DAY)
                            GROUP BY fechaCompra
                            ORDER BY total_ventas DESC
                            LIMIT 10";
        $result_fechas_pico = mysqli_query($conexion, $sql_fechas_pico);
        $fechas_pico = array();
        while($row = mysqli_fetch_assoc($result_fechas_pico)) {
            $fechas_pico[] = $row;
        }
?>

<!DOCTYPE html>
<html>
<head>
    <title>inicio</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <?php require_once "menu.php"; ?>
    <script>
        function updateDateTime() {
            const now = new Date();
            const options = { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            };
            document.getElementById('live-datetime').innerHTML = now.toLocaleDateString('es-ES', options);
        }

        // Update every second
        setInterval(updateDateTime, 1000);
    </script>
    <style>
        .pdf-button {
            background-color: #dc3545;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 20px 0;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        .pdf-button:hover {
            background-color: #c82333;
        }
    </style>
    
    <!-- Agregar DateRangePicker y Chart.js -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <script src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        .date-range-container {
            margin: 20px 0;
            padding: 15px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .chart-container {
            margin: 20px 0;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        #daterange {
            padding: 8px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            min-width: 200px;
        }
        
        .tables-grid {
            display: flex;
            gap: 20px;
            margin: 20px 0;
        }
        
        .tables-grid .table-section {
            flex: 1;
            margin: 0; /* Removing individual margins */
        }
        
        /* Ajustar ancho de las tablas para que se ajusten mejor */
        .data-table {
            width: 100%;
        }
        
        /* Asegurar que las tablas tengan el mismo alto */
        .table-section .data-table tbody {
            height: calc(100% - 50px);
        }

        .chart-grid {
            display: flex;
            gap: 20px;
            margin: 20px 0;
        }

        .chart-section {
            flex: 1;
        }

        .chart-container-small {
            height: 300px; /* Ajustar la altura según sea necesario */
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="dashboard-header">
            <div class="header-content">
                <div class="title-section">
                    <i class="fas fa-tachometer-alt dashboard-icon"></i>
                    <h1>Panel de Control</h1>
                </div>
                <p class="current-date" id="live-datetime"></p>
                <button onclick="generarPDF()" class="pdf-button">
                    <i class="fas fa-file-pdf"></i>
                    Generar Reporte PDF
                </button>
            </div>
        </div>
        
        <div class="dashboard-content">
            <div class="card primary">
                <div class="card-body">
                    <div class="card-icon">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                    <div class="card-info">
                        <h5 class="card-title">Tickets Vendidos Hoy</h5>
                        <h1 class="display-4"><?php echo $total_ventas; ?></h1>
                        <div class="card-trend">
                            <i class="fas fa-chart-line"></i>
                            <span>Ventas del día</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card success">
                <div class="card-body">
                    <div class="card-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="card-info">
                        <h5 class="card-title">Total en Dinero</h5>
                        <h1 class="display-4">$<?php echo number_format($total_dinero, 2); ?></h1>
                        <div class="card-trend">
                            <i class="fas fa-chart-bar"></i>
                            <span>Ingresos del día</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card info">
                <div class="card-body">
                    <div class="card-icon">
                        <i class="fas fa-child"></i>
                    </div>
                    <div class="card-info">
                        <h5 class="card-title">Tickets Niños</h5>
                        <h1 class="display-4"><?php echo $total_ninos; ?></h1>
                        <div class="card-trend">
                            <i class="fas fa-users"></i>
                            <span>Categoría infantil</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card warning">
                <div class="card-body">
                    <div class="card-icon">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="card-info">
                        <h5 class="card-title">Tickets Adultos</h5>
                        <h1 class="display-4"><?php echo $total_adultos; ?></h1>
                        <div class="card-trend">
                            <i class="fas fa-users"></i>
                            <span>Categoría adultos</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card danger">
                <div class="card-body">
                    <div class="card-icon">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div class="card-info">
                        <h5 class="card-title">Tickets Adultos Mayores</h5>
                        <h1 class="display-4"><?php echo $total_adultos_mayores; ?></h1>
                        <div class="card-trend">
                            <i class="fas fa-users"></i>
                            <span>Categoría adultos mayores</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tables-grid">
            <div class="table-section">
                <h2 class="section-title">Análisis de Ventas por Edad</h2>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Edad</th>
                                <th>Cantidad</th>
                                <th>Porcentaje</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $total_general = $total_ninos + $total_adultos + $total_adultos_mayores;
                            $categorias = [
                                ['Niños', $total_ninos, '#4299e1'],
                                ['Adultos', $total_adultos, '#48bb78'],
                                ['Adultos Mayores', $total_adultos_mayores, '#ed8936']
                            ];

                            foreach($categorias as $cat) {
                                $porcentaje = $total_general > 0 ? round(($cat[1] * 100) / $total_general, 1) : 0;
                                echo "<tr>";
                                echo "<td class='category-name'><span class='category-dot' style='background-color: {$cat[2]}'></span>{$cat[0]}</td>";
                                echo "<td class='quantity'>{$cat[1]}</td>";
                                echo "<td class='percentage'><div class='progress-bar' style='width: {$porcentaje}%; background-color: {$cat[2]}'></div><span>{$porcentaje}%</span></td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td><strong>Total</strong></td>
                                <td><strong><?php echo $total_general; ?></strong></td>
                                <td><strong>100%</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="table-section">
                <h2 class="section-title">Resumen de Ventas por Período</h2>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Período</th>
                                <th>Total Tickets</th>
                                <th>Total Dinero</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Hoy</strong></td>
                                <td><?php echo $total_ventas; ?></td>
                                <td>$<?php echo number_format($total_dinero, 2); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Esta Semana</strong></td>
                                <td><?php echo $total_semana; ?></td>
                                <td>$<?php echo number_format($dinero_semana, 2); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Este Mes</strong></td>
                                <td><?php echo $total_mes; ?></td>
                                <td>$<?php echo number_format($dinero_mes, 2); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="chart-grid">
            <div class="chart-section">
               
                <div class="chart-container">
                <div class="date-filter">
                    <input type="text" id="daterange" name="daterange" />
                </div>
                    <canvas id="ticketsChart"></canvas>
                </div> 
            </div>

            <div class="chart-section">
                <div class="chart-container">
                    <canvas id="capacityChart"></canvas>
                </div>
                <div class="capacity-stats">
                    <div class="stat-item">
                        <span class="stat-label">Tickets Vendidos:</span>
                        <span class="stat-value"><?php echo $total_ventas; ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Disponibles:</span>
                        <span class="stat-value"><?php echo $tickets_disponibles; ?></span>
                    </div>
                    <div class="stat-item total">
                        <span class="stat-label">Capacidad Total:</span>
                        <span class="stat-value">10,000 tickets</span>
                    </div>
                </div>
            </div>

            <!-- Nueva sección para tickets más vendidos -->
            <div class="chart-section">
                <div class="chart-container">
                    <canvas id="topTicketsChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Nueva sección: Ventas por hora y grupo de edad (PDD-54) -->
        <div class="tables-grid">
            <div class="table-section">
                <h2 class="section-title">📊 Análisis de Ventas por Hora y Grupo de Edad</h2>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Hora</th>
                                <th>Total</th>
                                <th>Niños</th>
                                <th>Adultos</th>
                                <th>Adultos Mayores</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if(count($ventas_por_hora) > 0) {
                                foreach($ventas_por_hora as $vh) {
                                    $hora_formato = str_pad($vh['hora'], 2, '0', STR_PAD_LEFT) . ':00';
                                    echo "<tr>";
                                    echo "<td><strong>{$hora_formato}</strong></td>";
                                    echo "<td class='quantity'>{$vh['total_ventas']}</td>";
                                    echo "<td class='quantity'>{$vh['ninos']}</td>";
                                    echo "<td class='quantity'>{$vh['adultos']}</td>";
                                    echo "<td class='quantity'>{$vh['adultos_mayores']}</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5' style='text-align:center;'>No hay datos disponibles para hoy</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Nueva sección: Fechas Pico (PDD-36, PDD-55) -->
            <div class="table-section">
                <h2 class="section-title">🔥 Fechas con Mayor Volumen de Ventas (Últimos 30 días)</h2>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Total Ventas</th>
                                <th>Total Ingresos</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if(count($fechas_pico) > 0) {
                                foreach($fechas_pico as $fp) {
                                    $fecha_formato = date('d/m/Y', strtotime($fp['fechaCompra']));
                                    echo "<tr>";
                                    echo "<td><strong>{$fecha_formato}</strong></td>";
                                    echo "<td class='quantity'>{$fp['total_ventas']}</td>";
                                    echo "<td class='quantity'>$" . number_format($fp['total_ingresos'], 2) . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='3' style='text-align:center;'>No hay datos disponibles</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Nueva sección: Gráfico de ventas por hora (PDD-54) -->
        <div class="chart-grid">
            <div class="chart-section">
                <div class="chart-container">
                    <canvas id="ventasPorHoraChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script>
        function generarPDF() {
            window.location.href = 'generar_reporte.php';
        }
        // Update every second
        setInterval(updateDateTime, 1000);
        
        $(function() {
            // Inicializar DateRangePicker con nuevas opciones
            $('#daterange').daterangepicker({
                opens: 'right',
                showDropdowns: true,
                autoApply: true,
                // maxDate: moment(), <- Removing this line to allow future dates
                linkedCalendars: false,
                showCustomRangeLabel: false,
                parentEl: ".date-filter",
                drops: 'down'
            }, actualizarGrafico);
            
            // Función para actualizar el gráfico
            function actualizarGrafico(start, end) {
                fetch(`obtener_datos_grafico.php?start=${start.format('YYYY-MM-DD')}&end=${end.format('YYYY-MM-DD')}`)
                    .then(response => response.json())
                    .then(data => {
                        const ctx = document.getElementById('ticketsChart').getContext('2d');
                        
                        if (window.myChart) {
                            window.myChart.destroy();
                        }
                        
                        window.myChart = new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: data.fechas,
                                datasets: [{
                                    label: 'Tickets Vendidos',
                                    data: data.tickets,
                                    borderColor: '#4299e1',
                                    tension: 0.1
                                }]
                            },
                            options: {
                                responsive: true,
                                plugins: {
                                    title: {
                                        display: true,
                                        text: 'Tickets Vendidos por Fecha'
                                    }
                                }
                            }
                        });
                    });
            }
            //
            // Cargar gráfico inicial
            actualizarGrafico($('#daterange').data('daterangepicker').startDate, 
                            $('#daterange').data('daterangepicker').endDate);
        });

        // Agregar el gráfico de capacidad
        const capacityCtx = document.getElementById('capacityChart').getContext('2d');
        new Chart(capacityCtx, {
            type: 'pie',
            data: {
                labels: ['Tickets Vendidos', 'Tickets Disponibles'],
                datasets: [{
                    data: [<?php echo $total_ventas; ?>, <?php echo $tickets_disponibles; ?>],
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 99, 132, 0.8)'
                    ],
                    borderColor: [
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 99, 132, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                aspectRatio: 2, // Ajustado para coincidir con la altura del gráfico de tickets
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: {
                                size: 11 // Texto de leyenda más pequeño
                            }
                        }
                    },
                    title: {
                        display: true,
                        text: 'Capacidad del Lugar',
                        font: {
                            size: 14
                        }
                    }
                }
            }
        });

        // Agregar gráfico de tickets más vendidos
        const topTicketsCtx = document.getElementById('topTicketsChart').getContext('2d');
        new Chart(topTicketsCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($ticket_names); ?>,
                datasets: [{
                    label: 'Tickets más vendidos',
                    data: <?php echo json_encode($ticket_counts); ?>,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(153, 102, 255, 0.8)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Cantidad vendida'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: 'Top 5 Tickets Más Vendidos',
                        font: {
                            size: 14
                        }
                    }
                }
            }
        });

        // Gráfico de ventas por hora y grupo de edad (PDD-54)
        const ventasPorHoraCtx = document.getElementById('ventasPorHoraChart').getContext('2d');
        
        <?php
        // Preparar datos para el gráfico
        $horas_labels = array();
        $datos_ninos = array();
        $datos_adultos = array();
        $datos_adultos_mayores = array();
        
        foreach($ventas_por_hora as $vh) {
            $horas_labels[] = str_pad($vh['hora'], 2, '0', STR_PAD_LEFT) . ':00';
            $datos_ninos[] = $vh['ninos'];
            $datos_adultos[] = $vh['adultos'];
            $datos_adultos_mayores[] = $vh['adultos_mayores'];
        }
        ?>
        
        new Chart(ventasPorHoraCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($horas_labels); ?>,
                datasets: [
                    {
                        label: 'Niños',
                        data: <?php echo json_encode($datos_ninos); ?>,
                        backgroundColor: 'rgba(66, 153, 225, 0.7)',
                        borderColor: 'rgba(66, 153, 225, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Adultos',
                        data: <?php echo json_encode($datos_adultos); ?>,
                        backgroundColor: 'rgba(72, 187, 120, 0.7)',
                        borderColor: 'rgba(72, 187, 120, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Adultos Mayores',
                        data: <?php echo json_encode($datos_adultos_mayores); ?>,
                        backgroundColor: 'rgba(237, 137, 54, 0.7)',
                        borderColor: 'rgba(237, 137, 54, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    x: {
                        stacked: false,
                        title: {
                            display: true,
                            text: 'Hora del Día'
                        }
                    },
                    y: {
                        stacked: false,
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Cantidad de Tickets'
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Ventas por Hora y Grupo de Edad (Hoy)',
                        font: {
                            size: 16
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
<?php 
    }else{
        header("location:../index.php");
    }
?>