<?php

require_once '../model/modelAuth.php';
date_default_timezone_set('America/Bogota');

class authentication
{
    public $_model;

    public function __construct()
    {
        $this->_model = new modelauthentication();
    }

    public function login($data)
    {

        $usuarioid = strtoupper(trim($data->username));
        $password  = trim($data->password);

        $usuarioid = htmlentities($usuarioid, ENT_QUOTES);
        $password  = htmlentities($password, ENT_QUOTES);

        if (empty($usuarioid) || empty($password)) {
            $body = 'Error';
            http_response_code(406);
            header("Content-type: application/json; charset=utf-8");
            echo json_encode($body);
            die();
        }


        $this->_model->loginUser($usuarioid, $password);

    }

    public function logout()
    {
        $this->_model->updatesalida();
    }

}
