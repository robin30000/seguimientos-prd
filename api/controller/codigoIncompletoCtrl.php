<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');

$data = json_decode(file_get_contents("php://input"),true);

if (isset($data['method'])) {
    switch ($data['method']) {
        case 'getListaCodigoIncompleto':
            require_once '../class/codigoIncompleto.php';
            $user = new codigoIncompleto();
            $user->getListaCodigoIncompleto();
            break;
        case 'gestionarCodigoIncompleto':
            require_once '../class/codigoIncompleto.php';
            $user = new codigoIncompleto();
            $user->gestionarCodigoIncompleto($data['data']);
            break;
        case 'registroscodigoincompleto':
            require_once '../class/codigoIncompleto.php';
            $user = new codigoIncompleto();
            $user->registroscodigoincompleto($data['data']);
            break;
        case 'csvRegistrosCodigoIncompleto':
            require_once '../class/codigoIncompleto.php';
            $user = new codigoIncompleto();
            $user->csvRegistrosCodigoIncompleto($data['data']);
            break;

        default:
            echo 'ninguna opción valida.';
            break;
    }
} else {
    echo 'ninguna opción valida.';
}
