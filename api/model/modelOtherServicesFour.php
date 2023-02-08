<?php
require_once '../class/conection.php';

class modelOtherServicesFour
{
    private $_DB;

    public function __construct()
    {
        $this->_DB = new Conection();
    }

    /**
     * TODO la tabla carga agenda no existe
     */
    public function UenCargada()
    {
        try {
            $stmt = $this->_DB->query("SELECT DISTINCT uen FROM carga_agenda ORDER BY uen");
            $stmt->execute();
            if ($stmt->rowCount()) {
                $result   = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $response = [$result, 201];
            } else {
                $response = ['No se encontraron datos'];
            }
        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        $this->_DB = null;
        echo json_encode($response);
    }

    /**
     * TODO tabla no existe
     */

    public function gestionComercial()
    {
        try {
            $stmt = $this->_DB->query("SELECT DISTINCT GESTION
                                                FROM procesos_comercial
                                                ORDER BY GESTION");
            $stmt->execute();
            if ($stmt->rowCount()) {
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $result = [$result, 201];
            } else {
                $response = ['No se econtraron datos', 400];
            }
        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        $this->_DB = null;
        echo json_encode($response);
    }

    public function causaRaiz()
    {
        try {
            echo 'No existe';
        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        $this->_DB = null;
        echo json_encode($response);
    }

    public function ResponsablePendiente($data)
    {
        try {
            echo 'No esxiste';
        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        $this->_DB = null;
        echo json_encode($response);
    }

    public function listaCausaRaiz($data)
    {
        try {
            echo 'No esxiste';
        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        $this->_DB = null;
        echo json_encode($response);
    }

    public function Causasraizinconsitencias()
    {
        try {
            echo 'No esxiste';
        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        $this->_DB = null;
        echo json_encode($response);
    }

    public function pendiBrutal()
    {
        try {
            $fecha = date("Y-m-d");

            $stmt = $this->_DB->prepare("select distinct pro.PEDIDO_CRM,
                                                                c.departamento,
                                                                (select descripcion
                                                                 from codigo_pendientes_click
                                                                 where codigo = pro.codigo_pendiente_incompleto) descripcion,
                                                                c.observacion
                                                from agendasDia pro
                                                         join (SELECT a.departamento, pedido_id, observacion
                                                               FROM carga_click a
                                                               where a.fecha_cita BETWEEN (:fechaini) AND (:fechafin)
                                                                 and uen = 'HG') c
                                                              on c.pedido_id = pro.PEDIDO_CRM
                                                                  and FECHA_CITA = :fecha
                                                where pro.fecha_cita = :fecha
                                                  and pro.tipo_trabajo = 'NUEVO'
                                                  and pro.ESTADO_CLICK not in ('Cancelado', 'Finalizada')
                                                  and pro.CODIGO_PENDIENTE_INCOMPLETO in ('O-53', 'OT-C06', 'O-101', 'OT-T05', 'OT-C12', 'OT-T04')");

            $stmt->execute([':fechaini' => "$fecha 00:00:00", 'fechafin' => "$fecha 23:59:59", ':fecha' => $fecha]);

            if ($stmt->rowCount()) {
                foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                    $pedido_id    = $row['PEDIDO_CRM'];
                    $pendiente    = $row['descripcion'];
                    $departamento = $row['departamento'];
                    $observacion  = utf8_encode($row['observacion']);

                    //////////////////Consulta en click si el pedido encontrado en pendi_insta se encuentra en estado Finalizada
                    $sqlbuscarBrutal = $this->_DB->prepare("select PedidoDespacho
                                                                    from BrutalForce
                                                                    where PedidoDespacho = :pedido_id");
                    $sqlbuscarBrutal->execute([':pedido_id' => $pedido_id]);
                    if ($sqlbuscarBrutal->rowCount()) {
                        $response = ["pedido" => "$pedido_id", 201];
                    } else {
                        $response = ["pedido" => "$pedido_id", "pendiente" => "$pendiente", "departamento" => "$departamento", "observacion" => "$observacion", 201];
                    }
                }
            } else {
                $response = ['No se encontraron datos', 400];
            }
        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        $this->_DB = null;
        echo json_encode($response);
    }

    /**
     * TODO tabla no existe
     */

    public function clasificacionComercial($data)
    {
        try {
            $stmt = $this->_DB->query("SELECT DISTINCT CLASIFICACION
                                                FROM procesos_comercial
                                                WHERE gestion = :gestion
                                                ORDER BY GESTION ");
            $stmt->execute([':gestion' => $data]);
            if ($stmt->rowCount()) {
                $response = [$stmt->fetchAll(PDO::FETCH_ASSOC), 201];
            } else {
                $response = ['No se encontraron datos', 400];
            }
        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        $this->_DB = null;
        echo json_encode($response);
    }

    public function buscaregistros($data)
    {
        try {
            $pedido = $data['pedido'];
            $fecha  = $data['fecha'];

            $query = $this->_DB->prepare("select pedido,
                               (select nombre from tecnicos where identificacion = id_tecnico limit 1) AS tecnico,
                               accion,
                               asesor,
                               tipo_pendiente,
                               (select tipo_trabajo from carga_click where pedido = pedido_id limit 1) AS tipo_trabajo,
                               fecha,
                               observaciones,
                               id
                        from registros
                        where pedido = :pedido
                        order by fecha DESC");
            $query->execute([':pedido' => $pedido]);

            $sqlagenda = $this->_DB->prepare("select count(pedido_id) Cantidad
                                                    from carga_agenda
                                                    where fecha_cita between '$fecha 00:00:00' and '$fecha 23:59:59'
                                                      and pedido_id = '$pedido'");
            $sqlagenda->execute([':pedido' => $pedido, ':fechaini' => "$fecha 00:00:00", ':fechafin' => "$fecha 23:59:59"]);
            $counteragenda = $sqlagenda->rowCount();

            $sqlnovedades = $this->_DB->query("SELECT DISTINCT NOVEDAD
                                                        FROM gestor_historicos_reagendamiento
                                                        WHERE PROCESO = 'INSTALACIONES'");

            $sqlnovedades->execute();
            if ($sqlnovedades->rowCount()) {
                $total   = $sqlnovedades->rowCount();
                $novedad = $sqlnovedades->fetchAll(PDO::FETCH_ASSOC);
                for ($i = 0; $i < $total; $i++) {
                    $novedades = $novedades . $sep . "'" . $novedad[$i]['NOVEDAD'] . "'";
                    $sep       = ",";
                }
            }

            $sqlagendamineto = $this->_DB->prepare("SELECT NOVEDAD,
                                                                   FECHA_ESTADO,
                                                                   CELULAR_AVISAR,
                                                                   OBSERVACION_FENIX,
                                                                   OBSERVACION_GESTOR
                                                            FROM portalbd.gestor_historicos_reagendamiento
                                                            where PEDIDO_ID = '$pedido'
                                                              AND NOVEDAD in (:novedades) ");

            $sqlagendamineto->execute([':novedad' => $novedad]);

            $queryclick = $this->_DB->prepare("select productos,
                                                           fecha_cita,
                                                           estado_id,
                                                           tipo_trabajo,
                                                           observacion,
                                                           (select descripcion
                                                            from codigo_pendientes_click
                                                            where codigo = pro.codigo_pendiente_incompleto) descripcion,
                                                           une_agendamientos
                                                    from carga_click pro
                                                    where pedido_id = :pedido");
            $queryclick->execute([':pedido' => $pedido]);

            $sqlclick = $this->_DB->prepare("select count(pedido_id) Cantidad
                                                    from carga_click
                                                    where fecha_cita between '$fecha 00:00:00' and '$fecha 23:59:59'
                                                      and pedido_id = :pedido");
            $sqlclick->execute([':pedido' => $pedido]);
            $counterClick = $sqlclick->rowCount();


            if ($query->rowCount() || $sqlagendamineto->rowCount() || $queryclick->rowCount()) {

                $resultado          = $query->fetchAll(PDO::FETCH_ASSOC);
                $resultadoagenda    = [];
                $resultadosimulador = [];
                $resultadosclick    = [];
                while ($row = $rst->fetch_assoc()) {
                    $resultado[] = $row;
                }
                foreach ($sqlagendamineto->fetchAll(PDO::FETCH_ASSOC) as $row) {
                    $resultadoagenda[]         = $row;
                    $row['OBSERVACION_FENIX']  = utf8_encode($row['OBSERVACION_FENIX']);
                    $row['OBSERVACION_GESTOR'] = utf8_encode($row['OBSERVACION_GESTOR']);
                }

                $resultadosclick = $queryclick->fetchAll(PDO::FETCH_ASSOC);

                $response = [$resultado, $counteragenda, $counterClick, $resultadoagenda, $resultadosclick, 201];

            } else {
                $response = ['No se encontraron datos', 400];
            }
        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        $this->_DB = null;
        echo json_encode($response);
    }

    public function guardarRecogerEquipos($data)
    {
        try {
            $datos = $data;

            $total = count($datos);

            for ($i = 0; $i < $total; $i++) {

                $pedido      = ($datos[$i]["pedido"]);
                $mac         = ($datos[$i]["mac"]);
                $serial      = ($datos[$i]["serial"]);
                $ciudad      = ($datos[$i]["ciudad"]);
                $CedTecnico  = ($datos[$i]["CedTecnico"]);
                $NomTecnico  = ($datos[$i]["NomTecnico"]);
                $contratista = ($datos[$i]["contratista"]);
                $fechahora   = date("Y-m-d H:i:s");

                $valores  = '("' . $pedido . '","Recoger Equipo","' . $fechahora . '","' . $ciudad . '","' . $CedTecnico . '","' . $NomTecnico . '","' . $contratista . '","' . $mac . '","' . $serial . '"),';
                $valoresQ = $valoresQ . $valores;
            }

            $valoresQ[strlen($valoresQ) - 1] = ";";

            $stmt = $this->_DB->prepare("INSERT INTO recogidaequipos (pedido, motivo, fecha, ciudad, CedTecnico, NomTecnico, contratista, mac, serialEq)
		 VALUES :valoresQ");
            $stmt->execute([':valoresQ' => $valoresQ]);


            if ($stmt->rowCount()) {
                $response = ['Datos guardados correctamente', 201];
            } else {
                $response = ['Ah ocurrido un error intentalo nuevamente', 400];
            }
        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        $this->_DB = null;
        echo json_encode($response);
    }
}
