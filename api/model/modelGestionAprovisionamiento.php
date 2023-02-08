<?php
require_once '../class/conection.php';

class modelGestionAprovisionamiento
{

    private $_DB;

    public function __construct()
    {
        $this->_DB = new Conection();
    }

    public function updateEnGestionResponse()
    {
        try {
            session_start();
            $hoy = date('Y-m-d');

            $stmt = $this->_DB->prepare("SELECT id,
                                                   engestion,
                                                   pedido,
                                                   observContingencia,
                                                   observContingenciaPortafolio,
                                                   (case when acepta is null then 'Pendiente' else acepta end)                     AS acepta,
                                                   (case when aceptaPortafolio is null then 'Pendiente' else aceptaPortafolio end) AS aceptaPortafolio
                                            FROM contingencias
                                            WHERE logindepacho = :login
                                              AND horagestion BETWEEN (:fechaini) AND (:fechafin)");

            $stmt->execute([':login' => $_SESSION['login'], ':fechaini' => "$hoy 00-00-00", ':fechafin' => "$hoy 23-59-59"]);

            if ($stmt->rowCount()) {
                $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $response = 0;
            }

        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
        $this->_DB = null;
        return $response;
    }
}
