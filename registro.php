
<?php

session_start();

require_once 'config/database.php';

$catalogo_ciudades = require 'config/ciudades.php';

/**
 * Normaliza texto para comparar ciudades y países.
 */
function normalizarTexto($texto) {
    $texto = mb_strtolower(trim($texto), 'UTF-8');

    return strtr($texto, [
        'á' => 'a',
        'é' => 'e',
        'í' => 'i',
        'ó' => 'o',
        'ú' => 'u',
        'ü' => 'u'
    ]);
}

/**
 * Busca una ciudad en el catálogo y devuelve su país.
 */
function buscarPaisPorCiudad($ciudad, $catalogo) {
    foreach ($catalogo as $pais => $ciudades) {
        foreach ($ciudades as $nombreCiudad) {
            if (
                normalizarTexto($nombreCiudad) ===
                normalizarTexto($ciudad)
            ) {
                return [
                    'ciudad' => $nombreCiudad,
                    'pais' => $pais
                ];
            }
        }
    }

    return null;
}

// Variables del formulario.
$mensaje = '';
$tipo_mensaje = '';
$errores = [];

$numero_documento = '';
$nombre = '';
$apellidos = '';
$correo = '';
$ciudad = '';
$pais = '';
$password = '';
$confirm_password = '';

// Procesar el formulario.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $numero_documento = trim($_POST['numero_documento'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $apellidos = trim($_POST['apellidos'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $ciudad = trim($_POST['ciudad'] ?? '');
    $pais = trim($_POST['pais'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validar documento.
    if ($numero_documento === '') {
        $errores['numero_documento'] =
            'El documento es obligatorio.';
    }

    // Validar nombre.
    if ($nombre === '') {
        $errores['nombre'] =
            'El nombre es obligatorio.';
    }

    // Validar apellidos.
    if ($apellidos === '') {
        $errores['apellidos'] =
            'Los apellidos son obligatorios.';
    }

    // Validar correo electrónico.
    if ($correo === '') {
        $errores['correo'] =
            'El correo electrónico es obligatorio.';
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errores['correo'] =
            'El correo electrónico no es válido.';
    }

    // Validar ciudad y país.
    if ($ciudad === '') {
        $errores['ciudad'] =
            'La ciudad es obligatoria.';
    }

    if ($pais === '') {
        $errores['pais'] =
            'El país es obligatorio.';
    }

    // Validar contraseña.
    if ($password === '') {
        $errores['password'] =
            'La contraseña es obligatoria.';
    } elseif (strlen($password) < 6) {
        $errores['password'] =
            'La contraseña debe tener al menos 6 caracteres.';
    }

    // Confirmar contraseña.
    if ($confirm_password === '') {
        $errores['confirm_password'] =
            'Debes confirmar la contraseña.';
    } elseif ($password !== $confirm_password) {
        $errores['confirm_password'] =
            'Las contraseñas no coinciden.';
    }

    // Comprobar que la ciudad exista en el catálogo.
    if ($ciudad !== '') {
        $coincidencia = buscarPaisPorCiudad(
            $ciudad,
            $catalogo_ciudades
        );

        if ($coincidencia === null) {
            $errores['ciudad'] =
                'La ciudad no está en el catálogo. Verifica su nombre.';
        } else {
            // Comprobar que el país corresponda a la ciudad.
            if (
                $pais !== '' &&
                normalizarTexto($pais) !==
                normalizarTexto($coincidencia['pais'])
            ) {
                $errores['pais'] =
                    'El país no corresponde a la ciudad seleccionada.';
            } else {
                // Usar los nombres oficiales del catálogo.
                $ciudad = $coincidencia['ciudad'];
                $pais = $coincidencia['pais'];
            }
        }
    }

    // Registrar únicamente si no existen errores.
    if (empty($errores)) {

        try {
            $database = new Database();
            $db = $database->getConnection();

            // Comprobar documento o correo duplicados.
            $query = "SELECT id
                      FROM usuarios
                      WHERE numero_documento = :documento
                         OR correo = :correo";

            $stmt = $db->prepare($query);

            $stmt->bindParam(
                ':documento',
                $numero_documento
            );

            $stmt->bindParam(
                ':correo',
                $correo
            );

            $stmt->execute();

            if ($stmt->fetch(PDO::FETCH_ASSOC)) {

                $mensaje =
                    'El número de documento o correo ya está registrado.';

                $tipo_mensaje = 'danger';

            } else {

                // Encriptar la contraseña antes de guardarla.
                $password_hash = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                // Insertar el usuario.
                $query = "INSERT INTO usuarios
                    (
                        numero_documento,
                        nombre,
                        apellidos,
                        correo,
                        ciudad,
                        pais,
                        password
                    )
                    VALUES
                    (
                        :documento,
                        :nombre,
                        :apellidos,
                        :correo,
                        :ciudad,
                        :pais,
                        :password
                    )";

                $stmt = $db->prepare($query);

                $stmt->bindParam(
                    ':documento',
                    $numero_documento
                );

                $stmt->bindParam(
                    ':nombre',
                    $nombre
                );

                $stmt->bindParam(
                    ':apellidos',
                    $apellidos
                );

                $stmt->bindParam(
                    ':correo',
                    $correo
                );

                $stmt->bindParam(
                    ':ciudad',
                    $ciudad
                );

                $stmt->bindParam(
                    ':pais',
                    $pais
                );

                $stmt->bindParam(
                    ':password',
                    $password_hash
                );

                if ($stmt->execute()) {

                    $mensaje =
                        'Registro exitoso. Ahora puedes iniciar sesión.';

                    $tipo_mensaje = 'success';

                    // Limpiar el formulario después del éxito.
                    $numero_documento = '';
                    $nombre = '';
                    $apellidos = '';
                    $correo = '';
                    $ciudad = '';
                    $pais = '';
                    $password = '';
                    $confirm_password = '';

                } else {

                    $mensaje =
                        'Error al registrar el usuario.';

                    $tipo_mensaje = 'danger';
                }
            }

        } catch (Exception $e) {

            $mensaje =
                'Ocurrió un error al procesar el registro.';

            $tipo_mensaje = 'danger';

            // Guardar el detalle técnico en el registro del servidor.
            error_log($e->getMessage());
        }

    } else {

        $mensaje =
            'Por favor, corrige los errores en el formulario.';

        $tipo_mensaje = 'danger';
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Registro de Usuario</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="assets/style.css">
</head>

<body>

    <div class="container">

        <div class="row justify-content-center align-items-center min-vh-100">

            <div class="col-md-6 col-lg-5">

                <div class="card shadow">

                    <div class="card-body p-5">

                        <h2 class="text-center mb-4">
                            Registro de Usuario
                        </h2>

                        <?php if ($mensaje !== ''): ?>

                            <div
                                class="alert alert-<?php echo htmlspecialchars($tipo_mensaje, ENT_QUOTES, 'UTF-8'); ?>"
                                role="alert"
                            >
                                <?php
                                echo htmlspecialchars(
                                    $mensaje,
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                                ?>
                            </div>

                        <?php endif; ?>

                        <form
                            method="POST"
                            action="registro.php"
                            novalidate
                        >

                            <!-- Número de documento -->
                            <div class="mb-3">

                                <label
                                    for="numero_documento"
                                    class="form-label"
                                >
                                    Número de Documento
                                </label>

                                <input
                                    type="text"
                                    class="form-control <?php echo isset($errores['numero_documento']) ? 'is-invalid' : ''; ?>"
                                    id="numero_documento"
                                    name="numero_documento"
                                    value="<?php echo htmlspecialchars($numero_documento, ENT_QUOTES, 'UTF-8'); ?>"
                                    required
                                >

                                <div class="invalid-feedback">
                                    <?php
                                    echo htmlspecialchars(
                                        $errores['numero_documento'] ?? '',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    );
                                    ?>
                                </div>

                            </div>

                            <!-- Nombre y apellidos -->
                            <div class="row">

                                <div class="col-md-6 mb-3">

                                    <label
                                        for="nombre"
                                        class="form-label"
                                    >
                                        Nombre
                                    </label>

                                    <input
                                        type="text"
                                        class="form-control <?php echo isset($errores['nombre']) ? 'is-invalid' : ''; ?>"
                                        id="nombre"
                                        name="nombre"
                                        value="<?php echo htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8'); ?>"
                                        required
                                    >

                                    <div class="invalid-feedback">
                                        <?php
                                        echo htmlspecialchars(
                                            $errores['nombre'] ?? '',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        );
                                        ?>
                                    </div>

                                </div>

                                <div class="col-md-6 mb-3">

                                    <label
                                        for="apellidos"
                                        class="form-label"
                                    >
                                        Apellidos
                                    </label>

                                    <input
                                        type="text"
                                        class="form-control <?php echo isset($errores['apellidos']) ? 'is-invalid' : ''; ?>"
                                        id="apellidos"
                                        name="apellidos"
                                        value="<?php echo htmlspecialchars($apellidos, ENT_QUOTES, 'UTF-8'); ?>"
                                        required
                                    >

                                    <div class="invalid-feedback">
                                        <?php
                                        echo htmlspecialchars(
                                            $errores['apellidos'] ?? '',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        );
                                        ?>
                                    </div>

                                </div>

                            </div>

                            <!-- Correo electrónico -->
                            <div class="mb-3">

                                <label
                                    for="correo"
                                    class="form-label"
                                >
                                    Correo Electrónico
                                </label>

                                <input
                                    type="email"
                                    class="form-control <?php echo isset($errores['correo']) ? 'is-invalid' : ''; ?>"
                                    id="correo"
                                    name="correo"
                                    value="<?php echo htmlspecialchars($correo, ENT_QUOTES, 'UTF-8'); ?>"
                                    required
                                >

                                <div class="invalid-feedback">
                                    <?php
                                    echo htmlspecialchars(
                                        $errores['correo'] ?? '',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    );
                                    ?>
                                </div>

                            </div>

                            <!-- Ciudad y país -->
                            <div class="row">

                                <div class="col-md-6 mb-3">

                                    <label
                                        for="ciudad"
                                        class="form-label"
                                    >
                                        Ciudad
                                    </label>

                                    <input
                                        type="text"
                                        class="form-control <?php echo isset($errores['ciudad']) ? 'is-invalid' : ''; ?>"
                                        id="ciudad"
                                        name="ciudad"
                                        list="lista-ciudades"
                                        value="<?php echo htmlspecialchars($ciudad, ENT_QUOTES, 'UTF-8'); ?>"
                                        required
                                    >

                                    <datalist id="lista-ciudades">

                                        <?php foreach ($catalogo_ciudades as $nombrePais => $ciudades): ?>

                                            <?php foreach ($ciudades as $nombreCiudad): ?>

                                                <option
                                                    value="<?php echo htmlspecialchars($nombreCiudad, ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-pais="<?php echo htmlspecialchars($nombrePais, ENT_QUOTES, 'UTF-8'); ?>"
                                                ></option>

                                            <?php endforeach; ?>

                                        <?php endforeach; ?>

                                    </datalist>

                                    <div class="invalid-feedback">
                                        <?php
                                        echo htmlspecialchars(
                                            $errores['ciudad'] ?? '',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        );
                                        ?>
                                    </div>

                                </div>

                                <div class="col-md-6 mb-3">

                                    <label
                                        for="pais"
                                        class="form-label"
                                    >
                                        País
                                    </label>

                                    <input
                                        type="text"
                                        class="form-control <?php echo isset($errores['pais']) ? 'is-invalid' : ''; ?>"
                                        id="pais"
                                        name="pais"
                                        value="<?php echo htmlspecialchars($pais, ENT_QUOTES, 'UTF-8'); ?>"
                                        required
                                    >

                                    <div class="invalid-feedback">
                                        <?php
                                        echo htmlspecialchars(
                                            $errores['pais'] ?? '',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        );
                                        ?>
                                    </div>

                                </div>

                            </div>

                            <!-- Contraseña -->
                            <div class="mb-3">

                                <label
                                    for="password"
                                    class="form-label"
                                >
                                    Contraseña
                                </label>

                                <input
                                    type="password"
                                    class="form-control <?php echo isset($errores['password']) ? 'is-invalid' : ''; ?>"
                                    id="password"
                                    name="password"
                                    required
                                >

                                <div class="invalid-feedback">
                                    <?php
                                    echo htmlspecialchars(
                                        $errores['password'] ?? '',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    );
                                    ?>
                                </div>

                            </div>

                            <!-- Confirmar contraseña -->
                            <div class="mb-3">

                                <label
                                    for="confirm_password"
                                    class="form-label"
                                >
                                    Confirmar Contraseña
                                </label>

                                <input
                                    type="password"
                                    class="form-control <?php echo isset($errores['confirm_password']) ? 'is-invalid' : ''; ?>"
                                    id="confirm_password"
                                    name="confirm_password"
                                    required
                                >

                                <div class="invalid-feedback">
                                    <?php
                                    echo htmlspecialchars(
                                        $errores['confirm_password'] ?? '',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    );
                                    ?>
                                </div>

                            </div>

                            <button
                                type="submit"
                                class="btn btn-primary w-100"
                            >
                                Registrarse
                            </button>

                        </form>

                        <div class="text-center mt-3">

                            <p class="mb-0">
                                ¿Ya tienes cuenta?
                                <a href="login.php">
                                    Inicia sesión aquí
                                </a>
                            </p>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Autocompletar el país al escribir o seleccionar una ciudad.
        (function () {

            const normalizar = texto =>
                texto.trim()
                    .toLowerCase()
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '');

            const inputCiudad = document.getElementById('ciudad');
            const inputPais = document.getElementById('pais');
            const opciones = document.querySelectorAll(
                '#lista-ciudades option'
            );

            const mapa = {};

            opciones.forEach(opcion => {
                mapa[normalizar(opcion.value)] =
                    opcion.dataset.pais;
            });

            function actualizarPais() {
                const ciudadNormalizada =
                    normalizar(inputCiudad.value);

                if (mapa[ciudadNormalizada]) {
                    inputPais.value =
                        mapa[ciudadNormalizada];
                }
            }

            inputCiudad.addEventListener('input', actualizarPais);
            inputCiudad.addEventListener('change', actualizarPais);

            actualizarPais();

        })();
    </script>

</body>

</html>
