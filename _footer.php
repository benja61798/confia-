<?php
// Este archivo puede ser incluido en todas las páginas para consistencia
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/estilos_generales.css">
    <link rel="stylesheet" href="css/login-page.css">
    <title>Document</title>
</head>
<body>
    
    </main>
    <footer class="bg-light text-center py-3 mt-4">
        <div class="container">
            <p class="mb-0">&copy; <?php echo date("Y"); ?> Confía+. Todos los derechos reservados.</p>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            once: true,
            mirror: false,
        });
    </script>
    <?php if (isset($customJs)): ?>
        <script src="<?php echo $customJs; ?>"></script>
    <?php endif; ?>
</body>
</html>