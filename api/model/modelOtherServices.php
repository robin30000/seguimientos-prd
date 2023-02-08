<?php
require_once '../class/conection.php';
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);

class modelOtherServices
{
    private $_DB;

    public function __construct()
    {
        $this->_DB = new Conection();
    }

    public function insertarCambioEquipo($data)
    {
        try {

            $datos      = $data['datoscambio'];
            $tecnologia = $data['tecnologia'];
            $pedido     = $data['pedido'];
            //HFC-DTH
            $cuentaDomiciliaria = $datos['cuenta'];
            $IdCuenta           = $datos['IdCuenta'];
            //todos
            $motivo = $datos['motivoCambio'];
            //DTH
            $chipSale   = $datos['chipSale'];
            $chipEntra  = $datos['chipEntra'];
            $SmartEntra = $datos['SmartEntra'];
            $SmartSale  = $datos['SmartSale'];
            //HFC
            $servicioHFC    = $datos['servicio'];
            $equipoEntraHFC = $datos['equipoEntra'];
            $macEntraHFC    = $datos['macEntra'];
            $equipoSaleHFC  = $datos['equipoSale'];
            $macSaleHFC     = $datos['macSale'];
            //ADSL
            $Serialsale  = $datos['Serialsale'];
            $Serialentra = $datos['Serialentra'];
            $Marcasale   = $datos['Marcasale'];
            $Marcaentra  = $datos['Marcaentra'];
            $Refsale     = $datos['Refsale'];
            $Refentra    = $datos['Refentra'];

            //    echo "pedido: ".$pedido;

            if ($tecnologia == "DTH") {
                $stmt = $this->_DB->prepare("INSERT INTO cambio_equipos
                                                    (pedido, cuenta_domiciliaria, id_cuenta, motivo_cambio, tecnologia, dth_chip_sale,
                                                     dth_chip_entra, dth_smartcard_sale, dth_smartcard_entra)
                                                    values (:pedido, :cuentaDomiciliaria, :IdCuenta, :motivo, :tecnologia, :chipSale,
                                                            :chipEntra, :SmartSale, :SmartEntra)");
                $stmt->execute([
                    ':pedido'             => $pedido,
                    ':cuentaDomiciliaria' => $cuentaDomiciliaria,
                    ':IdCuenta'           => $IdCuenta,
                    ':motivo'             => $motivo,
                    ':tecnologia'         => $tecnologia,
                    ':chipSale'           => $chipSale,
                    ':chipEntra'          => $chipEntra,
                    ':SmartSale'          => $SmartSale,
                    ':SmartEntra'         => $SmartEntra,

                ]);
                if ($stmt->rowCount() == 1) {
                    $response = ['Datos Guardados', 201];
                } else {
                    $response = ['Ah ocurrido un error intentalo nuevamenete', 400];
                }
            } elseif ($tecnologia == "HFC") {
                $stmt = $this->_DB->prepare("INSERT INTO cambio_equipos (pedido, cuenta_domiciliaria, id_cuenta, tipo_servicio, motivo_cambio, tecnologia, hfc_equipo_sale,
                                                        hfc_equipo_entra, hfc_mac_voz_entra, hfc_mac_voz_sale)
                                                    values (:pedido, :cuentaDomiciliaria, :IdCuenta, :servicioHFC, :motivo, :tecnologia, :equipoSaleHFC,
                                                            :equipoEntraHFC, :macEntraHFC, :macSaleHFC)");

                $stmt->execute([
                    ':pedido'             => $pedido,
                    ':cuentaDomiciliaria' => $cuentaDomiciliaria,
                    ':IdCuenta'           => $IdCuenta,
                    ':servicioHFC'        => $servicioHFC,
                    ':motivo'             => $motivo,
                    ':tecnologia'         => $tecnologia,
                    ':equipoSaleHFC'      => $equipoSaleHFC,
                    ':equipoEntraHFC'     => $equipoEntraHFC,
                    ':macEntraHFC'        => $macEntraHFC,
                    ':macSaleHFC'         => $macSaleHFC,
                ]);

                if ($stmt->rowCount() == 1) {
                    $response = ['Datos Guardados', 201];
                } else {
                    $response = ['Ah ocurrido un error intentalo nuevamenete', 400];
                }

            } elseif ($tecnologia == "ADSL") {

                $stmt = $this->_DB->prepare("INSERT INTO cambio_equipos (pedido, motivo_cambio, tecnologia, adsl_serial_sale,
                                                        adsl_serial_entra, adsl_marca_sale, adsl_marca_entra, adsl_ref_entra, adsl_ref_sale)
                                                    values (:pedido, :motivo, :tecnologia, :Serialsale,
                                                            :Serialentra, :Marcaentra, :Marcasale, :Refentra, :Refsale)");

                $stmt->execute([
                    ':pedido'      => $pedido,
                    ':motivo'      => $motivo,
                    ':tecnologia'  => $tecnologia,
                    ':Serialsale'  => $Serialsale,
                    ':Serialentra' => $Serialentra,
                    ':Marcaentra'  => $Marcaentra,
                    ':Marcasale'   => $Marcasale,
                    ':Refentra'    => $Refentra,
                    ':Refsale'     => $Refsale,
                ]);

                if ($stmt->rowCount() == 1) {
                    $response = ['Datos Guardados', 201];
                } else {
                    $response = ['Ah ocurrido un error intentalo nuevamenete', 400];
                }

            }

            $lastInsert = $this->_DB->lastInsertId();
            $response   = $response['lastInsert'] = $lastInsert;
        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        $this->_DB = null;
        echo json_encode($response);
    }

    public function GuardarPedidoEncuesta($params)
    {
        try {
            session_start();
            $info_encuesta = $params['infoPedidoEncuesta'];
            $info_gestion  = $params['gestionDolores'];
            $duracion      = $params['counter'];
            $fechaInicial  = $params['fechaInicial'];
            $fechaFinal    = $params['fechaFinal'];
            $login         = $_SESSION['login'];

            $codigo        = utf8_decode($info_gestion['codigo']);
            $resultado     = utf8_decode($info_gestion['resultado']);
            $intentos      = $info_gestion['intentos'];
            $observaciones = utf8_decode($info_gestion['observaciones']);

            $telefono          = $info_encuesta['telefono'];
            $cedula            = $info_encuesta['cedula'];
            $detalle           = $info_encuesta['detalle'];
            $fecha_instalacion = $info_encuesta['fecha_instalacion'];
            $departamento      = utf8_decode($info_encuesta['departamento']);
            $municipio         = utf8_decode($info_encuesta['municipio']);
            $contratista       = $info_encuesta['contratista'];
            $Interfaz          = $info_encuesta['Interfaz'];
            $semana            = $info_encuesta['semana'];

            if ($resultado == "ERROR SELECCIÓN DE RESPUESTA" || $resultado == "CLIENTE NO BRINDA INFORMACIÓN") {
                $agrupador = "Error selección rspuesta";
            } elseif ($resultado == "DAÑOS LUEGO INSTALACIÓN" || $resultado == "INCUMPLIMIENTO AGENDA" || $resultado == "TECNICO NO DA INFORMACIÓN" || $resultado == "DESORDEN EN SITIO") {
                $agrupador = "Ejecución en campo";
            } elseif ($resultado == "OFERTA DIFERENTE" || $resultado == "MALA ASESORIA" || $resultado == "MAL AGENDAMIENTO") {
                $agrupador = "Vendedor";
            }

            $agrupador = utf8_decode($agrupador);

            $stmt = $this->_DB->prepare("INSERT INTO doloresClientes (pedido, cedula, telefono, fecha_instalacion,
                                                 departamento, municipio, contratista, Interfaz, observaciones, codigo, resultado,
                                                 fecha_inicio_contacto, fecha_fin_contacto, duracion, usuario, semana, agrupador)
                                                values (:detalle, :cedula, :telefono, :fecha_instalacion, 
                                                        :departamento, :municipio, :contratista, :Interfaz, :observaciones, :codigo, 
                                                        :resultado, :fechaInicial, :fechaFinal, :duracion, :login, :semana, :agrupador)");
            $stmt->execute([
                ':$detalle'           => $detalle,
                ':$cedula'            => $cedula,
                ':$telefono'          => $telefono,
                ':$fecha_instalacion' => $fecha_instalacion,
                ':$departamento'      => $departamento,
                ':$municipio'         => $municipio,
                ':$contratista'       => $contratista,
                ':$Interfaz'          => $Interfaz,
                ':$observaciones'     => $observaciones,
                ':$codigo'            => $codigo,
                ':$resultado'         => $resultado,
                ':$fechaInicial'      => $fechaInicial,
                ':$fechaFinal'        => $fechaFinal,
                ':$duracion'          => $duracion,
                ':$login'             => $login,
                ':$semana'            => $semana,
                ':$agrupador'         => $agrupador,
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

    public function gestiodespachoBrutal($data)
    {
        try {
            $infogestion   = $data['datosguardar'];
            $pedido        = $infogestion['pedido'];
            $accion        = utf8_decode($infogestion['accion']);
            $correo        = $infogestion['correo'];
            $observaciones = utf8_decode($infogestion['observaciones']);
            $cedula        = $infogestion['cedula'];
            $tecnico       = utf8_decode($infogestion['tecnico']);
            $celular       = $infogestion['celular'];
            $zona          = utf8_decode($infogestion['zona']);
            $idLlamada     = $infogestion['idLlamada'];
            $supervisor    = utf8_decode($infogestion['supervisor']);
            $tipoTrans     = utf8_decode($infogestion['tipoTrans']);
            $numSAPEIni    = utf8_decode($infogestion['numSAPEIni']);
            $numSAPEFin    = utf8_decode($infogestion['numSAPEFin']);
            $prioridad     = $infogestion['prioridad'];

            if ($infogestion['accion'] !== "Gestión AAA") {
                $numSAPEIni = "";
                $numSAPEFin = "";
            }

            $login = $_SESSION['login'];

            $stmt = $this->_DB->prepare("select PedidoDespacho from BrutalForce where PedidoDespacho = :pedido");
            $stmt->execute([':pedido' => $pedido]);

            if (!$stmt->rowCount()) {
                $response = ['No se encontraron datos', 201];
            } else {
                if ($login == 'lmontcre') {
                    $accion = $accion . "__Valle";
                    $stmt   = $this->_DB->prepare("INSERT INTO BrutalForce(PedidoDespacho, AccionDespacho, CorreoDespacho, ObservacionesDespacho,
                                                            LoginDespacho, gestionAsesor, zona, tipoTransaccion, idLlamada, supervisor,
                                                            celular, tecnico, cedula, numSAPEIni, numSAPEFin, prioridad)
                                                        values (:pedido, :accion, :correo, :observaciones, :login, '1',
                                                                :zona, :tipoTrans, :idLlamada, :supervisor, :celular,
                                                                :tecnico, :cedula, :numSAPEIni, :numSAPEFin, :prioridad)");
                    $stmt->execute([
                        ':pedido'        => $pedido,
                        ':accion'        => $accion,
                        ':correo'        => $correo,
                        ':observaciones' => $observaciones,
                        ':login'         => $login,
                        ':zona'          => $zona,
                        ':tipoTrans'     => $tipoTrans,
                        ':idLlamada'     => $idLlamada,
                        ':supervisor'    => $supervisor,
                        ':celular'       => $celular,
                        ':tecnico'       => $tecnico,
                        ':cedula'        => $cedula,
                        ':numSAPEIni'    => $numSAPEIni,
                        ':numSAPEFin'    => $numSAPEFin,
                        ':prioridad'     => $prioridad,
                    ]);
                    if ($stmt->rowCount() == 1) {
                        $response = ['Datos guardados correctamente', 201];
                    } else {
                        $response = ['Ah ocurrido un error intentalo nuevamente', 400];
                    }
                }
            }


        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        $this->_DB = null;
        echo json_encode($response);
    }

    public function gestionFinal()
    {
        try {

            $stmt = $this->_DB->query("SELECT PedidoDespacho,
                                                       AccionDespacho,
                                                       CorreoDespacho,
                                                       Asesor,
                                                       ObservacionesDespacho,
                                                       FechaGestionDespacho,
                                                       tipoTransaccion,
                                                       zona,
                                                       LoginDespacho,
                                                       idLlamada,
                                                       supervisor
                                                from BrutalForce
                                                where gestionAsesor = '1'
                                                order by fechaInicioGestion DESC");
            $stmt->execute();

            if ($stmt->rowCount()) {
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $result = '';
            }

            $hora = date("H");

            if ($hora >= 10) {
                $hoy            = date("Y-m-d");
                $nuevafecha     = strtotime('-1 day', strtotime($hoy));
                $nuevafecha     = date('Y-m-j', $nuevafecha);
                $horaIni        = " 00:00:00";
                $horaFin        = " 23:59:59";
                $diaAnteriorIni = $nuevafecha . $horaIni;
                $diaAnteriorFin = $nuevafecha . $horaFin;

                $stmt2 = $this->_DB->prepare("SELECT idGestion
                                                    from BrutalForce
                                                    where gestionAsesor in ('2', '3')
                                                      and estadoFinalPedido = 'Pendiente'
                                                      and ObservacionesFinales is not null
                                                      and fechagestionAsesor between (:diaAnteriorIni) and (:diaAnteriorFin)
                                                    order by fechagestionAsesor");
                $stmt2->execute([
                    'diaAnteriorIni' => $diaAnteriorIni,
                    'diaAnteriorFin' => $diaAnteriorFin,
                ]);

                if ($stmt2->rowCount()) {
                    $idGestion       = $stmt2->fetchAll(PDO::FETCH_ASSOC);
                    $idgestionAsesor = $idGestion['idGestion'];

                    $stmtUpdate = $this->_DB->prepare("UPDATE BrutalForce
                                                                SET pedidobloqueado = '2'
                                                                WHERE idGestion = :idgestionAsesor");
                    $stmtUpdate->execute([
                        ':idgestionAsesor' => $idgestionAsesor,
                    ]);
                }
            }

            $queryAsesores = $this->_DB->query("SELECT PedidoDespacho,
                                                               Asesor,
                                                               CausaActividad,
                                                               pedidoNuevo,
                                                               numeroOferta,
                                                               fechaclick,
                                                               estado,
                                                               numeroIncidente,
                                                               actividadRealizaGrupo,
                                                               estadoFinalPedido,
                                                               fechagestionAsesor,
                                                               ObservacionAsesor,
                                                               ObservacionesDespacho,
                                                               CorreoDespacho,
                                                               ObservacionesFinales
                                                        from BrutalForce
                                                        where gestionAsesor in ('2', '3')
                                                          and estadoFinalPedido = 'Pendiente'
                                                          and pedidobloqueado <> '2'
                                                        order by fechagestionAsesor");
            $queryAsesores->execute();

            if ($queryAsesores->rowCount()) {
                $resultadoAsesores = $queryAsesores->fetchAll(PDO::FETCH_ASSOC);
                $response          = [$result, $resultadoAsesores, 201];
            } else {
                $response = ['No se encontraron registros', 400];
            }

        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        $this->_DB = null;
        echo json_encode($response);
    }

    public function DashBoard()
    {
        try {

            $queryEnGestion = $this->_DB->query("SELECT PedidoDespacho,
                                                               Asesor,
                                                               AccionDespacho,
                                                               FechaGestionDespacho,
                                                               SEC_TO_TIME((TIMESTAMPDIFF(SECOND, FechaGestionDespacho, NOW())))              AS TiempoSaveDespacho,
                                                               SEC_TO_TIME((TIMESTAMPDIFF(SECOND, fechaInicioGestion, NOW())))                AS TiempoConGestor,
                                                               SEC_TO_TIME((TIMESTAMPDIFF(SECOND, FechaGestionDespacho, fechaInicioGestion))) AS Despacho_Asesor,
                                                               (select count(PedidoDespacho)
                                                                from BrutalForce
                                                                where pedidobloqueado = '1'
                                                                  and gestionAsesor = '1')                                                    AS total
                                                        from BrutalForce
                                                        where pedidobloqueado = '1'
                                                          and gestionAsesor = '1'
                                                        order by FechaGestionDespacho");
            $queryEnGestion->execute();

            if ($queryEnGestion->rowCount()) {
                $resul     = $queryEnGestion->fetchAll(PDO::FETCH_ASSOC);
                $enGestion = $resul['AccionDespacho'];
            }

            $querySin_gestion = $this->_DB->query("SELECT PedidoDespacho,
                                                                   FechaGestionDespacho,
                                                                   AccionDespacho,
                                                                   SEC_TO_TIME((TIMESTAMPDIFF(SECOND, FechaGestionDespacho, NOW()))) AS Despacho_Asesor,
                                                                   (select count(PedidoDespacho)
                                                                    from BrutalForce
                                                                    where pedidobloqueado is null
                                                                      and gestionAsesor = '1')                                       AS total
                                                            from BrutalForce
                                                            where pedidobloqueado is null
                                                              and gestionAsesor = '1'
                                                            order by FechaGestionDespacho");
            $querySin_gestion->execute();

            if ($querySin_gestion->rowCount()) {
                $resul       = $querySin_gestion->fetchAll(PDO::FETCH_ASSOC);
                $Sin_gestion = $resul['AccionDespacho'];
            }

            $queryEscalados = $this->_DB->query("SELECT PedidoDespacho,
                                                                   (case
                                                                        when locate('/', REVERSE(estado)) = 0 then estado
                                                                        else right(estado, locate('/', REVERSE(estado)) - 1)
                                                                       end)                                                          AS estado,
                                                                   numeroOferta,
                                                                   asesor,
                                                                   FechaGestionDespacho,
                                                                   AccionDespacho,
                                                                   AccionDespacho,
                                                                   SEC_TO_TIME((TIMESTAMPDIFF(SECOND, FechaGestionDespacho, NOW()))) AS Tiempo,
                                                                   (select count(PedidoDespacho)
                                                                    from BrutalForce
                                                                    where pedidobloqueado in ('1', '2')
                                                                      and gestionAsesor in ('2', '3')
                                                                      and estado not like '%Finalizado%')                            AS total
                                                            from BrutalForce
                                                            where pedidobloqueado in ('1', '2')
                                                              and gestionAsesor in ('2', '3')
                                                              and estado not like '%Finalizado%'
                                                            order by FechaGestionDespacho");
            $queryEscalados->execute();

            if ($queryEscalados->rowCount()) {
                $resul     = $queryEscalados->fetchAll(PDO::FETCH_ASSOC);
                $Escalados = $resul['AccionDespacho'];
            }

            $queryPendiente_analisis = $this->_DB->query("SELECT PedidoDespacho,
                                                                           Asesor,
                                                                           FechaGestionDespacho,
                                                                           fechagestionAsesor,
                                                                           AccionDespacho,
                                                                           SEC_TO_TIME((TIMESTAMPDIFF(SECOND, fechagestionAsesor, NOW()))) AS Despacho_Asesor,
                                                                           (select count(PedidoDespacho)
                                                                            from BrutalForce
                                                                            where ObservacionesFinales is null
                                                                              and gestionAsesor = '2'
                                                                              and estado like '%Finalizado%')                              AS total
                                                                    from BrutalForce
                                                                    where ObservacionesFinales is null
                                                                      and gestionAsesor = '2'
                                                                      and estado like '%Finalizado%'
                                                                    order by FechaGestionDespacho");
            $queryPendiente_analisis->execute();

            if ($queryPendiente_analisis->rowCount()) {
                $resul              = $queryPendiente_analisis->fetchAll(PDO::FETCH_ASSOC);
                $Pendiente_analisis = $resul['AccionDespacho'];
            }

            $escaladosCalidad = $this->_DB->query("SELECT PedidoDespacho,
                                                                   (case
                                                                        when locate('/', REVERSE(estado)) = 0 then estado
                                                                        else right(estado, locate('/', REVERSE(estado)) - 1)
                                                                       end)                                                          AS                   estado,
                                                                   numeroOferta,
                                                                   asesor,
                                                                   FechaGestionDespacho,
                                                                   AccionDespacho,
                                                                   AccionDespacho,
                                                                   SEC_TO_TIME((TIMESTAMPDIFF(SECOND, FechaGestionDespacho, NOW()))) AS                   Tiempo,
                                                                   (select count(PedidoDespacho)
                                                                    from BrutalForce
                                                                    where pedidobloqueado in ('1', '2')
                                                                      and gestionAsesor in ('2', '3')
                                                                      and estado not like '%Finalizado%'
                                                                      and SEC_TO_TIME((TIMESTAMPDIFF(SECOND, FechaGestionDespacho, NOW()))) > '00:40:00') totalescalaCalidad
                                                            from BrutalForce
                                                            where pedidobloqueado in ('1', '2')
                                                              and gestionAsesor in ('2', '3')
                                                              and estado not like '%Finalizado%'
                                                              and SEC_TO_TIME((TIMESTAMPDIFF(SECOND, FechaGestionDespacho, NOW()))) > '00:40:00'");

            $escaladosCalidad->execute();

            if ($escaladosCalidad->rowCount()) {
                $resul            = $escaladosCalidad->fetchAll(PDO::FETCH_ASSOC);
                $EscaladosCalidad = $resul;
            }

            $queryEnGestionCalidad = $this->_DB->query("SELECT PedidoDespacho,
                                                                       Asesor,
                                                                       AccionDespacho,
                                                                       FechaGestionDespacho,
                                                                       SEC_TO_TIME((TIMESTAMPDIFF(SECOND, FechaGestionDespacho, NOW())))                      AS TiempoSaveDespacho,
                                                                       SEC_TO_TIME((TIMESTAMPDIFF(SECOND, fechaInicioGestion, NOW())))                        AS TiempoConGestor,
                                                                       SEC_TO_TIME((TIMESTAMPDIFF(SECOND, FechaGestionDespacho, fechaInicioGestion)))         AS Despacho_Asesor,
                                                                       (select count(PedidoDespacho)
                                                                        from BrutalForce
                                                                        where pedidobloqueado = '1'
                                                                          and gestionAsesor = '1'
                                                                          and SEC_TO_TIME((TIMESTAMPDIFF(SECOND, FechaGestionDespacho, NOW()))) > '00:40:00') AS totalcalidad
                                                                from BrutalForce
                                                                where pedidobloqueado = '1'
                                                                  and gestionAsesor = '1'
                                                                  and SEC_TO_TIME((TIMESTAMPDIFF(SECOND, FechaGestionDespacho, NOW()))) > '00:40:00'");

            $queryEnGestionCalidad->execute();

            if ($queryEnGestionCalidad->rowCount()) {
                $resul            = $queryEnGestionCalidad->fetchAll(PDO::FETCH_ASSOC);
                $enGestionCalidad = $resul;
            }

            $queryAsesoresCalidad = $this->_DB->prepare("SELECT  CausaActividad,
                                                                       actividadRealizaGrupo,
                                                                       estadoFinalPedido,
                                                                       ObservacionAsesor,
                                                                       ObservacionesDespacho,
                                                                       ObservacionesFinales,
                                                                       SEC_TO_TIME((TIMESTAMPDIFF(SECOND, FechaGestionDespacho, NOW())))
                                                                           AS                                                   TiempoSaveDespacho,
                                                                       (select count(PedidoDespacho)
                                                                        from BrutalForce
                                                                        where gestionAsesor
                                                                            in ('2', '3')
                                                                          and estadoFinalPedido = 'Pendiente'
                                                                          and pedidobloqueado <> '2'
                                                                          and AccionDespacho <> 'Anulación'
                                                                          and SEC_TO_TIME((TIMESTAMPDIFF(SECOND,
                                                                                                         FechaGestionDespacho,
                                                                                                         NOW()))) > '00:40:00') total
                                                                from BrutalForce
                                                                where gestionAsesor in ('2', '3')
                                                                  and estadoFinalPedido = 'Pendiente'
                                                                  and pedidobloqueado <> '2'
                                                                  and AccionDespacho <> 'Anulación'
                                                                  and SEC_TO_TIME((TIMESTAMPDIFF(SECOND, FechaGestionDespacho,
                                                                                                 NOW()))) > '00:40:00'
                                                                order by FechaGestionDespacho");

            $queryAsesoresCalidad->execute();

            if ($queryAsesoresCalidad->rowCount()) {
                $resul                    = $queryAsesoresCalidad->fetchAll(PDO::FETCH_ASSOC);
                $resultadoAsesoresCalidad = $resul;
            }

            $response = [$enGestion, $Sin_gestion, $Escalados, $Pendiente_analisis, $EscaladosCalidad, $enGestionCalidad, $resultadoAsesoresCalidad, 201];

        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }

        $this->_DB = null;
        echo json_encode($response);
    }

    public function gestionAsesorBrutal($data)
    {

        try {

            $fechagestionAsesor = date('Y-m-d H:i:s');

            $infogestion   = $data['datosguardar'];
            $datosDespacho = $data['datosDespacho'];

            $idGestion             = $datosDespacho['PedidoDespacho'];
            $PedidoDespacho        = $datosDespacho['PedidoDespacho'];
            $AccionDespacho        = $datosDespacho['AccionDespacho'];
            $CorreoDespacho        = $datosDespacho['CorreoDespacho'];
            $ObservacionesDespacho = $datosDespacho['ObservacionesDespacho'];
            $FechaGestionDespacho  = $datosDespacho['FechaGestionDespacho'];
            $tipoTransaccion       = $datosDespacho['tipoTransaccion'];
            $zona                  = utf8_decode($datosDespacho['zona']);
            $LoginDespacho         = $datosDespacho['LoginDespacho'];

            $causaActividad = utf8_decode($infogestion['causaActividad']);
            $estado         = utf8_decode($infogestion['estado']);
            $pedidoNuevo    = $infogestion['pedidoNuevo'];
            $fechaClick     = $infogestion['fechaClick'];
            $numeroOferta   = $infogestion['numeroOferta'];
            $actividad      = utf8_decode($infogestion['actividad']);
            $canal          = utf8_decode($infogestion['canal']);
            $incidente      = $infogestion['incidente'];
            $observaciones  = utf8_decode($infogestion['observaciones']);

            $login = $_SESSION['login'];

            $stmt = $this->_DB->prepare("select idgestion from BrutalForce where PedidoDespacho = :PedidoDespacho");
            $stmt->execute([':PedidoDespacho' => $PedidoDespacho]);

            if ($stmt->rowCount()) {
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $id     = $result['idgestion'];

                $stmt = $this->_DB->prepare("UPDATE BrutalForce
                                                    SET gestionAsesor         = '2',
                                                        Asesor                = :login,
                                                        CausaActividad        = :causaActividad,
                                                        canalVentas           = :canal,
                                                        pedidoNuevo           = :pedidoNuevo,
                                                        numeroOferta          = :numeroOferta,
                                                        estado                = :estado,
                                                        fechaclick            = STR_TO_DATE('$fechaClick', '%d/%m/%Y %T'),
                                                        numeroIncidente       = :incidente,
                                                        actividadRealizaGrupo = :actividad,
                                                        ObservacionAsesor     = :observaciones,
                                                        fechagestionAsesor    = :fechagestionAsesor,
                                                        estadoFinalPedido     = 'Pendiente'
                                                    WHERE idgestion = :id ");
                $stmt->execute([
                    ':login'              => $login,
                    ':causaActividad'     => $causaActividad,
                    ':canal'              => $canal,
                    ':pedidoNuevo'        => $pedidoNuevo,
                    ':numeroOferta'       => $numeroOferta,
                    ':estado'             => $estado,
                    ':incidente'          => $incidente,
                    ':actividad'          => $actividad,
                    ':observaciones'      => $observaciones,
                    ':fechagestionAsesor' => $fechagestionAsesor,
                    ':id'                 => $id,
                ]);
                if ($stmt->rowCount()) {
                    $response = ['Datos actualizados', 201];
                } else {
                    $response = ['Ah ocurrido un error intentalo nuevamente', 400];
                }
            } else {
                $response = ['No se encotraron datos', 400];
            }

        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        $this->_DB = null;
        echo json_encode($response);
    }

    public function savecontingencia($datosguardar)
    {
        try {
            session_start();
            $login        = $_SESSION['login'];
            $estadoActual = (isset($datosguardar['estado'])) ? $datosguardar['estado'] : '';
            $accion       = (isset($datosguardar['accion'])) ? $datosguardar['accion'] : '';
            $ciudad       = (isset($datosguardar['ciudad'])) ? $datosguardar['ciudad'] : '';
            $correo       = (isset($datosguardar['correo'])) ? $datosguardar['correo'] : '';
            $macEntra     = (isset($datosguardar['macEntra'])) ? $datosguardar['macEntra'] : '';
            $macSale      = (isset($datosguardar['macSale'])) ? $datosguardar['macSale'] : '';
            $motivo       = (isset($datosguardar['motivo'])) ? $datosguardar['motivo'] : '';
            $observacion  = (isset($datosguardar['observacion'])) ? $datosguardar['observacion'] : '';
            $pedido       = (isset($datosguardar['pedido'])) ? $datosguardar['pedido'] : '';
            $proceso      = (isset($datosguardar['proceso'])) ? $datosguardar['proceso'] : '';
            $remite       = (isset($datosguardar['remite'])) ? $datosguardar['remite'] : '';
            $producto     = (isset($datosguardar['producto'])) ? $datosguardar['producto'] : '';
            $tecnologia   = (isset($datosguardar['tecnologia'])) ? $datosguardar['tecnologia'] : '';
            $tipoEquipo   = (isset($datosguardar['tipoEquipo'])) ? $datosguardar['tipoEquipo'] : '';
            $uen          = (isset($datosguardar['uen'])) ? $datosguardar['uen'] : '';
            $contrato     = (isset($datosguardar['contrato'])) ? $datosguardar['contrato'] : '';
            $perfil       = (isset($datosguardar['perfil'])) ? $datosguardar['perfil'] : '';
            $paquetes     = (isset($datosguardar['paquetes'])) ? $datosguardar['paquetes'] : '';
            $paqueteconca = (isset($datosguardar['$paqueteconca'])) ? $datosguardar['$paqueteconca'] : '';


            if ($paquetes != '') {
                $tam          = count($paquetes);
                $paqueteconca = "";
                for ($i = 0; $i < $tam; $i++) {
                    $paqueteconca = $paqueteconca . $paquetes[$i] . "/";
                }
            }

            /*CUANDO SE SELECCIONE SEGUN EL PRODUCTO GUARDE EN EL CAMPO DE GRUPO*/
            if ($producto == "TV" && $accion == "Corregir portafolio") {
                $grupo = "PORTAFOLIO";
            } elseif ($producto == "Internet" && $accion == "Corregir portafolio") {
                $grupo = "PORTAFOLIO";
            } elseif ($producto == "ToIP" && $accion == "Corregir portafolio") {
                $grupo = "PORTAFOLIO";
            } elseif ($producto == "Internet+ToIP" && $accion == "Corregir portafolio" || $producto == "Internet+ToIP" && $accion == "OC Telefonia") {
                $grupo = "PORTAFOLIO";
            } elseif ($producto == "TV" && $accion == "mesaOffline") {
                $grupo = "PORTAFOLIO";
            } elseif ($producto == "Internet" && $accion == "mesaOffline") {
                $grupo = "PORTAFOLIO";
            } elseif ($producto == "ToIP" && $accion == "mesaOffline" || $producto == "ToIP" && $accion == "OC Telefonia") {
                $grupo = "PORTAFOLIO";
            } elseif ($producto == "Internet+ToIP" && $accion == "mesaOffline") {
                $grupo = "PORTAFOLIO";
            } elseif ($producto == "TV") {
                $grupo = "TV";
            } elseif ($producto == "Internet") {
                $grupo = "INTER";
            } elseif ($producto == "ToIP") {
                $grupo        = "INTER";
                $paqueteconca = $datosguardar['linea'];
            } elseif ($producto == "Internet+ToIP") {
                $grupo        = "INTER";
                $paqueteconca = $datosguardar['linea'];
            }

            $isFieldContingency = isset($datosguardar['_id']);


            if ($isFieldContingency) {
                $idTerreno               = $datosguardar['_id'];
                $horaGestionTerreno      = $datosguardar['fecha'];
                $nuevaHoraGestionTerreno = date('Y-m-d H:i:s', strtotime($horaGestionTerreno));

                $stmt = $this->_DB->prepare("INSERT INTO contingencias (accion, ciudad, correo, macEntra, macSale, motivo,
                                                       observacion, paquetes, pedido, proceso, producto,
                                                       remite, tecnologia, tipoEquipo, uen, contrato, perfil, grupo, logindepacho, id_terreno, horagestion)
                                                    values (:accion, :ciudad, :correo, :macEntra, :macSale, :motivo,
                                                            :observacion, :paqueteconca, :pedido, :proceso, :producto,
                                                            :remite, :tecnologia, :tipoEquipo, :uen, :contrato, :perfil, :grupo, :login, :idTerreno, :nuevaHoraGestionTerreno)");
                $stmt->execute([
                    ':accion'                  => $accion,
                    ':ciudad'                  => $ciudad,
                    ':correo'                  => $correo,
                    ':macEntra'                => $macEntra,
                    ':macSale'                 => $macSale,
                    ':motivo'                  => $motivo,
                    ':observacion'             => $observacion,
                    ':paqueteconca'            => $paqueteconca,
                    ':pedido'                  => $pedido,
                    ':proceso'                 => $proceso,
                    ':producto'                => $producto,
                    ':remite'                  => $remite,
                    ':tecnologia'              => $tecnologia,
                    ':tipoEquipo'              => $tipoEquipo,
                    ':uen'                     => $uen,
                    ':contrato'                => $contrato,
                    ':perfil'                  => $perfil,
                    ':grupo'                   => $grupo,
                    ':login'                   => $login,
                    ':idTerreno'               => $idTerreno,
                    ':nuevaHoraGestionTerreno' => $nuevaHoraGestionTerreno,
                ]);

                if ($stmt->rowCount()) {
                    $response = ['Datos ingresados correctamente', 201];
                } else {
                    $response = ['Ah ocurrido un error intentalo nuevamente', 400];
                }
            } else {
                $idTerreno = null;

                $stmt = $this->_DB->prepare("INSERT INTO contingencias (accion, ciudad, correo, macEntra, macSale, motivo,
                                                       observacion, paquetes, pedido, proceso, producto,
                                                       remite, tecnologia, tipoEquipo, uen, contrato, perfil, grupo, logindepacho, id_terreno)
                                                    values (:accion, :ciudad, :correo, :macEntra, :macSale, :motivo,
                                                            :observacion, :paqueteconca, :pedido, :proceso, :producto,
                                                            :remite, :tecnologia, :tipoEquipo, :uen, :contrato, :perfil, :grupo, :login, :idTerreno)");


                if ($accion !== "" || $correo !== "" || $pedido !== "" || $proceso !== "") {
                    $stmt->execute([
                        ':accion'       => $accion,
                        ':ciudad'       => $ciudad,
                        ':correo'       => $correo,
                        ':macEntra'     => $macEntra,
                        ':macSale'      => $macSale,
                        ':motivo'       => $motivo,
                        ':observacion'  => $observacion,
                        ':paqueteconca' => $paqueteconca,
                        ':pedido'       => $pedido,
                        ':proceso'      => $proceso,
                        ':producto'     => $producto,
                        ':remite'       => $remite,
                        ':tecnologia'   => $tecnologia,
                        ':tipoEquipo'   => $tipoEquipo,
                        ':uen'          => $uen,
                        ':contrato'     => $contrato,
                        ':perfil'       => $perfil,
                        ':grupo'        => $grupo,
                        ':login'        => $login,
                        ':idTerreno'    => $idTerreno,
                    ]);

                    if ($stmt->rowCount()) {
                        $response = ['Datos ingresados correctamente', 201];
                    } else {
                        $response = ['Ah ocurrido un error intentalo nuevamente', 400];
                    }
                }
            }
        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        $this->_DB = null;
        echo json_encode($response);
    }

    public function CancelarContingencias($datoscancelar)
    {

        try {
            session_start();
            $login                = $_SESSION['login'];
            $pedido               = $datoscancelar['pedido'];
            $id                   = $datoscancelar['id'];
            $observacionesconting = 'Cancelado por Despachador';
            $acepta               = 'Rechaza';
            $tipificacion         = 'Cancelado por Despachador';
            $horacontingencia     = date("Y-m-d H:i:s");

            $stmt = $this->_DB->prepare("SELECT id FROM contingencias WHERE pedido = :pedido AND engestion IS NULL OR pedido = :pedido AND engestion = '0'");
            $stmt->execute([':pedido' => $pedido]);

            if ($stmt->rowCount()) {
                $update = $this->_DB->prepare("UPDATE contingencias
                                                        SET horacontingencia   = :horacontingencia,
                                                            observContingencia = :observacionesconting,
                                                            tipificacion       = :tipificacion,
                                                            acepta             = :acepta,
                                                            finalizado         = 'OK',
                                                            logincontingencia  = :login,
                                                            engestion          = 1
                                                        WHERE id = :id");
                $update->execute([
                    ':horacontingencia'     => $horacontingencia,
                    ':observacionesconting' => $observacionesconting,
                    ':tipificacion'         => $tipificacion,
                    ':acepta'               => $acepta,
                    ':login'                => $login,
                    ':id'                   => $id,
                ]);

                if ($update->rowCount() == 1) {
                    $response = ['Contingencia Cancelada', 201];
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

    public function guardarEscalar($gestionescalado)
    {
        try {

            $estadoActual   = utf8_decode($gestionescalado['estado']);
            $PedidoDespacho = utf8_decode($gestionescalado['PedidoDespacho']);
            $fechaClick     = $gestionescalado['fechaclick'];
            $observaciones  = utf8_decode($gestionescalado['ObservacionAsesor']);

            if (preg_match("/^20\d{2}(-|\/)((0[1-9])|(1[0-2]))(-|\/)((0[1-9])|([1-2][0-9])|(3[0-1]))(T|\s)(([0-1][0-9])|(2[0-3])):([0-5][0-9]):([0-5][0-9])$/", $fechaClick)) {
                $fechaClick = "";
            } else {
                $fechaClick = "STR_TO_DATE('$fechaClick', '%d/%m/%Y %T') ";
            }

            $stmt = $this->_DB->prepare("select idgestion, estado from BrutalForce where PedidoDespacho = :PedidoDespacho");
            $stmt->execute([':PedidoDespacho' => $PedidoDespacho]);

            if ($stmt->rowCount()) {
                $result         = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $id             = $result['idgestion'];
                $estadoAnterior = $result['estado'];
                $estadoActual   = $estadoAnterior . "/" . $estadoActual;
                $update         = $this->_DB->prepare("UPDATE BrutalForce
                                                                SET estado            = :estadoActual,
                                                                    ObservacionAsesor = :observaciones,
                                                                    fechaClick        = :fechaClick
                                                                WHERE idgestion = :id");
                $update->execute([
                    ':$estadoActual'  => $estadoActual,
                    ':$observaciones' => $observaciones,
                    ':$fechaClick'    => $fechaClick,
                    ':$id'            => $id,
                ]);

                if ($update->rowCount()) {
                    $response = [':Datos actualizados', 201];
                } else {
                    $response = ['Ah ocurrido un error intentalo nuevamente'];
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

    public function gestionAsesorFinal($datosFinal)
    {
        try {

            $PedidoDespacho       = $datosFinal['PedidoDespacho'];
            $PedidoNuevo          = $datosFinal['pedidoNuevo'];
            $estadoFinalPedido    = utf8_decode($datosFinal['estadoFinalPedido']);
            $ObservacionesFinales = utf8_decode($datosFinal['ObservacionesFinales']);

            $stmt = $this->_DB->prepare("select idgestion id from BrutalForce where PedidoDespacho = :PedidoDespacho");
            $stmt->execute([':PedidoDespacho' => $PedidoDespacho]);

            if ($stmt->rowCount()) {
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $id     = $result['id'];

                $update = $this->_DB->prepare("UPDATE BrutalForce
                                                        SET gestionAsesor='3',
                                                            ObservacionesFinales = :ObservacionesFinales,
                                                            estadoFinalPedido = :estadoFinalPedido,
                                                            pedidoNuevo = :PedidoNuevo
                                                        WHERE idgestion = :id");
                $update->execute([
                    ':$ObservacionesFinales' => $ObservacionesFinales,
                    ':$estadoFinalPedido'    => $estadoFinalPedido,
                    ':$PedidoNuevo'          => $PedidoNuevo,
                    ':$id'                   => $id,
                ]);

                if ($update->rowCount() == 1) {
                    $response = ['Datos actualizados', 201];
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

    public function gestionPendientes()
    {
        try {
            $login = $_SESSION['login'];

            $stmt = $this->_DB->prepare("select estado, count(estado) total
                                                from BrutalForce
                                                where Asesor = :login
                                                  and estado is not null
                                                  and estado not like '%Finalizado%'
                                                group by estado
                                                order by count(estado) DESC");

            $stmt->execute([':login' => $login]);

            $stmt2 = $this->_DB->prepare("SELECT count(PedidoDespacho) Cantidad
                                                from BrutalForce
                                                where gestionAsesor = '1'
                                                  and (pedidobloqueado is null or pedidobloqueado = '0')
                                                  and AccionDespacho = 'Anulación'");
            $stmt2->execute();

            if ($stmt2->rowCount()) {
                $counter = $stmt2->rowCount();
            }

            if ($stmt->rowCount() || $counter !== 0) {
                $result   = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $response = [$result['estado'], $counter, 201];
            } else {
                $response = ['Ah ocurrido un error intentalo nuevamente', 400];
            }

        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        $this->_DB = null;
        echo json_encode($response);
    }

    public function Pendientesxestado($datos)
    {

        try {
            session_start();
            $login  = $_SESSION['login'];
            $estado = $datos['estado'];

            $stmt = $this->_DB->prepare("select PedidoDespacho
                                                from BrutalForce
                                                where Asesor = :login
                                                  and estado = :estado");
            $stmt->execute([
                ':login'  => $login,
                ':estado' => $estado,
            ]);

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

}
