<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');

$data = json_decode(file_get_contents("php://input"),true);

if (isset($data['method'])) {
    switch ($data['method']) {
        case 'premisasInfraestructuras':
            require_once '../class/generacionTt.php';
            $user = new generacionTt();
            $user->premisasInfraestructuras($data['data']);
            break;
        case 'guardarGeneracionTT':
            require_once '../class/contingencia.php';
            $user = new generacionTt();
            $user->guardarGeneracionTT($data['data']);
            break;
        case 'csvGeneracionTT':
            require_once '../class/contingencia.php';
            $user = new generacionTt();
            $user->csvGeneracionTT($data['data']);
            break;

    }
} else {
    echo 'ninguna opción valida.';
}
