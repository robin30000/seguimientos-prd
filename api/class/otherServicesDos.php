<?php
require_once '../model/modelOtherServicesDos.php';

class otherServicesDos
{
    public $_model;

    public function __construct()
    {
        $this->_model = new modelOtherServicesDos();
    }

    public function gestionBorrar($data)
    {
        $this->_model->gestionBorrar($data);
    }

    public function desbloquear($data)
    {
        $this->_model->desbloquear($data);
    }

    public function csvPreagen($data)
    {
        $this->_model->csvPreagen($data);
    }

    public function csvContingencias($data)
    {
        $this->_model->csvContingencias($data);
    }

    public function csvEstadosClick($data)
    {
        $this->_model->csvEstadosClick($data);
    }

    public function CsvpeniInsta($data)
    {
        $this->_model->CsvpeniInsta($data);
    }

    public function CsvGestionPendientes($data)
    {
        $this->_model->CsvGestionPendientes($data);
    }

    public function CsvNpsSemana($data)
    {
        $this->_model->CsvNpsSemana($data);
    }

    public function buscarPedido($data)
    {
        $this->_model->buscarPedido($data);
    }

    public function buscarPedidoSegui($data)
    {
        $this->_model->buscarPedidoSegui($data);
    }

    public function csvRegistros($data)
    {
        $this->_model->csvRegistros($data);
    }

    public function expBrutal($data)
    {
        $this->_model->expBrutal($data);
    }

    public function Csvtecnico($data)
    {
        $this->_model->Csvtecnico($data);
    }

    public function diferenciasClick($data)
    {
        $this->_model->diferenciasClick($data);
    }

    public function observacionAsesor($data)
    {
        $this->_model->observacionAsesor($data);
    }

    public function contadorpedientesBF()
    {
        $this->_model->contadorpedientesBF();
    }

    public function seguimientoClick($data)
    {
        $this->_model->seguimientoClick($data);
    }

    public function registrosComercial($data)
    {
        $this->_model->registrosComercial($data);
    }
}
