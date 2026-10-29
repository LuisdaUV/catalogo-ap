<?php
class HomeController {
    public function index() {
        session_start();
        
        // Si ya hay sesión, redirigir al controlador del catálogo
        if(isset($_SESSION['user_id'])) {
            header("Location: index.php?controller=catalogo");
            exit();
        }
        
        // Si no hay sesión, mostrar la vista principal (landing page)
        require_once 'views/login.php';
    }
}
?>