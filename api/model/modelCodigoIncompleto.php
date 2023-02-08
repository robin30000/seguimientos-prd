<?php
require_once '../class/conection.php';

class modelCodigoIncompleto
{
    private $_DB;

    public function __construct()
    {
        $this->_DB = new Conection();

    }

    public function getListaCodigoIncompleto()
    {

        try {
            $now  = date('Y-m-d');
            $stmt = $this->_DB->prepare("SELECT *
                                                FROM gestion_codigo_incompleto
                                                WHERE fecha_creado BETWEEN :fechaini AND :fechafin
                                                  AND status_soporte = '0'");
            $stmt->execute([
                ':fechaini' => "$now 00:00:00",
                ':fechafin' => "$now 23:59:59",
            ]);

            if ($stmt->rowCount()) {
                $response = [$stmt->fetchAll(PDO::FETCH_ASSOC), 201];
            } else {
                $response = ['Error', 400];
            }

        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        $this->_DB = null;

        echo json_encode($response);
    }

    public function gestionarCodigoIncompleto($data)
    {
        try {

            $id_codigo_incompleto = $data['id_codigo_incompleto'];
            $tipificacion         = $data['tipificacion'];
            $observacion          = $data['observacion'];
            $fecha_respuesta      = date('Y-m-d H:i:s');

            $stmt = $this->_DB->prepare("UPDATE gestion_codigo_incompleto
                                            SET status_soporte    = 1,
                                                respuesta_gestion = :tipificacion,
                                                observacion       = :observacion,
                                                login             = :login,
                                                fecha_respuesta   = :fecha_respuesta
                                            WHERE id_codigo_incompleto = :id_codigo_incompleto");
            $stmt->execute([
                ':tipificacion'         => $tipificacion,
                ':observacion'          => $observacion,
                ':login'                => $_SESSION['login'],
                ':fecha_respuesta'      => $fecha_respuesta,
                ':id_codigo_incompleto' => $id_codigo_incompleto,
            ]);

            if ($stmt->rowCount()) {
                $response = [['type' => 'success', 'msg' => 'OK'], 201];
            } else {
                $response = [['type' => 'Error', 'msg' => 'Ah ocurrido un error intentalo nuevamente'], 400];
            }
        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        $this->_DB = null;
        echo json_encode($response);
    }

    public function registroscodigoincompleto($params)
    {
        try {
            $pagina = $params['page'];
            $datos  = $params['datos'];

            $fechaini = (!isset($datos['fechaini'])) ? date("Y-m-d") : $datos['fechaini']; //CORRECCION DE VALIDACION DE FECHA
            $fechafin = (!isset($datos['fechafin'])) ? date("Y-m-d") : $datos['fechafin']; //CORRECCION DE VALIDACION DE FECHA

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

            $query = "SELECT id_codigo_incompleto,
                           tarea,
                           numero_contacto,
                           nombre_contacto,
                           unepedido,
                           tasktypecategory,
                           unemunicipio,
                           uneproductos,
                           engineer_id,
                           engineer_name,
                           mobile_phone,
                           status_soporte,
                           fecha_solicitud_firebase,
                           fecha_creado,
                           respuesta_gestion,
                           observacion,
                           login,
                           fecha_respuesta
                    FROM gestion_codigo_incompleto
                    WHERE fecha_respuesta BETWEEN '$fechaini 00:00:00' AND '$fechafin 23:59:59'
                      AND status_soporte = '1'
                    ORDER BY fecha_creado DESC
                    LIMIT 100 offset $pagina";

            $queryCount = "SELECT COUNT(tarea) as Cantidad
            FROM gestion_codigo_incompleto 
            WHERE fecha_respuesta BETWEEN '$fechaini 00:00:00' AND '$fechafin 23:59:59'
            ORDER BY fecha_creado DESC";

            $rr = $this->_DB->query($queryCount);
            $rr->execute();

            $counter = 0;
            if ($rr->rowCount()) {
                $result = [];
                if ($row = $rr->fetchAll(PDO::FETCH_ASSOC)) {
                    $counter = $row[0]['Cantidad'];
                }
            }

            $rst = $this->_DB->query($query);
            $rst->execute();
            //echo $this->mysqli->query($sqlLogin);
            //
            if ($rst->rowCount()) {
                $result   = $rst->fetchAll(PDO::FETCH_ASSOC);
                $response = [$result, $counter];
            } else {
                $response = ['No se encontraron datos'];
            }
        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }

        $this->_DB = null;
        echo json_encode($response);
    }

    public function csvRegistrosCodigoIncompleto($params)
    {

        try {

            session_start();
            $usuarioid = $_SESSION['login'];

            $fechaini  = $params['fechaini'];
            $fechafin  = $params['fechafin'];

            if ($fechaini == "" && $fechafin == "") {
                $fechaini = date("Y") . "-" . date("m") . "-" . date("d");
                $fechafin = date("Y") . "-" . date("m") . "-" . date("d");
            }


            $query = "SELECT id_codigo_incompleto, tarea, numero_contacto, nombre_contacto, unepedido, tasktypecategory, unemunicipio, uneproductos, engineer_id, engineer_name, mobile_phone, status_soporte, fecha_solicitud_firebase, fecha_creado, respuesta_gestion, observacion, login, fecha_respuesta 
            FROM gestion_codigo_incompleto
            WHERE fecha_respuesta BETWEEN '$fechaini 00:00:00' AND '$fechafin 23:59:59' AND status_soporte = '1'
            ORDER BY fecha_creado DESC;";

            $resQuery = $this->_DB->query($query);
            if ($resQuery->rowCount()) {
                $result   = $resQuery->fetchAll(PDO::FETCH_ASSOC);
                $response = [$result, 201];

            } else {
                $response = ['', 203];
            }
        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        $this->_DB = null;
        echo json_encode($response);
    }
}
