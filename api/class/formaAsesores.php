<?php
require_once 'utils.php';
require_once '../model/modelFormaAsesores.php';

class formaAsesores
{
    public $_model;

    public $_utils;

    public function __construct()
    {
        $this->_model = new ModelFormaAsesores();
        $this->_utils = new utils();
    }

    public function ciudades()
    {
        $this->_model->rst();
        $this->_model->rstdep();
    }

    public function regionesTip()
    {

        $regionesTip = $this->_model->regionesTip();
        $this->_utils->response($this->_utils->json([$regionesTip]), 201);

        /*$rst = $this->connseguimiento->query($query);

        if ($rst->num_rows > 0) {

            $resultado = array();

            while ($row = $rst->fetch_assoc()) {
                $regiones = $this->quitar_tildes(utf8_encode($row['region']));
                $row['region'] = $regiones;
                $resultado[] = $row;
            }
            $this->response($this->json(array($resultado)), 201);

        } else {
            $error = array();

            $this->response($this->json($error), 400);
        }*/
    }

    public function procesos()
    {
        $procesos = $this->_model->procesos();
        $this->_utils->response($this->_utils->json([$procesos]), 201);
    }

    public function registros($data)
    {

        $pagina   = $data['page'];
        $datos    = $data['datos'];
        $response = $this->_model->registros($pagina, $datos);

        /* $fechaini = $datos['fechaini'];
        $fechafin = $datos['fechafin']; */

        $this->_utils->response($this->_utils->json([$response[0], $response[1]]), 201);

    }

}
