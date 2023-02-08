<?php
require_once '../model/modelSoporteGpon.php';

class soporteGpon
{
    private $_model;

    public function __construct()
    {
        $this->_model = new modelSoporteGpon();
    }

    public function getSoporteGponByTask($data)
    {
        $this->_model->getSoporteGponByTask($data);
    }

    public function validarLlenadoSoporteGpon($data)
    {
        $this->_model->validarLlenadoSoporteGpon($data);
    }

    public function postPendientesSoporteGpon($data)
    {
        $this->_model->postPendientesSoporteGpon($data);
    }

    public function getListaPendientesSoporteGpon()
    {
        $this->_model->getListaPendientesSoporteGpon();
    }

    public function gestionarSoporteGpon($data)
    {
        $this->_model->gestionarSoporteGpon($data);
    }

    public function registrossoportegpon($data)
    {
        $this->_model->registrossoportegpon($data);
    }

    public function csvRegistrosSoporteGpon($data)
    {
        $this->_model->csvRegistrosSoporteGpon($data);
    }

    public function marcarEngestionGpon($data)
    {
        $this->_model->marcarEngestionGpon($data);
    }

}
