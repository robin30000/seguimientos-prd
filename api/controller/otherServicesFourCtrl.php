<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');

$data = json_decode(file_get_contents("php://input"),true);

if (isset($data['method'])) {
    switch ($data['method']) {
        case 'UenCargada':
            require_once '../class/otherServicesFour.php';
            $user = new otherServicesFour();
            $user->UenCargada();
            break;
        case 'gestionComercial':
            require_once '../class/otherServicesFour.php';
            $user = new otherServicesFour();
            $user->gestionComercial();
            break;
        case 'causaRaiz':
            require_once '../class/otherServicesFour.php';
            $user = new otherServicesFour();
            $user->causaRaiz();
            break;
        case 'ResponsablePendiente':
            require_once '../class/otherServicesFour.php';
            $user = new otherServicesFour();
            $user->ResponsablePendiente($data['data']);
            break;
        case 'listaCausaRaiz':
            require_once '../class/otherServicesFour.php';
            $user = new otherServicesFour();
            $user->listaCausaRaiz($data['data']);
            break;
        case 'Causasraizinconsitencias':
            require_once '../class/otherServicesFour.php';
            $user = new otherServicesFour();
            $user->Causasraizinconsitencias();
            break;
        case 'pendiBrutal':
            require_once '../class/otherServicesFour.php';
            $user = new otherServicesFour();
            $user->pendiBrutal();
            break;
        case 'clasificacionComercial':
            require_once '../class/otherServicesFour.php';
            $user = new otherServicesFour();
            $user->clasificacionComercial($data['data']);
            break;
        case 'buscaregistros':
            require_once '../class/otherServicesFour.php';
            $user = new otherServicesFour();
            $user->buscaregistros($data['data']);
            break;
            case 'guardarRecogerEquipos':
            require_once '../class/otherServicesFour.php';
            $user = new otherServicesFour();
            $user->guardarRecogerEquipos($data['data']);
            break;

    }
} else {
    echo 'ninguna opción valida.';
}
