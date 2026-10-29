<?php
require_once 'models/ProductoModel.php';

class CatalogoController {
    public function index() {
        session_start();
        
        // Verificación de seguridad
        if(!isset($_SESSION['user_id'])) {
            header("Location: index.php?controller=login");
            exit();
        }

        // Obtener datos del modelo
        $productoModel = new ProductoModel();
        $productos = $productoModel->obtenerProductosPromocion();

        // Cargar la vista
        require_once 'views/catalogo.php';
    }
}
?>