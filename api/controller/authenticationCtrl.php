<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');

$data = json_decode(file_get_contents("php://input"));

if (isset($data->method)) {
    switch ($data->method) {
        case 'login':
            require_once '../class/authentication.php';
            $user = new authentication();
            $user->login($data->data);
            break;
        case 'logout':
            require_once '../class/user.php';
            $user = new authentication();
            $user->logout();
            break;

        default:
            echo 'ninguna opción valida.';
            break;
    }
} else {
    echo 'ninguna opción valida.';
}
