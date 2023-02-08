<?php
require_once '../model/modelOtherServicesFour.php';

class otherServicesFour
{
    public $_model;

    public function __construct()
    {
        $this->_model = new modelOtherServicesFour();
    }

    public function UenCargada()
    {
        $this->_model->UenCargada();
    }

    public function gestionComercial()
    {
        $this->_model->gestionComercial();
    }

    public function causaRaiz()
    {
        $this->_model->causaRaiz();
    }

    public function ResponsablePendiente($data)
    {
        $this->_model->ResponsablePendiente($data);
    }

    public function listaCausaRaiz($data)
    {
        $this->_model->listaCausaRaiz($data);
    }

    public function Causasraizinconsitencias()
    {
        $this->_model->Causasraizinconsitencias();
    }

    public function pendiBrutal()
    {
        $this->_model->pendiBrutal();
    }

    public function clasificacionComercial($data)
    {
        $this->_model->clasificacionComercial($data);
    }

    public function buscaregistros($data)
    {
        $this->_model->buscaregistros($data);
    }

    public function guardarRecogerEquipos($data)
    {
        $this->_model->guardarRecogerEquipos($data);
    }
}
