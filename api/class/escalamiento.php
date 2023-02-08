<?php
require_once '../model/modelEscalamiento.php';

class escalamiento
{

    public $_model;

    public function __construct()
    {
        $this->_model = new modelEscalamiento();
    }

    public function escalamientoInfraestructura($data)
    {
        $this->_model->escalamientoInfraestructura($data);
    }

    public function GrupoCola()
    {
        $this->_model->GrupoCola();
    }

    public function gestionEscalimiento()
    {
        $this->_model->gestionEscalimiento();
    }

    public function observacionEscalimiento($data)
    {
        $this->_model->observacionEscalimiento($data);
    }

    public function notasEscalamiento($data)
    {
        $this->_model->notasEscalamiento($data);
    }

    public function infoEscalamiento($data)
    {
        $this->_model->infoEscalamiento($data);
    }

    public function csvEscalamientoExp($data)
    {
        $this->_model->csvEscalamientoExp($data);
    }

    public function saveescalamiento($data)
    {
        $this->_model->saveescalamiento($data);
    }

    public function exportEscalamientos()
    {
        $this->_model->exportEscalamientos();
    }
}
