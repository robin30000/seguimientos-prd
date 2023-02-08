<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');

$data = json_decode(file_get_contents("php://input"),true);

if (isset($data['method'])) {
    switch ($data['method']) {
        case 'insertarCambioEquipo':
            require_once '../class/otherServicesDos.php';
            $user = new otherServicesDos();
            $user->gestionBorrar($data['data']);
            break;
        case 'desbloquear':
            require_once '../class/otherServicesDos.php';
            $user = new otherServicesDos();
            $user->desbloquear($data['data']);
            break;
        case 'csvPreagen':
            require_once '../class/otherServicesDos.php';
            $user = new otherServicesDos();
            $user->csvPreagen($data['data']);
            break;
        case 'csvContingencias':
            require_once '../class/otherServicesDos.php';
            $user = new otherServicesDos();
            $user->csvContingencias($data['data']);
            break;
        case 'csvEstadosClick':
            require_once '../class/otherServicesDos.php';
            $user = new otherServicesDos();
            $user->csvEstadosClick($data['data']);
            break;
        case 'CsvpeniInsta':
            require_once '../class/otherServicesDos.php';
            $user = new otherServicesDos();
            $user->CsvpeniInsta($data['data']);
            break;
        case 'CsvGestionPendientes':
            require_once '../class/otherServicesDos.php';
            $user = new otherServicesDos();
            $user->CsvGestionPendientes($data['data']);
            break;
        case 'CsvNpsSemana':
            require_once '../class/otherServicesDos.php';
            $user = new otherServicesDos();
            $user->CsvNpsSemana($data['data']);
            break;
        case 'buscarPedido':
            require_once '../class/otherServicesDos.php';
            $user = new otherServicesDos();
            $user->buscarPedido($data['data']);
            break;
        case 'buscarPedidoSegui':
            require_once '../class/otherServicesDos.php';
            $user = new otherServicesDos();
            $user->buscarPedidoSegui($data['data']);
            break;
        case 'csvRegistros':
            require_once '../class/otherServicesDos.php';
            $user = new otherServicesDos();
            $user->csvRegistros($data['data']);
            break;
        case 'expBrutal':
            require_once '../class/otherServicesDos.php';
            $user = new otherServicesDos();
            $user->expBrutal($data['data']);
            break;
        case 'Csvtecnico':
            require_once '../class/otherServicesDos.php';
            $user = new otherServicesDos();
            $user->Csvtecnico($data['data']);
            break;
        case 'diferenciasClick':
            require_once '../class/otherServicesDos.php';
            $user = new otherServicesDos();
            $user->diferenciasClick($data['data']);
            break;
        case 'observacionAsesor':
            require_once '../class/otherServicesDos.php';
            $user = new otherServicesDos();
            $user->observacionAsesor($data['data']);
            break;
        case 'contadorpedientesBF':
            require_once '../class/otherServicesDos.php';
            $user = new otherServicesDos();
            $user->contadorpedientesBF();
            break;
        case 'seguimientoClick':
            require_once '../class/otherServicesDos.php';
            $user = new otherServicesDos();
            $user->seguimientoClick($data['data']);
            break;
        case 'registrosComercial':
            require_once '../class/otherServicesDos.php';
            $user = new otherServicesDos();
            $user->registrosComercial($data['data']);
            break;


        default:
            echo 'ninguna opción valida.';
            break;
    }
} else {
    echo 'ninguna opción valida.';
}
