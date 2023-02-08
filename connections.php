<?php
//header('Content-type: text/html; charset=utf-8');

function getConnPortalbd(){

        //$db = new mysqli('10.100.82.125', 'root', '123456', 'test');
	$Host="10.100.82.125";
	$User="root";
	$Pwd="123456";
	$Bd="portalbd";

        $db = new mysqli($Host, $User, $Pwd, $Bd);

        if($db->connect_errno > 0){
            die('Unable to connect to database [' . $db->connect_error . ']');
        }

	return $db;

}

function getConnClick(){

    $serverName = "NETV-PSQL09-05";
    $connectionInfo = array( "Database"=>"Service Optimization", "UID"=>"BI_Clicksoftware", "PWD"=>"6n`Vue8yYK7Os4D-y");
    $conn = sqlsrv_connect( $serverName, $connectionInfo);

    if( $conn ) {
        echo "Conexión establecida.<br />";
    }else{
        echo "Conexión no se pudo establecer.<br />";
        die( print_r( sqlsrv_errors(), true));
    }

    return $conn;
}

function getConnSeguimientoPedidos(){

        //$db = new mysqli('10.100.82.125', 'root', '123456', 'test');
    //$Host="10.100.72.10";
    //$Host="localhost";
    //$Host="10.100.82.73";
    $Host="10.100.88.2";
    /* $User="root";
    $Pwd="9A$!4As02WXw"; */
    $User='seguimientocrud';
    $Pwd='3Po1Ep56L7WGa$mY';
    $Bd='seguimientopedidos';

    $db = new mysqli($Host, $User, $Pwd, $Bd);
    //$db->set_charset("utf8");

        if($db->connect_errno > 0){
            die('Unable to connect to database [' . $db->connect_error . ']');
        }

    return $db;

}

function getConnPortalbd03(){

        //$db = new mysqli('10.100.82.125', 'root', '123456', 'test');
        $Host="10.100.82.156";
        $User="root";
        $Pwd="123456";
        $Bd="gestor_informes";

        $db = new mysqli($Host, $User, $Pwd, $Bd);
        //$db->set_charset("utf8");

        if($db->connect_errno > 0){
            die('Unable to connect to database [' . $db->connect_error . ']');
        }

        return $db;

}

function getConnScheduling(){

        $Host="10.100.82.125";
        $User="root";
        $Pwd="123456";
        $Bd="scheduling";

        $db1 = new mysqli($Host, $User, $Pwd, $Bd);
        //$db1->set_charset("utf8");

        if($db1->connect_errno > 0){
            die('Unable to connect to database [' . $db1->connect_error . ']');
        }

        return $db1;

}

function getConnFenix(){
        $host="10.120.53.129";
        //$user="ebarrien";
        $user="CTA_ASIGNACION";
        //$pwd="eb4rr1en";
        $pwd="PWD123ABC";

	putenv("ORACLE_HOME=/usr/lib/oracle/12.1/client64/lib/");
	putenv("LD_LIBRARY_PATH=/usr/lib/oracle/12.1/client64/lib/:/lib:/usr/lib");

        putenv("NLS_LANG=LATIN AMERICAN SPANISH_AMERICA.WE8ISO8859P9");
        putenv("NLS_DATE_FORMAT=DD/MM/RRRR");

        $db = "(DESCRIPTION  =  (ADDRESS = (PROTOCOL = TCP)(HOST = $host)(PORT = 1521)) ".
        "(CONNECT_DATA = (SERVER = DEDICATED)(SERVICE_NAME = FENIXUNE) ) )";

        // Connects to the XE service (i.e. database) on the "localhost" machine
        $conn = oci_connect($user, $pwd, $db);

        if (!$conn) {
            $e = oci_error();
            trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
            echo "ERROR: $e";
            return;
        }

        return $conn;

}

function getConnFenixSTBY(){
        $host="10.100.67.64";
        $user="macevedg";
        $pwd="mauricio1";

        putenv("NLS_LANG=LATIN AMERICAN SPANISH_AMERICA.WE8ISO8859P9");
        putenv("NLS_DATE_FORMAT=DD/MM/RRRR");

        $db = "(DESCRIPTION =
        (ADDRESS = (PROTOCOL = TCP)(HOST = $host)(PORT = 1521))
        (CONNECT_DATA =
        (SERVER = DEDICATED)
        (SERVICE_NAME = FENIXUNE)
        )
        )";

        // Connects to the XE service (i.e. database) on the "localhost" machine
        $conn = oci_connect($user, $pwd, $db);

        if (!$conn) {
            $e = oci_error();
            trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
            echo "ERROR: $e";
            return;
        }

        return $conn;

}



function getConnFenixBogota(){
        $host="10.133.3.40";
        $user="sql_uebarrien";
        $pwd="UNETE2030";

        putenv("NLS_LANG=LATIN AMERICAN SPANISH_AMERICA.WE8ISO8859P9");
        //putenv("NLS_LANG=LATIN AMERICAN SPANISH.AMERICAN");

        $db = "(DESCRIPTION =
        (ADDRESS = (PROTOCOL = TCP)(HOST = $host)(PORT = 1521))
        (CONNECT_DATA =
        (SERVER = DEDICATED)
        (SERVICE_NAME = FENIX)
        )
        )";

        // Connects to the XE service (i.e. database) on the "localhost" machine
        $conn = oci_connect($user, $pwd, $db);

        if (!$conn) {
            $e = oci_error();
            trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
            echo "ERROR: $e";
            return;
        }
        return $conn;

}

function getConnAgendamiento(){

        //$db = new mysqli('10.100.82.125', 'root', '123456', 'test');
        $Host="10.100.67.36";
        $User="usr_agen_fergonz";
        $Pwd="4X@a893R50449b02afY5d896$70#3a64";
        $Bd="dbAgendamiento";


        $db = new mysqli($Host, $User, $Pwd, $Bd);
        //$db->set_charset("utf8");

        if($db->connect_errno > 0){
            die('Unable to connect to database [' . $db->connect_error . ']');
        }

        return $db;

}
