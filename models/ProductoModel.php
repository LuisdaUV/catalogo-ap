<?php
require_once 'config/database.php';

class ProductoModel {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function obtenerProductosPromocion() {
        try {
            $query = "SELECT * FROM productos WHERE activo = TRUE AND fecha_fin >= CURDATE() ORDER BY fecha_inicio DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(Exception $e) {
            return [];
        }
    }
    public function obtenerProductoPorId($id) {
        try {
            // Asumo que tu llave primaria se llama 'id'. Si se llama 'id_producto', cámbialo aquí.
            $query = "SELECT * FROM productos WHERE id = :id";
            $stmt =$this->db->prepare($query);$stmt->bindParam(':id', $id, PDO::PARAM_INT);$stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(Exception $e) {
            return false;
        }
    }
}
?>