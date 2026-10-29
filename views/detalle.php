<?php
$producto = $producto ?? null;
if (!is_array($producto)) {
    http_response_code(404);
    exit('Producto no encontrado.');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle del Producto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <!-- Navbar (Misma del catálogo) -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php?controller=catalogo">Catálogo de Productos</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?controller=logout">Cerrar Sesión</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card shadow">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h3 class="mb-0"><?php echo htmlspecialchars($producto['nombre']); ?></h3>
                        <div class="badge bg-danger fs-6">
                            <?php 
                            $descuento = round((($producto['precio_original'] - $producto['precio_promocion']) / $producto['precio_original']) * 100);
                            echo $descuento . '% OFF';
                            ?>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <!-- Si tienes imágenes en la base de datos, puedes agregarlas aquí -->
                        <div class="bg-light text-center p-5 mb-4 rounded border">
                            <span class="text-muted">Espacio para Imagen del Producto</span>
                        </div>

                        <h5 class="text-primary">Descripción:</h5>
                        <p class="lead text-muted"><?php echo htmlspecialchars($producto['descripcion']); ?></p>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div>
                                <p class="mb-0 text-muted">Precio Anterior: <span class="text-decoration-line-through">$<?php echo number_format($producto['precio_original'], 2); ?></span></p>
                                <h2 class="text-success mb-0">Precio Actual: $<?php echo number_format($producto['precio_promocion'], 2); ?></h2>
                            </div>
                            <div class="text-end text-muted">
                                <small>Promoción válida hasta:<br>
                                <strong><?php echo date('d/m/Y', strtotime($producto['fecha_fin'])); ?></strong></small>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-light p-3">
                        <div class="d-flex justify-content-between">
                            <a href="index.php?controller=catalogo" class="btn btn-outline-secondary">Volver al Catálogo</a>
                            <button class="btn btn-success px-4">Comprar Ahora</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>