<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: confia.php"); // Redirige al login si no hay sesión iniciada
    exit();


}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="referrer" content="origin">
  <title>Confía+: Tu Apoyo en Salud Mental</title>
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link rel="stylesheet" href="css/estilos_generales.css">
  <link type="icon" rel="icono/png" href="img/logo.png">
</head>
<body>
     <header class="header">
      <div class="container">
            <a href="#" class="logo">Confía+</a>
             <nav class="nav">
                <ul>
                    <li><a href="#problema">El Problema</a></li>
                    <li><a href="#solucion">Nuestra Solución</a></li>
                    <li><a href="#instituciones">Para Instituciones</a></li>
                    <li><a href="#contacto">Contacto</a></li>
                </ul>
             </nav>
            <a href="confia.php" class="btn btn-login" title="Ir a Iniciar Sesión o Registrarse">
                Iniciar Sesión / Registrarse
            </a>
        </div>
     </header>

    <section class="hero" data-aos="fade-up" data-aos-duration="1000">
        <div class="container">
         <h1>Confía+: Tu Apoyo en Salud Mental para Estudiantes Universitarios</h1>
            <p class="subtitle">
                Conectamos a estudiantes con apoyo psicológico accesible, empático y personalizado.
            </p>
            <a href="#solucion" class="btn btn-primary">Explora nuestros recursos</a>
        </div>
    </section>

    <section id="problema" class="section problem-section">
        <div class="container">
         <h2 data-aos="fade-right">Un Contexto Desafiante</h2>
            <div class="problem-content">
                <p data-aos="fade-right" data-aos-delay="100">
                    Actualmente, los estudiantes de educación superior enfrentan un contexto desafiante en términos de salud mental. …
                </p>
                <p data-aos="fade-right" data-aos-delay="200">
                    Diversos informes de instituciones chilenas como el MINSAL, SENDA y estudios universitarios han identificado …
                </p>
            </div>
        </div>
    </section>

    <section id="solucion" class="section solution-section bg-light">
        <div class="container">
             <h2 data-aos="fade-left">Nuestra Solución: Confía+</h2>
            <p class="solution-intro" data-aos="fade-left" data-aos-delay="100">
                Confía+ es una plataforma digital que busca mejorar el bienestar emocional mediante:
            </p>

            <div class="features-grid">
                <div class="feature-item" data-aos="zoom-in" data-aos-delay="200">
                    <h3><i class="icon fas fa-user-check"></i> Asistencia Personalizada</h3>
                    <p>Apoyo adaptado a tus necesidades individuales y seguimiento de tu progreso.</p>
                </div>
                <div class="feature-item" data-aos="zoom-in" data-aos-delay="300">
                    <h3><i class="icon fas fa-heartbeat"></i> Seguimiento Activo y Alertas</h3>
                        <p>Monitoreo continuo de tu bienestar emocional con alertas tempranas.</p>
                </div>
                <div class="feature-item" data-aos="zoom-in" data-aos-delay="400">
                 <h3><i class="icon fas fa-book-open"></i> Contenidos Educativos</h3>
                    <p>Recursos que desestigmatizan el apoyo psicológico y brindan herramientas útiles.</p>
                </div>
                <div class="feature-item" data-aos="zoom-in" data-aos-delay="500">
                 <h3><i class="icon fas fa-shield-alt"></i> Interfaz Empática y Segura</h3>
                 <p>Priorizamos tu experiencia de usuario y garantizamos la más estricta confidencialidad.</p>
                </div>
            </div>

            <p class="solution-outro" data-aos="fade-up" data-aos-delay="600">
                Confía+ es tu aliado en el camino hacia un bienestar emocional duradero.
            </p>
        </div>
    </section>

    <section id="instituciones" class="section institutions-section">
        <div class="container">
          <h2 data-aos="fade-right">Confía+: Una Alianza Estratégica para Instituciones</h2>
            <div class="institutions-content">
                <p data-aos="fade-right" data-aos-delay="100">
                    En Confía+, entendemos los desafíos que enfrentan las instituciones de educación superior para brindar un apoyo efectivo …
                </p>
                <ul data-aos="fade-up" data-aos-delay="200">
                    <li><i class="icon fas fa-check-circle"></i> Mejora del bienestar y rendimiento estudiantil.</li>
                    <li><i class="icon fas fa-check-circle"></i> Herramienta de apoyo institucional con reportes anonimizados.</li>
                    <li><i class="icon fas fa-check-circle"></i> Reducción de la demanda en servicios presenciales.</li>
                    <li><i class="icon fas fa-check-circle"></i> Solución digital escalable en salud mental.</li>
                </ul>
                <a href="#contacto" class="btn btn-secondary" data-aos="fade-up" data-aos-delay="300">
                    Contacta para una alianza estratégica
                </a>
            </div>
        </div>
    </section>

    <section class="section testimonials-section bg-light">
        <div class="container">
          <h2 data-aos="fade-up">Lo que nuestros estudiantes opinan</h2>
            <div class="testimonial-grid">
                <div class="testimonial-item" data-aos="flip-up" data-aos-delay="100">
                    <p>"Confía+ me dio el apoyo que necesitaba cuando me sentía más solo…"</p>
                   <span>— Estudiante de Ingeniería, U. de Chile</span>
                </div>
                 <div class="testimonial-item" data-aos="flip-up" data-aos-delay="200">
                    <p>"Gracias a los contenidos educativos de Confía+ entendí mejor mi ansiedad…"</p>
                        <span>— Estudiante de Psicología, P. U. Católica</span>
                 </div>
                    <div class="testimonial-item" data-aos="flip-up" data-aos-delay="300">
                        <p>"La interfaz es tan amigable y se siente tan segura…"</p>
                        <span>— Estudiante de Medicina, U. de Santiago</span>
                    </div>
            </div>
        </div>
    </section>

    <section id="contacto" class="section contact-section">
        <div class="container">
            <h2>Contáctanos</h2>

            <form action="https://formsubmit.co/confia.contacto@gmail.com"
                method="POST"
                class="contact-form">

                <input type="hidden" name="_captcha" value="false">
                            <input type="hidden" name="_url" value="http://localhost/index.php">
                <input type="hidden" name="_next" value="http://localhost/index.php">

                <div class="form-group">
                    <label for="name">Nombre:</label>
                    <input type="text" id="name" name="name" required>
                </div>

                <div class="form-group">
                <label for="email">Correo Electrónico:</label>
                <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                <label for="message">Mensaje:</label>
                <textarea id="message" name="message" rows="5" required></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Enviar Mensaje</button>
            </form>
        </div>
    </section>

    <footer class="footer">
         <div class="container">
            <p>&copy; 2025 Confía+. Todos los derechos reservados.</p>
                <nav class="footer-nav">
                    <ul>
                        <li><a href="#">Política de Privacidad</a></li>
                        <li><a href="#">Términos de Servicio</a></li>
                    </ul>
                </nav>
         </div>
    </footer>

        <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
        <script src="js/main.js"></script>
</body>
</html>