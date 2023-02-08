<?php
require_once '../class/conection.php';
require_once '../class/utils.php';

class modelOtherServicesThree
{
    private $_DB;

    public $_utils;

    public function __construct()
    {
        $this->_DB    = new Conection();
        $this->_utils = new utils();
    }

    public function gestionBrutal($data)
    {
        try {
            session_start();
            $login  = $_SESSION['login'];
            $accion = $data;

            $fechaIniciogestionAsesor = date('Y-m-d H:i:s');

            if ($accion == "Anulado") {
                $resultado = [];

                $query = "SELECT idGestion,
                               PedidoDespacho,
                               AccionDespacho,
                               CorreoDespacho,
                               ObservacionesDespacho,
                               FechaGestionDespacho,
                               tipoTransaccion,
                               zona,
                               LoginDespacho,
                               idLlamada,
                               supervisor
                        from BrutalForce
                        where gestionAsesor = '1'
                          and (pedidobloqueado is null or pedidobloqueado = '0')
                          and AccionDespacho = 'Anulación'
                        order by FechaGestionDespacho
                        limit 1";

                $stmt = $this->_DB->query($query);
                $stmt->execute();

                if ($stmt->rowCount()) {
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    $update = $this->_DB->prepare("UPDATE BrutalForce
                                                            SET pedidobloqueado    = '1',
                                                                Asesor             = :login,
                                                                fechaInicioGestion = :fechaIniciogestionAsesor
                                                            WHERE idGestion = :idgestionAsesor");
                    $update->execute([
                        ':login'                    => $login,
                        ':fechaIniciogestionAsesor' => $fechaIniciogestionAsesor,
                        ':idgestionAsesor'          => $result['idGestion'],
                    ]);

                    if ($update->rowCount()) {
                        $response = [$result, 201];
                    } else {
                        $response = ['Ah ocurrido un error intentalo nuevamente', 400];
                    }
                } else {
                    $response = ['No se encontraron datos', 400];
                }

            } else {

                $stmt = $this->_DB->query("SELECT idGestion,
                                                           PedidoDespacho,
                                                           AccionDespacho,
                                                           CorreoDespacho,
                                                           ObservacionesDespacho,
                                                           FechaGestionDespacho,
                                                           tipoTransaccion,
                                                           zona,
                                                           LoginDespacho,
                                                           idLlamada,
                                                           supervisor
                                                    from BrutalForce
                                                    where gestionAsesor = '1'
                                                      and (pedidobloqueado is null or pedidobloqueado = '0')
                                                      and AccionDespacho <> 'Anulación'
                                                    order by FechaGestionDespacho
                                                    limit 1");

                if ($stmt->rowCount()) {
                    $result          = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $idgestionAsesor = $result['idGestion'];

                    $update = $this->_DB->prepare("UPDATE BrutalForce
                                                            SET pedidobloqueado    = '1',
                                                                Asesor             = :login,
                                                                fechaInicioGestion = :fecha
                                                            WHERE idGestion = :id");
                    $update->execute([':login' => $login, ':fecha' => $fechaIniciogestionAsesor, ':id' => $idgestionAsesor]);
                    if ($update->rowCount()) {
                        $response = [$result, 201];
                    } else {
                        $response = ['Ah ocurrido un error intentalo nuevamente', 400];
                    }
                } else {

                    $stmt = $this->_DB->query("SELECT idGestion,
                                                               PedidoDespacho,
                                                               AccionDespacho,
                                                               CorreoDespacho,
                                                               ObservacionesDespacho,
                                                               FechaGestionDespacho,
                                                               tipoTransaccion,
                                                               zona,
                                                               LoginDespacho,
                                                               idLlamada,
                                                               supervisor
                                                        from BrutalForce
                                                        where gestionAsesor = '1'
                                                          and (pedidobloqueado is null or pedidobloqueado = '0')
                                                          and AccionDespacho = 'Anulación'
                                                        order by FechaGestionDespacho 
                                                        limit 1");
                    $stmt->execute();

                    if ($stmt->rowCount()) {
                        $result          = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        $idgestionAsesor = $result['idGestion'];

                        $update = $this->_DB->prepare("UPDATE BrutalForce
                                                            SET pedidobloqueado    = '1',
                                                                Asesor             = '$login',
                                                                fechaInicioGestion = '$fechaIniciogestionAsesor'
                                                            WHERE idGestion = '$idgestionAsesor'");
                        $update->execute([':login' => $login, ':fecha' => $fechaIniciogestionAsesor, ':id' => $idgestionAsesor]);
                        $response = [$result, 201];
                    } else {
                        $response = ['No se encontraron datos', 400];
                    }
                }
            }
        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        $this->_DB = null;
        echo json_encode($response);
    }

    public function BuscarPedidoBrutal($data)
    {
        try {
            $stmt = $this->_DB->prepare("SELECT  AccionDespacho,
                                                       ObservacionesDespacho,
                                                       tipoTransaccion,
                                                       estado,
                                                       zona
                                                from BrutalForce
                                                where PedidoDespacho = :pedido
                                                  and estado not like '%Finalizado%'");
            $stmt->execute([':pedido' => $data]);

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

    public function meses()
    {
        try {
            $stmt = $this->_DB->query("select distinct mes from nps");
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

    public function mesesrepa()
    {
        try {
            $stmt = $this->_DB->query("select distinct mes from npsreparaciones");
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

    public function actualizarregion()
    {
        try {
            echo "no existe";
        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
    }

    public function departamentos()
    {
        try {
            $stmt = $this->_DB->query("select distinct DEPARTAMENTO_OPERACION
                                                from pendi_insta
                                                where DEPARTAMENTO_OPERACION is not null
                                                order by DEPARTAMENTO_OPERACION");
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

    public function conceptospendientes($data)
    {
        try {
            $stmt = $this->_DB->prepare("select regional, count(CONCEPTO_ATC) total_concepto
                                                    from pendi_insta
                                                    where INTERFAZ = :data
                                                      and regional is not null
                                                    group by regional
                                                    order by count(CONCEPTO_ATC) DESC");
            $stmt->execute([':data' => $data]);
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

    public function getConceptosTotales($data)
    {
        try {
            $regional = $data['regional'];
            $interfaz = $data['interfaz'];

            $stmt = $this->_DB->prepare("select CONCEPTO_ATC, count(CONCEPTO_ATC) total_concepto
                                                from pendi_insta
                                                where REGIONAL = :regional
                                                  and INTERFAZ = :interfaz
                                                group by CONCEPTO_ATC
                                                order by count(CONCEPTO_ATC) DESC");
            $stmt->execute([':regional' => $regional, ':interfaz' => $interfaz]);
            if ($stmt->rowCount()) {
                foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                    $CONCEPTO_ATC        = $this->_utils->quitar_tildes(utf8_encode($row['CONCEPTO_ATC']));
                    $row['CONCEPTO_ATC'] = $CONCEPTO_ATC;
                    $result[]            = $row;
                }
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

    public function ResumenInsta($data)
    {
        try {
            $departamento = $data;

            if (!$departamento) {
                $condition = "";
            } else {
                $condition = "where departamento = '$departamento'";
            }

            $today         = date("Y-m-d");
            $fechaanterior = date('Y-m-d', strtotime('-1 day', strtotime($today)));
            $mes           = date("m", strtotime($today));
            $anio          = date("Y", strtotime($today));

            $query = "SELECT DEPARTAMENTO_OPERACION, count(pedido_id) total_pedidos, sum(productos) total_productos
                        FROM pendi_insta
                        where fecha = '$today'
                        group by DEPARTAMENTO_OPERACION
                        order by total_pedidos DESC";

            $RESPONSABLE_GESTION = "SELECT RESPONSABLE_GESTION,
                                           sum(total)       totaldireccion,
                                           sum(Entre_0_5)   as Entre_0_5,
                                           sum(Entre_5_10)  as Entre_5_10,
                                           sum(Entre_10_15) as Entre_10_15,
                                           sum(Entre_15_30) as Entre_15_30,
                                           sum(Mayor_30)    as Mayor_30
                                    FROM resumenpendientesInstaresponsable $condition
                                    group by RESPONSABLE_GESTION
                                    order by sum(total) DESC";

            $TIPO_SOLICITUD_ORIG = "SELECT solicitud,
                                           sum(total)       totalsolicitud,
                                           sum(Entre_0_5)   as Entre_0_5,
                                           sum(Entre_5_10)  as Entre_5_10,
                                           sum(Entre_10_15) as Entre_10_15,
                                           sum(Entre_15_30) as Entre_15_30,
                                           sum(Mayor_30)    as Mayor_30
                                    FROM resumenpendientesInstasolicitudRango $condition
                                    group by solicitud
                                    order by sum(total) DESC";

            $NOVEDADES = "SELECT solicitud,
                               sum(total)     as total,
                               sum(sinAgenda) as Sin_Agenda,
                               sum(vencida)   as Vencida,
                               sum(futura)    as Futura,
                               sum(paraHoy)   as Para_HOY,
                               sum(manana)    as Para_Manana
                        FROM resumenpendientesInstaSolicitudAgenda $condition
                        group by solicitud
                        order by sum(total) DESC ";

            $historico_rangos = "SELECT *
                                    FROM HistoricoPendientesInsta
                                    where fecha BETWEEN
                                              ('$anio-$mes-01') AND ('$anio-$mes-31')
                                    order by fecha";

            $historico_porcentajes = "SELECT ROUND((Entre_0_5 / total) * 100, 2)   porecentaje0_5,
                                               ROUND((Entre_5_10 / total) * 100, 2)  porecentaje5_10,
                                               ROUND((Entre_10_15 / total) * 100, 2) porecentaje10_15,
                                               ROUND((Entre_15_30 / total) * 100, 2) porecentaje15_30,
                                               ROUND((Mayor_30 / total) * 100, 2)    porecentajemayor_30,
                                               ROUND((total / total) * 100)          as total
                                        FROM HistoricoPendientesInsta
                                        where fecha = '$today' ";

            $diferencia_totales = "SELECT distinct (select Entre_0_5
                                 from HistoricoPendientesInsta where fecha = '$fechaanterior') - (select Entre_0_5 from HistoricoPendientesInsta where fecha = '$today') as Entre_0_5,
                                (select Entre_5_10 from HistoricoPendientesInsta where fecha = '$fechaanterior') - (select Entre_5_10 from HistoricoPendientesInsta where fecha = '$today') as Entre_5_10,
                                (select Entre_10_15 from HistoricoPendientesInsta where fecha = '$fechaanterior') - (select Entre_10_15 from HistoricoPendientesInsta where fecha = '$today') as Entre_10_15,
                                (select Entre_15_30 from HistoricoPendientesInsta where fecha = '$fechaanterior') - (select Entre_15_30 from HistoricoPendientesInsta where fecha = '$today') as Entre_15_30,
                                (select mayor_30 from HistoricoPendientesInsta where fecha = '$fechaanterior') - (select mayor_30 from HistoricoPendientesInsta where fecha = '$today') as mayor_30,
                                (select total from HistoricoPendientesInsta where fecha = '$fechaanterior') - (select total from HistoricoPendientesInsta where fecha = '$today') as total
                                FROM HistoricoPendientesInsta";

            $rstdiferencia_totales = $this->_DB->query($diferencia_totales);
            $rstdiferencia_totales->execute();
            foreach ($rstdiferencia_totales->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $resultadorstdiferencia_totales[] = $row;
            }

            $rst = $this->_DB->query($query);
            $rst->execute();

            $resultado       = [];
            $Totalregionales = [];
            $total_pedidos   = 0;
            $total_productos = 0;
            foreach ($rst->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $resultado[]     = $row;
                $total_pedidos   = $row['total_pedidos'] + $total_pedidos;
                $total_productos = $row['total_productos'] + $total_productos;
            }

            $rstdireccion = $this->_DB->query($RESPONSABLE_GESTION);
            $rstdireccion->execute();
            foreach ($rstdireccion->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $resultadodireccion[] = $row;
            }

            $rstsolicitud = $this->_DB->query($TIPO_SOLICITUD_ORIG);
            $rstsolicitud->execute();
            foreach ($rstsolicitud->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $solicitud            = $this->_utils->quitar_tildes(utf8_encode($row['solicitud']));
                $row['solicitud']     = $solicitud;
                $resultadosolicitud[] = $row;
            }

            $rsthistoricos = $this->_DB->query($historico_rangos);
            $rsthistoricos->execute();

            foreach ($rsthistoricos->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $resultadohistoricos[] = $row;
            }

            $rstporcentajes = $this->_DB->query($historico_porcentajes);
            $rstporcentajes->execute();
            foreach ($rstporcentajes->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $resultadoporcentajes[] = $row;
            }

            $rstnovedades = $this->_DB->query($NOVEDADES);
            $rstnovedades->execute();
            foreach ($rstnovedades->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $solicitud            = $this->_utils->quitar_tildes(utf8_encode($row['solicitud']));
                $row['solicitud']     = $solicitud;
                $resultadonovedades[] = $row;
            }

            $response = [
                $resultado,
                $total_pedidos,
                $total_productos,
                $resultadodireccion,
                $resultadosolicitud,
                $resultadohistoricos,
                $resultadoporcentajes,
                $resultadonovedades,
                $resultadorstdiferencia_totales,
                201,
            ];
        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        $this->_DB = null;
        echo json_encode($response);
    }

    public function tipo_trabajoclick()
    {
        try {
            $stmt = $this->_DB->query("SELECT DISTINCT tipo_trabajo
                                                FROM carga_click
                                                ORDER BY tipo_trabajo");
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
}
