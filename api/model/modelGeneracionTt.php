<?php

require_once '../class/conection.php';

class modelGeneracionTt
{

    private $_DB;

    public function __construct()
    {
        $this->_DB = new Conection();
    }

    public function premisasInfraestructuras($params)
    {
        try {

            $pagina   = $params['page'];
            $datos    = $params['datos'];
            $fechaini = $datos['fechaini'];
            $fechafin = $datos['fechafin'];

            if ($fechaini == "" || $fechafin == "") {
                $fechaini = date('Y-m-d');
                $fechafin = date('Y-m-d');
            }

            if ($pagina == "undefined") {
                $pagina = "0";
            } else {
                $pagina = $pagina - 1;
            }

            $pagina = $pagina * 100;

            $stmt = $this->_DB->prepare("SELECT g.id, g.tt, g.quienSolicitaLaCCC, g.elementoAfectado, g.ciudad, g.region, g.fechaSolicitud
                                                FROM GeneracionTT g
                                                WHERE 1 = 1
                                                  AND g.fechaSolicitud BETWEEN (:fechaini) AND (:fechafin)
                                                ORDER BY g.fechaSolicitud DESC
                                                limit 100 offset :pagina");
            $stmt->execute([
                ':fechaini' => "$fechaini 00:00:00",
                ':fechafin' => "$fechafin 23:59:59",
                ':pagina'   => $pagina,
            ]);

            if ($stmt->rowCount()) {
                $counter = $stmt->rowCount();

                $totalPaginas = $counter / 100;
                $totalPaginas = ceil($totalPaginas); //redondear al siguiente

                $response = [
                    'data'         => $stmt->fetchAll(PDO::FETCH_ASSOC),
                    'contador'     => $counter,
                    'totalPaginas' => $totalPaginas,
                    201,
                ];
            } else {
                $response = ['No se encontraron datos', 400];
            }

        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        $this->_DB = null;
        echo json_encode($response);
    }

    public function guardarGeneracionTT($datos)
    {
        try {
            // $key = $datos['id'];
            $tt                 = $datos['tt'];
            $quienSolicitaLaCCC = $datos['quienSolicitaLaCCC'];
            $elementoAfectado   = $datos['elementoAfectado'];
            $ciudad             = $datos['region'];
            $region             = $datos['municipio'];

            if (isset($datos['id'])) {

                $stmt = $this->_DB->prepare("UPDATE GeneracionTT g
                                                    SET g.quienSolicitaLaCCC = LOWER(TRIM(:quienSolicitaLaCCC)),
                                                        g.elementoAfectado   = LOWER(TRIM(:elementoAfectado)),
                                                        g.ciudad             = LOWER(TRIM(:ciudad)),
                                                        g.region             = LOWER(:region),
                                                        g.fechaSolicitud     = NOW()
                                                    WHERE g.id = :key");

                $stmt->execute([
                    ':quienSolicitaLaCCC' => $quienSolicitaLaCCC,
                    ':elementoAfectado'   => $elementoAfectado,
                    ':ciudad'             => $ciudad,
                    ':region'             => $region,
                    ':id'                 => $datos['id'],
                ]);

                if ($stmt->rowCount()) {
                    $response = ['Pedido actualizado', 201];
                } else {
                    $response = ['Ah ocurrido un error intentalo de nuevo', 400];
                }

            } else {

                $stmt = $this->_DB->prepare("INSERT INTO GeneracionTT (tt, quienSolicitaLaCCC, elementoAfectado, ciudad, region, fechaSolicitud, pedidos_asociados)
                                                    VALUES (:tt, LOWER(TRIM(:quienSolicitaLaCCC)), LOWER(TRIM(:elementoAfectado)), LOWER(:ciudad), LOWER(:region), NOW(),:n)");

                $stmt->execute([
                    ':tt'                 => $tt,
                    ':quienSolicitaLaCCC' => $quienSolicitaLaCCC,
                    ':elementoAfectado'   => $elementoAfectado,
                    ':ciudad'             => $ciudad,
                    ':region'             => $region,
                    ':n'                  => '',
                ]);

                if ($stmt->rowCount()) {
                    $response = ['Pedido actualizado', 201];
                } else {
                    $response = ['Ah ocurrido un error intentalo de nuevo', 400];
                }

            }

        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }

        $this->_DB = null;
        echo json_encode($response);
    }

    public function csvGeneracionTT($datos)
    {
        try {
            session_start();
            $usuarioid = $_SESSION['login'];
            $fechaini  = $datos['fechaini'];
            $fechafin  = $datos['fechafin'];

            if ($fechaini == "" && $fechafin == "") {
                $fechaini = date("Y") . "-" . date("m") . "-" . date("d");
                $fechafin = date("Y") . "-" . date("m") . "-" . date("d");
            }

            if ($fechaini == $fechafin) {
                $filename = "GeneracionTT" . "_" . $fechaini . "_" . $usuarioid . ".csv";
            } else {
                $filename = "GeneracionTT" . "_" . $fechaini . "_" . $fechafin . "_" . $usuarioid . ".csv";
            }

            $stmt = $this->_DB->prepare("SELECT g.tt, g.quienSolicitaLaCCC, g.elementoAfectado, g.ciudad, g.region, g.fechaSolicitud
                                                FROM GeneracionTT g
                                                WHERE 1 = 1
                                                  AND g.fechaSolicitud BETWEEN (:fechaini) AND (:fechafin)");
            $stmt->execute([
                ':fechaini' => "$fechaini 00:00:00",
                ':fechafin' => "$fechafin 23:59:59",
            ]);

            if ($stmt->rowCount()) {
                $counter  = $stmt->rowCount();
                $fp       = fopen("../tmp/$filename", 'w');
                $columnas = [
                    'TT',
                    'Quien Solicita',
                    'Elemento Afectado',
                    'Ciudad',
                    'Region',
                    'Fecha Solicitud',
                ];

                fputcsv($fp, $columnas);
                fputcsv($fp, $stmt->fetchAll(PDO::FETCH_ASSOC));
                fclose($fp);
                $response = [$filename, $counter, 201];
            } else {
                $response = ['No se encontraron datos', 400];
            }

        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        $this->_DB = null;
        echo json_encode($response);
    }
}
