<?php
require_once '../class/conection.php';

class modelotrosServiciosDos
{

    private $_DB;

    public function __construct()
    {
        $this->_DB = new Conection();
    }

    public function listadoEstadosClick($datos)
    {
        try {

            $fecha        = $datos['fecha'];
            $uen          = $datos['uen'];
            $tipo_trabajo = $datos['tipo_trabajo'];

            if ($fecha == "") {
                $fecha = date("Y") . "-" . date("m") . "-" . date("d");
            }

            if ($uen != "") {
                $uen = "and uen = '$uen'";
            } else {
                $uen = "";
            }
            if ($tipo_trabajo != "") {
                $tipo_trabajo  = "and tipo_trabajo = '$tipo_trabajo'";
                $tipo_trabajo1 = "and (select tipo_trabajo from carga_click where pro.pedido_id = pedido_id limit 1) = '$tipo_trabajo'";
            } else {
                $tipo_trabajo = "";
            }

            $query = $this->_DB->query("select estado_id, count(pedido_id) total_estados 
                from carga_click  
                where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')  
                $tipo_trabajo $uen 
                group by estado_id  
                order by total_estados DESC  ");
            //echo $query;
            $query->execute();

            //echo $this->mysqli->query($sqlLogin);
            //
            if ($query->rowCount()) {

                $resultado = [];

                while ($row = $query->fetchAll(PDO::FETCH_ASSOC)) {

                    $resultado[] = $row;

                }

                $response = [$resultado, 201];
            } else {
                $response = ['', 400];

            }
        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        echo json_encode($response);
    }

    public function BuscarPedidoinsta($data)
    {
        try {

        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
    }

    public function GuardarPedidoPendiInsta($data)
    {
        try {
            session_start();
            $today        = date("Y") . "-" . date("m") . "-" . date("d");
            $usuarioid    = $_SESSION['login'];
            $datospedidos = $data['datosdelpedido'];
            $infoGuardar  = $data['info'];
            $Causa_raiz   = $infoGuardar['causaraiz'];
            $responsable  = $infoGuardar['responsable'];
            $observacion  = $infoGuardar['observaciones'];

            $pedido                 = $datospedidos['Pedido'];
            $id                     = $datospedidos['id'];
            $Novedad_malo           = $datospedidos['Novedad_malo'];
            $Finalizado_click       = $datospedidos['Finalizado_click'];
            $update_concepto_oracle = $datospedidos['update_concepto_oracle'];
            $fecha_agenda           = $datospedidos['fecha_agenda'];

            $query = $this->_DB->query("INSERT INTO historicoGestionPendientes 
                (id_gestion, pedido, causa_raiz, responsable, observacion, novedad_malo, finalizado_click, 
                update_concepto_oracle, fecha_agenda) 
                VALUES ('$id', '$pedido', '$Causa_raiz', '$responsable', '$observacion', '$Novedad_malo', '$Finalizado_click', 
                '$update_concepto_oracle', '$fecha_agenda') ");

            $query->execute();

            $sqlupdate = $this->_DB->query("UPDATE gestion_pendientes SET bloqueo='NO', causa_raiz='$Causa_raiz', responsable ='$responsable', 
                observacion='$observacion', Asesor_carga='null', fecha_update='$today', 
                actualizado ='SI' WHERE id='$id' ");

            $sqlupdate->execute();

        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
    }

    public function deleteregistrosCarga($data)
    {
        try {
            $id = $data;

            $sql = $this->_DB->query("delete from carga_archivos where id = :id");
            $sql->execute([':id' => $id]);

        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
    }

    public function Accionesoffline($data)
    {
        try {
            $producto = $data;

            $query = $this->_DB->query("SELECT DISTINCT ACCION
                 FROM accionesoffline 
                 WHERE producto = :product
                 ORDER BY ACCION");

            $query->execute([':product' => $producto]);

            if ($query->rowCount()) {


                $resultado = $query->fetchAll(PDO::FETCH_ASSOC);
                $response  = [$resultado, 201];

            } else {
                $response = ['', 400];
            } // If no records "No Content" status
        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        echo json_encode($response);
    }

    public function acciones($data)
    {
        try {
            $proceso = $data;

            $query = $this->_DB->query(" SELECT DISTINCT ACCION
                 FROM procesos 
                 where 1=1 and proceso = :proceso and accion <> ''
                 ORDER BY ACCION");

            $query->execute([':process' => $proceso]);
            if ($query->rowCount()) {

                $resultado = [];
                while ($row = $query->fetchAll(PDO::FETCH_ASSOC)) {
                    $resultado[] = $row;
                }

                $response = [$resultado, 201];

            } else {
                $response = ['', 400];
            } // If no records "No Content" status
        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        echo json_encode($response);
    }

    public function Codigos($data)
    {
        try {
            $proceso         = $data['proceso'];
            $UNESourceSystem = $data['UNESourceSystem'];

            $query = $this->_DB->query("SELECT DISTINCT codigo
					FROM codigosPendiente
					WHERE proceso = :process AND UNESourceSystem = :une
					ORDER BY codigo");

            $query->execute([':process' => $proceso, ':une' => $UNESourceSystem]);

            if ($query->rowCount()) {

                $resultado = [];
                while ($row = $query->fetchAll(PDO::FETCH_ASSOC)) {

                    $row['codigo'] = utf8_encode($row['codigo']);

                    $resultado[] = $row;
                }
                $response = [$resultado, 201];

            } else {
                $response = ['', 201];
            }
        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }

        echo json_encode($response);
    }

    public function Diagnosticos($data)
    {
        try {
            $producto = $data['producto'];

            $query = $this->_DB->query("SELECT DISTINCT diagnostico
					FROM diagnosticoFalla
					WHERE producto = :product
					ORDER BY diagnostico");

            $query->execute(array(':product' => $producto));

            if ($query->rowCount()) {

                $resultado = [];
                while ($row = $query->fetchAll(PDO::FETCH_ASSOC)) {

                    $row['diagnostico'] = utf8_encode($row['diagnostico']);

                    $resultado[] = $row;
                }
                $response = [$resultado, 201];
            } else {
                $response = ['', 200];
            }
        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        echo json_encode($response);
    }
}
