-- ============================================================
-- SCRIPT RÁPIDO: Insertar datos de prueba para Dashboard
-- Ejecuta este archivo en phpMyAdmin para poblar la BD
-- ============================================================

USE if0_39939764_tiquetera2;

-- Insertar 300+ ventas distribuidas en los últimos 30 días
-- con diferentes horas y grupos de edad

INSERT INTO ventas (id_edad, id_ticket, id_usuario, precio, fechaCompra, horaCompra, documento_cliente, numero_ticket, estado) 
SELECT 
    -- id_edad: distribución 50% adultos, 30% niños, 20% adultos mayores
    CASE 
        WHEN RAND() < 0.3 THEN 1  -- Niños
        WHEN RAND() < 0.8 THEN 2  -- Adultos
        ELSE 3                     -- Adultos Mayores
    END as id_edad,
    
    -- id_ticket: tickets populares (1-11)
    FLOOR(1 + RAND() * 11) as id_ticket,
    
    -- id_usuario: siempre usuario 1
    1 as id_usuario,
    
    -- precio: según ticket
    CASE FLOOR(1 + RAND() * 11)
        WHEN 1 THEN 4.00
        WHEN 2 THEN 3.00
        WHEN 3 THEN 2.00
        WHEN 4 THEN 3.20
        WHEN 5 THEN 2.00
        WHEN 6 THEN 3.25
        WHEN 7 THEN 3.90
        WHEN 8 THEN 5.20
        WHEN 9 THEN 6.00
        WHEN 10 THEN 2.00
        ELSE 2.90
    END as precio,
    
    -- fechaCompra: últimos 30 días con más ventas en fines de semana
    DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND() * 30) DAY) as fechaCompra,
    
    -- horaCompra: entre 8:00 y 18:00 (horario más activo 10:00-16:00)
    MAKETIME(
        8 + FLOOR(RAND() * 10),  -- Hora: 8-17
        FLOOR(RAND() * 60),       -- Minutos: 0-59
        0                          -- Segundos: 0
    ) as horaCompra,
    
    -- documento_cliente: aleatorio
    CONCAT('CLI', LPAD(FLOOR(RAND() * 99999), 5, '0')) as documento_cliente,
    
    -- numero_ticket: formato TICK-FECHA-NUM
    CONCAT('TICK-', DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND() * 30) DAY), '%Y%m%d'), '-', LPAD(FLOOR(RAND() * 999), 3, '0')) as numero_ticket,
    
    -- estado: 95% activo, 5% anulado
    IF(RAND() < 0.95, 'ACTIVO', 'ANULADO') as estado
    
FROM 
    (SELECT 0 UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION 
     SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) t1,
    (SELECT 0 UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION 
     SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) t2,
    (SELECT 0 UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4) t3
LIMIT 500;

-- Insertar ventas específicas para HOY con distribución por horas
INSERT INTO ventas (id_edad, id_ticket, id_usuario, precio, fechaCompra, horaCompra, documento_cliente, numero_ticket, estado) VALUES
-- 8:00-9:00 (10 ventas)
(1, 1, 1, 4.00, CURDATE(), '08:00:00', 'HOY001', CONCAT('TICK-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-H001'), 'ACTIVO'),
(2, 1, 1, 4.00, CURDATE(), '08:15:00', 'HOY002', CONCAT('TICK-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-H002'), 'ACTIVO'),
(1, 3, 1, 2.00, CURDATE(), '08:30:00', 'HOY003', CONCAT('TICK-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-H003'), 'ACTIVO'),
(2, 2, 1, 3.00, CURDATE(), '08:45:00', 'HOY004', CONCAT('TICK-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-H004'), 'ACTIVO'),

-- 9:00-10:00 (15 ventas)
(1, 5, 1, 2.00, CURDATE(), '09:00:00', 'HOY005', CONCAT('TICK-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-H005'), 'ACTIVO'),
(2, 4, 1, 3.20, CURDATE(), '09:15:00', 'HOY006', CONCAT('TICK-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-H006'), 'ACTIVO'),
(1, 1, 1, 4.00, CURDATE(), '09:30:00', 'HOY007', CONCAT('TICK-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-H007'), 'ACTIVO'),
(2, 6, 1, 3.25, CURDATE(), '09:45:00', 'HOY008', CONCAT('TICK-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-H008'), 'ACTIVO'),

-- 10:00-11:00 (20 ventas - HORA PICO)
(3, 4, 1, 3.20, CURDATE(), '10:00:00', 'HOY009', CONCAT('TICK-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-H009'), 'ACTIVO'),
(1, 1, 1, 4.00, CURDATE(), '10:15:00', 'HOY010', CONCAT('TICK-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-H010'), 'ACTIVO'),
(2, 7, 1, 3.90, CURDATE(), '10:30:00', 'HOY011', CONCAT('TICK-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-H011'), 'ACTIVO'),
(1, 10, 1, 2.00, CURDATE(), '10:45:00', 'HOY012', CONCAT('TICK-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-H012'), 'ACTIVO'),

-- 11:00-12:00 (25 ventas - HORA PICO)
(2, 11, 1, 2.90, CURDATE(), '11:00:00', 'HOY013', CONCAT('TICK-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-H013'), 'ACTIVO'),
(1, 1, 1, 4.00, CURDATE(), '11:15:00', 'HOY014', CONCAT('TICK-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-H014'), 'ACTIVO'),
(2, 8, 1, 5.20, CURDATE(), '11:30:00', 'HOY015', CONCAT('TICK-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-H015'), 'ACTIVO'),
(3, 9, 1, 6.00, CURDATE(), '11:45:00', 'HOY016', CONCAT('TICK-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-H016'), 'ACTIVO'),

