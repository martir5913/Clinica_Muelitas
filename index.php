<?php
// Configuración de variables globales
$projectName = 'Clinica Dental — Muelitas';
$phone       = '+502 0000-0000';
$email       = 'info@muelitas.com';
$address     = 'Guatemala, Guatemala';
$year        = date('Y');

include 'header.php';
?>

<main class="site-main">
    <!-- Sección Hero / Bienvenida -->
    <section class="hero-section">
        <div class="hero-container">
            <div class="hero-badge">Portal Administrativo</div>
            <h2 class="hero-title">Gestión Dental Moderna e Inteligente</h2>
            <p class="hero-description">
                Bienvenido al sistema de administración de <strong><?php echo htmlspecialchars($projectName, ENT_QUOTES, 'UTF-8'); ?></strong>. 
                Optimice el registro de sus pacientes, controle el horario de los odontólogos, 
                organice el catálogo de tratamientos y configure la programación de citas desde un solo portal unificado.
            </p>
            <div class="hero-action-hint">
                <span class="pulse-dot"></span> Seleccione un módulo en el menú superior para comenzar.
            </div>
        </div>
    </section>

    <!-- Sección Nosotros -->
    <section class="seccion-institucional" id="nosotros">
        <div class="seccion-header">
            <span class="seccion-subtitle">Quiénes Somos</span>
            <h3 class="seccion-main-title">Sobre Nosotros</h3>
        </div>
        <div class="nosotros-grid">
            <div class="nosotros-content">
                <p>
                    <strong>Clinica Dental Muelitas</strong> es un proyecto y centro odontológico dedicado a proveer soluciones 
                    de salud oral integrales. Diseñado con una visión de vanguardia técnica y médica, nuestro portal centraliza 
                    las operaciones administrativas para garantizar que cada paciente reciba una atención precisa, organizada y humana.
                </p>
                <p>
                    Nos enfocamos en facilitar el día a día tanto de los pacientes como de nuestro equipo médico, utilizando tecnología 
                    de punta y metodologías estructuradas para el seguimiento de consultas, historiales de tratamientos y agendas de citas.
                </p>
            </div>
            <div class="nosotros-media">
                <img src="./images/clinica.png" alt="Instalaciones de Clinica Dental Muelitas" class="clinica-img">
            </div>
        </div>
    </section>


    <!-- Sección Misión y Visión -->
    <section class="seccion-mision-vision">
        <div class="grid-mision-vision">
            <!-- Tarjeta Misión -->
            <div class="mision-card">
                <div class="card-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <circle cx="12" cy="12" r="6"/>
                        <circle cx="12" cy="12" r="2"/>
                    </svg>
                </div>
                <h4>Nuestra Misión</h4>
                <p>
                    Brindar atención dental integral, humana y eficiente, apoyando a cada paciente con procesos clínicos 
                    ordenados y una experiencia altamente confiable desde su primera consulta médica.
                </p>
            </div>

            <!-- Tarjeta Visión -->
            <div class="vision-card">
                <div class="card-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                </div>
                <h4>Nuestra Visión</h4>
                <p>
                    Ser un referente de organización clínica y calidad odontológica, reconocido en el sector de la salud 
                    por nuestra continua innovación tecnológica, calidez en el trato y excelencia en el servicio preventivo y correctivo.
                </p>
            </div>
        </div>
    </section>

    <!-- Sección Google Maps -->
    <section class="seccion-institucional" id="ubicacion">
        <div class="seccion-header">
            <span class="seccion-subtitle">Dónde Encontrarnos</span>
            <h3 class="seccion-main-title">Ubicación de la Clínica</h3>
        </div>
        <div class="seccion-body">
            <p>
                Visite nuestras instalaciones modernas. Nos ubicamos en una zona accesible con parqueo privado, seguridad 
                y comodidad garantizada para todos nuestros pacientes.
            </p>
            <div class="mapa-container">
                <iframe
                    class="mapa-frame"
                    title="Ubicación de Clinica Dental Muelitas"
                    src="https://www.google.com/maps?q=Clinica%20Dental%20Muelitas&output=embed"
                    loading="lazy"
                    allowfullscreen>
                </iframe>
            </div>
        </div>
    </section>
</main>

<?php include 'footer.php'; ?>
