<?php
require_once __DIR__ . '/../models/admin-usuariosModel.php';

class EstudiantesController {

    public function listar() {
        $est = new Estudiantes();
        return $est->getAll();
    }

    public function crear($post, $files) {
        $est = new Estudiantes();

        // Imagen (opcional)
        $foto = null;
        if (!empty($files['foto_est']['name']) && $files['foto_est']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = dirname(__DIR__) . '/uploads/estudiantes';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }

            $extension = strtolower(pathinfo($files['foto_est']['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (in_array($extension, $allowedExtensions, true)) {
                $nombre = uniqid('est_', true) . '.' . $extension;
                $destino = $uploadDir . DIRECTORY_SEPARATOR . $nombre;

                if (move_uploaded_file($files['foto_est']['tmp_name'], $destino)) {
                    $foto = $nombre;
                }
            }
        }

        $post['foto_est'] = $foto;

        return $est->create($post);
    }

    public function eliminar($id) {
        $est = new Estudiantes();
        return $est->delete($id);
    }
}
