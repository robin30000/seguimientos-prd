<?php
require_once '../class/conection.php';
//ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('America/Bogota');

class modelNivelacion
{
    private $_DB;

    public function __construct()
    {
        $this->_DB = new Conection();
    }

    public function saveTicket($data)
    {

        try {

            var_dump($data);
        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }

    }

    public function searchIdTecnic($data)
    {


        $stmt = $this->_DB->prepare("select nombre from tecnicos where identificacion = :id");
        $stmt->execute(array(':id' => $data));
        if ($stmt->rowCount()) {
            $result   = $stmt->fetch(PDO::FETCH_OBJ);
            $response = array('state' => 1, 'data' => $result);
        } else {
            $response = array('state' => 0, 'msj' => 'No se encontraron datos');
        }

        $this->_DB = null;
        echo json_encode($response);
    }

    public function saveNivelation($data)
    {
        $login = $data['login'];
        $data = $data['datos'];

        switch ($data['solicitud']) {
            case '1':
                $solicitud = 'Abrir';
                break;
            case'2':
                $solicitud = 'Asignar';
                break;
            case'3':
                $solicitud = 'Despachar';
                break;
            default:
                $solicitud = '';
        }

        /*$solicitud = match ($data->solicitud) {
            '1' => 'Abrir',
            '2' => 'Asignar',
            '3' => 'Despachar',
            default => '',
        };*/

        switch ($data['motivo']) {
            case '1':
                $motivo = 'Cubrir Novedad';
                break;
            case'2':
                $motivo = 'Ruta Atrasada';
                break;
            case'3':
                $motivo = 'Desplazamiento Largo';
                break;
            case'4':
                $motivo = 'Microzona errada';
                break;
            case'5':
                $motivo = 'Trabajo Futuro';
                break;
            case'6':
                $motivo = 'Retraso en la mesa de soporte';
                break;
            case'7':
                $motivo = 'Pedido amarillo';
                break;
            case'8':
                $motivo = 'Reabrir pedido';
                break;
            case'9':
                $motivo = 'Pedido cancelado';
                break;
            case'10':
                $motivo = 'Inicio despues de las 9:00am';
                break;
            case'11':
                $motivo = 'Pedido Abierto';
                break;
            case'12':
                $motivo = 'Técnico no es del proceso';
                break;
            case'13':
                $motivo = 'Click no despachado';
                break;
            default:
                $motivo = '';
        }

        if(!isset($data['newTecName'])  || empty($data['newTecName']) || isset($data['newTecName']) == ''){
            $data['newTecName'] = 'No Aplica';
        }

        if (!isset($data['newIdTecnic'])  || empty($data['newIdTecnic']) || isset($data['newIdTecnic']) ==''){
            $data['newIdTecnic'] = 'No aplica';
        }

        /*$motivo = match ($data->motivo) {
            '1' => 'Cubrir Novedad',
            '2' => 'Ruta Atrazada',
            '3' => 'Desplazamiento Largo',
            '4' => 'Microzona errada',
            '5' => 'Trabajo Futuro',
            '6' => 'Retrazo en la mesa de soporte',
            '7' => 'Pedido amarillo',
            '8' => 'Reabrir pedido',
            '9' => 'Pedido cancelado',
            '10' => 'Inicio despues de las 9:00am',
            '11' => 'Abrir Pedido',
            '12' => 'Técnico no es del proceso',
            default => '',
        };*/

        $submotivo = isset($data['submotivo']) ? $data['submotivo'] : '';

        switch ($submotivo) {
            case '1':
                $submotivo = 'Contingencia';
                break;
            case '2':
                $submotivo = 'Auditoria NAP';
                break;
            case '3':
                $submotivo = 'Auditoria TAP';
                break;
            case '4':
                $submotivo = 'Soporte Gpon';
                break;
            case '5':
                $submotivo = 'Escalamiento infraestructura';
                break;
            case '6':
                $submotivo = 'Unidad residencial';
                break;
            case '7':
                $submotivo = 'Ejecución/Reinstalación';
                break;
        }

        /*$submotivo = match ($submot) {
            '1' => 'Contingencia',
            '2' => 'Auditoria NAP',
            '3' => 'Auditoria TAP',
            '4' => 'Soporte Gpon',
            '5' => 'Escalamiento infraestructura',
            '6' => 'Unidad residencial',
            '7' => 'Ejecución/Reinstalación',
            default => '',
        };*/


        $stmt = $this->_DB->prepare("SELECT * FROM nivelacion WHERE ticket_id = :id and estado = 0 or estado = 1");
        $stmt->execute(array(':id' => $data['ticket']));

        if ($stmt->rowCount()){
            $response = array('state' => 0, 'msj' => 'La tarea ingresada se encuentra en gestión y no se a dado respuesta');
            echo json_encode($response);
            exit();
        }



        $stmt = $this->_DB->prepare("INSERT INTO nivelacion (ticket_id, nombre_tecnico, cc_tecnico, pedido, proceso, zona, zubzona, cc_nuevo_tecnico,
                                                                        nombre_nuevo_tecnico, solicitud, motivo, submotivo, fecha_ingreso, creado_por, estado, observacionVeedor )
                                                VALUES (:ticket_id, :nombre_tecnico, :cc_tecnico, :pedido, :proceso, :zona, :zubzona, :cc_nuevo_tecnico,
                                                                        :nombre_nuevo_tecnico, :solicitud, :motivo, :submotivo, :fecha_ingreso, :creado_por, '0', :observacionVeedor)");
        $stmt->execute(array(
            ':ticket_id'            => $data['ticket'],
            ':nombre_tecnico'       => $data['nombreTecnico'],
            ':cc_tecnico'           => $data['idTecnico'],
            ':pedido'               => $data['pedido'],
            ':proceso'              => $data['proceso'],
            ':zona'                 => $data['zona'],
            ':zubzona'              => $data['subZona'],
            ':cc_nuevo_tecnico'     => $data['newIdTecnic'],
            ':nombre_nuevo_tecnico' => $data['newTecName'],
            ':solicitud'            => $solicitud,
            ':motivo'               => $motivo,
            ':submotivo'            => $submotivo,
            ':fecha_ingreso'        => date('Y-m-d h:i:s'),
            ':creado_por'           => $login['LOGIN'],
            ':observacionVeedor'    => $data['observacionVeedor']
        ));

        if ($stmt->rowCount() == 1) {
            $response = array('state' => 1, 'msj' => 'La solicitud de nivelación se ha creado correctamente');
        } else {
            $response = array('state' => 0, 'msj' => 'Ah ocurrido un error intentalo nuevamente');
        }

        $this->_DB = null;
        echo json_encode($response);
    }

