<?php
require_once '../model/visitasEnConjunto.php';

class visitasEnConjunto
{

    public $_model;

    public function __construct()
    {
        $this->_model = new modelVisitasEnConjunto();
    }

    public function visitasEnConjunto($data)
    {
        $this->_model->visitasEnConjunto($data);
    }

    public function GrupoVisitasEnConjunto()
    {
        $this->_model->GrupoVisitasEnConjunto();
    }

    public function infoVisitasEnConjunto($data)
    {
        $this->_model->infoVisitasEnConjunto($data);
    }

    public function expCsvVisitasEnConjunto($data)
    {
        $this->_model->expCsvVisitasEnConjunto($data);
    }

    public function RegionesVisConjunto()
    {
        $this->_model->RegionesVisConjunto();
    }

    public function MunicipiosVisConjunto($data)
    {
        $this->_model->MunicipiosVisConjunto($data);
    }

    public function MunicipioVisConjuntoUpdate($data)
    {
        $this->_model->MunicipioVisConjuntoUpdate($data);
    }
}
