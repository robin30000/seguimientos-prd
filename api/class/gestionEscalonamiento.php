<?php
require_once '../model/modelGestionEscalonamiento.php';
require_once 'utils.php';

class gestionEscalonamiento
{
    public $_model;

    public $_utils;

    public function __construct()
    {
        $this->_model = new modelGestionEscalonamiento();
        $this->_utils = new utils();
    }

    public function gestionEscalonamiento()
    {

        $response = $this->_model->gestionEscalonamiento();
        $this->_utils->response($this->_utils->json([$response]), 201);
    }

    public function datosescalamientosprioridad2()
    {
        $response = $this->_model->datosescalamientosprioridad2();
        $this->_utils->response($this->_utils->json([$response]), 201);
    }
}
