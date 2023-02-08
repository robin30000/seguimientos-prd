<?php
require_once '../model/modelOtherServices.php';

class otherServices
{
    public $_model;

    public function __construct()
    {
        $this->_model = new modelOtherServices();
    }

    public function insertarCambioEquipo($data)
    {
        $this->_model->insertarCambioEquipo($data);
    }

    public function GuardarPedidoEncuesta($data)
    {
        $this->_model->GuardarPedidoEncuesta($data);
    }

    public function gestiodespachoBrutal($data)
    {
        $this->_model->gestiodespachoBrutal($data);
    }

    public function gestionFinal()
    {
        $this->_model->gestionFinal();
    }

    public function DashBoard()
    {
        $this->_model->DashBoard();
    }

    public function gestionAsesorBrutal($data)
    {
        $this->_model->gestionAsesorBrutal($data);
    }

    public function savecontingencia($data)
    {
        $this->_model->savecontingencia($data);
    }

    public function CancelarContingencias($data)
    {
        $this->_model->CancelarContingencias($data);
    }

    public function guardarEscalar($data)
    {
        $this->_model->guardarEscalar($data);
    }

    public function gestionAsesorFinal($data)
    {
        $this->_model->gestionAsesorFinal($data);
    }

    public function gestionPendientes()
    {
        $this->_model->gestionPendientes();
    }

    public function Pendientesxestado($data)
    {
        $this->_model->Pendientesxestado($data);
    }

}
