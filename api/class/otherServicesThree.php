<?php
require_once '../model/modelOtherServicesThree.php';

class otherServicesThree
{
    public $_model;

    public function __construct()
    {
        $this->_model = new modelOtherServicesThree();
    }

    public function gestionBrutal($data)
    {
        $this->_model->gestionBrutal($data);
    }

    public function BuscarPedidoBrutal($data)
    {
        $this->_model->BuscarPedidoBrutal($data);
    }

    public function meses()
    {
        $this->_model->meses();
    }

    public function mesesrepa()
    {
        $this->_model->mesesrepa();
    }

    public function actualizarregion()
    {
        $this->_model->actualizarregion();
    }

    public function departamentos()
    {
        $this->_model->departamentos();
    }

    public function conceptospendientes($data)
    {
        $this->_model->conceptospendientes($data);
    }

    public function getConceptosTotales($data)
    {
        $this->_model->getConceptosTotales($data);
    }

    public function ResumenInsta($data)
    {
        $this->_model->ResumenInsta($data);
    }

    public function tipo_trabajoclick()
    {
        $this->_model->tipo_trabajoclick();
    }

}
