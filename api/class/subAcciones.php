<?php
require_once '../model/modelSubAccion.php';
class subAcciones
{

    public $_model;

    public function __construct()
    {
        $this->_model = new modelSubAccion();
    }

    public function subacciones($data){
        $this->_model->subacciones($data);
    }
}
