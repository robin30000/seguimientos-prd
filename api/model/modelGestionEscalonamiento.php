<?php
require_once '../class/conection.php';

class modelGestionEscalonamiento
{
    private $_BD;

    public function __construct()
    {
        $this->_BD = new Conection();
    }

    public function gestionEscalonamiento()
    {

        try {
            /*LOGICA QUE LLEVA LA INFORMACION A TV, INTERNET-ToIp Y CORREGIR PORTAFOLIO*/
            $stmt = $this->_BD->query("SELECT e.pedido,
                                                   e.tarea,
                                                   e.tecnico,
                                                   e.id_tecnico,
                                                   e.fecha_solicitud,
                                                   e.fecha_gestion,
                                                   e.login_gestion,
                                                   e.engestion,
                                                   e.proceso,
                                                   e.producto,
                                                   e.motivo,
                                                   e.area,
                                                   e.region,
                                                   e.tipo_tarea,
                                                   e.tecnologia,
                                                   e.departamento,
                                                   e.prueba_smnet,
                                                   e.foto_adjunta,
                                                   e.marcacion_tap,
                                                   e.direccion_tap,
                                                   e.valor_tap,
                                                   e.informacion_adicional,
                                                   e.mac_real_cpe,
                                                   e.correa_marcacion,
                                                   observaciones,
                                                   id_terreno,
                                                   CASE
                                                       WHEN (SELECT COUNT(*)
                                                             FROM escalamiento_infraestructura e1
                                                             WHERE e1.pedido = e.pedido
                                                               AND e1.fecha_solicitud >= DATE_SUB(CURDATE(), INTERVAL 10 DAY)
                                                               AND e1.estado <> '0') > 0 THEN 'TRUE'
                                                       ELSE 'FALSE' END alerta
                                            FROM escalamiento_infraestructura e
                                            WHERE e.estado = '0'
                                              AND e.pedido <> ''
                                            ORDER BY e.fecha_solicitud");
            $stmt->execute();
            if ($stmt->rowCount()) {
                $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $response = 0;
            }
        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }

        $this->_BD = null;

        return $response;
    }

    public function datosescalamientosprioridad2()
    {

        try {
            /*LOGICA QUE LLEVA LA INFORMACION A TV, INTERNET-ToIp Y CORREGIR PORTAFOLIO*/
            $stmt = $this->_BD->query("SELECT e.pedido,
                                                   e.tarea,
                                                   e.tecnico,
                                                   e.id_tecnico,
                                                   e.fecha_solicitud,
                                                   e.fecha_gestion,
                                                   e.login_gestion,
                                                   e.engestion,
                                                   e.proceso,
                                                   e.producto,
                                                   e.motivo,
                                                   e.area,
                                                   e.region,
                                                   e.tipo_tarea,
                                                   e.tecnologia,
                                                   e.departamento,
                                                   e.prueba_smnet,
                                                   e.foto_adjunta,
                                                   e.marcacion_tap,
                                                   e.direccion_tap,
                                                   e.valor_tap,
                                                   e.informacion_adicional,
                                                   e.mac_real_cpe,
                                                   e.correa_marcacion,
                                                   observaciones,
                                                   id_terreno,
                                                   CASE
                                                       WHEN (SELECT COUNT(*)
                                                             FROM escalamiento_infraestructura e1
                                                             WHERE e1.pedido = e.pedido
                                                               AND e1.fecha_solicitud >= DATE_SUB(CURDATE(), INTERVAL 10 DAY)
                                                               AND e1.estado <> '0') > 0 THEN 'TRUE'
                                                       ELSE 'FALSE' END alerta
                                            FROM escalamiento_infraestructura e
                                            WHERE e.estado = '1'
                                              AND e.pedido <> ''
                                              AND e.tipificacion = 'Escalamiento ok nivel 2 Prioridad'
                                              AND e.ans < 20
                                            ORDER BY e.fecha_solicitud");
            if ($stmt->rowCount()) {
                $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $response = 0;
            }
        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        $this->_BD = null;

        return $response;


        /*if ($rst->num_rows > 0) {

            /*$resultadoEscalamiento = [];
            while ($row = $rst->fetch_assoc()) {

                $row['pedido']                = utf8_encode($row['pedido']);
                $row['tarea']                 = utf8_encode($row['tarea']);
                $row['tecnico']               = utf8_encode($row['tecnico']);
                $row['id_tecnico']            = utf8_encode($row['id_tecnico']);
                $row['fecha_solicitud']       = utf8_encode($row['fecha_solicitud']);
                $row['fecha_gestion']         = utf8_encode($row['fecha_gestion']);
                $row['login_gestion']         = utf8_encode($row['login_gestion']);
                $row['engestion']             = utf8_encode($row['engestion']);
                $row['proceso']               = utf8_encode($row['proceso']);
                $row['observacion']           = utf8_encode($row['observacion']);
                $row['engestion']             = utf8_encode($row['engestion']);
                $row['proceso']               = utf8_encode($row['proceso']);
                $row['producto']              = utf8_encode($row['producto']);
                $row['motivo']                = utf8_encode($row['motivo']);
                $row['area']                  = utf8_encode($row['area']);
                $row['region']                = utf8_encode($row['region']);
                $row['tipo_tarea']            = utf8_encode($row['tipo_tarea']);
                $row['tecnologia']            = utf8_encode($row['tecnologia']);
                $row['departamento']          = utf8_encode($row['departamento']);
                $row['prueba_smnet']          = utf8_encode($row['prueba_smnet']);
                $row['foto_adjunta']          = utf8_encode($row['foto_adjunta']);
                $row['marcacion_tap']         = utf8_encode($row['marcacion_tap']);
                $row['direccion_tap']         = utf8_encode($row['direccion_tap']);
                $row['valor_tap']             = utf8_encode($row['valor_tap']);
                $row['informacion_adicional'] = utf8_encode($row['informacion_adicional']);
                $row['mac_real_cpe']          = utf8_encode($row['mac_real_cpe']);
                $row['correa_marcacion']      = utf8_encode($row['correa_marcacion']);
                $row['observaciones']         = utf8_encode($row['observaciones']);
                $row['alerta']                = utf8_encode($row['alerta']);

                // SE AGREGA LA DETENCION DE PORTAFOLIO PARA RECONOZCA EN VALOR
                $resultadoEscalamiento[] = $row;
            }
        }*/
    }
}


/*if ($rst->num_rows > 0) {

    $resultadoEscalamiento = [];
    while ($row = $rst->fetch_assoc()) {

        $row['pedido']                = utf8_encode($row['pedido']);
        $row['tarea']                 = utf8_encode($row['tarea']);
        $row['tecnico']               = utf8_encode($row['tecnico']);
        $row['id_tecnico']            = utf8_encode($row['id_tecnico']);
        $row['fecha_solicitud']       = utf8_encode($row['fecha_solicitud']);
        $row['fecha_gestion']         = utf8_encode($row['fecha_gestion']);
        $row['login_gestion']         = utf8_encode($row['login_gestion']);
        $row['engestion']             = utf8_encode($row['engestion']);
        $row['proceso']               = utf8_encode($row['proceso']);
        $row['observacion']           = utf8_encode($row['observacion']);
        $row['engestion']             = utf8_encode($row['engestion']);
        $row['proceso']               = utf8_encode($row['proceso']);
        $row['producto']              = utf8_encode($row['producto']);
        $row['motivo']                = utf8_encode($row['motivo']);
        $row['area']                  = utf8_encode($row['area']);
        $row['region']                = utf8_encode($row['region']);
        $row['tipo_tarea']            = utf8_encode($row['tipo_tarea']);
        $row['tecnologia']            = utf8_encode($row['tecnologia']);
        $row['departamento']          = utf8_encode($row['departamento']);
        $row['prueba_smnet']          = utf8_encode($row['prueba_smnet']);
        $row['foto_adjunta']          = utf8_encode($row['foto_adjunta']);
        $row['marcacion_tap']         = utf8_encode($row['marcacion_tap']);
        $row['direccion_tap']         = utf8_encode($row['direccion_tap']);
        $row['valor_tap']             = utf8_encode($row['valor_tap']);
        $row['informacion_adicional'] = utf8_encode($row['informacion_adicional']);
        $row['mac_real_cpe']          = utf8_encode($row['mac_real_cpe']);
        $row['correa_marcacion']      = utf8_encode($row['correa_marcacion']);
        $row['observaciones']         = utf8_encode($row['observaciones']);
        $row['alerta']                = utf8_encode($row['alerta']);

        // SE AGREGA LA DETENCION DE PORTAFOLIO PARA RECONOZCA EN VALOR
        $resultadoEscalamiento[] = $row;
    }

    $this->response($this->json([$resultadoEscalamiento]), 201);

} else {
    $error = [];
    $this->response($this->json($error), 400);
}
}

}*/
