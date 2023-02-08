<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');

$data = json_decode(file_get_contents("php://input"),true);

if (isset($data['method'])) {
    switch ($data['method']) {
        case 'resumenContingencias':
            require_once '../class/contingencia.php';
            $user = new contingencia();
            $user->resumencontingencias($data['data']);
            break;
        case 'datoscontingencias':
            require_once '../class/contingencia.php';
            $user = new contingencia();
            $user->datoscontingencias();
            break;

        case 'registrosOffline':
            require_once '../class/contingencia.php';
            $user = new contingencia();
            $user->registrosOffline();
            break;

        case 'graficaDepartamento':
            require_once '../class/contingencia.php';
            $user = new contingencia();
            $user->graficaDepartamento($data['data']);
            break;

        case 'marcaPortafolio':
            require_once '../class/contingencia.php';
            $user = new contingencia();
            $user->marcaPortafolio($data['data']);
            break;
        case 'guardarpedidocontingencia':
            require_once '../class/contingencia.php';
            $user = new contingencia();
            $user->guardarpedidocontingencia($data['data']);
            break;
        case 'guardarescalamiento':
            require_once '../class/contingencia.php';
            $user = new contingencia();
            $user->guardarescalamiento($data['data']);
            break;
        case 'cerrarMasivamenteContingencias':
            require_once '../class/contingencia.php';
            $user = new contingencia();
            $user->cerrarMasivamenteContingencias($data['data']);
            break;
        case 'guardarPedidoContingenciaPortafolio':
            require_once '../class/contingencia.php';
            $user = new contingencia();
            $user->guardarPedidoContingenciaPortafolio($data['data']);
            break;

        case 'garantiasInstalaciones':
            require_once '../class/contingencia.php';
            $user = new contingencia();
            $user->garantiasInstalaciones($data['data']);
            break;

        case 'graficaAcumulados':
            require_once '../class/contingencia.php';
            $user = new contingencia();
            $user->graficaAcumulados($data['data']);
            break;
        case 'graficaAcumuladosrepa':
            require_once '../class/contingencia.php';
            $user = new contingencia();
            $user->graficaAcumuladosrepa($data['data']);
            break;

        default:
            echo 'ninguna opción valida.';
            break;
    }
} else {
    echo 'ninguna opción valida.';
}
