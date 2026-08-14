<?php
// Configuración global del proyecto
if (!isset($projectName)) {
    $projectName = 'Clinica Dental — Muelitas';
}
if (!isset($phone)) {
    $phone       = '+502 0000-0000';
}
if (!isset($email)) {
    $email       = 'info@muelitas.com';
}
if (!isset($address)) {
    $address     = 'Guatemala, Guatemala';
}
if (!isset($year)) {
    $year        = date('Y');
}
if (!isset($extraStyles)) {
    $extraStyles = '';
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($projectName, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="styles.css">
    <?php if ($extraStyles !== ''): ?>
        <link rel="stylesheet" href="<?php echo htmlspecialchars($extraStyles, ENT_QUOTES, 'UTF-8'); ?>">
    <?php endif; ?>
</head>

<body>

    <header class="site-header">
        <div class="site-header-inner">
            <a href="index.php" class="logo-link">
                <!-- SVG Dental Moderno / Minimalista -->
                <svg class="logo-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M7 10.5c0-3.5 1.5-6 5-6s5 2.5 5 6c0 4-2 7-2.5 9-.2.8-.8 1.3-1.6 1.4-.3 0-.6 0-.8 0-.8-.1-1.4-.6-1.6-1.4-.5-2-2.5-5-2.5-9z" />
                    <path d="M12 4.5c0 3.5-2 5-4.5 6" />
                    <path d="M12 4.5c0 3.5 2 5 4.5 6" />
                    <path d="M18 3l1 .5M19 6.5l1-.5" />
                </svg>
                <span class="logo-text">Clinica Muelitas</span>
            </a>
<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
            <nav class="site-nav">
                <a href="pacientes.php" id="nav-pacientes" class="<?php echo ($currentPage === 'pacientes.php') ? 'active' : ''; ?>">Pacientes</a>
                <a href="dentistas.php" id="nav-dentistas" class="<?php echo ($currentPage === 'dentistas.php') ? 'active' : ''; ?>">Dentistas</a>
                <a href="servicios.php" id="nav-servicios" class="<?php echo ($currentPage === 'servicios.php') ? 'active' : ''; ?>">Servicios</a>
                <a href="tratamientos.php" id="nav-tratamientos" class="<?php echo ($currentPage === 'tratamientos.php') ? 'active' : ''; ?>">Tratamientos</a>
                <a href="citas.php" id="nav-citas" class="<?php echo ($currentPage === 'citas.php') ? 'active' : ''; ?>">Citas</a>
                <a href="reportes.php" id="nav-reportes" class="<?php echo ($currentPage === 'reportes.php') ? 'active' : ''; ?>">Reportes</a>
            </nav>
        </div>
    </header>
