<?php
require_once '../class/conection.php';

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);

class modelOtherServicesDos
{
    private $_DB;

    public function __construct()
    {
        $this->_DB = new Conection();
    }

    public function gestionBorrar($datosFinal)
    {
        try {

            $PedidoDespacho = $datosFinal['PedidoDespacho'];

            $stmt = $this->_DB->prepare("select idgestion id from BrutalForce where PedidoDespacho = :PedidoDespacho");
            $stmt->execute([':PedidoDespacho' => $PedidoDespacho]);

            if ($stmt->rowCount()) {
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $id     = $result['id'];

                $stmt = $this->_DB->prepare("DELETE FROM BrutalForce WHERE idgestion = :id");
                $stmt->execute([':id' => $id]);

                if ($stmt->rowCount()) {
                    $response = ['Datos eliminados', 201];
                } else {
                    $response = ['Ah ocurrido un error intentalo nuevamente', 400];
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

    public function desbloquear($datosFinal)
    {
        try {

            $PedidoDespacho = $datosFinal['PedidoDespacho'];

            $stmt = $this->_DB->prepare("select idgestion id from BrutalForce where PedidoDespacho = :PedidoDespacho");
            $stmt->execute([':PedidoDespacho' => $PedidoDespacho]);

            if ($stmt->rowCount()) {
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $id     = $result['id'];
                $update = $this->_DB->prepare("update BrutalForce SET pedidobloqueado = '0' WHERE idgestion = :id");
                $update->execute([':id' => $id]);

                if ($stmt->rowCount()) {
                    $response = ['Desbloqueo exitoso', 201];
                } else {
                    $response = ['Ah ocurrido un error intentalo nuevamente', 400];
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

    public function csvPreagen($data)
    {
        try {
            session_start();
            $usuarioid   = $_SESSION['login'];
            $datos       = $data['datos'];
            $fecha       = $datos['fecha'];
            $valor       = $data['valor'];
            $uen         = $datos['uen'];
            $tipotrabajo = $datos['tipo_trabajo'];
            $ciudad      = $datos['CIUDAD'];
            $sep         = "";
            $ciudades    = "";
            $bandera     = 0;
            $bandera1    = 0;

            if ($ciudad == null) {
                $ciudad = "";
            } else {
                $total = count($ciudad);
                for ($i = 0; $i < $total; $i++) {

                    if ($valida = strpos($ciudad[$i], '_DEPA') !== false) {
                        $bandera  = $bandera + 1;
                        $ciudades = $ciudades . $sep . "'" . str_replace("_DEPA", "", $ciudad[$i]) . "'";
                    } else {
                        $bandera1 = $bandera1 + 1;
                        $ciudades = $ciudades . $sep . "'" . $ciudad[$i] . "'";
                    }
                    $sep = ",";
                }
            }

            if ($bandera > 0 && $bandera1 == 0) {
                $ciudades = "and departamento in (" . $ciudades . ")";
            } elseif ($bandera == 0 && $bandera1 > 0) {
                $ciudades = "and ciudad in (" . $ciudades . ")";
            } else {
                $ciudades = "";
            }

            if ($fecha == "") {
                $fecha = date("Y") . "-" . date("m") . "-" . date("d");
            }

            if ($uen != "") {
                $uen = "and uen = '$uen'";
            } else {
                $uen = "";
            }
            if ($tipotrabajo != "") {
                $tipo_trabajo  = "and tipo_trabajo = '$tipotrabajo'";
                $tipo_trabajo1 = "and (select tipo_trabajo from carga_click where pro.pedido_id = pedido_id limit 1) = '$tipotrabajo'";
            } else {
                $tipo_trabajo  = "";
                $tipo_trabajo1 = "";
            }

            $filename = $valor . "_" . $fecha . "_" . $uen . "_" . $tipotrabajo . "_" . $usuarioid . ".csv";

            if ($valor == "Totalagendados") {

                $query = "select pedido_id, cliente, departamento, ciudad, direccion, " .
                         "productos, fecha_cita, jornada_cita, UEN, " .
                         "(select estado_id from carga_click cl " .
                         "where pro.pedido_id=cl.pedido_id limit 1) estado_click, " .
                         "(select observacion from carga_click cl  " .
                         "where pro.pedido_id=cl.pedido_id limit 1) observacion_click, " .
                         "(select fecha_carga_click from carga_click cl  " .
                         "where pro.pedido_id=cl.pedido_id limit 1) fecha_ingreso_click " .
                         "from carga_agenda pro " .
                         "where pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') " .
                         "$uen $tipo_trabajo1 $ciudades";


            } elseif ($valor == "TotalVistaClick") {

                $query = "select pedido_id, cliente, departamento, ciudad, direccion, productos, " .
                         "fecha_cita, " .
                         "jornada_cita, uen, estado_id,  " .
                         "(select descripcion from codigo_pendientes_click " .
                         "where codigo = pro.codigo_pendiente_incompleto) descripcion, " .
                         "actividad_trabajo, observacion, fecha_carga_click, tipo_trabajo  " .
                         "from carga_click pro  " .
                         "where pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') " .
                         "and pro.pedido_id in (select pedido_id from carga_agenda " .
                         "where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) " .
                         "$uen $tipo_trabajo $ciudades";

            } elseif ($valor == "TotalConfirmados") {

                $query = "select distinct pedido, cliente, departamento, ciudad, productos, " .
                         "fecha_cita, jornada_cita, uen, accion " .
                         "from (select reg.pedido, pro.cliente, pro.departamento, pro.ciudad,  " .
                         "pro.productos, " .
                         "pro.fecha_cita, pro.jornada_cita, pro.uen, reg.accion " .
                         "from registros reg, carga_agenda pro   " .
                         "where pro.pedido_id in (select pedido_id from carga_click " .
                         "where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) " .
                         "and pro.pedido_id = reg.pedido  " .
                         "and accion = 'Visita confirmada' " .
                         "and reg.fecha BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') " .
                         "and pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') " .
                         "$uen $tipo_trabajo1 $ciudades)b  ";

            } elseif ($valor == "TotalSinConfirmar") {

                $query = "select pedido_id, cliente, departamento, ciudad, productos, fecha_cita, " .
                         "jornada_cita, uen, descripcion, " .
                         "estado_id " .
                         "from (select distinct pro.pedido_id, pro.cliente, pro.departamento, " .
                         "pro.ciudad, pro.productos, " .
                         "pro.fecha_cita, pro.jornada_cita, pro.uen, (select descripcion from " .
                         "codigo_pendientes_click " .
                         "where codigo = pro.codigo_pendiente_incompleto) descripcion, estado_id " .
                         "from carga_click pro " .
                         "where pro.fecha_cita BETWEEN ('$fecha 00:00:00') " .
                         "AND ('$fecha 23:59:59') " .
                         "and pro.pedido_id in (select pedido_id from carga_agenda " .
                         "where fecha_cita BETWEEN ('$fecha 00:00:00') " .
                         "AND ('$fecha 23:59:59')) " .
                         "and pro.pedido_id not in " .
                         "(select reg.pedido " .
                         "from registros reg, carga_agenda pro  " .
                         "where pro.pedido_id in (select pedido_id from carga_click " .
                         "where fecha_cita BETWEEN ('$fecha 00:00:00') " .
                         "AND ('$fecha 23:59:59')) " .
                         "and pro.pedido_id = reg.pedido " .
                         "and accion = 'Visita confirmada' " .
                         "and reg.fecha BETWEEN ('$fecha 00:00:00') " .
                         "AND ('$fecha 23:59:59') " .
                         "and pro.fecha_cita BETWEEN ('$fecha 00:00:00') " .
                         "AND ('$fecha 23:59:59')) " .
                         "and pro.pedido_id not in " .
                         "(select pedido_id 	" .
                         "from carga_agenda " .
                         "where pedido_id not in " .
                         "(select pedido from registros where fecha " .
                         "BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) " .
                         "and fecha_cita BETWEEN ('$fecha 00:00:00') " .
                         "AND ('$fecha 23:59:59') " .
                         "and pedido_id in (select pedido_id from carga_click " .
                         "where fecha_cita BETWEEN ('$fecha 00:00:00') " .
                         "AND ('$fecha 23:59:59'))) $uen $tipo_trabajo $ciudades)a ";

            } elseif ($valor == "TotalNogestionados") {

                $query = "select pedido_id, cliente, departamento, ciudad, productos, fecha_cita, " .
                         "jornada_cita, uen " .
                         "from carga_agenda pro " .
                         "where pedido_id not in (select pedido from registros where fecha " .
                         "BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) " .
                         "and fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') " .
                         "and pedido_id in (select pedido_id from carga_click  " .
                         "where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) " .
                         "$uen $tipo_trabajo1 $ciudades";

            } elseif ($valor == "TotalFinalClick") {

                $query = "select pedido_id,cliente, departamento, ciudad, direccion, productos, " .
                         "fecha_cita, jornada_cita, estado_id, uen, codigo_pendiente_incompleto,  " .
                         "(select descripcion from codigo_pendientes_click " .
                         "where codigo = pro.codigo_pendiente_incompleto) descripcion, tipo_trabajo, " .
                         "observacion, fecha_carga_click " .
                         "from carga_click pro " .
                         "where pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') " .
                         "and pro.pedido_id in (select pedido_id from carga_agenda " .
                         "where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) " .
                         "and pro.estado_id = 'Finalizada' " .
                         "$uen $tipo_trabajo $ciudades";

            } elseif ($valor == "Diferenciasagendados") {

                $query = "select pedido_id, cliente, departamento, ciudad, direccion, " .
                         "productos, fecha_cita, jornada_cita, UEN, " .
                         "(select estado_id from carga_click cl  " .
                         "where pro.pedido_id=cl.pedido_id limit 1) estado_click, " .
                         "(select observacion from carga_click cl  " .
                         "where pro.pedido_id=cl.pedido_id limit 1) observacion_click, " .
                         "(select fecha_carga_click from carga_click cl " .
                         "where pro.pedido_id=cl.pedido_id limit 1) fecha_ingreso_click " .
                         "from carga_agenda pro " .
                         "where pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') " .
                         "and pro.pedido_id not in (select pedido_id from carga_click " .
                         "where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) " .
                         "$uen $tipo_trabajo1 $ciudades";

            } elseif ($valor == "DiferenciasVistaClick") {

                $query = "select pedido_id, cliente, departamento, ciudad, direccion, productos, " .
                         "fecha_cita, " .
                         "jornada_cita, uen, estado_id,  " .
                         "(select descripcion from codigo_pendientes_click " .
                         "where codigo = pro.codigo_pendiente_incompleto) descripcion, " .
                         "actividad_trabajo, observacion, fecha_carga_click, tipo_trabajo  " .
                         "from carga_click pro  " .
                         "where pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') " .
                         "and pro.pedido_id not in (select pedido_id from carga_agenda " .
                         "where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) " .
                         "$uen $tipo_trabajo $ciudades";

                $queryCount = "select count(pedido_id) Cantidad " .
                              "from carga_click pro " .
                              "where pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')  " .
                              "and pro.pedido_id not in (select pedido_id from carga_agenda " .
                              "where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59'))  " .
                              "$uen $tipo_trabajo $ciudades";
            } elseif ($valor == "DiferenciasConfirmados") {

                $query = "select pedido_id,cliente, departamento, ciudad, direccion, productos, " .
                         "fecha_cita, jornada_cita,  " .
                         "(select accion from registros " .
                         "where pedido_id = pedido limit 1) accion, (select observaciones from registros " .
                         "where pedido_id = pedido limit 1) accion " .
                         "from carga_click pro " .
                         "where pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') " .
                         "and pro.pedido_id not in (select pedido_id from carga_agenda  " .
                         "where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) " .
                         "and pro.pedido_id in (select pedido from registros where  " .
                         "accion = 'Visita confirmada'  " .
                         "and fecha BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) " .
                         "and pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') " .
                         "$uen $tipo_trabajo $ciudades";

            } elseif ($valor == "DiferenciasSinConfirmar") {

                $query = "select pedido_id, cliente, departamento, ciudad, productos, fecha_cita, " .
                         "jornada_cita, uen, (select descripcion from " .
                         "codigo_pendientes_click " .
                         "where codigo = pro.codigo_pendiente_incompleto) descripcion, " .
                         "estado_id " .
                         "from carga_click pro   " .
                         "where pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') " .
                         "and pro.pedido_id not in (select pedido_id from carga_agenda  " .
                         "where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59'))  " .
                         "and pro.pedido_id not in " .
                         "(select pedido_id " .
                         "from carga_click pro  " .
                         "where pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')  " .
                         "and pro.pedido_id not in (select pedido_id from carga_agenda " .
                         "where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) " .
                         "and pro.pedido_id in (select pedido from registros where " .
                         "accion = 'Visita confirmada' " .
                         "and fecha BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) " .
                         "and pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) " .
                         "and pro.pedido_id not in " .
                         "(select pedido_id " .
                         "from carga_click  " .
                         "where pedido_id not in (select pedido from registros  " .
                         "where fecha BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) " .
                         "and fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')  " .
                         "and pedido_id not in (select pedido_id from carga_agenda " .
                         "where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59'))) $uen $tipo_trabajo $ciudades";

            } elseif ($valor == "Diferenciasnogestionados") {

                $query = "select pedido_id, cliente, departamento, ciudad, productos, fecha_cita, " .
                         "jornada_cita, uen " .
                         "from carga_click " .
                         "where pedido_id not in (select pedido from registros " .
                         "where fecha BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) " .
                         "and fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') " .
                         "and pedido_id not in (select pedido_id from carga_agenda " .
                         "where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) $uen $tipo_trabajo $ciudades";

            } elseif ($valor == "DiferenciasFinalClick") {

                $query = "select pedido_id,cliente, departamento, ciudad, direccion, productos, " .
                         "fecha_cita, jornada_cita, estado_id, uen, codigo_pendiente_incompleto,  " .
                         "(select descripcion from codigo_pendientes_click " .
                         "where codigo = pro.codigo_pendiente_incompleto) descripcion, tipo_trabajo, " .
                         "observacion, fecha_carga_click " .
                         "from carga_click pro " .
                         "where pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') " .
                         "and pro.pedido_id not in (select pedido_id from carga_agenda " .
                         "where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) " .
                         "and pro.estado_id = 'Finalizada' " .
                         "$uen $tipo_trabajo $ciudades";
            }

            $stmt = $this->_DB->prepare($query);

            if ($stmt->rowCount()) {
                $fp = fopen("../tmp/$filename", 'w');
                if ($valor == "Totalagendados" || $valor == "Diferenciasagendados") {
                    $columnas = [
                        'PEDIDO',
                        'CLIENTE',
                        'DEPARTAMENTO',
                        'CIUDAD',
                        'DIRECCION',
                        'PRODUCTOS',
                        'FECHA_CITA',
                        'JORNADA_CITA',
                        'UEN',
                        'ESTADO_CLICK',
                        'OBSERVACION_CLICK',
                        'FECHA_INGRESO_CLICK',
                    ];
                } elseif ($valor == "TotalVistaClick" || $valor == "DiferenciasVistaClick") {
                    $columnas = [
                        'PEDIDO',
                        'CLIENTE',
                        'DEPARTAMENTO',
                        'CIUDAD',
                        'DIRECCION',
                        'PRODUCTOS',
                        'FECHA_CITA',
                        'JORNADA_CITA',
                        'UEN',
                        'ESTADO_CLICK',
                        'DESCRIPCION',
                        'ACTIVIDAD_TRABAJO',
                        'OBSERVACION_CLICK',
                        'FECHA_INGRESO_CLICK',
                        'TIPO_PENDIENTE',
                    ];
                } elseif ($valor == "TotalConfirmados" || $valor == "DiferenciasConfirmados") {
                    $columnas = [
                        'PEDIDO',
                        'CLIENTE',
                        'DEPARTAMENTO',
                        'CIUDAD',
                        'PRODUCTOS',
                        'FECHA_CITA',
                        'JORNADA_CITA',
                        'UEN',
                        'ACCION',
                        'OBSERVACIONES',
                    ];
                } elseif ($valor == "TotalSinConfirmar" || $valor == "DiferenciasSinConfirmar") {
                    $columnas = [
                        'PEDIDO',
                        'CLIENTE',
                        'DEPARTAMENTO',
                        'CIUDAD',
                        'PRODUCTOS',
                        'FECHA_CITA',
                        'JORNADA_CITA',
                        'UEN',
                        'DESCRIPCION',
                        'ESTADO_ID',
                    ];
                } elseif ($valor == "TotalNogestionados" || $valor == "Diferenciasnogestionados") {
                    $columnas = [
                        'PEDIDO',
                        'CLIENTE',
                        'DEPARTAMENTO',
                        'CIUDAD',
                        'PRODUCTOS',
                        'FECHA_CITA',
                        'JORNADA_CITA',
                        'UEN',
                    ];
                } elseif ($valor == "TotalFinalClick" || $valor == "DiferenciasFinalClick") {
                    $columnas = [
                        'PEDIDO',
                        'CLIENTE',
                        'DEPARTAMENTO',
                        'CIUDAD',
                        'DIRECCION',
                        'PRODUCTOS',
                        'FECHA_CITA',
                        'ESTADO_ID',
                        'UEN',
                        'CODIGO_PENDIENTE',
                        'DESCRIPCION',
                        'TIPO_PENDIENTE',
                        'OBSERVACION',
                        'FECHA_INGRESO_CLICK',
                    ];
                }

                fputcsv($fp, $columnas);
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                fputcsv($fp, $result['observacion_click']);
                fclose($fp);

                $response = [$filename, $stmt->rowCount(), 201];
            } else {
                $response = ['Ah ocurrido un error intentalo nuevamente', 400];
            }
        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        $this->_DB = null;
        echo json_encode($response);
    }

    public function csvContingencias($data)
    {
        try {
            session_start();
            $usuarioid = $_SESSION['login'];
            $fechaIni  = $data['fechaIni'];
            $fechafin  = $data['fechafin'];

            $stmt = $this->_DB->prepare("SELECT C.accion,
                                                       C.ciudad,
                                                       C.correo,
                                                       C.macEntra,
                                                       C.macSale,
                                                       C.motivo,
                                                       C.observacion,
                                                       C.paquetes,
                                                       C.pedido,
                                                       C.proceso,
                                                       C.producto,
                                                       C.remite,
                                                       C.tecnologia,
                                                       C.tipoEquipo,
                                                       C.uen,
                                                       C.contrato,
                                                       C.perfil,
                                                       C.logindepacho,
                                                       C.logincontingencia,
                                                       C.horagestion,
                                                       C.horacontingencia,
                                                       C.observContingencia,
                                                       C.acepta,
                                                       C.tipificacion,
                                                       C.fechaClickMarca,
                                                       C.loginContingenciaPortafolio,
                                                       C.horaContingenciaPortafolio,
                                                       C.tipificacionPortafolio,
                                                       C.observContingenciaPortafolio,
                                                       C.generarcr
                                                FROM contingencias AS C
                                                WHERE C.horagestion BETWEEN (:fechaIni) AND (:fechafin)
                                                  AND C.accion IN ('Cambio de equipo', 'Contingencia', 'Refresh', 'Registros ToIP', 'Reenvio de registros')");
            $stmt->execute([':fechaIni' => "$fechaIni 00:00:00", ':fechafin' => "$fechafin 23:59:59"]);

            if ($stmt->rowCount()) {

                $result   = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $response = [$result, 201];

            } else {
                $response = ['No se encontraron datos', 400];
            }

        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        $this->_DB = null;
        echo json_encode($response);
    }

    public function csvEstadosClick($datos)
    {
        try {
            session_start();
            $usuarioid   = $_SESSION['login'];
            $fecha       = $datos['fecha'];
            $uen         = $datos['uen'];
            $tipotrabajo = $datos['tipo_trabajo'];

            if ($fecha == "") {
                $fecha = date("Y") . "-" . date("m") . "-" . date("d");
            }

            if ($uen != "") {
                $uen = "and uen = '$uen'";
            } else {
                $uen = "";
            }
            if ($tipotrabajo != "") {
                $tipo_trabajo  = "and tipo_trabajo = '$tipotrabajo'";
                $tipo_trabajo1 = "and (select tipo_trabajo from carga_click where pro.pedido_id = pedido_id limit 1) = '$tipotrabajo'";
            } else {
                $tipo_trabajo  = "";
                $tipo_trabajo1 = "";
            }

            //echo "estos son los datos, usuario: ".$usuarioid." fecha: ".$fecha." y valor: ".$valor;
            //echo "estos son los otros tipo trabajo, usuario: ".$tipotrabajo." uen: ".$uen;

            $filename = "Estados_click" . "_" . $fecha . "_" . $uen . "_" . $tipotrabajo . "_" . $usuarioid . ".csv";

            $query = "select pro.pedido_id,pro.cliente,pro.departamento, pro.ciudad, pro.direccion, pro.productos, " .
                     "pro.fecha_cita, pro.jornada_cita, pro.estado_id, pro.uen, pro.codigo_pendiente_incompleto,  " .
                     "(select descripcion from codigo_pendientes_click  " .
                     "where codigo = pro.codigo_pendiente_incompleto) descripcion, pro.tipo_trabajo, " .
                     "pro.observacion, pro.fecha_carga_click, c.id_tecnico, c.accion, c.tipo_pendiente, c.fecha, c.observaciones " .
                     "from carga_click pro " .
                     "left join (SELECT a.pedido, a.id_tecnico, a.accion, a.tipo_pendiente, a.fecha, a.observaciones " .
                     "FROM registros a " .
                     "where a.fecha BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') ) c  " .
                     "on c.pedido = pro.pedido_id " .
                     "where pro.estado_id is not null " .
                     "and pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha  23:59:59') " .
                     "$uen $tipotrabajo";

            $stmt = $this->_DB->query($query);
            $stmt->execute();

            if ($stmt->rowCount()) {
                $fp = fopen("../tmp/$filename", 'w');

                $columnas = [
                    'PEDIDO',
                    'CLIENTE',
                    'DEPARTAMENTO',
                    'CIUDAD',
                    'DIRECCION',
                    'PRODUCTOS',
                    'FECHA_CITA',
                    'JORNADA_CITA',
                    'ESTADO_CLICK',
                    'UEN',
                    'CONCEPTO',
                    'DESCRIPCION',
                    'TIPO_TRABAJO',
                    'OBSERVACION_CLICK',
                    'FECHA_INGRESO_CLICK',
                    'TECNICO',
                    'ACCION',
                    'SUBACCION',
                    'FECHA_GESTION',
                    'OBSERVACIONES',
                ];

                fputcsv($fp, $columnas);
                while ($row = $stmt->fetchAll(PDO::FETCH_ASSOC)) {
                    $row['observacion']   = utf8_decode($row['observacion']);
                    $row['observaciones'] = utf8_decode($row['observaciones']);
                    fputcsv($fp, $row);
                }

                fclose($fp);
                $response = [$filename, $stmt->rowCount(), 201];
            } else {
                $response = ['No se encontraron datos', 400];
            }

        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        $this->_DB = null;
        echo json_encode($response);
    }

    public function CsvpeniInsta($regional)
    {
        try {
            session_start();
            $usuarioid = $_SESSION['login'];

            $filename = "Pendiente_instalaciones" . "_" . $regional . "_" . $usuarioid . ".csv";

            if ($regional == "Total") {
                $regional = " ";
            } else {
                $regional = "where REGIONAL = '$regional'";
            }

            $query = "SELECT PEDIDO_ID, " .
                     "MUNICIPIO, " .
                     "USUARIO_ID, " .
                     "NOMBRE_CANAL, " .
                     "RADICADO, " .
                     "RUTA_TRABAJO, " .
                     "TIPO_TRABAJO, " .
                     "TEL_CELULAR, " .
                     "DEPARTAMENTO, " .
                     "REGIONAL, " .
                     "INTERFAZ, " .
                     "SUBZONA, " .
                     "SEGM_PYMES, " .
                     "AREA_TRABAJO, " .
                     "FECHA_INGRESO, " .
                     "FECHA_CONCEPTO, " .
                     "FECHA_ACTUALIZACION, " .
                     "DEPARTAMENTO_OPERACION, " .
                     "DIRECCION, " .
                     "CONCEPTO_ID_ATC, " .
                     "CONCEPTO_ATC, " .
                     "ESTADO_ID_ATC, " .
                     "UEN_CALCULADA, " .
                     "TIPO_DOCUMENTO, " .
                     "CLIENTE_ID, " .
                     "NOMBRE_CLIENTE, " .
                     "CONCEPTO_ORACLE, " .
                     "RESPONSABLE_GESTION, " .
                     "AREA_RESPONSABLE, " .
                     "RESPONSABLE_OPERATIVO, " .
                     "RESPONSABLE_ACTIVITY, " .
                     "TIPO_SOLICITUD, " .
                     "TIPO_SOLICITUD_ORIG, " .
                     "RANGO_INGRESO_DIAS, " .
                     "RANGO_CONCEPTO_DIAS, " .
                     "FECHA_CITA_CALC, " .
                     "ESTADO_AGENDA, " .
                     "PRODUCTOS, " .
                     "CLIENTES, " .
                     "DETALLE_PRODUCTOS, " .
                     "DETALLE_ELEMENTOS, " .
                     "DETALLE_PRODUCTOS1, " .
                     "DETALLE_ESTADO_CN, " .
                     "DETALLE_SUBPRODUCTOS, " .
                     "REQUIEREAGENDA_ESTADOCN_ACTIVIDADCN, " .
                     "DETALLE_ACTIVIDAD_CN, " .
                     "DETALLE_CONCEPTOS, " .
                     "DETALLE_USUARIO_ULT_CONC, " .
                     "DETALLE_CONCEPTOS_ORA, " .
                     "DETALLE_TIPO_SOLICITUD, " .
                     "DETALLE_TECNOLOGIA, " .
                     "DETALLE_COLAS, " .
                     "DETALLE_ETAPA, " .
                     "DIAS_INGRESO, " .
                     "DIAS_CONCEPTO, " .
                     "ESTRATO, " .
                     "NRO_AGENDAMIENTOS, " .
                     "FECHA, " .
                     "HORAS_DE_CARGA, " .
                     "NRO_PRODUCTOS_NUEVOS, UNIDAD_NEGOCIO " .
                     "FROM pendi_insta " .
                     "$regional ";

            $stmt = $this->_DB->query($query);

            if ($stmt->rowCount()) {
                $fp       = fopen("../tmp/$filename", 'w');
                $columnas = [
                    'PEDIDO_ID',
                    'MUNICIPIO',
                    'USUARIO_ID',
                    'NOMBRE_CANAL',
                    'RADICADO',
                    'RUTA_TRABAJO',
                    'TIPO_TRABAJO',
                    'TEL_CELULAR',
                    'DEPARTAMENTO',
                    'REGIONAL',
                    'INTERFAZ',
                    'SUBZONA',
                    'SEGM_PYMES',
                    'AREA_TRABAJO',
                    'FECHA_INGRESO',
                    'FECHA_CONCEPTO',
                    'FECHA_ACTUALIZACION',
                    'DEPARTAMENTO_OPERACION',
                    'DIRECCION',
                    'CONCEPTO_ID_ATC',
                    'CONCEPTO_ATC',
                    'ESTADO_ID_ATC',
                    'UEN_CALCULADA',
                    'TIPO_DOCUMENTO',
                    'CLIENTE_ID',
                    'NOMBRE_CLIENTE',
                    'CONCEPTO_ORACLE',
                    'RESPONSABLE_GESTION',
                    'AREA_RESPONSABLE',
                    'RESPONSABLE_OPERATIVO',
                    'RESPONSABLE_ACTIVITY',
                    'TIPO_SOLICITUD',
                    'TIPO_SOLICITUD_ORIG',
                    'RANGO_INGRESO_DIAS',
                    'RANGO_CONCEPTO_DIAS',
                    'FECHA_CITA_CALC',
                    'ESTADO_AGENDA',
                    'PRODUCTOS',
                    'CLIENTES',
                    'DETALLE_PRODUCTOS',
                    'DETALLE_ELEMENTOS',
                    'DETALLE_PRODUCTOS1',
                    'DETALLE_ESTADO_CN',
                    'DETALLE_SUBPRODUCTOS',
                    'REQUIEREAGENDA_ESTADOCN_ACTIVIDADCN',
                    'DETALLE_ACTIVIDAD_CN',
                    'DETALLE_CONCEPTOS',
                    'DETALLE_USUARIO_ULT_CONC',
                    'DETALLE_CONCEPTOS_ORA',
                    'DETALLE_TIPO_SOLICITUD',
                    'DETALLE_TECNOLOGIA',
                    'DETALLE_COLAS',
                    'DETALLE_ETAPA',
                    'DIAS_INGRESO',
                    'DIAS_CONCEPTO',
                    'ESTRATO',
                    'NRO_AGENDAMIENTOS',
                    'FECHA',
                    'HORAS_DE_CARGA',
                    'NRO_PRODUCTOS_NUEVOS',
                    'UNIDAD_NEGOCIO',
                ];
                fputcsv($fp, $columnas);
                fputcsv($fp, $stmt->fetchAll(PDO::FETCH_ASSOC));
                fclose($fp);
                $response = [$filename, $stmt->rowCount(), 201];
            } else {
                $response = ['No se encontraron datos'];
            }

        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        $this->_DB = null;
        echo json_encode($response);
    }

    public function CsvGestionPendientes($data)
    {
        /**
         * TODO
         * metodo no entocntrado
         */
    }

    public function CsvNpsSemana($semana)
    {
        try {
            session_start();
            $usuarioid = $_SESSION['login'];

            $fecha = date("Y") . "-" . date("m") . "-" . date("d");

            $filename = "NPS_Semanal" . "_" . $semana . "_" . $usuarioid . ".csv";

            $stmt = $this->_DB->query("select campanaid,
                                                       lanzamiento,
                                                       idllamada,
                                                       telefono,
                                                       mensaje,
                                                       accion,
                                                       fecha,
                                                       idllamada2,
                                                       estado,
                                                       cedula,
                                                       detalle,
                                                       fecha2,
                                                       fecha_carga,
                                                       fecha_instalacion,
                                                       departamento,
                                                       municipio,
                                                       regional,
                                                       contratista,
                                                       interfaz,
                                                       producto,
                                                       tipo_solicitud,
                                                       pregunta,
                                                       respuesta,
                                                       presente
                                                FROM nps
                                                where semana = :semana");
            $stmt->execute([':semana' => $semana]);

            if ($stmt->rowCount()) {
                $fp = fopen("../tmp/$filename", 'w');

                $columnas = [
                    'CAMPANAID',
                    'LANZAMIENTO',
                    'ID LLAMADA',
                    'TELEFONO',
                    'MENSAJE',
                    'ACCION',
                    'FECHA',
                    'IDLLAMADA',
                    'ESTADO',
                    'CEDULA',
                    'DETALLE',
                    'FECHA_2',
                    'FECHA_CARGA',
                    'FECHA_INSTALACION',
                    'DEPARTAMENTO',
                    'MUNICIPIO',
                    'REGION',
                    'CONTRATISTA_SSMM',
                    'INTERFAZ',
                    'PRODUCTO',
                    'TIPO_SOLICITUD',
                    'PREGUNTA',
                    'RESPUESTA',
                    'PRESENTE',
                ];

                fputcsv($fp, $columnas);
                fputcsv($fp, $stmt->fetchAll(PDO::FETCH_ASSOC));
                fclose($fp);

                $response = [$filename, $stmt->rowCount(), 201];

            } else {
                $response = ['No se encontraron datos', 400];
            }

        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        $this->_DB = null;
        echo json_encode($response);
    }

    public function buscarPedido($data)
    {
        try {

            /**
             * TODO
             * verificar metodo al parecer incompleto
             */

            $url = $data->url . $data->pedidos;

            $json = file_get_contents($url);
            $obj  = json_decode($json);

            var_dump($obj);

        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
    }

    public function buscarPedidoSegui($data)
    {
        try {
            $pedido   = $data['pedido'];
            $producto = $data['producto'];
            $remite   = $data['remite'];

            $stmt = $this->_DB->prepare("SELECT * FROM registros WHERE pedido = :pedido");
            $stmt->execute([':pedido' => $pedido]);

            if ($stmt->rowCount() || $remite == "Gestión contact center") {
                $sel = $this->_DB->prepare("SELECT *
                                                    FROM contingencias
                                                    WHERE acepta IS NOT NULL
                                                      AND aceptaPortafolio IS NOT NULL
                                                      AND pedido = :pedido");
                $sel->execute([':pedido' => $pedido]);

                if ($sel->rowCount()) {
                    $response = ['Aceptado o Rechazado', 201];
                } else {
                    $stmt = $this->_DB->prepare("SELECT * FROM contingencias
										WHERE acepta is null
										AND aceptaPortafolio IS NULL
										AND pedido = :pedido
										AND producto = :producto
										AND accion IN('Contingencia','Cambio de equipo','Refresh','Crear Espacio','crear cliente','Registros ToIP','mesaOffline', 'Cambio EID')");
                    $stmt->execute([':pedido' => $pedido, ':producto' => $producto]);

                    if ($stmt->rowCount()) {
                        $response = ['No se guarda', 400];
                    } else {
                        $response = ['Se guarda', 201];
                    }
                }

            } else {
                $response = ['No se encontraron registros', 400];
            }

        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        $this->_DB = null;
        echo json_encode($response);
    }

    public function csvRegistros($datos)
    {
        try {
            //session_start();
            //$usuarioid = $_SESSION['login'];
            $fechaini = $datos['fechaini'] ?? '';
            $fechafin = $datos['fechafin'] ?? '';
            $concepto = $datos['concepto'] ?? '';
            $buscar   = $datos['buscar'] ?? '';

            if ($fechaini == "" && $fechafin == "") {
                $fechaini = date("Y") . "-" . date("m") . "-" . date("d");
                $fechafin = date("Y") . "-" . date("m") . "-" . date("d");
            }

            if ($fechaini == $fechafin) {
                $filename = "Registros" . "_" . $fechaini . "_" . $concepto . "_" . $buscar . ".csv";
            } else {
                $filename = "Registros" . "_" . $fechaini . "_" . $fechafin . "_" . $concepto . "_" . $buscar . ".csv";
            }
            if ($concepto == "" || $buscar == "") {
                $parametros = "";
            } else {
                $parametros = "and $concepto = '$buscar'";
            }

            $stmt = $this->_DB->query("select a.pedido,
                                                       a.id_tecnico,
                                                       a.empresa,
                                                       a.asesor,
                                                       a.despacho,
                                                       replace(a.observaciones, ';', '') as observaciones,
                                                       a.accion,
                                                       a.tipo_pendiente,
                                                       a.fecha,
                                                       a.proceso,
                                                       a.producto,
                                                       a.duracion,
                                                       a.llamada_id,
                                                       a.prueba_integrada,
                                                       a.pruebaSmnet,
                                                       a.UNESourceSystem,
                                                       a.pendiente,
                                                       a.diagnostico
                                                from registros a
                                                where a.fecha between '$fechaini 00:00:00' and '$fechafin 23:59:59'
                                                  and a.asesor <> 'IVR'
                                                    $parametros");
            $stmt->execute();

            if ($stmt->rowCount()) {
                $result   = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $response = [$result, 201];
            } else {
                $response = ['No se encontraron datos', 400];
            }

        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        $this->_DB = null;
        echo json_encode($response);
    }

    public function expBrutal($datos)
    {
        try {

            $usuarioid = $_SESSION['login'];

            $fechas   = $datos['fechas'];
            $fechaini = $fechas['fechaini'];
            $fechafin = $fechas['fechafin'];
            $concepto = $datos['concepto'];
            $buscar   = $datos['buscar'];

            $filename = "Registros" . "_" . $fechaini . "_" . $fechafin . "_" . $usuarioid . ".csv";

            $stmt = $this->_DB->prepare("select PedidoDespacho,
                                                       AccionDespacho,
                                                       LoginDespacho,
                                                       CorreoDespacho,
                                                       FechaGestionDespacho,
                                                       ObservacionesDespacho,
                                                       CausaActividad,
                                                       estado,
                                                       fechaInicioGestion,
                                                       fechagestionAsesor,
                                                       SEC_TO_TIME((TIMESTAMPDIFF(MINUTE, fechaInicioGestion, fechagestionAsesor)) * 60) AS Tiempo_Gestion_Asesor,
                                                       Asesor,
                                                       ObservacionAsesor,
                                                       pedidoNuevo,
                                                       numeroOferta,
                                                       fechaclick,
                                                       numeroIncidente,
                                                       actividadRealizaGrupo,
                                                       estadoFinalPedido,
                                                       tipoTransaccion,
                                                       zona,
                                                       ObservacionesFinales,
                                                       canalVentas,
                                                       idLlamada
                                                from BrutalForce
                                                where FechaGestionDespacho between (:fechaini) and (:fechafin)");
            $stmt->execute([
                ':fechaini' => "$fechaini 00:00:00",
                ':fechafin' => "$fechafin 23:59:59",
            ]);

            if ($stmt->rowCount()) {
                $fp       = fopen("../tmp/$filename", 'w');
                $columnas = [
                    'PEDIDO',
                    'ACCION',
                    'DESPACHADOR',
                    'CORREO_DESPACHADOR',
                    'FECHA_DESPACHADOR',
                    'OBSERVACIONES_DESPACHADOR',
                    'CAUSA_ACTIVIDAD',
                    'ESTADO',
                    'FECHA_INICIO_GESTION_ASESOR',
                    'FECHA_FIN_GESTION_ASESOR',
                    'TIEMPO_GESTION_ASESOR',
                    'ASESOR',
                    'OBSERVACIONES_ASESOR',
                    'NUEVO_PEDIDO',
                    'NUMERO_OFERTA',
                    'FECHA_CLICK',
                    'NUMERO_INCIDENTE',
                    'ACTIVIDAD_RELIZA_GRUPO',
                    'ESTADO_FINAL_PEDIDO',
                    'TIPO_TRANSACCION',
                    'ZONA',
                    'OBSERVACIONES_FINALES',
                    'CANAL_VENTAS',
                    'ID_LLAMADA',
                ];

                fputcsv($fp, $columnas);
                while ($row = $stmt->fetchAll(PDO::FETCH_ASSOC)) {

                    $row['ObservacionesDespacho'] = str_replace(",", ".", $row['ObservacionesDespacho']);
                    $row['ObservacionAsesor']     = str_replace(",", ".", $row['ObservacionAsesor']);
                    $row['ObservacionesFinales']  = str_replace(",", ".", $row['ObservacionesFinales']);

                    $row['ObservacionesDespacho'] = str_replace(";", ".", $row['ObservacionesDespacho']);
                    $row['ObservacionAsesor']     = str_replace(";", ".", $row['ObservacionAsesor']);
                    $row['ObservacionesFinales']  = str_replace(";", ".", $row['ObservacionesFinales']);

                    $row['ObservacionesDespacho'] = str_replace("\n", " ", $row['ObservacionesDespacho']);
                    $row['ObservacionAsesor']     = str_replace("\n", " ", $row['ObservacionAsesor']);
                    $row['ObservacionesFinales']  = str_replace("\n", " ", $row['ObservacionesFinales']);

                    fputcsv($fp, $row);
                }

                fclose($fp);

                $response = [$filename, $stmt->rowCount(), 201];

            } else {
                $response = ['No se encotraron datos', 400];
            }

        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        $this->_DB = null;
        echo json_encode($response);
    }

    public function Csvtecnico($params)
    {
        try {
            session_start();

            $usuarioid = $_SESSION['login'];
            $datos     = $params['datos'] ?? '';
            $fechaini  = $datos['fechaini'] ?? '';
            $fechafin  = $datos['fechafin'] ?? '';

            if ($fechaini == "" && $fechafin == "") {
                $fechaini = date("Y") . "-" . date("m") . "-" . date("d");
                $fechafin = date("Y") . "-" . date("m") . "-" . date("d");
            }

            if ($fechaini == $fechafin) {
                $filename = "Cambio_Equipos" . "_" . $fechaini . ".csv";
            } else {
                $filename = "Cambio_Equipos" . "_" . $fechaini . "_" . $fechafin . "_" . $usuarioid . ".csv";
            }

            $query = "SELECT a.pedido AS PEDIDO, a.id_tecnico AS TECNICO, 
            t.nombre AS 'NOMBRE TECNICO',
            t.ciudad AS 'CIUDAD',
            a.empresa AS EMPRESA, 
            a.tipo_pendiente AS 'TIPO PENDIENTE', 
            a.accion AS 'ACCION', 
            DAY(a.fecha) AS DIA, 
            MONTH(a.fecha) AS MES, 
            YEAR(a.fecha) AS ANO, 
            a.producto AS PRODUCTO, 
            a.plantilla AS PLANTILLA,
            ce.hfc_equipo_sale AS MACSALE,
            ce.hfc_equipo_entra AS MACENTRA,
            a.proceso AS PROCESO
            FROM registros a 
            LEFT JOIN cambio_equipos ce ON ce.pedido = a.pedido
            LEFT JOIN tecnicos t ON t.identificacion = a.id_tecnico
            WHERE a.fecha BETWEEN '$fechaini 00:00:00' AND '$fechafin 23:59:59' AND (a.tipo_pendiente='Cambio de Equipo' OR a.accion='Cambio Equipo' OR a.tipo_pendiente='') AND a.proceso IN( 'Reparaciones', 'Instalaciones') AND (ce.hfc_equipo_sale IS NOT NULL OR ce.hfc_equipo_entra IS NOT NULL)
            GROUP BY a.pedido , a.id_tecnico , t.nombre , t.ciudad , a.empresa , a.tipo_pendiente , a.accion , DIA, MES, ANO, a.producto , a.plantilla , ce.hfc_equipo_sale , ce.hfc_equipo_entra , a.proceso;";

            // echo "$query\n";

            $stmt = $this->_DB->query($query);
            $stmt->execute();

            /*$columnas = [
                'PEDIDO',
                'TECNICO',
                'NOMBRE_TECNICO',
                'CIUDAD',
                'EMPRESA',
                'TIPO_PENDIENTE',
                'DIA',
                'MES',
                'ANO',
                'PRODUCTO',
                //'PLANTILLA',
                'MOTIVO',
                'MAC_SALE',
                'MAC_ENTRA',
                'PROCESO',
            ];*/


            if ($stmt->rowCount() > 0) {
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                //////////////////Si existen datos que cumplen las condiciones
                /*foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $obj) {
                    //while ($obj = $stmt->fetchAll(PDO::FETCH_ASSOC)) {
                    //////////////////Ciclo para cada pedido encontrado
                    //var_dump($obj);exit();


                    $pedido = $obj['PEDIDO'];

                    $tecnico        = $obj['TECNICO'];
                    $nombre_tecnico = $obj['NOMBRE TECNICO'];
                    $ciudad         = $obj['CIUDAD'];
                    $empresa        = $obj['EMPRESA'];
                    $tipo_pendiente = $obj['TIPO PENDIENTE'];

                    $dia       = $obj['DIA'];
                    $mes       = $obj['MES'];
                    $ano       = $obj['ANO'];
                    $producto  = $obj['PRODUCTO'];
                    $plantilla = $obj['PLANTILLA'];

                    $sep = ",";

                    $plantilla2 = str_replace("*", ",", $plantilla);

                    $pieces = explode($sep, $plantilla2);

                    $size = count($pieces);

                    $MOTIVO = "";
                    /* $MAC_SALE = "";
                    $MARCA_SALE = "";
                    $REFERENCIA_SALE = "";

                    $MAC_SALE  = $obj['MACSALE'];
                    $MAC_ENTRA = $obj['MACENTRA'];
                    $PROCESO   = $obj['PROCESO'];

                    for ($i = 0; $i < $size; $i++) {
                        //MOTIVO
                        $bool = stripos($pieces[$i], 'Motivo');
                        if ($bool === false) {
                        } else {
                            $tmp    = explode(":", $pieces[$i]);
                            $MOTIVO = trim(strtoupper($tmp[1]));
                            continue;
                        }

                        //MAC SALE
                        $bool = stripos($pieces[$i], 'Mac');
                        if ($bool === false) {
                        } else {
                            //echo "DETECTO MAC!!! ".$pieces[$i]." - - ";
                            $bool = stripos($pieces[$i], 'Sale');
                            if ($bool === false) {
                            } else {
                                $tmp = explode(":", $pieces[$i]);

                                $tmp[1] = str_replace(" ", "", $tmp[1]);

                                if ($tmp[1] == "") {
                                    continue;
                                }

                                $MAC_SALE = trim(strtoupper($tmp[1]));
                                continue;
                            }
                        }

                        //CHIP SALE
                        $bool = stripos($pieces[$i], 'chip');
                        if ($bool === false) {
                        } else {
                            //echo "DETECTO MAC!!! ".$pieces[$i]." - - ";
                            $bool = stripos($pieces[$i], 'Sale');
                            if ($bool === false) {
                            } else {
                                $tmp = explode(":", $pieces[$i]);

                                $tmp[1] = str_replace(" ", "", $tmp[1]);

                                if ($tmp[1] == "") {
                                    continue;
                                }

                                $MAC_SALE = trim(strtoupper($tmp[1]));
                                continue;
                            }
                        }

                        //MARCA SALE
                        $bool = stripos($pieces[$i], 'Marca');
                        if ($bool === false) {
                        } else {

                            $bool = stripos($pieces[$i], 'Sale');
                            if ($bool === false) {
                            } else {
                                $tmp    = explode(":", $pieces[$i]);
                                $tmp[1] = str_replace(" ", "", $tmp[1]);

                                if ($tmp[1] == "") {
                                    continue;
                                }

                                $MARCA_SALE = trim(strtoupper($tmp[1]));
                                continue;
                            }
                        }

                        //REFERENCIA SALE
                        $bool = stripos($pieces[$i], 'Referencia');
                        if ($bool === false) {
                        } else {

                            $bool = stripos($pieces[$i], 'Sale');
                            if ($bool === false) {
                            } else {
                                $tmp    = explode(":", $pieces[$i]);
                                $tmp[1] = str_replace(" ", "", $tmp[1]);

                                if ($tmp[1] == "") {
                                    continue;
                                }

                                $REFERENCIA_SALE = trim(strtoupper($tmp[1]));
                                continue;
                            }
                        }
                        //echo " $pedido,$tecnico,$nombre_tecnico,$ciudad,$empresa,$tipo_pendiente,$dia,$mes,$ano,$producto,$MOTIVO, $MAC_SALE,$MARCA_SALE,$REFERENCIA_SALE \n";

                    } //END FOR
                    //echo " $pedido,$tecnico,$nombre_tecnico,$ciudad,$empresa,$tipo_pendiente,$dia,$mes,$ano,$producto,$MOTIVO, $MAC_SALE,$MARCA_SALE,$REFERENCIA_SALE \n";

                    /* fputcsv($fp, array($pedido, $tecnico, $nombre_tecnico, $ciudad, $empresa, $tipo_pendiente, $dia, $mes, $ano, $producto, $MOTIVO, $MAC_SALE, $MARCA_SALE, $REFERENCIA_SALE)); */
                //fputcsv($fp, [$pedido, $tecnico, $nombre_tecnico, $ciudad, $empresa, $tipo_pendiente, $dia, $mes, $ano, $producto, $MOTIVO, $MAC_SALE, $MAC_ENTRA, $PROCESO]);
                //$response = [$pedido, $tecnico, $nombre_tecnico, $ciudad, $empresa, $tipo_pendiente, $dia, $mes, $ano, $producto, $MOTIVO, $MAC_SALE, $MAC_ENTRA, $PROCESO];
                /*
                                } //ciclo grande*/
                $response = [$result];
            } else {
                $response = [];
            }


            //fclose($fp);

        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }

        $this->_DB = null;
        echo json_encode($response);
    }

    public
    function diferenciasClick(
        $fecha
    ) {
        try {
            $fechaanterior = date('Y-m-d', strtotime('-1 day', strtotime($fecha)));

            $query = "TRUNCATE TABLE view_diferencias_Click";

            $stmt = $this->_DB->query($query);
            $stmt->execute();

            $stmt = $this->_DB->query("INSERT INTO view_diferencias_Click
                                                    (`razon`)
                                                VALUES ('Pendiente por un producto'),
                                                       ('No tiene Ordenes de trabajo'),
                                                       ('Sin novedades'),
                                                       ('FENIX BOGOTA'),
                                                       ('Pedido anulado o pendiente por producto'),
                                                       ('Inconcistencia en componente - Fenix')");

            $stmt->execute();

            $carga = $this->_DB->prepare("select pro.razon,
                                                       (select count(razon)
                                                        from seguimientoClick
                                                        where fecha_cita BETWEEN (:fechaini) AND (:fechafin)
                                                          and razon = pro.razon) total_actual,
                                                       (select count(razon)
                                                        from seguimientoClick
                                                        where fecha_cita BETWEEN (:fechaanteriror) AND (:fechaanteriorfin)
                                                          and razon = pro.razon) total_anterior
                                                from seguimientoClick pro
                                                group by pro.razon ");
            $carga->execute([
                ':fechaini'         => "$fecha 00:00:00",
                ':fechafin'         => "$fecha 23:59:59",
                ':fechaanteriror'   => "$fechaanterior 00:00:00",
                ':fechaanteriorfin' => "$fechaanterior 23:59:59",
            ]);

            if ($carga->rowCount()) {

                while ($row = $carga->fetchAll(PDO::FETCH_ASSOC)) {

                    $totalrazon_actual   = $row['total_actual'];
                    $totalrazon_anterior = $row['total_anterior'];
                    $razon               = $row['razon'];
                    $total_actual        = $total_actual + $totalrazon_actual;
                    $total_anterior      = $total_anterior + $totalrazon_anterior;

                    $update = $this->_DB->prepare("UPDATE view_diferencias_Click
                                                            SET `total_actual`='$totalrazon_actual',
                                                                `total_anterior`='$totalrazon_anterior'
                                                            WHERE `razon` = '$razon'");
                    $update->execute([
                        ':totalrazon_actual'   => $totalrazon_actual,
                        ':totalrazon_anterior' => $totalrazon_anterior,
                        ':razon'               => $razon,
                    ]);

                }

                $stmt = $this->_DB->query("SELECT * FROM view_diferencias_Click");
                $stmt->execute();

                if ($stmt->rowCount()) {

                    $result   = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $response = [$result, $total_actual, $total_anterior, 201];

                } else {
                    $response = ['Ah ocurrido un error intentalo nuevamente', 201];
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

    public
    function observacionAsesor(
        $pedido
    ) {
        try {

            $query = "SELECT ObservacionAsesor FROM BrutalForce where PedidoDespacho = :pedido";
            $stmt  = $this->_DB->prepare($query);
            $stmt->execute([':pedido' => $pedido]);

            if ($stmt->rowCount()) {
                $result   = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $response = [$result, 201];
            } else {
                $response = ['No se encontraron datos', 400];
            }
        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        $this->_DB = null;
        echo json_encode($response);
    }

    public
    function contadorpedientesBF()
    {
        try {
            $stmt = $this->_DB->query("SELECT (SELECT COUNT(PedidoDespacho)
                                                   FROM BrutalForce
                                                   WHERE pedidobloqueado IS NULL
                                                     AND acciondespacho IN ('Renumerar', 'One-Tv', 'B2B', 'Consulta de la oferta')
                                                     AND gestionasesor = '1') +
                                                  (SELECT COUNT(PedidoDespacho) FROM BrutalForce WHERE pedidobloqueado = '1' AND gestionAsesor = '1') AS Pendientes");
            $stmt->execute();

            if ($stmt->rowCount()) {
                $result   = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $response = [$result, 201];
            } else {
                $response = ['No se encontraron datos', 400];
            }

        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        $this->_DB = null;
        echo json_encode($response);
    }

    public
    function seguimientoClick(
        $fecha
    ) {
        try {
            $mes  = date("m", strtotime($fecha));
            $anio = date("Y", strtotime($fecha));

            $query = "TRUNCATE TABLE seguimiento_Click_resumen";
            $stmt  = $this->_DB->query($query);
            $stmt->execute();

            //insert jornadaID

            $stmt = $this->_DB->prepare("select distinct fecha_cita
                                                from seguimientoClick
                                                where fecha_cita BETWEEN
                                                (:fechaini) AND (:fechafin)");
            $stmt->execute([':fechaini' => "$anio-$mes-01 00:00:00", ':fechafin' => "$anio-$mes-31 23:59:59"]);

            if ($stmt->rowCount()) {
                while ($row = $stmt->fetchAll(PDO::FETCH_ASSOC)) {

                    $fecha_cita = $row['fecha_cita'];

                    $this->_DB->beginTransaction();

                    $query = "INSERT INTO seguimiento_Click_resumen (`fecha`) VALUES (:fecha_cita)";
                    $stmt  = $this->_DB->prepare($query);
                    $stmt->execute([':fecha_cita' => $fecha_cita]);

                    //Se visita y queda atendido
                    $sqltotal = $this->_DB->prepare("select count(estado_final) total
                                                            from seguimientoClick
                                                            where fecha_cita BETWEEN (:fechaini) AND (:fechafin)
                                                              and estado_final = :msj");
                    $sqltotal->execute([':fechaini' => "$fecha_cita 00:00:00", ':fechafin' => "$fecha_cita 23:59:59", ':msj' => "Se visita y queda atendido"]);


                    if ($sqltotal->rowCount()) {
                        $result = $sqltotal->fetchAll(PDO::FETCH_ASSOC);
                        $total  = $result['total'];
                    } else {
                        echo 'Error';
                        exit();
                    }

                    $update = $this->_DB->prepare("UPDATE seguimiento_Click_resumen
                                                            SET `visita_atendido` = :total
                                                            WHERE `fecha` = :fecha_cita");
                    $update->execute([':total' => $total, ':fecha_cita' => $fecha_cita]);

                    //Sube a Click pero no se visita
                    $stmt = $this->_DB->prepare("select count(estado_final) total
                                                        from seguimientoClick
                                                        where fecha_cita BETWEEN (:fechaini) AND (:fechafin)
                                                          and estado_final = :msj");
                    $stmt->execute([':fechaini' => "$fecha_cita 00:00:00", ':fechafin' => "$fecha_cita 23:59:59", ':msj' => "Sube a Click pero no se visita"]);


                    if ($stmt->rowCount()) {
                        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        $total  = $result['total'];
                    } else {
                        echo 'Error';
                        exit();
                    }

                    $sqlupdate = $this->_DB->prepare("UPDATE seguimiento_Click_resumen
                                                            SET `sube_no_visita` = :total
                                                            WHERE `fecha` = :fecha");
                    $sqlupdate->execute([':total' => $total, ':fecha' => $fecha_cita]);

                    //no sube
                    $stmt = $this->_DB->prepare("select count(estado_final) total
                                                        from seguimientoClick
                                                        where fecha_cita BETWEEN (:fechaini) AND (:fechafin)
                                                          and estado_final = :msj");
                    $stmt->execute([':fechaini' => "$fecha_cita 00:00:00", ':fechafin' => "$fecha_cita 23:59:59", ':msj' => "No subieron a click"]);

                    if ($stmt->rowCount()) {
                        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        $total  = $result['total'];
                    } else {
                        echo 'Error';
                        exit();
                    }

                    $update = $this->_DB->prepare("UPDATE seguimiento_Click_resumen
                                                            SET `no_sube` = :total
                                                            WHERE `fecha` = :fecha");
                    $update->execute([':total' => $total, ':fecha' => $fecha_cita]);

                    //reagendado
                    $stmt = $this->_DB->prepare("select count(estado_final) total
                                                        from seguimientoClick
                                                        where fecha_cita BETWEEN (:fechaini) AND (:fechafin)
                                                          and estado_final = :msj");
                    $stmt->execute([
                        ':fechaini' => "$fecha_cita 00:00:00",
                        ':fechafin' => "$fecha_cita 23:59:59",
                        ':msj'      => "Reagendado",
                    ]);

                    if ($stmt->rowCount()) {
                        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        $total  = $result['total'];
                    } else {
                        echo 'Error';
                        exit();
                    }

                    $update = $this->_DB->prepare("UPDATE seguimiento_Click_resumen
                                                            SET `reagendado` = :total
                                                            WHERE `fecha` = :fecha_cita");
                    $update->execute([':total' => $total, ':fecha' => $fecha_cita]);


                    //mal_agendado_no_sube
                    $stmt = $this->_DB->prepare("select count(estado_final) total
                                                        from seguimientoClick
                                                        where fecha_cita BETWEEN (:fechaini) AND (:fechafin)
                                                          and estado_final = :msj");

                    $stmt->execute([
                        ':fechaini' => "$fecha_cita 00:00:00",
                        ':fechafin' => "$fecha_cita 23:59:59",
                        ':msj'      => "Pedido mal agendado, no sube a Click",
                    ]);

                    if ($stmt->rowCount()) {
                        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        $total  = $result['total'];
                    } else {
                        echo 'Error';
                        exit();
                    }

                    $update = $this->_DB->prepare("UPDATE seguimiento_Click_resumen
                                                        SET `mal_agendado_no_sube` = :total
                                                        WHERE `fecha` = :fecha_cita");
                    $update->execute([':total' => $total, ':fecha' => $fecha_cita]);

                    //Se visito pero quedo incompleto
                    $stmt = $this->_DB->prepare("select count(estado_final) total
                                                        from seguimientoClick
                                                        where fecha_cita BETWEEN (:fechaini) AND (:fechafin)
                                                          and estado_final = :msj");

                    $stmt->execute([
                        ':fechaini' => "$fecha_cita 00:00:00",
                        ':fechafin' => "$fecha_cita 23:59:59",
                        ':msj'      => "Se visito pero quedo incompleto",
                    ]);

                    if ($stmt->rowCount()) {
                        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        $total  = $result['total'];
                    } else {
                        echo 'Error';
                        exit();
                    }

                    $update = $this->_DB->prepare("UPDATE seguimiento_Click_resumen
                                                        SET `visita_incompleto` = :total
                                                        WHERE `fecha` = :fecha_cita");
                    $update->execute([':total' => $total, ':fecha' => $fecha_cita]);

                    //No se visita pero se cambia estado
                    $stmt = $this->_DB->prepare("select count(estado_final) total
                                                        from seguimientoClick
                                                        where fecha_cita BETWEEN (:fechaini) AND (:fechafin)
                                                          and estado_final = :msj");

                    $stmt->execute([
                        ':fechaini' => "$fecha_cita 00:00:00",
                        ':fechafin' => "$fecha_cita 23:59:59",
                        ':msj'      => "No se visita pero se cambia estado",
                    ]);

                    if ($stmt->rowCount()) {
                        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        $total  = $result['total'];
                    } else {
                        echo 'Error';
                        exit();
                    }

                    $update = $this->_DB->prepare("UPDATE seguimiento_Click_resumen
                                                        SET `no_visita_cambia_estado` = :total
                                                        WHERE `fecha` = :fecha_cita");
                    $update->execute([':total' => $total, ':fecha' => $fecha_cita]);

                    //Visita cancelada
                    $stmt = $this->_DB->prepare("select count(estado_final) total
                                                        from seguimientoClick
                                                        where fecha_cita BETWEEN (:fechaini) AND (:fechafin)
                                                          and estado_final = :msj");

                    $stmt->execute([
                        ':fechaini' => "$fecha_cita 00:00:00",
                        ':fechafin' => "$fecha_cita 23:59:59",
                        ':msj'      => "Visita cancelada",
                    ]);

                    if ($stmt->rowCount()) {
                        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        $total  = $result['total'];
                    } else {
                        echo 'Error';
                        exit();
                    }

                    $update = $this->_DB->prepare("UPDATE seguimiento_Click_resumen
                                                        SET `cancelada` = :total
                                                        WHERE `fecha` = :fecha_cita");
                    $update->execute([':total' => $total, ':fecha' => $fecha_cita]);

                }

                $stmt = $this->_DB->prepare("SELECT fecha,
                                                           visita_atendido,
                                                           sube_no_visita,
                                                           no_sube,
                                                           reagendado,
                                                           mal_agendado_no_sube,
                                                           visita_incompleto,
                                                           no_visita_cambia_estado,
                                                           cancelada
                                                    FROM seguimiento_Click_resumen
                                                    where fecha BETWEEN (:fechaini) AND (:fechafin)");
                $stmt->execute([':fechaini' => "$anio-$mes-01 00:00:00", ':fechafin' => "$anio-$mes-31 23:59:59"]);
                if ($stmt->rowCount()) {
                    $categorias              = [];
                    $visita_atendido         = [];
                    $sube_no_visita          = [];
                    $no_sube                 = [];
                    $reagendado              = [];
                    $mal_agendado_no_sube    = [];
                    $visita_incompleto       = [];
                    $no_visita_cambia_estado = [];
                    $cancelada               = [];
                    $i                       = 1;
                    while ($row = $stmt->fetchAll(PDO::FETCH_ASSOC)) {

                        $cate                 = $row['fecha'];
                        $visita_aten          = $row['visita_atendido'];
                        $sube_no_vis          = $row['sube_no_visita'];
                        $no_su                = $row['no_sube'];
                        $reage                = $row['reagendado'];
                        $mal_agendado_no_su   = $row['mal_agendado_no_sube'];
                        $visita_incom         = $row['visita_incompleto'];
                        $no_visita_cambia_est = $row['no_visita_cambia_estado'];
                        $cancel               = $row['cancelada'];

                        $categorias[]              = ["label" => "$cate"];
                        $visita_atendido[]         = ["value" => "$visita_aten"];
                        $sube_no_visita[]          = ["value" => "$sube_no_vis"];
                        $no_sube[]                 = ["value" => "$no_su"];
                        $reagendado[]              = ["value" => "$reage"];
                        $mal_agendado_no_sube[]    = ["value" => "$mal_agendado_no_su"];
                        $visita_incompleto[]       = ["value" => "$visita_incom"];
                        $no_visita_cambia_estado[] = ["value" => "$no_visita_cambia_est"];
                        $cancelada[]               = ["value" => "$cancel"];
                        $i++;
                    }
                    $response = [
                        $categorias,
                        $visita_atendido,
                        $sube_no_visita,
                        $no_sube,
                        $reagendado,
                        $mal_agendado_no_sube,
                        $visita_incompleto,
                        $no_visita_cambia_estado,
                        $cancelada,
                        201,
                    ];
                } else {
                    $response = ['No se econtraron datos', 400];
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

    public
    function registrosComercial(
        $data
    ) {
        try {
            $pagina   = $data['page'];
            $concepto = $data['concepto'];
            $dato     = $data['dato'];
            $inicial  = $data['inicial'];
            $final    = $data['final'];

            if ($pagina == "undefined") {
                $pagina = "0";
            } else {
                $pagina = $pagina - 1;
            }

            $pagina = $pagina * 100;

            if ($concepto == 'Pedido') {
                $parametro = "and a.pedido_actual = '$dato'";
            }
            if ($concepto == 'Asesor') {
                $parametro = " and a.login_asesor = '$dato'";
            }
            if ($concepto == 'Gestion') {
                $parametro = " and a.gestion = '$dato'";
            }
            if ($concepto == 'Clasificacion') {
                $parametro = " and a.clasificacion = '$dato'";
            }
            if ($concepto == 'Ciudad') {
                $parametro = " and a.ciudad = '$dato'";
            }
            if ($concepto == 'Estado') {
                $parametro = " and a.estado = '$dato'";
            };

            $query = "SELECT a.ID,
                       a.LOGIN_ASESOR,
                       a.PEDIDO_ACTUAL,
                       a.PEDIDO_NUEVO,
                       a.CIUDAD,
                       a.GESTION,
                       a.CLASIFICACION,
                       a.ESTADO,
                       a.OBSERVACIONES,
                       a.FECHA_CARGA
                FROM registros_comercial a
                where 1 = 1 $parametro 
                                      and a.FECHA_CARGA BETWEEN ('$inicial 00:00:00') AND ('$final 23:59:59')
                order by a.FECHA_CARGA
                DESC
                    limit 100 offset $pagina";

            $stmt = $this->_DB->query($query);
            $stmt->execute();

            if ($stmt->rowCount()) {
                $result   = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $response = [$result, $stmt->rowCount(), 201];
            } else {
                $response = ['No se encontraron datos'];
            }
        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        $this->_DB = null;
        echo json_encode($response);
    }
}
