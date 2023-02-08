<?php
require_once '../class/conection.php';

class modelTurnos
{
    private $_DB;

    public function __construct()
    {
        $this->_DB = new Conection();
    }

    public function usuariosTurnos()
    {
        try {
            $stmt = $this->_DB->query("select distinct login
                                                from usuarios
                                                where perfil = '5'
                                                order by login");
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

    public function listaTurnos($data)
    {
        try {
            $fechaini = $data['fechaini'];
            $fechaFin = $data['fechafin'];

            $stmt = $this->_DB->prepare("SELECT *
                                                from turnosSeguimiento
                                                where fecha between (:fechaini) and (:fechaFin)
                                                order by usuario");
            $stmt->execute([
                ':fechaini' => $fechaini,
                ':fechafin' => $fechaFin,
            ]);

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

    public function cumpleTurnos($data)
    {
        try {

            $fecha = $data['fechaIni'];

            $stmt = $this->_DB->prepare("select fecha,
                                                   usuario,
                                                   horaInicio,
                                                   horaFin,
                                                   totaTurno,
                                                   fechaing,
                                                   fecha_salida,
                                                   (case
                                                        when c.status = 'logged off' then 'Deslogueado'
                                                        else 'Logueado' end) as status,
                                                   c.total_dia,
                                                   (case
                                                        when totaTurno <= c.total_dia then 'Cumple'
                                                        else 'No cumple' end)   cumple,
                                                   (case
                                                        when horaInicio >= c.fechaing then 'ingreso'
                                                        else 'No ingreso' end) as ingreso
                                            from turnosSeguimiento tur
                                                     join (SELECT a.idusuario,
                                                                  date_format(a.fecha_ingreso, '%H:%i:%s') fechaing,
                                                                  date_format(a.fecha_salida, '%H:%i:%s') fecha_salida,
                                                                  a.status,
                                                                  a.total_dia
                                                           FROM registro_ingresoSeguimiento a
                                                           where a.fecha_ingreso BETWEEN (:fechaini)
                                                                     AND (:fechafin)) c
                                                          on c.idusuario = tur.usuario
                                                              and fecha = :fecha");
            $stmt->execute([
                ':fechaini' => "$fecha 00:00:00",
                ':fechafin' => "$fecha 23:59:59",
                ':fecha'    => $fecha,
            ]);


            if ($stmt->rowCount()) {
                $ausencia = $this->_DB->prepare("SELECT usuario,
                                                               horaInicio,
                                                               horaFin,
                                                               novedades
                                                        from turnosSeguimiento tur
                                                        where fecha = :fecha
                                                          and not EXISTS(SELECT idusuario
                                                                         from registro_ingresoSeguimiento ing
                                                                         where fecha_ingreso BETWEEN (:fechaini)
                                                                             AND (:fechafin)
                                                                           and tur.usuario = ing.idusuario)");
                $ausencia->execute([
                    ':fechaini' => "$fecha 00:00:00",
                    ':fechafin' => "$fecha 23:59:59",
                    ':fecha'    => $fecha,
                ]);

                if ($ausencia->rowCount()) {
                    $result   = $ausencia->fetchAll(PDO::FETCH_ASSOC);
                    $response = [$result, 201];
                } else {
                    $response = ['No se encontraron datos', 400];
                }

            } else {
                $response = ['No se encontraron datos en la consulta', 400];
            }

        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }

        $this->_DB = null;
        echo json_encode($response);
    }

    public function guardarTurnos($data)
    {
        try {
            $datosTurnos = $data['datosTurnos'];
            $total       = count($datosTurnos);

            for ($i = 0; $i < $total; $i++) {

                $fecha       = $datosTurnos[$i]['fecha'];
                $horaFin     = $datosTurnos[$i]['horaFin'];
                $horaIni     = $datosTurnos[$i]['horaInicio'];
                $usuario     = $datosTurnos[$i]['usuario'];
                $usuariocrea = $datosTurnos[$i]['usuariocrea'];
                $novedad     = $datosTurnos[$i]['novedad'];

                if ($novedad == null) {
                    $novedad = 'Turno';
                }

                $horaIni = date('H:i', strtotime($horaIni));
                $horaFin = date('H:i', strtotime($horaFin));
                $dif     = date("H:i", strtotime("00:00:00") + strtotime($horaFin) - strtotime($horaIni));

                $stmt = $this->_DB->prepare("INSERT INTO turnosSeguimiento
                                                        (fecha, usuario, horaInicio, horaFin, totaTurno, novedades, usuarioCrea)
                                                    values (:fecha, :usuario, :horaIni, :horaFin, :dif, :novedad, :usuariocrea)");
                $stmt->execute([
                    ':fecha'       => $fecha,
                    ':usuario'     => $usuario,
                    ':horaIni'     => $horaIni,
                    ':horaFin'     => $horaFin,
                    ':dif'         => $dif,
                    ':novedad'     => $novedad,
                    ':usuariocrea' => $usuariocrea,
                ]);
                if ($stmt->rowCount()) {
                    $response = ['Se guardado correctamente.', 201];
                } else {
                    $response = ['Ah ocurrido un error intentalo nuevamente.', 400];
                }
            }
        } catch
        (PDOException $e) {
            var_dump($e->getMessage());
        }
        $this->_DB = null;
        echo json_encode($response);
    }

    public function updateTurno($data)
    {
        try {

            $datosTurnos = $data['datos'];

            $id      = $datosTurnos['idturnos'];
            $horaFin = $datosTurnos['horaFin'];
            $horaIni = $datosTurnos['horaInicio'];
            $novedad = $datosTurnos['novedades'];

            $stmt = $this->_DB->prepare("UPDATE turnosSeguimiento
                                                SET horaInicio = :horaIni,
                                                    horaFin    = :horaFin,
                                                    novedades  = :novedad
                                                WHERE idturnos = :id");
            $stmt->execute([
                ':horaini' => $horaIni,
                ':horafin' => $horaFin,
                ':novedad' => $novedad,
                ':id'      => $id,
            ]);

            if ($stmt->rowCount()) {
                $response = ['Datos actualizados', 201];
            } else {
                $response = ['Ah ocurrido un error intentalo de nuevo', 400];
            }

        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }

        $this->_DB = null;
        echo json_encode($response);
    }

    public function CsvExporteAdherencia($data)
    {

        try {
            session_start();
            $fechaIni = $data['fechaIni'];
            $fechaFin = $data['fechaFin'];
            $login    = $_SESSION['login'];

            $filename = "AdherenciaTurnos" . "_" . $fechaIni . "_" . $fechaFin . "_" . $login . ".csv";

            $stmt = $this->_DB->prepare("select c.date,
                                                   usuario,
                                                   horaInicio,
                                                   horaFin,
                                                   totaTurno,
                                                   c.fechaing,
                                                   c.fecha_salida,
                                                   (case
                                                        when c.status = 'logged off' then 'Deslogueado'
                                                        else 'Logueado' end)  status,
                                                   c.total_dia,
                                                   (case
                                                        when totaTurno <= c.total_dia then 'Cumple'
                                                        else 'No cumple' end) cumple,
                                                   (case
                                                        when horaInicio >= c.fechaing then 'OK'
                                                        else 'Tarde' end)     ingreso
                                            from turnosSeguimiento tur
                                                     join (SELECT a.idusuario,
                                                                  date_format(a.fecha_ingreso, '%Y-%m-%d') date,
                                                                  date_format(a.fecha_ingreso, '%H:%i:%s')
                                                                                                           fechaing,
                                                                  date_format(a.fecha_salida, '%H:%i:%s')  fecha_salida,
                                                                  a.status,
                                                                  a.total_dia
                                                           FROM registro_ingresoSeguimiento a
                                                           where a.fecha_ingreso BETWEEN (:fechaini)
                                                                     AND (:fechafin)) c
                                                          on c.idusuario = tur.usuario
                                                              and fecha = c.date ");
            $stmt->execute([
                ':fechaini' => "$fechaIni 00:00:00",
                ':fechafin' => "$fechaFin 23:59:59",
            ]);

            if ($stmt->rowCount()) {
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $fp     = fopen("../tmp/$filename", 'w');

                $columnas = [
                    'FECHA',
                    'USUARIO',
                    'INICIO_TURNO',
                    'FIN_TURNO',
                    'TOTAL_TURNO',
                    'HORA_INGRESO',
                    'HORA_SALIDA',
                    'ESTADO_FINAL',
                    'TOTAL_CONEXION',
                    'CUMPLIMIENTO_HORARIO',
                    'CUMPLIMIENTO_INGRESO',
                ];

                fputcsv($fp, $columnas);
                fputcsv($fp, $result);
                fclose($fp);

                $response = [$filename, $stmt->rowCount()];

            } else {
                $response = ['No se encontraron datos', 400];
            }
        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        $this->_DB = null;
        echo json_encode($response);
    }

    public function deleteTurno($data)
    {
        try {
            $stmt = $this->_DB->prepare("delete
                                                from turnosSeguimiento
                                                where idturnos = :id");
            $stmt->execute([':id' => $data]);

            if ($stmt->rowCount()) {
                $response = ['Operacion exitosa', 201];
            } else {
                $response = ['Ah ocurrido un error intentalo de nuevo', 400];
            }

        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        $this->_DB = null;
        echo json_encode($response);
    }
}
