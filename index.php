<?php

require_once "vendor/autoload.php";

$app = new \Slim\Slim();

$db = new mysqli('localhost', 'root', '', 'curso_angular4');

// Configuración de cabeceras
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
$method = $_SERVER['REQUEST_METHOD'];
if ($method == "OPTIONS") {
    die();
}

$app->get("/pruebas", function () use ($app) {
    echo "Hola mundo desde Slim PHP";
});

// Listar todos los productos
$app->get("/productos", function () use ($app, $db) {
    $sql = "select * from productos order by id desc;";
    $query = $db->query($sql);
    while ($producto = $query->fetch_assoc()) {
        $productos[] = $producto;
    }

    $result = array(
        'status' => 'success',
        'code' => 200,
        'data' => $productos,
    );

    echo json_encode($result);
});

// Devolver un solo producto
$app->get("/productos/:id", function ($id) use ($app, $db) {
    $sql = "select * from productos where id = $id;";
    $query = $db->query($sql);

    $result = array(
        'status' => 'error',
        'code' => 404,
        'message' => 'Producto no disponible',
    );

    if ($query->num_rows == 1) {
        $producto = $query->fetch_assoc();

        $result = array(
            'status' => 'success',
            'code' => 200,
            'data' => $producto,
        );
    }

    echo json_encode($result);
});

// Eliminar un producto
$app->get("/delete-producto/:id", function ($id) use ($app, $db) {
    $sql = "delete from productos where id = $id";
    $query = $db->query($sql);

    if ($query) {
        $result = array(
            'status' => 'success',
            'code' => 200,
            'data' => 'El producto se ha eliminado correctamente',
        );
    } else {
        $result = array(
            'status' => 'error',
            'code' => 404,
            'message' => 'El producto no se ha eliminado',
        );
    }

    echo json_encode($result);
});

// Actualizar un producto
$app->post("/update-producto/:id", function ($id) use ($app, $db) {
    $json = $app->request->post('json');
    $data = json_decode($json, true);

    $sql = "update productos set " .
        "nombre = '{$data["nombre"]}', " .
        "descripcion = '{$data["descripcion"]}', ";

    if (isset($data['imagen'])) {
        $sql .= "imagen = '{$data["imagen"]}', ";
    }

    $sql .= "precio = '{$data["precio"]}'" .
        "where id = $id";

    $query = $db->query($sql);

    if ($query) {
        $result = array(
            'status' => 'success',
            'code' => 200,
            'data' => 'El producto se ha actualizado correctamente',
        );
    } else {
        $result = array(
            'status' => 'error',
            'code' => 404,
            'message' => 'El producto no se ha actualizado',
        );
    }

    echo json_encode($result);
});

// Subir una imagen a un producto
$app->post("/upload-file", function () use ($app, $db) {
    $result = array(
        'status' => 'error',
        'code' => 404,
        'message' => 'El archivo no ha podido subirse',
    );

    if (isset($_FILES['uploads'])) {
        $piramideUploader = new PiramideUploader();

        $upload = $piramideUploader->upload('image', 'uploads', 'uploads', array('image/jpeg', 'image/png', 'image/gif'));
        $file = $piramideUploader->getInfoFile();
        $file_name = $file['complete_name'];

        if (isset($upload) && $upload['uploaded'] == false) {
            $result = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'El archivo no ha podido subirse',
            );
        } else {
            $result = array(
                'status' => 'success',
                'code' => 200,
                'message' => 'El archivo se ha subido',
                'filename' => $file_name,
            );
        }
    }

    echo json_encode($result);
});

// Guardar productos
$app->post("/productos", function () use ($app, $db) {
    $json = $app->request->post('json');
    $data = json_decode($json, true);

    if (!isset($data['nombre'])) {
        $data['nombre'] = null;
    }

    if (!isset($data['descripcion'])) {
        $data['descripcion'] = null;
    }

    if (!isset($data['precio'])) {
        $data['precio'] = null;
    }

    if (!isset($data['imagen'])) {
        $data['imagen'] = null;
    }

    $query = "insert into productos values(null," .
        "'{$data['nombre']}'," .
        "'{$data['descripcion']}'," .
        "'{$data['precio']}'," .
        "'{$data['imagen']}'" .
        ")";

    $insert = $db->query($query);

    $result = array(
        'status' => 'error',
        'code' => 404,
        'message' => 'Producto NO se ha creado correctamente',
    );

    if ($insert) {
        $result = array(
            'status' => 'success',
            'code' => 200,
            'message' => 'Producto creado correctamente',
        );
    }

    echo json_encode($result);
});

$app->run();
