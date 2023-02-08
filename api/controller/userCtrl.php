<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');

$data = json_decode(file_get_contents("php://input"),true);

if (isset($data['method'])) {
    switch ($data['method']) {
        case 'login':
            require_once '../class/user.php';
            $user = new user();
            $user->editarUsuario($data['data']);
            break;
        case 'editarRegistro':
            require_once '../class/user.php';
            $user = new user();
            $user->editarRegistro($data['data']);
            break;
        case 'CrearpedidoComercial':
            require_once '../class/user.php';
            $user = new user();
            $user->CrearpedidoComercial($data['data']);
            break;
        case 'guardarPlan':
            require_once '../class/user.php';
            $user = new user();
            $user->guardarPlan($data['data']);
            break;
        case 'CrearpedidoOffline':
            require_once '../class/user.php';
            $user = new user();
            $user->CrearpedidoOffline($data['data']);
            break;
        case 'ingresarPedidoAsesor':
            require_once '../class/user.php';
            $user = new user();
            $user->ingresarPedidoAsesor($data['data']);
            break;
        case 'creaUsuario':
            require_once '../class/user.php';
            $user = new user();
            $user->creaUsuario($data['data']);
            break;
        case 'creaTecnico':
            require_once '../class/user.php';
            $user = new user();
            $user->creaTecnico($data['data']);
            break;
        case 'listadoUsuarios':
            require_once '../class/user.php';
            $user = new user();
            $user->listadoUsuarios($data['data']);
            break;
        case 'borrarUsuario':
            require_once '../class/user.php';
            $user = new user();
            $user->borrarUsuario($data['data']);
            break;
        case 'borrarTecnico':
            require_once '../class/user.php';
            $user = new user();
            $user->borrarTecnico($data['data']);
            break;
            case 'editarTecnico':
            require_once '../class/user.php';
            $user = new user();
            $user->editarTecnico($data['data']);
            break;


        default:
            echo 'ninguna opción valida.';
            break;
    }
} else {
    echo 'ninguna opción valida.';
}
