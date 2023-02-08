<?php

require_once '../model/modelAlarma.php';

class alarma
{
    public $_model;

    public function __construct()
    {
        $this->_model = new modelAlarma();
    }

    public function nuevaAlarma($data)
    {
        $this->_model->nuevaAlarma(($data));
    }

    public function editAlarma($data)
    {
        $this->_model->editAlarma($data);
    }

    public function listadoAlarmas()
    {
        $this->_model->listadoAlarmas();
    }

    public function deleteAlarma($data)
    {
        $this->_model->deleteAlarma($data);
    }
}
