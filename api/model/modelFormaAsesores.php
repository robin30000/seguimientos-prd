<?php
error_reporting(E_ALL);
require_once '../class/conection.php';

class ModelFormaAsesores
{
    private $_DB;

    public function __construct()
    {
        $this->_DB = new Conection();
    }

    public function rst()
    {
        try {


            $stmt = $this->_DB->query("SELECT DISTINCT lower(`DEPARTAMENTO`), lower(`CIUDAD`) 
                                       FROM ciudades 
                                      ORDER BY CIUDAD");
            $stmt->execute();
            if ($stmt->rowCount()) {
                $response = [$stmt->fetchAll(PDO::FETCH_ASSOC), 201];
            } else {
                $response = '';
            }


        } catch (PDOException $e) {

        }
        $this->_DB = null;
        echo json_encode($response);
    }

    public function rstdep()
    {
        try {


            $stmt = $this->_DB->query("SELECT DISTINCT DEPARTAMENTO 
                                       FROM ciudades 
                                       ORDER BY DEPARTAMENTO ASC");

            $stmt->execute();

            if ($stmt->rowCount()) {
                $response = [$stmt->fetchAll(PDO::FETCH_ASSOC), 201];
            } else {
                $response = '';
            }


        } catch (PDOException $e) {

        }
        $this->_DB = null;
        echo json_encode($response);
    }

    public function regionesTip()
    {
        try {
            $stmt = $this->_DB->query("SELECT DISTINCT region
                                      FROM regiones 
                                      ORDER BY region ASC");
            $stmt->execute();
            if ($stmt->rowCount()) {
                $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $response = '';
            }
        } catch (PDOException $e) {

        }
        $this->_DB = null;

        return $response;
    }

    public function procesos()
    {
        try {
            $stmt = $this->_DB->query("SELECT DISTINCT trim(PROCESO) proceso" .
                                      " FROM procesos " .
                                      " ORDER BY PROCESO ASC");
            $stmt->execute();
            if ($stmt->rowCount()) {
                $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $response = '';
            }
        } catch (PDOException $e) {

        }
        $this->_DB = null;

        return $response;

    }

    public function registros($pagina, $datos)
    {
        $fechaini = (!isset($datos['fechaini'])) ? date("Y-m-d") : $datos['fechaini']; //CORRECCION DE VALIDACION DE FECHA
        $fechafin = (!isset($datos['fechafin'])) ? date("Y-m-d") : $datos['fechafin']; //CORRECCION DE VALIDACION DE FECHA
        $concepto = (isset($datos['concepto'])) ? $datos['concepto'] : '';
        $buscar   = (isset($datos['buscar'])) ? $datos['buscar'] : '';

        if ($fechaini == "" || $fechafin == "") {
            $fechaini = date("Y-m-d");
            $fechafin = date("Y-m-d");
        }
        //$today = date("Y-m-d");

        if ($pagina == "undefined") {
            $pagina = "0";
        } else {
            $pagina = $pagina - 1;
        }

        $pagina = $pagina * 100;

        if ($concepto == "" || $buscar == "") {
            $parametro = "";
        } else {
            $parametro = " and $concepto = '$buscar'";
        }

        try {
            $stmt = $this->_DB->query("SELECT a.id, a.pedido, " .
                                      " (select nombre from tecnicos " .
                                      "where a.id_tecnico = identificacion limit 1) tecnico, " .
                                      "trim(a.accion) AS accion, " .
                                      "a.asesor, " .
                                      "a.fecha, a.duracion, a.proceso, " .
                                      "a.observaciones, a.llamada_id, a.id_tecnico, a.empresa, a.despacho, a.producto, " .
                                      "a.accion, trim(a.tipo_pendiente) tipo_pendiente, (select ciudad from tecnicos " .
                                      "where a.id_tecnico = identificacion limit 1) ciudad, a.plantilla " .
                                      "FROM registros a " .
                                      "where 1=1 $parametro " .
                                      " and a.fecha BETWEEN ('$fechaini 00:00:00') AND ('$fechafin 23:59:59') " .
                                      "and asesor <> 'IVR'" .
                                      " order by a.fecha DESC " .
                                      " limit 100 offset $pagina");

            $stmt->execute();

            $queryCount = " select count(*) as Cantidad from registros a " .
                          " where 1=1 " .
                          " and a.fecha BETWEEN ('$fechaini 00:00:00') AND ('$fechafin 23:59:59') " .
                          " $parametro ";

            //echo $queryCount;

            $rr = $this->_DB->query($queryCount);

            $counter = 0;
            if ($rr->rowCount() > 0) {
                if ($row = $rr->fetchAll(PDO::FETCH_ASSOC)) {
                    $counter = $row['Cantidad'];
                }
            }


            if ($stmt->rowCount()) {
                $result   = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $response = [$result, $counter];
            } else {
                $response = 0;
            }
        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        $this->_DB = null;

        return $response;


        /*$query = "SELECT a.id, a.pedido, " .
                 " (select nombre from tecnicos " .
                 "where a.id_tecnico = identificacion limit 1) tecnico, " .
                 "trim(a.accion) AS accion, " .
                 "a.asesor, " .
                 "a.fecha, a.duracion, a.proceso, " .
                 "a.observaciones, a.llamada_id, a.id_tecnico, a.empresa, a.despacho, a.producto, " .
                 "a.accion, trim(a.tipo_pendiente) tipo_pendiente, (select ciudad from tecnicos " .
                 "where a.id_tecnico = identificacion limit 1) ciudad, a.plantilla " .
                 "FROM registros a " .
                 "where 1=1 " .
                 " $parametro " .
                 " and a.fecha BETWEEN ('$fechaini 00:00:00') AND ('$fechafin 23:59:59') " .
                 "and asesor <> 'IVR'" .
                 " order by a.fecha DESC " .
                 " limit 100 offset $pagina ";

        $queryCount = " select count(*) as Cantidad from registros a " .
                      " where 1=1 " .
                      " and a.fecha BETWEEN ('$fechaini 00:00:00') AND ('$fechafin 23:59:59') " .
                      " $parametro ";

        //echo $queryCount;

        $rr = $this->connseguimiento->query($queryCount);

        $counter = 0;
        if ($rr->num_rows > 0) {
            $result = [];
            if ($row = $rr->fetch_assoc()) {
                $counter = $row['Cantidad'];
            }
        }

        $rst = $this->connseguimiento->query($query);

        //echo $this->mysqli->query($sqlLogin);
        //
        if ($rst->num_rows > 0) {

            $resultado = [];

            while ($row = $rst->fetch_assoc()) {

                //var_dump($row);
                $row['id']             = utf8_encode($row['id']);
                $row['pedido']         = utf8_encode($row['pedido']);
                $row['tecnico']        = utf8_encode($row['tecnico']);
                $row['accion']         = utf8_encode($row['accion']);
                $row['asesor']         = utf8_encode($row['asesor']);
                $row['fecha']          = utf8_encode($row['fecha']);
                $row['duracion']       = utf8_encode($row['duracion']);
                $row['proceso']        = utf8_encode($row['proceso']);
                $row['observaciones']  = utf8_encode($row['observaciones']);
                $row['llamada_id']     = utf8_encode($row['llamada_id']);
                $row['id_tecnico']     = utf8_encode($row['id_tecnico']);
                $row['empresa']        = utf8_encode($row['empresa']);
                $row['despacho']       = utf8_encode($row['despacho']);
                $row['producto']       = utf8_encode($row['producto']);
                $row['tipo_pendiente'] = utf8_encode($row['tipo_pendiente']);
                $row['ciudad']         = utf8_encode($row['ciudad']);
                $row['plantilla']      = utf8_encode($row['plantilla']);

                //var_dump($row);
                $resultado[] = $row;

            }*/
    }
}
