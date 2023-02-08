<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');

$data = json_decode(file_get_contents("php://input"),true);

if (isset($data['method'])) {
    switch ($data['method']) {
        case 'novedadesTecnico':
            require_once '../class/novedadesTecnico.php';
            $user = new novedadesTecnico();
            $user->novedadesTecnico($data['data']);
            break;
        case 'guardarNovedadesTecnico':
            require_once '../class/novedadesTecnico.php';
            $user = new novedadesTecnico();
            $user->guardarNovedadesTecnico($data['data']);
            break;
        case 'updateNovedadesTecnico':
            require_once '../class/novedadesTecnico.php';
            $user = new novedadesTecnico();
            $user->updateNovedadesTecnico($data['data']);
            break;
        case 'csvNovedadesTecnico':
            require_once '../class/novedadesTecnico.php';
            $user = new novedadesTecnico();
            $user->csvNovedadesTecnico($data['data']);
            break;
        case 'Regiones':
            require_once '../class/novedadesTecnico.php';
            $user = new novedadesTecnico();
            $user->Regiones();
            break;
        case 'Municipios':
            require_once '../class/novedadesTecnico.php';
            $user = new novedadesTecnico();
            $user->Municipios($data['region']);
            break;

        case 'SituacionNovedadesVisitas':
            require_once '../class/novedadesTecnico.php';
            $user = new novedadesTecnico();
            $user->SituacionNovedadesVisitas();
            break;

        case 'DetalleNovedadesVisitas':
            require_once '../class/novedadesTecnico.php';
            $user = new novedadesTecnico();
            $user->DetalleNovedadesVisitas($data['situacion']);
            break;

        case 'BFobservaciones':
            require_once '../class/novedadesTecnico.php';
            $user = new novedadesTecnico();
            $user->BFobservaciones($data['data']);
            break;
        case 'registrospwdTecnicos':
            require_once '../class/novedadesTecnico.php';
            $user = new novedadesTecnico();
            $user->registrospwdTecnicos($data['data']);
            break;
        case 'editarPwdTecnicos':
            require_once '../class/novedadesTecnico.php';
            $user = new novedadesTecnico();
            $user->editarPwdTecnicos($data['data']);
            break;
        case 'csvContrasenasTecnicos':
            require_once '../class/novedadesTecnico.php';
            $user = new novedadesTecnico();
            $user->csvContrasenasTecnicos();
            break;


        default:
            echo 'ninguna opción valida.';
            break;

    }
} else {
    echo 'ninguna opción valida.';
}
