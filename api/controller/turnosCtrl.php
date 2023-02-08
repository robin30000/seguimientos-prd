<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');

$data = json_decode(file_get_contents("php://input"),true);

if (isset($data['method'])) {
    switch ($data['method']) {
        case 'usuariosTurnos':
            require_once '../class/turnos.php';
            $user = new turnos();
            $user->usuariosTurnos();
            break;
        case 'listaTurnos':
            require_once '../class/turnos.php';
            $user = new turnos();
            $user->listaTurnos($data['data']);
            break;
        case 'cumpleTurnos':
            require_once '../class/turnos.php';
            $user = new turnos();
            $user->cumpleTurnos($data['data']);
            break;
        case 'guardarTurnos':
            require_once '../class/turnos.php';
            $user = new turnos();
            $user->guardarTurnos($data['data']);
            break;
        case 'updateTurno':
            require_once '../class/turnos.php';
            $user = new turnos();
            $user->updateTurno($data['data']);
            break;
        case 'CsvExporteAdherencia':
            require_once '../class/turnos.php';
            $user = new turnos();
            $user->CsvExporteAdherencia($data['data']);
            break;
        case 'deleteTurno':
            require_once '../class/turnos.php';
            $user = new turnos();
            $user->deleteTurno($data['data']);
            break;

        default:
            echo 'ninguna opción valida.';
            break;
    }
} else {
    echo 'ninguna opción valida.';
}
