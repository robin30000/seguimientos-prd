<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['method'])) {
    switch ($data['method']) {
        case 'insertarCambioEquipo':
            require_once '../class/otherServices.php';
            $user = new otherServices();
            $user->insertarCambioEquipo($data['data']);
            break;
        case 'GuardarPedidoEncuesta':
            require_once '../class/otherServices.php';
            $user = new otherServices();
            $user->GuardarPedidoEncuesta($data['data']);
            break;
        case 'gestiodespachoBrutal':
            require_once '../class/otherServices.php';
            $user = new otherServices();
            $user->gestiodespachoBrutal($data['data']);
            break;
        case 'gestionFinal':
            require_once '../class/otherServices.php';
            $user = new otherServices();
            $user->gestionFinal();
            break;
        case 'DashBoard':
            require_once '../class/otherServices.php';
            $user = new otherServices();
            $user->DashBoard();
            break;
        case 'gestionAsesorBrutal':
            require_once '../class/otherServices.php';
            $user = new otherServices();
            $user->gestionAsesorBrutal($data['data']);
            break;
        case 'savecontingencia':
            require_once '../class/otherServices.php';
            $user = new otherServices();
            $user->savecontingencia($data['data']);
            break;
        case 'CancelarContingencias':
            require_once '../class/otherServices.php';
            $user = new otherServices();
            $user->CancelarContingencias($data['data']);
            break;
        case 'guardarEscalar':
            require_once '../class/otherServices.php';
            $user = new otherServices();
            $user->guardarEscalar($data['data']);
            break;
        case 'gestionAsesorFinal':
            require_once '../class/otherServices.php';
            $user = new otherServices();
            $user->gestionAsesorFinal($data['data']);
            break;
        case 'gestionPendientes':
            require_once '../class/otherServices.php';
            $user = new otherServices();
            $user->gestionPendientes();
            break;
        case 'Pendientesxestado':
            require_once '../class/otherServices.php';
            $user = new otherServices();
            $user->Pendientesxestado($data['data']);
            break;

        default:
            echo 'ninguna opción valida.';
            break;
    }
} else {
    echo 'ninguna opción valida.';
}
