<?php
require_once '../model/modelTurnos.php';

class turnos
{
    public $_model;

    public function __construct()
    {
        $this->_model = new modelTurnos();
    }

    public function usuariosTurnos()
    {
        $this->_model->usuariosTurnos();
    }

    public function listaTurnos($data)
    {
        $this->_model->listaTurnos($data);
    }

    public function cumpleTurnos($data)
    {
        $this->_model->cumpleTurnos($data);
    }

    public function guardarTurnos($data)
    {
        $this->_model->guardarTurnos($data);
    }

    public function updateTurno($data)
    {
        $this->_model->updateTurno($data);
    }

    public function CsvExporteAdherencia($data)
    {
        $this->_model->CsvExporteAdherencia($data);
    }

    public function deleteTurno($data)
    {
        $this->_model->deleteTurno($data);
    }
}
