<?php

require_once '../class/conection.php';

class modelauthentication
{
    private $_DB;

    public function __construct()
    {
        $this->_DB = new Conection();
    }

    public function loginUser($usuarioid, $password)
    {
        $today      = date('Y-m-d');
        $fecha      = date('Y-m-d H:i:s');
        $usuarioIp  = $_SERVER['REMOTE_ADDR'];
        $usuarioPc  = gethostbyaddr($usuarioIp);
        $aplicacion = "Seguimiento";

        try {
            $stmt = $this->_DB->prepare("SELECT id, login, nombre, identificacion, perfil FROM usuarios WHERE login = ? AND password = ?");
            $stmt->bindParam(1, $usuarioid, PDO::PARAM_STR);
            $stmt->bindParam(2, $password, PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() == 1) {
                $resLogin = $stmt->fetch(PDO::FETCH_OBJ);
                session_destroy();
                session_start();

                $_SESSION["logueado"]      = true;
                $_SESSION['timeOnline']    = time() * 1000;
                $_SESSION['online']        = date("H:i:s");
                $_SESSION["login"]         = $resLogin->login;
                $_SESSION['id']            = $resLogin->id;
                $_SESSION['token_session'] = uniqid();
                $_SESSION['fecha_ingreso'] = date('Y-m-d H:i:s');
                $_SESSION['perfil']        = $resLogin->perfil;

                $stmtIngreso = $this->_DB->prepare("SELECT id
                                                         , fecha_ingreso
                                                         , date_format(fecha_ingreso, '%H:%i:%s') as hora_ingreso
                                                    FROM registro_ingresoSeguimiento
                                                    WHERE fecha_ingreso between :fechaini
                                                        and :fechafin
                                                      and idusuario = :usuario_id");

                $stmtIngreso->execute([':fechaini' => "$today 00:00:00", ':fechafin' => "$today 23:59:59", ':usuario_id' => $resLogin->login]);

                if ($stmtIngreso->rowCount()) {
                    $resStmtIngreso = $stmtIngreso->fetch(PDO::FETCH_OBJ);
                    $stmt           = $this->_DB->prepare("update registro_ingresoSeguimiento set status='logged in', ingresos=ingresos+1 where id=?");
                    $stmt->bindParam(1, $resLogin->id, PDO::PARAM_INT);
                    $stmt->execute();

                    $fecha_ingreso = $resStmtIngreso->fecha_ingreso;
                    $hora_ingreso  = $resStmtIngreso->hora_ingreso;
                } else {

                    $otherStmt = $this->_DB->prepare("insert into registro_ingresoSeguimiento (idusuario,status,fecha_ingreso, ip, pc, aplicacion) " .
                                                     "values(:usuario_id,'logged in',:fechaIngreso, :ip, :usuarioPc, :aplicacion)");
                    $otherStmt->execute([
                        ':usuario_id'   => $resLogin->login,
                        ':fechaIngreso' => date('Y-m-d H:i:s'),
                        ':ip'           => $usuarioIp,
                        ':usuarioPc'    => $usuarioPc,
                        ':aplicacion'   => $aplicacion,
                    ]);

                    $stmt = $this->_DB->prepare("SELECT fecha_ingreso, date_format(fecha_ingreso,'%H:%i:%s') AS hora_ingreso
                                                        FROM registro_ingresoSeguimiento
                                                        WHERE fecha_ingreso between :fechaini and :fechafin
                                                          and idusuario = :usuario_id
                                                        limit 1");

                    $stmt->execute([':fechaini' => "$today 00:00:00", ':fechafin' => "$today 23:59:59", ':usuario_id' => $resLogin->id]);

                    if ($stmt->rowCount()) {
                        $res           = $stmt->fetch(PDO::FETCH_OBJ);
                        $fecha_ingreso = $res->fecha_ingreso;
                        $hora_ingreso  = $res->hora_ingreso;
                    } else {
                        $fecha_ingreso = 'SinFecha';
                        $hora_ingreso  = 'SinHora';
                    }
                }

                http_response_code(201);
                header("Content-type: application/json; charset=utf-8");
                echo json_encode($resLogin);
                die();

            } else {
                $body = 'Error';
                http_response_code(406);
                header("Content-type: application/json; charset=utf-8");
                echo json_encode($body);
                die();
            }


        } catch (PDOException $e) {
            //$res = array('state' => 0, 'msg' => 'Error ' . $e->getMessage());
        }
        $this->_DB = null;

        return $res;
    }

    public function updatesalida()
    {
        session_start();
        $today = date('Y-m-d');
        $stmt  = $this->_DB->prepare("SELECT id, fecha_ingreso 
                 , date_format(fecha_ingreso,'%H:%i:%s') as hora_ingreso, SEC_TO_TIME((TIMESTAMPDIFF(second, fecha_ingreso, ? ))) total FROM 
                                    registro_ingresoSeguimiento 
                 WHERE fecha_ingreso between ? and ? 
                 and idusuario = ? limit 1");
        $stmt->bindParam(1, $_SESSION['fecha_ingreso']);
        $stmt->bindValue(2, "$today 00:00:00");
        $stmt->bindValue(3, "$today 23:59:59");
        $stmt->bindParam(4, $_SESSION['login']);

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_OBJ);

        $total_dia = $result->total;

        $hora     = substr($total_dia, 0, 2);
        $minutos  = substr($total_dia, 3, 2);
        $segundos = substr($total_dia, 6, 2);

        $totalminutos = round((($hora * 60) + $minutos + $segundos) / 60, 2);

        $stmt = $this->_DB->prepare("update registro_ingresoSeguimiento
                        set status='logged off',
                            fecha_salida = :fechaSal,
                            salidas=salidas + 1,
                            total_dia = :total_dia, hora = :hora, minutos = :minutos, segundos = :segundos, total_factura = :total_factura
                        where id= :id");
        $stmt->execute([
            ':fechaSal'      => date('Y-m-d H:i:s'),
            ':total_dia'     => $total_dia,
            ':hora'          => $hora,
            ':minutos'       => $minutos,
            ':segundos'      => $segundos,
            ':total_factura' => $totalminutos,
            ':id'            => $result->id,
        ]);

        if ($stmt->rowCount() == 1) {

            if (isset($_COOKIE[session_name()])) {
                setcookie(session_name(), "", time() - 3600, "/");
            }
            session_start();
            $_SESSION = [];
            session_destroy();
            $response = ['logged out', 201];
        } else {
            $response = ['Error', 400];
        }
        echo json_encode($response);
    }
}