    public function en_genstion_nivelacion($data)
    {

        $login = $data['LOGIN'];

        $stmt = $this->_DB->prepare("SELECT COUNT(*) AS total, CASE estado WHEN 1 THEN 'gestion' WHEN 2 THEN 'realizado' WHEN 0 THEN 'pendiente' END as estado
                                            FROM nivelacion 
                                            GROUP BY estado and creado_por = :login");
        $stmt->execute(array(':login' => $login));

        $tarea = $this->_DB->prepare("SELECT ticket_id, observaciones
                                            FROM nivelacion
                                            where creado_por = :login and  estado = 2");

        $tarea->execute(array(':login' => $login));

        if ($stmt->rowCount()) {
            $result    = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $res_tarea = $tarea->fetchAll(PDO::FETCH_ASSOC);
            $response  = array('gestion' => $result, 'tarea' => $res_tarea);
        } else {
            $response = array('pendiente' => 0, 'realizado' => 0);
        }

        $this->_DB = null;
        echo json_encode($response);
    }


    public function buscarhistoricoNivelacion($data)
    {

        $stmt = $this->_DB->prepare("select * from nivelacion where ticket_id = :ticket");
        $stmt->execute(array(':ticket' => $data));
        $stmt->execute();
        if ($stmt->rowCount()) {
            $result   = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $response = array('state' => 1, 'data' => $result);
        } else {
            $response = array('state' => 0, 'msj' => 'No se encontraron datos');
        }

        $this->_DB = null;
        echo json_encode($response);
    }

    public function gestionarNivelacion()
    {
        $fecha = date('Y-m-d');
        $stmt = $this->_DB->query("select n.creado_por,
                                                   n.pedido,
                                                   n.ticket_id,
                                                   n.proceso,
                                                   n.zona,
                                                   n.zubzona,
                                                   n.nombre_tecnico,
                                                   n.cc_tecnico,
                                                   n.solicitud,
                                                   n.motivo,
                                                   n.submotivo,
                                                   n.cc_nuevo_tecnico,
                                                   n.nombre_nuevo_tecnico,
                                                   n.observaciones,
                                                   n.fecha_ingreso,
                                                   n.id,
                                                   n.gestiona_por,
                                                   n.creado_por,
                                                   n.observacionVeedor
                                            from nivelacion n where n.estado != 2 and n.fecha_ingreso BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') order by n.fecha_ingreso");
        $stmt->execute();
        if ($stmt->rowCount()) {
            $result   = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $response = array('state' => 1, 'data' => $result);
        } else {
            $response = array('state' => 0);
        }

        $this->_DB = null;
        echo json_encode($response);
    }

    public function guardaNivelacion($data)
    {
        session_start();

        $id = $data['datos'];
        $login = $data['login'];

        if ($login){
            $stmt = $this->_DB->prepare("select en_gestion, gestiona_por from nivelacion where id = :id");
            $stmt->execute(array(':id' => $id['id']));
            $result = $stmt->fetch(PDO::FETCH_OBJ);
            if ($result->gestiona_por != $login['LOGIN']) {
                $response = array('state' => 0, 'msj' => "La tarea no se encuentra en gentión por el usuario actual");
            } else{
                $stmt = $this->_DB->prepare("update nivelacion set se_realiza_nivelacion = :nivelacion, observaciones = :observaciones, fecha_respuesta = :fecha_respuesta, estado = '2' where id = :id");
                $stmt->execute(array(
                    ':nivelacion'    => $id['nivelacion'],
                    ':observaciones' => $id['observaciones'],
                    ':id'            => $id['id'],
                    ':fecha_respuesta' => date('Y-m-d H:i:s'),
                ));
                if ($stmt->rowCount() == 1) {
                    $response = array('state' => 1, 'msj' => "Se a realizado el cambio de la tarea correctamente");
                } else {
                    $response = array('state' => 0, 'msj' => "Ah ocurrido un error intentalo nuevamente");
                }
            }
        }else{
            $response = array('state' => 0, 'msj' => "La tarea no se encuentra en gestion");
        }


        $this->_DB = null;
        echo json_encode($response);
    }

    public function gestionarRegistrosNivelacion()
    {
        try {
            $stmt = $this->_DB->query("select n.creado_por,
                                                       n.pedido,
                                                       n.ticket_id,
                                                       n.proceso,
                                                       n.zona,
                                                       n.zubzona,
                                                       n.nombre_tecnico,
                                                       n.cc_tecnico,
                                                       n.solicitud,
                                                       n.motivo,
                                                       n.submotivo,
                                                       n.cc_nuevo_tecnico,
                                                       n.nombre_nuevo_tecnico,
                                                       n.observaciones,
                                                       n.fecha_ingreso,
                                                       n.id,
                                                       n.se_realiza_nivelacion,
                                                       n.observacionVeedor
                                                from nivelacion n");
            $stmt->execute();
            if ($stmt->rowCount()) {
                $result   = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $response = array('state' => 1, 'data' => $result);
            } else {
                $response = array('state' => 0);
            }
        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        $this->_DB = null;
        echo json_encode($response);
    }

    public function marcarEnGestionNivelacion($data)
    {
        $id = $data['datos'];
        $login = $data['login'];

       /* $stmt = $this->_DB->prepare("select en_gestion, gestiona_por from nivelacion where id = :id");
        $stmt->execute(array(':id' => $id));*/

        $fecha = date('Y-m-d H:i:s');

        $stmt = $this->_DB->prepare("select en_gestion, gestiona_por from nivelacion where id = :id");
        $stmt->execute(array(':id' => $id));

        $resul = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($resul['gestiona_por'] == $login['LOGIN']) {

            $stmt = $this->_DB->prepare("update nivelacion set en_gestion = 0, gestiona_por = '' where id = :id");
            if ($stmt->execute(array(':id' => $id, ':user' => $login['LOGIN']))) {
                $response = array('state' => 1, 'msj' => 'La tarea se encuentra desbloqueada');
            } else {
                $response = array( 'state' => 0, 'msj' => 'Ah ocurrido un error intentalo nuevamente');
            }

        } elseif ($resul['gestiona_por'] == '') {

            $stmt = $this->_DB->prepare("update nivelacion set gestiona_por = :gestion, estado = 1, fecha_gestion = :fecha_gestion, en_gestion = 1 where id = :id");
            $stmt->execute(array(':gestion' => $login['LOGIN'], ':id' => $id, ':fecha_gestion' => $fecha));
            if ($stmt->rowCount() == 1){
                $response = array('state' => 1, 'msj' => 'La tarea se encuentra Bloqueada');
            }

        } elseif ($resul['en_gestion'] != $login['LOGIN']) {
            $response = array('state' => 1, 'msj' => 'La tarea se encuentra en gestión');
        }

        /*if ($stmt->rowCount()) {
            $resul = $stmt->fetch(PDO::FETCH_OBJ);
            if ($resul->gestiona_por == $login) {
                $stmt = $this->_DB->prepare("update nivelacion set en_gestion = '', gestiona_por = '', fecha_gestion = '' where id = :id");
                if ($stmt->execute(array(':id' => $id))) {
                    $response = array('state' => 1, 'msj' => 'La tarea se encuentra desbloqueada');
                } else {
                    $response = array('state' => 0, 'msj' => 'Ah ocurrido un error intentalo nuevamente');
                }

            } elseif ($resul->en_gestion == '') {
                $stmt = $this->_DB->prepare("update nivelacion set gestiona_por = :gestion, estado = 1, fecha_gestion = :fecha_gestion, en_gestion = 1 where id = :id");
                $stmt->execute(array(':gestion' => $login, ':id' => $id, ':fecha_gestion' => date("Y-m-d h:i:s")));
                $response = array('state' => 1, 'msj' => 'La tarea se encuentra Bloqueada');
            } elseif ($resul->en_gestion != $login) {
                $response = array('state' => 1, 'msj' => 'La tarea se encuentra en gestión');
            }
        }*/

        $this->_DB = null;
        echo json_encode($response);
    }

    public function csvNivelacion($data){
        $fechaini = $data->fechaini;
        $fechafin = $data->fechafin;


        $stmt = $this->_DB->query("select ticket_id,
                                           fecha_ingreso,
                                           fecha_gestion,
                                           fecha_respuesta,
                                           nombre_tecnico,
                                           cc_tecnico,
                                           pedido,
                                           proceso,
                                           motivo,
                                           submotivo,
                                           zona,
                                           zubzona,
                                           nombre_nuevo_tecnico,
                                           cc_nuevo_tecnico,
                                           creado_por,
                                           gestiona_por,
                                           observaciones,
                                           se_realiza_nivelacion
                                    from nivelacion where 1=1 and fecha_ingreso BETWEEN ('$fechaini 00:00:00') AND ('$fechafin 23:59:59')");

        $stmt->execute();

        if ($stmt->rowCount()){
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $response = array($result);
        }else{
            $response = array('state' => 0, 'msj' => 'No se encontraron datos');
        }
        $this->_DB = null;
        echo json_encode($response);

    }


}
