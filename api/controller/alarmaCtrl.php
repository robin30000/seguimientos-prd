<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['method'])) {
    switch ($data['method']) {
        case 'nuevaAlarma':
            require_once '../class/alarma.php';
            $user = new alarma();
            $user->nuevaAlarma($data['data']);
            break;
        case 'editAlarma':
            require_once '../class/alarma.php';
            $user = new alarma();
            $user->editAlarma($data['data']);
            break;
        case 'listadoAlarmas':
            require_once '../class/alarma.php';
            $user = new alarma();
            $user->listadoAlarmas();
            break;
            case 'deleteAlarma':
            require_once '../class/alarma.php';
            $user = new alarma();
            $user->deleteAlarma($data['data']);
            break;

        default:
            echo 'ninguna opción valida.';
            break;
    }
} else {
    echo 'ninguna opción valida.';
}
