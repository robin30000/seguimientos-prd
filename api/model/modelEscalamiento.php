<?php
require_once '../class/conection.php';

class modelEscalamiento
{

    private $_DB;

    public function __construct()
    {
        $this->_DB = new Conection();
    }

    public function escalamientoInfraestructura($data)
    {

        try {
            $pagina   = $data['page'];
            $datos    = $data['datos'];
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

            $stmt = $this->_DB->prepare("SELECT id,
                                               pedido,
                                               tecnico,
                                               loginDespachador,
                                               ciudad,
                                               gestion,
                                               observacion,
                                               nota,
                                               fechaGestion
                                        FROM Escalamientos
                                        WHERE 1 = 1
                                          AND fechaGestion BETWEEN (:fechaini) AND (:fechafin)
                                        ORDER BY fechaGestion DESC
                                        limit 100 offset :pagina");
            $stmt->execute([
                ':fechaini' => "$fechaini 00:00:00",
                ':fechafin' => "$fechafin 23:59:59",
                ':page'     => $pagina,
            ]);

            if ($stmt->rowCount()) {
                $counter = $stmt->rowCount();

                $totalPaginas = $counter / 100;
                $totalPaginas = ceil($totalPaginas); //redondear al siguiente
                $resul        = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $response = ['data' => $resul, 'contador' => $counter, 'totalPaginas' => $totalPaginas];
            } else {
                $response = ['No se encontraron datos', 400];
            }
        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }

        $this->_DB = null;
        echo json_encode($response);
    }

    public function GrupoCola()
    {
        try {
            $stmt = $this->_DB->query("SELECT nota FROM Notas WHERE nota <> 'mal codigo' ORDER BY nota");
            $stmt->execute();

            if ($stmt->rowCount()) {
                $response = [$stmt->fetchAll(PDO::FETCH_ASSOC), 201];
            } else {
                $response = ['No se encontraron registros', 400];
            }

        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }

        $this->_DB = null;
        echo json_encode($response);

    }

    public function gestionEscalimiento()
    {
        try {
            $stmt = $this->_DB->query("SELECT g.gestion FROM Gestiones g");

            $stmt->execute();

            if ($stmt->rowCount()) {
                $result   = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $response = [$result, 201];
            } else {
                $response = ['No se encontraron registros', 201];
            }

        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }

        $this->_DB = null;
        echo json_encode($response);

    }

    public function observacionEscalimiento($data)
    {

        try {
            $stmt = $this->_DB->prepare("SELECT o.observacion
                                                FROM Gestiones g
                                                         INNER JOIN GestionesObservaciones go ON g.codGestion = go.codGestion
                                                         INNER JOIN Observaciones o ON go.codObservacion = o.codObservacion
                                                WHERE g.gestion = :gestion");
            $stmt->execute([':gestion' => $data]);

            if ($stmt->rowCount()) {
                $result   = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $response = [$result, 201];
            } else {
                $response = ['No se encontraron registros', 400];
            }

        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }

        $this->_DB = null;
        echo json_encode($response);
    }

    public function notasEscalamiento($data)
    {
        try {
            $stmt = $this->_DB->prepare("SELECT n.nota
                                                FROM Gestiones g
                                                         INNER JOIN GestionesObservaciones go ON g.codGestion = go.codGestion
                                                         INNER JOIN Observaciones o ON go.codObservacion = o.codObservacion
                                                         INNER JOIN ObservacionesNotas ot ON o.codObservacion = ot.codObservacion
                                                         INNER JOIN Notas n ON ot.codNota = n.codNota
                                                WHERE o.observacion = :observacion");
            $stmt->execute([':observacion' => $data]);

            if ($stmt->rowCount()) {
                $result   = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $response = [$result, 201];
            } else {
                $response = ['No se encontraron registros', 400];
            }
        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        $this->_DB = null;
        echo json_encode($response);
    }

    public function infoEscalamiento($params)
    {
        try {
            $datos = $params['datosEdicion'];

            $pedido            = $datos['pedido'];
            $tecnico           = $datos['tecnico'];
            $loginDespachador  = $datos['loginDespachador'];
            $nombreDespachador = $datos['nombreDespachador'];
            $tecnologia        = $datos['tecnologia'];
            $region            = $datos['region'];
            $ciudad            = $datos['municipio'];
            $grupoCola         = $datos['grupoCola'];
            $gestion           = $datos['gestion'];
            $observacion       = $datos['observacion'];
            $nota              = $datos['nota'];
            $devueltoPorInfra  = $datos['devueltoPorInfra'];
            $gtc               = $datos['gtc'];
            $click             = $datos['click'];
            $pagSto            = $datos['pagSto'];
            $siebel            = $datos['siebel'];

            if ($gtc == true) {
                $gtc = 'SI';
            } else {
                $gtc = 'NO';
            }

            if ($click == true) {
                $click = 'SI';
            } else {
                $click = 'NO';
            }

            if ($pagSto == true) {
                $pagSto = 'SI';
            } else {
                $pagSto = 'NO';
            }

            if ($siebel == true) {
                $siebel = 'SI';
            } else {
                $siebel = 'NO';
            }

            if (isset($datos['id'])) {

                $stmt = $this->_DB->prepare("UPDATE Escalamientos e
				SET e.pedido 			= TRIM(:pedido),
					e.tecnico 			= LOWER(TRIM(:tecnico)),
					e.loginDespachador 	= LOWER(TRIM(:loginDespachador)),
					e.nombreDespachador = LOWER(TRIM(:nombreDespachador)),
					e.tecnologia 		= TRIM(:tecnologia),
					e.region 			= LOWER(TRIM(:region)),
					e.ciudad 			= LOWER(TRIM(:ciudad)),
					e.grupoCola 		= LOWER(TRIM(:grupoCola)),
					e.gestion 			= LOWER(TRIM(:gestion)),
					e.observacion 		= LOWER(TRIM(:observacion)),
					e.nota 				= LOWER(TRIM(:nota)),
					e.fechaGestion 		= NOW(),
					e.devueltoPorInfra 	= TRIM(:devueltoPorInfra),
					e.gtc 				= :gtc,
					e.click 			= :click,
					e.paginaSeguimiento = :pagSto,
					e.siebel 			= :siebel
				WHERE e.id = :key");
                $stmt->execute([
                    ':pedido'            => $pedido,
                    ':tecnico'           => $tecnico,
                    ':loginDespachador'  => $loginDespachador,
                    ':nombreDespachador' => $nombreDespachador,
                    ':tecnologia'        => $tecnologia,
                    ':region'            => $region,
                    ':ciudad'            => $ciudad,
                    ':grupoCola'         => $grupoCola,
                    ':gestion'           => $gestion,
                    ':observacion'       => $observacion,
                    ':nota'              => $nota,
                    ':devueltoPorInfra'  => $devueltoPorInfra,
                    ':gtc'               => $gtc,
                    ':click'             => $click,
                    ':pagSto'            => $pagSto,
                    ':siebel'            => $siebel,
                    ':key'               => $datos['id'],
                ]);

                if ($stmt->rowCount()) {
                    $response = ['Pedido actualizado', 201];
                } else {
                    $response = ['Error intentelo de nuevo', 400];
                }

            } else {

                $stmt = $this->_DB->prepare("INSERT INTO Escalamientos
                                                    (pedido, tecnico, loginDespachador, nombreDespachador, tecnologia, region, ciudad, grupoCola,
                                                     gestion, observacion, nota, fechaGestion, devueltoPorInfra, gtc, click, paginaSeguimiento, siebel)
                                                    VALUES (TRIM(:pedido), LOWER(TRIM(:tecnico)), LOWER(TRIM(:loginDespachador)), LOWER(TRIM(:nombreDespachador)), TRIM(:tecnologia),
                                                            LOWER(TRIM(:region)), LOWER(TRIM(:ciudad)), LOWER(TRIM(:grupoCola)), LOWER(TRIM(:gestion)), LOWER(TRIM(:observacion)),
                                                            LOWER(TRIM(:nota)), NOW(), TRIM(:devueltoPorInfra), :gtc, :click, :pagSto, :siebel)");
                $stmt->execute([
                    ':pedido'            => $pedido,
                    ':tecnico'           => $tecnico,
                    ':loginDespachador'  => $loginDespachador,
                    ':nombreDespachador' => $nombreDespachador,
                    ':tecnologia'        => $tecnologia,
                    ':region'            => $region,
                    ':ciudad'            => $ciudad,
                    ':grupoCola'         => $grupoCola,
                    ':gestion'           => $gestion,
                    ':observacion'       => $observacion,
                    ':nota'              => $nota,
                    ':devueltoPorInfra'  => $devueltoPorInfra,
                    ':gtc'               => $gtc,
                    ':click'             => $click,
                    ':pagSto'            => $pagSto,
                    ':siebel'            => $siebel,
                ]);

                if ($stmt->rowCount()) {
                    $response = ['Pedido Actualizado', 201];
                } else {
                    $response = ['Ah ocurrido un erro intentalo de nuevo'];
                }

            }
        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }

        $this->_DB = null;
        echo json_encode($response);
    }

    public function csvEscalamientoExp($params)
    {
        try {

            $usuarioid = $_SESSION['login'];
            $datos     = $params['datos'];
            $fechaini  = $datos['fechain'];
            $fechafin  = $datos['fechafi'];

            if ($fechaini == "" && $fechafin == "") {
                $fechaini = date("Y") . "-" . date("m") . "-" . date("d");
                $fechafin = date("Y") . "-" . date("m") . "-" . date("d");
            }

            if ($fechaini == $fechafin) {
                $filename = "Escalamiento" . "_" . $fechaini . "_" . $usuarioid . ".csv";
            } else {
                $filename = "Escalamiento" . "_" . $fechaini . "_" . $fechafin . "_" . $usuarioid . ".csv";
            }

            $stmt = $this->_DB->prepare("SELECT e.fechaGestion,
                                                           e.pedido,
                                                           e.tecnico,
                                                           e.loginDespachador,
                                                           e.nombreDespachador,
                                                           e.tecnologia,
                                                           e.region,
                                                           e.ciudad,
                                                           e.grupoCola,
                                                           e.gestion,
                                                           e.observacion,
                                                           e.nota,
                                                           e.devueltoPorInfra,
                                                           e.gtc,
                                                           e.click,
                                                           e.paginaSeguimiento,
                                                           e.siebel
                                                    FROM Escalamientos e
                                                    WHERE 1 = 1
                                                      AND e.fechaGestion BETWEEN (:fechaini) AND (:fechafin)");
            $stmt->execute([':fechaini' => "$fechaini 00:00:00", ':fechafin' => "$fechafin 23:59:59"]);

            if ($stmt->rowCount()) {
                $fp = fopen("../tmp/$filename", 'w');

                $columnas = [
                    'Fecha Gestion',
                    'Pedido',
                    'Login del Tecnico',
                    'Login Despachador',
                    'Nombre del Despachador',
                    'Tecnologia',
                    'Region',
                    'Ciudad',
                    'Grupo - Cola',
                    'Gestion',
                    'Observacion',
                    'Notas',
                    'Devuelto por Infra',
                    'Se Escalo en Gtc',
                    'Se Escalo en Click',
                    'Se Escalo en PagSto',
                    'Se Escalo en Siebel',
                ];
                fputcsv($fp, $columnas);
                fputcsv($fp, $stmt->fetchAll(PDO::FETCH_ASSOC));
                fclose($fp);
                $response = [$filename, $stmt->rowCount(), 201];
            } else {
                $response = ['', 400];
            }

        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        $this->_DB = null;
        echo json_encode($response);
    }

    public function saveescalamiento($params)
    {
        try {

            $datosguardar          = $params['datosguardar'];
            $login                 = $_SESSION['login'];
            $pedido                = $datosguardar['pedido'];
            $task                  = $datosguardar['task'];
            $tecnico               = $datosguardar['engineer'];
            $id_tecnico            = $datosguardar['engineerID'];
            $fecha_solicitud       = $datosguardar['dateCreated'];
            $fecha_solicitud       = date('Y-m-d H:i:s', strtotime($fecha_solicitud));
            $login_gestion         = $login;
            $engestion             = 1;
            $proceso               = $datosguardar['proceso'];
            $producto              = $datosguardar['producto'];
            $motivo                = $datosguardar['motivo'];
            $area                  = $datosguardar['area'];
            $region                = $datosguardar['region'];
            $crm                   = $datosguardar['crm'];
            $task_type             = $datosguardar['taskType'];
            $tecnologia            = $datosguardar['tech'];
            $departamento          = $datosguardar['department'];
            $prueba_smnet          = $datosguardar['isSmnetTestSif'];
            $foto_adjunta          = $datosguardar['isPhoto'];
            $marcacion_tap         = $datosguardar['correa_marcacion'];
            $direccion_tap         = $datosguardar['addressTap'];
            $valor_tap             = $datosguardar['vTap'];
            $mac_real_cpe          = $datosguardar['mac_real_cpe'];
            $informacion_adicional = $datosguardar['informacion_adicional'];
            $correa_marcacion      = $datosguardar['correa_marcacion'];
            $observaciones         = $datosguardar['observaciones'];
            $ans                   = $datosguardar['ans'];
            $id_terreno            = $datosguardar['_id'];
            $estado                = 0;

            if (!$pedido || !$tecnico || !$proceso || !$producto) {
                $response = ['Verifique los datos', 400];
                echo json_encode($response);
                exit();
            }

            $stmt = $this->_DB->prepare("INSERT INTO escalamiento_infraestructura
                                            (pedido, tarea, tecnico, id_tecnico, fecha_solicitud, login_gestion, engestion, proceso,
                                             producto, motivo, area, region, crm, tipo_tarea, tecnologia, departamento, prueba_smnet, foto_adjunta,
                                             marcacion_tap, direccion_tap, valor_tap, mac_real_cpe, informacion_adicional, correa_marcacion, 
                                             observaciones, estado, id_terreno, ans)
                                            values (:pedido, :task, :tecnico, :id_tecnico, :fecha_solicitud, :login_gestion, :engestion, :proceso,
                                                    :producto, :motivo, :area, :region, :crm, :task_type, :tecnologia, :departamento, :prueba_smnet, :foto_adjunta,
                                                    :marcacion_tap, :direccion_tap, :valor_tap, :mac_real_cpe, :informacion_adicional, :correa_marcacion, 
                                                    :observaciones, :estado, :id_terreno, :ans)");
            $stmt->execute([
                ':$pedido'                => $pedido,
                ':$task'                  => $task,
                ':$tecnico'               => $tecnico,
                ':$id_tecnico'            => $id_tecnico,
                ':$fecha_solicitud'       => $fecha_solicitud,
                ':$login_gestion'         => $login_gestion,
                ':$engestion'             => $engestion,
                ':$proceso'               => $proceso,
                ':$producto'              => $producto,
                ':$motivo'                => $motivo,
                ':$area'                  => $area,
                ':$region'                => $region,
                ':$crm'                   => $crm,
                ':$task_type'             => $task_type,
                ':$tecnologia'            => $tecnologia,
                ':$departamento'          => $departamento,
                ':$prueba_smnet'          => $prueba_smnet,
                ':$foto_adjunta'          => $foto_adjunta,
                ':$marcacion_tap'         => $marcacion_tap,
                ':$direccion_tap'         => $direccion_tap,
                ':$valor_tap'             => $valor_tap,
                ':$mac_real_cpe'          => $mac_real_cpe,
                ':$informacion_adicional' => $informacion_adicional,
                ':$correa_marcacion'      => $correa_marcacion,
                ':$observaciones'         => $observaciones,
                ':$id_terreno'            => $id_terreno,
                ':$ans'                   => $ans,

            ]);

            if ($stmt->rowCount() == 1) {
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

    public function exportEscalamientos()
    {

        try {
            $stmt = $this->_DB->query("SELECT * FROM escalamiento_infraestructura ORDER BY fecha_solicitud");
            $stmt->execute();

            if ($stmt->rowCount()) {
                $result   = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $response = [$result, 201];
            } else {
                $response = ['No se encontraron registros', 400];
            }

        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        $this->_DB = null;
        echo json_encode($response);
    }
}
