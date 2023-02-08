<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['method'])) {
    switch ($data['method']) {
        case 'escalamientoInfraestructura':
            require_once '../class/escalamiento.php';
            $user = new escalamiento();
            $user->escalamientoInfraestructura($data['data']);
            break;
        case 'GrupoCola':
            require_once '../class/escalamiento.php';
            $user = new escalamiento();
            $user->GrupoCola();
            break;
        case 'gestionEscalimiento':
            require_once '../class/escalamiento.php';
            $user = new escalamiento();
            $user->gestionEscalimiento();
            break;
        case 'observacionEscalimiento':
            require_once '../class/escalamiento.php';
            $user = new escalamiento();
            $user->observacionEscalimiento($data['data']);
            break;
        case 'notasEscalamiento':
            require_once '../class/escalamiento.php';
            $user = new escalamiento();
            $user->notasEscalamiento($data['data']);
            break;
        case 'infoEscalamiento':
            require_once '../class/escalamiento.php';
            $user = new escalamiento();
            $user->infoEscalamiento($data['data']);
            break;
        case 'csvEscalamientoExp':
            require_once '../class/escalamiento.php';
            $user = new escalamiento();
            $user->csvEscalamientoExp($data['data']);
            break;
        case 'saveescalamiento':
            require_once '../class/escalamiento.php';
            $user = new escalamiento();
            $user->saveescalamiento($data['data']);
            break;
        case 'exportEscalamientos':
            require_once '../class/escalamiento.php';
            $user = new escalamiento();
            $user->exportEscalamientos();
            break;

    }
} else {
    echo 'ninguna opción valida.';
}