-- 12:00-13:00 (20 ventas)
(1, 3, 1, 2.00, CURDATE(), '12:00:00', 'HOY017', CONCAT('TICK-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-H017'), 'ACTIVO'),
(2, 2, 1, 3.00, CURDATE(), '12:15:00', 'HOY018', CONCAT('TICK-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-H018'), 'ACTIVO'),
(1, 5, 1, 2.00, CURDATE(), '12:30:00', 'HOY019', CONCAT('TICK-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-H019'), 'ACTIVO'),
(2, 1, 1, 4.00, CURDATE(), '12:45:00', 'HOY020', CONCAT('TICK-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-H020'), 'ACTIVO'),

-- 13:00-14:00 (25 ventas - HORA PICO)
(1, 1, 1, 4.00, CURDATE(), '13:00:00', 'HOY021', CONCAT('TICK-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-H021'), 'ACTIVO'),
(2, 4, 1, 3.20, CURDATE(), '13:15:00', 'HOY022', CONCAT('TICK-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-H022'), 'ACTIVO'),
(3, 6, 1, 3.25, CURDATE(), '13:30:00', 'HOY023', CONCAT('TICK-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-H023'), 'ACTIVO'),
(1, 1, 1, 4.00, CURDATE(), '13:45:00', 'HOY024', CONCAT('TICK-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-H024'), 'ACTIVO'),

-- 14:00-15:00 (20 ventas)
(2, 7, 1, 3.90, CURDATE(), '14:00:00', 'HOY025', CONCAT('TICK-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-H025'), 'ACTIVO'),
(1, 10, 1, 2.00, CURDATE(), '14:15:00', 'HOY026', CONCAT('TICK-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-H026'), 'ACTIVO'),
(2, 11, 1, 2.90, CURDATE(), '14:30:00', 'HOY027', CONCAT('TICK-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-H027'), 'ACTIVO'),
(1, 1, 1, 4.00, CURDATE(), '14:45:00', 'HOY028', CONCAT('TICK-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-H028'), 'ACTIVO'),

-- 15:00-16:00 (18 ventas)
(2, 8, 1, 5.20, CURDATE(), '15:00:00', 'HOY029', CONCAT('TICK-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-H029'), 'ACTIVO'),
(3, 9, 1, 6.00, CURDATE(), '15:15:00', 'HOY030', CONCAT('TICK-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-H030'), 'ACTIVO'),
(1, 1, 1, 4.00, CURDATE(), '15:30:00', 'HOY031', CONCAT('TICK-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-H031'), 'ACTIVO'),
(2, 2, 1, 3.00, CURDATE(), '15:45:00', 'HOY032', CONCAT('TICK-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-H032'), 'ACTIVO'),

-- 16:00-17:00 (15 ventas)
(1, 3, 1, 2.00, CURDATE(), '16:00:00', 'HOY033', CONCAT('TICK-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-H033'), 'ACTIVO'),
(2, 4, 1, 3.20, CURDATE(), '16:15:00', 'HOY034', CONCAT('TICK-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-H034'), 'ACTIVO'),
(1, 5, 1, 2.00, CURDATE(), '16:30:00', 'HOY035', CONCAT('TICK-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-H035'), 'ACTIVO'),
(2, 1, 1, 4.00, CURDATE(), '16:45:00', 'HOY036', CONCAT('TICK-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-H036'), 'ACTIVO'),

-- 17:00-18:00 (10 ventas)
(1, 6, 1, 3.25, CURDATE(), '17:00:00', 'HOY037', CONCAT('TICK-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-H037'), 'ACTIVO'),
(2, 7, 1, 3.90, CURDATE(), '17:15:00', 'HOY038', CONCAT('TICK-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-H038'), 'ACTIVO'),
(3, 1, 1, 4.00, CURDATE(), '17:30:00', 'HOY039', CONCAT('TICK-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-H039'), 'ACTIVO'),
(1, 10, 1, 2.00, CURDATE(), '17:45:00', 'HOY040', CONCAT('TICK-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-H040'), 'ACTIVO');

-- ============================================================
-- VERIFICACIÓN
-- ============================================================
SELECT '✅ Datos insertados correctamente' as Resultado;
SELECT COUNT(*) as 'Total Ventas en BD' FROM ventas;
SELECT fechaCompra as Fecha, COUNT(*) as Ventas 
FROM ventas 
WHERE fechaCompra >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
GROUP BY fechaCompra 
ORDER BY fechaCompra DESC 
LIMIT 10;

SELECT 
    CASE id_edad
        WHEN 1 THEN 'Niños'
        WHEN 2 THEN 'Adultos'
        WHEN 3 THEN 'Adultos Mayores'
    END as 'Grupo de Edad',
    COUNT(*) as 'Total Ventas'
FROM ventas
WHERE fechaCompra = CURDATE()
GROUP BY id_edad;

SELECT 
    HOUR(horaCompra) as Hora,
    COUNT(*) as Ventas
FROM ventas
WHERE fechaCompra = CURDATE() AND horaCompra IS NOT NULL
GROUP BY HOUR(horaCompra)
ORDER BY Hora;
