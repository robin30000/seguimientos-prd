<?php
require_once '../class/conection.php';

class modelNovedadesTecnico
{
    private $_DB;

    public function __construct()
    {
        $this->_DB = new Conection();
    }

    public function novedadesTecnico($params)
    {
        try {

            $pagina = $params['page'];
            $datos  = $params['datos'];
            /* $fechaini = $datos['fechaini'];
            $fechafin = $datos['fechafin']; */
            $fechaini = (!isset($datos['fechaini'])) ? date("Y-m-d") : $datos['fechaini']; //CORRECCION DE VALIDACION DE FECHA
            $fechafin = (!isset($datos['fechafin'])) ? date("Y-m-d") : $datos['fechafin']; //CORRECCION DE VALIDACION DE FECHA

            if ($fechaini == "" || $fechafin == "") {
                $fechaini = date('Y-m-d');
                $fechafin = date('Y-m-d');
            }

            if (!$pagina) {
                $pagina = "0";
            } else {
                $pagina = $pagina - 1;
            }

            $pagina = $pagina * 100;

            $query = "SELECT id, cedulaTecnico, nombreTecnico, contracto, proceso, pedido, tiponovedad, municipio, situacion, 
                                horamarcaensitio, observaciones, idllamada, observacionCCO
						FROM NovedadesVisitas
							WHERE 1=1
							AND fecha BETWEEN ('$fechaini 00:00:00') AND ('$fechafin 23:59:59')
							ORDER BY fecha DESC
							limit 100 offset $pagina";

            $queryCount = "SELECT COUNT(*) AS Cantidad FROM NovedadesVisitas
								WHERE 1=1
								AND fecha BETWEEN ('$fechaini 00:00:00') AND ('$fechafin 23:59:59')";

            $rr = $this->_DB->query($queryCount);
            $rr->execute();

            //Dado el total, contra el numumero de paginas
            $totalPaginas = 0;
            $counter      = 0;
            if ($rr->rowCount() > 0) {
                $result = array();
                if ($row = $rr->fetchAll(PDO::FETCH_ASSOC)) {
                    $counter = $row[0]['Cantidad'];

                    $totalPaginas = $counter / 100;
                    $totalPaginas = ceil($totalPaginas); //redondear al siguiente
                }
            }

            $rst = $this->_DB->query($query);
            $rst->execute();

            if ($rst->rowCount() > 0) {
                $result    = $rst->fetchAll(PDO::FETCH_ASSOC);
                $resultado = [];

                while ($row = $rst->fetchAll(PDO::FETCH_ASSOC)) {

                    $row['cedulaTecnico']    = utf8_encode($row['cedulaTecnico']);
                    $row['nombreTecnico']    = utf8_encode($row['nombreTecnico']);
                    $row['contracto']        = utf8_encode($row['contracto']);
                    $row['proceso']          = utf8_encode($row['proceso']);
                    $row['pedido']           = utf8_encode($row['pedido']);
                    $row['tiponovedad']      = utf8_encode($row['tiponovedad']);
                    $row['municipio']        = utf8_encode($row['municipio']);
                    $row['situacion']        = utf8_encode($row['situacion']);
                    $row['horamarcaensitio'] = utf8_encode($row['horamarcaensitio']);
                    $row['idllamada']        = utf8_encode($row['idllamada']);
                    $row['observaciones']    = utf8_encode($row['observaciones']);
                    $row['observacionCCO']   = utf8_encode($row['observacionCCO']);

                    $resultado[] = $row;

                }
                $response = [
                    'data'         => $result,
                    'contador'     => $counter,
                    'totalPaginas' => $totalPaginas,
                ];
            } else {
                $response = ['state' => 0, 'msj' => 'No se encontraron datos'];
            }
        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        $this->_DB = null;
        echo json_encode($response);
    }

    public function guardarNovedadesTecnico($data)
    {
        session_start();
        $login = $_SESSION['login'];
        //$login  = $login['LOGIN'];
        /* $key = $datos['id'];
		$contracto = $datos['contracto'];
		$cedulaTecnico = $datos['cedulaTecnico'];
		$nombreTecnico = utf8_decode($datos['nombreTecnico']);
		$region = $datos['region'];
		$municipio = utf8_decode($datos['municipio']);
		$situacion = $datos['situacion'];
		$detalle = $datos['detalle'];
		$observaciones = utf8_decode($datos['observaciones']);
		$tiponovedad = utf8_decode($datos['tiponovedad']);
		$pedido = $datos['pedido'];
		$proceso = $datos['proceso']; */
        $key                = (isset($data['id'])) ? $data['id'] : '';
        $contracto          = (isset($data['contracto'])) ? $data['contracto'] : '';
        $cedulaTecnico      = (isset($data['cedulaTecnico'])) ? $data['cedulaTecnico'] : '';
        $nombreTecnico      = (isset($data['nombreTecnico'])) ? utf8_decode($data['nombreTecnico']) : '';
        $region             = (isset($data['region'])) ? $data['region'] : '';
        $municipio          = (isset($data['municipio'])) ? utf8_decode($data['municipio']) : '';
        $situacion          = (isset($data['situacion'])) ? $data['situacion'] : '';
        $detalle            = (isset($data['detalle'])) ? $data['detalle'] : '';
        $observaciones      = (isset($data['observaciones'])) ? utf8_decode($data['observaciones']) : '';
        $tiponovedad        = (isset($data['tiponovedad'])) ? utf8_decode($data['tiponovedad']) : '';
        $pedido             = (isset($data['pedido'])) ? $data['pedido'] : '';
        $proceso            = (isset($data['proceso'])) ? $data['proceso'] : '';
        $situaciontriangulo = (isset($data['situaciontriangulo'])) ? utf8_decode($data['situaciontriangulo']) : '';
        $motivo             = (isset($data['motivotriangulo'])) ? utf8_decode($data['motivotriangulo']) : '';
        if (isset($data['submotivotriangulo'])) {
            $submotivo = utf8_decode($data['submotivotriangulo']);
        } else {
            $submotivo = "";
        }
        $horamarcasitio = date('h:i A', strtotime($data['horamarcaensitio']));
        $idllamada      = $data['idLlamada'];

        $contrato2      = $data['contrato2'];
        $cedulaTecnico2 = $data['cedulaTecnico2'];
        $nombreTecnico2 = utf8_decode($data['nombreTecnico2']);
        $proceso2       = $data['proceso2'];
        $municipio2     = utf8_decode($data['municipio2']);


        if ($tiponovedad == 'Cumplimiento de Agenda' and $cedulaTecnico == null) {

            $stmt = $this->_DB->prepare("INSERT INTO NovedadesVisitas
            (fecha, usuario, tiponovedad, pedido, contracto, cedulaTecnico, nombreTecnico, proceso, region, municipio, situacion, horamarcaensitio, detalle, observaciones, idllamada, motivo,
             submotivo)
            VALUES (NOW(), :login, :tiponovedad, :pedido, UPPER(:contrato2), TRIM(:cedulaTecnico2), TRIM(:nombreTecnico2), TRIM(:proceso2), LOWER(:region), LOWER(:municipio2),
                    LOWER(:situaciontriangulo), :horamarcasitio, LOWER(:detalle), LOWER(TRIM(:observaciones)), TRIM(:idllamada), :motivo, :submotivo)");
            $stmt->execute([
                ':login'              => $login,
                ':tiponovedad'        => $tiponovedad,
                ':pedido'             => $pedido,
                ':contrato2'          => $contrato2,
                ':cedulaTecnico2'     => $cedulaTecnico2,
                ':nombreTecnico2'     => $nombreTecnico2,
                ':proceso2'           => $proceso2,
                ':region'             => $region,
                ':municipio2'         => $municipio2,
                ':situaciontriangulo' => $situaciontriangulo,
                ':horamarcasitio'     => $horamarcasitio,
                ':detalle'            => $detalle,
                ':observaciones'      => $observaciones,
                ':idllamada'          => $idllamada,
                ':motivo'             => $motivo,
                ':submotivo'          => $submotivo,
            ]);

            if ($stmt->rowCount() == 1) {
                $response = ['Pedido actualizado' . 201];
            } else {
                $response = ['Error' . 400];
            }

            /*$sqlInsetar = ("
							INSERT INTO NovedadesVisitas
								(fecha, usuario, tiponovedad, pedido, contracto, cedulaTecnico, nombreTecnico, proceso, region, municipio, situacion, horamarcaensitio, detalle, observaciones, idllamada, motivo, submotivo)
							VALUES
								(NOW(), '$login', '$tiponovedad', '$pedido', UPPER('$contrato2'), TRIM($cedulaTecnico2), TRIM('$nombreTecnico2'), TRIM('$proceso2'), LOWER('$region'), LOWER('$municipio2'), LOWER('$situaciontriangulo'), '$horamarcasitio', LOWER('$detalle'), LOWER(TRIM('$observaciones')), TRIM('$idllamada'), '$motivo', '$submotivo')
						");

            $rst = $this->connseguimiento->query($sqlInsetar);

            /*==========OPCION 1=============
            if (is_numeric($rst) or $rst === true) {
                $this->response($this->json('Pedido actualizado'), 201);
            } else {
                $this->response($this->json("Error"), 400);
            }*/

        } elseif ($tiponovedad == 'Cumplimiento de Agenda' and $region <> null) {

            $stmt = $this->_DB->prepare("INSERT INTO NovedadesVisitas
            (fecha, usuario, tiponovedad, pedido, contracto, cedulaTecnico, nombreTecnico, proceso, region, municipio, situacion, horamarcaensitio, detalle, observaciones, idllamada, motivo,
             submotivo)
            VALUES (NOW(), :login, :tiponovedad, :pedido, :contracto, TRIM(:cedulaTecnico), UPPER(TRIM(:nombreTecnico)), TRIM(:proceso), LOWER(:region), LOWER(:municipio),
                    LOWER(:situaciontriangulo), :horamarcasitio, LOWER(:detalle), LOWER(TRIM(:observaciones)), TRIM(:idllamada), :motivo, :submotivo)");

            $stmt->execute([
                ':login'              => $login,
                ':tiponovedad'        => $tiponovedad,
                ':pedido'             => $pedido,
                ':contracto'          => $contracto,
                ':cedulaTecnico'      => $cedulaTecnico,
                ':nombreTecnico'      => $nombreTecnico,
                ':proceso'            => $proceso,
                ':region'             => $region,
                ':municipio'          => $municipio,
                ':situaciontriangulo' => $situaciontriangulo,
                ':horamarcasitio'     => $horamarcasitio,
                ':detalle'            => $detalle,
                ':observaciones'      => $observaciones,
                ':idllamada'          => $idllamada,
                ':motivo'             => $motivo,
                ':submotivo'          => $submotivo,
            ]);

            if ($stmt->rowCount() == 1) {
                $response = ['Pedido actualizado' . 201];
            } else {
                $response = ['Error' . 400];
            }

            /*$sqlInsetar = ("
							INSERT INTO NovedadesVisitas
								(fecha, usuario, tiponovedad, pedido, contracto, cedulaTecnico, nombreTecnico, proceso, region, municipio, situacion, horamarcaensitio, detalle, observaciones, idllamada, motivo, submotivo)
							VALUES
								(NOW(), '$login', '$tiponovedad', '$pedido', '$contracto', TRIM($cedulaTecnico), UPPER(TRIM('$nombreTecnico')), TRIM('$proceso'), LOWER('$region'), LOWER('$municipio'), LOWER('$situaciontriangulo'), '$horamarcasitio', LOWER('$detalle'), LOWER(TRIM('$observaciones')), TRIM('$idllamada'), '$motivo', '$submotivo')
						");

            $rst = $this->connseguimiento->query($sqlInsetar);

            /*==========OPCION 1=============
            if (is_numeric($rst) or $rst === true) {
                $this->response($this->json('Pedido actualizado'), 201);
            } else {
                $this->response($this->json("Error"), 400);
            }*/

        } elseif ($tiponovedad == 'Triangulo de Produccion' and $cedulaTecnico == null) {

            $stmt = $this->_DB->prepare("INSERT INTO NovedadesVisitas
            (fecha, usuario, tiponovedad, pedido, contracto, cedulaTecnico, nombreTecnico, proceso, region, municipio, situacion, horamarcaensitio, observaciones, idllamada, motivo, submotivo)
            VALUES (NOW(), :login, :tiponovedad, :pedido, UPPER(:contrato2), TRIM(:cedulaTecnico2), TRIM(:nombreTecnico2), LOWER(:proceso2), LOWER(:region), LOWER(:municipio2),
                    LOWER(:situaciontriangulo), :horamarcasitio, LOWER(TRIM(:observaciones)), TRIM(:idllamada), :motivo, :submotivo)");

            $stmt->execute([
                ':$login'             => $login,
                ':tiponovedad'        => $tiponovedad,
                ':pedido'             => $pedido,
                ':contrato2'          => $contrato2,
                ':cedulaTecnico2'     => $cedulaTecnico2,
                ':nombreTecnico2'     => $nombreTecnico2,
                ':proceso2'           => $proceso2,
                ':region'             => $region,
                ':municipio2'         => $municipio2,
                ':situaciontriangulo' => $situaciontriangulo,
                ':horamarcasitio'     => $horamarcasitio,
                ':observaciones'      => $observaciones,
                ':idllamada'          => $idllamada,
                ':motivo'             => $motivo,
                ':submotivo'          => $submotivo,
            ]);

            if ($stmt->rowCount() == 1) {
                $response = ['Pedido actualizado' . 201];
            } else {
                $response = ['Error' . 400];
            }

            /*$sqlInsetar = ("
							INSERT INTO NovedadesVisitas
								(fecha, usuario, tiponovedad, pedido, contracto, cedulaTecnico, nombreTecnico, proceso, region, municipio, situacion, horamarcaensitio, observaciones, idllamada, motivo, submotivo)
							VALUES
								(NOW(), '$login', '$tiponovedad', '$pedido', UPPER('$contrato2'), TRIM($cedulaTecnico2), TRIM('$nombreTecnico2'), LOWER('$proceso2'),LOWER('$region'), LOWER('$municipio2'), LOWER('$situaciontriangulo'), '$horamarcasitio', LOWER(TRIM('$observaciones')), TRIM('$idllamada'), '$motivo', '$submotivo')
						");

            $rst = $this->connseguimiento->query($sqlInsetar);

            /*==========OPCION 1=============
            if (is_numeric($rst) or $rst === true) {
                $this->response($this->json('Pedido actualizado'), 201);
            } else {
                $this->response($this->json("Error"), 400);
            }*/

        } elseif ($tiponovedad == 'Triangulo de Produccion' and $region <> null) {

            $stmt = $this->_DB->prepare("INSERT INTO NovedadesVisitas
            (fecha, usuario, tiponovedad, pedido, contracto, cedulaTecnico, nombreTecnico, proceso, region, municipio, situacion, horamarcaensitio, observaciones, idllamada, motivo, submotivo)
            VALUES (NOW(), '$login', '$tiponovedad', '$pedido', '$contracto', TRIM($cedulaTecnico), UPPER(TRIM('$nombreTecnico')), TRIM('$proceso'), LOWER('$region'), LOWER('$municipio'),
                    LOWER('$situaciontriangulo'), '$horamarcasitio', LOWER(TRIM('$observaciones')), TRIM('$idllamada'), '$motivo', '$submotivo')");

            $stmt->execute([
                ':login'              => $login,
                ':tiponovedad'        => $tiponovedad,
                ':pedido'             => $pedido,
                ':contracto'          => $contracto,
                ':cedulaTecnico'      => $cedulaTecnico,
                ':nombreTecnico'      => $nombreTecnico,
                ':proceso'            => $proceso,
                ':region'             => $region,
                ':municipio'          => $municipio,
                ':situaciontriangulo' => $situaciontriangulo,
                ':horamarcasitio'     => $horamarcasitio,
                ':observaciones'      => $observaciones,
                ':idllamada'          => $idllamada,
                ':motivo'             => $motivo,
                ':submotivo'          => $submotivo,
            ]);

            if ($stmt->rowCount() == 1) {
                $response = ['Pedido actualizado' . 201];
            } else {
                $response = ['Error' . 400];
            }

            /*$sqlInsetar = ("
							INSERT INTO NovedadesVisitas
								(fecha, usuario, tiponovedad, pedido, contracto, cedulaTecnico, nombreTecnico, proceso, region, municipio, situacion, horamarcaensitio, observaciones, idllamada, motivo, submotivo)
							VALUES
								(NOW(), '$login', '$tiponovedad', '$pedido', '$contracto', TRIM($cedulaTecnico), UPPER(TRIM('$nombreTecnico')), TRIM('$proceso'), LOWER('$region'), LOWER('$municipio'), LOWER('$situaciontriangulo'), '$horamarcasitio', LOWER(TRIM('$observaciones')), TRIM('$idllamada'), '$motivo', '$submotivo')
						");

            $rst = $this->connseguimiento->query($sqlInsetar);

            /*==========OPCION 1=============
            if (is_numeric($rst) or $rst === true) {
                $this->response($this->json('Pedido actualizado'), 201);
            } else {
                $this->response($this->json("Error"), 400);
            }*/

        }
        $this->_DB = null;
        echo json_encode($response);
    }

    public function updateNovedadesTecnico($data)
    {
        try {
            $observacionCCO = $data['datosEditar'];
            $pedido         = $data['pedido'];

            $stmt = $this->_DB->prepare("UPDATE NovedadesVisitas SET observacionCCO = :observacionCCO WHERE pedido = :pedido");
            $stmt->execute([':$observacionCCO' => $observacionCCO, ':$pedido' => $pedido]);

            if ($stmt->rowCount() == 1) {
                $response = ['Novedad actualizada' . 201];
            } else {
                $response = ['Error' . 400];
            }
        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        $this->_DB = null;
        echo json_encode($response);
    }

    public function csvNovedadesTecnico($data)
    {

        session_start();
        $usuarioid = $_SESSION['login'];
        if ($data) {
            $fechaini = $data['fechaini'];
            $fechafin = $data['fechafin'];
        }


        if ($fechaini == "" && $fechafin == "") {
            $fechaini = date("Y") . "-" . date("m") . "-" . date("d");
            $fechafin = date("Y") . "-" . date("m") . "-" . date("d");
        }

        try {
            $stmt = $this->_DB->prepare("SELECT n.fecha,
                                                   n.usuario,
                                                   n.municipio,
                                                   n.region,
                                                   n.proceso,
                                                   n.horamarcaensitio,
                                                   n.tiponovedad,
                                                   n.pedido,
                                                   n.cedulaTecnico,
                                                   n.nombreTecnico,
                                                   n.contracto,
                                                   n.situacion,
                                                   n.motivo,
                                                   n.submotivo,
                                                   n.observaciones,
                                                   n.observacionCCO,
                                                   n.idllamada
                                            FROM NovedadesVisitas n
                                            WHERE 1 = 1
                                              AND n.fecha BETWEEN (:fechaini) AND (:fechafin)");
            $stmt->execute([':fechaini' => "$fechaini 00:00:00", ':fechafin' => "$fechaini 23-59-59"]);

            if ($stmt->rowCount()) {
                $result   = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $response = [$result, $stmt->rowCount(), 201];

            } else {
                $response = ['', 400];
            }

        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }

        $this->_DB = null;
        echo json_encode($response);
    }

    public function Regiones()
    {
        try {
            $stmt = $this->_DB->query("SELECT region FROM regiones ORDER BY region");
            $stmt->execute();
            if ($stmt->rowCount()) {
                $response = [$stmt->fetchAll(PDO::FETCH_ASSOC), 200];
            } else {
                $response = ['Error', 400];
            }
        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        $this->_DB = null;
        echo json_encode($response);
    }

    public function Municipios($data)
    {

        try {
            $stmt = $this->_DB->prepare("SELECT municipio
                                            FROM municipios m
                                            INNER JOIN regiones r ON m.codigoRg=r.codigoRg
                                            WHERE region = ?
                                            ORDER BY municipio");
            $stmt->bindParam(1, $data, PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount()) {
                $response = [$stmt->fetchAll(PDO::PARAM_STR), 201];
            } else {
                $response = ["error", 400];
            }
        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        $this->_DB = null;
        echo json_encode($response);
    }

    public function SituacionNovedadesVisitas()
    {
        try {
            $stmt = $this->_DB->query("SELECT situacion
					FROM SituacionNovedadesVisitas
					ORDER BY situacion");

            if ($stmt->rowCount()) {

                $response = [$stmt->fetchAll(PDO::FETCH_ASSOC), 201];

            } else {
                $response = ["error", 400];
            } // If no records "No Content" status
        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        $this->_DB = null;
        echo json_encode($response);
    }

    public function DetalleNovedadesVisitas($data)
    {
        try {
            $stmt = $this->_DB->prepare("SELECT dnv.detalle
					FROM DetalleNovedadesVisitas dnv
					INNER JOIN SituacionNovedadesVisitas snv ON dnv.situacionId=snv.situacionId
					WHERE snv.situacion = ?
					ORDER BY dnv.detalle ");
            $stmt->bindParam(1, $data, PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount()) {
                $response = [$stmt->fetchAll(PDO::FETCH_ASSOC), 201];
            } else {
                $response = ["error", 400];
            }
        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        $this->_DB = null;
        echo json_encode($response);
    }

    public function BFobservaciones()
    {
        session_start();
        $login = $_SESSION['login'];
        $hoy   = date("Y-m-d");
        try {
            $stmt = $this->_DB->prepare("SELECT PedidoDespacho, observacionAsesor, pedidobloqueado, gestionAsesor, estado, AccionDespacho
						FROM BrutalForce
						WHERE loginDespacho = :login
						AND (FechaGestionDespacho BETWEEN (:fechaini) AND (:fechafin) OR fechagestionAsesor BETWEEN (:fechaini) AND (:fechafin))");
            $stmt->execute([':login' => $login, ':fechaini' => "$hoy 00:00:00", ':fechafin' => "$hoy 23:59:59"]);

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

    public function registrospwdTecnicos($data)
    {

        try {

            $datos    = $data['datos'];
            $concepto = $datos['concepto'];
            $buscar   = $datos['buscar'];

            $stmt = $this->_DB->query("SELECT c.cedula, c.login, c.nombre, c.password, c.expiraCuenta, c.expirapsw FROM cuentasTecnicos c where 1=1 And :parametro");
            $stmt->execute([':parametro' => " $concepto = '$buscar'"]);
            if ($stmt->rowCount()) {
                $response = [$stmt->fetchAll(PDO::FETCH_ASSOC), 201];
            } else {
                $response = ['', 400];
            }

        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        $this->_DB = null;
        echo json_encode($response);
    }

    public function editarPwdTecnicos($data)
    {
        try {
            $datos  = $data['datosEdicion'];
            $cedula = $datos['cedula'];
            $pwd    = $datos['newpwd'];

            $stmt = $this->_DB->prepare("update cuentasTecnicos set password = :password where cedula = :cedula");
            $stmt->execute([':password' => $pwd, ':cedula' => $cedula]);
            if ($stmt->rowCount()) {
                $response = ['Usuario actualizado', 201];
            } else {
                $response = ['Ah ocurrido un error intentalo de nuevo', 400];
            }

        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }

        $this->_DB = null;
        echo json_encode($response);
    }

    public function csvContrasenasTecnicos()
    {
        try {
            $usuarioid = $_SESSION['login'];
            $filename  = "ContrasenasTecnicosClick" . "_" . $usuarioid . ".csv";

            $stmt = $this->_DB->query("SELECT c.cedula, c.login, c.nombre, c.password, c.expiraCuenta, c.expirapsw
						FROM cuentasTecnicos c");
            $stmt->execute();

            if ($stmt->rowCount()) {
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $response = array($result, 201);

            } else {
                $response = ['', 400];
            }
        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        $this->_DB = null;
        echo json_encode($response);
    }

}
