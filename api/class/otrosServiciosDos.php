<?php
require_once '../model/modelotrosServiciosDos.php';
class otrosServiciosDos
{
    public function __construct()
    {
        $this->_model = new modelotrosServiciosDos();
    }

    public function listadoEstadosClick($data)
    {
        $this->_model->listadoEstadosClick($data);
    }

    public function BuscarPedidoinsta($data)
    {
        $this->_model->BuscarPedidoinsta($data);
    }

    public function GuardarPedidoPendiInsta($data)
    {
        $this->_model->GuardarPedidoPendiInsta($data);
    }

    public function deleteregistrosCarga($data)
    {
        $this->_model->deleteregistrosCarga($data);
    }

    public function Accionesoffline($data)
    {
        $this->_model->Accionesoffline($data);
    }

    public function acciones($data)
    {
        $this->_model->acciones($data);
    }

    public function Codigos($data)
    {
        $this->_model->Codigos($data);
    }

    public function Diagnosticos($data)
    {
        $this->_model->Diagnosticos($data);
    }
}