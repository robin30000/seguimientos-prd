<?php
require_once '../class/conection.php';

class modelOtrosServicios
{
    private $_DB;

    public function __construct()
    {
        $this->_DB = new Conection();
    }


    public function DepartamentosContratos($data)
    {
        try {
            $mesenviado = $data;

            if ($mesenviado == "" || $mesenviado == undefined) {

                $query = $this->_DB->query("select max(fecha_instalacion) AS fecha from nps ");
                $fecha = date("Y-m-d");

                if ($query->rowCount()) {
                    if ($row = $query->fetchAll(PDO::FETCH_ASSOC)) {
                        $fecha = $row['fecha'];
                    }
                }

                $dia  = substr($fecha, 8, 2);
                $mes  = substr($fecha, 5, 2);
                $anio = substr($fecha, 0, 4);

                $nom_mes   = date('M', mktime(0, 0, 0, $mes, $dia, $anio));
                $semana    = "Semana " . date('W', mktime(0, 0, 0, $mes, $dia, $anio));
                $diaSemana = date("w", mktime(0, 0, 0, $mes, $dia, $anio));

            } else {
                $nom_mes   = $mesenviado;
                $semana    = "Semana " . date('W', mktime(0, 0, 0, $mes, $dia, $anio));
                $diaSemana = date("w", mktime(0, 0, 0, $mes, $dia, $anio));
            }

            $sql = $this->_DB->prepare("select gen.regional, round((select count(num_respuesta) 
                from nps  
                where num_respuesta = '5' and num_pregunta = '4' and contratista = 'EIA' 
                and regional = gen.regional and mes=gen.mes)/ 
                (select count(num_respuesta) from nps where num_respuesta = '5' and num_pregunta = '4'  
                and regional = gen.regional and mes=gen.mes)*100, 2)as EIA,  
                round((select count(num_respuesta) from nps 
                where num_respuesta = '5' and num_pregunta = '4' and contratista = 'Conavances' 
                and regional = gen.regional and mes=gen.mes)/ 
                (select count(num_respuesta) from nps where num_respuesta = '5' and num_pregunta = '4'  
                and regional = gen.regional and mes=gen.mes)*100, 2) as Conavances,  
                round((select count(num_respuesta) from nps  
                where num_respuesta = '5' and num_pregunta = '4' and contratista = 'EAGLE' 
                and regional = gen.regional and mes=gen.mes)/ 
                (select count(num_respuesta) from nps where num_respuesta = '5' and num_pregunta = '4' 
                and regional = gen.regional and mes=gen.mes)*100, 2) as EAGLE, 
                round((select count(num_respuesta) from nps 
                where num_respuesta = '5' and num_pregunta = '4' and contratista = 'EMT' 
                and regional = gen.regional and mes=gen.mes)/ 
                (select count(num_respuesta) from nps where num_respuesta = '5' and num_pregunta = '4'  
                and regional = gen.regional and mes=gen.mes)*100, 2) as EMT,  
                round((select count(num_respuesta) from nps  
                where num_respuesta = '5' and num_pregunta = '4' and contratista = 'RYE' 
                and regional = gen.regional and mes=gen.mes)/ 
                (select count(num_respuesta) from nps where num_respuesta = '5' and num_pregunta = '4' 
                and regional = gen.regional and mes=gen.mes)*100, 2) as RYE, 
                round((select count(num_respuesta) from nps 
                where num_respuesta = '5' and num_pregunta = '4' and contratista = 'SERVTEK' 
                and regional = gen.regional and mes=gen.mes)/ 
                (select count(num_respuesta) from nps where num_respuesta = '5' and num_pregunta = '4'  
                and regional = gen.regional and mes=gen.mes)*100, 2) as SERVTEK 
                from nps gen where  contratista = gen.contratista 
                and mes = :mes
                group by gen.regional order by regional desc ");
            $sql->execute([':mes' => $nom_mes]);

            $resultadodptoContrato = [];

            if ($sql->rowCount()) {

                $dptos      = [];
                $eia        = [];
                $conavances = [];
                $eagle      = [];
                $emt        = [];
                $rye        = [];
                $servtek    = [];

                while ($row = $sql->fetchAll(PDO::FETCH_ASSOC)) {
                    $row['regional']         = utf8_encode($row['regional']);
                    $label                   = $row['regional'];
                    $resultadodptoContrato[] = $row;
                    $deptoeia                = $row['EIA'];
                    $deptocona               = $row['Conavances'];
                    $deptoeagle              = $row['EAGLE'];
                    $deptoemt                = $row['EMT'];
                    $deptorye                = $row['RYE'];
                    $deptoservt              = $row['SERVTEK'];

                    $dptos[]      = ["label" => "$label"];
                    $eia[]        = ["value" => "$deptoeia"];
                    $conavances[] = ["value" => "$deptocona"];
                    $eagle[]      = ["value" => "$deptoeagle"];
                    $emt[]        = ["value" => "$deptoemt"];
                    $rye[]        = ["value" => "$deptorye"];
                    $servtek[]    = ["value" => "$deptoservt"];
                }

                $query = $this->_DB->prepare("select gen.contratista contratista, 
                    round((select count(respuesta) 
                    from nps  
                    where num_respuesta = '5' and num_pregunta = '4'  and contratista = gen.contratista  
                    and mes=gen.mes)/  
                    (select count(pregunta)   
                    from nps where contratista = gen.contratista and num_pregunta = '4'  
                    and mes=gen.mes)*100, 2) as SI  
                    from nps gen  
                    where mes= :mes 
                    group by gen.contratista  order by  contratista");
                $query->execute([':mes' => $nom_mes]);

                $contratos = [];

                while ($row = $query->fetchAll(PDO::FETCH_ASSOC)) {
                    $label       = utf8_encode($row['contratista']);
                    $rescontrato = $row['SI'];

                    $contratos[] = ["label" => "$label", "value" => "$rescontrato"];
                }

                $response = [$resultadodptoContrato, $dptos, $eia, $conavances, $eagle, $emt, $rye, $servtek, $contratos, 201];

            } else {
                $response = [0, 400];
            }
        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        $this->_DB = null;
        echo json_encode($response);
    }

    public function insertData($datos)
    {
        try {
            $fecha       = $datos['fecha'];
            $uen         = $datos['uen'];
            $tipotrabajo = $datos['tipo_trabajo'];
            $ciudad      = $datos['CIUDAD'];
            $mes         = date("m", strtotime($fecha));
            $anio        = date("Y", strtotime($fecha));
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

            //truncate table
            $query = $this->_DB->query("TRUNCATE TABLE jornada_estados");

            //insert jornadaID
            $query = $this->_DB->query("INSERT INTO jornada_estados 
                (`id_jornada`) VALUES ('AM'),('PM'),('HF'),('TOTAL'),('DIFERENCIA'); ");

            //carga de agendados
            $sqlcarga = $this->_DB->query("select count(pro.jornada_cita) total_jornada, 
                (case 
                when pro.jornada_cita = 'AM' then 'AM' 
                when pro.jornada_cita = 'PM' then 'PM' 
                else 'HF' 
                end) jornada, (select count(pro.jornada_cita) from carga_agenda pro 
                where pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') 
                '$uen' '$tipo_trabajo1' '$ciudades') TOTAL,
                (select count(pro.pedido_id) 
                from carga_agenda pro 
                where pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') 
                and pro.pedido_id not in (select pedido_id from carga_click 
                where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) 
                '$uen' '$tipo_trabajo1' '$ciudades') DIFERENCIA
                from carga_agenda pro 
                where pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')
                '$uen' '$tipo_trabajo1' '$ciudades'
                group by jornada");
            //echo $sqlcarga;
            $sqlcarga->execute();

            while ($row = $sqlcarga->fetchAll(PDO::FETCH_ASSOC)) {

                $total_jornada = $row['total_jornada'];
                $jornada       = $row['jornada'];
                $diferencia    = $row['DIFERENCIA'];
                $total_carga   = $total_carga + $total_jornada;
                $sqlupdate     = $this->_DB->prepare("UPDATE jornada_estados SET `agendados`=:total_jornada WHERE `id_jornada`=:jornada ");

                $sqlupdate->execute([':total_jornada' => $total_jornada, ':jornada' => $jornada]);
            }
            $sqlupdate = $this->_DB->prepare("UPDATE jornada_estados SET `agendados`=:total_carga WHERE `id_jornada`='TOTAL'");
            $sqlupdate->execute([':total_carga' => $total_carga]);

            $sqlupdate1 = $this->_DB->prepare("UPDATE jornada_estados SET `agendados`=:diferencia WHERE `id_jornada`='DIFERENCIA'");
            $sqlupdate1->execute([':diferencia' => $diferencia]);

            //carga de agendados y click
            $sqlvistaClik = $this->_DB->query("select count(pro.jornada_cita) total_jornada,   
                (case when pro.jornada_cita = 'AM' then 'AM' 
                when pro.jornada_cita = 'PM' then 'PM' 
                else 'HF' end) jornada, 
                (select count(pro.jornada_cita) total_jornada 
                from carga_click pro 
                where pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') 
                and pro.pedido_id in (select pedido_id from carga_agenda 
                where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) 
                '$uen' '$tipo_trabajo' '$ciudades') TOTAL, 

                (select count(pro.pedido_id) 
                from carga_click pro 
                where pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') 
                and pro.pedido_id not in (select pedido_id from carga_agenda  
                where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) '$uen' '$tipo_trabajo' '$ciudades') DIFERENCIA 

                from carga_click pro 
                where pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') 
                and pro.pedido_id in (select pedido_id from carga_agenda 
                where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) 
                '$uen' '$tipo_trabajo' '$ciudades' group by jornada");
            $sqlvistaClik->execute();
            //echo $sqlvistaClik;
            while ($row = $sqlvistaClik->fetchAll(PDO::FETCH_ASSOC)) {

                $total_jornada    = $row['total_jornada'];
                $jornada          = $row['jornada'];
                $diferencia       = $row['DIFERENCIA'];
                $total_cargaclick = $total_cargaclick + $total_jornada;
                $sqlupdateclick   = $this->_DB->prepare("UPDATE jornada_estados SET `vista_click`='$total_jornada' WHERE `id_jornada`=:jornada");
                $sqlupdateclick->execute([':jornada' => $jornada]);
            }
            $sqlupdateclicktotal = $this->_DB->query("UPDATE jornada_estados SET `vista_click`='$total_cargaclick' WHERE `id_jornada`='TOTAL'");
            $sqlupdatedif        = $this->_DB->query("UPDATE jornada_estados SET `vista_click`='$diferencia' WHERE `id_jornada`='DIFERENCIA'");

            //carga de agendados y click confirmados
            $sqlconfirmados = $this->_DB->query("select sum(a.totales) as totales, a.jornada_cita,
                (select sum(b.totales) as totales from (select count(distinct reg.pedido) totales 
                from registros reg, carga_agenda pro   
                where pro.pedido_id in (select pedido_id from carga_click 
                where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) 
                and pro.pedido_id = reg.pedido  
                and accion = 'Visita confirmada' 
                and reg.fecha BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') 
                and pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') 
                '$uen' '$tipo_trabajo1' '$ciudades')b ) 
                as TOTAL, 

                (select count(distinct pedido_id) 
                from carga_click pro 
                where pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') 
                and pro.pedido_id not in (select pedido_id from carga_agenda 
                where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) 
                and pro.pedido_id in (select pedido from registros where 
                accion = 'Visita confirmada' 
                and fecha BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) 
                and pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') 
                '$uen' '$tipo_trabajo1' '$ciudades') DIFERENCIA 

                from (select count(distinct reg.pedido) totales,
                (case when pro.jornada_cita = 'AM' then 'AM' 
                when pro.jornada_cita = 'PM' then 'PM' 
                else 'HF' 
                end) jornada_cita 
                from registros reg, carga_agenda pro  
                where pro.pedido_id in (select pedido_id from carga_click
                where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59'))
                and pro.pedido_id = reg.pedido  
                and accion = 'Visita confirmada' 
                and reg.fecha BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') 
                and pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')
               '$uen' '$tipo_trabajo1' '$ciudades'
                group by jornada_cita) a 
                group by a.jornada_cita");

            $sqlconfirmados->execute();

            while ($row = $sqlconfirmados->fetchAll(PDO::FETCH_ASSOC)) {

                $total_jornada          = $row['totales'];
                $jornada                = $row['jornada_cita'];
                $diferencia             = $row['DIFERENCIA'];
                $total_cargaconfirmados = $total_cargaconfirmados + $total_jornada;
                $sqlupdatecoonfirma     = $this->_DB->prepare("UPDATE jornada_estados SET `confirmados`='$total_jornada' WHERE `id_jornada`=:jornada ");
                $sqlupdatecoonfirma->execute([':jornada']);
            }
            $sqlupdateconfirmatotal = $this->_DB->prepare("UPDATE jornada_estados SET `confirmados`='$total_cargaconfirmados' WHERE `id_jornada`='TOTAL' ");
            $sqlupdatedif           = $this->_DB->prepare("UPDATE jornada_estados SET `confirmados`=:diferencia WHERE `id_jornada`='DIFERENCIA'");
            $sqlupdatedif->execute([':diferencia']);
            //sin gestionar
            $sqlnogestion = $this->_DB->query("select count(pedido_id) pendientes, (case when jornada_cita = 'AM' then 'AM' 
                when jornada_cita = 'PM' then 'PM' 
                else 'HF' 
                end) jornada_cita, 
                (select count(pedido_id) pendientes 
                from carga_agenda  pro 
                where pedido_id not in (select pedido from registros where fecha BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') ) 
                and fecha_cita  BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') 
                and pedido_id in (select pedido_id from carga_click  
                where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') )  '$uen' '$tipo_trabajo1' '$ciudades') TOTAL, 

                (select count(pedido_id) 
                from carga_click 
                where pedido_id not in (select pedido from registros 
                where fecha BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') ) 
                and fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')  
                and pedido_id not in (select pedido_id from carga_agenda 
                where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') )  '$uen' '$tipo_trabajo1' '$ciudades') DIFERENCIA 

                from carga_agenda  pro 
                where pedido_id not in (select pedido from registros where fecha BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) 
                and fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')  
                and pedido_id in (select pedido_id from carga_click  
                where fecha_cita  BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') ) 
                '$uen' '$tipo_trabajo1' '$ciudades' 
                group by (case when jornada_cita = 'AM' then 'AM' 
                when jornada_cita = 'PM' then 'PM' 
                else 'HF' end) ");

            $sqlnogestion->execute();

            while ($row = $sqlnogestion->fetchAll(PDO::FETCH_ASSOC)) {

                $total_jornada        = $row['pendientes'];
                $jornada              = $row['jornada_cita'];
                $diferencia           = $row['DIFERENCIA'];
                $total_carganogestion = $total_carganogestion + $total_jornada;
                $sqlupdatenogestion   = $this->_DB->prepare("UPDATE jornada_estados SET `no_gestionados`=:total_jornada WHERE `id_jornada`=:jornada ");
                $sqlupdatenogestion->execute([':total_jornada' => $total_jornada, ':jornada' => $jornada]);
            }
            $sqlupdatenogestiontotal = $this->_DB->prepare("UPDATE jornada_estados SET `no_gestionados`=:total_carganogestion WHERE `id_jornada`='TOTAL' ");
            $sqlupdatenogestiontotal->execute([':total_carganogestion' => $total_carganogestion]);
            $sqlupdatedif = $this->_DB->prepare("UPDATE jornada_estados 
                SET `no_gestionados`=:diferencia WHERE `id_jornada`='DIFERENCIA' ");
            $sqlupdatedif->execute([':diferencia' => $diferencia]);

            //finalizados de click
            $sqlfinalizadosclick = $this->_DB->query("select count(pro.jornada_cita) total_jornada, 
                (case when pro.jornada_cita = 'AM' then 'AM'  
                when pro.jornada_cita = 'PM' then 'PM'
                else 'HF' end) jornada,  
                ((select count(pro.jornada_cita) 
                from carga_click pro  
                where pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')
                and pedido_id in (select pedido_id from carga_agenda  
                where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) 
                and estado_id = 'Finalizada') '$uen' '$tipo_trabajo' '$ciudades') TOTAL, 

                (select count(pedido_id) 
                from carga_click pro 
                where pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') 
                and pro.pedido_id not in (select pedido_id from carga_agenda 
                where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) 
                and pro.estado_id = 'Finalizada' '$uen' '$tipo_trabajo' '$ciudades') DIFERENCIA 

                from carga_click pro  
                where pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') 
                and pedido_id in (select pedido_id from carga_agenda 
                where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) 
                and estado_id = 'Finalizada' '$uen' '$tipo_trabajo' '$ciudades' group by jornada 
                order by (case when pro.jornada_cita = 'AM' then 'AM' 
                when pro.jornada_cita = 'PM' then 'PM' else 'HF' end) ");
            $sqlfinalizadosclick->execute();

            while ($row = $sqlfinalizadosclick->fetchAll(PDO::FETCH_ASSOC)) {

                $total_jornada       = $row['total_jornada'];
                $jornada             = $row['jornada'];
                $diferencia          = $row['DIFERENCIA'];
                $total_cargafinclick = $total_cargafinclick + $total_jornada;
                $sqlupdatefinalclick = $this->_DB->prepare("UPDATE jornada_estados SET `finalizados_click`=:total_jornada WHERE `id_jornada`=:jornada");
                $sqlupdatefinalclick->execute([':total_jornada' => $total_jornada, ':jornada' => $jornada]);
            }
            $sqlupdatefinalclicktotal = $this->_DB->prepare("UPDATE jornada_estados SET `finalizados_click`=:total_cargafinclick WHERE `id_jornada`='TOTAL'");
            $sqlupdatefinalclicktotal->execute([':total_cargafinclick' => $total_cargafinclick]);
            $sqlupdatedif = $this->_DB->prepare("UPDATE jornada_estados SET `finalizados_click`=:diferencia WHERE `id_jornada`='DIFERENCIA'");
            $sqlupdatedif->execute([':diferencia' => $diferencia]);

            //Sin confirmar de click
            $sqlSinConfirmar = $this->_DB->query("select count(pro.jornada_cita) total_jornada,   
                (case when pro.jornada_cita = 'AM' then 'AM'   
                when pro.jornada_cita = 'PM' then 'PM'  
                else 'HF' end) jornada,  
                (select count(pro.jornada_cita) total_jornada    
                from carga_click pro  
                where pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') 
                and pro.pedido_id in (select pedido_id from carga_agenda  
                where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') )  
                and pro.pedido_id not in  
                (select reg.pedido 
                from registros reg, carga_agenda pro  
                where pro.pedido_id in (select pedido_id from carga_click 
                where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') ) 
                and pro.pedido_id = reg.pedido  
                and accion = 'Visita confirmada' 
                and reg.fecha BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')  
                and pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') )) 
                and pro.pedido_id not in 
                (select pedido_id	
                from carga_agenda 
                where pedido_id not in 
                (select pedido from registros where fecha BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') ) 
                and fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') 
                and pedido_id in (select pedido_id from carga_click 
                where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') )) '$uen' '$tipo_trabajo' '$ciudades') as TOTAL, 
                (select count(pro.pedido_id) 
                from carga_click pro  
                where pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') 
                and pro.pedido_id not in (select pedido_id from carga_agenda  
                where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') )  
                and pro.pedido_id not in 
                (select pedido_id 
                from carga_click pro  
                where pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')  
                and pro.pedido_id not in (select pedido_id from carga_agenda 
                where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') ) 
                and pro.pedido_id in (select pedido from registros where 
                accion = 'Visita confirmada' 
                and fecha BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') ) 
                and pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') ) 
                and pro.pedido_id not in 
                (select pedido_id 
                from carga_click  
                where pedido_id not in (select pedido from registros  
                where fecha BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') ) 
                and fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')  
                and pedido_id not in (select pedido_id from carga_agenda 
                where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') ))'$uen' '$tipo_trabajo' '$ciudades') as DIFERENCIA 
                from carga_click pro 
                where pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') 
                and pro.pedido_id in (select pedido_id from carga_agenda 
                where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') ) 
                and pro.pedido_id not in 
                (select reg.pedido 
                from registros reg, carga_agenda pro  
                where pro.pedido_id in (select pedido_id from carga_click 
                where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') ) 
                and pro.pedido_id = reg.pedido  
                and accion = 'Visita confirmada' 
                and reg.fecha BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')  
                and pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') ) 
                and pro.pedido_id not in 
                (select pedido_id 
                from carga_agenda 
                where pedido_id not in 
                (select pedido from registros where fecha 
                BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') ) 
                and fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') 
                and pedido_id in (select pedido_id from carga_click 
                where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59'))) 
                '$uen' '$tipo_trabajo' '$ciudades' group by jornada ");
            $sqlSinConfirmar->execute();


            while ($row = $sqlSinConfirmar->fetchAll(PDO::FETCH_ASSOC)) {

                $total_jornada         = $row['total_jornada'];
                $jornada               = $row['jornada'];
                $diferencia            = $row['DIFERENCIA'];
                $total_sinconfirmar    = $total_sinconfirmar + $total_jornada;
                $sqlupdatesinconfirmar = $this->_DB->prepare("UPDATE jornada_estados SET `sin_confirmar`=:totaljornada WHERE `id_jornada`=:jornada ");
                $sqlupdatesinconfirmar->execute([':totaljornada' => $total_jornada, ':jornada' => $jornada]);

            }
            $sqlupdatesinconfirmartotal = $this->_DB->prepare("UPDATE jornada_estados SET `sin_confirmar`=:total_sinconfirma  WHERE `id_jornada`='TOTAL' ");
            $sqlupdatesinconfirmartotal->execute([':total_sinconfirma' => $total_sinconfirmar]);
            $sqlupdatedif = $this->_DB->prepare("UPDATE jornada_estados SET `sin_confirmar`=:diferencia WHERE `id_jornada`='DIFERENCIA' ");
            $sqlupdatedif->execute([':diferencia' => $diferencia]);

            $query = $this->_DB->query("SELECT * FROM jornada_estados where id_jornada not in('TOTAL','DIFERENCIA')");

            $query2 = $this->_DB->query("SELECT *, ROUND((confirmados/vista_click)*100,2) eficacia, 
                ROUND((finalizados_click/agendados)*100,2) efectividad 
                FROM jornada_estados where id_jornada in ('TOTAL') ");

            $resultado2 = [];

            while ($row = $query2->fetchAll(PDO::FETCH_ASSOC)) {
                $resultado2[] = $row;
            }

            $query3 = $this->_DB->query("SELECT * FROM jornada_estados where id_jornada in ('DIFERENCIA') ");

            $resultado3 = [];

            while ($row = $query3->fetchAll(PDO::FETCH_ASSOC)) {
                $resultado3[] = $row;
            }

            $queryalarmados = $this->_DB->query("SELECT count(pedido_id) total FROM alarmados where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') ");
            $queryalarmados->execute();

            $counter = 0;
            if ($queryalarmados->rowCount()) {
                $result = [];
                if ($row = $queryalarmados->fetchAll(PDO::FETCH_ASSOC)) {
                    $counter = $row['total'];
                }
            }
            //para las graficas

            $query4 = $this->_DB->query("select a.final final_click, b.agenda agendados, a.fecha_cita fecha, c.click click from   

                (select count(pro.pedido_id) agenda, pro.fecha_cita  
                from carga_agenda pro 
                where pro.fecha_cita  BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') 
                '$tipo_trabajo1' '$uen' '$ciudades'
                group by pro.fecha_cita) b, 

                (select count(jornada_cita) final, 
                fecha_cita from carga_click click 
                where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') 
               and pedido_id in (select pedido_id from carga_agenda   
               where fecha_cita = click.fecha_cita)  
                and estado_id='Finalizada' '$tipo_trabajo' '$uen' '$ciudades' group by fecha_cita) a, 

                (select count(jornada_cita) click, pro.fecha_cita  
                from carga_click pro 
                where pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') 
                and pedido_id in (select pedido_id from carga_agenda  
                where fecha_cita = pro.fecha_cita) '$tipo_trabajo' '$uen' '$ciudades' group by pro.fecha_cita) c  
                where a.fecha_cita = c.fecha_cita 
                and a.fecha_cita = b.fecha_cita 
                group by a.fecha_cita ");
            $query4->execute();


            if ($query4->rowCount()) {
                $fecha       = [];
                $click       = [];
                $agendados   = [];
                $final_click = [];
                $i           = 1;
                while ($row = $query4->fetchAll(PDO::FETCH_ASSOC)) {

                    $date     = $row['fecha'];
                    $en_click = $row['click'];
                    $agenda   = $row['agendados'];
                    $finaliza = $row['final_click'];

                    $fecha[]       = ["label" => "$date"];
                    $click[]       = ["value" => "$en_click"];
                    $agendados[]   = ["value" => "$agenda"];
                    $final_click[] = ["value" => "$finaliza"];
                    $i++;
                }
            }
            //fin de la graficas

            if ($query->rowCount()) {

                $resultado = [];

                while ($row = $query->fetchAll(PDO::FETCH_ASSOC)) {

                    $resultado[] = $row;

                }

                $response = [$resultado, $resultado2, $resultado3, $fecha, $click, $agendados, $final_click, $counter, 201];

            } else {
                $response = ['', 400];

            }
        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        $this->_DB = null;
        echo json_encode($response);
    }

    public function getRegistrosCarga()
    {
        try {

            $query = $this->_DB->query("select a.id, a.nombre_archivo, a.tipo, a.fecha_carga, a.login,(select  
                CASE WHEN a.tipo = 'alarmados' THEN (select count(c.pedido_id) 
                from alarmados c where a.id=c.id_archivo) 
                else (select count(b.pedido_id) 
                from carga_agenda b where a.id=b.archivo_id) 
                END) TOTAL_REGISTROS 
                FROM carga_archivos a order by fecha_carga DESC limit 10");
            $query->execute();

            if ($query->rowCount()) {

                $resultado = [];

                while ($row = $query->fetchAll(PDO::FETCH_ASSOC)) {

                    if ($row['tipo'] == "vistaCliente") {
                        $row['tipo'] = "Carga Preagenda";
                    } elseif ($row['tipo'] == "alarmados") {
                        $row['tipo'] = "Alarmados";
                    }

                    $resultado[] = $row;

                }
                $response = [$resultado, 201];

            } else {
                $response = ['', 400];
            }
        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        $this->_DB = null;
        echo json_encode($response);
    }

    public function getDemePedidoEncuesta()
    {
        try {

            $query = $this->_DB->query("select max(fecha_instalacion) fecha from nps ");

            $query->execute();

            $fecha = date("Y-m-d");

            if ($query->rowCount()) {
                $result = [];
                if ($row = $query->fetchAll(PDO::FETCH_ASSOC)) {
                    $fecha = $row['fecha'];
                }
            }

            $dia  = substr($fecha, 8, 2);
            $mes  = substr($fecha, 5, 2);
            $anio = substr($fecha, 0, 4);

            $nom_mes   = date('M', mktime(0, 0, 0, $mes, $dia, $anio));
            $semana    = "Semana " . date('W', mktime(0, 0, 0, $mes, $dia, $anio));
            $diaSemana = date("w", mktime(0, 0, 0, $mes, $dia, $anio));

            $query = $this->_DB->query("select idnps, telefono, cedula, detalle, fecha_instalacion, departamento, municipio, contratista, Interfaz, respuesta, semana 
                from nps 
                where semana = (select max(semana) from nps) 
                and num_pregunta = '4' 
                and num_respuesta in ('1','2','3')  
                and gestion_dolores is null or gestion_dolores =' ' 
                order by fecha_instalacion ASC, respuesta ASC limit 1 ");
            $query->execute();

            //echo $this->mysqli->query($sqlLogin);
            //
            if ($query->rowCount()) {

                $resultado = [];

                while ($row = $query->fetchAll(PDO::FETCH_ASSOC)) {

                    $idgestion_dolores = $row['idnps'];

                    $query = $this->_DB->query("UPDATE nps SET gestion_dolores ='1' WHERE idnps='$idgestion_dolores' ");
                    $query->execute();

                    $row['municipio']    = utf8_encode($row['municipio']);
                    $row['departamento'] = utf8_encode($row['departamento']);
                    $resultado[]         = $row;

                }
                $response = [$resultado, 201];

            } else {
                $response = ['', 400];

            }
        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        $this->_DB = null;
        echo json_encode($response);
    }

    public function resumenSemanas($data)
    {
        try {
            $datos      = $data['pregunta'];
            $pregunta   = $datos['pregunta'];
            $mesenviado = $data['mes'];

            if ($mesenviado == "" || $mesenviado == undefined) {

                $query = $this->_DB->query("select max(fecha_instalacion) fecha from nps ");

                $query->execute();

                $fecha = date("Y-m-d");

                if ($query->rowCount()) {
                    $result = [];
                    if ($row = $query->fetchAll(PDO::FETCH_ASSOC)) {
                        $fecha = $row['fecha'];
                    }
                }

                $dia  = substr($fecha, 8, 2);
                $mes  = substr($fecha, 5, 2);
                $anio = substr($fecha, 0, 4);

                $nom_mes   = date('M', mktime(0, 0, 0, $mes, $dia, $anio));
                $semana    = "Semana " . date('W', mktime(0, 0, 0, $mes, $dia, $anio));
                $diaSemana = date("w", mktime(0, 0, 0, $mes, $dia, $anio));

            } else {
                $nom_mes   = $mesenviado;
                $semana    = "Semana " . date('W', mktime(0, 0, 0, $mes, $dia, $anio));
                $diaSemana = date("w", mktime(0, 0, 0, $mes, $dia, $anio));
            }

            if ($diaSemana == 0) {
                $diaSemana = 7;
            }
            $primerDia = date("d-m-Y", mktime(0, 0, 0, $mes, $dia - $diaSemana + 1, $anio));
            $ultimoDia = date("d-m-Y", mktime(0, 0, 0, $mes, $dia + (7 - $diaSemana), $anio));

            if ($pregunta == "1" || $pregunta == "6" || $pregunta == "7" || $pregunta == "8") {

                $query = "round((select count(num_respuesta) 
                    from nps 
                    where num_respuesta = '1' and num_pregunta = '$pregunta' and mes = gen.mes and semana = gen.semana)/  
                    (select count(num_pregunta)   
                    from nps where semana = gen.semana and mes = gen.mes and num_pregunta = '$pregunta' limit 1 )*100, 2) as SI,   
                    round((select count(num_respuesta)   
                    from nps   
                    where num_respuesta = '2' and num_pregunta = '$pregunta' and mes = gen.mes and semana = gen.semana)/  
                    (select count(num_pregunta)   
                    from nps where semana = gen.semana and mes = gen.mes and num_pregunta = '$pregunta' limit 1 )*100, 2) as NO ";

            } else {

                $query = "round((select count(num_respuesta) 
                    		from nps 
                            where num_respuesta = '1' and num_pregunta = '$pregunta' and mes = gen.mes and semana = gen.semana)/ 
                            (select count(num_pregunta) 
                    from nps where semana = gen.semana and mes = gen.mes and num_pregunta = '$pregunta' limit 1 )*100, 2) as NO,  
                    round((select count(num_respuesta) 
                    		from nps  
                            where num_respuesta = '2' and num_pregunta = '$pregunta' and mes = gen.mes and semana = gen.semana)/ 
                            (select count(num_pregunta)  
                    from nps where semana = gen.semana and mes = gen.mes and num_pregunta = '$pregunta' limit 1 )*100, 2) as PROBNO, 
                    round((select count(num_respuesta)  
                    		from nps  
                            where num_respuesta = '3' and num_pregunta = '$pregunta' and mes = gen.mes and semana = gen.semana)/ 
                            (select count(num_pregunta)  
                    from nps where semana = gen.semana and mes = gen.mes and num_pregunta = '$pregunta' limit 1 )*100, 2) as NOSEGURO, 
                    round((select count(num_respuesta)  
                    		from nps 
                            where num_respuesta = '4' and num_pregunta = '$pregunta' and mes = gen.mes and semana = gen.semana)/ 
                            (select count(num_pregunta)  
                    from nps where semana = gen.semana and mes = gen.mes and num_pregunta = '$pregunta' limit 1 )*100, 2) as PROBSI, 
                    round((select count(num_respuesta)  
                    		from nps 
                            where num_respuesta = '5' and num_pregunta = '$pregunta' and mes = gen.mes and semana = gen.semana)/ 
                            (select count(num_pregunta)  
                    from nps where semana = gen.semana and mes = gen.mes and num_pregunta = '$pregunta' limit 1 )*100, 2) as SI, 
                    round(round((select count(num_respuesta)  
                    from nps where num_respuesta = '5' and num_pregunta = '$pregunta' and mes = gen.mes and semana = gen.semana)-(select count(respuesta) 
                    from nps where num_respuesta = '3' and num_pregunta = '$pregunta' and mes = gen.mes and semana = gen.semana)-(select count(respuesta) 
                    from nps where num_respuesta = '2' and num_pregunta = '$pregunta' and mes = gen.mes and semana = gen.semana)-(select count(respuesta) 
                    from nps where num_respuesta = '1' and num_pregunta = '$pregunta' and mes = gen.mes and semana = gen.semana))/(select count(respuesta) 
                    from nps where num_pregunta = '$pregunta' and semana = gen.semana)*100, 2) as NPS ";

            }

            $sql = $this->_DB->query("select gen.semana, 
                 $query 
                from nps gen 
                where mes = '$nom_mes' 
                group by gen.semana order by semana desc ");

            $sql->execute();

            //echo $this->mysqli->query($sqlLogin);
            //
            if ($sql->rowCount()) {
                $categorias = [];
                $resultado  = [];
                if ($pregunta == "1" || $pregunta == "6" || $pregunta == "7" || $pregunta == "8") {
                    $si = [];
                    $no = [];

                    while ($row = $sql->fetchAll(PDO::FETCH_ASSOC)) {
                        $year       = date("Y");
                        $week       = substr($row['semana'], 7);
                        $diaInicial = date('Y-m-d', strtotime($year . 'W' . $week));
                        $diaFinal   = date('Y-m-d', strtotime($diaInicial . "+ 6 days"));

                        $row["fechaInic"] = $diaInicial;
                        $row["fechaFin"]  = $diaFinal;

                        $resultado[] = $row;
                        $label       = $row['semana'];
                        $resno       = $row['NO'];
                        $ressi       = $row['SI'];

                        $categorias[] = ["label" => "$label"];
                        $no[]         = ["value" => "$resno"];
                        $si[]         = ["value" => "$ressi"];
                    }

                } else {
                    $no       = [];
                    $probno   = [];
                    $noseguro = [];
                    $probsi   = [];
                    $si       = [];

                    while ($row = $sql->fetchAll(PDO::FETCH_ASSOC)) {

                        $year       = date("Y");
                        $week       = substr($row['semana'], 7);
                        $diaInicial = date('Y-m-d', strtotime($year . 'W' . $week));
                        $diaFinal   = date('Y-m-d', strtotime($diaInicial . "+ 6 days"));

                        $row["fechaInic"] = $diaInicial;
                        $row["fechaFin"]  = $diaFinal;
                        $resultado[]      = $row;
                        $label            = $row['semana'];
                        $resno            = $row['NO'];
                        $prono            = $row['PROBNO'];
                        $nosegur          = $row['NOSEGURO'];
                        $prosi            = $row['PROBSI'];
                        $ressi            = $row['SI'];

                        $categorias[] = ["label" => "$label"];
                        $no[]         = ["value" => "$resno"];
                        $probno[]     = ["value" => "$prono"];
                        $noseguro[]   = ["value" => "$nosegur"];
                        $probsi[]     = ["value" => "$prosi"];
                        $si[]         = ["value" => "$ressi"];
                    }
                }

                $sql1 = $this->_DB->query("select round(((select count(num_respuesta) 
                    from nps where num_pregunta = '$pregunta' and num_respuesta = '5' and mes = '$nom_mes')-(select count(respuesta) 
                    from nps where num_pregunta = '$pregunta' and num_respuesta = '3' and mes = '$nom_mes')-(select count(respuesta) 
                    from nps where num_pregunta = '$pregunta' and num_respuesta = '2' and mes = '$nom_mes')-(select count(respuesta) 
                    from nps where num_pregunta = '$pregunta' and num_respuesta = '1' and mes = '$nom_mes'))/(select count(respuesta) 
                    from nps where num_pregunta = '$pregunta' and mes = '$nom_mes')*100, 2) as NPS ");
                $sql1->execute();

                $NPSAcumulado = 0;
                $result       = [];
                if ($row = $sql1->fetchAll(PDO::FETCH_ASSOC)) {
                    $NPSAcumulado = $row['NPS'];
                }

                $Query = $this->_DB->query("select gen.respuesta, count(gen.num_respuesta) total, 
                    round((count(gen.num_respuesta)/(select count(num_pregunta) 
                    from nps where num_pregunta = '$pregunta' and mes = gen.mes limit 1 )) *100, 2) as porcentaje 
                    from nps gen 
                    where gen.num_pregunta = '$pregunta'
                    and mes = '$nom_mes' 
                    group by gen.num_respuesta ");

                $Query->execute();

                $resultadorespuestas = [];

                while ($row = $Query->fetchAll(PDO::FETCH_ASSOC)) {
                    $resultadorespuestas[] = $row;
                }

                if ($pregunta == "1" || $pregunta == "6" || $pregunta == "7" || $pregunta == "8") {

                    $querydiario = "round((select count(num_respuesta) 
                        from nps
                        where num_respuesta = '1' and num_pregunta = '$pregunta' and mes=gen.mes and fecha_instalacion = gen.fecha_instalacion)/ 
                        (select count(num_pregunta)
                        from nps where num_pregunta = '$pregunta'  and mes=gen.mes 
                        and fecha_instalacion = gen.fecha_instalacion limit 1 )*100, 2) as SI,
                        round((select count(num_respuesta)  
                        from nps  
                        where num_respuesta = '2' and num_pregunta = '$pregunta' and mes=gen.mes and fecha_instalacion = gen.fecha_instalacion)/ 
                        (select count(num_pregunta)  
                        from nps where num_pregunta = '$pregunta'  and mes=gen.mes 
                        and fecha_instalacion = gen.fecha_instalacion limit 1 )*100, 2) as NO ";
                } else {

                    $querydiario = "round((select count(num_respuesta) 
                        from nps
                        where num_respuesta = '1' and num_pregunta = '$pregunta' and mes=gen.mes and fecha_instalacion = gen.fecha_instalacion)/ 
                        (select count(num_pregunta)
                        from nps where num_pregunta = '$pregunta' and mes=gen.mes 
                        and fecha_instalacion = gen.fecha_instalacion limit 1 )*100, 2) as NO,
                        round((select count(num_respuesta)  
                        from nps
                        where num_respuesta = '2' and num_pregunta = '$pregunta' and mes=gen.mes and fecha_instalacion = gen.fecha_instalacion)/ 
                        (select count(num_pregunta)
                        from nps where num_pregunta = '$pregunta' and mes=gen.mes 
                        and fecha_instalacion = gen.fecha_instalacion limit 1 )*100, 2) as PROBNO,  
                        round((select count(num_respuesta)  
                        from nps  
                        where num_respuesta = '3' and num_pregunta = '$pregunta' and mes=gen.mes and fecha_instalacion = gen.fecha_instalacion)/ 
                        (select count(num_pregunta)  
                        from nps where num_pregunta = '$pregunta' and mes=gen.mes 
                        and fecha_instalacion = gen.fecha_instalacion limit 1 )*100, 2) as NOSEGURO, 
                        round((select count(num_respuesta)  
                        from nps  
                        where num_respuesta = '4' and num_pregunta = '$pregunta' and mes=gen.mes and fecha_instalacion = gen.fecha_instalacion)/ 
                        (select count(num_pregunta)  
                        from nps where num_pregunta = '$pregunta' and mes=gen.mes  
                        and fecha_instalacion = gen.fecha_instalacion limit 1 )*100, 2) as PROBSI, 
                        round((select count(num_respuesta)  
                        from nps  
                        where num_respuesta = '5' and num_pregunta = '$pregunta' and mes=gen.mes and fecha_instalacion = gen.fecha_instalacion)/ 
                        (select count(num_pregunta)  
                        from nps where num_pregunta = '$pregunta' and mes=gen.mes  
                        and fecha_instalacion = gen.fecha_instalacion limit 1 )*100, 2) as SI  ";
                }

                $sqlDiario = $this->_DB->query("select gen.fecha_instalacion dia, 
                    $querydiario 
                    from nps gen 
                    where gen.num_pregunta = '$pregunta' 
                    and gen.mes = '$nom_mes' 
                    group by gen.fecha_instalacion order by gen.fecha_instalacion ");

                $sqlDiario->execute();
                //echo $sqlDiario;
                $resultadoDiario = [];

                if ($sqlDiario->rowCount()) {
                    $dias            = [];
                    $resultadoDiario = [];
                    if ($pregunta == "1" || $pregunta == "6" || $pregunta == "7" || $pregunta == "8") {
                        $diasi = [];
                        $diano = [];

                        while ($row = $sqlDiario->fetchAll(PDO::FETCH_ASSOC)) {
                            $resultadoDiario[] = $row;
                            $label             = $row['dia'];
                            $diaresno          = $row['NO'];
                            $diaressi          = $row['SI'];

                            $dias[]  = ["label" => "$label"];
                            $diano[] = ["value" => "$diaresno"];
                            $diasi[] = ["value" => "$diaressi"];
                        }

                    } else {
                        $diano       = [];
                        $diaprobno   = [];
                        $dianoseguro = [];
                        $diaprobsi   = [];
                        $diasi       = [];

                        while ($row = $sqlDiario->fetchAll(PDO::FETCH_ASSOC)) {
                            $resultadoDiario[] = $row;
                            $label             = $row['dia'];
                            $diaresno          = $row['NO'];
                            $diaprono          = $row['PROBNO'];
                            $dianosegur        = $row['NOSEGURO'];
                            $diaprosi          = $row['PROBSI'];
                            $diaressi          = $row['SI'];

                            $dias[]        = ["label" => "$label"];
                            $diano[]       = ["value" => "$diaresno"];
                            $diaprobno[]   = ["value" => "$diaprono"];
                            $dianoseguro[] = ["value" => "$dianosegur"];
                            $diaprobsi[]   = ["value" => "$diaprosi"];
                            $diasi[]       = ["value" => "$diaressi"];
                        }
                    }
                }

                if ($pregunta == "1" || $pregunta == "6" || $pregunta == "7" || $pregunta == "8") {

                    $querydepartamento = "round((select count(num_respuesta)  
                        from nps
                        where num_respuesta = '1' and num_pregunta = '$pregunta'  and mes=gen.mes and regional = gen.regional)/ 
                        (select count(num_pregunta)
                        from nps where regional = gen.regional and mes=gen.mes and num_pregunta = '$pregunta'  limit 1 )*100, 2) as SI, 
                        round((select count(num_respuesta)
                        from nps  
                        where num_respuesta = '2' and num_pregunta = '$pregunta' and mes=gen.mes and regional = gen.regional)/
                        (select count(num_pregunta)  
                        from nps where regional = gen.regional and mes=gen.mes and num_pregunta = '$pregunta' limit 1 )*100, 2) as NO ";
                } else {

                    $querydepartamento = "round((select count(num_respuesta)  
                        from nps
                        where num_respuesta = '1' and num_pregunta = '$pregunta' and mes=gen.mes and regional = gen.regional)/ 
                        (select count(num_pregunta)
                        from nps where regional = gen.regional and mes=gen.mes and num_pregunta = '$pregunta'  limit 1 )*100, 2) as NO,  
                        round((select count(num_respuesta)
                        from nps  
                        where num_respuesta = '2' and num_pregunta = '$pregunta' and mes=gen.mes  and regional = gen.regional)/
                        (select count(num_pregunta)  
                        from nps where regional = gen.regional and mes=gen.mes and num_pregunta = '$pregunta' limit 1 )*100, 2) as PROBNO,
                        round((select count(num_respuesta)  
                        from nps
                        where num_respuesta = '3' and num_pregunta = '$pregunta' and mes=gen.mes and regional = gen.regional)/ 
                        (select count(num_pregunta)
                        from nps where regional = gen.regional and mes=gen.mes and num_pregunta = '$pregunta' limit 1 )*100, 2) as NOSEGURO,  
                        round((select count(num_respuesta)
                        from nps 
                        where num_respuesta = '4' and num_pregunta = '$pregunta' and mes=gen.mes and regional = gen.regional)/
                        (select count(num_pregunta)  
                        from nps where regional = gen.regional and mes=gen.mes and num_pregunta = '$pregunta' limit 1 )*100, 2) as PROBSI,
                        round((select count(num_respuesta) 
                        from nps
                        where num_respuesta = '5' and num_pregunta = '$pregunta' and mes=gen.mes and regional = gen.regional)/
                        (select count(num_pregunta)
                        from nps where regional = gen.regional and mes=gen.mes and num_pregunta = '$pregunta' limit 1 )*100, 2) as SI, 
                        round(((select count(num_respuesta)
                        from nps where num_respuesta = '5' and num_pregunta = '$pregunta' and mes = gen.mes and regional = gen.regional)
                        -(select count(num_respuesta)
                        from nps where num_respuesta = '1' and num_pregunta = '$pregunta' and mes = gen.mes and regional = gen.regional) 
                        -(select count(num_respuesta) 
                        from nps where num_respuesta = '2' and num_pregunta = '$pregunta' and mes = gen.mes and regional = gen.regional) 
                        -(select count(num_respuesta) 
                        from nps where num_respuesta = '3' and num_pregunta = '$pregunta' and mes = gen.mes and regional = gen.regional))/ 
                        (select count(num_respuesta) 
                        from nps where num_pregunta = '4' and mes = gen.mes and regional = gen.regional)*100,2) as NPS ";
                }

                $sqlDepartamento = $this->_DB->query("select gen.regional regional, 
                    $querydepartamento 
                    from nps gen 
                    where gen.mes = '$nom_mes' 
                    group by gen.regional order by gen.regional ");

                $sqlDepartamento->execute();

                $resultadoDepartamento = [];

                while ($row = $sqlDepartamento->fetchAll(PDO::FETCH_ASSOC)) {
                    $row['regional']         = utf8_encode($row['regional']);
                    $resultadoDepartamento[] = $row;
                }

                if ($pregunta == "1" || $pregunta == "6" || $pregunta == "7" || $pregunta == "8") {

                    $valoresSemana = "(select count(num_respuesta) 
                        from nps  
                        where num_respuesta = '1' and num_pregunta = '$pregunta' and mes=gen.mes and semana = gen.semana ) as SI, 
                        (select count(num_respuesta) from nps  
                        where num_respuesta = '2' and num_pregunta = '$pregunta' and mes=gen.mes and semana = gen.semana ) as NO, 
                        (select count(num_respuesta)  
                        from nps where semana = gen.semana and mes=gen.mes and num_pregunta = '$pregunta' ) as TOTAL ";
                } else {

                    $valoresSemana = "(select count(num_respuesta) 
                        from nps  
                        where num_respuesta = '1' and num_pregunta = '$pregunta' and semana = gen.semana and mes = gen.mes) as NO,   
                        (select count(num_respuesta)  from nps  
                        where num_respuesta = '2' and num_pregunta = '$pregunta' and semana = gen.semana and mes = gen.mes) as PROBNO,  
                        (select count(num_respuesta)  from nps   
                        where num_respuesta = '3' and num_pregunta = '$pregunta' and semana = gen.semana and mes = gen.mes) as NOSEGURO,  
                        (select count(num_respuesta)  from nps  
                        where num_respuesta = '4' and num_pregunta = '$pregunta' and semana = gen.semana and mes = gen.mes) as PROBSI,  
                        (select count(num_respuesta)  from nps  
                        where num_respuesta = '5' and num_pregunta = '$pregunta' and semana = gen.semana and mes = gen.mes) as SI, 
                        (select count(num_respuesta)  
                        from nps where semana = gen.semana and num_pregunta = '$pregunta' and mes = gen.mes) as TOTAL ";
                }

                $sqlValoresSemana = $this->_DB->query("select gen.semana,  
                    $valoresSemana 
                    from nps gen 
                    where gen.mes = '$nom_mes' 
                    group by gen.semana order by gen.semana desc ");

                $sqlValoresSemana->execute();

                $resultadoValSemana = [];

                while ($row = $sqlValoresSemana->fetchAll(PDO::FETCH_ASSOC)) {

                    $year       = date("Y");
                    $week       = substr($row['semana'], 7);
                    $diaInicial = date('Y-m-d', strtotime($year . 'W' . $week));
                    $diaFinal   = date('Y-m-d', strtotime($diaInicial . "+ 6 days"));

                    $row["fechaInic"] = $diaInicial;
                    $row["fechaFin"]  = $diaFinal;

                    $resultadoValSemana[] = $row;
                }

                if ($pregunta == "1" || $pregunta == "6" || $pregunta == "7" || $pregunta == "8") {

                    $querycontrato = "round((select count(contratista) 
                        from nps  
                        where num_respuesta = '1' and num_pregunta = '$pregunta' and mes=gen.mes and contratista = gen.contratista)/ 
                        (select count(contratista)  
                        from nps where contratista = gen.contratista and mes=gen.mes and num_pregunta = '$pregunta' limit 1 )*100, 2) as SI,  
                        round((select count(contratista)  
                        from nps  
                        where num_respuesta = '2' and num_pregunta = '$pregunta' and mes=gen.mes and contratista = gen.contratista)/ 
                        (select count(contratista)  
                        from nps where contratista = gen.contratista and mes=gen.mes and num_pregunta = '$pregunta' limit 1 )*100, 2) as NO ";
                } else {

                    $querycontrato = "round((select count(contratista) 
                        from nps  
                        where num_respuesta = '1' and num_pregunta = '$pregunta' and mes=gen.mes and contratista = gen.contratista)/ 
                        (select count(contratista)  
                        from nps where contratista = gen.contratista and mes=gen.mes and num_pregunta = '$pregunta' limit 1 )*100, 2) as NO,  
                        round((select count(contratista)   
                        from nps 
                        where num_respuesta = '2' and num_pregunta = '$pregunta' and mes=gen.mes and contratista = gen.contratista)/ 
                        (select count(contratista)  
                        from nps where contratista = gen.contratista and mes=gen.mes and num_pregunta = '$pregunta' limit 1 )*100, 2) as PROBNO,  
                        round((select count(contratista)  
                        from nps  
                        where num_respuesta = '3' and num_pregunta = '$pregunta' and mes=gen.mes and contratista = gen.contratista)/ 
                        (select count(contratista)  
                        from nps where contratista = gen.contratista and mes=gen.mes and num_pregunta = '$pregunta' limit 1 )*100, 2) as NOSEGURO,  
                        round((select count(contratista)  
                        from nps  
                        where num_respuesta = '4' and num_pregunta = '$pregunta' and mes=gen.mes and contratista = gen.contratista)/ 
                        (select count(contratista)  
                        from nps where contratista = gen.contratista and mes=gen.mes and num_pregunta = '$pregunta' limit 1 )*100, 2) as PROBSI,  
                        round((select count(contratista)  
                        from nps  
                        where num_respuesta = '5' and num_pregunta = '$pregunta' and mes=gen.mes and contratista = gen.contratista)/ 
                        (select count(contratista)  
                        from nps where contratista = gen.contratista and mes=gen.mes and num_pregunta = '$pregunta' limit 1 )*100, 2) as SI ";
                }

                $SqlContrato = $this->_DB->query("select gen.contratista contrato, 
                    $querycontrato 
                    from  nps gen 
                    where gen.mes = '$nom_mes' 
                    group by gen.contratista ");

                $SqlContrato->execute();

                $resultadoContrato = [];

                while ($row = $SqlContrato->fetchAll(PDO::FETCH_ASSOC)) {
                    $resultadoContrato[] = $row;
                }
                $response = [
                    $resultado,
                    $NPSAcumulado,
                    $resultadorespuestas,
                    $resultadoDiario,
                    $resultadoDepartamento,
                    $resultadoContrato,
                    $categorias,
                    $no,
                    $probno,
                    $noseguro,
                    $probsi,
                    $si,
                    $dias,
                    $diano,
                    $diaprobno,
                    $dianoseguro,
                    $diaprobsi,
                    $diasi,
                    $resultadoValSemana,
                    $nom_mes,
                    201,
                ];

            } else {
                $response = ['', 400];
            }

        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        $this->_DB = null;
        echo json_encode($response);
    }

    public function listadoTecnicos($data)
    {
        try {
            $pagina   = $data['page'] ?? 1;
            $concepto = $data['concepto'];
            $tecnico  = $data['tecnico'];
            /*if ($pagina == "undefined") {
                $pagina = "0";
            } else {
                $pagina = $pagina - 1;
            }*/

            $pagina = $pagina * 100;

            if ($concepto == 'nombre') {
                $parametro = " and nombre LIKE '%$tecnico%'";
            } elseif ($concepto == 'identificacion') {
                $parametro = " and identificacion = '$tecnico'";
            } elseif ($concepto == 'ciudad') {
                $parametro = " and ciudad = '$tecnico'";
            } elseif ($concepto == 'celuar') {
                $parametro = " and celular = '$tecnico'";
            };

            $query = $this->_DB->query("select a.ID, a.IDENTIFICACION, a.NOMBRE, a.CIUDAD, a.CELULAR,  a.empresa, 
             (select b.nombre from empresas b where b.id=a.empresa) as NOM_EMPRESA 
             from tecnicos a 
             where 1 = 1 $parametro");

            $queryCount = $this->_DB->query("select count(*) as Cantidad from tecnicos h where 1 = 1 $parametro");
            //echo $query;
            $queryCount->execute();

            $counter = 0;
            if ($queryCount->rowCount()) {
                $row     = $queryCount->fetchAll(PDO::FETCH_ASSOC);
                $counter = $row[0]['Cantidad'];
            }
            //echo $this->mysqli->query($sqlLogin);
            //
            $query->execute();

            if ($query->rowCount()) {
                $response = [$query->fetchAll(PDO::FETCH_ASSOC), $counter, 201];

            } else {
                $response = ['', 400];
            }
        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        $this->_DB = null;
        echo json_encode($response);
    }

    public function buscarPedidoContingencias($data)
    {
        try {
            $pedido = $data;

            if ($pedido !== "") {

                $query = $this->_DB->prepare("SELECT pedido, accion, ciudad, correo, macEntra, macSale, paquetes, motivo, proceso, producto, contrato, perfil,
						horagestion, logindepacho,	logincontingencia, loginContingenciaPortafolio, horacontingencia, horaContingenciaPortafolio,
						tipoEquipo, tecnologia, remite, tipificacion, tipificacionPortafolio, acepta, aceptaPortafolio, observacion, observContingencia,
						observContingenciaPortafolio, ingresoEquipos
						FROM contingencias
						WHERE pedido = :pedido
					");

                $query->execute([':pedido' => $pedido]);

                if ($query->rowCount()) {
                    $resultado = $query->fetchAll(PDO::FETCH_ASSOC);

                    $response = [$resultado, 201];
                } else {
                    $response = ['', 400];
                }
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
