<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');

$data = json_decode(file_get_contents("php://input"),true);

if (isset($data['method'])) {
    switch ($data['method']) {
        case 'visitasEnConjunto':
            require_once '../class/visitasEnConjunto.php';
            $user = new visitasEnConjunto();
            $user->visitasEnConjunto($data['data']);
            break;
        case 'GrupoVisitasEnConjunto':
            require_once '../class/visitasEnConjunto.php';
            $user = new visitasEnConjunto();
            $user->GrupoVisitasEnConjunto();
            break;
        case 'infoVisitasEnConjunto':
            require_once '../class/visitasEnConjunto.php';
            $user = new visitasEnConjunto();
            $user->infoVisitasEnConjunto($data['data']);
            break;
        case 'expCsvVisitasEnConjunto':
            require_once '../class/visitasEnConjunto.php';
            $user = new visitasEnConjunto();
            $user->expCsvVisitasEnConjunto($data['data']);
            break;
        case 'RegionesVisConjunto':
            require_once '../class/visitasEnConjunto.php';
            $user = new visitasEnConjunto();
            $user->RegionesVisConjunto();
            break;
        case 'MunicipiosVisConjunto':
            require_once '../class/visitasEnConjunto.php';
            $user = new visitasEnConjunto();
            $user->MunicipiosVisConjunto($data['data']);
            break;
        case 'MunicipioVisConjuntoUpdate':
            require_once '../class/visitasEnConjunto.php';
            $user = new visitasEnConjunto();
            $user->MunicipioVisConjuntoUpdate($data['data']);
            break;

        default:
            echo 'ninguna opción valida.';
            break;
    }
} else {
    echo 'ninguna opción valida.';
}
