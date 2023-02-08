<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');

$data = json_decode(file_get_contents("php://input"),true);

if (isset($data['method'])) {
    switch ($data['method']) {
        case 'gestionBrutal':
            require_once '../class/otherServicesThree.php';
            $user = new otherServicesThree();
            $user->gestionBrutal($data['data']);
            break;
        case 'BuscarPedidoBrutal':
            require_once '../class/otherServicesThree.php';
            $user = new otherServicesThree();
            $user->BuscarPedidoBrutal($data['data']);
            break;
        case 'meses':
            require_once '../class/otherServicesThree.php';
            $user = new otherServicesThree();
            $user->meses();
            break;
        case 'mesesrepa':
            require_once '../class/otherServicesThree.php';
            $user = new otherServicesThree();
            $user->mesesrepa();
            break;
        case 'actualizarregion':
            require_once '../class/otherServicesThree.php';
            $user = new otherServicesThree();
            $user->actualizarregion();
            break;
        case 'departamentos':
            require_once '../class/otherServicesThree.php';
            $user = new otherServicesThree();
            $user->departamentos();
            break;
        case 'conceptospendientes':
            require_once '../class/otherServicesThree.php';
            $user = new otherServicesThree();
            $user->conceptospendientes($data['data']);
            break;
        case 'getConceptosTotales':
            require_once '../class/otherServicesThree.php';
            $user = new otherServicesThree();
            $user->getConceptosTotales($data['data']);
            break;
        case 'ResumenInsta':
            require_once '../class/otherServicesThree.php';
            $user = new otherServicesThree();
            $user->ResumenInsta($data['data']);
            break;
        case 'tipo_trabajoclick':
            require_once '../class/otherServicesThree.php';
            $user = new otherServicesThree();
            $user->tipo_trabajoclick();
            break;
        default:
            echo 'ninguna opción valida.';
            break;
    }
} else {
    echo 'ninguna opción valida.';
}
