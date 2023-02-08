<?php
require_once '../class/conection.php';

class modelVisitasEnConjunto
{
    private $_DB;

    public function __construct()
    {
        $this->_DB = new Conection();
    }

    public function visitasEnConjunto($data)
    {
        try {
            $pagina   = $data['page'];
            $datos    = $data['datos'];
            $fechaini = $data['fechaini'];
            $fechafin = $data['fechafin'];

            if ($fechaini == "" || $fechafin == "") {
                $fechaini = date('Y-m-d');
                $fechafin = date('Y-m-d');
            }

            if ($pagina == "undefined") {
                $pagina = "0";
            } else {
                $pagina = $pagina - 1;
            }

            $pagina = $pagina * 100;

            $stmt = $this->_DB->prepare("SELECT id,
                                                   pedido,
                                                   tecnicopremisas,
                                                   tecnicoinfraestructura,
                                                   fechavisita,
                                                   region,
                                                   municipio,
                                                   contrato,
                                                   gestion,
                                                   quiensolicitavisita,
                                                   notas,
                                                   grupo,
                                                   fechasolicitud,
                                                   fechafingestion
                                            FROM visitasenconjunto
                                            WHERE 1 = 1
                                              AND fechasolicitud BETWEEN (:fechaini) AND (:fechafin)
                                            ORDER BY fechasolicitud DESC
                                            limit 100 offset :pagina");
            $stmt->execute([
                ':fechaini' => "$fechaini 00:00:00",
                ':fechafin' => "$fechafin 23:59:59",
                ':pagina'   => $pagina,
            ]);

            if ($stmt->rowCount()) {
                $totalPaginas = $stmt->rowCount() / 100;
                $totalPaginas = ceil($totalPaginas); //redondear al siguiente

                $response = ['data' => $stmt->fetchAll(PDO::FETCH_ASSOC), 'contador' => $stmt->rowCount(), 'totalPaginas' => $totalPaginas, 201];

            } else {
                $response = ['', 400];
            }

        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        $this->_DB = null;
        echo json_encode($response);
    }

    public function GrupoVisitasEnConjunto()
    {
        try {
            $stmt = $this->_DB->query("SELECT nota FROM Notas WHERE nota <> 'mal codigo' ORDER BY nota");
            $stmt->execute();

            if ($stmt->rowCount()) {
                $response = [$stmt->fetchAll(PDO::FETCH_ASSOC), 201];
            } else {
                $response = ['', 400];
            }

        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        $this->_DB = null;
        echo json_encode($response);
    }

    public function infoVisitasEnConjunto($data)
    {
        try {
            $datos = $data;

            $key                    = $datos['id'];
            $pedido                 = $datos['pedido'];
            $tecnicopremisas        = utf8_decode($datos['tecnicopremisas']);
            $tecnicoinfraestructura = utf8_decode($datos['tecnicoinfraestructura']);
            $fechavisita            = $datos['fechavisita'];
            $region                 = $datos['region'];
            $municipio              = $datos['municipio'];
            $contrato               = $datos['contrato'];
            $quiensolicitavisita    = utf8_decode($datos['quiensolicitavisita']);
            $gestion                = utf8_decode($datos['gestion']);
            $notas                  = utf8_decode($datos['notas']);
            $grupo                  = $datos['grupo'];
            $fechasolicitud         = date("Y-m-d H:i:s");
            $fechafingestion        = $datos['fechafingestion'];

            //echo json_encode($datos);
            if (isset($datos['id'])) {
                $stmt = $this->_DB->prepare("UPDATE visitasenconjunto v
                                                    SET v.pedido 					= TRIM(:pedido),
                                                        v.tecnicopremisas			= LOWER(TRIM(:tecnicopremisas)),
                                                        v.tecnicoinfraestructura  	= LOWER(TRIM(:tecnicoinfraestructura)),
                                                        v.fechavisita			    = TRIM(:fechavisita),
                                                        v.region 					= TRIM(:region),
                                                        v.municipio 				= TRIM(:municipio),
                                                        v.contrato 					= TRIM(:contrato),
                                                        v.gestion 					= TRIM(:gestion),
                                                        v.quiensolicitavisita		= LOWER(TRIM(:quiensolicitavisita)),
                                                        v.notas 					= TRIM(:notas),
                                                        v.grupo 					= TRIM(:grupo),
                                                        v.fechafingestion			= TRIM(:fechafingestion)
                                                    WHERE v.id = :key");
                $stmt->execute([
                    ':pedido'                 => $pedido,
                    ':tecnicopremisas'        => $tecnicopremisas,
                    ':tecnicoinfraestructura' => $tecnicoinfraestructura,
                    ':fechavisita'            => $fechavisita,
                    ':region'                 => $region,
                    ':municipio'              => $municipio,
                    ':contrato'               => $contrato,
                    ':gestion'                => $gestion,
                    ':quiensolicitavisita'    => $quiensolicitavisita,
                    ':notas'                  => $notas,
                    ':grupo'                  => $grupo,
                    ':fechafingestion'        => $fechafingestion,
                    ':key'                    => $key,
                ]);

                if ($stmt->rowCount() == 1) {
                    $response = ['datos actualizados', 201];
                } else {
                    $response = ['Ah ocurrido un error intentalo de nuevo', 400];
                }

            } else {
                $stmt = $this->_DB->prepare("INSERT INTO visitasenconjunto
                (pedido, tecnicopremisas, tecnicoinfraestructura, fechavisita, region, municipio, contrato, gestion, quiensolicitavisita, notas, grupo, fechasolicitud, fechafingestion)
                VALUES (TRIM(:pedido), LOWER(TRIM(:tecnicopremisas)), LOWER(TRIM(:tecnicoinfraestructura)), TRIM(:fechavisita), TRIM(:region), TRIM(:municipio),
                        TRIM(:contrato), TRIM(:gestion), LOWER(TRIM(:quiensolicitavisita)), TRIM(:notas), TRIM(:grupo), :fechasolicitud, TRIM(:fechafingestion))");

                $stmt->execute([
                    ':pedido'                 => $pedido,
                    ':tecnicopremisas'        => $tecnicopremisas,
                    ':tecnicoinfraestructura' => $tecnicoinfraestructura,
                    ':fechavisita'            => $fechavisita,
                    ':region'                 => $region,
                    ':municipio'              => $municipio,
                    ':contrato'               => $contrato,
                    ':gestion'                => $gestion,
                    ':quiensolicitavisita'    => $quiensolicitavisita,
                    ':notas'                  => $notas,
                    ':grupo'                  => $grupo,
                    ':fechasolicitud'         => $fechasolicitud,
                    ':fechafingestion'        => $fechafingestion,
                ]);

                if ($stmt->rowCount() == 1) {
                    $response = ['Registro exitoso', 201];
                } else {
                    $response = ['Ah ocurrido un error intentalo de nuevo', 400];
                }
            }
        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        $this->_DB = null;
        echo json_encode($response);
    }

    public function expCsvVisitasEnConjunto($datos)
    {
        try {
            session_start();
            $usuarioid = $_SESSION['login'];
            $fechaini = $datos['fechai'];
            $fechafin = $datos['fechaf'];

            if ($fechaini == "" && $fechafin == "") {
                $fechaini = date("Y") . "-" . date("m") . "-" . date("d");
                $fechafin = date("Y") . "-" . date("m") . "-" . date("d");
            }

            if ($fechaini == $fechafin) {
                $filename = "Visitas_En_Conjunto" . "_" . $fechaini . "_" . $usuarioid . ".csv";
            } else {
                $filename = "Visitas_en_Conjuntoo" . "_" . $fechaini . "_" . $fechafin . "_" . $usuarioid . ".csv";
            }

            $stmt = $this->_DB->prepare("SELECT v.pedido,
                                                   v.tecnicopremisas,
                                                   v.tecnicoinfraestructura,
                                                   v.fechavisita,
                                                   v.region,
                                                   v.municipio,
                                                   v.contrato,
                                                   v.gestion,
                                                   v.quiensolicitavisita,
                                                   v.notas,
                                                   v.grupo,
                                                   v.fechasolicitud,
                                                   v.fechafingestion
                                            FROM visitasenconjunto v
                                            WHERE 1 = 1
                                              AND v.fechasolicitud BETWEEN (:fechaini) AND (:fechafin)");
            $stmt->execute([
                ':fechaini' => "$fechaini 00:00:00",
                ':fechafin' => "$fechafin 23:59:59",
            ]);

            if ($stmt->rowCount()) {
                $fp = fopen("../tmp/$filename", 'w');

                $columnas = [
                    'Pedido',
                    'Tecnico de Premisas',
                    'Tecnico de Infraestructura',
                    'Fecha de la visita',
                    'Region',
                    'Municipio',
                    'Contrato',
                    'Gestion',
                    'Quien solicita la visita',
                    'Notas',
                    'grupo',
                    'Fecha solicitud',
                    'Fecha fin gestion',
                ];

                fputcsv($fp, $columnas);
                fputcsv($fp, $stmt->fetchAll(PDO::FETCH_ASSOC));
                fclose($fp);
                $response = [$filename, $stmt->rowCount(), 201];
            } else {
                $response = ['', 400];
            }

        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        $this->_DB = null;
        echo json_encode($response);
    }

    public function RegionesVisConjunto()
    {
        try {
            $stmt = $this->_DB->query("SELECT region
					FROM regiones
					ORDER BY region");
            $stmt->execute();

            if ($stmt->rowCount()) {
                $response = [$stmt->fetchAll(PDO::FETCH_ASSOC), 201];
            } else {
                $response = ['', 400];
            }
        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        $this->_DB = null;
        echo json_encode($response);

    }

    public function MunicipiosVisConjunto($data)
    {

        try {
            $stmt = $this->_DB->prepare("SELECT municipio
					FROM municipios m
					INNER JOIN regiones r ON m.codigoRg=r.codigoRg
					WHERE region = :region
					ORDER BY municipio");
            $stmt->execute([':region' => $data]);

            if ($stmt->rowCount()) {
                $response = [$stmt->fetchAll(PDO::FETCH_ASSOC), 201];
            } else {
                $response = ['', 400];
            }

        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        $this->_DB = null;
        echo json_encode($response);
    }

    public function MunicipioVisConjuntoUpdate($data)
    {

        try {

            $stmt = $this->_DB->prepare("SELECT municipio
                                                FROM visitasenconjunto v
                                                WHERE id = :id");
            $stmt->execute([':id' => $data]);

            if ($stmt->rowCount()) {
                $response = [$stmt->fetchAll(PDO::FETCH_ASSOC), 201];
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

