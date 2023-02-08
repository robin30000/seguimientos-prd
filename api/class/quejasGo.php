<?php

require_once '../model/modelQuejasGo.php';
require_once 'utils.php';
class quejasGo
{

    public $_model;

    public $_utils;

    public function __construct()
    {
        $this->_utils = new utils();
        $this->_model = new modelQuejasGo();
    }

    public function listaQuejasGoDia($data)
    {
        $this->_model->listaQuejasGoDia($data);
    }

    public function csvQuejasGo($data)
    {
        $this->_model->csvQuejasGo($data);
    }

    public function buscarTecnico($data)
    {
        $this->_model->buscarTecnico($data);
    }

    public function crearTecnicoQuejasGo($data)
    {
        $this->_model->crearTecnicoQuejasGo($data);
    }

    public function ciudadesQGo()
    {
        $this->_model->ciudadesQGo();
    }

    public function guardarQuejaGo($data)
    {
        $this->_model->guardarQuejaGo($data);
    }

    public function ActualizarObserQuejasGo($data)
    {
        $this->_model->ActualizarObserQuejasGo($data);
    }
}