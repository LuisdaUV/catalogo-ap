<?php
require_once 'models/ProductoModel.php';

class DetalleController {
    public function index() {
        session_start();
        
        // Verificación de seguridad
        if(!isset($_SESSION['user_id'])) {
            header("Location: index.php?controller=login");
            exit();
        }

        // Verificar que se haya enviado un ID por la URL
        if(!isset($_GET['id']) || empty($_GET['id'])) {
            header("Location: index.php?controller=catalogo");
            exit();
        }

        $id = $_GET['id'];

        // Obtener los datos del producto
        $productoModel = new ProductoModel();
        $producto = $productoModel->obtenerProductoPorId($id);

        // Si el producto no existe o fue alterada la URL, devolver al catálogo
        if(!$producto) {
            header("Location: index.php?controller=catalogo");
            exit();
        }

        // Cargar la vista de detalle
        require_once 'views/detalle.php';
    }
}
?>