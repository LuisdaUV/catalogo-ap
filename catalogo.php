
<?php
session_start();
require_once 'config/database.php';

// Verificar si el usuario inició sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Consultar los productos activos en promoción
try {
    $database = new Database();
    $db = $database->getConnection();

    $query = "SELECT * FROM productos
              WHERE activo = TRUE
              AND fecha_fin >= CURDATE()
              ORDER BY fecha_inicio DESC";

    $stmt = $db->prepare($query);
    $stmt->execute();

    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $productos = [];
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Catálogo de Productos</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link rel="stylesheet" href="assets/style.css">
</head>

<body>

    <!-- Barra de navegación -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">

            <a class="navbar-brand" href="catalogo.php">
                Catálogo de Productos
            </a>

            <button
                class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarNav"
                aria-controls="navbarNav"
                aria-expanded="false"
                aria-label="Mostrar navegación">

                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">

                    <li class="nav-item">
                        <span class="nav-link text-light">
                            Hola,
                            <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Usuario'); ?>
                        </span>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            Cerrar Sesión
                        </a>
                    </li>

                </ul>
            </div>
        </div>
    </nav>

    <!-- Contenido del catálogo -->
    <div class="container mt-4">

        <h1 class="mb-4">Productos en Promoción</h1>

        <?php if (empty($productos)): ?>

            <div class="alert alert-info">
                No hay productos en promoción actualmente.
            </div>

        <?php else: ?>

            <div class="row">

                <?php foreach ($productos as $producto): ?>

                    <?php
                    // Calcular el porcentaje de descuento
                    $precioOriginal = (float) $producto['precio_original'];
                    $precioPromocion = (float) $producto['precio_promocion'];

                    $descuento = $precioOriginal > 0
                        ? round((($precioOriginal - $precioPromocion) / $precioOriginal) * 100)
                        : 0;

                    // Identificador para la ventana de detalles
                    // Se usa el índice para evitar depender de que exista una columna "id".
                    $modalId = 'detalleProducto' . (int) $producto['id'];
                    ?>

                    <div class="col-md-4 mb-4">

                        <div class="card h-100 shadow-sm">

                            <div class="card-body">

                                <div class="badge bg-danger mb-2">
                                    <?php echo $descuento; ?>% OFF
                                </div>

                                <h5 class="card-title">
                                    <?php echo htmlspecialchars($producto['nombre']); ?>
                                </h5>

                                <p class="card-text">
                                    <?php echo htmlspecialchars($producto['descripcion']); ?>
                                </p>

                                <div class="mb-2">

                                    <span class="text-decoration-line-through text-muted">
                                        $<?php echo number_format($precioOriginal, 2); ?>
                                    </span>

                                    <span class="h4 text-success ms-2">
                                        $<?php echo number_format($precioPromocion, 2); ?>
                                    </span>

                                </div>

                                <small class="text-muted">
                                    Válido hasta:
                                    <?php echo date('d/m/Y', strtotime($producto['fecha_fin'])); ?>
                                </small>

                            </div>

                            <!-- Botón funcional de detalles -->
                            <div class="card-footer bg-white border-0">

                                <button
                                    type="button"
                                    class="btn btn-primary w-100"
                                    data-bs-toggle="modal"
                                    data-bs-target="#<?php echo $modalId; ?>">

                                    Ver Detalles

                                </button>

                            </div>

                        </div>

                    </div>

                    <!-- Ventana con la información adicional del producto -->
                    <div
                        class="modal fade"
                        id="<?php echo $modalId; ?>"
                        tabindex="-1"
                        aria-labelledby="<?php echo $modalId; ?>Titulo"
                        aria-hidden="true">

                        <div class="modal-dialog modal-dialog-centered">

                            <div class="modal-content">

                                <div class="modal-header">

                                    <h5
                                        class="modal-title"
                                        id="<?php echo $modalId; ?>Titulo">

                                        Detalles del producto:
                                        <?php echo htmlspecialchars($producto['nombre']); ?>

                                    </h5>

                                    <button
                                        type="button"
                                        class="btn-close"
                                        data-bs-dismiss="modal"
                                        aria-label="Cerrar">
                                    </button>

                                </div>

                                <div class="modal-body">

                                    <h6>Descripción del producto</h6>

                                    <p>
                                        <?php echo nl2br(htmlspecialchars($producto['descripcion'])); ?>
                                    </p>

                                    <hr>

                                    <p>
                                        <strong>Precio original:</strong>
                                        $<?php echo number_format($precioOriginal, 2); ?>
                                    </p>

                                    <p>
                                        <strong>Precio promocional:</strong>
                                        <span class="text-success">
                                            $<?php echo number_format($precioPromocion, 2); ?>
                                        </span>
                                    </p>

                                    <p>
                                        <strong>Descuento:</strong>
                                        <span class="badge bg-danger">
                                            <?php echo $descuento; ?>% OFF
                                        </span>
                                    </p>

                                    <p>
                                        <strong>Inicio de la promoción:</strong>
                                        <?php
                                        echo !empty($producto['fecha_inicio'])
                                            ? date('d/m/Y', strtotime($producto['fecha_inicio']))
                                            : 'No disponible';
                                        ?>
                                    </p>

                                    <p>
                                        <strong>Fin de la promoción:</strong>
                                        <?php
                                        echo !empty($producto['fecha_fin'])
                                            ? date('d/m/Y', strtotime($producto['fecha_fin']))
                                            : 'No disponible';
                                        ?>
                                    </p>

                                </div>

                                <div class="modal-footer">

                                    <button
                                        type="button"
                                        class="btn btn-secondary"
                                        data-bs-dismiss="modal">

                                        Cerrar

                                    </button>

                                </div>

                            </div>

                        </div>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </div>

    <!-- JavaScript de Bootstrap para abrir y cerrar las ventanas -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
