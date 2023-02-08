<?php
require_once '../class/conection.php';

class modelAlarma
{

    private $_DB;

    public function __construct()
    {
        $this->_DB = new Conection();
    }

    public function nuevaAlarma($data)
    {
        try {

            $login           = $data['datosCrearAlarma'];
            $nombrealarma    = $login['NOMBRE'];
            $ciudad          = $login['CIUDAD'];
            $producto        = $login['PRODUCTO'];
            $proceso         = $login['PROCESO'];
            $accion          = $login['ACCION'];
            $subaccion       = $login['SUBACCION'];
            $mensaje         = $login['MENSAJE'];
            $cantidad_campos = "";

            if ($ciudad != "") {
                $cantidad_campos = "ciudad";
            }
            if ($producto != "") {
                $cantidad_campos = $cantidad_campos . "," . "tecnologia_producto";
            }
            if ($proceso != "") {
                $cantidad_campos = $cantidad_campos . "," . "proceso";
            }
            if ($accion != "") {
                $cantidad_campos = $cantidad_campos . "," . "accion";
            }
            if ($subaccion != "") {
                $cantidad_campos = $cantidad_campos . "," . "asubaccion";
            }

            $stmt = $this->_DB->prepare("INSERT INTO alarmas (nombre_alarma, ciudad, tecnologia_producto, proceso, accion, subaccion, cantidad_campos, mensaje) 
                                                VALUES (:nombrealarma, :ciudad, :producto, :proceso, :accion, :subaccion, :cantidad_campos, :mensaje)");
            $stmt->execute([
                ':nombrealarma'    => $nombrealarma,
                ':ciudad'          => $ciudad,
                ':$producto'       => $producto,
                ':proceso'         => $proceso,
                ':accion'          => $accion,
                ':subaccion'       => $subaccion,
                ':cantidad_campos' => $cantidad_campos,
                ':mensaje'         => $mensaje,
            ]);

            if ($stmt->rowCount() == 1) {
                $response = ['Alarma creada', 201];
            } else {
                $response = ['Ah ocurrido un error intentalo nuevamente', 400];
            }

        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        $this->_DB = null;
        echo json_encode($response);
    }

    public function editAlarma($datosAlarma)
    {
        try {

            $nombre_alarma       = $datosAlarma['nombre_alarma'];
            $ciudad              = $datosAlarma['ciudad'];
            $tecnologia_producto = $datosAlarma['tecnologia_producto'];
            $proceso             = $datosAlarma['proceso'];
            $accion              = $datosAlarma['accion'];
            $id                  = $datosAlarma['id'];
            $subaccion           = $datosAlarma['subaccion'];
            $mensaje             = $datosAlarma['mensaje'];

            if ($ciudad != "") {
                $cantidad_campos = "ciudad";
            }
            if ($tecnologia_producto != "") {
                $cantidad_campos = $cantidad_campos . "," . "tecnologia_producto";
            }
            if ($proceso != "") {
                $cantidad_campos = $cantidad_campos . "," . "proceso";
            }
            if ($accion != "") {
                $cantidad_campos = $cantidad_campos . "," . "accion";
            }
            if ($subaccion != "") {
                $cantidad_campos = $cantidad_campos . "," . "subaccion";
            }

            $stmt = $this->_DB->prepare("update alarmas
                                                set nombre_alarma       = :nombre_alarma,
                                                    ciudad              = :ciudad,
                                                    tecnologia_producto = :tecnologia_producto,
                                                    proceso             = :proceso,
                                                    accion              = :accion,
                                                    subaccion           = :subaccion,
                                                    mensaje             = :mensaje,
                                                    cantidad_campos     = :cantidad_campos
                                                where id = :id");
            $stmt->execute([
                ':nombre_alarma'       => $nombre_alarma,
                ':ciudad'              => $ciudad,
                ':tecnologia_producto' => $tecnologia_producto,
                ':proceso'             => $proceso,
                ':accion'              => $accion,
                ':subaccion'           => $subaccion,
                ':mensaje'             => $mensaje,
                ':cantidad_campos'     => $cantidad_campos,
                ':id'                  => $id,
            ]);

            if ($stmt->rowCount() == 1) {
                $response = ['Alarma actualizada', 201];
            } else {
                $response = ['Ah ocurrodo un error intentalo nuevamente', 400];
            }

        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        $this->_DB = null;
        echo json_encode($response);
    }

    public function listadoAlarmas()
    {
        try {
            $stmt = $this->_DB->query("SELECT * FROM alarmas");
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

    public function deleteAlarma($data)
    {
        try {
            $stmt = $this->_DB->prepare("DELETE FROM alarmas WHERE id = :id");
            $stmt->execute([':id' => $data]);

            if ($stmt->rowCount() == 1) {
                $response = ['Alarmar eliminada', 201];
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
