<?php

require_once '../model/modelUser.php';
date_default_timezone_set('America/Bogota');

class user
{
    public $_model;

    public function __construct()
    {
        $this->_model = new modelUser();
    }

    public function editarUsuario($data)
    {
        $this->_model->editarUsuario($data);
    }

    public function editarRegistro($data)
    {
        $this->_model->editarRegistro($data);
    }

    public function CrearpedidoComercial($data)
    {
        $this->_model->CrearpedidoComercial($data);
    }

    public function guardarPlan($data)
    {
        $this->_model->guardarPlan($data);
    }

    public function CrearpedidoOffline($data)
    {
        $this->_model->CrearpedidoOffline($data);
    }

    public function ingresarPedidoAsesor($data)
    {
        $this->_model->ingresarPedidoAsesor($data);
    }

    public function creaUsuario($data)
    {
        $this->_model->creaUsuario($data);
    }

    public function creaTecnico($data)
    {
        $this->_model->creaTecnico($data);
    }

    public function listadoUsuarios($data)
    {
        $this->_model->listadoUsuarios($data);
    }

    public function borrarUsuario($data)
    {
        $this->_model->borrarUsuario($data);
    }

    public function borrarTecnico($data)
    {
        $this->_model->borrarTecnico($data);
    }

    public function editarTecnico($data)
    {
        $this->_model->editarTecnico($data);
    }

}
