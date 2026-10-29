<?php
require_once 'config/database.php';

class UsuarioModel {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function obtenerUsuarioPorCorreo($correo) {
    // Asegúrate de que sea SELECT * o que incluya 'password'
    $query = "SELECT * FROM usuarios WHERE correo = :correo";
    $stmt = $this->db->prepare($query);
    $stmt->bindParam(':correo', $correo);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}



    // NUEVOS MÉTODOS PARA EL REGISTRO
    
    public function verificarExistencia($documento, $correo) {
        $query = "SELECT id FROM usuarios WHERE numero_documento = :documento OR correo = :correo";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':documento', $documento);
        $stmt->bindParam(':correo', $correo);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }

    public function registrarUsuario($datos) {
        $query = "INSERT INTO usuarios (numero_documento, nombre, apellidos, correo, ciudad, pais, password) 
                  VALUES (:documento, :nombre, :apellidos, :correo, :ciudad, :pais, :password)";
        $stmt = $this->db->prepare($query);
        
        $stmt->bindParam(':documento', $datos['numero_documento']);
        $stmt->bindParam(':nombre', $datos['nombre']);
        $stmt->bindParam(':apellidos', $datos['apellidos']);
        $stmt->bindParam(':correo', $datos['correo']);
        $stmt->bindParam(':ciudad', $datos['ciudad']);
        $stmt->bindParam(':pais', $datos['pais']);
        $stmt->bindParam(':password', $datos['password']);
        
        return $stmt->execute();
    }
}
?>