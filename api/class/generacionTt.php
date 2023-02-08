<?php
require_once '../model/modelGeneracionTt.php';

class generacionTt
{
    public $_model;

    public function __construct()
    {
        $this->_model = new modelGeneracionTt();
    }

    public function premisasInfraestructuras($data)
    {
        $this->_model->premisasInfraestructuras($data);
    }

    public function guardarGeneracionTT($data)
    {
        $this->_model->guardarGeneracionTT($data);
    }

    public function csvGeneracionTT($data)
    {
        $this->_model->csvGeneracionTT($data);
    }
}
