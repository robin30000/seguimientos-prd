<?php
require_once '../model/modelOtrosServicios.php';
require_once 'utils.php';
class otrosServicios
{
    public function __construct()
    {
        $this->_model = new modelOtrosServicios();
    }

    public function DepartamentosContratos($data)
    {
       $this->_model->DepartamentosContratos($data);
    }
    public function insertData($data){
        $this->_model->insertData($data);
    }

    public function getRegistrosCarga()
    {
        $this->_model->getRegistrosCarga();
    }

    public function getDemePedidoEncuesta()
    {
        $this->_model->getDemePedidoEncuesta();
    }

    public function resumenSemanas($data)
    {
        $this->_model->resumenSemanas($data);
    }

    public function listadoTecnicos($data)
    {
        $this->_model->listadoTecnicos($data);
    }

    public function buscarPedidoContingencias($data)
    {
        $this->_model->buscarPedidoContingencias($data);
    }
}