<?php
// Enrutador básico (Front Controller)

// Por defecto, carga el HomeController si no se especifica otro
$controllerName = isset($_GET['controller']) ? $_GET['controller'] : 'home';

// Formatea el nombre (ej. 'login' se convierte en 'LoginController')
$controllerClass = ucfirst($controllerName) . 'Controller';
$controllerFile = 'controllers/' . $controllerClass . '.php';

// Verifica si el archivo del controlador existe y lo ejecuta
if (file_exists($controllerFile)) {
    require_once $controllerFile;
    $controller = new $controllerClass();
    $controller->index();
} else {
    echo "Error 404: El controlador no existe.";
}

?>