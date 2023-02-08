<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');

$data = json_decode(file_get_contents("php://input"),true);

if (isset($data['method'])) {

    switch ($data['method']) {

        case'listadoEstadosClick':
            require_once '../class/otrosServiciosDos.php';
            $user = new otrosServiciosDos();
            $user->listadoEstadosClick($data['data']);
            break;
        case'BuscarPedidoinsta':
            require_once '../class/otrosServiciosDos.php';
            $user = new otrosServiciosDos();
            $user->BuscarPedidoinsta($data['data']);
            break;
        case'GuardarPedidoPendiInsta':
            require_once '../class/otrosServiciosDos.php';
            $user = new otrosServiciosDos();
            $user->GuardarPedidoPendiInsta($data['data']);
            break;

        case'deleteregistrosCarga':
            require_once '../class/otrosServiciosDos.php';
            $user = new otrosServiciosDos();
            $user->deleteregistrosCarga($data['data']);
            break;

        case'Accionesoffline':
            require_once '../class/otrosServiciosDos.php';
            $user = new otrosServiciosDos();
            $user->Accionesoffline($data['data']);
            break;
        case'acciones':
            require_once '../class/otrosServiciosDos.php';
            $user = new otrosServiciosDos();
            $user->acciones($data['data']);
            break;
        case'Codigos':
            require_once '../class/otrosServiciosDos.php';
            $user = new otrosServiciosDos();
            $user->Codigos($data['data']);
            break;
        case'Diagnosticos':
            require_once '../class/otrosServiciosDos.php';
            $user = new otrosServiciosDos();
            $user->Diagnosticos($data['data']);
            break;
        default:
            echo 'ninguna opción valida.';
            break;

    }
} else {
    echo 'ninguna opción valida.';
}
