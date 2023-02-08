<?php

//Habilita Errores - Deshabilitar para funcionar
error_reporting(0);
//ini_set('display_errors', '1');

//Incluir conexiones a las bases de datos
require_once "Rest.inc.php";
include_once "/var/www/html/seguimientopedidos/connections.php";
require_once "../Phpexcel/Classes/PHPExcel/IOFactory.php";
require_once "../Phpexcel/Classes/PHPExcel.php";
require_once '../api/class/conection.php';

date_default_timezone_set('America/Bogota');

class API extends REST {

	public $data = "";

	//const DB_SERVER = "10.100.82.73";
	const DB_SERVER = "10.100.88.2";
	const DB_USER = "root";
	const DB_PASSWORD = "123456";
	const DB = "seguimientopedidos";

	private $db = NULL;
	private $portalbd = NULL;
	private $mysqli03 = NULL;
	private $connf = NULL;
	private $connfstby = NULL;
	private $connfb = NULL;
	private $connseguimiento = NULL;
	private $mysqliScheduling = NULL;
	public static $doink = 0;

	private $pedidoGlobal = '';

	public function __construct() {
		parent::__construct(); // Init parent contructor
		//$this->dbConnect();					// Initiate Database connection
		$this->dbSeguimientoConnect();
		$this->_conbd = new Conection();
	}

	/*
		                 *      Encode array into JSON
	*/
	private function json($data) {
		if (is_array($data)) {
			return json_encode($data);
		}
	}

	/*
		 *  Connect to Database
		*/
	//Conexion a Seguimiento
	private function dbSeguimientoConnect() {

		$this->connseguimiento = getConnSeguimientoPedidos();
	}

	//Conexion a Click
	private function dbClickConnect() {

		$this->connclick = getConnClick();
	}

	//Conexion al Gestor Operaciones
	private function dbConnect() {

		$this->portalbd = getConnPortalbd();
	}

	//Conexion al maquina virtual 3, informes
	private function dbConnectInformes() {

		$this->mysqliinf = getConnPortalbd03();
	}

	//Conexiones a Fenix
	private function dbFenixConnect() {

		$this->connf = getConnFenix();
	}

	private function dbFenixSTBYConnect() {

		$this->connfstby = getConnFenixSTBY();
	}

	private function dbFenixBogotaConnect() {

		$this->connfb = getConnFenixBogota();
	}

	/* Dynmically call the method based on the query string
		 */
	public function processApi() {
		$func = strtolower(trim(str_replace("/", "", $_REQUEST['x'])));

		if ((int) method_exists($this, $func) > 0) {
			$this->$func();
		} else {
			$this->response("No, i dont know this service!!  ", 404);
		}

	}

	//Incia Servicios para consumir

	//Funcion para loguear los usuarios
	private function loginUser() {

		$usuarioid = "";
		$password = "";

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		//$this->dbConnect();
		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$login = $params['datosAutenticacion'];
		$usuarioid = $login['username'];
		$password = $login['password'];
		$today = date('Y-m-d');
		$fecha = date('Y-m-d H:i:s');
		$usuarioIp = $_SERVER['REMOTE_ADDR'];
		$usuarioPc = gethostbyaddr($usuarioIp);
		$aplicacion = "Seguimiento";

		//se logea, activity feed
		//echo "PHP: aca \n".$usuarioid." - ".$password;

		if (!empty($usuarioid) and !empty($password)) {

			$sqlLogin = "SELECT ID, " .
				" LOGIN, " .
				"  NOMBRE, " .
				"  IDENTIFICACION, " .
				"  PERFIL, " .
				"  PASSWORD " .
				" FROM usuarios " .
				" where login = '$usuarioid' and password='$password'";

			//echo $sqlLogin;

			$rst = $this->connseguimiento->query($sqlLogin);
			//echo $rst;

			if ($rst->num_rows > 0) {
			//update just the status, not dates cuz he already loged in early
				$resultado = array();

				while ($row = $rst->fetch_assoc()) {

					$row['nombre'] = $row['nombre'];
					$resultado[] = $row;

				}

				/********************************/
				$sqlIngresoSalida = "SELECT " .
					" id " .
					", fecha_ingreso " .
					", date_format(fecha_ingreso,'%H:%i:%s') as hora_ingreso " .
					" FROM registro_ingresoSeguimiento " .
					" WHERE fecha_ingreso between '$today 00:00:00' " .
					" and '$today 23:59:59' and idusuario='$usuarioid' ";

				$rIO = $this->connseguimiento->query($sqlIngresoSalida);

				if ($rIO->num_rows > 0) {
				//update just the status, not dates cuz he already loged in early
					$result1 = $rIO->fetch_assoc();
					$idd = $result1['id'];
					$sqllogin = "update registro_ingresoSeguimiento set status='logged in', ingresos=ingresos+1 where id=$idd";

					$rrr = $this->connseguimiento->query($sqllogin);

					$row['fecha_ingreso'] = $result1['fecha_ingreso'];
					$row['hora_ingreso'] = $result1['hora_ingreso'];

				} else {
				//make an insert, first time logged in today

					$sqllogin = "insert into registro_ingresoSeguimiento (idusuario,status,fecha_ingreso, ip, pc, aplicacion) " .
						"values(UPPER('$usuarioid'),'logged in','$fecha', '$usuarioIp', '$usuarioPc', '$aplicacion')";
					//    echo $sqllogin;
					$rrr = $this->connseguimiento->query($sqllogin);

					$sqllogin2 = "SELECT fecha_ingreso " .
						", date_format(fecha_ingreso,'%H:%i:%s') as hora_ingreso FROM " .
						" registro_ingresoSeguimiento " .
						" WHERE fecha_ingreso between '$today 00:00:00' and '$today 23:59:59' " .
						" and idusuario='$usuarioid' limit 1";

					$rs = $this->connseguimiento->query($sqllogin2);

					if ($rs->num_rows > 0) {
						$result1 = $rs->fetch_assoc();
						$row['fecha_ingreso'] = $result1['fecha_ingreso'];
						$row['hora_ingreso'] = $result1['hora_ingreso'];
					} else {
						$row['fecha_ingreso'] = 'SinFecha';
						$row['hora_ingreso'] = 'SinHora';
					}
				}

				/********************************/

				$this->response($this->json(($resultado)), 201);
			} else {
				$this->response($this->json('Usuario No existe.'), 406);
			}
		} else {
			$this->response($this->json("Error"), 406);
		}

	} //Funcion para loguear los usuarios

	private function logout() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);
		//$params = file_get_contents('php://input');

		$usuarioid = $params['USUARIO_ID'];
		$fecha = $params['fecha'];
		$perfil = $params['PERFIl'];
		$today = date("Y-m-d");

		//var_dump($params);

		if (!empty($usuarioid)) {

			$query = "SELECT id, fecha_ingreso " .
				", date_format(fecha_ingreso,'%H:%i:%s') as hora_ingreso, SEC_TO_TIME((TIMESTAMPDIFF(second, fecha_ingreso, '$fecha' ))) total FROM " .
				" registro_ingresoSeguimiento " .
				" WHERE fecha_ingreso between '$today 00:00:00' and '$today 23:59:59' " .
				" and idusuario='$usuarioid' limit 1";

			$r = $this->connseguimiento->query($query) or die($this->connemtel->error . __LINE__);

			if ($r->num_rows > 0) {
				$result = $r->fetch_assoc();
				// If success everythig is good send header as "OK" and user details
				$idd = $result['id'];
				$total_dia = $result['total'];

				$hora = substr($total_dia, 0, 2);
				$minutos = substr($total_dia, 3, 2);
				$segundos = substr($total_dia, 6, 2);

				$totalminutos = round($hora * 60 + $minutos + $segundos / 60, 2);
				$parametro = ", hora = '$hora', minutos = '$minutos', segundos = '$segundos', total_factura = '$totalminutos' ";

				$sqllogin = "update registro_ingresoSeguimiento set status='logged off',fecha_salida='$fecha',salidas=salidas+1, " .
					"total_dia = '$total_dia' $parametro where id=$idd";

				$rr = $this->connseguimiento->query($sqllogin);
				$this->response($this->json('logged out'), 201);

			} //doesnt have sense, do nothing
			$this->response($this->json('User do not exist!!!'), 400); // If no records "No Content" status

		}

		//$error = array('status' => "Failed", "msg" => "Invalid User Name or password");
		$error = "No se pudo cerrar sesión ";
		$this->response($this->json(array($error)), 400);
	}

	/*REVISAR ESTA CONSULTA DE MESAOFFLINE*/
	private function buscarPedidoSegui() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();
		$params = json_decode(file_get_contents('php://input'), true);

		$pedido = $params['pedido'];
		$producto = $params['producto'];
		$remite = $params['remite'];

		$sqlpedido = ("SELECT * FROM registros WHERE pedido = '$pedido'");

		$rst = $this->connseguimiento->query($sqlpedido);

		if ($rst->num_rows > 0 || $remite == "Gestión contact center") {

			/*ORGANIZAR ESTE QUERY PORQUE ESTA DEJANDO DUPLICAR PEDIDO*/
			$sqlpedidocontingencia = ("	SELECT * FROM contingencias
										WHERE acepta IS NOT NULL
										AND aceptaPortafolio IS NOT NULL
										AND pedido = '$pedido'
									");

			$rstContingencia = $this->connseguimiento->query($sqlpedidocontingencia);

			if ($rstContingencia->num_rows > 0) {

				$this->response($this->json("Aceptado o rechazado"), 201);

			} else {

				/*qurey que permite dejas subir la info realiza validadcion para no dejar duplicar pedidos en gestion*/
				$sqlpedidoproducto = ("	SELECT * FROM contingencias
										WHERE acepta is null
										AND aceptaPortafolio IS NULL
										AND pedido = '$pedido'
										AND producto = '$producto'
										AND accion IN('Contingencia','Cambio de equipo','Refresh','Crear Espacio','crear cliente','Registros ToIP','mesaOffline', 'Cambio EID')
									");

				$rstpedidoProducto = $this->connseguimiento->query($sqlpedidoproducto);

				if ($rstpedidoProducto->num_rows > 0) {

					$this->response($this->json("no se guarda"), 401);
				} else {

					$this->response($this->json("se guarda"), 201);
				}
			}
		} else {
			$this->response($this->json("No existe"), 400);
		}
	}

	private function buscarPedido() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		//$this->dbConnect();
		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$url = $params['url'] . $params['pedidos'];

		$json = file_get_contents('$url');
		$obj = json_decode($json);

		//busco pedido, activity feed

		/*
			$resultado=array();

			$resultado[] = file_get_contents('$url');
			//echo "PHP: aca \n".$usuarioid." - ".$password;
			var_dump($resultado);
			$this->response($this->json(($resultado)), 201);
		*/
	}

	private function editarUsuario() {

		$usuarioid = "";
		$password = "";

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		//$this->dbConnect();
		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$login = $params['datosEdicion'];
		$id = $login['ID'];
		$usuarioid = $login['LOGIN'];
		$nombre = $login['NOMBRE'];
		$identificacion = $login['IDENTIFICACION'];
		$perfil = $login['perfil'];
		$password = $login['PASSWORD'];
		//echo var_dump($login);
		$sqlUsuario = "update usuarios set " .
			"nombre='$nombre',identificacion='$identificacion', " .
			"login='$usuarioid',password='$password',perfil='$perfil' where id='$id'";
		//echo $sqlUsuario;
		$rst = $this->connseguimiento->query($sqlUsuario);

		$this->response($this->json('Usuario actualizado'), 201);
		//actualiza usuario, activity feed
	}

	private function editarRegistro() {

		$usuarioid = "";
		$password = "";

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		//$this->dbConnect();
		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'));

		//var_dump($params);exit();

		$datos = $params->datosEdicion;
		$login = $params->datosLogin;

		$user = $login->LOGIN;

		$accion = $datos->accion;
		$tipo_pendiente = $datos->tipo_pendiente;
		$observaciones = $params->datosEdicion;
		$id = $datos->id;


		$sqleditRegistro = "update registros set " .
			"asesor = '$user', accion='$accion',tipo_pendiente='$tipo_pendiente', observaciones='$observaciones' " .
			"where id=$id";

		//echo $sqleditRegistro;
		$rst = $this->connseguimiento->query($sqleditRegistro);

		$this->response($this->json('Pedido actualizado'), 201);
		//edita registro, activity feed
	}

	private function editarTecnico() {

		$usuarioid = "";
		$password = "";

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		//$this->dbConnect();
		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$login = $params['datosTecnico'];
		$identificacion = $login['IDENTIFICACION'];
		$nombre = $login['NOMBRE'];
		$ciudad = $login['CIUDAD'];
		$celular = $login['CELULAR'];
		$empresa = $login['empresa'];
		$id = $login['ID'];

		$sqlUsuario = "update tecnicos set " .
			"nombre='$nombre',identificacion='$identificacion', " .
			"ciudad='$ciudad',celular='$celular',empresa='$empresa' where id='$id'";
		//echo $sqlUsuario;
		$rst = $this->connseguimiento->query($sqlUsuario);

		$this->response($this->json('Usuario actualizado'), 201);
		//edita tecnico, activity feed
	}

	private function editAlarma() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		//$this->dbConnect();
		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$datosAlarma = $params['datosAlarma'];

		$nombre_alarma = $datosAlarma['nombre_alarma'];
		$ciudad = $datosAlarma['ciudad'];
		$tecnologia_producto = $datosAlarma['tecnologia_producto'];
		$proceso = $datosAlarma['proceso'];
		$accion = $datosAlarma['accion'];
		$id = $datosAlarma['id'];
		$subaccion = $datosAlarma['subaccion'];
		$mensaje = $datosAlarma['mensaje'];

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

		$sqlUsuario = "update alarmas set " .
			"nombre_alarma='$nombre_alarma',ciudad='$ciudad', " .
			"tecnologia_producto='$tecnologia_producto',proceso='$proceso',accion='$accion',subaccion='$subaccion', " .
			"mensaje='$mensaje', cantidad_campos='$cantidad_campos' where id='$id'";
		//echo $sqlUsuario;
		$rst = $this->connseguimiento->query($sqlUsuario);

		$this->response($this->json('Usuario actualizado'), 201);
		//edita alarma, activity feed
	}

	private function creaTecnico() {

		$usuarioid = "";
		$password = "";

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		//$this->dbConnect();
		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$login = $params['datosCrearTecnico'];
		$identificacion = $login['IDENTIFICACION'];
		$nombre = $login['NOMBRE'];
		$ciudad = $login['CIUDAD'];
		$celular = $login['CELULAR'];
		$empresa = $login['empresa'];
		$id_tecnico_forma = $params['id_tecnico'];

		if ($identificacion == "") {
			$identificacion = $id_tecnico_forma;
		}
		$sql = " INSERT INTO tecnicos ( " .
			" IDENTIFICACION, " .
			" NOMBRE, " .
			" CIUDAD, " .
			" CELULAR, " .
			" EMPRESA) values ( " .
			" '$identificacion', " .
			" '$nombre', " .
			" '$ciudad', " .
			" '$celular', " .
			" '$empresa')";
		//echo $sql;
		$rst = $this->connseguimiento->query($sql);
		$this->response($this->json('Usuario creado'), 201);
		//crea tecnico, activity feed
	}

	private function creaUsuario() {

		$usuarioid = "";
		$password = "";

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		//$this->dbConnect();
		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$login = $params['datosCrearUsuario'];
		$identificacion = $login['IDENTIFICACION'];
		$nombre = $login['NOMBRE'];
		$loginUser = $login['LOGIN'];
		$perfil = $login['PERFIL'];
		$password = $login['PASSWORD'];

		$sql = " INSERT INTO usuarios ( " .
			" IDENTIFICACION, " .
			" NOMBRE, " .
			" LOGIN, " .
			" PERFIL, " .
			" PASSWORD) values ( " .
			" '$identificacion', " .
			" '$nombre', " .
			" '$loginUser', " .
			" '$perfil', " .
			" '$password')";

		$rst = $this->connseguimiento->query($sql);

		$this->response($this->json('Usuario creado'), 201);
		//crea tecnico, activity feed
	}

	private function listadoUsuarios() {

		if ($this->get_request_method() != "GET") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$pagina = $this->_request['page'];
		$concepto = $this->_request['concepto'];
		$usuario = $this->_request['usuario'];
		$parametro = "";
		//echo "selección".$buscar;
		//echo "dato".$usuario;

		//$today = date("Y-m-d");

		if ($pagina == "undefined") {
			$pagina = "0";
		} else {
			$pagina = $pagina - 1;
		}

		$pagina = $pagina * 100;

		if ($concepto == 'nombre') {
			$parametro = "and a.nombre LIKE '%$usuario%'";

		} else if ($concepto == 'login') {
			$parametro = " and a.login LIKE '%$usuario%'";
		};

		$query = "SELECT a.ID, " .
			" a.NOMBRE, " .
			"  a.IDENTIFICACION, " .
			"  a.LOGIN, a.perfil, " .
			"  (select b.nombre from perfiles b where b.perfil=a.perfil) as PERFIL, " .
			"  a.PASSWORD " .
			" FROM usuarios a" .
			"	where 1=1 " .
			" 	$parametro order by a.nombre ASC " .
			" limit 100 offset $pagina ";

		$queryCount = " select count(*) as Cantidad from usuarios h " .
			" where 1=1 " .
			" $parametro ";
		//echo $query;
		$rr = $this->connseguimiento->query($queryCount);
		$counter = 0;
		if ($rr->num_rows > 0) {
			$result = array();
			if ($row = $rr->fetch_assoc()) {
				$counter = $row['Cantidad'];
			}
		}

		$rst = $this->connseguimiento->query($query);

		//echo $this->mysqli->query($sqlLogin);
		//
		if ($rst->num_rows > 0) {

			$resultado = array();

			while ($row = $rst->fetch_assoc()) {

				//var_dump($row);

				$row['ID'] = utf8_encode($row['ID']);
				$row['NOMBRE'] = utf8_encode($row['NOMBRE']);
				$row['IDENTIFICACION'] = utf8_encode($row['IDENTIFICACION']);
				$row['LOGIN'] = utf8_encode($row['LOGIN']);
				$row['perfil'] = utf8_encode($row['perfil']);
				$row['PERFIL'] = utf8_encode($row['PERFIL']);
				$row['PASSWORD'] = utf8_encode($row['PASSWORD']);

				$resultado[] = $row;

			}
			$this->response($this->json(array($resultado, $counter)), 201);

		} else {
			$error = array();

			$this->response($this->json($error), 400);
		}
	}

	private function observacionAsesor() {
		if ($this->get_request_method() != "GET") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$pedido = $this->_request['pedido'];

		$query = "SELECT ObservacionAsesor " .
			"FROM BrutalForce where PedidoDespacho = '$pedido'";

		$rst = $this->connseguimiento->query($query);
		$resultado = array();

		if ($rst->num_rows > 0) {

			$resultado = array();

			while ($row = $rst->fetch_assoc()) {
				$row['ObservacionAsesor'] = utf8_encode($row['ObservacionAsesor']);
				$resultado[] = $row;

			}
			$this->response($this->json(array($resultado)), 201);

		} else {
			$error = array();

			$this->response($this->json($error), 400);
		}
	}

	private function contadorpedientesBF() {
		if ($this->get_request_method() != "GET") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$query = "SELECT(SELECT COUNT(PedidoDespacho) FROM BrutalForce WHERE pedidobloqueado IS NULL AND acciondespacho IN ('Renumerar', 'One-Tv', 'B2B', 'Consulta de la oferta') AND gestionasesor = '1') + (SELECT COUNT(PedidoDespacho) FROM BrutalForce WHERE pedidobloqueado = '1' AND gestionAsesor = '1') AS Pendientes";

		$rst = $this->connseguimiento->query($query);
		$resultado = array();

		if ($rst->num_rows > 0) {

			$resultado = array();

			while ($row = $rst->fetch_assoc()) {
				$resultado[] = $row;

			}
			$this->response($this->json(array($resultado)), 201);

		} else {
			$error = array();

			$this->response($this->json($error), 400);
		}
	}

	private function diferenciasClick() {

		if ($this->get_request_method() != "GET") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$fecha = $this->_request['fecha'];
		$fechaanterior = date('Y-m-d', strtotime('-1 day', strtotime($fecha)));

		//$today = date("Y-m-d");

		$query = "TRUNCATE TABLE view_diferencias_Click";
		$rr = $this->connseguimiento->query($query);

		//insert jornadaID
		$query = "INSERT INTO view_diferencias_Click " .
			"(`razon`) VALUES ('Pendiente por un producto'), " .
			"('No tiene Ordenes de trabajo'), " .
			"('Sin novedades'), " .
			"('FENIX BOGOTA'), " .
			"('Pedido anulado o pendiente por producto'), " .
			"('Inconcistencia en componente - Fenix')";
		$rr = $this->connseguimiento->query($query);

		//carga de agendados
		$sqlcarga = "select pro.razon, " .
			"(select count(razon) " .
			"from seguimientoClick " .
			"where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') " .
			"and razon = pro.razon) total_actual, " .
			"(select count(razon) " .
			"from seguimientoClick " .
			"where fecha_cita BETWEEN ('$fechaanterior 00:00:00') AND ('$fechaanterior 23:59:59') " .
			"and razon = pro.razon) total_anterior " .
			"from seguimientoClick pro " .
			"group by pro.razon ";

		$rr = $this->connseguimiento->query($sqlcarga);

		while ($row = $rr->fetch_assoc()) {

			$totalrazon_actual = $row['total_actual'];
			$totalrazon_anterior = $row['total_anterior'];
			$razon = $row['razon'];
			$total_actual = $total_actual + $totalrazon_actual;
			$total_anterior = $total_anterior + $totalrazon_anterior;

			$sqlupdate = "UPDATE view_diferencias_Click " .
				"SET `total_actual`='$totalrazon_actual', " .
				"`total_anterior`='$totalrazon_anterior' " .
				" WHERE `razon`='$razon'; ";

			$rupdate = $this->connseguimiento->query($sqlupdate);
		}

		$query = "SELECT * " .
			"FROM view_diferencias_Click";

		$rst = $this->connseguimiento->query($query);
		$resultado = array();

		if ($rst->num_rows > 0) {

			$resultado = array();

			while ($row = $rst->fetch_assoc()) {

				$resultado[] = $row;

			}
			$this->response($this->json(array($resultado, $total_actual, $total_anterior)), 201);

		} else {
			$error = array();

			$this->response($this->json($error), 400);
		}
	}

	private function seguimientoClick() {

		if ($this->get_request_method() != "GET") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$fecha = $this->_request['fecha'];
		$mes = date("m", strtotime($fecha));
		$año = date("Y", strtotime($fecha));
		//$fechaanterior = date ( 'Y-m-d' , strtotime ( '-1 day' , strtotime ( $fecha ) ) );

		//echo "este es el mes".$mes;

		$query = "TRUNCATE TABLE seguimiento_Click_resumen";
		$rr = $this->connseguimiento->query($query);

		//insert jornadaID

		$sqldias = "select distinct fecha_cita from seguimientoClick " .
			"where fecha_cita BETWEEN " .
			"('$año-$mes-01 00:00:00') AND ('$año-$mes-31 23:59:59')";

		$rr = $this->connseguimiento->query($sqldias);

		while ($row = $rr->fetch_assoc()) {

			$fecha_cita = $row['fecha_cita'];

			$query = "INSERT INTO seguimiento_Click_resumen " .
				"(`fecha`) VALUES ('$fecha_cita') ";

			$insertfecha = $this->connseguimiento->query($query);
			//Se visita y queda atendido
			$query = "select count(estado_final) total from seguimientoClick " .
				"where fecha_cita BETWEEN ('$fecha_cita 00:00:00') AND ('$fecha_cita 23:59:59') " .
				"and estado_final = 'Se visita y queda atendido'";

			$sqltotal = $this->connseguimiento->query($query);
			$total = 0;
			if ($sqltotal->num_rows > 0) {
				$result = array();
				if ($row = $sqltotal->fetch_assoc()) {
					$total = $row['total'];
				}
			}

			$sqlupdate = "UPDATE seguimiento_Click_resumen " .
				"SET `visita_atendido`='$total' " .
				" WHERE `fecha`='$fecha_cita'; ";

			$rupdate = $this->connseguimiento->query($sqlupdate);

			//Fin Se visita y queda atendido

			//Sube a Click pero no se visita
			$query = "select count(estado_final) total from seguimientoClick " .
				"where fecha_cita BETWEEN ('$fecha_cita 00:00:00') AND ('$fecha_cita 23:59:59') " .
				"and estado_final = 'Sube a Click pero no se visita'";

			$sqltotal = $this->connseguimiento->query($query);
			$total = 0;
			if ($sqltotal->num_rows > 0) {
				$result = array();
				if ($row = $sqltotal->fetch_assoc()) {
					$total = $row['total'];
				}
			}

			$sqlupdate = "UPDATE seguimiento_Click_resumen " .
				"SET `sube_no_visita`='$total' " .
				" WHERE `fecha`='$fecha_cita'; ";

			$rupdate = $this->connseguimiento->query($sqlupdate);
			//Sube a Click pero no se visita

			//no sube
			$query = "select count(estado_final) total from seguimientoClick " .
				"where fecha_cita BETWEEN ('$fecha_cita 00:00:00') AND ('$fecha_cita 23:59:59') " .
				"and estado_final = 'No subieron a click'";

			$sqltotal = $this->connseguimiento->query($query);
			$total = 0;
			if ($sqltotal->num_rows > 0) {
				$result = array();
				if ($row = $sqltotal->fetch_assoc()) {
					$total = $row['total'];
				}
			}

			$sqlupdate = "UPDATE seguimiento_Click_resumen " .
				"SET `no_sube`='$total' " .
				" WHERE `fecha`='$fecha_cita'; ";

			$rupdate = $this->connseguimiento->query($sqlupdate);
			//No sube

			//reagendado
			$query = "select count(estado_final) total from seguimientoClick " .
				"where fecha_cita BETWEEN ('$fecha_cita 00:00:00') AND ('$fecha_cita 23:59:59') " .
				"and estado_final = 'Reagendado'";

			$sqltotal = $this->connseguimiento->query($query);
			$total = 0;
			if ($sqltotal->num_rows > 0) {
				$result = array();
				if ($row = $sqltotal->fetch_assoc()) {
					$total = $row['total'];
				}
			}

			$sqlupdate = "UPDATE seguimiento_Click_resumen " .
				"SET `reagendado`='$total' " .
				" WHERE `fecha`='$fecha_cita'; ";

			$rupdate = $this->connseguimiento->query($sqlupdate);
			//reagendado

			//mal_agendado_no_sube
			$query = "select count(estado_final) total from seguimientoClick " .
				"where fecha_cita BETWEEN ('$fecha_cita 00:00:00') AND ('$fecha_cita 23:59:59') " .
				"and estado_final = 'Pedido mal agendado, no sube a Click'";

			$sqltotal = $this->connseguimiento->query($query);
			$total = 0;
			if ($sqltotal->num_rows > 0) {
				$result = array();
				if ($row = $sqltotal->fetch_assoc()) {
					$total = $row['total'];
				}
			}

			$sqlupdate = "UPDATE seguimiento_Click_resumen " .
				"SET `mal_agendado_no_sube`='$total' " .
				" WHERE `fecha`='$fecha_cita'; ";

			$rupdate = $this->connseguimiento->query($sqlupdate);
			//mal_agendado_no_sube

			//Se visito pero quedo incompleto
			$query = "select count(estado_final) total from seguimientoClick " .
				"where fecha_cita BETWEEN ('$fecha_cita 00:00:00') AND ('$fecha_cita 23:59:59') " .
				"and estado_final = 'Se visito pero quedo incompleto'";

			$sqltotal = $this->connseguimiento->query($query);
			$total = 0;
			if ($sqltotal->num_rows > 0) {
				$result = array();
				if ($row = $sqltotal->fetch_assoc()) {
					$total = $row['total'];
				}
			}

			$sqlupdate = "UPDATE seguimiento_Click_resumen " .
				"SET `visita_incompleto`='$total' " .
				" WHERE `fecha`='$fecha_cita'; ";

			$rupdate = $this->connseguimiento->query($sqlupdate);
			//Se visito pero quedo incompleto

			//No se visita pero se cambia estado
			$query = "select count(estado_final) total from seguimientoClick " .
				"where fecha_cita BETWEEN ('$fecha_cita 00:00:00') AND ('$fecha_cita 23:59:59') " .
				"and estado_final = 'No se visita pero se cambia estado'";

			$sqltotal = $this->connseguimiento->query($query);
			$total = 0;
			if ($sqltotal->num_rows > 0) {
				$result = array();
				if ($row = $sqltotal->fetch_assoc()) {
					$total = $row['total'];
				}
			}

			$sqlupdate = "UPDATE seguimiento_Click_resumen " .
				"SET `no_visita_cambia_estado`='$total' " .
				" WHERE `fecha`='$fecha_cita'; ";

			$rupdate = $this->connseguimiento->query($sqlupdate);
			//No se visita pero se cambia estado

			//Visita cancelada
			$query = "select count(estado_final) total from seguimientoClick " .
				"where fecha_cita BETWEEN ('$fecha_cita 00:00:00') AND ('$fecha_cita 23:59:59') " .
				"and estado_final = 'Visita cancelada'";

			$sqltotal = $this->connseguimiento->query($query);
			$total = 0;
			if ($sqltotal->num_rows > 0) {
				$result = array();
				if ($row = $sqltotal->fetch_assoc()) {
					$total = $row['total'];
				}
			}

			$sqlupdate = "UPDATE seguimiento_Click_resumen " .
				"SET `cancelada`='$total' " .
				" WHERE `fecha`='$fecha_cita'; ";

			$rupdate = $this->connseguimiento->query($sqlupdate);
			//Visita cancelada

		}

		$query = "SELECT fecha, visita_atendido, sube_no_visita, no_sube, reagendado, " .
			"mal_agendado_no_sube, " .
			"visita_incompleto, no_visita_cambia_estado, cancelada " .
			"FROM seguimiento_Click_resumen " .
			"where fecha BETWEEN ('$año-$mes-01 00:00:00') AND ('$año-$mes-31 23:59:59') ";

		$r = $this->connseguimiento->query($query) or die($this->connseguimiento->error . __LINE__);

		if ($r->num_rows > 0) {
			$result = array();
			$categorias = array();
			$visita_atendido = array();
			$sube_no_visita = array();
			$no_sube = array();
			$reagendado = array();
			$mal_agendado_no_sube = array();
			$visita_incompleto = array();
			$no_visita_cambia_estado = array();
			$cancelada = array();
			$i = 1;
			while ($row = $r->fetch_assoc()) {

				$cate = $row['fecha'];
				$visita_aten = $row['visita_atendido'];
				$sube_no_vis = $row['sube_no_visita'];
				$no_su = $row['no_sube'];
				$reage = $row['reagendado'];
				$mal_agendado_no_su = $row['mal_agendado_no_sube'];
				$visita_incom = $row['visita_incompleto'];
				$no_visita_cambia_est = $row['no_visita_cambia_estado'];
				$cancel = $row['cancelada'];

				$categorias[] = array("label" => "$cate");
				$visita_atendido[] = array("value" => "$visita_aten");
				$sube_no_visita[] = array("value" => "$sube_no_vis");
				$no_sube[] = array("value" => "$no_su");
				$reagendado[] = array("value" => "$reage");
				$mal_agendado_no_sube[] = array("value" => "$mal_agendado_no_su");
				$visita_incompleto[] = array("value" => "$visita_incom");
				$no_visita_cambia_estado[] = array("value" => "$no_visita_cambia_est");
				$cancelada[] = array("value" => "$cancel");
				$i++;
			}
			//echo  $vista_atendido;
			$this->response($this->json(array($categorias, $visita_atendido, $sube_no_visita, $no_sube, $reagendado, $mal_agendado_no_sube, $visita_incompleto, $no_visita_cambia_estado, $cancelada)), 200); // send user details
		}
		$this->response('', 204);
	}

	private function listadoAlarmas() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		//$this->dbConnect();
		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$query = "SELECT * " .
			"FROM alarmas";
		//echo $query;

		$rst = $this->connseguimiento->query($query);

		if ($rst->num_rows > 0) {
			$resultado = array();
			while ($row = $rst->fetch_assoc()) {
				$row['ciudad'] = utf8_encode($row['ciudad']);
				$resultado[] = $row;
			}
			$this->response($this->json($resultado), 201);
		} else {
			$error = array();

			$this->response($this->json($error), 400);
		}
	}

	private function nuevaAlarma() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		//$this->dbConnect();
		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$login = $params['datosCrearAlarma'];
		$nombrealarma = $login['NOMBRE'];
		$ciudad = $login['CIUDAD'];
		$producto = $login['PRODUCTO'];
		$proceso = $login['PROCESO'];
		$accion = $login['ACCION'];
		$subaccion = $login['SUBACCION'];
		$mensaje = $login['MENSAJE'];
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

		$sql = " INSERT INTO alarmas ( " .
			" cantidad_campos, " .
			" ciudad, " .
			" nombre_alarma, " .
			" mensaje, " .
			" subaccion, accion, proceso, tecnologia_producto) " .
			" values ( " .
			" '$cantidad_campos', " .
			" '$ciudad', " .
			" '$nombrealarma', " .
			" '$mensaje', '$subaccion', '$accion', '$proceso', '$producto'  " .
			" '$password')";
		//echo $sql;
		$rst = $this->connseguimiento->query($sql);

		$this->response($this->json('Alarma creada'), 201);
		//crea alarma, activity feed
	}

	private function getgraficaSeguimientoClcik() {
		if ($this->get_request_method() != "GET") {
			$this->response('', 406);
		}

		$today = date("Y-m-d");

		$fecha = $this->_request['fecha'];
		$mes = date("m", strtotime($fecha));
		$año = date("Y", strtotime($fecha));

		$this->dbSeguimientoConnect();

		$query = "SELECT fecha, visita_atendido, sube_no_visita, no_sube, reagendado, " .
			"mal_agendado_no_sube, " .
			"visita_incompleto, no_visita_cambia_estado, cancelada " .
			"FROM seguimiento_Click_resumen " .
			"where fecha BETWEEN ('$año-$mes-01 00:00:00') AND ('$año-$mes-31 23:59:59') ";

		$r = $this->connseguimiento->query($query) or die($this->connseguimiento->error . __LINE__);

		if ($r->num_rows > 0) {
			$result = array();
			$categorias = array();
			$visita_atendido = array();
			$sube_no_visita = array();
			$no_sube = array();
			$reagendado = array();
			$mal_agendado_no_sube = array();
			$visita_incompleto = array();
			$no_visita_cambia_estado = array();
			$cancelada = array();
			$i = 1;
			while ($row = $r->fetch_assoc()) {

				$cate = $row['fecha'];
				$visita_aten = $row['visita_atendido'];
				$sube_no_vis = $row['sube_no_visita'];
				$no_su = $row['no_sube'];
				$reage = $row['reagendado'];
				$mal_agendado_no_su = $row['mal_agendado_no_sube'];
				$visita_incom = $row['visita_incompleto'];
				$no_visita_cambia_est = $row['no_visita_cambia_estado'];
				$cancel = $row['cancelada'];

				$categorias[] = array("label" => "$cate");
				$visita_atendido[] = array("value" => "$visita_aten");
				$sube_no_visita[] = array("value" => "$sube_no_vis");
				$no_sube[] = array("value" => "$no_su");
				$reagendado[] = array("value" => "$reage");
				$mal_agendado_no_sube[] = array("value" => "$mal_agendado_no_su");
				$visita_incompleto[] = array("value" => "$visita_incom");
				$no_visita_cambia_estado[] = array("value" => "$no_visita_cambia_est");
				$cancelada[] = array("value" => "$cancel");
				$i++;
			}
			//echo  $vista_atendido;
			$this->response($this->json(array($categorias, $visita_atendido, $sube_no_visita, $no_sube, $reagendado, $mal_agendado_no_sube, $visita_incompleto, $no_visita_cambia_estado, $cancelada)), 200); // send user details
		}
		$this->response('', 204);
		// If no records "No Content" status
	}

	private function resumencontingencias() {

		if ($this->get_request_method() != "GET") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$fechaIni = $this->_request['fechaini'];
		$fechaFin = $this->_request['fechafin'];

		$month = date('m', strtotime($fechaIni));
		$year = date('Y', strtotime($fechaIni));
		$day = date("d", mktime(0, 0, 0, $month + 1, 0, $year));

		$diaFinal = date('Y-m-d', mktime(0, 0, 0, $month, $day, $year));
		$diaInicial = date('Y-m-d', mktime(0, 0, 0, $month, 1, $year));

		/* $query = "select logindepacho, pedido, horagestion, logincontingencia, horacontingencia, " .
			"(case when acepta is null then 'Pendiente'  " .
			"else acepta end) estado " .
			"from contingencias  " .
			"where horagestion between ('$fechaIni 00:00:00') and ('$fechaFin 23:59:59') " .
			"AND accion IN ('Cambio de equipo', 'Contingencia', 'Refresh', 'Registros ToIP', 'Reenvio de registros') " .
			"and pedido <> '' " .
			"order by horagestion DESC "; */

		$query = "SELECT logindepacho, pedido, horagestion, logincontingencia, horacontingencia,
		(CASE 
			WHEN acepta IS NULL THEN 'Pendiente' 
			ELSE acepta 
		END) estado
		FROM contingencias
		WHERE horagestion BETWEEN ('$fechaIni 00:00:00') AND ('$fechaFin 23:59:59')
		AND accion IN ('Cambio de equipo', 'Contingencia', 'Refresh', 'Registros ToIP', 'Reenvio de registros')
		AND pedido <> ''
		ORDER BY horagestion DESC;";

		$rst = $this->connseguimiento->query($query);

		if ($rst->num_rows > 0) {

			$resultado = array();

			while ($row = $rst->fetch_assoc()) {

				$resultado[] = $row;

			}
		}

		$queryTV = ("SELECT logindepacho, pedido, horagestion, logincontingencia, horacontingencia,
				(CASE
					WHEN acepta IS NULL THEN 'Pendiente'
				ELSE acepta END) estado
			FROM contingencias
			WHERE horagestion BETWEEN ('$fechaIni 00:00:00') AND ('$fechaFin 23:59:59')
			AND producto = 'TV'
			AND accion IN ('Cambio de equipo', 'Contingencia', 'Refresh', 'Registros ToIP', 'Reenvio de registros')
			AND pedido <> ''
			ORDER BY horagestion DESC;
		");

		$rstTV = $this->connseguimiento->query($queryTV);

		if ($rstTV->num_rows > 0) {

			$resultadoTV = array();

			while ($row = $rstTV->fetch_assoc()) {

				$resultadoTV[] = $row;

			}
		}

		$queryInTo = ("SELECT logindepacho, pedido, horagestion, logincontingencia, horacontingencia,
							(CASE
								WHEN acepta IS NULL THEN 'Pendiente'
							ELSE acepta END) estado
						FROM contingencias
						WHERE horagestion BETWEEN ('$fechaIni 00:00:00') AND ('$fechaFin 23:59:59')
						AND producto IN('ToIP', 'Internet+ToIP', 'Internet')
						AND accion IN ('Cambio de equipo', 'Contingencia', 'Refresh', 'Registros ToIP', 'Reenvio de registros')
						AND pedido <> ''
						ORDER BY horagestion DESC
					");

		$rstInTo = $this->connseguimiento->query($queryInTo);

		if ($rstInTo->num_rows > 0) {

			$resultadoInTo = array();

			while ($row = $rstInTo->fetch_assoc()) {

				$resultadoInTo[] = $row;

			}
		}

		$queryCP = ("SELECT logindepacho, pedido, horagestion, loginContingenciaPortafolio, horaContingenciaPortafolio,
							(CASE
								WHEN aceptaPortafolio = 'Acepta' THEN 'Acepta'
								WHEN aceptaPortafolio = 'Rechaza' THEN 'Rechaza'
								WHEN aceptaPortafolio IS NULL THEN 'Pendiente'
							ELSE aceptaPortafolio = 'Acepta' END) estado
						FROM contingencias
						WHERE horagestion BETWEEN ('$fechaIni 00:00:00') AND ('$fechaFin 23:59:59')
						AND accion IN ('Corregir portafolio', 'mesaOffline')
						AND pedido <> ''
						ORDER BY horagestion DESC
					");

		$rstCP = $this->connseguimiento->query($queryCP);

		if ($rstCP->num_rows > 0) {

			$resultadoCP = array();

			while ($row = $rstCP->fetch_assoc()) {

				$resultadoCP[] = $row;

			}
		}

		/* $querydiario = "select date_format(horagestion,'%Y-%m-%d') fecha, count(*) total " .
			"from contingencias " .
			"where horagestion between ('$diaInicial 00:00:00')   " .
			"and ('$diaFinal 23:59:59') " .
			"AND accion IN ('Cambio de equipo', 'Contingencia', 'Refresh', 'Registros ToIP', 'Reenvio de registros') " .
			"and pedido <> '' " .
			"group by fecha order by fecha DESC "; */

		$querydiario = "SELECT DATE_FORMAT(horagestion,'%Y-%m-%d') fecha, COUNT(*) total
		FROM contingencias
		WHERE horagestion BETWEEN ('$diaInicial 00:00:00') AND ('$diaFinal 23:59:59')
		AND accion IN ('Cambio de equipo', 'Contingencia', 'Refresh', 'Registros ToIP', 'Reenvio de registros')
		AND pedido <> ''
		GROUP BY fecha
		ORDER BY fecha DESC;";

		$rstdiario = $this->connseguimiento->query($querydiario);

		$resultadodiario = array();

		while ($row = $rstdiario->fetch_assoc()) {

			$resultadodiario[] = $row;
		}

		/* $querydiarioCP = "select date_format(horagestion,'%Y-%m-%d') fecha, count(*) total " .
			"from contingencias " .
			"where horagestion between ('$diaInicial 00:00:00')   " .
			"and ('$diaFinal 23:59:59') " .
			"AND accion IN ('Corregir portafolio', 'mesaOffline') " .
			"and pedido <> '' " .
			"group by fecha order by fecha DESC "; */

		$querydiarioCP = "SELECT date_format(horagestion,'%Y-%m-%d') fecha, COUNT(*) total
		FROM contingencias
		WHERE horagestion BETWEEN ('$diaInicial 00:00:00') AND ('$diaFinal 23:59:59')
		AND accion IN ('Corregir portafolio', 'mesaOffline')
		AND pedido <> ''
		GROUP BY fecha
		ORDER BY fecha DESC;";

		$rstdiarioCP = $this->connseguimiento->query($querydiarioCP);

		$resultadodiarioCP = array();

		while ($row = $rstdiarioCP->fetch_assoc()) {

			$resultadodiarioCP[] = $row;
		}

		/*QUERY PARA EL CONTADOR DE TV Y INTERET*/
		$queryestadosMes = ("SELECT (CASE 
		WHEN acepta IS NULL THEN 'Pendiente' 
		ELSE acepta 
		END) estado, 
		COUNT(*) total,
		(SELECT COUNT(*)
			FROM contingencias C2
			WHERE horagestion BETWEEN ('$diaInicial 00:00:00') AND ('$diaFinal 23:59:59')
			AND C2.accion IN ('Cambio de equipo', 'Contingencia', 'Refresh', 'Registros ToIP', 'Reenvio de registros')
		) totalestados
		FROM contingencias AS C1
		WHERE horagestion BETWEEN ('$diaInicial 00:00:00') AND ('$diaFinal 23:59:59')
		AND pedido <> ''
		AND C1.accion IN ('Cambio de equipo', 'Contingencia', 'Refresh', 'Registros ToIP', 'Reenvio de registros')
		GROUP BY estado
		ORDER BY total DESC;");

		$rstestadosMes = $this->connseguimiento->query($queryestadosMes);

		if ($rstestadosMes->num_rows > 0) {

			$resultadoestadosMes = array();

			while ($row = $rstestadosMes->fetch_assoc()) {

				$resultadoestadosMes[] = $row;

			}
		}

		/*QUERY PARA EL CONTADOR DE CORREGOR PORTAFOLIO*/
		$queryestadosMesCP = ("SELECT (CASE
										WHEN C1.aceptaPortafolio = 'Acepta' THEN 'Acepta'
										WHEN C1.aceptaPortafolio = 'Rechaza' THEN 'Rechaza'
										WHEN C1.aceptaPortafolio IS NULL THEN 'Pendiente'
										ELSE C1.aceptaPortafolio = 'Acepta'
									END) estado, COUNT(*) total,
									(SELECT COUNT(*)
										FROM contingencias C2
										WHERE C2.horagestion BETWEEN ('$diaInicial 00:00:00') AND ('$diaFinal 23:59:59')
										AND C2.accion IN ('Corregir portafolio', 'mesaOffline')
										OR C2.horaContingenciaPortafolio BETWEEN ('$diaInicial 00:00:00') AND ('$diaFinal 23:59:59')
										AND C2.accion IN ('Corregir portafolio', 'mesaOffline')
									)totalestados
								FROM contingencias AS C1
								WHERE C1.horagestion BETWEEN ('$diaInicial 00:00:00') AND ('$diaFinal 23:59:59')
								AND C1.accion IN ('Corregir portafolio', 'mesaOffline')
								OR C1.horaContingenciaPortafolio BETWEEN ('$diaInicial 00:00:00') AND ('$diaFinal 23:59:59')
								AND C1.accion IN ('Corregir portafolio', 'mesaOffline')
								AND pedido <> ''
								GROUP BY estado
								ORDER BY total DESC
							");

		//$rstestadosMesCP = $this->connseguimiento->query($queryestadosMesCP);

		//if ($rstestadosMesCP->num_rows > 0) {
		if (1 > 0) {

			$resultadoestadosMesCP = array(
				array(
					"estado" => "Acepta",
					"total" => "0",
					"totalestados" => "0"
				),
				array(
					"estado" => "Rechaza",
					"total" => "0",
					"totalestados" => "0"
				),
				array(
					"estado" => "Pendiente",
					"total" => "0",
					"totalestados" => "0"
				)
			);



			/* while ($row = $rstestadosMesCP->fetch_assoc()) {

				$resultadoestadosMesCP[] = $row;

			} */
			$this->response($this->json(array($resultado, $resultadoestadosMes, $resultadodiario, $resultadoestadosMesCP, $resultadodiarioCP, $resultadoCP, $resultadoTV, $resultadoInTo)), 201);
		} else {
			$this->response($this->json($error), 400);
		}
	}

	private function listadoTecnicos() {

		if ($this->get_request_method() != "GET") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$pagina = $this->_request['page'];
		$concepto = $this->_request['concepto'];
		$tecnico = $this->_request['tecnico'];
		//echo "selección".$buscar;
		//echo "dato".$usuario;

		//$today = date("Y-m-d");

		if ($pagina == "undefined") {
			$pagina = "0";
		} else {
			$pagina = $pagina - 1;
		}

		$pagina = $pagina * 100;

		if ($concepto == 'nombre') {
			$parametro = "and nombre LIKE '%$tecnico%'";
		} else if ($concepto == 'identificacion') {
			$parametro = " and identificacion = '$tecnico'";
		} else if ($concepto == 'ciudad') {
			$parametro = " and ciudad = '$tecnico'";
		} else if ($concepto == 'celuar') {
			$parametro = " and celular = '$tecnico'";
		};

		$query = "select a.ID, a.IDENTIFICACION, a.NOMBRE, a.CIUDAD, a.CELULAR,  a.empresa, " .
			" (select b.nombre from empresas b where b.id=a.empresa) as NOM_EMPRESA " .
			" from tecnicos a " .
			" where 1=1 $parametro order by a.nombre ASC " .
			" limit 100 offset $pagina ";

		$queryCount = " select count(*) as Cantidad from tecnicos h " .
			" where 1=1 " .
			" $parametro ";
		//echo $query;
		$rr = $this->connseguimiento->query($queryCount);
		$counter = 0;
		if ($rr->num_rows > 0) {
			$result = array();
			if ($row = $rr->fetch_assoc()) {
				$counter = $row['Cantidad'];
			}
		}
		//echo $this->mysqli->query($sqlLogin);
		//
		$rst = $this->connseguimiento->query($query);
		if ($rst->num_rows > 0) {

			$resultado = array();

			while ($row = $rst->fetch_assoc()) {

				$row['NOMBRE'] = $row['NOMBRE'];
				$resultado[] = $row;

			}
			$this->response($this->json(array($resultado, $counter)), 201);

		} else {
			$error = array();

			$this->response($this->json($error), 400);
		}
	}

	private function conceptospendientes() {

		if ($this->get_request_method() != "GET") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$interfaz = $this->_request['interfaz'];

		$query = "select regional, count(CONCEPTO_ATC) total_concepto " .
			"from pendi_insta " .
			"where INTERFAZ = '$interfaz' " .
			"and regional is not null " .
			"group by regional order by count(CONCEPTO_ATC) DESC ";

		$rst = $this->connseguimiento->query($query);
		if ($rst->num_rows > 0) {

			$resultadoconceptos = array();
			$resultado = array();

			while ($row = $rst->fetch_assoc()) {

				$resultado[] = $row;
			}
			$this->response($this->json(array($resultado)), 201);

		} else {
			$error = array();

			$this->response($this->json($error), 400);
		}
	}

	private function getConceptosTotales() {

		if ($this->get_request_method() != "GET") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$regional = $this->_request['regional'];
		$interfaz = $this->_request['interfaz'];

		$query = "select CONCEPTO_ATC, count(CONCEPTO_ATC) total_concepto " .
			"from pendi_insta " .
			"where REGIONAL = '$regional' " .
			"and INTERFAZ = '$interfaz' " .
			"group by CONCEPTO_ATC order by count(CONCEPTO_ATC) DESC ";

		$rst = $this->connseguimiento->query($query);
		if ($rst->num_rows > 0) {

			$resultado = array();

			while ($row = $rst->fetch_assoc()) {

				$CONCEPTO_ATC = $this->quitar_tildes(utf8_encode($row['CONCEPTO_ATC']));
				$row['CONCEPTO_ATC'] = $CONCEPTO_ATC;
				$resultado[] = $row;
			}

			$this->response($this->json(array($resultado)), 201);

		} else {
			$error = array();

			$this->response($this->json($error), 400);
		}
	}

	private function ResumenInsta() {

		if ($this->get_request_method() != "GET") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$departamento = $this->_request['departamento'];

		if ($departamento == undefined) {
			$condition = "";
		} else {
			$condition = "where departamento = '$departamento'";
		}

		$today = date("Y-m-d");
		$fechaanterior = date('Y-m-d', strtotime('-1 day', strtotime($today)));
		$mes = date("m", strtotime($today));
		$año = date("Y", strtotime($today));

		$query = "SELECT DEPARTAMENTO_OPERACION, count(pedido_id) total_pedidos, sum(productos) total_productos " .
			"FROM pendi_insta " .
			"where fecha = '$today' " .
			"group by DEPARTAMENTO_OPERACION order by total_pedidos DESC";

		/*$RESPONSABLE_GESTION="select pendi.RESPONSABLE_GESTION, count(pendi.RESPONSABLE_GESTION) totaldireccion,  ".
									"(select count(RANGO_INGRESO_DIAS) from pendi_insta where RANGO_INGRESO_DIAS in ('Entre 4-5', ".
									"'Entre 3-4', 'Entre 2-3', 'Entre 1-2', 'Entre 0-1') ".
									"and fecha = '$today' $condition".
									"and RESPONSABLE_GESTION = pendi.RESPONSABLE_GESTION $condition limit 1) Entre_0_5, ".
									"(select count(RANGO_INGRESO_DIAS) from pendi_insta where RANGO_INGRESO_DIAS = 'Entre 5-10' ".
									"and fecha = '$today' $condition".
									" and RESPONSABLE_GESTION = pendi.RESPONSABLE_GESTION $condition limit 1) Entre_5_10, ".
									"(select count(RANGO_INGRESO_DIAS) from pendi_insta where RANGO_INGRESO_DIAS = 'Entre 10-15' ".
									"and fecha = '$today' $condition".
									" and RESPONSABLE_GESTION = pendi.RESPONSABLE_GESTION $condition limit 1) Entre_10_15, ".
									" (select count(RANGO_INGRESO_DIAS) from pendi_insta where RANGO_INGRESO_DIAS = 'Entre 15-30' ".
									"and fecha = '$today' $condition".
									" and RESPONSABLE_GESTION = pendi.RESPONSABLE_GESTION $condition limit 1) Entre_15_30,  ".
									"  (select count(RANGO_INGRESO_DIAS) from pendi_insta where RANGO_INGRESO_DIAS = 'Mayor de 30' ".
									"and fecha = '$today' $condition".
									" and RESPONSABLE_GESTION = pendi.RESPONSABLE_GESTION $condition limit 1) Mayor_30 ".
									"from pendi_insta pendi ".
									"where fecha = '$today' $condition ".
									"group by pendi.RESPONSABLE_GESTION ".
									"order by count(pendi.RESPONSABLE_GESTION) DESC";*/

		$RESPONSABLE_GESTION = "SELECT RESPONSABLE_GESTION, sum(total) totaldireccion, sum(Entre_0_5) Entre_0_5, sum(Entre_5_10) Entre_5_10, " .
			"sum(Entre_10_15) Entre_10_15, sum(Entre_15_30) Entre_15_30, sum(Mayor_30) Mayor_30 " .
			"FROM resumenpendientesInstaresponsable " .
			"$condition  " .
			"group by RESPONSABLE_GESTION order by sum(total) DESC ";

		/*$TIPO_SOLICITUD_ORIG="select pendi.TIPO_SOLICITUD_ORIG, count(pendi.TIPO_SOLICITUD_ORIG) totalsolicitud,  ".
									"(select count(RANGO_INGRESO_DIAS) from pendi_insta where RANGO_INGRESO_DIAS in ('Entre 4-5', ".
									"'Entre 3-4', 'Entre 2-3', 'Entre 1-2', 'Entre 0-1') ".
									"and fecha = '$today'  ".
									"and TIPO_SOLICITUD_ORIG = pendi.TIPO_SOLICITUD_ORIG $condition limit 1) Entre_0_5, ".
									"(select count(RANGO_INGRESO_DIAS) from pendi_insta where RANGO_INGRESO_DIAS = 'Entre 5-10' ".
									"and fecha = '$today'  ".
									" and TIPO_SOLICITUD_ORIG = pendi.TIPO_SOLICITUD_ORIG $condition limit 1) Entre_5_10, ".
									"(select count(RANGO_INGRESO_DIAS) from pendi_insta where RANGO_INGRESO_DIAS = 'Entre 10-15' ".
									"and fecha = '$today'  ".
									" and TIPO_SOLICITUD_ORIG = pendi.TIPO_SOLICITUD_ORIG $condition limit 1) Entre_10_15, ".
									" (select count(RANGO_INGRESO_DIAS) from pendi_insta where RANGO_INGRESO_DIAS = 'Entre 15-30' ".
									"and fecha = '$today'  ".
									" and TIPO_SOLICITUD_ORIG = pendi.TIPO_SOLICITUD_ORIG $condition limit 1) Entre_15_30,  ".
									"  (select count(RANGO_INGRESO_DIAS) from pendi_insta where RANGO_INGRESO_DIAS = 'Mayor de 30' ".
									"and fecha = '$today'  ".
									" and TIPO_SOLICITUD_ORIG = pendi.TIPO_SOLICITUD_ORIG $condition limit 1) Mayor_30 ".
									"from pendi_insta pendi ".
									"where fecha = '$today' $condition ".
									"group by pendi.TIPO_SOLICITUD_ORIG ".
									"order by count(pendi.TIPO_SOLICITUD_ORIG) DESC";*/

		$TIPO_SOLICITUD_ORIG = "SELECT solicitud, sum(total) totalsolicitud, sum(Entre_0_5) Entre_0_5, sum(Entre_5_10) Entre_5_10, " .
			"sum(Entre_10_15) Entre_10_15, sum(Entre_15_30) Entre_15_30, sum(Mayor_30) Mayor_30 " .
			"FROM resumenpendientesInstasolicitudRango " .
			"$condition  " .
			"group by solicitud order by sum(total) DESC ";

		/*$NOVEDADES=	"select pendi.TIPO_SOLICITUD_ORIG, count(pendi.TIPO_SOLICITUD_ORIG) totalnovedades, ".
										"(select count(ESTADO_AGENDA) from pendi_insta where ESTADO_AGENDA = 'Sin_Agenda' ".
										"and fecha = '$today' $condition ".
										"and TIPO_SOLICITUD_ORIG = pendi.TIPO_SOLICITUD_ORIG  limit 1) Sin_Agenda,  ".
										"(select count(ESTADO_AGENDA) from pendi_insta where ESTADO_AGENDA = 'Vencida' ".
										"and fecha = '$today'  $condition ".
										"and TIPO_SOLICITUD_ORIG = pendi.TIPO_SOLICITUD_ORIG  limit 1) Vencida,  ".
										"(select count(ESTADO_AGENDA) from pendi_insta where ESTADO_AGENDA = 'Futura'  ".
										"and fecha = '$today'  $condition ".
										" and TIPO_SOLICITUD_ORIG = pendi.TIPO_SOLICITUD_ORIG  limit 1) Futura,  ".
										"(select count(ESTADO_AGENDA) from pendi_insta where ESTADO_AGENDA = 'Para_HOY'  ".
										"and fecha = '$today'  $condition ".
										" and TIPO_SOLICITUD_ORIG = pendi.TIPO_SOLICITUD_ORIG  limit 1) Para_HOY,   ".
										"(select count(ESTADO_AGENDA) from pendi_insta where ESTADO_AGENDA = 'Para_Mañana'  ".
										"and fecha = '$today' $condition ".
										"and TIPO_SOLICITUD_ORIG = pendi.TIPO_SOLICITUD_ORIG  limit 1) Para_Manana ".
										"from pendi_insta pendi  ".
										"where fecha = '$today' $condition ".
										"group by pendi.TIPO_SOLICITUD_ORIG ".
										"order by count(pendi.TIPO_SOLICITUD_ORIG) DESC ";*/

		$NOVEDADES = "SELECT solicitud, sum(total) total, sum(sinAgenda) Sin_Agenda, sum(vencida) Vencida, " .
			"sum(futura) Futura, sum(paraHoy) Para_HOY, sum(manana) Para_Manana " .
			"FROM resumenpendientesInstaSolicitudAgenda " .
			"$condition " .
			"group by solicitud order by sum(total) DESC ";

		$historico_rangos = "SELECT * FROM HistoricoPendientesInsta " .
			"where fecha BETWEEN " .
			"('$año-$mes-01') AND ('$año-$mes-31') order by fecha ASC ";

		$historico_porcentajes = "SELECT ROUND((Entre_0_5/total)*100, 2) porecentaje0_5, " .
			"ROUND((Entre_5_10/total)*100, 2) porecentaje5_10,  " .
			"ROUND((Entre_10_15/total)*100, 2) porecentaje10_15,  " .
			"ROUND((Entre_15_30/total)*100, 2) porecentaje15_30, " .
			"ROUND((Mayor_30/total)*100, 2) porecentajemayor_30, ROUND((total/total)*100) total " .
			"FROM HistoricoPendientesInsta  " .
			"where fecha = '$today' ";

		$diferencia_totales = "SELECT distinct " .
			"(select Entre_0_5  " .
			"from HistoricoPendientesInsta " .
			"where fecha = '$fechaanterior')- " .
			"(select Entre_0_5  " .
			"from HistoricoPendientesInsta " .
			"where fecha = '$today') Entre_0_5, " .
			"(select Entre_5_10 " .
			"from HistoricoPendientesInsta " .
			"where fecha = '$fechaanterior')- " .
			"(select Entre_5_10  " .
			"from HistoricoPendientesInsta " .
			"where fecha = '$today') Entre_5_10,  " .
			"(select Entre_10_15 " .
			"from HistoricoPendientesInsta " .
			"where fecha = '$fechaanterior')- " .
			"(select Entre_10_15 " .
			"from HistoricoPendientesInsta  " .
			"where fecha = '$today') Entre_10_15,  " .
			"(select Entre_15_30 " .
			"from HistoricoPendientesInsta " .
			"where fecha = '$fechaanterior')- " .
			"(select Entre_15_30  " .
			"from HistoricoPendientesInsta " .
			"where fecha = '$today') Entre_15_30, " .
			"(select mayor_30 " .
			"from HistoricoPendientesInsta " .
			"where fecha = '$fechaanterior')- " .
			"(select mayor_30  " .
			"from HistoricoPendientesInsta " .
			"where fecha = '$today') mayor_30,    " .
			"(select total " .
			"from HistoricoPendientesInsta " .
			"where fecha = '$fechaanterior')- " .
			"(select total  " .
			"from HistoricoPendientesInsta " .
			"where fecha = '$today') total   " .
			"FROM HistoricoPendientesInsta ";

		$rstdiferencia_totales = $this->connseguimiento->query($diferencia_totales);
		while ($row = $rstdiferencia_totales->fetch_assoc()) {
			$resultadorstdiferencia_totales[] = $row;
		}

		$rst = $this->connseguimiento->query($query);

		$resultado = array();
		$Totalregionales = array();
		$total_pedidos = 0;
		$total_productos = 0;
		while ($row = $rst->fetch_assoc()) {
			$resultado[] = $row;
			$total_pedidos = $row['total_pedidos'] + $total_pedidos;
			$total_productos = $row['total_productos'] + $total_productos;
		}

		$rstdireccion = $this->connseguimiento->query($RESPONSABLE_GESTION);
		while ($row = $rstdireccion->fetch_assoc()) {
			$resultadodireccion[] = $row;
		}

		$rstsolicitud = $this->connseguimiento->query($TIPO_SOLICITUD_ORIG);
		while ($row = $rstsolicitud->fetch_assoc()) {
			$solicitud = $this->quitar_tildes(utf8_encode($row['solicitud']));
			$row['solicitud'] = $solicitud;
			$resultadosolicitud[] = $row;
		}

		$rsthistoricos = $this->connseguimiento->query($historico_rangos);
		while ($row = $rsthistoricos->fetch_assoc()) {
			$resultadohistoricos[] = $row;
		}

		$rstporcentajes = $this->connseguimiento->query($historico_porcentajes);
		while ($row = $rstporcentajes->fetch_assoc()) {
			$resultadoporcentajes[] = $row;
		}

		$rstnovedades = $this->connseguimiento->query($NOVEDADES);
		while ($row = $rstnovedades->fetch_assoc()) {
			$solicitud = $this->quitar_tildes(utf8_encode($row['solicitud']));
			$row['solicitud'] = $solicitud;
			$resultadonovedades[] = $row;
		}

		$this->response($this->json(array($resultado, $total_pedidos, $total_productos, $resultadodireccion, $resultadosolicitud, $resultadohistoricos, $resultadoporcentajes, $resultadonovedades, $resultadorstdiferencia_totales)), 201);
	}

	private function borrarUsuario() {
		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);
		$id = $params['id'];

		$sql = "delete from usuarios where id=$id ";

		$rst = $this->connseguimiento->query($sql);

		//echo $rst;

		//Insert en log

		//$this->response($this->json($error), 200);
		//elimino usuario, activity feed
	}

	private function borrarAlarma() {
		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);
		$id = $params['id'];
		//echo 	$id;
		$sql = "delete from alarmas where id=$id ";

		$rst = $this->connseguimiento->query($sql);

		//echo $rst;

		//Insert en log

		$this->response($this->json($error), 200);
		//elimino alarma, activity feed
	}

	private function borrarTecnico() {
		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);
		$id = $params['id'];
		$sql = "delete from tecnicos where id=$id ";

		$rst = $this->connseguimiento->query($sql);

		//Insert en log

		//$this->response($this->json($error), 200);
		//elimino tecnico, activity feed
	}

	private function deleteregistrosCarga() {
		if ($this->get_request_method() != "GET") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$id = $this->_request['idCarga'];

		$sql = "delete from carga_archivos where id='$id' ";

		$rst = $this->connseguimiento->query($sql);

		//echo $rst;

		//Insert en log
		$this->response($this->json($error), 200);
		//elimino archivo cargado, activity feed
	}

	private function ciudades() {
		if ($this->get_request_method() != "GET") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$query = " SELECT DISTINCT DEPARTAMENTO, CIUDAD " .
			" FROM ciudades " .
			" ORDER BY CIUDAD ASC ";

		$rst = $this->connseguimiento->query($query);

		$querydep = " SELECT DISTINCT DEPARTAMENTO " .
			" FROM ciudades " .
			" ORDER BY DEPARTAMENTO ASC ";

		$rstdep = $this->connseguimiento->query($querydep);

		if ($rst->num_rows > 0) {

			$resultado = array();
			$resultadodepa = array();
			while ($row = $rst->fetch_assoc()) {
				$ciudades = $this->quitar_tildes(utf8_encode($row['CIUDAD']));
				$row['CIUDAD'] = $ciudades;
				$departamentos = $this->quitar_tildes(utf8_encode($row['DEPARTAMENTO']));
				$resultado[] = $row;
			}

			while ($row = $rstdep->fetch_assoc()) {
				$departamentos = $this->quitar_tildes(utf8_encode($row['DEPARTAMENTO']));
				$resultadodepa[] = $row;
			}

			$this->response($this->json(array($resultado, $resultadodepa)), 201);

		} else {
			$error = array();

			$this->response($this->json($error), 400);
		} // If no records "No Content" status
	}

	private function regionesTip() {
		if ($this->get_request_method() != "GET") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$query = " SELECT DISTINCT region" .
			" FROM regiones " .
			" ORDER BY region ASC ";

		$rst = $this->connseguimiento->query($query);

		if ($rst->num_rows > 0) {

			$resultado = array();

			while ($row = $rst->fetch_assoc()) {
				$regiones = $this->quitar_tildes(utf8_encode($row['region']));
				$row['region'] = $regiones;
				$resultado[] = $row;
			}
			$this->response($this->json(array($resultado)), 201);

		} else {
			$error = array();

			$this->response($this->json($error), 400);
		}
	}

	private function procesos() {
		if ($this->get_request_method() != "GET") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$query = " SELECT DISTINCT trim(PROCESO) proceso" .
			" FROM procesos " .
			" ORDER BY PROCESO ASC ";

		$rst = $this->connseguimiento->query($query);
		if ($rst->num_rows > 0) {

			$resultado = array();
			while ($row = $rst->fetch_assoc()) {
				$resultado[] = $row;
			}

			$this->response($this->json(array($resultado)), 201);

		} else {
			$error = array();

			$this->response($this->json($error), 400);
		} // If no records "No Content" status
	}

	private function departamentos() {
		if ($this->get_request_method() != "GET") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$query = "select distinct DEPARTAMENTO_OPERACION " .
			"from pendi_insta " .
			"where DEPARTAMENTO_OPERACION is not null " .
			"order by DEPARTAMENTO_OPERACION ASC ";

		$rst = $this->connseguimiento->query($query);
		if ($rst->num_rows > 0) {

			$resultado = array();
			while ($row = $rst->fetch_assoc()) {
				$resultado[] = $row;
			}

			$this->response($this->json(array($resultado)), 201);

		} else {
			$error = array();

			$this->response($this->json($error), 400);
		} // If no records "No Content" status
	}

	private function tipo_trabajoclick() {
		if ($this->get_request_method() != "GET") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$query = " SELECT DISTINCT tipo_trabajo" .
			" FROM carga_click " .
			" ORDER BY tipo_trabajo ASC ";

		$rst = $this->connseguimiento->query($query);
		if ($rst->num_rows > 0) {

			$resultado = array();
			while ($row = $rst->fetch_assoc()) {
				$resultado[] = $row;
			}

			$this->response($this->json(array($resultado)), 201);

		} else {
			$error = array();

			$this->response($this->json($error), 400);
		} // If no records "No Content" status
	}

	private function UenCargada() {
		if ($this->get_request_method() != "GET") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$query = " SELECT DISTINCT uen" .
			" FROM carga_agenda " .
			" ORDER BY uen ASC ";

		$rst = $this->connseguimiento->query($query);
		if ($rst->num_rows > 0) {

			$resultado = array();
			while ($row = $rst->fetch_assoc()) {
				$resultado[] = $row;
			}

			$this->response($this->json(array($resultado)), 201);

		} else {
			$error = array();

			$this->response($this->json($error), 400);
		} // If no records "No Content" status
	}

	private function acciones() {
		if ($this->get_request_method() != "GET") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$proceso = $this->_request['proceso'];

		$query = " SELECT DISTINCT ACCION" .
			" FROM procesos " .
			" where 1=1 and proceso='$proceso' and accion <> ''" .
			" ORDER BY ACCION ASC ";

		//	echo $query;

		$rst = $this->connseguimiento->query($query);
		if ($rst->num_rows > 0) {

			$resultado = array();
			while ($row = $rst->fetch_assoc()) {
				$resultado[] = $row;
			}

			$this->response($this->json(array($resultado)), 201);

		} else {
			$error = array();

			$this->response($this->json($error), 400);
		} // If no records "No Content" status
	}

	private function SubAcciones() {
		if ($this->get_request_method() != "GET") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$proceso = $this->_request['proceso'];
		$accion = $this->_request['accion'];

    $query = "select DISTINCT SUBACCION FROM procesos WHERE accion = '$accion' AND proceso = '$proceso' and subaccion <> ''";

		$rst = $this->connseguimiento->query($query);
		//echo $query;
		if ($rst->num_rows > 0) {

			$resultado = array();
			while ($row = $rst->fetch_assoc()) {
				$row['SUBACCION'] = utf8_encode($row['SUBACCION']);
				$resultado[] = $row;
			}

		/*$query = " SELECT DISTINCT SUBACCION" .
			" FROM procesos " .
			" where 1=1 and proceso='$proceso' and accion='$accion' and subaccion <> ''" .
			" ORDER BY SUBACCION ASC ";

		$rst = $this->connseguimiento->query($query);
		//echo $query;
		if ($rst->num_rows > 0) {

			$resultado = array();
			while ($row = $rst->fetch_assoc()) {
				$resultado[] = $row;
			}*/

			$this->response($this->json(array($resultado)), 201);

		} else {
			$error = array(
				'status' => 400,
				'msg' => 'Sin datos para listar',
			);
			$this->response($this->json($error), 400);
		} // If no records "No Content" status
	}

	private function Codigos() {

		if ($this->get_request_method() != "GET") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$proceso = $this->_request['proceso'];
		$UNESourceSystem = $this->_request['UNESourceSystem'];
		//echo json_encode($region);

		$query = " 	SELECT DISTINCT codigo
					FROM codigosPendiente
					WHERE proceso ='$proceso' AND UNESourceSystem ='$UNESourceSystem'
					ORDER BY codigo ASC  ";

		$rst = $this->connseguimiento->query($query);

		if ($rst->num_rows > 0) {

			$resultado = array();
			while ($row = $rst->fetch_assoc()) {

				$row['codigo'] = utf8_encode($row['codigo']);

				$resultado[] = $row;
			}

			$this->response($this->json(array($resultado)), 201);

		} else {
			$this->response($this->json($error), 200);
		}
	}

	private function Diagnosticos() {

		if ($this->get_request_method() != "GET") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$producto = $this->_request['producto'];

		$query = " 	SELECT DISTINCT diagnostico
					FROM diagnosticoFalla
					WHERE producto ='$producto'
					ORDER BY diagnostico ASC  ";

		$rst = $this->connseguimiento->query($query);

		if ($rst->num_rows > 0) {

			$resultado = array();
			while ($row = $rst->fetch_assoc()) {

				$row['diagnostico'] = utf8_encode($row['diagnostico']);

				$resultado[] = $row;
			}

			$this->response($this->json(array($resultado)), 201);

		} else {
			$this->response($this->json($error), 200);
		}
	}


	private function gestionComercial() {
		if ($this->get_request_method() != "GET") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$query = " SELECT DISTINCT GESTION" .
			" FROM procesos_comercial " .
			" ORDER BY GESTION ASC ";

		$rst = $this->connseguimiento->query($query);
		if ($rst->num_rows > 0) {

			$resultado = array();
			while ($row = $rst->fetch_assoc()) {
				$resultado[] = $row;
			}

			$this->response($this->json(array($resultado)), 201);

		} else {
			$error = array();

			$this->response($this->json($error), 400);
		} // If no records "No Content" status
	}

	private function clasificacionComercial() {

		if ($this->get_request_method() != "GET") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$gestion = $this->_request['gestion'];

		$query = " SELECT DISTINCT CLASIFICACION" .
			" FROM procesos_comercial " .
			" WHERE gestion = '$gestion' " .
			" ORDER BY GESTION ASC ";

		$rst = $this->connseguimiento->query($query);
		if ($rst->num_rows > 0) {

			$resultado = array();
			while ($row = $rst->fetch_assoc()) {
				$resultado[] = $row;
			}

			$this->response($this->json(array($resultado)), 201);

		} else {
			$error = array();

			$this->response($this->json($error), 400);
		} // If no records "No Content" status
	}

	private function meses() {

		if ($this->get_request_method() != "GET") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$query = " select distinct mes from nps";

		$rst = $this->connseguimiento->query($query);
		if ($rst->num_rows > 0) {

			$resultado = array();
			while ($row = $rst->fetch_assoc()) {
				$resultado[] = $row;
			}

			$this->response($this->json(array($resultado)), 201);

		} else {
			$error = array();

			$this->response($this->json($error), 400);
		} // If no records "No Content" status
	}

	private function mesesrepa() {

		if ($this->get_request_method() != "GET") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$query = " select distinct mes from npsreparaciones";

		$rst = $this->connseguimiento->query($query);
		if ($rst->num_rows > 0) {

			$resultado = array();
			while ($row = $rst->fetch_assoc()) {
				$resultado[] = $row;
			}

			$this->response($this->json(array($resultado)), 201);

		} else {
			$error = array();

			$this->response($this->json($error), 400);
		} // If no records "No Content" status
	}

	private function buscaregistros() {

		if ($this->get_request_method() != "GET") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$pedido = $this->_request['pedido'];
		$fecha = $this->_request['fecha'];

		$query = "select pedido,(select nombre from tecnicos where identificacion=id_tecnico limit 1) " .
		"tecnico,accion,asesor,tipo_pendiente, " .
		"(select tipo_trabajo from carga_click where pedido=pedido_id limit 1) tipo_trabajo, " .
		"fecha,observaciones,id  " .
		"from registros " .
		//	"where fecha between '$fecha 00:00:00' and '$fecha 23:59:59' ".
		"where pedido='$pedido' order by fecha DESC ";

		$sqlagenda = "select count(pedido_id) Cantidad from carga_agenda where fecha_cita between '$fecha 00:00:00' and '$fecha 23:59:59' and pedido_id='$pedido'";

		$rstagenda = $this->connseguimiento->query($sqlagenda);
		$counteragenda = 0;
		if ($rstagenda->num_rows > 0) {
			$result = array();
			if ($row = $rstagenda->fetch_assoc()) {
				$counteragenda = $row['Cantidad'];
			}
		}
		//$this->dbConnect();
		$this->dbSeguimientoConnect();

		$sqlnovedades = "SELECT DISTINCT NOVEDAD  " .
			"FROM gestor_historicos_reagendamiento " .
			"WHERE PROCESO = 'INSTALACIONES'  ";

		$rstnovedadagenda = $this->portalbd->query($sqlnovedades);

		if ($rstnovedadagenda->num_rows > 0) {
			$sep = "";
			$novedades = "";
			$novedad = array();
			while ($row = $rstnovedadagenda->fetch_assoc()) {
				$novedad[] = $row;
			}

			$total = count($novedad);
			for ($i = 0; $i < $total; $i++) {
				$novedades = $novedades . $sep . "'" . $novedad[$i]['NOVEDAD'] . "'";
				$sep = ",";
			}

		}

		$sqlagendamineto = "SELECT NOVEDAD, FECHA_ESTADO, CELULAR_AVISAR, OBSERVACION_FENIX, OBSERVACION_GESTOR " .
			"FROM portalbd.gestor_historicos_reagendamiento " .
			"where PEDIDO_ID = '$pedido' " .
			"AND NOVEDAD in ($novedades) ";

		$rstagendamiento = $this->portalbd->query($sqlagendamineto);

		$queryclick = "select productos, fecha_cita, estado_id, tipo_trabajo, observacion, " .
			"(select descripcion from codigo_pendientes_click " .
			"where codigo = pro.codigo_pendiente_incompleto) descripcion, une_agendamientos " .
			"from carga_click pro " .
			"where pedido_id='$pedido'";

		$rstseguiclcik = $this->connseguimiento->query($queryclick);
		/*$conna=getConnAgendamiento();

							$sqlsimulador = "select b.agm_pedido, a.lag_fechacita, lag_jornadacita, ".
											"lag_horacita, lag_fuente, lag_estadoto, ".
											"lag_estadotv, lag_estadoba, lag_motivoreagenda, ".
											"lag_usuariocrea, lag_fechacrea ".
											"from agn_logagendamientos a, agn_agendamientos b ".
											"where b.agm_pedido = '$pedido' ".
											"and a.lag_agendamiento = b.agm_id ";

							$rstsimulador = $conna->query($sqlsimulador) or die($this->mysqli->error.__LINE__);
				*/

		$sqlclick = "select count(pedido_id) Cantidad from carga_click where fecha_cita between '$fecha 00:00:00' and '$fecha 23:59:59' and pedido_id='$pedido'";
		$rstclick = $this->connseguimiento->query($sqlclick);
		$counterClick = 0;
		if ($rstclick->num_rows > 0) {
			$result = array();
			if ($row = $rstclick->fetch_assoc()) {
				$counterClick = $row['Cantidad'];
			}
		}

		$rst = $this->connseguimiento->query($query);
		if ($rst->num_rows > 0 || $rstagendamiento->num_rows > 0 || $rstseguiclcik->num_rows > 0) {

			$resultado = array();
			$resultadoagenda = array();
			$resultadosimulador = array();
			$resultadosclick = array();
			while ($row = $rst->fetch_assoc()) {
				$resultado[] = $row;
			}
			while ($row = $rstagendamiento->fetch_assoc()) {
				$resultadoagenda[] = $row;
				$row['OBSERVACION_FENIX'] = utf8_encode($row['OBSERVACION_FENIX']);
				$row['OBSERVACION_GESTOR'] = utf8_encode($row['OBSERVACION_GESTOR']);
			}
			while ($row = $rstseguiclcik->fetch_assoc()) {
				$resultadosclick[] = $row;

			}
			/* while($row = $rstsimulador->fetch_assoc()){
							      	$resultadosimulador[] = $row;
                                }       */

			$this->response($this->json(array($resultado, $counteragenda, $counterClick, $resultadoagenda, $resultadosclick)), 201);

		} // If no records "No Content" status
		//busqueda pedido en preagenda, activity feed
	}

	private function registros() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		//$this->dbConnect();
		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);
		$pagina = $params['page'];
		$datos = $params['datos'];
		/* $fechaini = $datos['fechaini'];
		$fechafin = $datos['fechafin']; */
		$fechaini = (!isset($datos['fechaini'])) ? date("Y-m-d") : $datos['fechaini']; //CORRECCION DE VALIDACION DE FECHA
		$fechafin = (!isset($datos['fechafin'])) ? date("Y-m-d") : $datos['fechafin']; //CORRECCION DE VALIDACION DE FECHA
		$concepto = (isset($datos['concepto'])) ? $datos['concepto'] : '';
		$buscar = (isset($datos['buscar'])) ? $datos['buscar'] : '';

		if ($fechaini == "" || $fechafin == "") {
			$fechaini = date("Y-m-d");
			$fechafin = date("Y-m-d");
		}
		//$today = date("Y-m-d");

		if ($pagina == "undefined") {
			$pagina = "0";
		} else {
			$pagina = $pagina - 1;
		}

		$pagina = $pagina * 100;

		if ($concepto == "" || $buscar == "") {
			$parametro = "";
		} else {
			$parametro = " and $concepto = '$buscar'";
		};

		$query = "SELECT a.id, a.pedido, " .
			" (select nombre from tecnicos " .
			"where a.id_tecnico = identificacion limit 1) tecnico, " .
			"trim(a.accion) AS accion, " .
			"a.asesor, " .
			"a.fecha, a.duracion, a.proceso, " .
			"a.observaciones, a.llamada_id, a.id_tecnico, a.empresa, a.despacho, a.producto, " .
			"a.accion, trim(a.tipo_pendiente) tipo_pendiente, (select ciudad from tecnicos " .
			"where a.id_tecnico = identificacion limit 1) ciudad, a.plantilla " .
			"FROM registros a " .
			"where 1=1 " .
			" $parametro " .
			" and a.fecha BETWEEN ('$fechaini 00:00:00') AND ('$fechafin 23:59:59') " .
			"and asesor <> 'IVR'" .
			" order by a.fecha DESC " .
			" limit 100 offset $pagina ";

		$queryCount = " select count(*) as Cantidad from registros a " .
			" where 1=1 " .
			" and a.fecha BETWEEN ('$fechaini 00:00:00') AND ('$fechafin 23:59:59') " .
			" $parametro ";

		//echo $queryCount;

		$rr = $this->connseguimiento->query($queryCount);

		$counter = 0;
		if ($rr->num_rows > 0) {
			$result = array();
			if ($row = $rr->fetch_assoc()) {
				$counter = $row['Cantidad'];
			}
		}

		$rst = $this->connseguimiento->query($query);

		//echo $this->mysqli->query($sqlLogin);
		//
		if ($rst->num_rows > 0) {

			$resultado = array();

			while ($row = $rst->fetch_assoc()) {

				//var_dump($row);
				$row['id'] = utf8_encode($row['id']);
				$row['pedido'] = utf8_encode($row['pedido']);
				$row['tecnico'] = utf8_encode($row['tecnico']);
				$row['accion'] = utf8_encode($row['accion']);
				$row['asesor'] = utf8_encode($row['asesor']);
				$row['fecha'] = utf8_encode($row['fecha']);
				$row['duracion'] = utf8_encode($row['duracion']);
				$row['proceso'] = utf8_encode($row['proceso']);
				$row['observaciones'] = utf8_encode($row['observaciones']);
				$row['llamada_id'] = utf8_encode($row['llamada_id']);
				$row['id_tecnico'] = utf8_encode($row['id_tecnico']);
				$row['empresa'] = utf8_encode($row['empresa']);
				$row['despacho'] = utf8_encode($row['despacho']);
				$row['producto'] = utf8_encode($row['producto']);
				$row['tipo_pendiente'] = utf8_encode($row['tipo_pendiente']);
				$row['ciudad'] = utf8_encode($row['ciudad']);
				$row['plantilla'] = utf8_encode($row['plantilla']);

				//var_dump($row);
				$resultado[] = $row;

			}
			$this->response($this->json(array($resultado, $counter)), 201);

		} else {
			$error = array();

			$this->response($this->json($error), 400);
		}
	}

	// ====================================================
	//       Funcion para subir al frond GeneracionTT
	// ====================================================

	private function premisasInfraestructuras() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);
		$pagina = $params['page'];
		$datos = $params['datos'];
		$fechaini = $datos['fechaini'];
		$fechafin = $datos['fechafin'];
		//$concepto = $datos['concepto'];
		//$buscar = $datos['buscar'];

		if ($fechaini == "" || $fechafin == "") {
			$fechaini = date('Y-m-d');
			$fechafin = date('Y-m-d');
		}

		//echo json_encode($fechaini);
		//echo json_encode($fechafin);

		// //$today = date("Y-m-d");

		if ($pagina == "undefined") {
			$pagina = "0";
		} else {
			$pagina = $pagina - 1;
		}

		$pagina = $pagina * 100;

		// if ($concepto == "" || $buscar == "") {
		// 	$parametro = "";
		// } else {
		// 	$parametro = " and $concepto = '$buscar'";
		// };

		$query = ("	SELECT g.id, g.tt, g.quienSolicitaLaCCC, g.elementoAfectado, g.ciudad, g.region, g.fechaSolicitud
						FROM GeneracionTT g
							WHERE 1=1
							AND g.fechaSolicitud BETWEEN ('$fechaini 00:00:00') AND ('$fechafin 23:59:59')
							ORDER BY g.fechaSolicitud DESC
							limit 100 offset $pagina
				");

		//limit 10 offset $pagina

		$queryCount = ("	SELECT COUNT(*) AS Cantidad FROM GeneracionTT g
								WHERE 1=1
								AND g.fechaSolicitud BETWEEN ('$fechaini 00:00:00') AND ('$fechafin 23:59:59')

					");

		$rr = $this->connseguimiento->query($queryCount);

		//var_dump($rr);

		//Dado el total, contra el numumero de paginas
		$totalPaginas = 0;
		$counter = 0;
		if ($rr->num_rows > 0) {
			$result = array();
			if ($row = $rr->fetch_assoc()) {
				$counter = $row['Cantidad'];

				$totalPaginas = $counter / 100;
				$totalPaginas = ceil($totalPaginas); //redondear al siguiente
			}
		}

		$rst = $this->connseguimiento->query($query);

		//var_dump($rst);

		if ($rst->num_rows > 0) {

			$resultado = array();

			while ($row = $rst->fetch_assoc()) {

				$row['quienSolicitaLaCCC'] = utf8_encode($row['quienSolicitaLaCCC']);
				$row['elementoAfectado'] = utf8_encode($row['elementoAfectado']);
				$row['ciudad'] = utf8_encode($row['ciudad']);
				$row['region'] = utf8_encode($row['region']);
				$row['fechaSolicitud'] = utf8_encode($row['fechaSolicitud']);

				//var_dump($row);

				$resultado[] = $row;

			}
			$this->response($this->json(array(

				'data' => $resultado,
				'contador' => $counter,
				'totalPaginas' => $totalPaginas,

			)), 201);

		} else {

			//$error = "Solicitud incorrecta ";
			$this->response($this->json("Error"), 400);
		}
	}

	// ====================================================
	//          Funcion para guardar GeneracionTT
	// ====================================================

	private function guardarGeneracionTT() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);
		$datos = $params['datosEdicion'];
		// $key = $datos['id'];
		$tt = $datos['tt'];
		$quienSolicitaLaCCC = $datos['quienSolicitaLaCCC'];
		$elementoAfectado = $datos['elementoAfectado'];
		$ciudad = $datos['region'];
		$region = $datos['municipio'];

		if (isset($datos['id'])) {

			$sqlUpdate = ("
				UPDATE GeneracionTT g
					SET 	g.quienSolicitaLaCCC = LOWER(TRIM('$quienSolicitaLaCCC')),
							g.elementoAfectado = LOWER(TRIM('$elementoAfectado')),
							g.ciudad = LOWER(TRIM('$ciudad')),
							g.region = LOWER('$region'),
							g.fechaSolicitud = NOW()
					WHERE g.id = $key
			");

			$rst = $this->connseguimiento->query($sqlUpdate);

			/*==========OPCION 1=============*/
			if (is_numeric($rst) OR $rst === true) {
				$this->response($this->json('Pedido actualizado'), 201);
			} else {
				$this->response($this->json("Error"), 400);
			}

			/*==========OPCION 2=============*/
			// if (is_numeric($rst) OR $rst === true) {
			// 	$respuesta = array('err' => false, 'Mensaje' => 'Registro actualizado');
			// } else {
			// 	$respuesta = array('err' => true, 'Mensaje' => $rst);
			// }

		} else {

			$sqlInsetar = ("
					INSERT INTO GeneracionTT
						(tt, quienSolicitaLaCCC, elementoAfectado, ciudad, region, fechaSolicitud)
					VALUES
						('$tt', LOWER(TRIM('$quienSolicitaLaCCC')), LOWER(TRIM('$elementoAfectado')), LOWER('$ciudad'), LOWER('$region'), NOW())
				");

			$rst = $this->connseguimiento->query($sqlInsetar);

			/*==========OPCION 1=============*/
			if (is_numeric($rst) OR $rst === true) {
				$this->response($this->json('Pedido actualizado'), 201);
			} else {
				$this->response($this->json("Error"), 400);
			}

			/*==========OPCION 2=============*/
			// if (is_numeric($rst) OR $rst === true) {
			// 	$respuesta = array('err' => false, 'Mensaje' => 'Registro actualizado');
			// } else {
			// 	$respuesta = array('err' => true, 'Mensaje' => $rst);
			// }

			//echo json_encode('INSERTAR');
		}

		echo json_encode($respuesta);

	}

	// ====================================================
	//          Funcion para exportar en CSV GENERACIONTT
	// ====================================================

	private function csvGeneracionTT() {
		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$usuarioid = $params['datosLogin'];
		$usuarioid = $usuarioid['LOGIN'];
		$datos = $params['datos'];
		$fechaini = $datos['fechaini'];
		$fechafin = $datos['fechafin'];

		if ($fechaini == "" && $fechafin == "") {
			$fechaini = date("Y") . "-" . date("m") . "-" . date("d");
			$fechafin = date("Y") . "-" . date("m") . "-" . date("d");
		}

		if ($fechaini == $fechafin) {
			$filename = "GeneracionTT" . "_" . $fechaini . "_" . $usuarioid . ".csv";
		} else {
			$filename = "GeneracionTT" . "_" . $fechaini . "_" . $fechafin . "_" . $usuarioid . ".csv";
		}

		$query = ("	SELECT g.tt, g.quienSolicitaLaCCC, g.elementoAfectado, g.ciudad, g.region, g.fechaSolicitud
						FROM GeneracionTT g
							WHERE 1=1
							AND g.fechaSolicitud BETWEEN ('$fechaini 00:00:00') AND ('$fechafin 23:59:59')
				");

		$queryCount = ("	SELECT COUNT(tt) AS Cantidad FROM GeneracionTT g
								WHERE g.fechaSolicitud BETWEEN ('$fechaini 00:00:00') AND ('$fechafin 23:59:59')

						");

		//s    echo $queryCount;
		//
		$rr = $this->connseguimiento->query($queryCount);
		$counter = 0;
		if ($rr->num_rows > 0) {
			$result = array();
			if ($row = $rr->fetch_assoc()) {
				$counter = $row['Cantidad'];
			}
		}
		//echo $counter;

		$rst = $this->connseguimiento->query($query) or die($this->connseguimiento->error . __LINE__);

		if ($rst->num_rows > 0) {

			//Insert en log
			//$sql_log="insert into gestor_despacho.gopd_logoperaciones (USUARIO_ID, TIPO_ACTIVIDAD, DESCRIPCION, IDENTIFICADOR, IP, PC) values(UPPER('$usuarioid'),'EXPORTE','EXPORTO CSV_HISTORICO','LOGIN: $usuarioid','$usuarioIp','$usuarioPc')";

			//$rlog = $this->connemtel->query($sql_log) or die($this->connemtel->error.__LINE__);
			//Insert en log

			$result = array();
			$fp = fopen("../tmp/$filename", 'w');
			//echo $fp;

			$columnas = array('TT',
				'Quien Solicita',
				'Elemento Afectado',
				'Ciudad',
				'Region',
				'Fecha Solicitud');

			fputcsv($fp, $columnas);
			//$carlitos=0;
			while ($row = $rst->fetch_assoc()) {

				$row['quienSolicitaLaCCC'] = utf8_encode($row['quienSolicitaLaCCC']);
				$row['elementoAfectado'] = utf8_encode($row['elementoAfectado']);
				$row['ciudad'] = utf8_encode($row['ciudad']);
				$row['region'] = utf8_encode($row['region']);
				$row['fechaSolicitud'] = utf8_encode($row['fechaSolicitud']);
				//$result[] = $row;
				fputcsv($fp, $row);
				//if($carlitos==0){var_dump($row);$carlitos=1;};
			}

			fclose($fp);

			$this->response($this->json(array($filename, $counter)), 200);
		}
		$this->response('', 203); // If no records "No Content" status
		//descarga archivo registros, forma asesores, activity feed
	}

	// ==============================================================
	//       Funcion para subir al front Novedades de los Tecnicos
	// ==============================================================

	private function novedadesTecnico() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);
		$pagina = $params['page'];
		$datos = $params['datos'];
		/* $fechaini = $datos['fechaini'];
		$fechafin = $datos['fechafin']; */
		$fechaini = (!isset($datos['fechaini'])) ? date("Y-m-d") : $datos['fechaini']; //CORRECCION DE VALIDACION DE FECHA
		$fechafin = (!isset($datos['fechafin'])) ? date("Y-m-d") : $datos['fechafin']; //CORRECCION DE VALIDACION DE FECHA

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

		$query = ("	SELECT id, cedulaTecnico, nombreTecnico, contracto, proceso, pedido, tiponovedad, /*region,*/ municipio, situacion, horamarcaensitio,/*detalle, */observaciones, idllamada, observacionCCO
						FROM NovedadesVisitas
							WHERE 1=1
							AND fecha BETWEEN ('$fechaini 00:00:00') AND ('$fechafin 23:59:59')
							ORDER BY fecha DESC
							limit 100 offset $pagina
				");

		$queryCount = ("	SELECT COUNT(*) AS Cantidad FROM NovedadesVisitas
								WHERE 1=1
								AND fecha BETWEEN ('$fechaini 00:00:00') AND ('$fechafin 23:59:59')

					");

		$rr = $this->connseguimiento->query($queryCount);

		//Dado el total, contra el numumero de paginas
		$totalPaginas = 0;
		$counter = 0;
		if ($rr->num_rows > 0) {
			$result = array();
			if ($row = $rr->fetch_assoc()) {
				$counter = $row['Cantidad'];

				$totalPaginas = $counter / 100;
				$totalPaginas = ceil($totalPaginas); //redondear al siguiente
			}
		}

		$rst = $this->connseguimiento->query($query);

		if ($rst->num_rows > 0) {

			$resultado = array();

			while ($row = $rst->fetch_assoc()) {

				$row['cedulaTecnico'] = utf8_encode($row['cedulaTecnico']);
				$row['nombreTecnico'] = utf8_encode($row['nombreTecnico']);
				$row['contracto'] = utf8_encode($row['contracto']);
				$row['proceso'] = utf8_encode($row['proceso']);
				$row['pedido'] = utf8_encode($row['pedido']);
				$row['tiponovedad'] = utf8_encode($row['tiponovedad']);
				$row['municipio'] = utf8_encode($row['municipio']);
				$row['situacion'] = utf8_encode($row['situacion']);
				$row['horamarcaensitio'] = utf8_encode($row['horamarcaensitio']);
				$row['idllamada'] = utf8_encode($row['idllamada']);
				$row['observaciones'] = utf8_encode($row['observaciones']);
				$row['observacionCCO'] = utf8_encode($row['observacionCCO']);

				$resultado[] = $row;

			}
			$this->response($this->json(array(

				'data' => $resultado,
				'contador' => $counter,
				'totalPaginas' => $totalPaginas,

			)), 201);

		} else {

			$this->response($this->json("Error"), 400);
		}
	}


	//=========================================================
	/*FUNCION PARA SUBIR LAS REGIONES*/
	//=========================================================

	private function Regiones() {

		if ($this->get_request_method() != "GET") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$query = " 	SELECT region
					FROM regiones
					ORDER BY region ASC ";

		$rst = $this->connseguimiento->query($query);

		if ($rst->num_rows > 0) {

			$resultado = array();
			while ($row = $rst->fetch_assoc()) {

				$row['region'] = utf8_encode($row['region']);

				$resultado[] = $row;

			}

			$this->response($this->json(array($resultado)), 201);

		} else {
			$this->response($this->json($error), 400);
		} // If no records "No Content" status
	}

	//=========================================================
	/*FUNCION PARA SUBIR LOS MUNICIPIOS*/
	//=========================================================

	private function Municipios() {

		if ($this->get_request_method() != "GET") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$region = $this->_request['region'];
		//echo json_encode($region);

		$query = " 	SELECT municipio
					FROM municipios m
					INNER JOIN regiones r ON m.codigoRg=r.codigoRg
					WHERE region ='$region'
					ORDER BY municipio ASC  ";

		$rst = $this->connseguimiento->query($query);

		if ($rst->num_rows > 0) {

			$resultado = array();
			while ($row = $rst->fetch_assoc()) {

				$row['municipio'] = utf8_encode($row['municipio']);

				$resultado[] = $row;

				//echo json_encode($resultado);
			}

			$this->response($this->json(array($resultado)), 201);

		} else {
			$this->response($this->json($error), 400);
		}
	}

	//=========================================================
	/*FUNCION PARA SUBIR LAS SituacionNovedadesVisitas*/
	//=========================================================

	private function SituacionNovedadesVisitas() {

		if ($this->get_request_method() != "GET") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$query = " 	SELECT situacion
					FROM SituacionNovedadesVisitas
					ORDER BY situacion ASC ";

		$rst = $this->connseguimiento->query($query);

		if ($rst->num_rows > 0) {

			$resultado = array();
			while ($row = $rst->fetch_assoc()) {

				$row['situacion'] = utf8_encode($row['situacion']);

				//var_dump($row);

				$resultado[] = $row;

			}

			$this->response($this->json(array($resultado)), 201);

		} else {
			$this->response($this->json($error), 400);
		} // If no records "No Content" status
	}

	//=========================================================
	/*FUNCION PARA SUBIR LOS DetalleNovedadesVisitas*/
	//=========================================================

	private function DetalleNovedadesVisitas() {

		if ($this->get_request_method() != "GET") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$situacion = $this->_request['situacion'];

		$query = " 	SELECT dnv.detalle
					FROM DetalleNovedadesVisitas dnv
					INNER JOIN SituacionNovedadesVisitas snv ON dnv.situacionId=snv.situacionId
					WHERE snv.situacion ='$situacion'
					ORDER BY dnv.detalle ASC  ";

		$rst = $this->connseguimiento->query($query);

		if ($rst->num_rows > 0) {

			$resultado = array();
			while ($row = $rst->fetch_assoc()) {

				$row['detalle'] = utf8_encode($row['detalle']);

				$resultado[] = $row;

				//echo json_encode($resultado);
			}

			$this->response($this->json(array($resultado)), 201);

		} else {
			$this->response($this->json($error), 400);
		}
	}

	// ====================================================
	//        Funcion para guardar Novedades Tecnicos
	// ====================================================

	private function guardarNovedadesTecnico() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);
		$datos = $params['datosEdicion'];
		$login = $params['login'];
		$login = $login['LOGIN'];
		/* $key = $datos['id'];
		$contracto = $datos['contracto'];
		$cedulaTecnico = $datos['cedulaTecnico'];
		$nombreTecnico = utf8_decode($datos['nombreTecnico']);
		$region = $datos['region'];
		$municipio = utf8_decode($datos['municipio']);
		$situacion = $datos['situacion'];
		$detalle = $datos['detalle'];
		$observaciones = utf8_decode($datos['observaciones']);
		$tiponovedad = utf8_decode($datos['tiponovedad']);
		$pedido = $datos['pedido'];
		$proceso = $datos['proceso']; */
		$key = (isset($datos['id'])) ? $datos['id'] : '';
		$contracto = (isset($datos['contracto'])) ? $datos['contracto'] : '';
		$cedulaTecnico = (isset($datos['cedulaTecnico'])) ? $datos['cedulaTecnico'] : '';
		$nombreTecnico = (isset($datos['nombreTecnico'])) ? utf8_decode($datos['nombreTecnico']) : '';
		$region = (isset($datos['region'])) ? $datos['region'] : '';
		$municipio = (isset($datos['municipio'])) ? utf8_decode($datos['municipio']) : '';
		$situacion = (isset($datos['situacion'])) ? $datos['situacion'] : '';
		$detalle = (isset($datos['detalle'])) ? $datos['detalle'] : '';
		$observaciones = (isset($datos['observaciones'])) ? utf8_decode($datos['observaciones']) : '';
		$tiponovedad = (isset($datos['tiponovedad'])) ? utf8_decode($datos['tiponovedad']) : '';
		$pedido = (isset($datos['pedido'])) ? $datos['pedido'] : '';
		$proceso = (isset($datos['proceso'])) ? $datos['proceso'] : '';
		$situaciontriangulo = (isset($datos['situaciontriangulo'])) ? utf8_decode($datos['situaciontriangulo']) : '';
		$motivo = (isset($datos['motivotriangulo'])) ? utf8_decode($datos['motivotriangulo']) : '';
		if(isset($datos['submotivotriangulo'])){
			$submotivo = utf8_decode($datos['submotivotriangulo']);
		} else {
			$submotivo = "";
		}
		$horamarcasitio = date('h:i A', strtotime($datos['horamarcaensitio']));
		$idllamada = $datos['idLlamada'];

		$contrato2 = $datos['contrato2'];
        $cedulaTecnico2 = $datos['cedulaTecnico2'];
        $nombreTecnico2 = utf8_decode($datos['nombreTecnico2']);
        $proceso2 = $datos['proceso2'];
        $municipio2 = utf8_decode($datos['municipio2']);


		if ($tiponovedad == 'Cumplimiento de Agenda' and $cedulaTecnico == null) {

					$sqlInsetar = ("
							INSERT INTO NovedadesVisitas
								(fecha, usuario, tiponovedad, pedido, contracto, cedulaTecnico, nombreTecnico, proceso, region, municipio, situacion, horamarcaensitio, detalle, observaciones, idllamada, motivo, submotivo)
							VALUES
								(NOW(), '$login', '$tiponovedad', '$pedido', UPPER('$contrato2'), TRIM($cedulaTecnico2), TRIM('$nombreTecnico2'), TRIM('$proceso2'), LOWER('$region'), LOWER('$municipio2'), LOWER('$situaciontriangulo'), '$horamarcasitio', LOWER('$detalle'), LOWER(TRIM('$observaciones')), TRIM('$idllamada'), '$motivo', '$submotivo')
						");

					$rst = $this->connseguimiento->query($sqlInsetar);

					/*==========OPCION 1=============*/
					if (is_numeric($rst) OR $rst === true) {
						$this->response($this->json('Pedido actualizado'), 201);
					} else {
						$this->response($this->json("Error"), 400);
					}

		} else if ($tiponovedad == 'Cumplimiento de Agenda' and $region <> null) {

					$sqlInsetar = ("
							INSERT INTO NovedadesVisitas
								(fecha, usuario, tiponovedad, pedido, contracto, cedulaTecnico, nombreTecnico, proceso, region, municipio, situacion, horamarcaensitio, detalle, observaciones, idllamada, motivo, submotivo)
							VALUES
								(NOW(), '$login', '$tiponovedad', '$pedido', '$contracto', TRIM($cedulaTecnico), UPPER(TRIM('$nombreTecnico')), TRIM('$proceso'), LOWER('$region'), LOWER('$municipio'), LOWER('$situaciontriangulo'), '$horamarcasitio', LOWER('$detalle'), LOWER(TRIM('$observaciones')), TRIM('$idllamada'), '$motivo', '$submotivo')
						");

					$rst = $this->connseguimiento->query($sqlInsetar);

					/*==========OPCION 1=============*/
					if (is_numeric($rst) OR $rst === true) {
						$this->response($this->json('Pedido actualizado'), 201);
					} else {
						$this->response($this->json("Error"), 400);
					}

		} else 	if ($tiponovedad == 'Triangulo de Produccion' and $cedulaTecnico == null) {

					$sqlInsetar = ("
							INSERT INTO NovedadesVisitas
								(fecha, usuario, tiponovedad, pedido, contracto, cedulaTecnico, nombreTecnico, proceso, region, municipio, situacion, horamarcaensitio, observaciones, idllamada, motivo, submotivo)
							VALUES
								(NOW(), '$login', '$tiponovedad', '$pedido', UPPER('$contrato2'), TRIM($cedulaTecnico2), TRIM('$nombreTecnico2'), LOWER('$proceso2'),LOWER('$region'), LOWER('$municipio2'), LOWER('$situaciontriangulo'), '$horamarcasitio', LOWER(TRIM('$observaciones')), TRIM('$idllamada'), '$motivo', '$submotivo')
						");

					$rst = $this->connseguimiento->query($sqlInsetar);

					/*==========OPCION 1=============*/
					if (is_numeric($rst) OR $rst === true) {
						$this->response($this->json('Pedido actualizado'), 201);
					} else {
						$this->response($this->json("Error"), 400);
					}

		} else if ($tiponovedad == 'Triangulo de Produccion' and $region <> null) {

					$sqlInsetar = ("
							INSERT INTO NovedadesVisitas
								(fecha, usuario, tiponovedad, pedido, contracto, cedulaTecnico, nombreTecnico, proceso, region, municipio, situacion, horamarcaensitio, observaciones, idllamada, motivo, submotivo)
							VALUES
								(NOW(), '$login', '$tiponovedad', '$pedido', '$contracto', TRIM($cedulaTecnico), UPPER(TRIM('$nombreTecnico')), TRIM('$proceso'), LOWER('$region'), LOWER('$municipio'), LOWER('$situaciontriangulo'), '$horamarcasitio', LOWER(TRIM('$observaciones')), TRIM('$idllamada'), '$motivo', '$submotivo')
						");

					$rst = $this->connseguimiento->query($sqlInsetar);

					/*==========OPCION 1=============*/
					if (is_numeric($rst) OR $rst === true) {
						$this->response($this->json('Pedido actualizado'), 201);
					} else {
						$this->response($this->json("Error"), 400);
					}

		}

		echo json_encode($respuesta);

	}

	// ==========================================================
	//          Funcion para exportar en CSV Novedades Tecnicos
	// ==========================================================

	private function csvNovedadesTecnico() {
		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$usuarioid = $params['datosLogin'];
		$usuarioid = $usuarioid['LOGIN'];
		$datos = $params['datos'];
		$fechaini = $datos['fechaini'];
		$fechafin = $datos['fechafin'];

		if ($fechaini == "" && $fechafin == "") {
			$fechaini = date("Y") . "-" . date("m") . "-" . date("d");
			$fechafin = date("Y") . "-" . date("m") . "-" . date("d");
		}

		if ($fechaini == $fechafin) {
			$filename = "NovedadesTecnicos" . "_" . $fechaini . "_" . $usuarioid . ".csv";
		} else {
			$filename = "NovedadesTecnicos" . "_" . $fechaini . "_" . $fechafin . "_" . $usuarioid . ".csv";
		}

		$query = ("	SELECT n.fecha, n.usuario, n.municipio, n.region, n.proceso, n.horamarcaensitio, n.tiponovedad, n.pedido, n.cedulaTecnico, n.nombreTecnico, n.contracto, n.situacion, n.motivo, n.submotivo, n.observaciones, n.observacionCCO, n.idllamada
						FROM NovedadesVisitas n
						WHERE 1=1
						AND n.fecha BETWEEN ('$fechaini 00:00:00') AND ('$fechafin 23:59:59')
				");

		$queryCount = ("	SELECT COUNT(*) AS Cantidad FROM NovedadesVisitas n
								WHERE n.fecha BETWEEN ('$fechaini 00:00:00') AND ('$fechafin 23:59:59')

						");

		$rr = $this->connseguimiento->query($queryCount);
		$counter = 0;
		if ($rr->num_rows > 0) {
			$result = array();
			if ($row = $rr->fetch_assoc()) {
				$counter = $row['Cantidad'];
			}
		}

		$rst = $this->connseguimiento->query($query) or die($this->connseguimiento->error . __LINE__);

		if ($rst->num_rows > 0) {

			$result = array();
			$fp = fopen("../tmp/$filename", 'w');

			$columnas = array('Fecha',
				'Despachador',
				'Municipio',
				'Region',
				'Proceso',
				'Hora marca en sitio',
				'Tipo de Novedad',
				'Pedido',
				'Cedula del Tecnico',
				'Nombre del Tecnico',
				'Contrato',
				'Situacion',
				'Motivo',
				'Submotivo',
				'Observaciones',
				'Observacion CCO',
				'ID Llamada'
			);

			fputcsv($fp, $columnas);

			while ($row = $rst->fetch_assoc()) {

				$row['fecha'] = utf8_encode($row['fecha']);
				$row['usuario'] = ($row['usuario']);
				$row['municipio'] = ($row['municipio']);
				$row['region'] = ($row['region']);
				$row['proceso'] = ($row['proceso']);
				$row['horamarcaensitio'] = utf8_encode($row['horamarcaensitio']);
				$row['tiponovedad'] = ($row['tiponovedad']);
				$row['pedido'] = utf8_encode($row['pedido']);
				$row['cedulaTecnico'] = ($row['cedulaTecnico']);
				$row['nombreTecnico'] = ($row['nombreTecnico']);
			 	$row['contracto'] = ($row['contracto']);
				$row['situacion'] = ($row['situacion']);
				$row['motivo'] = ($row['motivo']);
				$row['submotivo'] = ($row['submotivo']);
				$row['observaciones'] = ($row['observaciones']);
				$row['observacionCCO'] = ($row['observacionCCO']);
				$row['idllamada'] = utf8_encode($row['idllamada']);

				//$result[] = $row;
				fputcsv($fp, $row);

			}

			fclose($fp);

			$this->response($this->json(array($filename, $counter)), 200);
		}
		$this->response('', 203);
	}

	// ==========================================================
	//    Funcion para actualizar observaciones de supervisor
	// ==========================================================

	private function updateNovedadesTecnico() {
		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$observacionCCO = $params['datosEditar'];

		$pedido = $params['pedido'];

		$sqlUpdate = ("UPDATE NovedadesVisitas SET observacionCCO = '$observacionCCO' WHERE pedido = '$pedido'");

		$rst = $this->connseguimiento->query($sqlUpdate);

		if (is_numeric($rst) OR $rst === true) {
			$this->response($this->json('Novedad actualizada'), 201);
		} else {
			$this->response($this->json("Error"), 400);
		}

	}

	/*FIN DEL BLOQUE VISITAS DE LOS TECNICOS*/
	//=============================================================

	//=============================================================
	/*INICIO DEL BLOQUE ESCALAMIENTO PREMISAS*/

	private function escalamientoInfraestructura() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);
		$pagina = $params['page'];
		$datos = $params['datos'];
		$fechaini = $datos['fechaini'];
		$fechafin = $datos['fechafin'];

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

		$query = ("	SELECT id, pedido, tecnico, loginDespachador, ciudad, gestion, observacion, nota, fechaGestion
						FROM Escalamientos
							WHERE 1=1
							AND fechaGestion BETWEEN ('$fechaini 00:00:00') AND ('$fechafin 23:59:59')
							ORDER BY fechaGestion DESC
							limit 100 offset $pagina
				");

		$queryCount = ("	SELECT COUNT(*) AS Cantidad FROM Escalamientos
								WHERE 1=1
								AND fechaGestion BETWEEN ('$fechaini 00:00:00') AND ('$fechafin 23:59:59')

					");

		$rr = $this->connseguimiento->query($queryCount);

		//var_dump($rr);

		//Dado el total, contra el numumero de paginas
		$totalPaginas = 0;
		$counter = 0;
		if ($rr->num_rows > 0) {
			$result = array();
			if ($row = $rr->fetch_assoc()) {
				$counter = $row['Cantidad'];

				$totalPaginas = $counter / 100;
				$totalPaginas = ceil($totalPaginas); //redondear al siguiente
			}
		}

		$rst = $this->connseguimiento->query($query);

		//var_dump($rst);

		if ($rst->num_rows > 0) {

			$resultado = array();

			while ($row = $rst->fetch_assoc()) {

				$row['pedido'] = utf8_encode($row['pedido']);
				$row['tecnico'] = utf8_encode($row['tecnico']);
				$row['loginDespachador'] = utf8_encode($row['loginDespachador']);
				$row['ciudad'] = utf8_encode($row['ciudad']);
				$row['gestion'] = utf8_encode($row['gestion']);
				$row['observacion'] = utf8_encode($row['observacion']);
				$row['nota'] = utf8_encode($row['nota']);

				//var_dump($row);

				$resultado[] = $row;

			}
			$this->response($this->json(array(

				'data' => $resultado,
				'contador' => $counter,
				'totalPaginas' => $totalPaginas,

			)), 201);

		} else {

			//$error = "Solicitud incorrecta ";
			$this->response($this->json("Error"), 400);
		}
	}

	//=========================================================
	/*FUNCION PARA SOLICITAR GRUPO COLA*/
	//=========================================================

	private function GrupoCola() {

		if ($this->get_request_method() != "GET") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$query = "SELECT nota FROM Notas WHERE nota <> 'mal codigo' ORDER BY nota ASC";

		$rst = $this->connseguimiento->query($query);

		if ($rst->num_rows > 0) {

			$resultado = array();
			while ($row = $rst->fetch_assoc()) {

				$row['nota'] = utf8_encode($row['nota']);

				$resultado[] = $row;

			}
			$this->response($this->json(array($resultado)), 201);
		} else {
			$this->response($this->json($error), 400);
		}
	}

	//===================================================================
	/*FUNCION PARA SOLICITAR LA INFORMACION DE LA TABLA DE GESTIONES*/
	//==================================================================

	private function gestionEscalimiento() {

		if ($this->get_request_method() != "GET") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$query = "SELECT g.gestion FROM Gestiones g";

		$rst = $this->connseguimiento->query($query);

		if ($rst->num_rows > 0) {

			$resultado = array();
			while ($row = $rst->fetch_assoc()) {

				$row['gestion'] = utf8_encode($row['gestion']);

				$resultado[] = $row;

			}
			$this->response($this->json(array($resultado)), 201);
		} else {
			$this->response($this->json($error), 400);
		}
	}

	//==========================================================================
	/*FUNCION PARA SOLICITAR LA INFORMACION DE LA TABLA DE OBSERVACIONES*/
	//==========================================================================

	private function observacionEscalimiento() {

		if ($this->get_request_method() != "GET") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$gestion = $this->_request['gestion'];
		//echo json_encode($gestion);

		$query = " 	SELECT o.observacion FROM Gestiones g
					INNER JOIN GestionesObservaciones go 	ON g.codGestion=go.codGestion
					INNER JOIN Observaciones o				ON go.codObservacion=o.codObservacion
					WHERE g.gestion = '$gestion'
				";

		$rst = $this->connseguimiento->query($query);

		if ($rst->num_rows > 0) {

			$resultado = array();
			while ($row = $rst->fetch_assoc()) {

				$row['observacion'] = utf8_encode($row['observacion']);

				$resultado[] = $row;

				//echo json_encode($resultado);
			}

			$this->response($this->json(array($resultado)), 201);

		} else {
			$this->response($this->json($error), 400);
		}
	}

	//==========================================================================
	/*FUNCION PARA SOLICITAR LA INFORMACION DE LA TABLA DE NOTAS*/
	//==========================================================================

	private function notasEscalamiento() {

		if ($this->get_request_method() != "GET") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$observacion = $this->_request['observacion'];
		//echo json_encode($observacion);

		$query = " 	SELECT n.nota FROM Gestiones g
							INNER JOIN GestionesObservaciones go 	ON g.codGestion=go.codGestion
							INNER JOIN Observaciones o				ON go.codObservacion=o.codObservacion
							INNER JOIN ObservacionesNotas ot			ON o.codObservacion=ot.codObservacion
							INNER JOIN Notas n						ON ot.codNota=n.codNota
							WHERE o.observacion = '$observacion'
				";

		$rst = $this->connseguimiento->query($query);

		if ($rst->num_rows > 0) {

			$resultado = array();
			while ($row = $rst->fetch_assoc()) {

				$row['nota'] = utf8_encode($row['nota']);

				$resultado[] = $row;

				//echo json_encode($resultado);
			}

			$this->response($this->json(array($resultado)), 201);

		} else {
			$this->response($this->json($error), 400);
		}
	}

	// ====================================================
	//          Funcion para guardar Escalamiento
	// ====================================================

	private function infoEscalamiento() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);
		$datos = $params['datosEdicion'];

		echo json_encode($datos);
		//$key = $datos['key'];
		$pedido = $datos['pedido'];
		$tecnico = $datos['tecnico'];
		$loginDespachador = $datos['loginDespachador'];
		$nombreDespachador = $datos['nombreDespachador'];
		$tecnologia = $datos['tecnologia'];
		$region = $datos['region'];
		$ciudad = $datos['municipio'];
		$grupoCola = $datos['grupoCola'];
		$gestion = $datos['gestion'];
		$observacion = $datos['observacion'];
		$nota = $datos['nota'];
		$devueltoPorInfra = $datos['devueltoPorInfra'];
		$gtc = $datos['gtc'];
		$click = $datos['click'];
		$pagSto = $datos['pagSto'];
		$siebel = $datos['siebel'];

		if ($gtc == true) {
			$gtc = 'SI';
		} else if ($gtc == false) {
			$gtc = 'NO';
		}

		if ($click == true) {
			$click = 'SI';
		} else if ($click == false) {
			$click = 'NO';
		}

		if ($pagSto == true) {
			$pagSto = 'SI';
		} else if ($pagSto == false) {
			$pagSto = 'NO';
		}

		if ($siebel == true) {
			$siebel = 'SI';
		} else if ($siebel == false) {
			$siebel = 'NO';
		}

		if (isset($datos['id'])) {

			$sqlUpdate = ("
				UPDATE Escalamientos e
				SET e.pedido 			= TRIM('$pedido'),
					e.tecnico 			= LOWER(TRIM('$tecnico')),
					e.loginDespachador 	= LOWER(TRIM('$loginDespachador')),
					e.nombreDespachador = LOWER(TRIM('$nombreDespachador')),
					e.tecnologia 		= TRIM('$tecnologia'),
					e.region 			= LOWER(TRIM('$region')),
					e.ciudad 			= LOWER(TRIM('$ciudad')),
					e.grupoCola 		= LOWER(TRIM('$grupoCola')),
					e.gestion 			= LOWER(TRIM('$gestion')),
					e.observacion 		= LOWER(TRIM('$observacion')),
					e.nota 				= LOWER(TRIM('$nota')),
					e.fechaGestion 		= NOW(),
					e.devueltoPorInfra 	= TRIM('$devueltoPorInfra'),
					e.gtc 				= '$gtc',
					e.click 			= '$click',
					e.paginaSeguimiento = '$pagSto',
					e.siebel 			= '$siebel'
				WHERE e.id = $key
			");

			$rst = $this->connseguimiento->query($sqlUpdate);

			/*==========OPCION 1=============*/
			if (is_numeric($rst) OR $rst === true) {
				$this->response($this->json('Pedido actualizado'), 201);
			} else {
				$this->response($this->json("Error"), 400);
			}

		} else {

			$sqlInsetar = ("
					INSERT INTO Escalamientos
					(	pedido, tecnico, loginDespachador, nombreDespachador, tecnologia, region, ciudad, grupoCola,
						gestion, observacion, nota, fechaGestion, devueltoPorInfra, gtc, click, paginaSeguimiento, siebel
					)
					VALUES
					(	TRIM('$pedido'), LOWER(TRIM('$tecnico')), LOWER(TRIM('$loginDespachador')), LOWER(TRIM('$nombreDespachador')), TRIM('$tecnologia'),
						LOWER(TRIM('$region')), LOWER(TRIM('$ciudad')), LOWER(TRIM('$grupoCola')), LOWER(TRIM('$gestion')), LOWER(TRIM('$observacion')),
						LOWER(TRIM('$nota')), NOW(), TRIM('$devueltoPorInfra'), '$gtc', '$click', '$pagSto', '$siebel'
					)
				");

			$rst = $this->connseguimiento->query($sqlInsetar);

			//echo json_encode($rst);

			/*==========OPCION 1=============*/
			if (is_numeric($rst) OR $rst === true) {
				$this->response($this->json('Pedido actualizado'), 201);
			} else {
				$this->response($this->json("Error"), 400);
			}
		}
		echo json_encode($respuesta);
	}

	// ====================================================
	//          Funcion para exportar en CSV Escalamiento
	// ====================================================

	private function csvEscalamientoExp() {
		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$usuarioid = $params['datosLogin'];
		$usuarioid = $usuarioid['LOGIN'];
		$datos = $params['datos'];
		$fechaini = $datos['fechain'];
		$fechafin = $datos['fechafi'];

		if ($fechaini == "" && $fechafin == "") {
			$fechaini = date("Y") . "-" . date("m") . "-" . date("d");
			$fechafin = date("Y") . "-" . date("m") . "-" . date("d");
		}

		if ($fechaini == $fechafin) {
			$filename = "Escalamiento" . "_" . $fechaini . "_" . $usuarioid . ".csv";
		} else {
			$filename = "Escalamiento" . "_" . $fechaini . "_" . $fechafin . "_" . $usuarioid . ".csv";
		}

		$query = ("	SELECT 	e.fechaGestion, e.pedido, e.tecnico, e.loginDespachador, e.nombreDespachador, e.tecnologia, e.region, e.ciudad, e.grupoCola, e.gestion,
							e.observacion, e.nota, e.devueltoPorInfra, e.gtc, e.click, e.paginaSeguimiento, e.siebel
						FROM Escalamientos e
						WHERE 1=1
						AND e.fechaGestion BETWEEN ('$fechaini 00:00:00') AND ('$fechafin 23:59:59')
				");

		$queryCount = ("	SELECT COUNT(pedido) AS Cantidad FROM Escalamientos
								WHERE fechaGestion BETWEEN ('$fechaini 00:00:00') AND ('$fechafin 23:59:59')

						");

		$rr = $this->connseguimiento->query($queryCount);
		$counter = 0;
		if ($rr->num_rows > 0) {
			$result = array();
			if ($row = $rr->fetch_assoc()) {
				$counter = $row['Cantidad'];
			}
		}

		$rst = $this->connseguimiento->query($query) or die($this->connseguimiento->error . __LINE__);

		if ($rst->num_rows > 0) {

			$result = array();
			$fp = fopen("../tmp/$filename", 'w');

			$columnas = array(
				'Fecha Gestion',
				'Pedido',
				'Login del Tecnico',
				'Login Despachador',
				'Nombre del Despachador',
				'Tecnologia',
				'Region',
				'Ciudad',
				'Grupo - Cola',
				'Gestion',
				'Observacion',
				'Notas',
				'Devuelto por Infra',
				'Se Escalo en Gtc',
				'Se Escalo en Click',
				'Se Escalo en PagSto',
				'Se Escalo en Siebel',
			);

			fputcsv($fp, $columnas);

			while ($row = $rst->fetch_assoc()) {

				$row['pedido'] = utf8_encode($row['pedido']);
				$row['tecnico'] = utf8_encode($row['tecnico']);
				$row['loginDespachador'] = utf8_encode($row['loginDespachador']);
				$row['nombreDespachador'] = utf8_encode($row['nombreDespachador']);
				$row['tecnologia'] = utf8_encode($row['tecnologia']);
				$row['region'] = utf8_encode($row['region']);
				$row['ciudad'] = utf8_encode($row['ciudad']);
				$row['grupoCola'] = utf8_encode($row['grupoCola']);
				$row['gestion'] = utf8_encode($row['gestion']);
				$row['observacion'] = utf8_encode($row['observacion']);
				$row['nota'] = utf8_encode($row['nota']);
				$row['devueltoPorInfra'] = utf8_encode($row['devueltoPorInfra']);
				$row['gtc'] = utf8_encode($row['gtc']);
				$row['click'] = utf8_encode($row['click']);
				$row['paginaSeguimiento'] = utf8_encode($row['paginaSeguimiento']);
				$row['siebel'] = utf8_encode($row['siebel']);

				fputcsv($fp, $row);
			}

			fclose($fp);

			$this->response($this->json(array($filename, $counter)), 200);
		}
		$this->response('', 203);
	}

	/*=============================================================*/
	/*=============================================================*/
	/*-------->INICIO DEL BLOQUE DE VISITAS EN CONJUNTO<-----------*/
	/*=============================================================*/
	/*=============================================================*/

	// ==============================================================
	//      Funcion para subir al front las Visitas en Conjunto
	// ==============================================================
	private function visitasEnConjunto(){
		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);
		$pagina = $params['page'];
		$datos = $params['datos'];
		$fechaini = $datos['fechaini'];
		$fechafin = $datos['fechafin'];

		if ($fechaini == "" || $fechafin == "") {
			$fechaini = date('Y-m-d');
			$fechafin = date('Y-m-d');
		}

		if ($pagina == "undefined") {
			$pagina = "0";
		}
		else {
			$pagina = $pagina - 1;
		}

		$pagina = $pagina * 100;

		$query = ("	SELECT id, pedido, tecnicopremisas, tecnicoinfraestructura, fechavisita, region, municipio, contrato, gestion, quiensolicitavisita, notas, grupo, fechasolicitud, fechafingestion
						FROM visitasenconjunto
							WHERE 1=1
							AND fechasolicitud BETWEEN ('$fechaini 00:00:00') AND ('$fechafin 23:59:59')
							ORDER BY fechasolicitud DESC
							limit 100 offset $pagina
				");


		$queryCount = ("	SELECT COUNT(*) AS Cantidad FROM visitasenconjunto
								WHERE 1=1
								AND fechasolicitud BETWEEN ('$fechaini 00:00:00') AND ('$fechafin 23:59:59')

					");

		$rr = $this->connseguimiento->query($queryCount);

		//var_dump($rr);

		//Dado el total, contra el número de páginas
		$totalPaginas = 0;
		$counter = 0;
		if ($rr->num_rows > 0)
		{
			$result = array();
			if ($row = $rr->fetch_assoc())
			{
				$counter = $row['Cantidad'];

				$totalPaginas = $counter / 100;
				$totalPaginas = ceil($totalPaginas); //redondear al siguiente
			}
		}

		$rst = $this->connseguimiento->query($query);

		//var_dump($rst);

		if ($rst->num_rows > 0)
		{
			$resultado = array();
			while ($row = $rst->fetch_assoc())
			{
				$row['pedido'] = utf8_encode($row['pedido']);
				$row['tecnicopremisas'] = utf8_encode($row['tecnicopremisas']);
				$row['tecnicoinfraestructura'] = utf8_encode($row['tecnicoinfraestructura']);
				$row['municipio'] = utf8_encode($row['municipio']);
				$row['contrato'] = utf8_encode($row['contrato']);
				$row['gestion'] = utf8_encode($row['gestion']);
				$row['quiensolicitavisita'] = utf8_encode($row['quiensolicitavisita']);
				$row['notas'] = utf8_encode($row['notas']);
				$row['grupo'] = utf8_encode($row['grupo']);
				//var_dump($row);
				$resultado[] = $row;
			}


			$this->response($this->json(array(

				'data' => $resultado,
				'contador' => $counter,
				'totalPaginas' => $totalPaginas,

			)), 201);

		}
		else
		{
			//$error = "Solicitud incorrecta ";
			$this->response($this->json("Error"), 400);
		}
	}

	//==============================================================
	//  Funcion para solicitar el GRUPO de las visitas en conjutno
	//==============================================================

	private function GrupoVisitasEnConjunto() {

		if ($this->get_request_method() != "GET") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$query = "SELECT nota FROM Notas WHERE nota <> 'mal codigo' ORDER BY nota ASC";

		$rst = $this->connseguimiento->query($query);

		if ($rst->num_rows > 0) {

			$resultado = array();
			while ($row = $rst->fetch_assoc()) {

				$row['nota'] = utf8_encode($row['nota']);

				$resultado[] = $row;

			}
			$this->response($this->json(array($resultado)), 201);
		} else {
			$this->response($this->json($error), 400);
		}
	}

	// =============================================================
	//      Funcion para guardar las visitas en conjunto
	// =============================================================

	private function infoVisitasEnConjunto() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);
		$datos = $params['datosEdicion'];

		echo json_encode($datos);
		$key = $datos['id'];
		$pedido = $datos['pedido'];
		$tecnicopremisas = utf8_decode($datos['tecnicopremisas']);
		$tecnicoinfraestructura = utf8_decode($datos['tecnicoinfraestructura']);
		$fechavisita = $datos['fechavisita'];
		$region = $datos['region'];
		$municipio = $datos['municipio'];
		$contrato = $datos['contrato'];
		$quiensolicitavisita = utf8_decode($datos['quiensolicitavisita']);
		$gestion = utf8_decode($datos['gestion']);
		$notas = utf8_decode($datos['notas']);
		$grupo = $datos['grupo'];
		$fechasolicitud = date("Y-m-d H:i:s");
		$fechafingestion = $datos['fechafingestion'];

		//echo json_encode($datos);
		if (isset($datos['id'])) {

			$sqlUpdate = ("
				UPDATE visitasenconjunto v
				SET v.pedido 					= TRIM('$pedido'),
					v.tecnicopremisas			= LOWER(TRIM('$tecnicopremisas')),
					v.tecnicoinfraestructura  	= LOWER(TRIM('$tecnicoinfraestructura')),
					v.fechavisita			    = TRIM('$fechavisita'),
					v.region 					= TRIM('$region'),
					v.municipio 				= TRIM('$municipio'),
					v.contrato 					= TRIM('$contrato'),
					v.gestion 					= TRIM('$gestion'),
					v.quiensolicitavisita		= LOWER(TRIM('$quiensolicitavisita')),
					v.notas 					= TRIM('$notas'),
					v.grupo 					= TRIM('$grupo'),
					v.fechafingestion			= TRIM('$fechafingestion')

				WHERE v.id = $key
			");

			$this->connseguimiento->query($sqlUpdate);

		}
		else {
				$sqlInsertar = ("
					INSERT INTO visitasenconjunto
					(pedido, tecnicopremisas, tecnicoinfraestructura , fechavisita, region, municipio, contrato, gestion, quiensolicitavisita, notas, grupo, fechasolicitud, fechafingestion)
					VALUES
					(TRIM('$pedido'), LOWER(TRIM('$tecnicopremisas')), LOWER(TRIM('$tecnicoinfraestructura')), TRIM('$fechavisita'), TRIM('$region'), TRIM('$municipio'),
						TRIM('$contrato'), TRIM('$gestion'), LOWER(TRIM('$quiensolicitavisita')), TRIM('$notas'), TRIM('$grupo'), '$fechasolicitud', TRIM('$fechafingestion'))
				");

			$this->connseguimiento->query($sqlInsertar);

			/*==========OPCION 1=============*/
			// if (is_numeric($rst) OR $rst === true) {
			// 	$this->response($this->json('Pedido actualizado'), 201);
			// } else {
			// 	$this->response($this->json("Error"), 400);
			// }
		}
		//echo json_encode($respuesta);
	}

	// ====================================================
	//       Funcion para exportar en CSV Escalamiento
	// ====================================================

	private function csvVisitasEnConjuntoExp() {
		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$usuarioid = $params['datosLogin'];
		$usuarioid = $usuarioid['LOGIN'];
		$datos = $params['datos'];
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

		$query = ("	SELECT 	v.pedido, v.tecnicopremisas, v.tecnicoinfraestructura, v.fechavisita, v.region, v.municipio, v.contrato, v.gestion, v.quiensolicitavisita, v.notas, v.grupo, v.fechasolicitud, v.fechafingestion
						FROM visitasenconjunto v
						WHERE 1=1
						AND v.fechasolicitud BETWEEN ('$fechaini 00:00:00') AND ('$fechafin 23:59:59')
				");

		$queryCount = ("	SELECT COUNT(pedido) AS Cantidad FROM visitasenconjunto
								WHERE fechasolicitud BETWEEN ('$fechaini 00:00:00') AND ('$fechafin 23:59:59')

						");

		$rr = $this->connseguimiento->query($queryCount);
		$counter = 0;
		if ($rr->num_rows > 0) {
			$result = array();
			if ($row = $rr->fetch_assoc()) {
				$counter = $row['Cantidad'];
			}
		}

		$rst = $this->connseguimiento->query($query) or die($this->connseguimiento->error . __LINE__);

		if ($rst->num_rows > 0) {

			$result = array();
			$fp = fopen("../tmp/$filename", 'w');

			$columnas = array(
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
			);

			fputcsv($fp, $columnas);

			while ($row = $rst->fetch_assoc()) {

				$row['pedido'] = utf8_decode($row['pedido']);
				$row['tecnicopremisas'] = utf8_decode($row['tecnicopremisas']);
				$row['tecnicoinfraestructura'] = utf8_decode($row['tecnicoinfraestructura']);
				$row['region'] = utf8_decode($row['region']);
				$row['municipio'] = utf8_decode($row['municipio']);
				$row['contrato'] = utf8_decode($row['contrato']);
				$row['gestion'] = utf8_decode($row['gestion']);
				$row['quiensolicitavisita'] = utf8_decode($row['quiensolicitavisita']);
				$row['notas'] = utf8_decode($row['notas']);
				$row['grupo'] = utf8_decode($row['grupo']);

				fputcsv($fp, $row);
			}

			fclose($fp);

			$this->response($this->json(array($filename, $counter)), 200);
		}
		$this->response('', 203);
	}

	//=========================================================
	//  FUNCION PARA SUBIR LAS REGIONES VISITAS EN CONJUNTO
	//=========================================================

	private function RegionesVisConjunto() {

		if ($this->get_request_method() != "GET") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$query = " 	SELECT region
					FROM regiones
					ORDER BY region ASC ";

		$rst = $this->connseguimiento->query($query);

		if ($rst->num_rows > 0) {

			$resultado = array();
			while ($row = $rst->fetch_assoc()) {

				$row['region'] = utf8_encode($row['region']);

				$resultado[] = $row;

			}

			$this->response($this->json(array($resultado)), 201);

		} else {
			$this->response($this->json($error), 400);
		} // If no records "No Content" status
	}

	//=========================================================
	//  FUNCION PARA SUBIR LOS MUNICIPIOS VISITAS EN CONJUNTO
	//=========================================================

	private function MunicipiosVisConjunto() {

		if ($this->get_request_method() != "GET") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$region = $this->_request['region'];
		//echo json_encode($region);

		$query = " 	SELECT municipio
					FROM municipios m
					INNER JOIN regiones r ON m.codigoRg=r.codigoRg
					WHERE region ='$region'
					ORDER BY municipio ASC  ";

		$rst = $this->connseguimiento->query($query);

		if ($rst->num_rows > 0) {

			$resultado = array();
			while ($row = $rst->fetch_assoc()) {

				$row['municipio'] = utf8_encode($row['municipio']);

				$resultado[] = $row;

				//echo json_encode($resultado);
			}

			$this->response($this->json(array($resultado)), 201);

		} else {
			$this->response($this->json($error), 400);
		}
	}

	//======================================================================
	//  FUNCION PARA SUBIR EL MUNICIPIO VISITAS EN CONJUNTO PARA EL UPDATE
	//======================================================================

	private function MunicipioVisConjuntoUpdate() {

		if ($this->get_request_method() != "GET") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$idregistro = $this->_request['idregistro'];
		//echo json_encode($region);

		$query = " 	SELECT municipio
					FROM visitasenconjunto v
					WHERE id = '$idregistro' ";

		$rst = $this->connseguimiento->query($query);

		if ($rst->num_rows > 0) {

			$resultado = array();
			while ($row = $rst->fetch_assoc()) {

				$row['municipio'] = utf8_encode($row['municipio']);
				$resultado[] = $row;

				//echo json_encode($resultado);
			}

			$this->response($this->json(array($resultado)), 201);

		} else {
			$this->response($this->json($error), 400);
		}
	}

	/*FIN DEL BLOQUE VISTAS EN CONJUNTO*/



	//=============================================================
	/*FIN DEL BLOQUE ESCALAMIENTO PREMISAS*/
	//=============================================================

	//=============================================================
	/* INICIO FUNCIONES PARA QUEJASGO */
	//=============================================================

	private function extraeQuejasGoDia() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);
		$pagina = $params['page'];
		$datos = $params['datos'];
		$fechaini = $datos['fechaini'];
		$fechafin = $datos['fechafin'];
		$columnaBusqueda = $datos['columnaBusqueda'];
		$valorBusqueda = $datos ['valorBusqueda'];


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


		if ($columnaBusqueda == "" || $valorBusqueda == "") {

				$query = ("	SELECT id, pedido, cliente, cedtecnico, tecnico, accion, asesor, fecha, duracion, region, idllamada, observacion
							FROM quejasgo
							WHERE 1=1
							AND fecha BETWEEN ('$fechaini 00:00:00') AND ('$fechafin 23:59:59') 
							ORDER BY fecha DESC
							limit 100 offset $pagina
						");

		} else{

				$query = ("	SELECT id, pedido, cliente, cedtecnico, tecnico, accion, asesor, fecha, duracion, region, idllamada, observacion
								FROM quejasgo
									WHERE 1=1
									AND fecha BETWEEN ('$fechaini 00:00:00') AND ('$fechafin 23:59:59') AND $columnaBusqueda = '$valorBusqueda'
									ORDER BY fecha DESC
									limit 100 offset $pagina
						");

		}

		$queryCount = ("	SELECT COUNT(*) AS Cantidad FROM quejasgo
								WHERE 1=1
								AND fecha BETWEEN ('$fechaini 00:00:00') AND ('$fechafin 23:59:59') 

					");

		$rr = $this->connseguimiento->query($queryCount);

		//Dado el total, contra el numumero de paginas
		$totalPaginas = 0;
		$counter = 0;
		if ($rr->num_rows > 0) {
			$result = array();
			if ($row = $rr->fetch_assoc()) {
				$counter = $row['Cantidad'];

				$totalPaginas = $counter / 100;
				$totalPaginas = ceil($totalPaginas); //redondear al siguiente
			}
		}

		$rst = $this->connseguimiento->query($query);

		if ($rst->num_rows > 0) {

			$resultado = array();

			while ($row = $rst->fetch_assoc()) {

				$row['id'] = utf8_encode($row['id']);
				$row['pedido'] = utf8_encode($row['pedido']);
				$row['cliente'] = utf8_encode($row['cliente']);
				$row['cedtecnico'] = utf8_encode($row['cedtecnico']);
				$row['tecnico'] = utf8_encode($row['tecnico']);
				$row['accion'] = utf8_encode($row['accion']);
				$row['asesor'] = utf8_encode($row['asesor']);
				$row['fecha'] = utf8_encode($row['fecha']);
				$row['duracion'] = utf8_encode($row['duracion']);
				$row['region'] = utf8_encode($row['region']);
				$row['idLlamada'] = utf8_encode($row['idllamada']);
				$row['observacion'] = utf8_encode($row['observacion']);

				$resultado[] = $row;

			}
			$this->response($this->json(array(

				'data' => $resultado,
				'contador' => $counter,
				'totalPaginas' => $totalPaginas,

			)), 201);

		} else {

			$this->response($this->json("Error"), 400);
		}
	}

	private function csvQuejasGo() {
		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$usuarioid = $params['datosLogin'];
		$usuarioid = $usuarioid['LOGIN'];
		$datos = $params['datos'];
		$fechaini = $datos['fechaini'];
		$fechafin = $datos['fechafin'];
		$columnaBusqueda = $datos['columnaBusqueda'];
		$valorBusqueda = $datos ['valorBusqueda'];


		if ($fechaini == "" && $fechafin == "") {
			$fechaini = date("Y") . "-" . date("m") . "-" . date("d");
			$fechafin = date("Y") . "-" . date("m") . "-" . date("d");
		}

		if ($fechaini == $fechafin) {
			$filename = "QuejasGo" . "_" . $fechaini . "_" . $usuarioid . ".csv";
		} else {
			$filename = "QuejasGo" . "_" . $fechaini . "_" . $fechafin . "_" . $usuarioid . ".csv";
		}


		if ($columnaBusqueda == "" || $valorBusqueda == "") {

				$query = ("	SELECT g.id, g.pedido, g.cliente, g.cedtecnico, g.tecnico, g.accion, g.asesor, g.fecha, g.duracion, g.region, g.idllamada, g.observacion
								FROM quejasgo g
									WHERE 1=1
									AND g.fecha BETWEEN ('$fechaini 00:00:00') AND ('$fechafin 23:59:59')
						");

		} else{

				$query = ("	SELECT g.id, g.pedido, g.cliente, g.cedtecnico, g.tecnico, g.accion, g.asesor, g.fecha, g.duracion, g.region, g.idllamada, g.observacion
								FROM quejasgo g
									WHERE 1=1
									AND g.fecha BETWEEN ('$fechaini 00:00:00') AND ('$fechafin 23:59:59') AND $columnaBusqueda = '$valorBusqueda'
						");

		}



		$queryCount = ("	SELECT COUNT(pedido) AS Cantidad FROM quejasgo g
								WHERE g.fecha BETWEEN ('$fechaini 00:00:00') AND ('$fechafin 23:59:59') 

						");

		$rr = $this->connseguimiento->query($queryCount);
		$counter = 0;
		if ($rr->num_rows > 0) {
			$result = array();
			if ($row = $rr->fetch_assoc()) {
				$counter = $row['Cantidad'];
			}
		}
		//echo $counter;

		$rst = $this->connseguimiento->query($query) or die($this->connseguimiento->error . __LINE__);

		if ($rst->num_rows > 0) {

			$result = array();
			$fp = fopen("../tmp/$filename", 'w');

			$columnas = array('CONSECUTIVO',
				'PEDIDO',
				'CLIENTE',
				'CEDULA_TECNICO',
				'TECNICO',
				'ACCION',
				'ASESOR',
				'FECHA',
				'DURACION',
				'CIUDAD',
				'ID_LLAMADA',
				'OBSERVACIONES');

			fputcsv($fp, $columnas);

			while ($row = $rst->fetch_assoc()) {

				$row['id'] = utf8_encode($row['id']);
				$row['pedido'] = utf8_encode($row['pedido']);
				$row['cliente'] = utf8_encode($row['cliente']);
				$row['cedtecnico'] = utf8_encode($row['cedtecnico']);
				$row['tecnico'] = utf8_encode($row['tecnico']);
				$row['accion'] = utf8_encode($row['accion']);
				$row['asesor'] = utf8_encode($row['asesor']);
				$row['fecha'] = utf8_encode($row['fecha']);
				$row['duracion'] = utf8_encode($row['duracion']);
				$row['region'] = utf8_encode($row['region']);
				$row['idllamada'] = utf8_encode($row['idllamada']);
				$row['observacion'] = $row['observacion'];

				fputcsv($fp, $row);
			}

			fclose($fp);

			$this->response($this->json(array($filename, $counter)), 200);
		}
		$this->response('', 203);
	}

	private function buscarTecnico() {

		if ($this->get_request_method() != "GET") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$cedula = $this->_request['cedula'];

		$query = "SELECT a.nombre, a.ciudad FROM tecnicos a WHERE 1=1 AND a.identificacion = '$cedula'";

		$rst = $this->connseguimiento->query($query);
		if ($rst->num_rows > 0) {

			$resultado = array();

			while ($row = $rst->fetch_assoc()) {

				// $row['NOMBRE'] = $row['NOMBRE'];
				$resultado[] = $row;

			}
			$this->response($this->json(array($resultado)), 201);

		} else {

			$this->response($this->json($error), 200);
		}
	}

	private function crearTecnicoQuejasGo() {

		$usuarioid = "";
		$password = "";

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		//$this->dbConnect();
		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$data = $params['crearTecnicoquejasGoSel'];
		$identificacion = $data['cedtecnico'];
		$nombre = $data['nombretecnico'];
		$ciudad = $data['region'];
		$celular =$data['celulartecnico'];
		$empresa =$data['empresa'];

		$sql = " INSERT INTO tecnicos ( " .
			" identificacion, " .
			" nombre, " .
			" ciudad, " .
			" celular, " .
			" empresa) values ( " .
			" '$identificacion', " .
			" UPPER('$nombre')," .
			" '$ciudad', " .
			" '$celular', " .
			" '$empresa')";
		//echo $sql;
		$rst = $this->connseguimiento->query($sql);
		$this->response($this->json('Usuario creado'), 201);
		//crea tecnico, activity feed
	}

	private function ciudadesQGo() {

		if ($this->get_request_method() != "GET") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$query = " 	SELECT DISTINCT ciudad 
					FROM ciudades
					ORDER BY ciudad ASC ";

		$rst = $this->connseguimiento->query($query);

		if ($rst->num_rows > 0) {

			$resultado = array();
			while ($row = $rst->fetch_assoc()) {

				$row['ciudad'] = utf8_encode($row['ciudad']);

				$resultado[] = $row;

			}

			$this->response($this->json(array($resultado)), 201);

		} else {
			$this->response($this->json($error), 400);
		} // If no records "No Content" status
	}

	private function registrarQuejaGo() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);
		$datos = $params['dataquejago'];
		$duracion = $params['duracion'];
		$login = $params['login'];
		$asesor = $login['LOGIN'];
		$pedido = $datos['pedido'];
		$cliente = $datos['cliente'];
		$cedtecnico = $datos['cedtecnico'];
		$tecnico = $datos['tecnico'];
		$accion = $datos['accion'];
		$region = $datos['region'];
		$idllamada = $datos['idllamada'];
		$observacion = utf8_decode($datos['observacion']);


			$sqlInsetar = ("
					INSERT INTO quejasgo
						(pedido, cliente, cedtecnico, tecnico, accion, asesor, fecha, duracion, region, idllamada, observacion)
					VALUES
						('$pedido', UPPER(TRIM('$cliente')), '$cedtecnico', '$tecnico', '$accion', '$asesor', NOW(), '$duracion', '$region', '$idllamada', '$observacion')
				");

			$rst = $this->connseguimiento->query($sqlInsetar);


			if (is_numeric($rst) OR $rst === true) {
				$this->response($this->json('Queja guardada'), 201);
			} else {
				$this->response($this->json("Error"), 400);
			}

			echo json_encode($respuesta);
	}

	private function ActualizarObserQuejasGo() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		//printf("Initial character set: %s\n", mysqli_character_set_name($this->connseguimiento));

		/* change character set to utf8mb4 */
		//mysqli_set_charset($this->connseguimiento, "utf8mb4");

		//printf("Current character set: %s\n", mysqli_character_set_name($this->connseguimiento));exit();

		$params = json_decode(file_get_contents('php://input'), true);
		$datos = $params['observacion'];
		//$observaciones = utf8_decode($datos['observacion']);
		$observaciones = utf8_decode($datos['observacion']);
		$idqueja = $params['idqueja'];

			$sqlUpdate = ("UPDATE quejasgo SET observacion = '$observaciones' where id = '$idqueja'");

			$rst = $this->connseguimiento->query($sqlUpdate);

			if (is_numeric($rst) OR $rst === true) {
				$this->response($this->json('Observacion actualizada'), 201);
			} else {
				$this->response($this->json("Error"), 400);
			}

			echo json_encode($respuesta);
	}

	//=============================================================
	/* FIN FUNCIONES PARA QUEJASGO */
	//=============================================================

	private function registrosComercial() {

		if ($this->get_request_method() != "GET") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$pagina = $this->_request['page'];
		$concepto = $this->_request['concepto'];
		$dato = $this->_request['dato'];
		$inicial = $this->_request['inicial'];
		$final = $this->_request['final'];
		//echo "selección".$buscar;
		//echo "dato".$usuario;

		//$today = date("Y-m-d");

		if ($pagina == "undefined") {
			$pagina = "0";
		} else {
			$pagina = $pagina - 1;
		}

		$pagina = $pagina * 100;

		if ($concepto == 'Pedido') {
			$parametro = "and a.pedido_actual = '$dato'";
		}
		if ($concepto == 'Asesor') {
			$parametro = " and a.login_asesor = '$dato'";
		}
		if ($concepto == 'Gestion') {
			$parametro = " and a.gestion = '$dato'";
		}
		if ($concepto == 'Clasificacion') {
			$parametro = " and a.clasificacion = '$dato'";
		}
		if ($concepto == 'Ciudad') {
			$parametro = " and a.ciudad = '$dato'";
		}
		if ($concepto == 'Estado') {
			$parametro = " and a.estado = '$dato'";
		}
		;

		$query = "SELECT a.ID, " .
			" a.LOGIN_ASESOR, " .
			"  a.PEDIDO_ACTUAL, " .
			"  a.PEDIDO_NUEVO, a.CIUDAD, " .
			"  a.GESTION, a.CLASIFICACION, a.ESTADO, " .
			"  a.OBSERVACIONES, a.FECHA_CARGA " .
			" FROM registros_comercial a " .
			"	where 1=1 " .
			" 	$parametro " .
			" and a.FECHA_CARGA BETWEEN ('$inicial 00:00:00') AND ('$final 23:59:59') " .
			" order by a.FECHA_CARGA DESC " .
			" limit 100 offset $pagina ";

		$queryCount = " select count(*) as Cantidad from registros_comercial h " .
			" where 1=1 " .
			" $parametro ";
		//echo $queryCount;
		$rr = $this->connseguimiento->query($queryCount);
		$counter = 0;
		if ($rr->num_rows > 0) {
			$result = array();
			if ($row = $rr->fetch_assoc()) {
				$counter = $row['Cantidad'];
			}
		}

		$rst = $this->connseguimiento->query($query);

		//echo $this->mysqli->query($sqlLogin);
		//
		if ($rst->num_rows > 0) {

			$resultado = array();

			while ($row = $rst->fetch_assoc()) {

				$row['OBSERVACIONES'] = utf8_encode($row['OBSERVACIONES']);
				$resultado[] = $row;

			}
			$this->response($this->json(array($resultado, $counter)), 201);

		} else {
			$this->response($this->json($error), 400);
		}
	}

	private function CrearpedidoComercial() {

		$usuarioid = "";
		$password = "";

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		//$this->dbConnect();
		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$login = $params['datosLogin'];
		$user = $login['LOGIN'];
		$crearpedido = $params['datospedidoComercial'];
		$ciudad = $crearpedido['CIUDAD'];
		$estado = $crearpedido['ESTADO'];
		$gestion = $crearpedido['GESTION'];
		$observaciones = $crearpedido['OBSERVACIONES'];
		$pedido_actual = $crearpedido['PEDIDO_ACTUAL'];
		$pedido_nuevo = $crearpedido['PEDIDO_NUEVO'];
		$clasificacion = $crearpedido['CLASIFICACION'];

		$sql = " INSERT INTO registros_comercial ( " .
			" LOGIN_ASESOR, " .
			" PEDIDO_ACTUAL, " .
			" PEDIDO_NUEVO, " .
			" CIUDAD, " .
			" GESTION, CLASIFICACION, ESTADO, OBSERVACIONES) values ( " .
			" '$user', " .
			" '$pedido_actual', " .
			" '$pedido_nuevo', " .
			" '$ciudad', " .
			" '$gestion', '$clasificacion', '$estado', '$observaciones')";
		//echo $sql;
		$rst = $this->connseguimiento->query($sql);

		$this->response($this->json('Usuario creado'), 201);
		//crear pedido comercial, activity feed
	}

	private function Accionesoffline() {

		if ($this->get_request_method() != "GET") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$producto = $this->_request['producto'];

		$query = " SELECT DISTINCT ACCION" .
			" FROM accionesoffline " .
			" WHERE producto = '$producto' " .
			" ORDER BY ACCION ASC ";

		$rst = $this->connseguimiento->query($query);
		if ($rst->num_rows > 0) {

			$resultado = array();
			while ($row = $rst->fetch_assoc()) {
				$resultado[] = $row;
			}

			$this->response($this->json(array($resultado)), 201);

		} else {
			$error = array();

			$this->response($this->json($error), 400);
		} // If no records "No Content" status
	}

	private function CrearpedidoOffline() {

		$usuarioid = "";
		$password = "";

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		//$this->dbConnect();
		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$login = $params['datosLogin'];
		$user = $login['LOGIN'];
		$crearpedido = $params['datospedidoOffline'];
		$login_asesor = $crearpedido['LOGIN_ASESOR'];
		$pedido = $crearpedido['PEDIDO'];
		$proceso = $crearpedido['PROCESO'];
		$producto = $crearpedido['PRODUCTO'];
		$accion = $crearpedido['ACCION'];
		$actividad = $crearpedido['ACTIVIDAD'];
		$actividad2 = $crearpedido['ACTIVIDAD2'];
		$observaciones = $crearpedido['OBSERVACIONES'];
		$observaciones = str_replace("\n", "/", $observaciones);
		$sql = " INSERT INTO registros_offline ( " .
			" LOGIN_ASESOR_OFF, " .
			" LOGIN_ASESOR, " .
			" PEDIDO, " .
			" PROCESO, " .
			" PRODUCTO, ACCION, ACTIVIDAD, ACTIVIDAD2, OBSERVACIONES) values ( " .
			" '$user', " .
			" '$login_asesor', " .
			" '$pedido', " .
			" '$proceso', " .
			" '$producto', '$accion', '$actividad', '$actividad2', '$observaciones')";
		//echo $sql;

		$rst = $this->connseguimiento->query($sql);

		$this->response($this->json('Usuario creado'), 201);
		//crear pedido offline, activity feed
	}

	private function ingresarPedidoAsesor() {

		$usuarioid = "";
		$password = "";

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		//$this->dbConnect();
		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$login = $params['datosLogin'];
		$idcambioequipo = $params['idcambioequipo'];
		$duracion_llamada = $params['duracion_llamada'];
		$crearpedido = $params['datospedido'];
		$user = $login['LOGIN'];
		/* $plantilla = $params['plantilla'];
		$datosClick = $params['datosClick'];
		$id_llamada = $crearpedido['id_llamada'];
		$proceso = $crearpedido['proceso'];
		$accion = $crearpedido['accion'];
		$subaccion = $crearpedido['subAccion'];
		$observaciones = $crearpedido['observaciones'];
		$cod_familiar = $crearpedido['cod_familiar'];
		$prueba_integra = $crearpedido['prueba_integra'];
		$telefonia_tdm = $crearpedido['telefonia_tdm'];
		$telev_hfc = $crearpedido['telev_hfc'];
		$iptv = $crearpedido['iptv'];
		$internet = $crearpedido['internet'];
		$toip = $crearpedido['toip'];
		$smartPlay = $crearpedido['smartPlay'];
		$observaciones = $crearpedido['observaciones'];
		$observaciones = str_replace("\n", "/", $observaciones);
		$observaciones = str_replace("'", " ", $observaciones);
		$pruebaSMNET = $crearpedido['pruebaSMNET'];
		$UNESourceSystem = $crearpedido['UNESourceSystem'];
		$codigo = $crearpedido['pendiente'];
		$tipointeraccion = $crearpedido['interaccion'];
		$diagnostico = $crearpedido['diagnostico']; */

		$plantilla = (isset($params['plantilla'])) ? $params['plantilla'] : '';
		$datosClick = (isset($params['datosClick'])) ? $params['datosClick'] : '';
		$id_llamada = (isset($crearpedido['id_llamada'])) ? $crearpedido['id_llamada'] : '';
		$proceso = (isset($crearpedido['proceso'])) ? $crearpedido['proceso'] : '';
		$accion = (isset($crearpedido['accion'])) ? $crearpedido['accion'] : '';
		$subaccion = (isset($crearpedido['subAccion'])) ? $crearpedido['subAccion'] : '';
		$observaciones = (isset($crearpedido['observaciones'])) ? $crearpedido['observaciones'] : '';
		$cod_familiar = (isset($crearpedido['cod_familiar'])) ? $crearpedido['cod_familiar'] : '';
		$prueba_integra = (isset($crearpedido['prueba_integra'])) ? $crearpedido['prueba_integra'] : '';
		$telefonia_tdm = (isset($crearpedido['telefonia_tdm'])) ? $crearpedido['telefonia_tdm'] : '';
		$telev_hfc = (isset($crearpedido['telev_hfc'])) ? $crearpedido['telev_hfc'] : '';
		$iptv = (isset($crearpedido['iptv'])) ? $crearpedido['iptv'] : '';
		$internet = (isset($crearpedido['internet'])) ? $crearpedido['internet'] : '';
		$toip = (isset($crearpedido['toip'])) ? $crearpedido['toip'] : '';
		$smartPlay = (isset($crearpedido['smartPlay'])) ? $crearpedido['smartPlay'] : '';
		$observaciones = (isset($crearpedido['observaciones'])) ? $crearpedido['observaciones'] : '';
		$observaciones = str_replace("\n", "/", $observaciones);
		$observaciones = str_replace("'", " ", $observaciones);
		$pruebaSMNET = (isset($crearpedido['pruebaSMNET'])) ? $crearpedido['pruebaSMNET'] : '';
		$UNESourceSystem = (isset($crearpedido['UNESourceSystem'])) ? $crearpedido['UNESourceSystem'] : '';
		$codigo = (isset($crearpedido['pendiente'])) ? $crearpedido['pendiente'] : '';
		$tipointeraccion = (isset($crearpedido['interaccion'])) ? $crearpedido['interaccion'] : '';
		$diagnostico = (isset($crearpedido['diagnostico'])) ? $crearpedido['diagnostico'] : '';


		$clienteContestaLlamada = (isset($crearpedido['clienteContestaLlamada'])) ? $crearpedido['clienteContestaLlamada'] : '';
		$razonNoInstalacion = (isset($crearpedido['razonNoInstalacion'])) ? $crearpedido['razonNoInstalacion'] : '';
		$tecnicoVivienda = (isset($crearpedido['tecnicoVivienda'])) ? $crearpedido['tecnicoVivienda'] : '';
		$conocimientoAgenda = (isset($crearpedido['conocimientoAgenda'])) ? $crearpedido['conocimientoAgenda'] : '';

		if ($clienteContestaLlamada != '') {
			$observaciones = '¿Técnico esta en la vivienda?: '.$tecnicoVivienda.'||¿Tenia conocimiento de la agenda?: '.$conocimientoAgenda.'||¿cliente contesta la llamada?: '.$clienteContestaLlamada.'||¿Nos podría indicar por que no se puede instalar los servicios?: '.$razonNoInstalacion.'||'.$observaciones;
		}


		if ($tipointeraccion != 'llamada') {
			$id_llamada = '';
		}


		if ($datosClick['pEDIDO_UNE'] == "" || $datosClick['pEDIDO_UNE'] == "TIMEOUT") {

			$tecnico = $crearpedido['tecnico'];
			$despacho = $crearpedido['CIUDAD'];
			$producto = $crearpedido['producto'];
			$pedido = $params['pedido'];
			$nombre_de_la_empresa = $params['empresa'];

		} else {
			if ($datosClick['uNEProvisioner'] == "EMT") {
				$nombre_de_la_empresa = "EMTELCO";
			} else if ($datosClick['uNEProvisioner'] == "RYE") {
				$nombre_de_la_empresa = "REDES Y EDIFICACIONES";
			} else if ($datosClick['uNEProvisioner'] == "EIA") {
				$nombre_de_la_empresa = "ENERGIA INTEGRAL ANDINA";
			} else {
				$nombre_de_la_empresa = $datosClick['uNEProvisioner'];
			}
			$producto = $datosClick['uNETecnologias'];
			$tecnico = $datosClick['engineerID'];
			$despacho = $datosClick['uNEMunicipio'];
			$pedido = $datosClick['pEDIDO_UNE'];
		}

		if (
			($proceso == 'Reparaciones' && $accion == 'Cambio Equipo') ||
			($proceso == 'Instalaciones' && $accion == 'Aprovisionar') ||
			($proceso == 'Instalaciones' && $accion == 'Contingencia') ||
			($proceso == 'Reparaciones' && $accion == 'Aprovisionar') ||
			($proceso == 'Reparaciones' && $accion == 'Contingencia')
		) {
			$patron = array(",", ", ");
			$patronreplace = array("|", "|");
			$macEntra = str_replace($patron, $patronreplace, trim(strtoupper($crearpedido['macEntra'])));
			$macSale = str_replace($patron, $patronreplace, trim(strtoupper($crearpedido['macSale'])));

			$sqlInsertMacs = "INSERT INTO cambio_equipos (pedido, hfc_equipo_sale, hfc_equipo_entra) VALUES ('$pedido', '$macSale', '$macEntra');";

			$rstmacs = $this->connseguimiento->query($sqlInsertMacs);
		}

		if ($proceso == 'Reparaciones') {
			$sql1 = "INSERT INTO registros (pedido, id_tecnico, empresa, asesor, observaciones, " .
				"accion,tipo_pendiente,proceso,producto,duracion,llamada_id,prueba_integrada,codigo_familiar, " .
				"smartplay,toip,inter,iptv,telev,totdm,plantilla,despacho,id_cambio_equipo,pruebaSmnet,UNESourceSystem,pendiente,diagnostico) VALUES ('$pedido', '$tecnico', '$nombre_de_la_empresa', " .
				"upper('$user'), '$observaciones', '$accion','$subaccion','$proceso','$producto', " .
				"'$duracion_llamada','$id_llamada','$prueba_integra','$cod_familiar','$smartPlay','$toip','$internet', " .
				"'$iptv','$telev_hfc','$telefonia_tdm','$plantilla','$despacho','$idcambioequipo','$pruebaSMNET','$UNESourceSystem','$codigo','$diagnostico')";
		} else {
			/* $sql1 = "INSERT INTO registros (pedido, id_tecnico, empresa, asesor, observaciones, " .
				"accion,tipo_pendiente,proceso,producto,duracion,llamada_id,plantilla,despacho,pruebaSmnet,UNESourceSystem,pendiente,diagnostico)  " .
				"VALUES ('$pedido', '$tecnico', '$nombre_de_la_empresa', upper('$user'), '$observaciones', " .
				"'$accion','$subaccion','$proceso','$producto','$duracion_llamada','$id_llamada','$plantilla','$despacho','$pruebaSMNET','$UNESourceSystem','$codigo','$diagnostico')"; */

			$sql1 = "INSERT INTO registros (pedido, id_tecnico, empresa, asesor, observaciones, accion, tipo_pendiente, proceso, producto, duracion, llamada_id, plantilla, despacho, pruebaSmnet, UNESourceSystem, pendiente, diagnostico) VALUES ('$pedido', '$tecnico', '$nombre_de_la_empresa', upper('$user'), '$observaciones', '$accion','$subaccion','$proceso','$producto','$duracion_llamada','$id_llamada','$plantilla','$despacho','$pruebaSMNET','$UNESourceSystem','$codigo','$diagnostico')";
		}
		//   	echo "insert repara: ".$sql1;

		$rst = $this->connseguimiento->query($sql1);

		$this->response($this->json('Registro ingresado'), 201);
		//ingreso registro nuevo. forma asesor, activity feed
	}

	private function registrosOffline() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		//$this->dbConnect();
		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$query = "SELECT LOGIN_ASESOR_OFF, " .
			" LOGIN_ASESOR, " .
			" PEDIDO, " .
			" PROCESO, " .
			" PRODUCTO, ACCION, ACTIVIDAD, ACTIVIDAD2, OBSERVACIONES, FECHA_CARGA " .
			"FROM registros_offline";
		//echo $query;

		$queryCount = " select count(*) as Cantidad from registros_offline h " .
			" where 1=1 ";
		//echo $queryCount;
		$rr = $this->connseguimiento->query($queryCount);
		$counter = 0;
		if ($rr->num_rows > 0) {
			$result = array();
			if ($row = $rr->fetch_assoc()) {
				$counter = $row['Cantidad'];
			}
		}

		$this->dbSeguimientoConnect();
		$rst = $this->connseguimiento->query($query);

		//echo $this->mysqli->query($sqlLogin);
		//
		if ($rst->num_rows > 0) {

			$resultado = array();

			while ($row = $rst->fetch_assoc()) {

				$row['OBSERVACIONES'] = utf8_encode($row['OBSERVACIONES']);
				$resultado[] = $row;

			}
			$this->response($this->json(array($resultado, $counter)), 201);

		} else {
			$error = array();

			$this->response($this->json($error), 400);
		}
	}

	private function cargaRegistros() {

		ini_set('max_execution_time', 1000);

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		//ini_set('display_errors', '1');
		$target_dir = "../uploads/";
		$target_file = $target_dir . basename($_FILES["fileUpload"]["name"]);
		//$name     = $_FILES['fileUpload']['name'];
		$tname = $_FILES['fileUpload']['tmp_name'];
		$type = $_FILES['fileUpload']['type'];

		$login = $this->_request['user'];
		$fecha = date("Y-m-d H:i:s");
		$tname1 = basename($_FILES["fileUpload"]["name"]);
		$guardar = "";

		//$target_file = basename($_FILES["fileUpload"]["name"]);
		$uploadOk = 1;
		// Check if $uploadOk is set to 0 by an error
		if ($uploadOk == 0) {
			echo "Lo sentimos , el archivo no se ha subido.";
			// if everything is ok, try to upload file
		} else {

			if (move_uploaded_file($_FILES["fileUpload"]["tmp_name"], $target_file)) {
				echo "El archivo " . basename($_FILES["fileUpload"]["name"]) . " se ha subido";

			} else {

				echo "Ha habido un error al subir el archivo.";

			}
		}

		$tname1 = basename($_FILES["fileUpload"]["name"]);

		if ($type == 'application/vnd.ms-excel') {
			// Extension excel 97
			$ext = 'xls';
		} else if ($type == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
			// Extension excel 2007 y 2010
			$ext = 'xlsx';
		} else {
			// Extension no valida
			echo -1;
			exit();
		}

		$xlsx = 'Excel2007';
		$xls = 'Excel5';

		$objPHPExcel2 = PHPExcel_IOFactory::load($target_file);
		$Total_Sheet = $objPHPExcel2->getSheetCount();
		$Sheet = $objPHPExcel2->getSheet(0)->toArray(null, true, true, true);

		foreach ($Sheet as $key => $value) {
			if ($key == 1) {
				continue;
			}

			$pedido = trim($value['A']);
			$id_tecnico = trim($value['B']);
			$empresa = trim($value['C']);
			$despacho = trim($value['D']);
			$observaciones = trim($value['E']);
			$accion = trim($value['F']);
			$sub_accion = trim($value['G']);
			$proceso = trim($value['H']);

			$sql = "INSERT INTO registros " .
				"(pedido, id_tecnico, empresa, asesor, despacho, observaciones, " .
				"accion, tipo_pendiente, fecha, proceso) " .
				"VALUES " .
				"('$pedido','$id_tecnico','$empresa','CARGAMASIVA', " .
				"'$despacho','$observaciones','$accion', " .
				"'$sub_accion', NOW(),'$proceso'); ";

			$rst = $this->connseguimiento->query($sql);
		}
		die();

		/* //creando el lector
		$objReader = PHPExcel_IOFactory::createReader($$ext);

		//cargamos el archivo
		$objPHPExcel = $objReader->load($target_file);

		$dim = $objPHPExcel->getActiveSheet()->calculateWorksheetDimension();

		// list coloca en array $start y $end Lista Coloca en array $ inicio y final $
		list($start, $end) = explode(':', $dim);

		if (!preg_match('#([A-Z]+)([0-9]+)#', $start, $rslt)) {
			return false;
		}
		list($start, $start_h, $start_v) = $rslt;
		if (!preg_match('#([A-Z]+)([0-9]+)#', $end, $rslt)) {
			return false;
		}
		list($end, $end_h, $end_v) = $rslt;

		//empieza  lectura vertical
		$table = "<table  border='1'>";

		//   $truncate="TRUNCATE TABLE nps";

		//  $rrtruncate = $this->connseguimiento->query($truncate);

		for ($v = $start_v; $v <= $end_v; $v++) {
			//empieza lectura horizontal
			if ($v == 1) {
				continue;
			}

			$table .= "<tr>";

			for ($h = $start_h; ord($h) <= ord($end_h); $this->pp($h)) {
				$cellValue = $this->getCell($h . $v, $objPHPExcel);
				$table .= "<td>";
				$guardar .= " '$cellValue',";
				// echo $cellValue;
				if ($cellValue !== null) {
					$table .= $cellValue;
				}

				if ($h == "A") {
					$PEDIDO = $cellValue;
				}
				if ($h == "B") {
					$ID_TECNICO = $cellValue;
				}
				if ($h == "C") {
					$EMPRESA = $cellValue;
				}
				if ($h == "D") {
					$DESPACHO = utf8_decode($cellValue);
				}
				if ($h == "E") {
					$OBSERVACIONES = utf8_decode($cellValue);
				}
				if ($h == "F") {
					$ACCION = utf8_decode($cellValue);
				}
				if ($h == "G") {
					$SUBACCION = utf8_decode($cellValue);
				}
				if ($h == "H") {
					$PROCESO = utf8_decode($cellValue);
				}
			}

			$sql = "INSERT INTO registros " .
				"(pedido, id_tecnico, empresa, asesor, despacho, observaciones, " .
				"accion, tipo_pendiente, fecha, proceso) " .
				"VALUES " .
				"('$PEDIDO','$ID_TECNICO','$EMPRESA','CARGAMASIVA', " .
				"'$DESPACHO','$OBSERVACIONES','$ACCION', " .
				"'$SUBACCION', NOW(),'$PROCESO'); ";

			$rst = $this->connseguimiento->query($sql);
		} */
	}

	private function cargar_datos() {
		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		//ini_set('display_errors', '1');
		$target_dir = "../uploads/";
		$target_file = $target_dir . basename($_FILES["fileUpload"]["name"]);
		//$name     = $_FILES['fileUpload']['name'];
		$tname = $_FILES['fileUpload']['tmp_name'];
		$type = $_FILES['fileUpload']['type'];

		$login = $this->_request['user'];
		$tipo_carga = $this->_request['tipocarga'];

		$tname1 = basename($_FILES["fileUpload"]["name"]);

		if ($tipo_carga == "SeguiClick") {
			$sqldelete = "DELETE FROM carga_archivos WHERE tipo='SeguiClick'";
			$sqltruncate = "truncate table seguimientoClick";
			$rstdelete = $this->connseguimiento->query($sqldelete);
			$rsttruncate = $this->connseguimiento->query($sqltruncate);
		}

		$sql = "insert into carga_archivos(nombre_archivo, tipo, login) values('$tname1', '$tipo_carga', '$login')";
		$rst = $this->connseguimiento->query($sql);
		$id_carga = "0";
		$sql1 = "select max(id) as total_id from carga_archivos";
		$rr = $this->connseguimiento->query($sql1);
		$id_carga = 0;
		if ($rr->num_rows > 0) {
			$result = array();
			if ($row = $rr->fetch_assoc()) {
				$id_carga = $row['total_id'];
			}
		}

		$carga = json_decode(file_get_contents("php://input"), true);
		$fecha = '';
		$departamento = '';
		$zona = '';
		$am = '';
		$pm = '';

		//$target_file = basename($_FILES["fileUpload"]["name"]);
		$uploadOk = 1;
		// Check if $uploadOk is set to 0 by an error
		if ($uploadOk == 0) {
			echo "Lo sentimos , el archivo no se ha subido.";
			// if everything is ok, try to upload file
		} else {

			if (move_uploaded_file($_FILES["fileUpload"]["tmp_name"], $target_file)) {
				echo "El archivo " . basename($_FILES["fileUpload"]["name"]) . " se ha subido";

			} else {

				echo "Ha habido un error al subir el archivo.";

			}
		}

		$tname1 = basename($_FILES["fileUpload"]["name"]);

		if ($type == 'application/vnd.ms-excel') {
			// Extension excel 97
			$ext = 'xls';
		} else if ($type == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
			// Extension excel 2007 y 2010
			$ext = 'xlsx';
		} else {
			// Extension no valida
			echo -1;
			exit();
		}

		$xlsx = 'Excel2007';
		$xls = 'Excel5';

		//creando el lector
		$objReader = PHPExcel_IOFactory::createReader($$ext);

		//cargamos el archivo
		$objPHPExcel = $objReader->load($target_file);

		$dim = $objPHPExcel->getActiveSheet()->calculateWorksheetDimension();

		// list coloca en array $start y $end Lista Coloca en array $ inicio y final $
		list($start, $end) = explode(':', $dim);

		if (!preg_match('#([A-Z]+)([0-9]+)#', $start, $rslt)) {
			return false;
		}
		list($start, $start_h, $start_v) = $rslt;
		if (!preg_match('#([A-Z]+)([0-9]+)#', $end, $rslt)) {
			return false;
		}
		list($end, $end_h, $end_v) = $rslt;

		//empieza  lectura vertical
		$table = "<table  border='1'>";
		for ($v = $start_v; $v <= $end_v; $v++) {
			//empieza lectura horizontal
			if ($v == 1) {
				continue;
			}

			$table .= "<tr>";

			for ($h = $start_h; ord($h) <= ord($end_h); $this->pp($h)) {
				$cellValue = $this->getCell($h . $v, $objPHPExcel);
				$table .= "<td>";
				$guardar .= " '$cellValue',";
				if ($cellValue !== null) {
					$table .= $cellValue;
				}
				if ($tipo_carga == 'vistaCliente') {

					if ($h == "A") {
						$pedido_id = $cellValue;
					}
					if ($h == "B") {
						$cliente = $cellValue;
					}
					if ($h == "C") {
						$departamento = $cellValue;
					}
					if ($h == "D") {
						$ciudad = $cellValue;
					}
					if ($h == "E") {
						$direccion = $cellValue;
					}
					if ($h == "F") {
						$productos = $cellValue;
					}
					if ($h == "G") {
						$timestamp = PHPExcel_Shared_Date::ExcelToPHP($cellValue); //fecha larga
						$fecha_cita = gmdate("Y-m-d", $timestamp);
					}
					if ($h == "H") {
						$jornada_cita = $cellValue;
					}
					if ($h == "I") {
						$uen = $cellValue;
					}
				} else if ($tipo_carga == 'alarmados') {
					if ($h == "A") {
						$pedido_id = $cellValue;
					}
					if ($h == "B") {
						$departamento = $cellValue;
					}
					if ($h == "C") {
						$ciudad = $cellValue;
					}
					if ($h == "D") {
						$uen = $cellValue;
					}
					if ($h == "E") {
						$timestamp = PHPExcel_Shared_Date::ExcelToPHP($cellValue); //fecha larga
						$fecha_cita = gmdate("Y-m-d", $timestamp);
					}
				} else {
					if ($h == "A") {
						$pedido_id = $cellValue;
					}
					if ($h == "B") {
						$cliente = $cellValue;
					}
					if ($h == "C") {
						$departamento = $cellValue;
					}
					if ($h == "D") {
						$ciudad = $cellValue;
					}
					if ($h == "E") {
						$direccion = $cellValue;
					}
					if ($h == "F") {
						$productos = $cellValue;
					}
					if ($h == "G") {
						$timestamp = PHPExcel_Shared_Date::ExcelToPHP($cellValue); //fecha larga
						$fecha_cita = gmdate("Y-m-d", $timestamp);
					}
					if ($h == "H") {
						$jornada_cita = $cellValue;
					}
					if ($h == "I") {
						$uen = $cellValue;
					}
					if ($h == "J") {
						$estado = $cellValue;
					}
					if ($h == "K") {
						$razon = $cellValue;
					}
					if ($h == "L") {
						$sistema = $cellValue;
					}
					if ($h == "M") {
						$estado_final = $cellValue;
					}
				}
			}

			$no_permitidas = array("á", "é", "í", "ó", "ú", "Á", "É", "Í", "Ó", "Ú", "ñ", "À", "Ã", "Ì", "Ò", "Ù", "Ã™", "Ã ", "Ã¨", "Ã¬", "Ã²", "Ã¹", "ç", "Ç", "Ã¢", "ê", "Ã®", "Ã´", "Ã»", "Ã‚", "ÃŠ", "ÃŽ", "Ã”", "Ã›", "ü", "Ã¶", "Ã–", "Ã¯", "Ã¤", "«", "Ò", "Ã", "Ã„", "Ã‹", "Ñ");
			$permitidas = array("a", "e", "i", "o", "u", "A", "E", "I", "O", "U", "n", "N", "A", "E", "I", "O", "U", "a", "e", "i", "o", "u", "c", "C", "a", "e", "i", "o", "u", "A", "E", "I", "O", "U", "u", "o", "O", "i", "a", "e", "U", "I", "A", "E", "N");
			$ciudad = strtoupper(str_replace($no_permitidas, $permitidas, $ciudad));
			$ciudad = str_replace($no_permitidas, $permitidas, $ciudad);
			$departamento = strtoupper(str_replace($no_permitidas, $permitidas, $departamento));
			$departamento = str_replace($no_permitidas, $permitidas, $departamento);
			$estado = str_replace($no_permitidas, $permitidas, $estado);
			$sistema = str_replace($no_permitidas, $permitidas, $sistema);
			$estado_final = str_replace($no_permitidas, $permitidas, $estado_final);
			$razon = str_replace($no_permitidas, $permitidas, $razon);
			$productos = str_replace($no_permitidas, $permitidas, $productos);
			$cliente = str_replace($no_permitidas, $permitidas, $cliente);

			if ($uen == 'Hogares' || $uen == 'HG') {
				$uen = 'HG';
			} else if ($uen == 'C3' || $uen == 'C2' || $uen == 'B2B') {
				$uen = 'B2B';
			} else {
				$uen = 'Otros';
			}

			if ($tipo_carga == 'vistaCliente') {
				$sql = "insert into carga_agenda (pedido_id, cliente, " .
					"departamento, ciudad, direccion, productos, fecha_cita, jornada_cita, " .
					" uen, login, archivo_id) values('$pedido_id', '$cliente', '$departamento', " .
					"'$ciudad', '$direccion', '$productos', '$fecha_cita', '$jornada_cita', '$uen', '$login', '$id_carga');";
				$rst = $this->connseguimiento->query($sql);

			} else if ($tipo_carga == 'alarmados') {
				$sql = "insert into alarmados(id_archivo, pedido_id, " .
					"departamento, ciudad, fecha_cita, uen, " .
					"login) values('$id_carga', '$pedido_id', '$departamento', " .
					"'$ciudad', '$fecha_cita', '$uen', '$login');";
				$rst = $this->connseguimiento->query($sql);
			} else {
				$sql = "insert into seguimientoClick(pedido_id, cliente, " .
					"departamento, ciudad, direccion, uen, productos, fecha_cita, jornada_cita, " .
					"estado, razon, sistema_info, estado_final) " .
					"values('$pedido_id', '$cliente', '$departamento', " .
					"'$ciudad', '$direccion', '$uen', '$productos', " .
					"'$fecha_cita', '$jornada_cita', '$estado', " .
					"'$razon', '$sistema', '$estado_final'); ";
				$rst = $this->connseguimiento->query($sql);
			}

		}
		//cargo archivos preagenda, activity feed
	}

	private function getRegistrosCarga() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		$params = json_decode(file_get_contents('php://input'), true);

		//$this->dbConnect();
		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$query = "select a.id, a.nombre_archivo, a.tipo, a.fecha_carga, a.login,(select  " .
			"CASE WHEN a.tipo = 'alarmados' THEN (select count(c.pedido_id) " .
			"from alarmados c where a.id=c.id_archivo) " .
			"else (select count(b.pedido_id) " .
			"from carga_agenda b where a.id=b.archivo_id) " .
			"END) TOTAL_REGISTROS " .
			"FROM carga_archivos a order by fecha_carga DESC limit 10";

		// echo $query;

		$this->dbSeguimientoConnect();
		$rst = $this->connseguimiento->query($query);

		//echo $this->mysqli->query($sqlLogin);
		//
		if ($rst->num_rows > 0) {

			$resultado = array();

			while ($row = $rst->fetch_assoc()) {

				if ($row['tipo'] == "vistaCliente") {
					$row['tipo'] = "Carga Preagenda";
				} else if ($row['tipo'] == "alarmados") {
					$row['tipo'] = "Alarmados";
				}

				$resultado[] = $row;

			}
			$this->response($this->json(array($resultado)), 201);

		} else {
			$error = array();

			$this->response($this->json($error), 400);
		}
	}

	private function graficaDepartamento() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}
		//$this->dbConnect();
		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$mesenviado = $params['mes'];

		if ($mesenviado == "" || $mesenviado == undefined) {

			$query = "select max(fecha_instalacion) fecha from nps ";

			$rst = $this->connseguimiento->query($query);

			$fecha = date("Y-m-d");

			if ($rst->num_rows > 0) {
				$result = array();
				if ($row = $rst->fetch_assoc()) {
					$fecha = $row['fecha'];
				}
			}

			$dia = substr($fecha, 8, 2);
			$mes = substr($fecha, 5, 2);
			$anio = substr($fecha, 0, 4);

			$nom_mes = date('M', mktime(0, 0, 0, $mes, $dia, $anio));
			$semana = "Semana " . date('W', mktime(0, 0, 0, $mes, $dia, $anio));
			$diaSemana = date("w", mktime(0, 0, 0, $mes, $dia, $anio));

		} else {
			$nom_mes = $mesenviado;
			$semana = "Semana " . date('W', mktime(0, 0, 0, $mes, $dia, $anio));
			$diaSemana = date("w", mktime(0, 0, 0, $mes, $dia, $anio));

		}

		$query = "select gen.regional, round(((select count(respuesta)  " .
			"from nps where num_respuesta = '5' and num_pregunta = '4' and mes = gen.mes and regional = gen.regional) " .
			"-(select count(respuesta) " .
			"from nps where num_respuesta = '1' and num_pregunta = '4' and mes = gen.mes and regional = gen.regional) " .
			"-(select count(respuesta) " .
			"from nps where num_respuesta = '2' and num_pregunta = '4' and mes = gen.mes and regional = gen.regional) " .
			"-(select count(respuesta) " .
			"from nps where num_respuesta = '3' and num_pregunta = '4' and mes = gen.mes and regional = gen.regional))/ " .
			"(select count(respuesta) " .
			"from nps where num_pregunta = '4' and mes = gen.mes and regional = gen.regional)*100,2) as NPS " .
			"from nps gen  " .
			"where mes = '$nom_mes'  " .
			"group by gen.regional order by regional ASC ";

		$rst = $this->connseguimiento->query($query);

		//echo $this->mysqli->query($sqlLogin);
		//
		if ($rst->num_rows > 0) {
			$departamentos = array();
			$resultado = array();

			while ($row = $rst->fetch_assoc()) {
				$resultado[] = $row;
				$label = utf8_encode($row['regional']);
				$ressi = $row['NPS'];

				$departamentos[] = array("label" => "$label", "value" => "$ressi");
			}

			$this->response($this->json(array($departamentos)), 201);
		} else {
			$error = array();

			$this->response($this->json($error), 400);
		}
	}

	private function DepartamentosContratos() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}
		//$this->dbConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$mesenviado = $params['mes'];

		if ($mesenviado == "" || $mesenviado == undefined) {

			$query = "select max(fecha_instalacion) fecha from nps ";

			$rst = $this->connseguimiento->query($query);

			$fecha = date("Y-m-d");

			if ($rst->num_rows > 0) {
				$result = array();
				if ($row = $rst->fetch_assoc()) {
					$fecha = $row['fecha'];
				}
			}

			$dia = substr($fecha, 8, 2);
			$mes = substr($fecha, 5, 2);
			$anio = substr($fecha, 0, 4);

			$nom_mes = date('M', mktime(0, 0, 0, $mes, $dia, $anio));
			$semana = "Semana " . date('W', mktime(0, 0, 0, $mes, $dia, $anio));
			$diaSemana = date("w", mktime(0, 0, 0, $mes, $dia, $anio));

		} else {
			$nom_mes = $mesenviado;
			$semana = "Semana " . date('W', mktime(0, 0, 0, $mes, $dia, $anio));
			$diaSemana = date("w", mktime(0, 0, 0, $mes, $dia, $anio));
		}

		$sql = "select gen.regional, round((select count(num_respuesta) " .
			"from nps  " .
			"where num_respuesta = '5' and num_pregunta = '4' and contratista = 'EIA' " .
			"and regional = gen.regional and mes=gen.mes)/ " .
			"(select count(num_respuesta) from nps where num_respuesta = '5' and num_pregunta = '4'  " .
			"and regional = gen.regional and mes=gen.mes)*100, 2)as EIA,  " .
			"round((select count(num_respuesta) from nps " .
			"where num_respuesta = '5' and num_pregunta = '4' and contratista = 'Conavances' " .
			"and regional = gen.regional and mes=gen.mes)/ " .
			"(select count(num_respuesta) from nps where num_respuesta = '5' and num_pregunta = '4'  " .
			"and regional = gen.regional and mes=gen.mes)*100, 2) as Conavances,  " .
			"round((select count(num_respuesta) from nps  " .
			"where num_respuesta = '5' and num_pregunta = '4' and contratista = 'EAGLE' " .
			"and regional = gen.regional and mes=gen.mes)/ " .
			"(select count(num_respuesta) from nps where num_respuesta = '5' and num_pregunta = '4'  " .
			"and regional = gen.regional and mes=gen.mes)*100, 2) as EAGLE,  " .
			"round((select count(num_respuesta) from nps  " .
			"where num_respuesta = '5' and num_pregunta = '4' and contratista = 'EMT' " .
			"and regional = gen.regional and mes=gen.mes)/ " .
			"(select count(num_respuesta) from nps where num_respuesta = '5' and num_pregunta = '4'  " .
			"and regional = gen.regional and mes=gen.mes)*100, 2) as EMT,  " .
			"round((select count(num_respuesta) from nps  " .
			"where num_respuesta = '5' and num_pregunta = '4' and contratista = 'RYE' " .
			"and regional = gen.regional and mes=gen.mes)/ " .
			"(select count(num_respuesta) from nps where num_respuesta = '5' and num_pregunta = '4' " .
			"and regional = gen.regional and mes=gen.mes)*100, 2) as RYE, " .
			"round((select count(num_respuesta) from nps " .
			"where num_respuesta = '5' and num_pregunta = '4' and contratista = 'SERVTEK' " .
			"and regional = gen.regional and mes=gen.mes)/ " .
			"(select count(num_respuesta) from nps where num_respuesta = '5' and num_pregunta = '4'  " .
			"and regional = gen.regional and mes=gen.mes)*100, 2) as SERVTEK " .
			"from nps gen where  contratista = gen.contratista " .
			"and mes = '$nom_mes' " .
			"group by gen.regional order by regional desc ";
		//  echo $sql;
		$this->dbSeguimientoConnect();

		$dptoContrato = $this->connseguimiento->query($sql);

		$resultadodptoContrato = array();

		if ($dptoContrato->num_rows > 0) {

			$dptos = array();
			$eia = array();
			$conavances = array();
			$eagle = array();
			$emt = array();
			$rye = array();
			$servtek = array();

			while ($row = $dptoContrato->fetch_assoc()) {
				$row['regional'] = utf8_encode($row['regional']);
				$label = $row['regional'];
				$resultadodptoContrato[] = $row;
				$deptoeia = $row['EIA'];
				$deptocona = $row['Conavances'];
				$deptoeagle = $row['EAGLE'];
				$deptoemt = $row['EMT'];
				$deptorye = $row['RYE'];
				$deptoservt = $row['SERVTEK'];

				$dptos[] = array("label" => "$label");
				$eia[] = array("value" => "$deptoeia");
				$conavances[] = array("value" => "$deptocona");
				$eagle[] = array("value" => "$deptoeagle");
				$emt[] = array("value" => "$deptoemt");
				$rye[] = array("value" => "$deptorye");
				$servtek[] = array("value" => "$deptoservt");
			}

			$query = "select gen.contratista contratista, " .
				"round((select count(respuesta) " .
				"from nps  " .
				"where num_respuesta = '5' and num_pregunta = '4'  and contratista = gen.contratista  " .
				"and mes=gen.mes)/  " .
				"(select count(pregunta)   " .
				"from nps where contratista = gen.contratista and num_pregunta = '4'  " .
				"and mes=gen.mes)*100, 2) as SI  " .
				"from nps gen  " .
				"where mes= '$nom_mes' " .
				"group by gen.contratista  order by  contratista";

			$rst = $this->connseguimiento->query($query);

			//echo $this->mysqli->query($sqlLogin);
			//
			$contratos = array();

			while ($row = $rst->fetch_assoc()) {
				$label = utf8_encode($row['contratista']);
				$rescontrato = $row['SI'];

				$contratos[] = array("label" => "$label", "value" => "$rescontrato");
			}

			$this->response($this->json(array($resultadodptoContrato, $dptos, $eia, $conavances, $eagle, $emt, $rye, $servtek, $contratos)), 201);

		} else {
			$error = array();

			$this->response($this->json($error), 400);
		}
	}

	private function graficaAcumulados() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}
		//$this->dbConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$datos = $params['pregunta'];
		$pregunta = $datos['pregunta'];
		$mesenviado = $params['mes'];

		if ($mesenviado == "" || $mesenviado == undefined) {

			$query = "select max(fecha_instalacion) fecha from nps ";

			$rst = $this->connseguimiento->query($query);

			$fecha = date("Y-m-d");

			if ($rst->num_rows > 0) {
				$result = array();
				if ($row = $rst->fetch_assoc()) {
					$fecha = $row['fecha'];
				}
			}

			$dia = substr($fecha, 8, 2);
			$mes = substr($fecha, 5, 2);
			$anio = substr($fecha, 0, 4);

			$nom_mes = date('M', mktime(0, 0, 0, $mes, $dia, $anio));
			$semana = "Semana " . date('W', mktime(0, 0, 0, $mes, $dia, $anio));
			$diaSemana = date("w", mktime(0, 0, 0, $mes, $dia, $anio));

		} else {
			$nom_mes = $mesenviado;
			$semana = "Semana " . date('W', mktime(0, 0, 0, $mes, $dia, $anio));
			$diaSemana = date("w", mktime(0, 0, 0, $mes, $dia, $anio));
		}

		$query = "select gen.respuesta, count(gen.respuesta) total, " .
			"round((count(gen.respuesta)/(select count(num_pregunta)  " .
			"from nps where num_pregunta = '$pregunta' and mes = gen.mes limit 1 )) *100, 2) as porcentaje " .
			"from nps gen  " .
			"where gen.num_pregunta = '$pregunta'  " .
			"and mes = '$nom_mes' " .
			"group by gen.respuesta ";

		$rst = $this->connseguimiento->query($query);

		//echo $this->mysqli->query($sqlLogin);
		//
		if ($rst->num_rows > 0) {
			$categorias = array();
			$resultado = array();

			$total = array();
			$porcentaje = array();

			while ($row = $rst->fetch_assoc()) {
				$resultado[] = $row;
				$label = $row['respuesta'];
				$totales = $row['total'];
				$porcentajes = $row['porcentaje'];

				$categorias[] = array("label" => "$label");
				$total[] = array("value" => "$totales");
				$porcentaje[] = array("value" => "$porcentajes");
			}

		}

		$acumulado = array();
		$mes = array();
		$meta = array();

		if ($pregunta == "4") {

			$acumulado[] = array("value" => "38.7");
			$acumulado[] = array("value" => "37.9");

			$mes[] = array("label" => "Mar");
			$mes[] = array("label" => "Abr");
			$mes[] = array("label" => "May");
			$mes[] = array("label" => "Jun");
			$mes[] = array("label" => "Jul");
			$mes[] = array("label" => "Ago");
			$mes[] = array("label" => "Sep");
			$mes[] = array("label" => "Oct");
			$mes[] = array("label" => "Nov");
			$mes[] = array("label" => "Dic");

			$meta[] = array("value" => "38.70");
			$meta[] = array("value" => "37.90");
			$meta[] = array("value" => "38.50");
			$meta[] = array("value" => "41.00");
			$meta[] = array("value" => "42.00");
			$meta[] = array("value" => "49.00");
			$meta[] = array("value" => "53.00");
			$meta[] = array("value" => "57.00");
			$meta[] = array("value" => "61.00");
			$meta[] = array("value" => "65.00");
		}

		$Sqlmeses = "select distinct mes from nps";

		$rstMeses = $this->connseguimiento->query($Sqlmeses);

		if ($rstMeses->num_rows > 0) {

			while ($row = $rstMeses->fetch_assoc()) {
				$nom_mes = $row['mes'];

				if ($pregunta == "2") {
					$SqlAcumulado = "select mes, round(((select count(respuesta) " .
						"from nps where num_pregunta = '2' and num_respuesta = '5' " .
						"and mes = '$nom_mes')+ " .
						"(select count(respuesta)  " .
						"from nps where num_pregunta = '2' and num_respuesta = '4' " .
						"and mes = '$nom_mes')- " .
						"(select count(respuesta)  " .
						"from nps where num_pregunta = '2' and num_respuesta = '1'  " .
						"and mes = '$nom_mes'))/ " .
						"(select count(respuesta)  " .
						"from nps where num_pregunta = '2' and mes = '$nom_mes' " .
						")*100, 2) as NPS " .
						"from nps " .
						"where mes = '$nom_mes' " .
						"group by  mes ";
				} else if ($pregunta == "3") {
					$SqlAcumulado = "select mes, round(((select count(respuesta) " .
						"from nps where num_pregunta = '3' and num_respuesta = '1' " .
						"and mes = '$nom_mes')+ " .
						"(select count(respuesta)  " .
						"from nps where num_pregunta = '3' and num_respuesta = '2' " .
						"and mes = '$nom_mes')- " .
						"(select count(respuesta)  " .
						"from nps where num_pregunta = '3' and num_respuesta = '5'  " .
						"and mes = '$nom_mes'))/ " .
						"(select count(respuesta)  " .
						"from nps where num_pregunta = '3' and mes = '$nom_mes' " .
						")*100, 2) as NPS " .
						"from nps " .
						"where mes = '$nom_mes' " .
						"group by  mes ";

				} else {

					$SqlAcumulado = "select mes, round(((select count(num_respuesta) " .
						"from nps where num_pregunta = '$pregunta' and num_respuesta = '5' " .
						"and mes = '$nom_mes')-(select count(num_respuesta) " .
						"from nps where num_pregunta = '$pregunta' and num_respuesta = '3'  " .
						"and mes = '$nom_mes')-(select count(num_respuesta)  " .
						"from nps where num_pregunta = '$pregunta' and num_respuesta = '2'  " .
						"and mes = '$nom_mes')-(select count(num_respuesta)  " .
						"from nps where num_pregunta = '$pregunta' and num_respuesta = '1'  " .
						"and mes = '$nom_mes'))/(select count(num_respuesta)   " .
						"from nps where num_pregunta = '$pregunta' and mes = '$nom_mes' " .
						")*100, 2)  as NPS " .
						"from nps " .
						"where mes = '$nom_mes' " .
						"group by  mes ";
				}

				$rst = $this->connseguimiento->query($SqlAcumulado);

				//echo $this->mysqli->query($sqlLogin);
				//
				if ($rst->num_rows > 0) {

					if ($pregunta == "4") {
						while ($row = $rst->fetch_assoc()) {
							//	$meses=$row['mes'];
							$nps = $row['NPS'];
							$acumulado[] = array("value" => "$nps");
							//$mes[]=array("label"=>"$meses");
						}
					} else {
						while ($row = $rst->fetch_assoc()) {
							$meses = $row['mes'];
							$nps = $row['NPS'];
							$acumulado[] = array("value" => "$nps");
							$mes[] = array("label" => "$meses");
						}
					}
				}

			}
			$this->response($this->json(array($categorias, $total, $porcentaje, $acumulado, $mes, $meta)), 201);
		} else {
			$error = array();

			$this->response($this->json($error), 400);
		}
	}

	//-reparacion grafica
	private function graficaAcumuladosrepa() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}
		//$this->dbConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$datos = $params['pregunta'];
		$pregunta = $datos['pregunta'];
		$mesenviado = $params['mes'];

		if ($mesenviado == "" || $mesenviado == undefined) {

			$query = "select max(FECHA_2) fecha from npsreparaciones ";

			$rst = $this->connseguimiento->query($query);

			$fecha = date("Y-m-d");

			if ($rst->num_rows > 0) {
				$result = array();
				if ($row = $rst->fetch_assoc()) {
					$fecha = $row['fecha'];
				}
			}

			$dia = substr($fecha, 8, 2);
			$mes = substr($fecha, 5, 2);
			$anio = substr($fecha, 0, 4);

			$nom_mes = date('M', mktime(0, 0, 0, $mes, $dia, $anio));
			$semana = "Semana " . date('W', mktime(0, 0, 0, $mes, $dia, $anio));
			$diaSemana = date("w", mktime(0, 0, 0, $mes, $dia, $anio));

		} else {
			$nom_mes = $mesenviado;
			$semana = "Semana " . date('W', mktime(0, 0, 0, $mes, $dia, $anio));
			$diaSemana = date("w", mktime(0, 0, 0, $mes, $dia, $anio));
		}

		$query = "select gen.respuesta, count(gen.respuesta) total, " .
			"round((count(gen.respuesta)/(select count(pregunta)  " .
			"from npsreparaciones where num_pregunta = '$pregunta' and mes = gen.mes limit 1 )) *100, 2) as porcentaje " .
			"from npsreparaciones gen  " .
			"where gen.num_pregunta = '$pregunta'  " .
			"and mes = '$nom_mes' " .
			"group by gen.respuesta ";

		$rst = $this->connseguimiento->query($query);

		//echo $this->mysqli->query($sqlLogin);
		//
		if ($rst->num_rows > 0) {
			$categorias = array();
			$resultado = array();

			$total = array();
			$porcentaje = array();

			while ($row = $rst->fetch_assoc()) {
				$resultado[] = $row;
				$label = $row['respuesta'];
				$totales = $row['total'];
				$porcentajes = $row['porcentaje'];

				$categorias[] = array("label" => "$label");
				$total[] = array("value" => "$totales");
				$porcentaje[] = array("value" => "$porcentajes");
			}

		}

		$acumulado = array();
		$mes = array();
		$meta = array();

		if ($pregunta == "4") {

			$acumulado[] = array("value" => "12.5");
			$acumulado[] = array("value" => "7.00");

			$mes[] = array("label" => "Mar");
			$mes[] = array("label" => "Abr");
			$mes[] = array("label" => "May");
			$mes[] = array("label" => "Jun");
			$mes[] = array("label" => "Jul");
			$mes[] = array("label" => "Ago");
			$mes[] = array("label" => "Sep");
			$mes[] = array("label" => "Oct");
			$mes[] = array("label" => "Nov");
			$mes[] = array("label" => "Dic");

			$meta[] = array("value" => "12.50");
			$meta[] = array("value" => "7.00");
			$meta[] = array("value" => "13.50");
			$meta[] = array("value" => "19.60");
			$meta[] = array("value" => "20.50");
			$meta[] = array("value" => "21.40");
			$meta[] = array("value" => "22.30");
			$meta[] = array("value" => "23.20");
			$meta[] = array("value" => "24.10");
			$meta[] = array("value" => "25.00");
		}

		$Sqlmeses = "select distinct mes from npsreparaciones";

		$rstMeses = $this->connseguimiento->query($Sqlmeses);

		if ($rstMeses->num_rows > 0) {

			while ($row = $rstMeses->fetch_assoc()) {
				$nom_mes = $row['mes'];

				if ($pregunta == "2") {
					$SqlAcumulado = "select mes, round(((select count(respuesta) " .
						"from npsreparaciones where num_pregunta = '2' and num_respuesta = '5' " .
						"and mes = '$nom_mes')+ " .
						"(select count(respuesta)  " .
						"from npsreparaciones where num_pregunta = '2' and num_respuesta = '4' " .
						"and mes = '$nom_mes')- " .
						"(select count(respuesta)  " .
						"from npsreparaciones where num_pregunta = '2' and num_respuesta = '1'  " .
						"and mes = '$nom_mes'))/ " .
						"(select count(respuesta)  " .
						"from npsreparaciones where num_pregunta = '2' and mes = '$nom_mes' " .
						")*100, 2) as NPS " .
						"from npsreparaciones " .
						"where mes = '$nom_mes' " .
						"group by  mes ";
				} else if ($pregunta == "3") {
					$SqlAcumulado = "select mes, round(((select count(respuesta) " .
						"from npsreparaciones where num_pregunta = '3' and num_respuesta = '1' " .
						"and mes = '$nom_mes')+ " .
						"(select count(respuesta)  " .
						"from npsreparaciones where num_pregunta = '3' and num_respuesta = '2' " .
						"and mes = '$nom_mes')- " .
						"(select count(respuesta)  " .
						"from npsreparaciones where num_pregunta = '3' and num_respuesta = '5'  " .
						"and mes = '$nom_mes'))/ " .
						"(select count(respuesta)  " .
						"from npsreparaciones where num_pregunta = '3' and mes = '$nom_mes' " .
						")*100, 2) as NPS " .
						"from npsreparaciones " .
						"where mes = '$nom_mes' " .
						"group by  mes ";

				} else {

					$SqlAcumulado = "select mes, round(((select count(respuesta) " .
						"from npsreparaciones where num_pregunta = '$pregunta' and num_respuesta = '5' " .
						"and mes = '$nom_mes')-(select count(respuesta) " .
						"from npsreparaciones where num_pregunta = '$pregunta' and num_respuesta = '3'  " .
						"and mes = '$nom_mes')-(select count(respuesta)  " .
						"from npsreparaciones where num_pregunta = '$pregunta' and num_respuesta = '2'  " .
						"and mes = '$nom_mes')-(select count(respuesta)  " .
						"from npsreparaciones where num_pregunta = '$pregunta' and num_respuesta = '1'  " .
						"and mes = '$nom_mes'))/(select count(respuesta)   " .
						"from npsreparaciones where num_pregunta = '$pregunta' and mes = '$nom_mes' " .
						")*100, 2)  as NPS " .
						"from npsreparaciones " .
						"where mes = '$nom_mes' " .
						"group by  mes ";
				}

				$rst = $this->connseguimiento->query($SqlAcumulado);

				//echo $this->mysqli->query($sqlLogin);
				//
				if ($rst->num_rows > 0) {

					if ($pregunta == "4") {
						while ($row = $rst->fetch_assoc()) {
							//	$meses=$row['mes'];
							$nps = $row['NPS'];
							$acumulado[] = array("value" => "$nps");
							//$mes[]=array("label"=>"$meses");
						}
					} else {
						while ($row = $rst->fetch_assoc()) {
							$meses = $row['mes'];
							$nps = $row['NPS'];
							$acumulado[] = array("value" => "$nps");
							$mes[] = array("label" => "$meses");
						}
					}
				}

			}
			$this->response($this->json(array($categorias, $total, $porcentaje, $acumulado, $mes, $meta)), 201);
		} else {
			$error = array();

			$this->response($this->json($error), 400);
		}
	}

	//---------------------------------------fin reparacion grafica
	private function DemePedidoEncuesta() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		//$this->dbConnect();
		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$query = "select max(fecha_instalacion) fecha from nps ";

		$rst = $this->connseguimiento->query($query);

		$fecha = date("Y-m-d");

		if ($rst->num_rows > 0) {
			$result = array();
			if ($row = $rst->fetch_assoc()) {
				$fecha = $row['fecha'];
			}
		}

		$dia = substr($fecha, 8, 2);
		$mes = substr($fecha, 5, 2);
		$anio = substr($fecha, 0, 4);

		$nom_mes = date('M', mktime(0, 0, 0, $mes, $dia, $anio));
		$semana = "Semana " . date('W', mktime(0, 0, 0, $mes, $dia, $anio));
		$diaSemana = date("w", mktime(0, 0, 0, $mes, $dia, $anio));

		$query = "select idnps, telefono, cedula, detalle, fecha_instalacion, departamento, municipio, contratista, Interfaz, respuesta, semana " .
			"from nps " .
			"where semana = (select max(semana) from nps) " .
			"and num_pregunta = '4' " .
			"and num_respuesta in ('1','2','3')  " .
			"and gestion_dolores is null or gestion_dolores =' ' " .
			"order by fecha_instalacion ASC, respuesta ASC limit 1 ";

		// echo $query;

		$this->dbSeguimientoConnect();
		$rst = $this->connseguimiento->query($query);

		//echo $this->mysqli->query($sqlLogin);
		//
		if ($rst->num_rows > 0) {

			$resultado = array();

			while ($row = $rst->fetch_assoc()) {

				$idgestion_dolores = $row['idnps'];

				$query = "UPDATE nps SET " .
					" gestion_dolores ='1' WHERE idnps='$idgestion_dolores' ";

				//	echo $query;
				$uptdate = $this->connseguimiento->query($query);
				$row['municipio'] = utf8_encode($row['municipio']);
				$row['departamento'] = utf8_encode($row['departamento']);
				$resultado[] = $row;

			}
			$this->response($this->json(array($resultado)), 201);

		} else {
			$error = array();

			$this->response($this->json($error), 400);
		}
	}

	private function resumenSemanas() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		//$this->dbConnect();
		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$datos = $params['pregunta'];
		$pregunta = $datos['pregunta'];
		$mesenviado = $params['mes'];

		if ($mesenviado == "" || $mesenviado == undefined) {

			$query = "select max(fecha_instalacion) fecha from nps ";

			$rst = $this->connseguimiento->query($query);

			$fecha = date("Y-m-d");

			if ($rst->num_rows > 0) {
				$result = array();
				if ($row = $rst->fetch_assoc()) {
					$fecha = $row['fecha'];
				}
			}

			$dia = substr($fecha, 8, 2);
			$mes = substr($fecha, 5, 2);
			$anio = substr($fecha, 0, 4);

			$nom_mes = date('M', mktime(0, 0, 0, $mes, $dia, $anio));
			$semana = "Semana " . date('W', mktime(0, 0, 0, $mes, $dia, $anio));
			$diaSemana = date("w", mktime(0, 0, 0, $mes, $dia, $anio));

		} else {
			$nom_mes = $mesenviado;
			$semana = "Semana " . date('W', mktime(0, 0, 0, $mes, $dia, $anio));
			$diaSemana = date("w", mktime(0, 0, 0, $mes, $dia, $anio));
		}

		if ($diaSemana == 0) {
			$diaSemana = 7;
		}
		$primerDia = date("d-m-Y", mktime(0, 0, 0, $mes, $dia - $diaSemana + 1, $anio));
		$ultimoDia = date("d-m-Y", mktime(0, 0, 0, $mes, $dia + (7 - $diaSemana), $anio));

		if ($pregunta == "1" || $pregunta == "6" || $pregunta == "7" || $pregunta == "8") {

			$query = "round((select count(num_respuesta) " .
				"from nps " .
				"where num_respuesta = '1' and num_pregunta = '$pregunta' and mes = gen.mes and semana = gen.semana)/  " .
				"(select count(num_pregunta)   " .
				"from nps where semana = gen.semana and mes = gen.mes and num_pregunta = '$pregunta' limit 1 )*100, 2) as SI,   " .
				"round((select count(num_respuesta)   " .
				"from nps   " .
				"where num_respuesta = '2' and num_pregunta = '$pregunta' and mes = gen.mes and semana = gen.semana)/  " .
				"(select count(num_pregunta)   " .
				"from nps where semana = gen.semana and mes = gen.mes and num_pregunta = '$pregunta' limit 1 )*100, 2) as NO ";

		} else {

			$query = "round((select count(num_respuesta) " .
				"		from nps " .
				"        where num_respuesta = '1' and num_pregunta = '$pregunta' and mes = gen.mes and semana = gen.semana)/ " .
				"        (select count(num_pregunta) " .
				"from nps where semana = gen.semana and mes = gen.mes and num_pregunta = '$pregunta' limit 1 )*100, 2) as NO,  " .
				"round((select count(num_respuesta) " .
				"		from nps  " .
				"        where num_respuesta = '2' and num_pregunta = '$pregunta' and mes = gen.mes and semana = gen.semana)/ " .
				"        (select count(num_pregunta)  " .
				"from nps where semana = gen.semana and mes = gen.mes and num_pregunta = '$pregunta' limit 1 )*100, 2) as PROBNO, " .
				"round((select count(num_respuesta)  " .
				"		from nps  " .
				"        where num_respuesta = '3' and num_pregunta = '$pregunta' and mes = gen.mes and semana = gen.semana)/ " .
				"        (select count(num_pregunta)  " .
				"from nps where semana = gen.semana and mes = gen.mes and num_pregunta = '$pregunta' limit 1 )*100, 2) as NOSEGURO, " .
				"round((select count(num_respuesta)  " .
				"		from nps " .
				"        where num_respuesta = '4' and num_pregunta = '$pregunta' and mes = gen.mes and semana = gen.semana)/ " .
				"        (select count(num_pregunta)  " .
				"from nps where semana = gen.semana and mes = gen.mes and num_pregunta = '$pregunta' limit 1 )*100, 2) as PROBSI, " .
				"round((select count(num_respuesta)  " .
				"		from nps " .
				"        where num_respuesta = '5' and num_pregunta = '$pregunta' and mes = gen.mes and semana = gen.semana)/ " .
				"        (select count(num_pregunta)  " .
				"from nps where semana = gen.semana and mes = gen.mes and num_pregunta = '$pregunta' limit 1 )*100, 2) as SI, " .
				"round(round((select count(num_respuesta)  " .
				"from nps where num_respuesta = '5' and num_pregunta = '$pregunta' and mes = gen.mes and semana = gen.semana)-(select count(respuesta) " .
				"from nps where num_respuesta = '3' and num_pregunta = '$pregunta' and mes = gen.mes and semana = gen.semana)-(select count(respuesta) " .
				"from nps where num_respuesta = '2' and num_pregunta = '$pregunta' and mes = gen.mes and semana = gen.semana)-(select count(respuesta) " .
				"from nps where num_respuesta = '1' and num_pregunta = '$pregunta' and mes = gen.mes and semana = gen.semana))/(select count(respuesta) " .
				"from nps where num_pregunta = '$pregunta' and semana = gen.semana)*100, 2) as NPS ";

		}

		$sql = "select gen.semana, " .
			" $query " .
			"from nps gen " .
			"where mes = '$nom_mes' " .
			"group by gen.semana order by semana desc ";

		//        echo $sql;

		//  $this->dbSeguimientoConnect();
		$rst = $this->connseguimiento->query($sql);

		//echo $this->mysqli->query($sqlLogin);
		//
		if ($rst->num_rows > 0) {
			$categorias = array();
			$resultado = array();
			if ($pregunta == "1" || $pregunta == "6" || $pregunta == "7" || $pregunta == "8") {
				$si = array();
				$no = array();

				while ($row = $rst->fetch_assoc()) {
					$year = date("Y");
					$week = substr($row['semana'], 7);
					$diaInicial = date('Y-m-d', strtotime($year . 'W' . $week));
					$diaFinal = date('Y-m-d', strtotime($diaInicial . "+ 6 days"));

					$row["fechaInic"] = $diaInicial;
					$row["fechaFin"] = $diaFinal;

					$resultado[] = $row;
					$label = $row['semana'];
					$resno = $row['NO'];
					$ressi = $row['SI'];

					$categorias[] = array("label" => "$label");
					$no[] = array("value" => "$resno");
					$si[] = array("value" => "$ressi");
				}

			} else {
				$no = array();
				$probno = array();
				$noseguro = array();
				$probsi = array();
				$si = array();

				while ($row = $rst->fetch_assoc()) {

					$year = date("Y");
					$week = substr($row['semana'], 7);
					$diaInicial = date('Y-m-d', strtotime($year . 'W' . $week));
					$diaFinal = date('Y-m-d', strtotime($diaInicial . "+ 6 days"));

					$row["fechaInic"] = $diaInicial;
					$row["fechaFin"] = $diaFinal;
					$resultado[] = $row;
					$label = $row['semana'];
					$resno = $row['NO'];
					$prono = $row['PROBNO'];
					$nosegur = $row['NOSEGURO'];
					$prosi = $row['PROBSI'];
					$ressi = $row['SI'];

					$categorias[] = array("label" => "$label");
					$no[] = array("value" => "$resno");
					$probno[] = array("value" => "$prono");
					$noseguro[] = array("value" => "$nosegur");
					$probsi[] = array("value" => "$prosi");
					$si[] = array("value" => "$ressi");
				}
			}

			$sql1 = "select round(((select count(num_respuesta) " .
				"from nps where num_pregunta = '$pregunta' and num_respuesta = '5' and mes = '$nom_mes')-(select count(respuesta) " .
				"from nps where num_pregunta = '$pregunta' and num_respuesta = '3' and mes = '$nom_mes')-(select count(respuesta) " .
				"from nps where num_pregunta = '$pregunta' and num_respuesta = '2' and mes = '$nom_mes')-(select count(respuesta) " .
				"from nps where num_pregunta = '$pregunta' and num_respuesta = '1' and mes = '$nom_mes'))/(select count(respuesta) " .
				"from nps where num_pregunta = '$pregunta' and mes = '$nom_mes')*100, 2) as NPS ";
			$rr = $this->connseguimiento->query($sql1);

			$NPSAcumulado = 0;
			$result = array();
			if ($row = $rr->fetch_assoc()) {
				$NPSAcumulado = $row['NPS'];
			}

			$Query = "select gen.respuesta, count(gen.num_respuesta) total, " .
				"round((count(gen.num_respuesta)/(select count(num_pregunta) " .
				"from nps where num_pregunta = '$pregunta' and mes = gen.mes limit 1 )) *100, 2) as porcentaje " .
				"from nps gen " .
				"where gen.num_pregunta = '$pregunta' " .
				"and mes = '$nom_mes' " .
				"group by gen.num_respuesta ";

			$resumen = $this->connseguimiento->query($Query);

			$resultadorespuestas = array();

			while ($row = $resumen->fetch_assoc()) {
				$resultadorespuestas[] = $row;
			}

			if ($pregunta == "1" || $pregunta == "6" || $pregunta == "7" || $pregunta == "8") {

				$querydiario = "round((select count(num_respuesta) " .
					"from nps " .
					"where num_respuesta = '1' and num_pregunta = '$pregunta' and mes=gen.mes and fecha_instalacion = gen.fecha_instalacion)/ " .
					"(select count(num_pregunta) " .
					"from nps where num_pregunta = '$pregunta'  and mes=gen.mes " .
					"and fecha_instalacion = gen.fecha_instalacion limit 1 )*100, 2) as SI,  " .
					"round((select count(num_respuesta)  " .
					"from nps  " .
					"where num_respuesta = '2' and num_pregunta = '$pregunta' and mes=gen.mes and fecha_instalacion = gen.fecha_instalacion)/ " .
					"(select count(num_pregunta)  " .
					"from nps where num_pregunta = '$pregunta'  and mes=gen.mes " .
					"and fecha_instalacion = gen.fecha_instalacion limit 1 )*100, 2) as NO ";
			} else {

				$querydiario = "round((select count(num_respuesta) " .
					"from nps " .
					"where num_respuesta = '1' and num_pregunta = '$pregunta' and mes=gen.mes and fecha_instalacion = gen.fecha_instalacion)/ " .
					"(select count(num_pregunta) " .
					"from nps where num_pregunta = '$pregunta' and mes=gen.mes " .
					"and fecha_instalacion = gen.fecha_instalacion limit 1 )*100, 2) as NO,  " .
					"round((select count(num_respuesta)  " .
					"from nps  " .
					"where num_respuesta = '2' and num_pregunta = '$pregunta' and mes=gen.mes and fecha_instalacion = gen.fecha_instalacion)/ " .
					"(select count(num_pregunta)  " .
					"from nps where num_pregunta = '$pregunta' and mes=gen.mes " .
					"and fecha_instalacion = gen.fecha_instalacion limit 1 )*100, 2) as PROBNO,  " .
					"round((select count(num_respuesta)  " .
					"from nps  " .
					"where num_respuesta = '3' and num_pregunta = '$pregunta' and mes=gen.mes and fecha_instalacion = gen.fecha_instalacion)/ " .
					"(select count(num_pregunta)  " .
					"from nps where num_pregunta = '$pregunta' and mes=gen.mes " .
					"and fecha_instalacion = gen.fecha_instalacion limit 1 )*100, 2) as NOSEGURO, " .
					"round((select count(num_respuesta)  " .
					"from nps  " .
					"where num_respuesta = '4' and num_pregunta = '$pregunta' and mes=gen.mes and fecha_instalacion = gen.fecha_instalacion)/ " .
					"(select count(num_pregunta)  " .
					"from nps where num_pregunta = '$pregunta' and mes=gen.mes  " .
					"and fecha_instalacion = gen.fecha_instalacion limit 1 )*100, 2) as PROBSI, " .
					"round((select count(num_respuesta)  " .
					"from nps  " .
					"where num_respuesta = '5' and num_pregunta = '$pregunta' and mes=gen.mes and fecha_instalacion = gen.fecha_instalacion)/ " .
					"(select count(num_pregunta)  " .
					"from nps where num_pregunta = '$pregunta' and mes=gen.mes  " .
					"and fecha_instalacion = gen.fecha_instalacion limit 1 )*100, 2) as SI  ";
			}

			$sqlDiario = "select gen.fecha_instalacion dia, " .
				"$querydiario " .
				"from nps gen " .
				"where gen.num_pregunta = '$pregunta' " .
				"and gen.mes = '$nom_mes' " .
				"group by gen.fecha_instalacion order by gen.fecha_instalacion ";

			$diario = $this->connseguimiento->query($sqlDiario);
			//echo $sqlDiario;
			$resultadoDiario = array();

			if ($diario->num_rows > 0) {
				$dias = array();
				$resultadoDiario = array();
				if ($pregunta == "1" || $pregunta == "6" || $pregunta == "7" || $pregunta == "8") {
					$diasi = array();
					$diano = array();

					while ($row = $diario->fetch_assoc()) {
						$resultadoDiario[] = $row;
						$label = $row['dia'];
						$diaresno = $row['NO'];
						$diaressi = $row['SI'];

						$dias[] = array("label" => "$label");
						$diano[] = array("value" => "$diaresno");
						$diasi[] = array("value" => "$diaressi");
					}

				} else {
					$diano = array();
					$diaprobno = array();
					$dianoseguro = array();
					$diaprobsi = array();
					$diasi = array();

					while ($row = $diario->fetch_assoc()) {
						$resultadoDiario[] = $row;
						$label = $row['dia'];
						$diaresno = $row['NO'];
						$diaprono = $row['PROBNO'];
						$dianosegur = $row['NOSEGURO'];
						$diaprosi = $row['PROBSI'];
						$diaressi = $row['SI'];

						$dias[] = array("label" => "$label");
						$diano[] = array("value" => "$diaresno");
						$diaprobno[] = array("value" => "$diaprono");
						$dianoseguro[] = array("value" => "$dianosegur");
						$diaprobsi[] = array("value" => "$diaprosi");
						$diasi[] = array("value" => "$diaressi");
					}
				}
			}

			if ($pregunta == "1" || $pregunta == "6" || $pregunta == "7" || $pregunta == "8") {

				$querydepartamento = "round((select count(num_respuesta)  " .
					"from nps  " .
					"where num_respuesta = '1' and num_pregunta = '$pregunta'  and mes=gen.mes and regional = gen.regional)/ " .
					"(select count(num_pregunta)  " .
					"from nps where regional = gen.regional and mes=gen.mes and num_pregunta = '$pregunta'  limit 1 )*100, 2) as SI, " .
					"round((select count(num_respuesta) " .
					"from nps  " .
					"where num_respuesta = '2' and num_pregunta = '$pregunta' and mes=gen.mes and regional = gen.regional)/ " .
					"(select count(num_pregunta)  " .
					"from nps where regional = gen.regional and mes=gen.mes and num_pregunta = '$pregunta' limit 1 )*100, 2) as NO ";
			} else {

				$querydepartamento = "round((select count(num_respuesta)  " .
					"from nps  " .
					"where num_respuesta = '1' and num_pregunta = '$pregunta' and mes=gen.mes and regional = gen.regional)/ " .
					"(select count(num_pregunta)  " .
					"from nps where regional = gen.regional and mes=gen.mes and num_pregunta = '$pregunta'  limit 1 )*100, 2) as NO,  " .
					"round((select count(num_respuesta)  " .
					"from nps  " .
					"where num_respuesta = '2' and num_pregunta = '$pregunta' and mes=gen.mes  and regional = gen.regional)/ " .
					"(select count(num_pregunta)  " .
					"from nps where regional = gen.regional and mes=gen.mes and num_pregunta = '$pregunta' limit 1 )*100, 2) as PROBNO,  " .
					"round((select count(num_respuesta)  " .
					"from nps  " .
					"where num_respuesta = '3' and num_pregunta = '$pregunta' and mes=gen.mes and regional = gen.regional)/ " .
					"(select count(num_pregunta)  " .
					"from nps where regional = gen.regional and mes=gen.mes and num_pregunta = '$pregunta' limit 1 )*100, 2) as NOSEGURO,  " .
					"round((select count(num_respuesta)  " .
					"from nps " .
					"where num_respuesta = '4' and num_pregunta = '$pregunta' and mes=gen.mes and regional = gen.regional)/ " .
					"(select count(num_pregunta)  " .
					"from nps where regional = gen.regional and mes=gen.mes and num_pregunta = '$pregunta' limit 1 )*100, 2) as PROBSI, " .
					"round((select count(num_respuesta) " .
					"from nps  " .
					"where num_respuesta = '5' and num_pregunta = '$pregunta' and mes=gen.mes and regional = gen.regional)/ " .
					"(select count(num_pregunta)  " .
					"from nps where regional = gen.regional and mes=gen.mes and num_pregunta = '$pregunta' limit 1 )*100, 2) as SI, " .
					"round(((select count(num_respuesta)  " .
					"from nps where num_respuesta = '5' and num_pregunta = '$pregunta' and mes = gen.mes and regional = gen.regional) " .
					"-(select count(num_respuesta) " .
					"from nps where num_respuesta = '1' and num_pregunta = '$pregunta' and mes = gen.mes and regional = gen.regional) " .
					"-(select count(num_respuesta) " .
					"from nps where num_respuesta = '2' and num_pregunta = '$pregunta' and mes = gen.mes and regional = gen.regional) " .
					"-(select count(num_respuesta) " .
					"from nps where num_respuesta = '3' and num_pregunta = '$pregunta' and mes = gen.mes and regional = gen.regional))/ " .
					"(select count(num_respuesta) " .
					"from nps where num_pregunta = '4' and mes = gen.mes and regional = gen.regional)*100,2) as NPS ";
			}

			$sqlDepartamento = "select gen.regional regional, " .
				"$querydepartamento " .
				"from nps gen " .
				"where gen.mes = '$nom_mes' " .
				"group by gen.regional order by gen.regional ";

			$departamento = $this->connseguimiento->query($sqlDepartamento);

			$resultadoDepartamento = array();

			while ($row = $departamento->fetch_assoc()) {
				$row['regional'] = utf8_encode($row['regional']);
				$resultadoDepartamento[] = $row;
			}

			if ($pregunta == "1" || $pregunta == "6" || $pregunta == "7" || $pregunta == "8") {

				$valoresSemana = "(select count(num_respuesta) " .
					"from nps  " .
					"where num_respuesta = '1' and num_pregunta = '$pregunta' and mes=gen.mes and semana = gen.semana ) as SI, " .
					"(select count(num_respuesta) from nps  " .
					"where num_respuesta = '2' and num_pregunta = '$pregunta' and mes=gen.mes and semana = gen.semana ) as NO, " .
					"(select count(num_respuesta)  " .
					"from nps where semana = gen.semana and mes=gen.mes and num_pregunta = '$pregunta' ) as TOTAL ";
			} else {

				$valoresSemana = "(select count(num_respuesta) " .
					"from nps  " .
					"where num_respuesta = '1' and num_pregunta = '$pregunta' and semana = gen.semana and mes = gen.mes) as NO,   " .
					"(select count(num_respuesta)  from nps  " .
					"where num_respuesta = '2' and num_pregunta = '$pregunta' and semana = gen.semana and mes = gen.mes) as PROBNO,  " .
					"(select count(num_respuesta)  from nps   " .
					"where num_respuesta = '3' and num_pregunta = '$pregunta' and semana = gen.semana and mes = gen.mes) as NOSEGURO,  " .
					"(select count(num_respuesta)  from nps  " .
					"where num_respuesta = '4' and num_pregunta = '$pregunta' and semana = gen.semana and mes = gen.mes) as PROBSI,  " .
					"(select count(num_respuesta)  from nps  " .
					"where num_respuesta = '5' and num_pregunta = '$pregunta' and semana = gen.semana and mes = gen.mes) as SI, " .
					"(select count(num_respuesta)  " .
					"from nps where semana = gen.semana and num_pregunta = '$pregunta' and mes = gen.mes) as TOTAL ";
			}

			$sqlValoresSemana = "select gen.semana,  " .
				"$valoresSemana " .
				"from nps gen " .
				"where gen.mes = '$nom_mes' " .
				"group by gen.semana order by gen.semana desc ";

			$ValSemana = $this->connseguimiento->query($sqlValoresSemana);

			$resultadoValSemana = array();

			while ($row = $ValSemana->fetch_assoc()) {

				$year = date("Y");
				$week = substr($row['semana'], 7);
				$diaInicial = date('Y-m-d', strtotime($year . 'W' . $week));
				$diaFinal = date('Y-m-d', strtotime($diaInicial . "+ 6 days"));

				$row["fechaInic"] = $diaInicial;
				$row["fechaFin"] = $diaFinal;

				$resultadoValSemana[] = $row;
			}

			if ($pregunta == "1" || $pregunta == "6" || $pregunta == "7" || $pregunta == "8") {

				$querycontrato = "round((select count(contratista) " .
					"from nps  " .
					"where num_respuesta = '1' and num_pregunta = '$pregunta' and mes=gen.mes and contratista = gen.contratista)/ " .
					"(select count(contratista)  " .
					"from nps where contratista = gen.contratista and mes=gen.mes and num_pregunta = '$pregunta' limit 1 )*100, 2) as SI,  " .
					"round((select count(contratista)  " .
					"from nps  " .
					"where num_respuesta = '2' and num_pregunta = '$pregunta' and mes=gen.mes and contratista = gen.contratista)/ " .
					"(select count(contratista)  " .
					"from nps where contratista = gen.contratista and mes=gen.mes and num_pregunta = '$pregunta' limit 1 )*100, 2) as NO ";
			} else {

				$querycontrato = "round((select count(contratista) " .
					"from nps  " .
					"where num_respuesta = '1' and num_pregunta = '$pregunta' and mes=gen.mes and contratista = gen.contratista)/ " .
					"(select count(contratista)  " .
					"from nps where contratista = gen.contratista and mes=gen.mes and num_pregunta = '$pregunta' limit 1 )*100, 2) as NO,  " .
					"round((select count(contratista)   " .
					"from nps " .
					"where num_respuesta = '2' and num_pregunta = '$pregunta' and mes=gen.mes and contratista = gen.contratista)/ " .
					"(select count(contratista)  " .
					"from nps where contratista = gen.contratista and mes=gen.mes and num_pregunta = '$pregunta' limit 1 )*100, 2) as PROBNO,  " .
					"round((select count(contratista)  " .
					"from nps  " .
					"where num_respuesta = '3' and num_pregunta = '$pregunta' and mes=gen.mes and contratista = gen.contratista)/ " .
					"(select count(contratista)  " .
					"from nps where contratista = gen.contratista and mes=gen.mes and num_pregunta = '$pregunta' limit 1 )*100, 2) as NOSEGURO,  " .
					"round((select count(contratista)  " .
					"from nps  " .
					"where num_respuesta = '4' and num_pregunta = '$pregunta' and mes=gen.mes and contratista = gen.contratista)/ " .
					"(select count(contratista)  " .
					"from nps where contratista = gen.contratista and mes=gen.mes and num_pregunta = '$pregunta' limit 1 )*100, 2) as PROBSI,  " .
					"round((select count(contratista)  " .
					"from nps  " .
					"where num_respuesta = '5' and num_pregunta = '$pregunta' and mes=gen.mes and contratista = gen.contratista)/ " .
					"(select count(contratista)  " .
					"from nps where contratista = gen.contratista and mes=gen.mes and num_pregunta = '$pregunta' limit 1 )*100, 2) as SI ";
			}

			$SqlContrato = "select gen.contratista contrato, " .
				"$querycontrato " .
				"from  nps gen " .
				"where gen.mes = '$nom_mes' " .
				"group by gen.contratista ";

			$contrato = $this->connseguimiento->query($SqlContrato);

			$resultadoContrato = array();

			while ($row = $contrato->fetch_assoc()) {
				$resultadoContrato[] = $row;
			}

			$this->response($this->json(array($resultado, $NPSAcumulado, $resultadorespuestas, $resultadoDiario, $resultadoDepartamento, $resultadoContrato, $categorias, $no, $probno, $noseguro, $probsi, $si, $dias, $diano, $diaprobno, $dianoseguro, $diaprobsi, $diasi, $resultadoValSemana, $nom_mes)), 201);

		} else {
			$error = array();

			$this->response($this->json($error), 400);
		}
	}

	private function garantiasInstalaciones() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		//$this->dbConnect();
		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$mes = $params['mes'];

		$sqlDeparGarantias = "select Insta.departamento_dane, count(Insta.departamento_dane) Total,  " .
			"round((select count(departamento_dane)  " .
			"from garantias_insta  " .
			"where departamento_dane = Insta.departamento_dane and mesInsta = Insta.mesInsta)/  " .
			"(select count(departamento_dane)   " .
			"from garantias_insta where mesInsta = Insta.mesInsta )*100, 1) as porcentaje " .
			"from garantias_insta Insta " .
			"where mesInsta = '$mes' " .
			"group by Insta.departamento_dane order by Insta.departamento_dane ASC ";

		$rst = $this->connseguimiento->query($sqlDeparGarantias);

		$sqlTecnicos = "select cod_funcionario,  (case when count(cod_funcionario) >= '30' then 'Mayores a 30'  " .
			"when count(cod_funcionario) >= '20' and count(cod_funcionario) < '30' then 'Entre 20-29' " .
			"when count(cod_funcionario) >= '15' and count(cod_funcionario) < '20' then 'Entre 15-19' " .
			"when count(cod_funcionario) >= '10' and count(cod_funcionario) < '15' then 'Entre 10-14' " .
			"when count(cod_funcionario) >= '0' and count(cod_funcionario) < '10' then 'Entre 0-10' " .
			"end) Total " .
			"from garantias_insta " .
			"where mesInsta = '$mes' " .
			"group by cod_funcionario  " .
			"order by count(cod_funcionario) DESC ";

		$rstTecnicos = $this->connseguimiento->query($sqlTecnicos);

		$sqlCausa = "select causa_falla, count(*) Total " .
			"from garantias_insta " .
			"where mesInsta = '$mes' " .
			"group by causa_falla  " .
			"order by count(*) DESC";

		$rstCausa = $this->connseguimiento->query($sqlCausa);

		if ($rst->num_rows > 0) {

			$resultado = array();
			$Rangostecnicos = array();
			$RangosCausas = array();
			$May30 = 0;
			$Entre20_29 = 0;
			$Entre15_19 = 0;
			$Entre10_14 = 0;
			$Entre0_10 = 0;

			while ($row = $rst->fetch_assoc()) {
				$row['departamento_dane'] = utf8_encode($row['departamento_dane']);
				$resultado[] = $row;
			}

			if ($rstTecnicos->num_rows > 0) {

				while ($row = $rstTecnicos->fetch_assoc()) {

					if ($row['Total'] == 'Mayores a 30') {
						$May30 = $May30 + 1;
					} else if ($row['Total'] == 'Entre 20-29') {
						$Entre20_29 = $Entre20_29 + 1;
					} else if ($row['Total'] == 'Entre 15-19') {
						$Entre15_19 = $Entre15_19 + 1;
					} else if ($row['Total'] == 'Entre 10-14') {
						$Entre10_14 = $Entre10_14 + 1;
					} else {
						$Entre0_10 = $Entre0_10 + 1;
					}
				}
				$Rangostecnicos[] = array("rango" => "Mayor 30", "total" => "$May30");
				$Rangostecnicos[] = array("rango" => "Entre 20-29", "total" => "$Entre20_29");
				$Rangostecnicos[] = array("rango" => "Entre 15-19", "total" => "$Entre15_19");
				$Rangostecnicos[] = array("rango" => "Entre 10-14", "total" => "$Entre10_14");
				$Rangostecnicos[] = array("rango" => "Entre 0-9", "total" => "$Entre0_10");
			}

			if ($rstCausa->num_rows > 0) {

				while ($row = $rstCausa->fetch_assoc()) {

					if ($row['Total'] >= '30') {
						$row['causa_falla'] = utf8_decode($row['causa_falla']);
						$RangosCausas[] = $row;
					}
				}
			}

			$this->response($this->json(array($resultado, $Rangostecnicos, $RangosCausas)), 201);
		} else {
			$error = array();

			$this->response($this->json($error), 400);
		}
	}

	private function listadoEstadosClick() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		//$this->dbConnect();
		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$datos = $params['listaClick'];
		$fecha = $datos['fecha'];
		$uen = $datos['uen'];
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
			$tipo_trabajo = "and tipo_trabajo = '$tipo_trabajo'";
			$tipo_trabajo1 = "and (select tipo_trabajo from carga_click where pro.pedido_id = pedido_id limit 1) = '$tipo_trabajo'";
		} else {
			$tipo_trabajo = "";
		}

		$query = "select estado_id, count(pedido_id) total_estados " .
			"from carga_click  " .
			"where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')  " .
			"$tipo_trabajo $uen " .
			"group by estado_id  " .
			"order by total_estados DESC  ";
		//echo $query;
		$rst = $this->connseguimiento->query($query);

		//echo $this->mysqli->query($sqlLogin);
		//
		if ($rst->num_rows > 0) {

			$resultado = array();

			while ($row = $rst->fetch_assoc()) {

				$resultado[] = $row;

			}

			$this->response($this->json(array($resultado)), 201);
		} else {
			$error = array();

			$this->response($this->json($error), 400);
		}
	}

	private function csvContingencias() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$usuarioid = $params['datosLogin'];
		$usuarioid = $usuarioid['LOGIN'];
		$fechaIni = $params['fechaIni'];
		$fechafin = $params['fechafin'];

		//$month = date('m',strtotime($fecha));
		//   $year = date('Y',strtotime($fecha));
		//$day = date("d", mktime(0,0,0, $month+1, 0, $year));

		//   $diaFinal= date('Y-m-d', mktime(0,0,0, $month, $day, $year));
		//$diaInicial= date('Y-m-d', mktime(0,0,0, $month, 1, $year));

		$filename = "Contingencias" . "_" . $fechaIni . "_" . $fechafin . "_" . $usuarioid . ".csv";

		$query = ("SELECT C.accion, C.ciudad, C.correo, C.macEntra, C.macSale, C.motivo, C.observacion,
					C.paquetes, C.pedido, C.proceso, C.producto, C.remite, C.tecnologia, C.tipoEquipo, C.uen,
					C.contrato, C.perfil, C.logindepacho, C.logincontingencia, C.horagestion, C.horacontingencia,
					C.observContingencia, C.acepta, C.tipificacion, C.fechaClickMarca, C.loginContingenciaPortafolio,
					C.horaContingenciaPortafolio, C.tipificacionPortafolio, C.observContingenciaPortafolio, C.generarcr 
					FROM contingencias AS C
				WHERE C.horagestion BETWEEN ('$fechaIni 00:00:00') AND ('$fechafin 23:59:59')
				AND C.accion IN ('Cambio de equipo', 'Contingencia', 'Refresh', 'Registros ToIP', 'Reenvio de registros')");

		$rst = $this->connseguimiento->query($query);

		if ($rst->num_rows > 0) {

			$result = array();
			$fp = fopen("../tmp/$filename", 'w');
			//echo $fp;

			$columnas = array('ACCION',
				'CIUDAD',
				'CORREO',
				'MAC_ENTRA',
				'MAC_SALE',
				'MOTIVO',
				'OBSERVACIONES',
				'PAQUETES',
				'PEDIDO',
				'PROCESO',
				'PRODUCTO',
				'REMITENTE',
				'TECNOLOGIA',
				'TIPO_EQUIPO',
				'UEN',
				'CONTRATO',
				'PERFIL',
				'LOGIN',
				'LOGIN_GESTION',
				'HORA_INGRESO',
				'HORA_GESTION',
				'OBSERVACIONES_GESTION',
				'ESTADO',
				'TIPIFICACION',
				'FECHACLICKMARCA',
				'LOGIN_PORTAFILO',
				'HORA_GESTION_PORTAFOLIO',
				'TIPIFICACION_PORTAFOLIO',
				'OBSERVACIONES_GESTION_PORTAFOLIO',
				'GENERAR_CR',
			);

			fputcsv($fp, $columnas);
			// $carlitos = 0;
			while ($row = $rst->fetch_assoc()) {

				$row['observacion'] = str_replace(",", ".", $row['observacion']);
				$row['observacion'] = str_replace(";", ".", $row['observacion']);
				$row['observacion'] = str_replace("\n", " ", $row['observacion']);
				$row['observContingencia'] = str_replace(",", ".", $row['observContingencia']);
				$row['observContingencia'] = str_replace(";", ".", $row['observContingencia']);
				$row['observContingencia'] = str_replace("\n", " ", $row['observContingencia']);
				$row['observContingenciaPortafolio'] = str_replace(",", ".", $row['observContingenciaPortafolio']);
				$row['observContingenciaPortafolio'] = str_replace(";", ".", $row['observContingenciaPortafolio']);
				$row['observContingenciaPortafolio'] = str_replace("\n", " ", $row['observContingenciaPortafolio']);

				fputcsv($fp, $row);
				// if ($carlitos == 0) {
				// 	var_dump($row);
				// 	$carlitos = 1;};
			}

			fclose($fp);

			$this->response($this->json(array($filename)), 200);
		}
		$this->response('', 203); // If no records "No Content" status
		//descarga archivo estados click, activity feed
	}

	private function insertData() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		//$this->dbConnect();
		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$datos = $params['lista'];
		$fecha = $datos['fecha'];
		$uen = $datos['uen'];
		$tipotrabajo = $datos['tipo_trabajo'];
		$ciudad = $datos['CIUDAD'];
		$mes = date("m", strtotime($fecha));
		$año = date("Y", strtotime($fecha));
		$sep = "";
		$ciudades = "";
		$bandera = 0;
		$bandera1 = 0;

		if ($ciudad == null) {
			$ciudad = "";
		} else {
			$total = count($ciudad);
			for ($i = 0; $i < $total; $i++) {

				if ($valida = strpos($ciudad[$i], '_DEPA') !== false) {
					$bandera = $bandera + 1;
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
		} else if ($bandera == 0 && $bandera1 > 0) {
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
			$tipo_trabajo = "and tipo_trabajo = '$tipotrabajo'";
			$tipo_trabajo1 = "and (select tipo_trabajo from carga_click where pro.pedido_id = pedido_id limit 1) = '$tipotrabajo'";
		} else {
			$tipo_trabajo = "";
			$tipo_trabajo1 = "";
		}

		//truncate table
		$query = "TRUNCATE TABLE jornada_estados";
		$rr = $this->connseguimiento->query($query);

		//insert jornadaID
		$query = "INSERT INTO jornada_estados " .
			"(`id_jornada`) VALUES ('AM'),('PM'),('HF'),('TOTAL'),('DIFERENCIA'); ";
		$rr = $this->connseguimiento->query($query);

		//carga de agendados
		$sqlcarga = "select count(pro.jornada_cita) total_jornada, " .
			"(case " .
			"when pro.jornada_cita = 'AM' then 'AM' " .
			"when pro.jornada_cita = 'PM' then 'PM' " .
			"else 'HF' " .
			"end) jornada, (select count(pro.jornada_cita) from carga_agenda pro " .
			"where pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') " .
			"$uen $tipo_trabajo1 $ciudades) TOTAL, " .

			"(select count(pro.pedido_id) " .
			"from carga_agenda pro " .
			"where pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') " .
			"and pro.pedido_id not in (select pedido_id from carga_click " .
			"where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) " .
			"$uen $tipo_trabajo1 $ciudades) DIFERENCIA " .

			"from carga_agenda pro " .
			"where pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') " .
			"$uen $tipo_trabajo1 $ciudades " .
			"group by jornada";
		//echo $sqlcarga;
		$rr = $this->connseguimiento->query($sqlcarga);

		while ($row = $rr->fetch_assoc()) {

			$total_jornada = $row['total_jornada'];
			$jornada = $row['jornada'];
			$diferencia = $row['DIFERENCIA'];
			$total_carga = $total_carga + $total_jornada;
			$sqlupdate = "UPDATE jornada_estados " .
				"SET `agendados`='$total_jornada' WHERE `id_jornada`='$jornada' ";

			$rupdate = $this->connseguimiento->query($sqlupdate);
		}$sqlupdate = "UPDATE jornada_estados " .
			"SET `agendados`='$total_carga' WHERE `id_jornada`='TOTAL' ";
		$rupdate = $this->connseguimiento->query($sqlupdate);
		$sqlupdate = "UPDATE jornada_estados " .
			"SET `agendados`='$diferencia' WHERE `id_jornada`='DIFERENCIA' ";
		$rupdate = $this->connseguimiento->query($sqlupdate);

		//carga de agendados y click
		$sqlvistaClik = "select count(pro.jornada_cita) total_jornada,   " .
			"(case when pro.jornada_cita = 'AM' then 'AM' " .
			"when pro.jornada_cita = 'PM' then 'PM' " .
			"else 'HF' end) jornada, " .
			"(select count(pro.jornada_cita) total_jornada " .
			"from carga_click pro " .
			"where pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') " .
			"and pro.pedido_id in (select pedido_id from carga_agenda " .
			"where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) " .
			"$uen $tipo_trabajo $ciudades) TOTAL, " .

			"(select count(pro.pedido_id) " .
			"from carga_click pro " .
			"where pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') " .
			"and pro.pedido_id not in (select pedido_id from carga_agenda  " .
			"where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) $uen $tipo_trabajo $ciudades) DIFERENCIA " .

			"from carga_click pro " .
			"where pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') " .
			"and pro.pedido_id in (select pedido_id from carga_agenda " .
			"where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) " .
			"$uen $tipo_trabajo $ciudades group by jornada ";
		$rrclick = $this->connseguimiento->query($sqlvistaClik);
		//echo $sqlvistaClik;
		while ($row = $rrclick->fetch_assoc()) {

			$total_jornada = $row['total_jornada'];
			$jornada = $row['jornada'];
			$diferencia = $row['DIFERENCIA'];
			$total_cargaclick = $total_cargaclick + $total_jornada;
			$sqlupdateclick = "UPDATE jornada_estados " .
				"SET `vista_click`='$total_jornada' WHERE `id_jornada`='$jornada' ";

			$updateclick = $this->connseguimiento->query($sqlupdateclick);
		}
		$sqlupdateclicktotal = "UPDATE jornada_estados " .
			"SET `vista_click`='$total_cargaclick' WHERE `id_jornada`='TOTAL' ";
		$updateclicktotal = $this->connseguimiento->query($sqlupdateclicktotal);
		$sqlupdatedif = "UPDATE jornada_estados " .
			"SET `vista_click`='$diferencia' WHERE `id_jornada`='DIFERENCIA' ";
		$rupdatedif = $this->connseguimiento->query($sqlupdatedif);

		//carga de agendados y click confirmados
		$sqlconfirmados = "select sum(a.totales) as totales, a.jornada_cita, " .
			"(select sum(b.totales) as totales from (select count(distinct reg.pedido) totales " .
			"from registros reg, carga_agenda pro   " .
			"where pro.pedido_id in (select pedido_id from carga_click " .
			"where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) " .
			"and pro.pedido_id = reg.pedido  " .
			"and accion = 'Visita confirmada' " .
			"and reg.fecha BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') " .
			"and pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') " .
			"$uen $ciudades $tipo_trabajo1)b ) " .
			"as TOTAL, " .

			"(select count(distinct pedido_id) " .
			"from carga_click pro " .
			"where pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') " .
			"and pro.pedido_id not in (select pedido_id from carga_agenda " .
			"where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) " .
			"and pro.pedido_id in (select pedido from registros where " .
			"accion = 'Visita confirmada' " .
			"and fecha BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) " .
			"and pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') " .
			"$uen $tipo_trabajo1 $ciudades) DIFERENCIA " .

			"from (select count(distinct reg.pedido) totales, " .
			"(case when pro.jornada_cita = 'AM' then 'AM' " .
			"when pro.jornada_cita = 'PM' then 'PM' " .
			"else 'HF' " .
			"end) jornada_cita " .
			"from registros reg, carga_agenda pro  " .
			"where pro.pedido_id in (select pedido_id from carga_click " .
			"where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) " .
			"and pro.pedido_id = reg.pedido  " .
			"and accion = 'Visita confirmada' " .
			"and reg.fecha BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') " .
			"and pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') " .
			"$uen $tipo_trabajo1 $ciudades" .
			"group by jornada_cita) a " .
			"group by a.jornada_cita";
		$rrconfirmados = $this->connseguimiento->query($sqlconfirmados);
		//echo $sqlconfirmados;
		while ($row = $rrconfirmados->fetch_assoc()) {

			$total_jornada = $row['totales'];
			$jornada = $row['jornada_cita'];
			$diferencia = $row['DIFERENCIA'];
			$total_cargaconfirmados = $total_cargaconfirmados + $total_jornada;
			$sqlupdatecoonfirma = "UPDATE jornada_estados " .
				"SET `confirmados`='$total_jornada' WHERE `id_jornada`='$jornada' ";

			$updateconfirma = $this->connseguimiento->query($sqlupdatecoonfirma);
		}
		$sqlupdateconfirmatotal = "UPDATE jornada_estados " .
			"SET `confirmados`='$total_cargaconfirmados' WHERE `id_jornada`='TOTAL' ";
		$updateconfirmatotal = $this->connseguimiento->query($sqlupdateconfirmatotal);
		$sqlupdatedif = "UPDATE jornada_estados " .
			"SET `confirmados`='$diferencia' WHERE `id_jornada`='DIFERENCIA' ";
		$rupdatedif = $this->connseguimiento->query($sqlupdatedif);

		//sin gestionar
		$sqlnogestion = "select count(pedido_id) pendientes, (case when jornada_cita = 'AM' then 'AM' " .
			"when jornada_cita = 'PM' then 'PM' " .
			"else 'HF' " .
			"end) jornada_cita, " .
			"(select count(pedido_id) pendientes " .
			"from carga_agenda  pro " .
			"where pedido_id not in (select pedido from registros where fecha BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) " .
			"and fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') " .
			"and pedido_id in (select pedido_id from carga_click  " .
			"where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) $uen $tipo_trabajo1 $ciudades) TOTAL, " .

			"(select count(pedido_id) " .
			"from carga_click " .
			"where pedido_id not in (select pedido from registros " .
			"where fecha BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) " .
			"and fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') " .
			"and pedido_id not in (select pedido_id from carga_agenda " .
			"where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) $uen $tipo_trabajo $ciudades) DIFERENCIA " .

			"from carga_agenda  pro " .
			"where pedido_id not in (select pedido from registros where fecha BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) " .
			"and fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') " .
			"and pedido_id in (select pedido_id from carga_click  " .
			"where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) " .
			"$uen $tipo_trabajo1 $ciudades " .
			"group by (case when jornada_cita = 'AM' then 'AM' " .
			"when jornada_cita = 'PM' then 'PM' " .
			"else 'HF' end) ";
		//echo $sqlnogestion;
		$rrnogestion = $this->connseguimiento->query($sqlnogestion);

		while ($row = $rrnogestion->fetch_assoc()) {

			$total_jornada = $row['pendientes'];
			$jornada = $row['jornada_cita'];
			$diferencia = $row['DIFERENCIA'];
			$total_carganogestion = $total_carganogestion + $total_jornada;
			$sqlupdatenogestion = "UPDATE jornada_estados " .
				"SET `no_gestionados`='$total_jornada' WHERE `id_jornada`='$jornada' ";

			$updatenogestion = $this->connseguimiento->query($sqlupdatenogestion);
		}
		$sqlupdatenogestiontotal = "UPDATE jornada_estados " .
			"SET `no_gestionados`='$total_carganogestion' WHERE `id_jornada`='TOTAL' ";
		$updatenogestiontotal = $this->connseguimiento->query($sqlupdatenogestiontotal);
		$sqlupdatedif = "UPDATE jornada_estados " .
			"SET `no_gestionados`='$diferencia' WHERE `id_jornada`='DIFERENCIA' ";
		$rupdatedif = $this->connseguimiento->query($sqlupdatedif);

		//finalizados de click
		$sqlfinalizadosclick = "select count(pro.jornada_cita) total_jornada, " .
			"(case when pro.jornada_cita = 'AM' then 'AM'  " .
			"when pro.jornada_cita = 'PM' then 'PM' " .
			"else 'HF' end) jornada,  " .
			"((select count(pro.jornada_cita) " .
			"from carga_click pro  " .
			"where pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') " .
			"and pedido_id in (select pedido_id from carga_agenda  " .
			"where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) " .
			"and estado_id = 'Finalizada') $uen $tipo_trabajo $ciudades) TOTAL, " .

			"(select count(pedido_id) " .
			"from carga_click pro " .
			"where pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') " .
			"and pro.pedido_id not in (select pedido_id from carga_agenda " .
			"where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) " .
			"and pro.estado_id = 'Finalizada' $uen $tipo_trabajo $ciudades) DIFERENCIA " .

			"from carga_click pro  " .
			"where pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') " .
			"and pedido_id in (select pedido_id from carga_agenda " .
			"where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) " .
			"and estado_id = 'Finalizada' $uen $tipo_trabajo $ciudades group by jornada " .
			"order by (case when pro.jornada_cita = 'AM' then 'AM' " .
			"when pro.jornada_cita = 'PM' then 'PM' else 'HF' end) ";
		$rrclickfinalizados = $this->connseguimiento->query($sqlfinalizadosclick);
		//echo $sqlvistaClik;
		while ($row = $rrclickfinalizados->fetch_assoc()) {

			$total_jornada = $row['total_jornada'];
			$jornada = $row['jornada'];
			$diferencia = $row['DIFERENCIA'];
			$total_cargafinclick = $total_cargafinclick + $total_jornada;
			$sqlupdatefinalclick = "UPDATE jornada_estados " .
				"SET `finalizados_click`='$total_jornada' WHERE `id_jornada`='$jornada' ";

			$updatefinalclick = $this->connseguimiento->query($sqlupdatefinalclick);
		}
		$sqlupdatefinalclicktotal = "UPDATE jornada_estados " .
			"SET `finalizados_click`='$total_cargafinclick' WHERE `id_jornada`='TOTAL' ";
		$updatefinalclicktotal = $this->connseguimiento->query($sqlupdatefinalclicktotal);
		$sqlupdatedif = "UPDATE jornada_estados " .
			"SET `finalizados_click`='$diferencia' WHERE `id_jornada`='DIFERENCIA' ";
		$rupdatedif = $this->connseguimiento->query($sqlupdatedif);

		//Sin confirmar de click
		$sqlSinConfirmar = "select count(pro.jornada_cita) total_jornada,   " .
			"(case when pro.jornada_cita = 'AM' then 'AM'   " .
			"when pro.jornada_cita = 'PM' then 'PM'  " .
			"else 'HF' end) jornada,  " .
			"(select count(pro.jornada_cita) total_jornada    " .
			"from carga_click pro  " .
			"where pro.fecha_cita BETWEEN ('$fecha 00:00:00')  " .
			"AND ('$fecha 23:59:59')  " .
			"and pro.pedido_id in (select pedido_id from carga_agenda  " .
			"where fecha_cita BETWEEN ('$fecha 00:00:00')  " .
			"AND ('$fecha 23:59:59'))  " .
			"and pro.pedido_id not in  " .
			"(select reg.pedido " .
			"from registros reg, carga_agenda pro  " .
			"where pro.pedido_id in (select pedido_id from carga_click " .
			"where fecha_cita BETWEEN ('$fecha 00:00:00') " .
			"AND ('$fecha 23:59:59')) " .
			"and pro.pedido_id = reg.pedido  " .
			"and accion = 'Visita confirmada' " .
			"and reg.fecha BETWEEN ('$fecha 00:00:00') " .
			"AND ('$fecha 23:59:59') " .
			"and pro.fecha_cita BETWEEN ('$fecha 00:00:00') " .
			"AND ('$fecha 23:59:59')) " .
			"and pro.pedido_id not in " .
			"(select pedido_id	" .
			"from carga_agenda " .
			"where pedido_id not in " .
			"(select pedido from registros where fecha BETWEEN ('$fecha 00:00:00') " .
			"AND ('$fecha 23:59:59')) " .
			"and fecha_cita BETWEEN ('$fecha 00:00:00')  " .
			"AND ('$fecha 23:59:59') " .
			"and pedido_id in (select pedido_id from carga_click " .
			"where fecha_cita BETWEEN ('$fecha 00:00:00') " .
			"AND ('$fecha 23:59:59'))) $uen $tipo_trabajo $ciudades) as TOTAL, " .
			"(select count(pro.pedido_id) " .
			"from carga_click pro  " .
			"where pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') " .
			"and pro.pedido_id not in (select pedido_id from carga_agenda  " .
			"where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59'))  " .
			"and pro.pedido_id not in " .
			"(select pedido_id " .
			"from carga_click pro  " .
			"where pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')  " .
			"and pro.pedido_id not in (select pedido_id from carga_agenda " .
			"where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) " .
			"and pro.pedido_id in (select pedido from registros where " .
			"accion = 'Visita confirmada' " .
			"and fecha BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) " .
			"and pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) " .
			"and pro.pedido_id not in " .
			"(select pedido_id " .
			"from carga_click  " .
			"where pedido_id not in (select pedido from registros  " .
			"where fecha BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) " .
			"and fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')  " .
			"and pedido_id not in (select pedido_id from carga_agenda " .
			"where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59'))) $uen $tipo_trabajo $ciudades) as DIFERENCIA " .
			"from carga_click pro " .
			"where pro.fecha_cita BETWEEN ('$fecha 00:00:00') " .
			"AND ('$fecha 23:59:59') " .
			"and pro.pedido_id in (select pedido_id from carga_agenda " .
			"where fecha_cita BETWEEN ('$fecha 00:00:00') " .
			"AND ('$fecha 23:59:59')) " .
			"and pro.pedido_id not in " .
			"(select reg.pedido " .
			"from registros reg, carga_agenda pro  " .
			"where pro.pedido_id in (select pedido_id from carga_click " .
			"where fecha_cita BETWEEN ('$fecha 00:00:00') " .
			"AND ('$fecha 23:59:59')) " .
			"and pro.pedido_id = reg.pedido  " .
			"and accion = 'Visita confirmada' " .
			"and reg.fecha BETWEEN ('$fecha 00:00:00') " .
			"AND ('$fecha 23:59:59') " .
			"and pro.fecha_cita BETWEEN ('$fecha 00:00:00') " .
			"AND ('$fecha 23:59:59')) " .
			"and pro.pedido_id not in " .
			"(select pedido_id " .
			"from carga_agenda " .
			"where pedido_id not in " .
			"(select pedido from registros where fecha " .
			"BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) " .
			"and fecha_cita BETWEEN ('$fecha 00:00:00') " .
			"AND ('$fecha 23:59:59') " .
			"and pedido_id in (select pedido_id from carga_click " .
			"where fecha_cita BETWEEN ('$fecha 00:00:00') " .
			"AND ('$fecha 23:59:59'))) " .
			"$uen $tipo_trabajo $ciudades group by jornada ";
		//echo $sqlSinConfirmar;
		$rrsinConfirmar = $this->connseguimiento->query($sqlSinConfirmar);
		while ($row = $rrsinConfirmar->fetch_assoc()) {

			$total_jornada = $row['total_jornada'];
			$jornada = $row['jornada'];
			$diferencia = $row['DIFERENCIA'];
			$total_sinconfirmar = $total_sinconfirmar + $total_jornada;
			$sqlupdatesinconfirmar = "UPDATE jornada_estados " .
				"SET `sin_confirmar`='$total_jornada' WHERE `id_jornada`='$jornada' ";

			$updatesinconfirmar = $this->connseguimiento->query($sqlupdatesinconfirmar);
		}
		$sqlupdatesinconfirmartotal = "UPDATE jornada_estados " .
			"SET `sin_confirmar`='$total_sinconfirmar' WHERE `id_jornada`='TOTAL' ";
		$updatesindoncfirmartotal = $this->connseguimiento->query($sqlupdatesinconfirmartotal);
		$sqlupdatedif = "UPDATE jornada_estados " .
			"SET `sin_confirmar`='$diferencia' WHERE `id_jornada`='DIFERENCIA' ";
		$rupdatedif = $this->connseguimiento->query($sqlupdatedif);

		$query = "SELECT * " .
			"FROM jornada_estados where id_jornada not in('TOTAL','DIFERENCIA')";
		//echo $query;

		$rst = $this->connseguimiento->query($query);

		//echo $this->mysqli->query($sqlLogin);
		//

		$query2 = "SELECT *, ROUND((confirmados/vista_click)*100,2) eficacia, " .
			"ROUND((finalizados_click/agendados)*100,2) efectividad " .
			"FROM jornada_estados where id_jornada in ('TOTAL') ";

		$rst2 = $this->connseguimiento->query($query2);
		$resultado2 = array();

		while ($row = $rst2->fetch_assoc()) {
			$resultado2[] = $row;
		}

		$query3 = "SELECT * " .
			"FROM jornada_estados where id_jornada in ('DIFERENCIA') ";

		$rst3 = $this->connseguimiento->query($query3);
		$resultado3 = array();

		while ($row = $rst3->fetch_assoc()) {
			$resultado3[] = $row;
		}

		$queryalarmados = "SELECT count(pedido_id) total " .
			"FROM alarmados where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') ";

		$rr = $this->connseguimiento->query($queryalarmados);
		$counter = 0;
		if ($rr->num_rows > 0) {
			$result = array();
			if ($row = $rr->fetch_assoc()) {
				$counter = $row['total'];
			}
		}
		//para las graficas

		$query = "select a.final final_click, b.agenda agendados, a.fecha_cita fecha, c.click click from   " .

			"(select count(pro.pedido_id) agenda, pro.fecha_cita  " .
			"from carga_agenda pro " .
			"where pro.fecha_cita  BETWEEN ('$año-$mes-01 00:00:00') AND ('$año-$mes-31 23:59:59') " .
			"$tipo_trabajo1 $uen $ciudades" .
			"group by pro.fecha_cita) b, " .

			"(select count(jornada_cita) final, " .
			"fecha_cita from carga_click click " .
			"where fecha_cita BETWEEN ('$año-$mes-01 00:00:00') AND ('$año-$mes-31 23:59:59') " .
			"and pedido_id in (select pedido_id from carga_agenda   " .
			"where fecha_cita = click.fecha_cita)  " .
			"and estado_id='Finalizada' $tipo_trabajo $uen $ciudades group by fecha_cita) a, " .

			"(select count(jornada_cita) click, pro.fecha_cita  " .
			"from carga_click pro " .
			"where pro.fecha_cita BETWEEN ('$año-$mes-01 00:00:00') AND ('$año-$mes-31 23:59:59') " .
			"and pedido_id in (select pedido_id from carga_agenda  " .
			"where fecha_cita = pro.fecha_cita) $tipo_trabajo $uen $ciudades group by pro.fecha_cita) c  " .
			"where a.fecha_cita = c.fecha_cita " .
			"and a.fecha_cita = b.fecha_cita " .
			"group by a.fecha_cita ";

		$r = $this->connseguimiento->query($query) or die($this->connseguimiento->error . __LINE__);

		if ($r->num_rows > 0) {
			$fecha = array();
			$click = array();
			$agendados = array();
			$final_click = array();
			$i = 1;
			while ($row = $r->fetch_assoc()) {

				$date = $row['fecha'];
				$en_click = $row['click'];
				$agenda = $row['agendados'];
				$finaliza = $row['final_click'];

				$fecha[] = array("label" => "$date");
				$click[] = array("value" => "$en_click");
				$agendados[] = array("value" => "$agenda");
				$final_click[] = array("value" => "$finaliza");
				$i++;
			}
		}
		//fin de la graficas

		if ($rst->num_rows > 0) {

			$resultado = array();

			while ($row = $rst->fetch_assoc()) {

				$resultado[] = $row;

			}
			$this->response($this->json(array($resultado, $resultado2, $resultado3, $fecha, $click, $agendados, $final_click, $counter)), 201);

		} else {
			$error = array();

			$this->response($this->json($error), 400);
		}
	}

	private function csvPreagen() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$usuarioid = $params['datosLogin'];
		$usuarioid = $usuarioid['LOGIN'];
		$datos = $params['datos'];
		$fecha = $datos['fecha'];
		$valor = $params['valor'];
		$uen = $datos['uen'];
		$tipotrabajo = $datos['tipo_trabajo'];
		$ciudad = $datos['CIUDAD'];
		$sep = "";
		$ciudades = "";
		$bandera = 0;
		$bandera1 = 0;

		if ($ciudad == null) {
			$ciudad = "";
		} else {
			$total = count($ciudad);
			for ($i = 0; $i < $total; $i++) {

				if ($valida = strpos($ciudad[$i], '_DEPA') !== false) {
					$bandera = $bandera + 1;
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
		} else if ($bandera == 0 && $bandera1 > 0) {
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
			$tipo_trabajo = "and tipo_trabajo = '$tipotrabajo'";
			$tipo_trabajo1 = "and (select tipo_trabajo from carga_click where pro.pedido_id = pedido_id limit 1) = '$tipotrabajo'";
		} else {
			$tipo_trabajo = "";
			$tipo_trabajo1 = "";
		}

		//echo "estos son los datos, usuario: ".$usuarioid." fecha: ".$fecha." y valor: ".$valor;
		//echo "estos son los otros tipo trabajo, usuario: ".$tipotrabajo." uen: ".$uen;

		$filename = $valor . "_" . $fecha . "_" . $uen . "_" . $tipotrabajo . "_" . $usuarioid . ".csv";

		if ($valor == "Totalagendados") {
			$query = "select pedido_id, cliente, departamento, ciudad, direccion, " .
				"productos, fecha_cita, jornada_cita, UEN, " .
				"(select estado_id from carga_click cl " .
				"where pro.pedido_id=cl.pedido_id limit 1) estado_click, " .
				"(select observacion from carga_click cl  " .
				"where pro.pedido_id=cl.pedido_id limit 1) observacion_click, " .
				"(select fecha_carga_click from carga_click cl  " .
				"where pro.pedido_id=cl.pedido_id limit 1) fecha_ingreso_click " .
				"from carga_agenda pro " .
				"where pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') " .
				"$uen $tipo_trabajo1 $ciudades";

			$queryCount = "select count(pedido_id) Cantidad " .
				"from carga_agenda pro " .
				"where pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') " .
				"$uen $tipo_trabajo1 $ciudades";
			//echo $queryCount;
			//
		} else if ($valor == "TotalVistaClick") {

			$query = "select pedido_id, cliente, departamento, ciudad, direccion, productos, " .
				"fecha_cita, " .
				"jornada_cita, uen, estado_id,  " .
				"(select descripcion from codigo_pendientes_click " .
				"where codigo = pro.codigo_pendiente_incompleto) descripcion, " .
				"actividad_trabajo, observacion, fecha_carga_click, tipo_trabajo  " .
				"from carga_click pro  " .
				"where pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') " .
				"and pro.pedido_id in (select pedido_id from carga_agenda " .
				"where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) " .
				"$uen $tipo_trabajo $ciudades";

			$queryCount = "select count(pedido_id) Cantidad " .
				"from carga_click pro " .
				"where pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')  " .
				"and pro.pedido_id in (select pedido_id from carga_agenda " .
				"where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59'))  " .
				"$uen $tipo_trabajo $ciudades";
		} else if ($valor == "TotalConfirmados") {

			$query = "select distinct pedido, cliente, departamento, ciudad, productos, " .
				"fecha_cita, jornada_cita, uen, accion " .
				"from (select reg.pedido, pro.cliente, pro.departamento, pro.ciudad,  " .
				"pro.productos, " .
				"pro.fecha_cita, pro.jornada_cita, pro.uen, reg.accion " .
				"from registros reg, carga_agenda pro   " .
				"where pro.pedido_id in (select pedido_id from carga_click " .
				"where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) " .
				"and pro.pedido_id = reg.pedido  " .
				"and accion = 'Visita confirmada' " .
				"and reg.fecha BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') " .
				"and pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') " .
				"$uen $tipo_trabajo1 $ciudades)b  ";

			$queryCount = "select count(a.pedido) Cantidad " .
				"from (select distinct reg.pedido " .
				"from registros reg, carga_agenda pro  " .
				"where pro.pedido_id in (select pedido_id from carga_click " .
				"where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) " .
				"and pro.pedido_id = reg.pedido  " .
				"and accion = 'Visita confirmada' " .
				"and reg.fecha BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') " .
				"and pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') " .
				" $uen $tipo_trabajo1 $ciudades) a ";
		} else if ($valor == "TotalSinConfirmar") {

			$query = "select pedido_id, cliente, departamento, ciudad, productos, fecha_cita, " .
				"jornada_cita, uen, descripcion, " .
				"estado_id " .
				"from (select distinct pro.pedido_id, pro.cliente, pro.departamento, " .
				"pro.ciudad, pro.productos, " .
				"pro.fecha_cita, pro.jornada_cita, pro.uen, (select descripcion from " .
				"codigo_pendientes_click " .
				"where codigo = pro.codigo_pendiente_incompleto) descripcion, estado_id " .
				"from carga_click pro " .
				"where pro.fecha_cita BETWEEN ('$fecha 00:00:00') " .
				"AND ('$fecha 23:59:59') " .
				"and pro.pedido_id in (select pedido_id from carga_agenda " .
				"where fecha_cita BETWEEN ('$fecha 00:00:00') " .
				"AND ('$fecha 23:59:59')) " .
				"and pro.pedido_id not in " .
				"(select reg.pedido " .
				"from registros reg, carga_agenda pro  " .
				"where pro.pedido_id in (select pedido_id from carga_click " .
				"where fecha_cita BETWEEN ('$fecha 00:00:00') " .
				"AND ('$fecha 23:59:59')) " .
				"and pro.pedido_id = reg.pedido " .
				"and accion = 'Visita confirmada' " .
				"and reg.fecha BETWEEN ('$fecha 00:00:00') " .
				"AND ('$fecha 23:59:59') " .
				"and pro.fecha_cita BETWEEN ('$fecha 00:00:00') " .
				"AND ('$fecha 23:59:59')) " .
				"and pro.pedido_id not in " .
				"(select pedido_id 	" .
				"from carga_agenda " .
				"where pedido_id not in " .
				"(select pedido from registros where fecha " .
				"BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) " .
				"and fecha_cita BETWEEN ('$fecha 00:00:00') " .
				"AND ('$fecha 23:59:59') " .
				"and pedido_id in (select pedido_id from carga_click " .
				"where fecha_cita BETWEEN ('$fecha 00:00:00') " .
				"AND ('$fecha 23:59:59'))) $uen $tipo_trabajo $ciudades)a ";

			$queryCount = " select Cantidad " .
				"from (select distinct count(pro.pedido_id) Cantidad " .
				"from carga_click pro  " .
				"where pro.fecha_cita BETWEEN ('$fecha 00:00:00') " .
				"AND ('$fecha 23:59:59')  " .
				"and pro.pedido_id in (select pedido_id from carga_agenda " .
				"where fecha_cita BETWEEN ('$fecha 00:00:00') " .
				"AND ('$fecha 23:59:59')) " .
				"and pro.pedido_id not in " .
				"(select reg.pedido " .
				"from registros reg, carga_agenda pro  " .
				"where pro.pedido_id in (select pedido_id from carga_click " .
				"where fecha_cita BETWEEN ('$fecha 00:00:00') " .
				"AND ('$fecha 23:59:59')) " .
				"and pro.pedido_id = reg.pedido  " .
				"and accion = 'Visita confirmada' " .
				"and reg.fecha BETWEEN ('$fecha 00:00:00') " .
				"AND ('$fecha 23:59:59') " .
				"and pro.fecha_cita BETWEEN ('$fecha 00:00:00') " .
				"AND ('$fecha 23:59:59')) " .
				"and pro.pedido_id not in " .
				"(select pedido_id 			" .
				"from carga_agenda " .
				"where pedido_id not in  " .
				"(select pedido from registros where fecha " .
				"BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) " .
				"and fecha_cita BETWEEN ('$fecha 00:00:00') " .
				"AND ('$fecha 23:59:59') " .
				"and pedido_id in (select pedido_id from carga_click " .
				"where fecha_cita BETWEEN ('$fecha 00:00:00') " .
				"AND ('$fecha 23:59:59'))) $uen $tipo_trabajo $ciudades)a ";
		} else if ($valor == "TotalNogestionados") {

			$query = "select pedido_id, cliente, departamento, ciudad, productos, fecha_cita, " .
				"jornada_cita, uen " .
				"from carga_agenda pro " .
				"where pedido_id not in (select pedido from registros where fecha " .
				"BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) " .
				"and fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') " .
				"and pedido_id in (select pedido_id from carga_click  " .
				"where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) " .
				"$uen $tipo_trabajo1 $ciudades";

			$queryCount = "select count(pedido_id) Cantidad " .
				"from carga_agenda pro " .
				"where pedido_id not in (select pedido from registros where " .
				"fecha BETWEEN ('2017-02-01 00:00:00') AND ('2017-02-01 23:59:59')) " .
				"and fecha_cita BETWEEN ('2017-02-01 00:00:00') AND ('2017-02-01 23:59:59') " .
				"and pedido_id in (select pedido_id from carga_click  " .
				"where fecha_cita BETWEEN ('2017-02-01 00:00:00') AND ('2017-02-01 23:59:59')) " .
				"$uen $tipo_trabajo1 $ciudades";
		} else if ($valor == "TotalFinalClick") {

			$query = "select pedido_id,cliente, departamento, ciudad, direccion, productos, " .
				"fecha_cita, jornada_cita, estado_id, uen, codigo_pendiente_incompleto,  " .
				"(select descripcion from codigo_pendientes_click " .
				"where codigo = pro.codigo_pendiente_incompleto) descripcion, tipo_trabajo, " .
				"observacion, fecha_carga_click " .
				"from carga_click pro " .
				"where pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') " .
				"and pro.pedido_id in (select pedido_id from carga_agenda " .
				"where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) " .
				"and pro.estado_id = 'Finalizada' " .
				"$uen $tipo_trabajo $ciudades";

			$queryCount = "select count(pedido_id) Cantidad " .
				"from carga_click pro " .
				"where pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') " .
				"and pro.pedido_id in (select pedido_id from carga_agenda " .
				"where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) " .
				"and pro.estado_id = 'Finalizada' " .
				"$uen $tipo_trabajo $ciudades";
		} else if ($valor == "Diferenciasagendados") {

			$query = "select pedido_id, cliente, departamento, ciudad, direccion, " .
				"productos, fecha_cita, jornada_cita, UEN, " .
				"(select estado_id from carga_click cl  " .
				"where pro.pedido_id=cl.pedido_id limit 1) estado_click, " .
				"(select observacion from carga_click cl  " .
				"where pro.pedido_id=cl.pedido_id limit 1) observacion_click, " .
				"(select fecha_carga_click from carga_click cl " .
				"where pro.pedido_id=cl.pedido_id limit 1) fecha_ingreso_click " .
				"from carga_agenda pro " .
				"where pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') " .
				"and pro.pedido_id not in (select pedido_id from carga_click " .
				"where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) " .
				"$uen $tipo_trabajo1 $ciudades";

			$queryCount = "select count(pro.pedido_id) Cantidad " .
				"from carga_agenda pro " .
				"where pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') " .
				"and pro.pedido_id not in (select pedido_id from carga_click " .
				"where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) " .
				"$uen $tipo_trabajo1 $ciudades";
		} else if ($valor == "DiferenciasVistaClick") {

			$query = "select pedido_id, cliente, departamento, ciudad, direccion, productos, " .
				"fecha_cita, " .
				"jornada_cita, uen, estado_id,  " .
				"(select descripcion from codigo_pendientes_click " .
				"where codigo = pro.codigo_pendiente_incompleto) descripcion, " .
				"actividad_trabajo, observacion, fecha_carga_click, tipo_trabajo  " .
				"from carga_click pro  " .
				"where pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') " .
				"and pro.pedido_id not in (select pedido_id from carga_agenda " .
				"where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) " .
				"$uen $tipo_trabajo $ciudades";

			$queryCount = "select count(pedido_id) Cantidad " .
				"from carga_click pro " .
				"where pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')  " .
				"and pro.pedido_id not in (select pedido_id from carga_agenda " .
				"where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59'))  " .
				"$uen $tipo_trabajo $ciudades";
		} else if ($valor == "DiferenciasConfirmados") {

			$query = "select pedido_id,cliente, departamento, ciudad, direccion, productos, " .
				"fecha_cita, jornada_cita,  " .
				"(select accion from registros " .
				"where pedido_id = pedido limit 1) accion, (select observaciones from registros " .
				"where pedido_id = pedido limit 1) accion " .
				"from carga_click pro " .
				"where pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') " .
				"and pro.pedido_id not in (select pedido_id from carga_agenda  " .
				"where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) " .
				"and pro.pedido_id in (select pedido from registros where  " .
				"accion = 'Visita confirmada'  " .
				"and fecha BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) " .
				"and pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') " .
				"$uen $tipo_trabajo $ciudades";

			$queryCount = "select count(pedido_id) Cantidad " .
				"from carga_click pro  " .
				"where pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')  " .
				"and pro.pedido_id not in (select pedido_id from carga_agenda " .
				"where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) " .
				"and pro.pedido_id in (select pedido from registros where  " .
				"accion = 'Visita confirmada' " .
				"and fecha BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) " .
				"and pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') " .
				"$uen $tipo_trabajo $ciudades";
		} else if ($valor == "DiferenciasSinConfirmar") {

			$query = "select pedido_id, cliente, departamento, ciudad, productos, fecha_cita, " .
				"jornada_cita, uen, (select descripcion from " .
				"codigo_pendientes_click " .
				"where codigo = pro.codigo_pendiente_incompleto) descripcion, " .
				"estado_id " .
				"from carga_click pro   " .
				"where pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') " .
				"and pro.pedido_id not in (select pedido_id from carga_agenda  " .
				"where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59'))  " .
				"and pro.pedido_id not in " .
				"(select pedido_id " .
				"from carga_click pro  " .
				"where pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')  " .
				"and pro.pedido_id not in (select pedido_id from carga_agenda " .
				"where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) " .
				"and pro.pedido_id in (select pedido from registros where " .
				"accion = 'Visita confirmada' " .
				"and fecha BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) " .
				"and pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) " .
				"and pro.pedido_id not in " .
				"(select pedido_id " .
				"from carga_click  " .
				"where pedido_id not in (select pedido from registros  " .
				"where fecha BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) " .
				"and fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')  " .
				"and pedido_id not in (select pedido_id from carga_agenda " .
				"where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59'))) $uen $tipo_trabajo $ciudades";

			$queryCount = "select count(pro.pedido_id) Cantidad " .
				"from carga_click pro   " .
				"where pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') " .
				"and pro.pedido_id not in (select pedido_id from carga_agenda  " .
				"where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59'))  " .
				"and pro.pedido_id not in " .
				"(select pedido_id " .
				"from carga_click pro  " .
				"where pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')  " .
				"and pro.pedido_id not in (select pedido_id from carga_agenda " .
				"where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) " .
				"and pro.pedido_id in (select pedido from registros where " .
				"accion = 'Visita confirmada' " .
				"and fecha BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) " .
				"and pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) " .
				"and pro.pedido_id not in " .
				"(select pedido_id " .
				"from carga_click  " .
				"where pedido_id not in (select pedido from registros  " .
				"where fecha BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) " .
				"and fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')  " .
				"and pedido_id not in (select pedido_id from carga_agenda " .
				"where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59'))) $uen $tipo_trabajo $ciudades";
		} else if ($valor == "Diferenciasnogestionados") {

			$query = "select pedido_id, cliente, departamento, ciudad, productos, fecha_cita, " .
				"jornada_cita, uen " .
				"from carga_click " .
				"where pedido_id not in (select pedido from registros " .
				"where fecha BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) " .
				"and fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') " .
				"and pedido_id not in (select pedido_id from carga_agenda " .
				"where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) $uen $tipo_trabajo $ciudades";

			$queryCount = "select count(pedido_id) Cantidad " .
				"from carga_click " .
				"where pedido_id not in (select pedido from registros " .
				"where fecha BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) " .
				"and fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') " .
				"and pedido_id not in (select pedido_id from carga_agenda " .
				"where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) $uen $tipo_trabajo $ciudades";
		} else if ($valor == "DiferenciasFinalClick") {

			$query = "select pedido_id,cliente, departamento, ciudad, direccion, productos, " .
				"fecha_cita, jornada_cita, estado_id, uen, codigo_pendiente_incompleto,  " .
				"(select descripcion from codigo_pendientes_click " .
				"where codigo = pro.codigo_pendiente_incompleto) descripcion, tipo_trabajo, " .
				"observacion, fecha_carga_click " .
				"from carga_click pro " .
				"where pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') " .
				"and pro.pedido_id not in (select pedido_id from carga_agenda " .
				"where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) " .
				"and pro.estado_id = 'Finalizada' " .
				"$uen $tipo_trabajo $ciudades";

			$queryCount = "select count(pedido_id) Cantidad " .
				"from carga_click pro " .
				"where pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') " .
				"and pro.pedido_id not in (select pedido_id from carga_agenda " .
				"where fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59')) " .
				"and pro.estado_id = 'Finalizada' " .
				"$uen $tipo_trabajo $ciudades";
		}

		$rr = $this->connseguimiento->query($queryCount);
		$counter = 0;
		if ($rr->num_rows > 0) {
			$result = array();
			if ($row = $rr->fetch_assoc()) {
				$counter = $row['Cantidad'];
			}
		}
		//echo $counter;

		$rst = $this->connseguimiento->query($query) or die($this->connseguimiento->error . __LINE__);

		if ($rst->num_rows > 0) {

			//Insert en log
			//$sql_log="insert into gestor_despacho.gopd_logoperaciones (USUARIO_ID, TIPO_ACTIVIDAD, DESCRIPCION, IDENTIFICADOR, IP, PC) values(UPPER('$usuarioid'),'EXPORTE','EXPORTO CSV_HISTORICO','LOGIN: $usuarioid','$usuarioIp','$usuarioPc')";

			//$rlog = $this->connemtel->query($sql_log) or die($this->connemtel->error.__LINE__);
			//Insert en log

			$result = array();
			$fp = fopen("../tmp/$filename", 'w');
			//echo $fp;

			if ($valor == "Totalagendados" || $valor == "Diferenciasagendados") {
				$columnas = array('PEDIDO',
					'CLIENTE',
					'DEPARTAMENTO',
					'CIUDAD',
					'DIRECCION',
					'PRODUCTOS',
					'FECHA_CITA',
					'JORNADA_CITA',
					'UEN',
					'ESTADO_CLICK',
					'OBSERVACION_CLICK',
					'FECHA_INGRESO_CLICK');
			} else if ($valor == "TotalVistaClick" || $valor == "DiferenciasVistaClick") {
				$columnas = array('PEDIDO',
					'CLIENTE',
					'DEPARTAMENTO',
					'CIUDAD',
					'DIRECCION',
					'PRODUCTOS',
					'FECHA_CITA',
					'JORNADA_CITA',
					'UEN',
					'ESTADO_CLICK',
					'DESCRIPCION',
					'ACTIVIDAD_TRABAJO',
					'OBSERVACION_CLICK',
					'FECHA_INGRESO_CLICK',
					'TIPO_PENDIENTE');
			} else if ($valor == "TotalConfirmados" || $valor == "DiferenciasConfirmados") {
				$columnas = array('PEDIDO',
					'CLIENTE',
					'DEPARTAMENTO',
					'CIUDAD',
					'PRODUCTOS',
					'FECHA_CITA',
					'JORNADA_CITA',
					'UEN',
					'ACCION',
					'OBSERVACIONES');
			} else if ($valor == "TotalSinConfirmar" || $valor == "DiferenciasSinConfirmar") {
				$columnas = array('PEDIDO',
					'CLIENTE',
					'DEPARTAMENTO',
					'CIUDAD',
					'PRODUCTOS',
					'FECHA_CITA',
					'JORNADA_CITA',
					'UEN',
					'DESCRIPCION',
					'ESTADO_ID');
			} else if ($valor == "TotalNogestionados" || $valor == "Diferenciasnogestionados") {
				$columnas = array('PEDIDO',
					'CLIENTE',
					'DEPARTAMENTO',
					'CIUDAD',
					'PRODUCTOS',
					'FECHA_CITA',
					'JORNADA_CITA',
					'UEN');
			} else if ($valor == "TotalFinalClick" || $valor == "DiferenciasFinalClick") {
				$columnas = array('PEDIDO',
					'CLIENTE',
					'DEPARTAMENTO',
					'CIUDAD',
					'DIRECCION',
					'PRODUCTOS',
					'FECHA_CITA',
					'ESTADO_ID',
					'UEN',
					'CODIGO_PENDIENTE',
					'DESCRIPCION',
					'TIPO_PENDIENTE',
					'OBSERVACION',
					'FECHA_INGRESO_CLICK');
			}

			fputcsv($fp, $columnas);
			//$carlitos=0;
			while ($row = $rst->fetch_assoc()) {

				$row['observacion_click'] = utf8_decode($row['observacion_click']);
				//$result[] = $row;
				fputcsv($fp, $row);
				//if($carlitos==0){var_dump($row);$carlitos=1;};
			}

			fclose($fp);

			$this->response($this->json(array($filename, $counter)), 200);
		}
		$this->response('', 203); // If no records "No Content" status
		//descargo archivo preagenda, activity feed
	}

	private function csvEstadosClick() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$usuarioid = $params['datosLogin'];
		$usuarioid = $usuarioid['LOGIN'];
		$datos = $params['datos'];
		$fecha = $datos['fecha'];
		$valor = $params['valor'];
		$uen = $datos['uen'];
		$tipotrabajo = $datos['tipo_trabajo'];

		if ($fecha == "") {
			$fecha = date("Y") . "-" . date("m") . "-" . date("d");
		}

		if ($uen != "") {
			$uen = "and uen = '$uen'";
		} else {
			$uen = "";
		}
		if ($tipotrabajo != "") {
			$tipo_trabajo = "and tipo_trabajo = '$tipotrabajo'";
			$tipo_trabajo1 = "and (select tipo_trabajo from carga_click where pro.pedido_id = pedido_id limit 1) = '$tipotrabajo'";
		} else {
			$tipo_trabajo = "";
			$tipo_trabajo1 = "";
		}

		//echo "estos son los datos, usuario: ".$usuarioid." fecha: ".$fecha." y valor: ".$valor;
		//echo "estos son los otros tipo trabajo, usuario: ".$tipotrabajo." uen: ".$uen;

		$filename = "Estados_click" . "_" . $fecha . "_" . $uen . "_" . $tipotrabajo . "_" . $usuarioid . ".csv";

		$query = "select pro.pedido_id,pro.cliente,pro.departamento, pro.ciudad, pro.direccion, pro.productos, " .
			"pro.fecha_cita, pro.jornada_cita, pro.estado_id, pro.uen, pro.codigo_pendiente_incompleto,  " .
			"(select descripcion from codigo_pendientes_click  " .
			"where codigo = pro.codigo_pendiente_incompleto) descripcion, pro.tipo_trabajo, " .
			"pro.observacion, pro.fecha_carga_click, c.id_tecnico, c.accion, c.tipo_pendiente, c.fecha, c.observaciones " .
			"from carga_click pro " .
			"left join (SELECT a.pedido, a.id_tecnico, a.accion, a.tipo_pendiente, a.fecha, a.observaciones " .
			"FROM registros a " .
			"where a.fecha BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') ) c  " .
			"on c.pedido = pro.pedido_id " .
			"where pro.estado_id is not null " .
			"and pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha  23:59:59') " .
			"$ciudad $uen $tipotrabajo";

		$queryCount = "select count(pedido_id) Cantidad " .
			"from carga_click pro " .
			"left join (SELECT a.pedido, a.id_tecnico, a.accion, a.tipo_pendiente, a.fecha, a.observaciones " .
			"FROM registros a " .
			"where a.fecha BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') ) c  " .
			"on c.pedido = pro.pedido_id " .
			"where pro.estado_id is not null " .
			"and pro.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha  23:59:59') " .
			"$ciudad $uen $tipotrabajo";
		//echo $queryCount;
		//
		$rr = $this->connseguimiento->query($queryCount);
		$counter = 0;
		if ($rr->num_rows > 0) {
			$result = array();
			if ($row = $rr->fetch_assoc()) {
				$counter = $row['Cantidad'];
			}
		}
		//echo $counter;

		$rst = $this->connseguimiento->query($query) or die($this->connseguimiento->error . __LINE__);

		if ($rst->num_rows > 0) {

			//Insert en log
			//$sql_log="insert into gestor_despacho.gopd_logoperaciones (USUARIO_ID, TIPO_ACTIVIDAD, DESCRIPCION, IDENTIFICADOR, IP, PC) values(UPPER('$usuarioid'),'EXPORTE','EXPORTO CSV_HISTORICO','LOGIN: $usuarioid','$usuarioIp','$usuarioPc')";

			//$rlog = $this->connemtel->query($sql_log) or die($this->connemtel->error.__LINE__);
			//Insert en log

			$result = array();
			$fp = fopen("../tmp/$filename", 'w');
			//echo $fp;

			$columnas = array('PEDIDO',
				'CLIENTE',
				'DEPARTAMENTO',
				'CIUDAD',
				'DIRECCION',
				'PRODUCTOS',
				'FECHA_CITA',
				'JORNADA_CITA',
				'ESTADO_CLICK',
				'UEN',
				'CONCEPTO',
				'DESCRIPCION',
				'TIPO_TRABAJO',
				'OBSERVACION_CLICK',
				'FECHA_INGRESO_CLICK',
				'TECNICO',
				'ACCION',
				'SUBACCION',
				'FECHA_GESTION',
				'OBSERVACIONES');

			fputcsv($fp, $columnas);
			//$carlitos=0;
			while ($row = $rst->fetch_assoc()) {

				$row['observacion'] = utf8_decode($row['observacion']);
				$row['observaciones'] = utf8_decode($row['observaciones']);
				//$result[] = $row;
				fputcsv($fp, $row);
				//if($carlitos==0){var_dump($row);$carlitos=1;};
			}

			fclose($fp);

			$this->response($this->json(array($filename, $counter)), 200);
		}
		$this->response('', 203); // If no records "No Content" status
		//descarga archivo estados click, activity feed
	}

	private function CsvpeniInsta() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$usuarioid = $params['datosLogin'];
		$usuarioid = $usuarioid['LOGIN'];
		$regional = $params['regional'];

		$filename = "Pendiente_instalaciones" . "_" . $regional . "_" . $usuarioid . ".csv";

		if ($regional == "Total") {
			$regional = " ";
		} else {
			$regional = "where REGIONAL = '$regional'";
		}

		$query = "SELECT PEDIDO_ID, " .
			"MUNICIPIO, " .
			"USUARIO_ID, " .
			"NOMBRE_CANAL, " .
			"RADICADO, " .
			"RUTA_TRABAJO, " .
			"TIPO_TRABAJO, " .
			"TEL_CELULAR, " .
			"DEPARTAMENTO, " .
			"REGIONAL, " .
			"INTERFAZ, " .
			"SUBZONA, " .
			"SEGM_PYMES, " .
			"AREA_TRABAJO, " .
			"FECHA_INGRESO, " .
			"FECHA_CONCEPTO, " .
			"FECHA_ACTUALIZACION, " .
			"DEPARTAMENTO_OPERACION, " .
			"DIRECCION, " .
			"CONCEPTO_ID_ATC, " .
			"CONCEPTO_ATC, " .
			"ESTADO_ID_ATC, " .
			"UEN_CALCULADA, " .
			"TIPO_DOCUMENTO, " .
			"CLIENTE_ID, " .
			"NOMBRE_CLIENTE, " .
			"CONCEPTO_ORACLE, " .
			"RESPONSABLE_GESTION, " .
			"AREA_RESPONSABLE, " .
			"RESPONSABLE_OPERATIVO, " .
			"RESPONSABLE_ACTIVITY, " .
			"TIPO_SOLICITUD, " .
			"TIPO_SOLICITUD_ORIG, " .
			"RANGO_INGRESO_DIAS, " .
			"RANGO_CONCEPTO_DIAS, " .
			"FECHA_CITA_CALC, " .
			"ESTADO_AGENDA, " .
			"PRODUCTOS, " .
			"CLIENTES, " .
			"DETALLE_PRODUCTOS, " .
			"DETALLE_ELEMENTOS, " .
			"DETALLE_PRODUCTOS1, " .
			"DETALLE_ESTADO_CN, " .
			"DETALLE_SUBPRODUCTOS, " .
			"REQUIEREAGENDA_ESTADOCN_ACTIVIDADCN, " .
			"DETALLE_ACTIVIDAD_CN, " .
			"DETALLE_CONCEPTOS, " .
			"DETALLE_USUARIO_ULT_CONC, " .
			"DETALLE_CONCEPTOS_ORA, " .
			"DETALLE_TIPO_SOLICITUD, " .
			"DETALLE_TECNOLOGIA, " .
			"DETALLE_COLAS, " .
			"DETALLE_ETAPA, " .
			"DIAS_INGRESO, " .
			"DIAS_CONCEPTO, " .
			"ESTRATO, " .
			"NRO_AGENDAMIENTOS, " .
			"FECHA, " .
			"HORAS_DE_CARGA, " .
			"NRO_PRODUCTOS_NUEVOS, UNIDAD_NEGOCIO " .
			"FROM pendi_insta " .
			"$regional ";

		$queryCount = "select count(pedido_id) Cantidad " .
			"from pendi_insta pro " .
			"$regional ";
		//echo $queryCount;
		//
		$rr = $this->connseguimiento->query($queryCount);
		$counter = 0;
		if ($rr->num_rows > 0) {
			$result = array();
			if ($row = $rr->fetch_assoc()) {
				$counter = $row['Cantidad'];
			}
		}
		//echo $counter;

		$rst = $this->connseguimiento->query($query) or die($this->connseguimiento->error . __LINE__);

		if ($rst->num_rows > 0) {

			//Insert en log
			//$sql_log="insert into gestor_despacho.gopd_logoperaciones (USUARIO_ID, TIPO_ACTIVIDAD, DESCRIPCION, IDENTIFICADOR, IP, PC) values(UPPER('$usuarioid'),'EXPORTE','EXPORTO CSV_HISTORICO','LOGIN: $usuarioid','$usuarioIp','$usuarioPc')";

			//$rlog = $this->connemtel->query($sql_log) or die($this->connemtel->error.__LINE__);
			//Insert en log

			$result = array();
			$fp = fopen("../tmp/$filename", 'w');
			//echo $fp;

			$columnas = array('PEDIDO_ID',
				'MUNICIPIO',
				'USUARIO_ID',
				'NOMBRE_CANAL',
				'RADICADO',
				'RUTA_TRABAJO',
				'TIPO_TRABAJO',
				'TEL_CELULAR',
				'DEPARTAMENTO',
				'REGIONAL',
				'INTERFAZ',
				'SUBZONA',
				'SEGM_PYMES',
				'AREA_TRABAJO',
				'FECHA_INGRESO',
				'FECHA_CONCEPTO',
				'FECHA_ACTUALIZACION',
				'DEPARTAMENTO_OPERACION',
				'DIRECCION',
				'CONCEPTO_ID_ATC',
				'CONCEPTO_ATC',
				'ESTADO_ID_ATC',
				'UEN_CALCULADA',
				'TIPO_DOCUMENTO',
				'CLIENTE_ID',
				'NOMBRE_CLIENTE',
				'CONCEPTO_ORACLE',
				'RESPONSABLE_GESTION',
				'AREA_RESPONSABLE',
				'RESPONSABLE_OPERATIVO',
				'RESPONSABLE_ACTIVITY',
				'TIPO_SOLICITUD',
				'TIPO_SOLICITUD_ORIG',
				'RANGO_INGRESO_DIAS',
				'RANGO_CONCEPTO_DIAS',
				'FECHA_CITA_CALC',
				'ESTADO_AGENDA',
				'PRODUCTOS',
				'CLIENTES',
				'DETALLE_PRODUCTOS',
				'DETALLE_ELEMENTOS',
				'DETALLE_PRODUCTOS1',
				'DETALLE_ESTADO_CN',
				'DETALLE_SUBPRODUCTOS',
				'REQUIEREAGENDA_ESTADOCN_ACTIVIDADCN',
				'DETALLE_ACTIVIDAD_CN',
				'DETALLE_CONCEPTOS',
				'DETALLE_USUARIO_ULT_CONC',
				'DETALLE_CONCEPTOS_ORA',
				'DETALLE_TIPO_SOLICITUD',
				'DETALLE_TECNOLOGIA',
				'DETALLE_COLAS',
				'DETALLE_ETAPA',
				'DIAS_INGRESO',
				'DIAS_CONCEPTO',
				'ESTRATO',
				'NRO_AGENDAMIENTOS',
				'FECHA',
				'HORAS_DE_CARGA',
				'NRO_PRODUCTOS_NUEVOS',
				'UNIDAD_NEGOCIO');

			fputcsv($fp, $columnas);
			//$carlitos=0;
			while ($row = $rst->fetch_assoc()) {

				//$result[] = $row;
				fputcsv($fp, $row);
				//if($carlitos==0){var_dump($row);$carlitos=1;};
			}

			fclose($fp);

			$this->response($this->json(array($filename, $counter)), 200);
		}
		$this->response('', 203); // If no records "No Content" status
		//descarga archivo pendientes instalaciones, activity feed
	}

	// Exportar RRHH
	private function csvexportarRRHH() {

		if ($this->get_request_method() != "GET") {
			$this->response('', 406);
		}
		echo "Aun estoy funcionando";
		$this->dbClickConnect();
		echo "Aun estoy funcionando2";
		$params = json_decode(file_get_contents('php://input'), true);
		$hoy = getdate();

		$filename = "Disponibilidad" . "_" . $hoy . ".csv";

		$query = "SELECT ENG.ID Cedula, " .
			"ENG.LoginName Login, " .
			"ENG.Name Nombre, " .
			"ENG.Mobilephone, " .
			"REG.Name as Region, " .
			"DIST.Name as Distrito, " .
			"ENTY.Name as TipoTecnico, " .
			"ENG.UNEProvisioner Contratista, " .
			"ENG.Latitude, " .
			"ENG.Longitude, " .
			"SK.Name Skill, " .
			"DIST2.Name WorkingDistricts, " .
			"CAL.Name Calendario, " .
			"CONCAT(ASS.CommentText,'-',NAVA.Name) NoDisponibilidad " .
			"FROM W6ENGINEERS ENG " .
			"LEFT JOIN W6ENG_DYNAMIC_DATA EDD ON ENG.W6Key=EDD.Engineer " .
			"LEFT JOIN W6ENG_DYNAMIC_DATA_CONNECTED ENDC ON EDD.W6Key=ENDC.W6Key " .
			"LEFT JOIN W6REGIONS REG ON ENG.Region=REG.W6Key " .
			"LEFT JOIN W6DISTRICTS DIST ON ENG.District=DIST.W6Key " .
			"LEFT JOIN W6ENGINEER_TYPES ENTY ON ENG.EngineerType=ENTY.W6Key " .
			"LEFT JOIN W6ENGINEERS_SKILLS ENSK ON ENSK.W6Key=ENG.W6Key " .
			"LEFT JOIN W6SKILLS SK ON ENSK.SkillKey=SK.W6Key " .
			"LEFT JOIN W6ENGINEERS_WORKINGDISTRICTS WD ON WD.W6Key=ENG.W6Key " .
			"LEFT JOIN W6DISTRICTS DIST2 ON WD.WorkingDistrict=DIST2.W6Key " .
			"LEFT JOIN W6CALENDARS CAL ON ENG.Calendar=CAL.W6Key " .
			"LEFT JOIN W6ASSIGNMENTS ASS ON (ASS.AssignedEngineers=ENG.Name " .
			"AND ASS.StartTime<= CONVERT (date, SYSDATETIME()) " .
			"AND ASS.FinishTime>CONVERT (date, SYSDATETIME()) " .
			"AND ASS.Task is NULL) " .
			"LEFT JOIN W6NONAVAILABILITY_TYPES NAVA ON ASS.NonAvailabilityType= NAVA.W6Key " .
			"WHERE ENG.SOLicenseInactive=0 " .
			"AND ENG.Active=-1;";

		$rst = $this->dbClickConnect->query($query) or die($this->dbClickConnect->error . __LINE__);



		if ($rst->num_rows > 0) {

			$result = array();
			$fp = fopen("../tmp/$filename", 'w');
			//echo $fp;

			$columnas = array('CEDULA',
				'LOGIN',
				'NOMBRE',
				'MOBILE_PHONE',
				'REGION',
				'DISTRITO',
				'TIPO_TECNICO',
				'CONTRATISTA',
				'LATITUD',
				'LONGITUD',
				'SKILL',
				'WORKING_DISTRICTS',
				'CALENDARIO',
				'NO_DISPONIBILIDAD');

			fputcsv($fp, $columnas);

			while ($row = $rst->fetch_assoc()) {
				fputcsv($fp, $row);
			}

			fclose($fp);

			$this->response($this->json(array($filename, $rst->num_rows)), 200);
		}
		$this->response('', 203);
	}

	private function csvRegistros() {
		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$usuarioid = $params['datosLogin'];
		$usuarioid = $usuarioid['LOGIN'];
		$datos = $params['datos'];
		$fechaini = $datos['fechaini'];
		$fechafin = $datos['fechafin'];
		$concepto = $datos['concepto'];
		$buscar = $datos['buscar'];

		if ($fechaini == "" && $fechafin == "") {
			$fechaini = date("Y") . "-" . date("m") . "-" . date("d");
			$fechafin = date("Y") . "-" . date("m") . "-" . date("d");
		}

		//echo "estos son los datos, usuario: ".$usuarioid." fechaini: ".$fechaini." y fechafin: ".$fechafin;
		//echo "estos son los otros concepto, buscar: ".$concepto." buscar: ".$buscar;
		if ($fechaini == $fechafin) {
			$filename = "Registros" . "_" . $fechaini . "_" . $concepto . "_" . $buscar . ".csv";
		} else {
			$filename = "Registros" . "_" . $fechaini . "_" . $fechafin . "_" . $concepto . "_" . $buscar . ".csv";
		}
		if ($concepto == "" || $buscar == "") {
			$parametros = "";
		} else {
			$parametros = "and $concepto = '$buscar'";
		}
		$query = "select a.pedido,a.id_tecnico, " .
			"a.empresa,a.asesor,a.despacho,replace(a.observaciones,';','') as observaciones, " .
			"a.accion,a.tipo_pendiente, a.fecha,  " .
			"a.proceso, a.producto,a.duracion,a.llamada_id, a.prueba_integrada, a.pruebaSmnet, a.UNESourceSystem, a.pendiente, a.diagnostico " .
			"from registros a " .
			"where a.fecha between '$fechaini 00:00:00' and '$fechafin 23:59:59' " .
			"and a.asesor <> 'IVR' " .
			"$parametros";

		$queryCount = "select count(pedido) Cantidad " .
			"from registros a " .
			"where a.fecha between '$fechaini 00:00:00' and '$fechafin 23:59:59' " .
			"and a.asesor <> 'IVR' " .
			"$parametros";
		//s    echo $queryCount;
		//
		$rr = $this->connseguimiento->query($queryCount);
		$counter = 0;
		if ($rr->num_rows > 0) {
			$result = array();
			if ($row = $rr->fetch_assoc()) {
				$counter = $row['Cantidad'];
			}
		}
		//echo $counter;

		$rst = $this->connseguimiento->query($query) or die($this->connseguimiento->error . __LINE__);

		if ($rst->num_rows > 0) {

			//Insert en log
			//$sql_log="insert into gestor_despacho.gopd_logoperaciones (USUARIO_ID, TIPO_ACTIVIDAD, DESCRIPCION, IDENTIFICADOR, IP, PC) values(UPPER('$usuarioid'),'EXPORTE','EXPORTO CSV_HISTORICO','LOGIN: $usuarioid','$usuarioIp','$usuarioPc')";

			//$rlog = $this->connemtel->query($sql_log) or die($this->connemtel->error.__LINE__);
			//Insert en log

			$result = array();
			$fp = fopen("../tmp/$filename", 'w');
			//echo $fp;

			$columnas = array('PEDIDO',
				'ID_TECNICO',
				'EMPRESA',
				'LOGIN_ASESOR',
				'DESPACHO',
				'OBSERVACIONES',
				'ACCION',
				'SUB_ACCION',
				'FECHA',
				'PROCESO',
				'PRODUCTO',
				'DURACION_LLAMADA',
				'IDLLAMADA',
				'PRUEBA_INTEGRADA',
				'PRUEBASMNET',
				'UNESOURCESYSTEM',
				'PENDIENTE',
				'DIAGNOSTICO');

			fputcsv($fp, $columnas);
			//$carlitos=0;
			while ($row = $rst->fetch_assoc()) {

				$row['observaciones'] = $row['observaciones'];
				//$result[] = $row;
				fputcsv($fp, $row);
				//if($carlitos==0){var_dump($row);$carlitos=1;};
			}

			fclose($fp);

			$this->response($this->json(array($filename, $counter)), 200);
		}
		$this->response('', 203); // If no records "No Content" status
		//descarga archivo registros, forma asesores, activity feed
	}

	//Inicio Funcion Csvtecnico
	private function Csvtecnico() {
		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$usuarioid = $params['datosLogin'];
		$usuarioid = $usuarioid['LOGIN'];
		$datos = $params['datos'];
		$fechaini = $datos['fechaini'];
		$fechafin = $datos['fechafin'];

		if ($fechaini == "" && $fechafin == "") {
			$fechaini = date("Y") . "-" . date("m") . "-" . date("d");
			$fechafin = date("Y") . "-" . date("m") . "-" . date("d");
		}

		// Crea Nombre del Archivo con Fecha de Inicio, Fin y Loguin
		if ($fechaini == $fechafin) {
			$filename = "Cambio_Equipos" . "_" . $fechaini . ".csv";
		} else {
			$filename = "Cambio_Equipos" . "_" . $fechaini . "_" . $fechafin . "_" . $usuarioid . ".csv";
		}

		//Query donde se extraen los datos de la BD
		/* $query = "select " .
			"a.pedido as PEDIDO " .
			" ,a.id_tecnico as TECNICO " .
			" ,(select b.nombre from tecnicos b where b.identificacion=a.id_tecnico limit 1) as 'NOMBRE TECNICO' " .
			" ,(select b.ciudad from tecnicos b where b.identificacion=a.id_tecnico limit 1) as CIUDAD " .
			" ,a.empresa as EMPRESA " .
			" ,a.tipo_pendiente as 'TIPO PENDIENTE' " .
			" ,day(a.fecha) as DIA " .
			" ,month(a.fecha) as MES " .
			" ,year(a.fecha) as ANO " .
			" ,a.producto as PRODUCTO " .
			" ,a.plantilla AS PLANTILLA " .

			" from registros a where " .
			" a.fecha between '$fechaini 00:00:00' and '$fechafin 23:59:59' " .
			"AND  a.proceso='Reparaciones' " .
			"AND a.tipo_pendiente='Cambio de Equipo' "; */

		//(SELECT b.nombre FROM tecnicos b WHERE b.identificacion = a.id_tecnico limit 1) AS 'NOMBRE TECNICO',
		//(SELECT b.ciudad FROM tecnicos b WHERE b.identificacion = a.id_tecnico limit 1) AS CIUDAD,
		$query = "SELECT a.pedido AS PEDIDO, a.id_tecnico AS TECNICO, 
		t.nombre AS 'NOMBRE TECNICO',
		t.ciudad AS 'CIUDAD',
		a.empresa AS EMPRESA, 
		a.tipo_pendiente AS 'TIPO PENDIENTE', 
		a.accion AS 'ACCION', 
		DAY(a.fecha) AS DIA, 
		MONTH(a.fecha) AS MES, 
		YEAR(a.fecha) AS ANO, 
		a.producto AS PRODUCTO, 
		a.plantilla AS PLANTILLA,
		ce.hfc_equipo_sale AS MACSALE,
		ce.hfc_equipo_entra AS MACENTRA,
		a.proceso AS PROCESO
		FROM registros a 
		LEFT JOIN cambio_equipos ce ON ce.pedido = a.pedido
		LEFT JOIN tecnicos t ON t.identificacion = a.id_tecnico
		WHERE a.fecha BETWEEN '$fechaini 00:00:00' AND '$fechafin 23:59:59' AND (a.tipo_pendiente='Cambio de Equipo' OR a.accion='Cambio Equipo' OR a.tipo_pendiente='') AND a.proceso IN( 'Reparaciones', 'Instalaciones') AND (ce.hfc_equipo_sale IS NOT NULL OR ce.hfc_equipo_entra IS NOT NULL)
		GROUP BY a.pedido , a.id_tecnico , t.nombre , t.ciudad , a.empresa , a.tipo_pendiente , a.accion , DIA, MES, ANO, a.producto , a.plantilla , ce.hfc_equipo_sale , ce.hfc_equipo_entra , a.proceso;";

		// echo "$query\n";

		$stmt = $this->connseguimiento->query($query) or die($this->connseguimiento->error . __LINE__);

		$fp = fopen("../tmp/$filename", 'w');
		//echo $fp;

		/* $columnas = array('PEDIDO',
			'TECNICO',
			'NOMBRE_TECNICO',
			'CIUDAD',
			'EMPRESA',
			'TIPO_PENDIENTE',
			'DIA',
			'MES',
			'ANO',
			'PRODUCTO',
			//'PLANTILLA',
			'MOTIVO',
			'MAC_SALE',
			'MARCA_SALE',
			'REFERENCIA_SALE'); */

		$columnas = array('PEDIDO',
			'TECNICO',
			'NOMBRE_TECNICO',
			'CIUDAD',
			'EMPRESA',
			'TIPO_PENDIENTE',
			'DIA',
			'MES',
			'ANO',
			'PRODUCTO',
			//'PLANTILLA',
			'MOTIVO',
			'MAC_SALE',
			'MAC_ENTRA',
			'PROCESO'
		);

		fputcsv($fp, $columnas);

		//MODIFICACION EQUIPOS

		$totalinserts = 0;
		$totalignorados = 0;

		if ($stmt->num_rows > 0) {
			//////////////////Si existen datos que cumplen las condiciones

			while ($obj = $stmt->fetch_assoc()) {
				//////////////////Ciclo para cada pedido encontrado

				$pedido = $obj['PEDIDO'];
				$tecnico = $obj['TECNICO'];
				$nombre_tecnico = $obj['NOMBRE TECNICO'];
				$ciudad = $obj['CIUDAD'];
				$empresa = $obj['EMPRESA'];
				$tipo_pendiente = $obj['TIPO PENDIENTE'];

				$dia = $obj['DIA'];
				$mes = $obj['MES'];
				$ano = $obj['ANO'];
				$producto = $obj['PRODUCTO'];
				$plantilla = $obj['PLANTILLA'];

				$sep = ",";

				$plantilla2 = str_replace("*", ",", $plantilla);

				$pieces = explode($sep, $plantilla2);

				$size = count($pieces);

				$MOTIVO = "";
				/* $MAC_SALE = "";
				$MARCA_SALE = "";
				$REFERENCIA_SALE = ""; */

				$MAC_SALE = $obj['MACSALE'];
				$MAC_ENTRA = $obj['MACENTRA'];
				$PROCESO = $obj['PROCESO'];

				for ($i = 0; $i < $size; $i++) {
					//MOTIVO
					$bool = stripos($pieces[$i], 'Motivo');
					if ($bool === false) {
					} else {
						$tmp = explode(":", $pieces[$i]);
						$MOTIVO = trim(strtoupper($tmp[1]));
						continue;
					}

					//MAC SALE
					$bool = stripos($pieces[$i], 'Mac');
					if ($bool === false) {
					} else {
						//echo "DETECTO MAC!!! ".$pieces[$i]." - - ";
						$bool = stripos($pieces[$i], 'Sale');
						if ($bool === false) {
						} else {
							$tmp = explode(":", $pieces[$i]);

							$tmp[1] = str_replace(" ", "", $tmp[1]);

							if ($tmp[1] == "") {
								continue;
							}

							$MAC_SALE = trim(strtoupper($tmp[1]));
							continue;
						}
					}

					//CHIP SALE
					$bool = stripos($pieces[$i], 'chip');
					if ($bool === false) {
					} else {
						//echo "DETECTO MAC!!! ".$pieces[$i]." - - ";
						$bool = stripos($pieces[$i], 'Sale');
						if ($bool === false) {
						} else {
							$tmp = explode(":", $pieces[$i]);

							$tmp[1] = str_replace(" ", "", $tmp[1]);

							if ($tmp[1] == "") {
								continue;
							}

							$MAC_SALE = trim(strtoupper($tmp[1]));
							continue;
						}
					}

					//MARCA SALE
					$bool = stripos($pieces[$i], 'Marca');
					if ($bool === false) {
					} else {

						$bool = stripos($pieces[$i], 'Sale');
						if ($bool === false) {
						} else {
							$tmp = explode(":", $pieces[$i]);
							$tmp[1] = str_replace(" ", "", $tmp[1]);

							if ($tmp[1] == "") {
								continue;
							}

							$MARCA_SALE = trim(strtoupper($tmp[1]));
							continue;
						}
					}

					//REFERENCIA SALE
					$bool = stripos($pieces[$i], 'Referencia');
					if ($bool === false) {
					} else {

						$bool = stripos($pieces[$i], 'Sale');
						if ($bool === false) {
						} else {
							$tmp = explode(":", $pieces[$i]);
							$tmp[1] = str_replace(" ", "", $tmp[1]);

							if ($tmp[1] == "") {
								continue;
							}

							$REFERENCIA_SALE = trim(strtoupper($tmp[1]));
							continue;
						}
					}
					//echo " $pedido,$tecnico,$nombre_tecnico,$ciudad,$empresa,$tipo_pendiente,$dia,$mes,$ano,$producto,$MOTIVO, $MAC_SALE,$MARCA_SALE,$REFERENCIA_SALE \n";

				} //END FOR
				//echo " $pedido,$tecnico,$nombre_tecnico,$ciudad,$empresa,$tipo_pendiente,$dia,$mes,$ano,$producto,$MOTIVO, $MAC_SALE,$MARCA_SALE,$REFERENCIA_SALE \n";

				/* fputcsv($fp, array($pedido, $tecnico, $nombre_tecnico, $ciudad, $empresa, $tipo_pendiente, $dia, $mes, $ano, $producto, $MOTIVO, $MAC_SALE, $MARCA_SALE, $REFERENCIA_SALE)); */
				fputcsv($fp, array($pedido, $tecnico, $nombre_tecnico, $ciudad, $empresa, $tipo_pendiente, $dia, $mes, $ano, $producto, $MOTIVO, $MAC_SALE, $MAC_ENTRA, $PROCESO));

			} //ciclo grande
		}

		// FIN MODIFICACION EQUIPOS

		fclose($fp);

		$this->response($this->json(array($filename, $counter)), 200);
		//var_dump($stmt);
		//descarga archivo registros, forma asesores, activity feed
	}
	//Fin Funcion Csvtecnico

	private function insertarCambioEquipo() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		//$this->dbConnect();
		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$datos = $params['datoscambio'];
		$tecnologia = $params['tecnologia'];
		$pedido = $params['pedido'];
		//HFC-DTH
		$cuentaDomiciliaria = $datos['cuenta'];
		$IdCuenta = $datos['IdCuenta'];
		//todos
		$motivo = $datos['motivoCambio'];
		//DTH
		$chipSale = $datos['chipSale'];
		$chipEntra = $datos['chipEntra'];
		$SmartEntra = $datos['SmartEntra'];
		$SmartSale = $datos['SmartSale'];
		//HFC
		$servicioHFC = $datos['servicio'];
		$equipoEntraHFC = $datos['equipoEntra'];
		$macEntraHFC = $datos['macEntra'];
		$equipoSaleHFC = $datos['equipoSale'];
		$macSaleHFC = $datos['macSale'];
		//ADSL
		$Serialsale = $datos['Serialsale'];
		$Serialentra = $datos['Serialentra'];
		$Marcasale = $datos['Marcasale'];
		$Marcaentra = $datos['Marcaentra'];
		$Refsale = $datos['Refsale'];
		$Refentra = $datos['Refentra'];

		//    echo "pedido: ".$pedido;

		if ($tecnologia == "DTH") {
			$sql = " INSERT INTO cambio_equipos " .
				" (pedido, cuenta_domiciliaria, id_cuenta, motivo_cambio, tecnologia, dth_chip_sale, " .
				" dth_chip_entra, dth_smartcard_sale, dth_smartcard_entra) " .
				" values ( " .
				" '$pedido', '$cuentaDomiciliaria', '$IdCuenta', '$motivo', '$tecnologia', '$chipSale', " .
				" '$chipEntra', '$SmartSale', '$SmartEntra') ";
		}

		if ($tecnologia == "HFC") {
			$sql = " INSERT INTO cambio_equipos " .
				" (pedido, cuenta_domiciliaria, id_cuenta, tipo_servicio, motivo_cambio, tecnologia, hfc_equipo_sale, " .
				" hfc_equipo_entra, hfc_mac_voz_entra, hfc_mac_voz_sale) " .
				" values ( " .
				" '$pedido', '$cuentaDomiciliaria', '$IdCuenta', '$servicioHFC', '$motivo', '$tecnologia', '$equipoSaleHFC', " .
				" '$equipoEntraHFC', '$macEntraHFC', '$macSaleHFC') ";
		}
		if ($tecnologia == "ADSL") {
			$sql = " INSERT INTO cambio_equipos " .
				" (pedido, motivo_cambio, tecnologia, adsl_serial_sale, " .
				" adsl_serial_entra, adsl_marca_sale, adsl_marca_entra, adsl_ref_entra, adsl_ref_sale) " .
				" values ( " .
				" '$pedido', '$motivo', '$tecnologia', '$Serialsale', " .
				" '$Serialentra', '$Marcaentra', '$Marcasale', '$Refentra', '$Refsale') ";
		}

		$rst = $this->connseguimiento->query($sql);

		$sql1 = "select max(id) as id_insert from cambio_equipos";
		$rr = $this->connseguimiento->query($sql1);

		$counter = 0;
		if ($rr->num_rows > 0) {
			$result = array();
			if ($row = $rr->fetch_assoc()) {
				$counter = $row['id_insert'];
			}
		}
		$this->response($this->json(array($counter)), 200);

		//crea tecnico, activity feed
	}

	private function expBrutal() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$usuarioid = $params['datosLogin'];
		$usuarioid = $usuarioid['LOGIN'];
		$fechas = $params['fechas'];

		$datos = $params['datos'];
		$fechaini = $fechas['fechaini'];
		$fechafin = $fechas['fechafin'];
		$concepto = $datos['concepto'];
		$buscar = $datos['buscar'];

		$filename = "Registros" . "_" . $fechaini . "_" . $fechafin . "_" . $usuarioid . ".csv";

		$query = "select " .
			"PedidoDespacho, " .
			"AccionDespacho, LoginDespacho, " .
			"CorreoDespacho, " .
			"FechaGestionDespacho, ObservacionesDespacho, " .
			"CausaActividad, " .
			"estado,fechaInicioGestion, fechagestionAsesor, " .
			"SEC_TO_TIME((TIMESTAMPDIFF(MINUTE , fechaInicioGestion, fechagestionAsesor ))*60) AS Tiempo_Gestion_Asesor, " .
			"Asesor, ObservacionAsesor, " .
			"pedidoNuevo, numeroOferta, " .
			"fechaclick, " .
			"numeroIncidente, " .
			"actividadRealizaGrupo, " .
			"estadoFinalPedido, " .
			"tipoTransaccion, " .
			"zona, " .
			"ObservacionesFinales, canalVentas, " .
			"idLlamada " .
			"from BrutalForce " .
			"where FechaGestionDespacho between ('$fechaini 00:00:00') and ('$fechafin 23:59:59') ";

		//  echo $query;
		//
		/* $rr = $this->connseguimiento->query($queryCount);
	                    $counter=0;
	                        if($rr->num_rows > 0){
	                                $result = array();
	                                if($row = $rr->fetch_assoc()){
	                                        $counter = $row['Cantidad'];
	                                }
	                        }
		*/

		$rst = $this->connseguimiento->query($query) or die($this->connseguimiento->error . __LINE__);

		if ($rst->num_rows > 0) {

			//Insert en log
			//$sql_log="insert into gestor_despacho.gopd_logoperaciones (USUARIO_ID, TIPO_ACTIVIDAD, DESCRIPCION, IDENTIFICADOR, IP, PC) values(UPPER('$usuarioid'),'EXPORTE','EXPORTO CSV_HISTORICO','LOGIN: $usuarioid','$usuarioIp','$usuarioPc')";

			//$rlog = $this->connemtel->query($sql_log) or die($this->connemtel->error.__LINE__);
			//Insert en log

			$result = array();
			$fp = fopen("../tmp/$filename", 'w');
			//echo $fp;

			$columnas = array('PEDIDO',
				'ACCION',
				'DESPACHADOR',
				'CORREO_DESPACHADOR',
				'FECHA_DESPACHADOR',
				'OBSERVACIONES_DESPACHADOR',
				'CAUSA_ACTIVIDAD',
				'ESTADO',
				'FECHA_INICIO_GESTION_ASESOR',
				'FECHA_FIN_GESTION_ASESOR',
				'TIEMPO_GESTION_ASESOR',
				'ASESOR',
				'OBSERVACIONES_ASESOR',
				'NUEVO_PEDIDO',
				'NUMERO_OFERTA',
				'FECHA_CLICK',
				'NUMERO_INCIDENTE',
				'ACTIVIDAD_RELIZA_GRUPO',
				'ESTADO_FINAL_PEDIDO',
				'TIPO_TRANSACCION',
				'ZONA',
				'OBSERVACIONES_FINALES',
				'CANAL_VENTAS',
				'ID_LLAMADA');

			fputcsv($fp, $columnas);
			//$carlitos=0;
			while ($row = $rst->fetch_assoc()) {

				$row['ObservacionesDespacho'] = str_replace(",", ".", $row['ObservacionesDespacho']);
				$row['ObservacionAsesor'] = str_replace(",", ".", $row['ObservacionAsesor']);
				$row['ObservacionesFinales'] = str_replace(",", ".", $row['ObservacionesFinales']);

				$row['ObservacionesDespacho'] = str_replace(";", ".", $row['ObservacionesDespacho']);
				$row['ObservacionAsesor'] = str_replace(";", ".", $row['ObservacionAsesor']);
				$row['ObservacionesFinales'] = str_replace(";", ".", $row['ObservacionesFinales']);

				$row['ObservacionesDespacho'] = str_replace("\n", " ", $row['ObservacionesDespacho']);
				$row['ObservacionAsesor'] = str_replace("\n", " ", $row['ObservacionAsesor']);
				$row['ObservacionesFinales'] = str_replace("\n", " ", $row['ObservacionesFinales']);

				//$result[] = $row;
				fputcsv($fp, $row);
				//if($carlitos==0){var_dump($row);$carlitos=1;};
			}

			fclose($fp);

			$this->response($this->json(array($filename)), 200);
		}
		$this->response('', 203); // If no records "No Content" status
		//descarga archivo registros, forma asesores, activity feed
	}

	private function pendiBrutal() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$fecha = date("Y-m-d");

		$Sqlpendientes = "select distinct pro.PEDIDO_CRM, c.departamento, (select descripcion " .
			"from codigo_pendientes_click  " .
			"where codigo = pro.codigo_pendiente_incompleto) descripcion, c.observacion " .
			"from agendasDia pro  " .
			"join (SELECT a.departamento, pedido_id, observacion " .
			"FROM carga_click a  " .
			"where a.fecha_cita BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') " .
			"and uen = 'HG' ) c   " .
			"on c.pedido_id = pro.PEDIDO_CRM " .
			"and FECHA_CITA = '$fecha' " .
			"where pro.fecha_cita = '$fecha'  " .
			"and pro.tipo_trabajo = 'NUEVO' " .
			"and pro.ESTADO_CLICK not in ( 'Cancelado', 'Finalizada') " .
			"and pro.CODIGO_PENDIENTE_INCOMPLETO in ('O-53', " .
			"'OT-C06', 'O-101', 'OT-T05', 'OT-C12' ,'OT-T04') ";

		$rstpendientesBrutal = $this->connseguimiento->query($Sqlpendientes);

		$pedidospendi = array();
		while ($row = $rstpendientesBrutal->fetch_assoc()) {
			//////////////////Valido cada pedido en click
			$pedido_id = $row['PEDIDO_CRM'];
			$pendiente = $row['descripcion'];
			$departamento = $row['departamento'];
			$observacion = utf8_encode($row['observacion']);

			//////////////////Consulta en click si el pedido encontrado en pendi_insta se encuentra en estado Finalizada
			$sqlbuscarBrutal = "select PedidoDespacho " .
				"from BrutalForce  " .
				"where PedidoDespacho = '$pedido_id'; ";

			$rstbusqueda = $this->connseguimiento->query($sqlbuscarBrutal);

			if ($row1 = $rstbusqueda->fetch_assoc()) {
				//$pedidospendiOK[] = array("pedido" => "$pedido_id");
				$pedidospendi[] = array("pedido" => "$pedido_id");
			} else {
				//$pedidospendiNO[] = array("pedido" => "$pedido_id", "pendiente" => "$pendiente", "departamento" => "$departamento", "observacion" => "$observacion");
				$pedidospendi[] = array("pedido" => "$pedido_id", "pendiente" => "$pendiente", "departamento" => "$departamento", "observacion" => "$observacion");
			}
		}
		$this->response($this->json(array($pedidospendi)), 201);
	}

	private function DashBoard() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();
		// $fechaActual = date("Y")."-".date("m")."-".date("d")."-".date("h")."-".date("i")."-".date("s");

		$queryEnGestion = " SELECT PedidoDespacho, Asesor, AccionDespacho, FechaGestionDespacho, " .
			"SEC_TO_TIME((TIMESTAMPDIFF(SECOND , FechaGestionDespacho, NOW() ))) AS TiempoSaveDespacho, " .
			"SEC_TO_TIME((TIMESTAMPDIFF(SECOND , fechaInicioGestion, NOW() ))) AS TiempoConGestor,  " .
			"SEC_TO_TIME((TIMESTAMPDIFF(SECOND , FechaGestionDespacho, fechaInicioGestion ))) AS Despacho_Asesor, " .
			" (select count(PedidoDespacho) from BrutalForce  " .
			"	where pedidobloqueado = '1' " .
			"	and gestionAsesor = '1' ) total " .
			"from BrutalForce  " .
			"where pedidobloqueado = '1' " .
			"and gestionAsesor = '1'  order by FechaGestionDespacho ";
		//echo $queryEnGestion;
		$rstEnGestion = $this->connseguimiento->query($queryEnGestion);

		if ($rstEnGestion->num_rows > 0) {

			$enGestion = array();

			while ($row = $rstEnGestion->fetch_assoc()) {
				$row['AccionDespacho'] = utf8_encode($row['AccionDespacho']);
				$enGestion[] = $row;
			}
		}

		$querySin_gestion = "SELECT PedidoDespacho, FechaGestionDespacho, AccionDespacho, " .
			"SEC_TO_TIME((TIMESTAMPDIFF(SECOND , FechaGestionDespacho, NOW() ))) AS Despacho_Asesor, " .
			" (select count(PedidoDespacho) from BrutalForce  " .
			"where pedidobloqueado is null " .
			"and gestionAsesor = '1' ) total " .
			"from BrutalForce " .
			"where pedidobloqueado is null " .
			"and gestionAsesor = '1'  order by FechaGestionDespacho ";

		$rstSin_gestion = $this->connseguimiento->query($querySin_gestion);

		if ($rstSin_gestion->num_rows > 0) {

			$Sin_gestion = array();

			while ($row = $rstSin_gestion->fetch_assoc()) {
				$row['AccionDespacho'] = utf8_encode($row['AccionDespacho']);
				$Sin_gestion[] = $row;
			}
		}

		$queryEscalados = "SELECT PedidoDespacho, " .
			"(case when locate('/',REVERSE(estado)) = 0 then estado " .
			"else  right(estado,locate('/',REVERSE(estado))-1) " .
			"end) estado , " .
			"numeroOferta,asesor,FechaGestionDespacho, AccionDespacho, " .
			"AccionDespacho,SEC_TO_TIME((TIMESTAMPDIFF(SECOND , FechaGestionDespacho, NOW() ))) AS Tiempo,  " .
			"(select count(PedidoDespacho) from BrutalForce   " .
			"where  pedidobloqueado in ('1','2') " .
			"and gestionAsesor in ('2', '3') " .
			"and estado not like '%Finalizado%' ) total  " .
			"from BrutalForce   " .
			"where  pedidobloqueado in ('1','2') " .
			"and gestionAsesor in ('2', '3') and estado not like '%Finalizado%'  order by FechaGestionDespacho ";

		$rstEscalados = $this->connseguimiento->query($queryEscalados);

		if ($rstEscalados->num_rows > 0) {

			$Escalados = array();

			while ($row = $rstEscalados->fetch_assoc()) {
				$row['AccionDespacho'] = utf8_encode($row['AccionDespacho']);
				$Escalados[] = $row;
			}
		}

		$queryPendiente_analisis = "SELECT PedidoDespacho, Asesor, FechaGestionDespacho, fechagestionAsesor, AccionDespacho,  " .
			"SEC_TO_TIME((TIMESTAMPDIFF(SECOND , fechagestionAsesor, NOW() ))) AS Despacho_Asesor, " .
			"	(select count(PedidoDespacho) from BrutalForce   " .
			"where ObservacionesFinales is null " .
			"and gestionAsesor = '2' " .
			"and estado like '%Finalizado%' ) total " .
			"	from BrutalForce  " .
			"where ObservacionesFinales is null " .
			"and gestionAsesor = '2' " .
			"and estado like '%Finalizado%'  order by FechaGestionDespacho ";

		//  $rstqueryPendiente_analisis = $this->connseguimiento->query($queryPendiente_analisis);

		if ($rstqueryPendiente_analisis->num_rows > 0) {

			$Pendiente_analisis = array();

			while ($row = $rstqueryPendiente_analisis->fetch_assoc()) {
				$row['AccionDespacho'] = utf8_encode($row['AccionDespacho']);
				$Pendiente_analisis[] = $row;
			}
		}

		$escaladosCalidad = "SELECT PedidoDespacho, " .
			"(case when locate('/',REVERSE(estado)) = 0 then estado " .
			"else  right(estado,locate('/',REVERSE(estado))-1) " .
			"end) estado , " .
			"numeroOferta,asesor,FechaGestionDespacho, AccionDespacho, " .
			"AccionDespacho,SEC_TO_TIME((TIMESTAMPDIFF(SECOND , FechaGestionDespacho, NOW() ))) AS Tiempo,  " .
			"(select count(PedidoDespacho) from BrutalForce   " .
			"where  pedidobloqueado in ('1','2') " .
			"and gestionAsesor in ('2', '3') " .
			"and estado not like '%Finalizado%' and SEC_TO_TIME((TIMESTAMPDIFF(SECOND , FechaGestionDespacho, NOW() ))) > '00:40:00' ) totalescalaCalidad  " .
			"from BrutalForce   " .
			"where  pedidobloqueado in ('1','2') " .
			"and gestionAsesor in ('2', '3') and estado not like '%Finalizado%' " .
			"and SEC_TO_TIME((TIMESTAMPDIFF(SECOND , FechaGestionDespacho, NOW() ))) > '00:40:00' " .
			"order by FechaGestionDespacho ";

		$rstEscaladosCalidad = $this->connseguimiento->query($escaladosCalidad);

		if ($rstEscaladosCalidad->num_rows > 0) {

			$EscaladosCalidad = array();

			while ($row = $rstEscaladosCalidad->fetch_assoc()) {
				$row['AccionDespacho'] = utf8_encode($row['AccionDespacho']);
				$EscaladosCalidad[] = $row;
			}
		}

		$queryEnGestionCalidad = " SELECT PedidoDespacho, Asesor, AccionDespacho, FechaGestionDespacho, " .
			"SEC_TO_TIME((TIMESTAMPDIFF(SECOND , FechaGestionDespacho, NOW() ))) AS TiempoSaveDespacho, " .
			"SEC_TO_TIME((TIMESTAMPDIFF(SECOND , fechaInicioGestion, NOW() ))) AS TiempoConGestor,  " .
			"SEC_TO_TIME((TIMESTAMPDIFF(SECOND , FechaGestionDespacho, fechaInicioGestion ))) AS Despacho_Asesor, " .
			" (select count(PedidoDespacho) from BrutalForce  " .
			"	where pedidobloqueado = '1' " .
			"	and gestionAsesor = '1' and SEC_TO_TIME((TIMESTAMPDIFF(SECOND , FechaGestionDespacho, NOW() ))) > '00:40:00' ) totalcalidad " .
			"from BrutalForce  " .
			"where pedidobloqueado = '1' " .
			"and gestionAsesor = '1'  " .
			"and SEC_TO_TIME((TIMESTAMPDIFF(SECOND , FechaGestionDespacho, NOW() ))) > '00:40:00' " .
			"order by FechaGestionDespacho ";
		//echo $queryEnGestion;
		$rstEnGestionCalidad = $this->connseguimiento->query($queryEnGestionCalidad);

		if ($rstEnGestionCalidad->num_rows > 0) {

			$enGestion = array();

			while ($row = $rstEnGestionCalidad->fetch_assoc()) {
				$row['AccionDespacho'] = utf8_encode($row['AccionDespacho']);
				$enGestionCalidad[] = $row;
			}
		}

		$queryAsesoresCalidad = "SELECT PedidoDespacho, Asesor, CausaActividad, pedidoNuevo, numeroOferta, " .
			"fechaclick,  " .
			"estado, numeroIncidente, actividadRealizaGrupo, estadoFinalPedido,  " .
			"AccionDespacho, " .
			"FechaGestionDespacho, ObservacionAsesor, ObservacionesDespacho,  " .
			"CorreoDespacho, ObservacionesFinales,  " .
			"SEC_TO_TIME((TIMESTAMPDIFF(SECOND , FechaGestionDespacho, NOW() )))  " .
			"AS TiempoSaveDespacho,  " .
			"(select count(PedidoDespacho) from BrutalForce where gestionAsesor   " .
			"in ('2', '3') and estadoFinalPedido = 'Pendiente'  " .
			"and pedidobloqueado <> '2'  " .
			"and AccionDespacho <> 'Anulación' and SEC_TO_TIME((TIMESTAMPDIFF(SECOND ,  " .
			"FechaGestionDespacho,  " .
			"NOW() ))) > '00:40:00') total " .
			"from BrutalForce   " .
			"where gestionAsesor  in ('2', '3') and estadoFinalPedido = 'Pendiente'  " .
			"and pedidobloqueado <> '2'  " .
			"and AccionDespacho <> 'Anulación' " .
			"and SEC_TO_TIME((TIMESTAMPDIFF(SECOND , FechaGestionDespacho,  " .
			"NOW() ))) > '00:40:00' " .
			"order by FechaGestionDespacho ";

		$rstAsesoresCalidad = $this->connseguimiento->query(utf8_decode($queryAsesoresCalidad));

		if ($rstAsesoresCalidad->num_rows > 0) {

			$resultadoAsesoresCalidad = array();

			while ($row = $rstAsesoresCalidad->fetch_assoc()) {

				$row['CausaActividad'] = utf8_encode($row['CausaActividad']);
				$row['actividadRealizaGrupo'] = utf8_encode($row['actividadRealizaGrupo']);
				$row['estadoFinalPedido'] = utf8_encode($row['estadoFinalPedido']);
				$row['ObservacionAsesor'] = utf8_encode($row['ObservacionAsesor']);
				$row['ObservacionesDespacho'] = utf8_encode($row['ObservacionesDespacho']);
				$row['ObservacionesFinales'] = utf8_encode($row['ObservacionesFinales']);
				$resultadoAsesoresCalidad[] = $row;
			}
		}

		$this->response($this->json(array($enGestion, $Sin_gestion, $Escalados, $Pendiente_analisis, $EscaladosCalidad, $enGestionCalidad, $resultadoAsesoresCalidad)), 201);
	}

	private function gestionFinal() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$queryDespacho = " SELECT  PedidoDespacho, AccionDespacho, CorreoDespacho, Asesor, " .
			"ObservacionesDespacho, FechaGestionDespacho, tipoTransaccion, zona,   " .
			"LoginDespacho, idLlamada, supervisor from BrutalForce where gestionAsesor = '1' " .
			"order by fechaInicioGestion DESC";

		$rst = $this->connseguimiento->query($queryDespacho);

		if ($rst->num_rows > 0) {

			$resultado = array();

			while ($row = $rst->fetch_assoc()) {

				$row['AccionDespacho'] = utf8_encode($row['AccionDespacho']);
				$row['ObservacionesDespacho'] = utf8_encode($row['ObservacionesDespacho']);
				$row['tipoTransaccion'] = utf8_encode($row['tipoTransaccion']);
				$row['zona'] = utf8_encode($row['zona']);
				$row['supervisor'] = utf8_encode($row['supervisor']);
				$resultado[] = $row;
			}
		}

		$hora = date("H");

		if ($hora >= 10) {

			$hoy = date("Y-m-d");
			$nuevafecha = strtotime('-1 day', strtotime($hoy));
			$nuevafecha = date('Y-m-j', $nuevafecha);
			$horaIni = " 00:00:00";
			$horaFin = " 23:59:59";
			$diaAnteriorIni = $nuevafecha . $horaIni;
			$diaAnteriorFin = $nuevafecha . $horaFin;

			$query = "SELECT idGestion " .
				"from BrutalForce  " .
				"where gestionAsesor  in ('2', '3')  " .
				"and estadoFinalPedido = 'Pendiente'  " .
				"and ObservacionesFinales is not null " .
				"and fechagestionAsesor between ('$diaAnteriorIni') and ('$diaAnteriorFin') " .
				"order by fechagestionAsesor ";

			$rstResumen = $this->connseguimiento->query($query);

			if ($rstResumen->num_rows > 0) {

				while ($row = $rstResumen->fetch_assoc()) {

					$idgestionAsesor = $row['idGestion'];

					$query = "UPDATE BrutalForce SET " .
						" pedidobloqueado = '2' " .
						"WHERE idGestion='$idgestionAsesor' ";

					//	echo $query;
					$uptdate = $this->connseguimiento->query($query);
				}
			}
		}

		$queryAsesores = "SELECT PedidoDespacho, Asesor, CausaActividad, pedidoNuevo, numeroOferta, fechaclick, estado, " .
			"numeroIncidente, actividadRealizaGrupo, estadoFinalPedido,  " .
			"fechagestionAsesor, ObservacionAsesor, ObservacionesDespacho, CorreoDespacho, ObservacionesFinales from BrutalForce  " .
			"where gestionAsesor  in ('2', '3') and estadoFinalPedido = 'Pendiente' " .
			"and pedidobloqueado <> '2' " .
			"order by fechagestionAsesor ";

		$rstAsesores = $this->connseguimiento->query($queryAsesores);

		if ($rstAsesores->num_rows > 0) {

			$resultadoAsesores = array();

			while ($row = $rstAsesores->fetch_assoc()) {

				$row['CausaActividad'] = utf8_encode($row['CausaActividad']);
				$row['actividadRealizaGrupo'] = utf8_encode($row['actividadRealizaGrupo']);
				$row['estadoFinalPedido'] = utf8_encode($row['estadoFinalPedido']);
				$row['ObservacionAsesor'] = utf8_encode($row['ObservacionAsesor']);
				$row['ObservacionesDespacho'] = utf8_encode($row['ObservacionesDespacho']);
				$row['ObservacionesFinales'] = utf8_encode($row['ObservacionesFinales']);
				$resultadoAsesores[] = $row;
			}

			$this->response($this->json(array($resultado, $resultadoAsesores)), 201);
		} else {
			$error = array();

			$this->response($this->json($error), 400);
		}
	}

	private function BuscarPedidoBrutal() {

		if ($this->get_request_method() != "GET") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$pedido = $this->_request['pedido'];

		$query = " SELECT PedidoDespacho, Asesor, CausaActividad, pedidoNuevo, numeroOferta, fechaclick, AccionDespacho, " .
			"numeroIncidente, actividadRealizaGrupo, estado, tipoTransaccion, zona, " .
			"fechagestionAsesor, ObservacionAsesor, ObservacionesDespacho, estadoFinalPedido, CorreoDespacho, idLlamada " .
			" from BrutalForce " .
			"where PedidoDespacho = '$pedido' and estado not like '%Finalizado%'";

		$rst = $this->connseguimiento->query($query);

		//echo $this->mysqli->query($sqlLogin);
		//
		if ($rst->num_rows > 0) {

			$resultado = array();

			while ($row = $rst->fetch_assoc()) {

				$row['AccionDespacho'] = utf8_encode($row['AccionDespacho']);
				$row['ObservacionesDespacho'] = utf8_encode($row['ObservacionesDespacho']);
				$row['tipoTransaccion'] = utf8_encode($row['tipoTransaccion']);
				$row['estado'] = utf8_encode($row['estado']);
				$row['zona'] = utf8_encode($row['zona']);

				$resultado[] = $row;
			}
			$this->response($this->json(array($resultado)), 201);
		} else {
			$error = array();

			$this->response($this->json($error), 400);
		}
	}

	private function Pendientesxestado() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$usuarioid = $params['login'];
		$login = $usuarioid['LOGIN'];
		$datos = $params['datos'];
		$estado = $datos['estado'];

		$query = "select PedidoDespacho " .
			"from BrutalForce " .
			"where Asesor = '$login' " .
			"and estado = '$estado' ";

		$rst = $this->connseguimiento->query($query);

		//echo $this->mysqli->query($sqlLogin);
		//
		if ($rst->num_rows > 0) {

			$resultado = array();

			while ($row = $rst->fetch_assoc()) {

				$resultado[] = $row;
			}
			$this->response($this->json(array($resultado)), 201);
		} else {
			$error = array();

			$this->response($this->json($error), 400);
		}
	}

	private function gestionPendientes() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$usuarioid = $params['login'];
		$login = $usuarioid['LOGIN'];

		$query = "select estado, count(estado) total " .
			"from BrutalForce " .
			"where Asesor = '$login' and estado is not null  and estado not like '%Finalizado%' " .
			"group by estado order by count(estado) DESC ";

		$rst = $this->connseguimiento->query($query);

		$queryAnulados = "SELECT count(PedidoDespacho) Cantidad " .
			"from BrutalForce where gestionAsesor = '1'  " .
			"and (pedidobloqueado is null  or pedidobloqueado = '0')  " .
			"and AccionDespacho = 'Anulación' ";

		$rstAnulados = $this->connseguimiento->query(utf8_decode($queryAnulados));
		//echo $this->mysqli->query($sqlLogin);

		$counter = 0;
		if ($rstAnulados->num_rows > 0) {
			$result = array();
			if ($row = $rstAnulados->fetch_assoc()) {
				$counter = $row['Cantidad'];
			}
		}
		if ($rst->num_rows > 0 || $counter !== 0) {

			$resultado = array();

			while ($row = $rst->fetch_assoc()) {
				$row['estado'] = utf8_encode($row['estado']);
				$resultado[] = $row;
			}
			$this->response($this->json(array($resultado, $counter)), 201);
		} else {
			$this->response($this->json($error), 400);
		}
	}

	private function gestionBrutal() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$usuarioid = $params['login'];
		$accion = $params['accion'];
		$login = $usuarioid['LOGIN'];

		$fechaIniciogestionAsesor = date('Y-m-d H:i:s');

		if ($accion == "Anulado") {
			$resultado = array();

			$query = " SELECT idGestion, PedidoDespacho, AccionDespacho, CorreoDespacho, " .
				"ObservacionesDespacho, FechaGestionDespacho, tipoTransaccion, zona,  " .
				"LoginDespacho, idLlamada, supervisor from BrutalForce where gestionAsesor = '1' " .
				"and (pedidobloqueado is null  or pedidobloqueado = '0') " .
				"and AccionDespacho = 'Anulación' " .
				"order by FechaGestionDespacho ASC limit 1";

			$rst = $this->connseguimiento->query(utf8_decode($query));

			if ($rst->num_rows > 0) {

				while ($row = $rst->fetch_assoc()) {

					$idgestionAsesor = $row['idGestion'];

					$query = "UPDATE BrutalForce SET " .
						" pedidobloqueado = '1', Asesor = '$login', fechaInicioGestion = '$fechaIniciogestionAsesor' " .
						"WHERE idGestion='$idgestionAsesor' ";

					//	echo $query;
					$uptdate = $this->connseguimiento->query($query);
					$row['AccionDespacho'] = utf8_encode($row['AccionDespacho']);
					$row['ObservacionesDespacho'] = utf8_encode($row['ObservacionesDespacho']);
					$row['tipoTransaccion'] = utf8_encode($row['tipoTransaccion']);
					$row['zona'] = utf8_encode($row['zona']);
					$resultado[] = $row;
				}
				$this->response($this->json(array($resultado)), 201);

			} else {
				$this->response($this->json($error), 400);
			}
		} else {

			$query = " SELECT idGestion, PedidoDespacho, AccionDespacho, CorreoDespacho, " .
				"ObservacionesDespacho, FechaGestionDespacho, tipoTransaccion, zona,  " .
				"LoginDespacho, idLlamada, supervisor from BrutalForce where gestionAsesor = '1' " .
				"and (pedidobloqueado is null  or pedidobloqueado = '0') " .
				"and AccionDespacho <> 'Anulación' " .
				"order by FechaGestionDespacho ASC limit 1";

			$rst = $this->connseguimiento->query(utf8_decode($query));

			if ($rst->num_rows > 0) {
				$resultado = array();
				while ($row = $rst->fetch_assoc()) {

					$idgestionAsesor = $row['idGestion'];

					$query = "UPDATE BrutalForce SET " .
						" pedidobloqueado = '1', Asesor = '$login', fechaInicioGestion = '$fechaIniciogestionAsesor' " .
						"WHERE idGestion='$idgestionAsesor' ";

					//	echo $query;
					$uptdate = $this->connseguimiento->query($query);
					$row['AccionDespacho'] = utf8_encode($row['AccionDespacho']);
					$row['ObservacionesDespacho'] = utf8_encode($row['ObservacionesDespacho']);
					$row['tipoTransaccion'] = utf8_encode($row['tipoTransaccion']);
					$row['zona'] = utf8_encode($row['zona']);
					$resultado[] = $row;
				}
				$this->response($this->json(array($resultado)), 201);
			} else {
				$resultado = array();

				$query = " SELECT idGestion, PedidoDespacho, AccionDespacho, CorreoDespacho, " .
					"ObservacionesDespacho, FechaGestionDespacho, tipoTransaccion, zona,  " .
					"LoginDespacho, idLlamada, supervisor from BrutalForce where gestionAsesor = '1' " .
					"and (pedidobloqueado is null  or pedidobloqueado = '0') " .
					"and AccionDespacho = 'Anulación' " .
					"order by FechaGestionDespacho ASC limit 1";

				$rst = $this->connseguimiento->query(utf8_decode($query));

				if ($rst->num_rows > 0) {

					while ($row = $rst->fetch_assoc()) {

						$idgestionAsesor = $row['idGestion'];

						$query = "UPDATE BrutalForce SET " .
							" pedidobloqueado = '1', Asesor = '$login', fechaInicioGestion = '$fechaIniciogestionAsesor' " .
							"WHERE idGestion='$idgestionAsesor' ";

						//	echo $query;
						$uptdate = $this->connseguimiento->query($query);
						$row['AccionDespacho'] = utf8_encode($row['AccionDespacho']);
						$row['ObservacionesDespacho'] = utf8_encode($row['ObservacionesDespacho']);
						$row['tipoTransaccion'] = utf8_encode($row['tipoTransaccion']);
						$row['zona'] = utf8_encode($row['zona']);
						$resultado[] = $row;
					}
					$this->response($this->json(array($resultado)), 201);

				} else {
					$this->response($this->json($error), 400);
				}
			}
		}
	}

	private function gestionAsesorBrutal() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		//$this->dbConnect();
		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$fechagestionAsesor = date('Y-m-d H:i:s');

		$infogestion = $params['datosguardar'];
		$datosDespacho = $params['datosDespacho'];

		$idGestion = $datosDespacho['PedidoDespacho'];
		$PedidoDespacho = $datosDespacho['PedidoDespacho'];
		$AccionDespacho = $datosDespacho['AccionDespacho'];
		$CorreoDespacho = $datosDespacho['CorreoDespacho'];
		$ObservacionesDespacho = $datosDespacho['ObservacionesDespacho'];
		$FechaGestionDespacho = $datosDespacho['FechaGestionDespacho'];
		$tipoTransaccion = $datosDespacho['tipoTransaccion'];
		$zona = utf8_decode($datosDespacho['zona']);
		$LoginDespacho = $datosDespacho['LoginDespacho'];

		$causaActividad = utf8_decode($infogestion['causaActividad']);
		$estado = utf8_decode($infogestion['estado']);
		$pedidoNuevo = $infogestion['pedidoNuevo'];
		$fechaClick = $infogestion['fechaClick'];
		$numeroOferta = $infogestion['numeroOferta'];
		$actividad = utf8_decode($infogestion['actividad']);
		$canal = utf8_decode($infogestion['canal']);
		$incidente = $infogestion['incidente'];
		$observaciones = utf8_decode($infogestion['observaciones']);

		$usuarioid = $params['login'];
		$login = $usuarioid['LOGIN'];

		$query = "select idgestion from BrutalForce where PedidoDespacho = '$PedidoDespacho' ";

		$rst = $this->connseguimiento->query($query);

		if ($rst->num_rows > 0) {

			$resultado = array();

			while ($row = $rst->fetch_assoc()) {
				$resultado[] = $row;
				$id = $row['idgestion'];
			}

			$sqlupdate = "UPDATE BrutalForce SET gestionAsesor='2', Asesor='$login', CausaActividad = '$causaActividad', canalVentas = '$canal', " .
				" pedidoNuevo = '$pedidoNuevo', numeroOferta='$numeroOferta', estado = '$estado',  fechaclick =STR_TO_DATE('$fechaClick', '%d/%m/%Y %T'), " .
				" numeroIncidente = '$incidente',  actividadRealizaGrupo ='$actividad', numeroOferta ='$numeroOferta', " .
				" ObservacionAsesor = '$observaciones', fechagestionAsesor = '$fechagestionAsesor', estadoFinalPedido = 'Pendiente' " .
				" WHERE idgestion='$id' ";

			$rstupdate = $this->connseguimiento->query($sqlupdate);

			$this->response($this->json(array()), 200);
		}
	}

	private function updateEnGestion() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		$params = json_decode(file_get_contents('php://input'), true);

		$login = $params['login'];
		$login = $login['LOGIN'];

		$this->dbSeguimientoConnect();

		$hoy = date("Y-m-d");

		$query = ("
					SELECT id, engestion, pedido, observContingencia, observContingenciaPortafolio,
						(case when acepta is null then 'Pendiente' else acepta end) acepta,
						(case when aceptaPortafolio is null then 'Pendiente' else aceptaPortafolio end) aceptaPortafolio
						FROM contingencias
						WHERE logindepacho = '$login'
						AND horagestion BETWEEN ('$hoy 00:00:00') AND ('$hoy 23:59:59')
				");

		$rst = $this->connseguimiento->query(utf8_decode($query));

		if ($rst->num_rows > 0) {

			$resultado = array();

			while ($row = $rst->fetch_assoc()) {
				$row['observContingencia'] = utf8_encode($row['observContingencia']);
				$row['observContingenciaPortafolio'] = utf8_encode($row['observContingenciaPortafolio']);
				$resultado[] = $row;
			}
			$this->response($this->json(array($resultado)), 201);
		} else {

			$error = array();

			$this->response($this->json($error), 400);
		}
	}

	private function buscarPedidoContingencias() {

		if ($this->get_request_method() != "GET") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$pedido = $this->_request['pedido'];

		if ($pedido !== "") {

			$query = ("SELECT pedido, accion, ciudad, correo, macEntra, macSale, paquetes, motivo, proceso, producto, contrato, perfil,
						horagestion, logindepacho,	logincontingencia, loginContingenciaPortafolio, horacontingencia, horaContingenciaPortafolio,
						tipoEquipo, tecnologia, remite, tipificacion, tipificacionPortafolio, acepta, aceptaPortafolio, observacion, observContingencia,
						observContingenciaPortafolio, ingresoEquipos
						FROM contingencias
						WHERE pedido = '$pedido'
					");

			$rst = $this->connseguimiento->query($query);

			if ($rst->num_rows > 0) {
				$resultado = array();
				while ($row = $rst->fetch_assoc()) {

					$row['pedido'] = utf8_encode($row['pedido']);
					$row['accion'] = utf8_encode($row['accion']);
					$row['ciudad'] = utf8_encode($row['ciudad']);
					$row['correo'] = utf8_encode($row['correo']);
					$row['macEntra'] = utf8_encode($row['macEntra']);
					$row['macSale'] = utf8_encode($row['macSale']);
					$row['paquetes'] = utf8_encode($row['paquetes']);
					$row['motivo'] = utf8_encode($row['motivo']);
					$row['proceso'] = utf8_encode($row['proceso']);
					$row['producto'] = utf8_encode($row['producto']);
					$row['contrato'] = utf8_encode($row['contrato']);
					$row['perfil'] = utf8_encode($row['perfil']);
					$row['logindepacho'] = utf8_encode($row['logindepacho']);
					$row['logincontingencia'] = utf8_encode($row['logincontingencia']);
					$row['loginContingenciaPortafolio'] = utf8_encode($row['loginContingenciaPortafolio']);
					$row['tipoEquipo'] = utf8_encode($row['tipoEquipo']);
					$row['tecnologia'] = utf8_encode($row['tecnologia']);
					$row['remite'] = utf8_encode($row['remite']);
					$row['tipificacion'] = utf8_encode($row['tipificacion']);
					$row['tipificacionPortafolio'] = utf8_encode($row['tipificacionPortafolio']);
					$row['acepta'] = utf8_encode($row['acepta']);
					$row['aceptaPortafolio'] = utf8_encode($row['aceptaPortafolio']);
					$row['observacion'] = utf8_encode($row['observacion']);
					$row['observContingencia'] = utf8_encode($row['observContingencia']);
					$row['observContingenciaPortafolio'] = utf8_encode($row['observContingenciaPortafolio']);
					$row['ingresoEquipos'] = utf8_encode($row['ingresoEquipos']);
					$resultado[] = $row;

				}

				$this->response($this->json(array($resultado)), 201);
			} else {
				$error = array();
				$this->response($this->json($error), 400);
			}
		} else {
			$error = array();
			$this->response($this->json($error), 400);
		}
	}

	private function datoscontingencias() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		/*CONEXION A LA BASE DE DATOS*/
		$this->dbSeguimientoConnect();

		$hoy = date("Y-m-d H:i:s");

		/*LOGICA QUE LLEVA LA INFORMACION A TV, INTERNET-ToIp Y CORREGIR PORTAFOLIO*/
		$query = ("SELECT c.pedido, c.macEntra, c.macSale, c.logincontingencia, c.paquetes, c.ciudad, c.proceso, c.accion, c.tipoEquipo, c.remite, c.observacion, 
					c.engestion, c.producto, c.grupo, c.horagestion, c.perfil, c.tipificacion, c.acepta, c.loginContingenciaPortafolio, c.aceptaPortafolio, 
					c.tipificacionPortafolio, c.enGestionPortafolio, c.fechaClickMarcaPortafolio, c.id_terreno, CASE WHEN (SELECT COUNT(*)
					FROM contingencias c1
					WHERE c1.pedido=c.pedido AND c1.horagestion >= DATE_SUB(CURDATE(), INTERVAL 10 DAY) AND c1.finalizado = 'OK') > 0 THEN 'TRUE' ELSE 'FALSE' END alerta
				FROM contingencias c
				WHERE c.finalizado IS NULL AND c.finalizadoPortafolio IS NULL AND c.pedido <> ''
				ORDER BY c.horagestion ASC");

		$rst = $this->connseguimiento->query($query);

		if ($rst->num_rows > 0) {

			$resultadoTV = array();
			$resultadoOTROS = array();
			$resultadoPORTAFOLIO = array();
			while ($row = $rst->fetch_assoc()) {

				$row['pedido'] = utf8_encode($row['pedido']);
				$row['macEntra'] = utf8_encode($row['macEntra']);
				$row['macSale'] = utf8_encode($row['macSale']);
				$row['paquetes'] = utf8_encode($row['paquetes']);
				$row['ciudad'] = utf8_encode($row['ciudad']);
				$row['proceso'] = utf8_encode($row['proceso']);
				$row['accion'] = utf8_encode($row['accion']);
				$row['tipoEquipo'] = utf8_encode($row['tipoEquipo']);
				$row['remite'] = utf8_encode($row['remite']);
				$row['observacion'] = utf8_encode($row['observacion']);
				$row['engestion'] = utf8_encode($row['engestion']);
				$row['producto'] = utf8_encode($row['producto']);
				$row['grupo'] = utf8_encode($row['grupo']);
				$row['horagestion'] = utf8_encode($row['horagestion']);
				$row['perfil'] = utf8_encode($row['perfil']);
				$row['acepta'] = utf8_encode($row['acepta']);
				$row['loginContingenciaPortafolio'] = utf8_encode($row['loginContingenciaPortafolio']);
				$row['aceptaPortafolio'] = utf8_encode($row['aceptaPortafolio']);
				$row['tipificacionPortafolio'] = utf8_encode($row['tipificacionPortafolio']);
				$row['enGestionPortafolio'] = utf8_encode($row['enGestionPortafolio']);
				$row['fechaClickMarcaPortafolio'] = utf8_encode($row['fechaClickMarcaPortafolio']);
				$row['alerta'] = utf8_encode($row['alerta']);

				// SE AGREGA LA DETENCION DE PORTAFOLIO PARA RECONOZCA EN VALOR
				if ($row['grupo'] == "TV") {
					$resultadoTV[] = $row;
				} else if ($row['grupo'] == "INTER") {
					$resultadoOTROS[] = $row;
				} else if ($row['grupo'] == "PORTAFOLIO") {
					$resultadoPORTAFOLIO[] = $row;
				}
			}

			$this->response($this->json(array($resultadoTV, $resultadoOTROS, $resultadoPORTAFOLIO)), 201);

		} else {
			$error = array();
			$this->response($this->json($error), 400);
		}
	}

	private function datosescalamientos() {

		if ($this->get_request_method() != "GET") {
			$this->response('', 406);
		}

		/*CONEXION A LA BASE DE DATOS*/
		$this->dbSeguimientoConnect();

		$hoy = date("Y-m-d H:i:s");

		/*LOGICA QUE LLEVA LA INFORMACION A TV, INTERNET-ToIp Y CORREGIR PORTAFOLIO*/
		$query = ("SELECT e.pedido, e.tarea, e.tecnico, e.id_tecnico, e.fecha_solicitud, e.fecha_gestion, e.login_gestion, e.engestion, e.proceso, e.producto, e.motivo, 
					e.area, e.region, e.tipo_tarea, e.tecnologia, e.departamento, e.prueba_smnet, e.foto_adjunta, e.marcacion_tap, e.direccion_tap, 
					e.valor_tap, e.informacion_adicional, e.mac_real_cpe, e.correa_marcacion, observaciones, id_terreno, CASE WHEN (SELECT COUNT(*)
					FROM escalamiento_infraestructura e1
					WHERE e1.pedido = e.pedido AND e1.fecha_solicitud >= DATE_SUB(CURDATE(), INTERVAL 10 DAY) AND e1.estado <> '0') > 0 THEN 'TRUE' ELSE 'FALSE' END alerta
				FROM escalamiento_infraestructura e
				WHERE e.estado = '0' AND e.pedido <> ''
				ORDER BY e.fecha_solicitud ASC");

		$rst = $this->connseguimiento->query($query);

		if ($rst->num_rows > 0) {

			$resultadoEscalamiento = array();
			while ($row = $rst->fetch_assoc()) {

				$row['pedido'] = utf8_encode($row['pedido']);
				$row['tarea'] = utf8_encode($row['tarea']);
				$row['tecnico'] = utf8_encode($row['tecnico']);
				$row['id_tecnico'] = utf8_encode($row['id_tecnico']);
				$row['fecha_solicitud'] = utf8_encode($row['fecha_solicitud']);
				$row['fecha_gestion'] = utf8_encode($row['fecha_gestion']);
				$row['login_gestion'] = utf8_encode($row['login_gestion']);
				$row['engestion'] = utf8_encode($row['engestion']);
				$row['proceso'] = utf8_encode($row['proceso']);
				$row['observacion'] = utf8_encode($row['observacion']);
				$row['engestion'] = utf8_encode($row['engestion']);
				$row['proceso'] = utf8_encode($row['proceso']);
				$row['producto'] = utf8_encode($row['producto']);
				$row['motivo'] = utf8_encode($row['motivo']);
				$row['area'] = utf8_encode($row['area']);
				$row['region'] = utf8_encode($row['region']);
				$row['tipo_tarea'] = utf8_encode($row['tipo_tarea']);
				$row['tecnologia'] = utf8_encode($row['tecnologia']);
				$row['departamento'] = utf8_encode($row['departamento']);
				$row['prueba_smnet'] = utf8_encode($row['prueba_smnet']);
				$row['foto_adjunta'] = utf8_encode($row['foto_adjunta']);
				$row['marcacion_tap'] = utf8_encode($row['marcacion_tap']);
				$row['direccion_tap'] = utf8_encode($row['direccion_tap']);
				$row['valor_tap'] = utf8_encode($row['valor_tap']);
				$row['informacion_adicional'] = utf8_encode($row['informacion_adicional']);
				$row['mac_real_cpe'] = utf8_encode($row['mac_real_cpe']);
				$row['correa_marcacion'] = utf8_encode($row['correa_marcacion']);
				$row['observaciones'] = utf8_encode($row['observaciones']);
				$row['alerta'] = utf8_encode($row['alerta']);

				// SE AGREGA LA DETENCION DE PORTAFOLIO PARA RECONOZCA EN VALOR
				$resultadoEscalamiento[] = $row;
			}

			$this->response($this->json(array($resultadoEscalamiento)), 201);

		} else {
			$error = array();
			$this->response($this->json($error), 400);
		}
	}

	private function datosescalamientosprioridad2() {

		if ($this->get_request_method() != "GET") {
			$this->response('', 406);
		}

		/*CONEXION A LA BASE DE DATOS*/
		$this->dbSeguimientoConnect();

		$hoy = date("Y-m-d H:i:s");

		/*LOGICA QUE LLEVA LA INFORMACION A TV, INTERNET-ToIp Y CORREGIR PORTAFOLIO*/
		$query = ("SELECT e.pedido, e.tarea, e.tecnico, e.id_tecnico, e.fecha_solicitud, e.fecha_gestion, e.login_gestion, e.engestion, e.proceso, e.producto, e.motivo, 
					e.area, e.region, e.tipo_tarea, e.tecnologia, e.departamento, e.prueba_smnet, e.foto_adjunta, e.marcacion_tap, e.direccion_tap, 
					e.valor_tap, e.informacion_adicional, e.mac_real_cpe, e.correa_marcacion, observaciones, id_terreno, CASE WHEN (SELECT COUNT(*)
					FROM escalamiento_infraestructura e1
					WHERE e1.pedido = e.pedido AND e1.fecha_solicitud >= DATE_SUB(CURDATE(), INTERVAL 10 DAY) AND e1.estado <> '0') > 0 THEN 'TRUE' ELSE 'FALSE' END alerta
				FROM escalamiento_infraestructura e
				WHERE e.estado = '1' AND e.pedido <> '' AND e.tipificacion = 'Escalamiento ok nivel 2 Prioridad' AND e.ans < 20
				ORDER BY e.fecha_solicitud ASC");

		$rst = $this->connseguimiento->query($query);

		if ($rst->num_rows > 0) {

			$resultadoEscalamiento = array();
			while ($row = $rst->fetch_assoc()) {

				$row['pedido'] = utf8_encode($row['pedido']);
				$row['tarea'] = utf8_encode($row['tarea']);
				$row['tecnico'] = utf8_encode($row['tecnico']);
				$row['id_tecnico'] = utf8_encode($row['id_tecnico']);
				$row['fecha_solicitud'] = utf8_encode($row['fecha_solicitud']);
				$row['fecha_gestion'] = utf8_encode($row['fecha_gestion']);
				$row['login_gestion'] = utf8_encode($row['login_gestion']);
				$row['engestion'] = utf8_encode($row['engestion']);
				$row['proceso'] = utf8_encode($row['proceso']);
				$row['observacion'] = utf8_encode($row['observacion']);
				$row['engestion'] = utf8_encode($row['engestion']);
				$row['proceso'] = utf8_encode($row['proceso']);
				$row['producto'] = utf8_encode($row['producto']);
				$row['motivo'] = utf8_encode($row['motivo']);
				$row['area'] = utf8_encode($row['area']);
				$row['region'] = utf8_encode($row['region']);
				$row['tipo_tarea'] = utf8_encode($row['tipo_tarea']);
				$row['tecnologia'] = utf8_encode($row['tecnologia']);
				$row['departamento'] = utf8_encode($row['departamento']);
				$row['prueba_smnet'] = utf8_encode($row['prueba_smnet']);
				$row['foto_adjunta'] = utf8_encode($row['foto_adjunta']);
				$row['marcacion_tap'] = utf8_encode($row['marcacion_tap']);
				$row['direccion_tap'] = utf8_encode($row['direccion_tap']);
				$row['valor_tap'] = utf8_encode($row['valor_tap']);
				$row['informacion_adicional'] = utf8_encode($row['informacion_adicional']);
				$row['mac_real_cpe'] = utf8_encode($row['mac_real_cpe']);
				$row['correa_marcacion'] = utf8_encode($row['correa_marcacion']);
				$row['observaciones'] = utf8_encode($row['observaciones']);
				$row['alerta'] = utf8_encode($row['alerta']);

				// SE AGREGA LA DETENCION DE PORTAFOLIO PARA RECONOZCA EN VALOR
				$resultadoEscalamiento[] = $row;
			}

			$this->response($this->json(array($resultadoEscalamiento)), 201);

		} else {
			$error = array();
			$this->response($this->json($error), 400);
		}
	}

	private function savecontingencia() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		//$this->dbConnect();
		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$datosguardar = $params['datosguardar'];
		$login = $params['login'];
		$login = $login['LOGIN'];
		$estadoActual = (isset($datosguardar['estado'])) ? $datosguardar['estado'] : '';
		$accion = (isset($datosguardar['accion'])) ? $datosguardar['accion'] : '';
		$ciudad = (isset($datosguardar['ciudad'])) ? $datosguardar['ciudad'] : '';
		$correo = (isset($datosguardar['correo'])) ? $datosguardar['correo'] : '';
		$macEntra = (isset($datosguardar['macEntra'])) ? $datosguardar['macEntra'] : '';
		$macSale = (isset($datosguardar['macSale'])) ? $datosguardar['macSale'] : '';
		$motivo = (isset($datosguardar['motivo'])) ? $datosguardar['motivo'] : '';
		$observacion = (isset($datosguardar['observacion'])) ? $datosguardar['observacion'] : '';
		$pedido = (isset($datosguardar['pedido'])) ? $datosguardar['pedido'] : '';
		$proceso = (isset($datosguardar['proceso'])) ? $datosguardar['proceso'] : '';
		$remite = (isset($datosguardar['remite'])) ? $datosguardar['remite'] : '';
		$producto = (isset($datosguardar['producto'])) ? $datosguardar['producto'] : '';
		$tecnologia = (isset($datosguardar['tecnologia'])) ? $datosguardar['tecnologia'] : '';
		$tipoEquipo = (isset($datosguardar['tipoEquipo'])) ? $datosguardar['tipoEquipo'] : '';
		$uen = (isset($datosguardar['uen'])) ? $datosguardar['uen'] : '';
		$contrato = (isset($datosguardar['contrato'])) ? $datosguardar['contrato'] : '';
		$perfil = (isset($datosguardar['perfil'])) ? $datosguardar['perfil'] : '';
		$paquetes = (isset($datosguardar['paquetes'])) ? $datosguardar['paquetes'] : '';

		$tam = sizeof($paquetes);
		$paqueteconca = "";
		for ($i = 0; $i < $tam; $i++) {
			$paqueteconca = $paqueteconca . $paquetes[$i] . "/";
		}

		/*CUANDO SE SELECCIONE SEGUN EL PRODUCTO GUARDE EN EL CAMPO DE GRUPO*/
		if ($producto == "TV" && $accion == "Corregir portafolio") {
			$grupo = "PORTAFOLIO";
		} else if ($producto == "Internet" && $accion == "Corregir portafolio") {
			$grupo = "PORTAFOLIO";
		} else if ($producto == "ToIP" && $accion == "Corregir portafolio") {
			$grupo = "PORTAFOLIO";
		} else if ($producto == "Internet+ToIP" && $accion == "Corregir portafolio" || $producto == "Internet+ToIP" && $accion == "OC Telefonia") {
			$grupo = "PORTAFOLIO";
		} else if ($producto == "TV" && $accion == "mesaOffline") {
			$grupo = "PORTAFOLIO";
		} else if ($producto == "Internet" && $accion == "mesaOffline") {
			$grupo = "PORTAFOLIO";
		} else if ($producto == "ToIP" && $accion == "mesaOffline" || $producto == "ToIP" && $accion == "OC Telefonia") {
			$grupo = "PORTAFOLIO";
		} else if ($producto == "Internet+ToIP" && $accion == "mesaOffline") {
			$grupo = "PORTAFOLIO";
		} else if ($producto == "TV") {
			$grupo = "TV";
		} else if ($producto == "Internet") {
			$grupo = "INTER";
		} else if ($producto == "ToIP") {
			$grupo = "INTER";
			$paqueteconca = $datosguardar['linea'];
		} else if ($producto == "Internet+ToIP") {
			$grupo = "INTER";
			$paqueteconca = $datosguardar['linea'];
		}

		$isFieldContingency = (isset($datosguardar['_id'])) ? true : false;

		if($isFieldContingency){
			$idTerreno = $datosguardar['_id'];
			$horaGestionTerreno = $datosguardar['fecha'];
			$nuevaHoraGestionTerreno = date('Y-m-d H:i:s', strtotime($horaGestionTerreno));
			$sqlupdate = "INSERT INTO contingencias " .
			"(accion,ciudad,correo,macEntra,macSale,motivo, " .
			"observacion,paquetes,pedido,proceso,producto, " .
			"remite,tecnologia,tipoEquipo,uen,contrato,perfil,grupo,logindepacho,id_terreno,horagestion, engestion) " .
			" values ( " .
			" '$accion', '$ciudad', '$correo', '$macEntra', '$macSale', '$motivo', " .
			"'$observacion', '$paqueteconca', '$pedido' , '$proceso', '$producto', " .
			"'$remite', '$tecnologia', '$tipoEquipo', '$uen', '$contrato', '$perfil' " .
			", '$grupo', '$login','$idTerreno','$nuevaHoraGestionTerreno', 0); ";
		}else{
			$idTerreno = null;
			$sqlupdate = "INSERT INTO contingencias " .
			"(accion,ciudad,correo,macEntra,macSale,motivo, " .
			"observacion,paquetes,pedido,proceso,producto, " .
			"remite,tecnologia,tipoEquipo,uen,contrato,perfil,grupo,logindepacho,id_terreno, engestion) " .
			" values ( " .
			" '$accion', '$ciudad', '$correo', '$macEntra', '$macSale', '$motivo', " .
			"'$observacion', '$paqueteconca', '$pedido' , '$proceso', '$producto', " .
			"'$remite', '$tecnologia', '$tipoEquipo', '$uen', '$contrato', '$perfil' " .
			", '$grupo', '$login','$idTerreno', 0); ";
		}



		if ($accion !== "" || $correo !== "" || $pedido !== "" || $proceso !== "") {
			$rst = $this->connseguimiento->query(utf8_decode($sqlupdate));
			$this->response($this->json(array()), 200);
		}
	}

	private function saveescalamiento() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$datosguardar = $params['datosguardar'];
		$login = $params['login'];
		$login = $login['LOGIN'];
		$pedido = $datosguardar['pedido'];
		$task = $datosguardar['task'];
		$tecnico = $datosguardar['engineer'];
		$id_tecnico = $datosguardar['engineerID'];
		$fecha_solicitud = $datosguardar['dateCreated'];
		$fecha_solicitud = date('Y-m-d H:i:s', strtotime($fecha_solicitud));
		$login_gestion = $login;
		$engestion = 1;
		$proceso = $datosguardar['proceso'];
		$producto = $datosguardar['producto'];
		$motivo = $datosguardar['motivo'];
		$area = $datosguardar['area'];
		$region = $datosguardar['region'];
		$crm = $datosguardar['crm'];
		$task_type = $datosguardar['taskType'];
		$tecnologia = $datosguardar['tech'];
		$departamento = $datosguardar['department'];
		$prueba_smnet = $datosguardar['isSmnetTestSif'];
		$foto_adjunta = $datosguardar['isPhoto'];
		$marcacion_tap = $datosguardar['correa_marcacion'];
		$direccion_tap = $datosguardar['addressTap'];
		$valor_tap = $datosguardar['vTap'];
		$mac_real_cpe = $datosguardar['mac_real_cpe'];
		$informacion_adicional = $datosguardar['informacion_adicional'];
		$correa_marcacion = $datosguardar['correa_marcacion'];
		$observaciones = $datosguardar['observaciones'];
		$ans = $datosguardar['ans'];
		$id_terreno = $datosguardar['_id'];
		$estado = 0;

		$sqlupdate = "INSERT INTO escalamiento_infraestructura " .
			"(pedido,tarea,tecnico,id_tecnico,fecha_solicitud,login_gestion,engestion,proceso, " .
			"producto,motivo,area,region,crm,tipo_tarea,tecnologia,departamento,prueba_smnet,foto_adjunta, " .
			"marcacion_tap,direccion_tap,valor_tap,mac_real_cpe,informacion_adicional,correa_marcacion,observaciones,estado,id_terreno,ans) " .
			" values ( " .
			" '$pedido', '$task', '$tecnico', '$id_tecnico', '$fecha_solicitud', '$login_gestion', '$engestion', '$proceso', " .
			"'$producto', '$motivo', '$area', '$region', '$crm', '$task_type', '$tecnologia', '$departamento' , '$prueba_smnet', '$foto_adjunta', " .
			"'$marcacion_tap', '$direccion_tap', '$valor_tap', '$mac_real_cpe', '$informacion_adicional', '$correa_marcacion' " .
			", '$observaciones', '$estado', '$id_terreno', '$ans'); ";

		if ($pedido !== "" || $tecnico !== "" || $proceso !== "" || $producto !== "") {
			$rst = $this->connseguimiento->query(utf8_decode($sqlupdate));
			$this->response($this->json(array()), 200);
		}
	}

	private function CancelarContingencias() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$datoscancelar = $params['datoscancelar'];
		$login = $params['login'];
		$login = $login['LOGIN'];
		$pedido = $datoscancelar['pedido'];
		$id = $datoscancelar['id'];
		$observacionesconting = 'Cancelado por Despachador';
		$acepta = 'Rechaza';
		$tipificacion = 'Cancelado por Despachador';
		$horacontingencia = date("Y-m-d H:i:s");


		$query = "SELECT id FROM contingencias WHERE pedido = '$pedido' AND engestion IS NULL OR pedido = '$pedido' AND engestion = '0'";

		$rst = $this->connseguimiento->query($query);

			if ($rst->num_rows > 0) {

				$sqlupdate = ("UPDATE contingencias SET horacontingencia = '$horacontingencia', observContingencia = '$observacionesconting', tipificacion = '$tipificacion', acepta = '$acepta', finalizado = 'OK', logincontingencia = '$login', engestion = 1 WHERE id = '$id'");

				$rstupdate = $this->connseguimiento->query($sqlupdate);

				if (is_numeric($rstupdate) OR $rstupdate === true) {
					$this->response($this->json('Contingencia Cancelada'), 201);
				} else {
					$this->response($this->json("Error"), 400);
				}

			} else {
				$this->response($this->json("Error"), 402);
			}


	}

	/*QUERY PARA LA GUARDAR SI SE ACEPTA O SE RECHAZA LA CONTIGENCIA GESTIÓN CONTIGENCIA*/
	private function guardarpedidocontingencia() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$datosguardar = $params['datos'];
		$login = $params['login'];
		$login = $login['LOGIN'];
		$pedido = (isset($datosguardar['pedido'])) ? $datosguardar['pedido'] : '';
		$producto = (isset($datosguardar['producto'])) ? $datosguardar['producto'] : '';
		$observacionesconting = (isset($datosguardar['observacionescontingencia'])) ? utf8_decode($datosguardar['observacionescontingencia']) : '';
		$ingresoClick = (isset($datosguardar['ingresoClick'])) ? $datosguardar['ingresoClick'] : '';
		$tipificacion = (isset($datosguardar['tipificacion'])) ? utf8_decode($datosguardar['tipificacion']) : '';
		$generarCr = (isset($datosguardar['generarcr'])) ? $datosguardar['generarcr'] : '';
		$horacontingencia = date("Y-m-d H:i:s");

		if ($tipificacion == 'Ok') {
			$acepta = 'Acepta';
		} else {
			$acepta = 'Rechaza';
		}

		$query = ("SELECT id
					FROM contingencias
					WHERE pedido = '$pedido'
					AND producto= '$producto'
					AND finalizado IS NULL
					AND accion IN('Contingencia', 'Reenvio de registros', 'Refresh', 'Cambio de equipo', 'Crear Espacio', 'crear cliente', 'Registros ToIP', 'mesaOffline', 'Cambio EID', 'Crear Linea IMS')
				");

		$rst = $this->connseguimiento->query($query);

		if ($rst->num_rows > 0) {

			$resultado = array();

			while ($row = $rst->fetch_assoc()) {
				$resultado[] = $row;
				$id = $row['id'];
			}

			/*ESTE QUERY ME ACTULIZA LA INFORMACION QUE ANALISTA A GESTIONADO*/
			$sqlupdate = "UPDATE contingencias SET horacontingencia = '$horacontingencia', " .
				"observContingencia = '$observacionesconting', " .
				"ingresoEquipos = '$ingresoClick', tipificacion='$tipificacion', " .
				"acepta = '$acepta', generarcr = '$generarCr', finalizado = 'OK' " .
				" WHERE id='$id' ";

			$rstupdate = $this->connseguimiento->query($sqlupdate);

			$this->response($this->json(array()), 200);
		}
	}

	/*QUERY PARA LA GUARDAR SI SE ACEPTA O SE RECHAZA LA CONTIGENCIA GESTIÓN CONTIGENCIA*/
	private function guardarescalamiento() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$datosguardar = $params['datos'];
		$login = $params['login'];
		$login = $login['LOGIN'];
		$pedido = $datosguardar['pedido'];
		$producto = $datosguardar['producto'];
		$observacionesescalamiento = utf8_decode($datosguardar['observacionesescalamiento']);
		$tipificacion = utf8_decode($datosguardar['tipificacion']);
		$horaescalamiento = date("Y-m-d H:i:s");

		$query = ("SELECT id
					FROM escalamiento_infraestructura
					WHERE pedido = '$pedido'
					AND producto= '$producto'
				");

		$rst = $this->connseguimiento->query($query);

		if ($rst->num_rows > 0) {
			$resultado = array();

			while ($row = $rst->fetch_assoc()) {
				$resultado[] = $row;
				$id = $row['id'];
			}

			/*ESTE QUERY ME ACTULIZA LA INFORMACION QUE ANALISTA A GESTIONADO*/
			if($tipificacion == "Escalamiento ok nivel 2 Prioridad"){
				$sqlupdate = "UPDATE escalamiento_infraestructura SET fecha_respuesta = '$horaescalamiento', " .
					"login_gestion = '', " .
					"tipificacion='$tipificacion', " .
					"engestion='0', " .
					"estado = '1' " .
					" WHERE id='$id' ";
			} else {
				if($tipificacion == "Agendado" || $tipificacion == "No tecnicos disponibles" || $tipificacion == "ANS de mas de 20 horas" || $tipificacion == "No agendado"){
					$sqlupdate = "UPDATE escalamiento_infraestructura SET fecha_respuesta = '$horaescalamiento', " .
						"observacion_respuesta = '$observacionesescalamiento', " .
						"tipificacion='$tipificacion', " .
						"estado = '2' " .
						" WHERE id='$id' ";
				}
				else {
					$sqlupdate = "UPDATE escalamiento_infraestructura SET fecha_respuesta = '$horaescalamiento', " .
						"observacion_respuesta = '$observacionesescalamiento', " .
						"tipificacion='$tipificacion', " .
						"estado = '1' " .
						" WHERE id='$id' ";
				}
			}

			$rstupdate = $this->connseguimiento->query($sqlupdate);

			$this->response($this->json(array($id)), 200);
		}
	}

	/*******FUNCION PARA LA MARCA EN TV E INTERNET**********/
	private function marca() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		//$this->dbConnect();
		$today = date("Y-m-d H:i:s");
		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$datosguardar = $params['datos'];
		$login = $params['login'];
		$login = $login['LOGIN'];
		$pedido = $datosguardar['pedido'];
		$gestion = $datosguardar['bloqueo'];
		$producto = $datosguardar['producto'];

		if ($gestion == true) {
			$gestion = 1;
		} else {
			$gestion = 0;
		}

		$query = "SELECT id, logincontingencia " .
			"FROM contingencias where engestion = '1'  " .
			"and finalizado is null " .
			"and pedido = '$pedido' and producto = '$producto' ";

		$rst = $this->connseguimiento->query($query);

		if ($rst->num_rows > 0) {

			$resultado = array();

			while ($row = $rst->fetch_assoc()) {
				$resultado[] = $row;
				$logincontingencia = $row['logincontingencia'];
				$id = $row['id'];
			}
			if ($login == $logincontingencia) {

				$sqlupdate = "UPDATE contingencias SET engestion = '0', " .
					"logincontingencia = '' " .
					", fechaClickMarca='$today' " .
					" WHERE id='$id' ";

				$this->connseguimiento->query($sqlupdate);
				$this->response($this->json(array("desbloqueado")), 200);
			} else {
				$this->response($this->json(array($resultado)), 200);
			}

		} else {

			$query = ("SELECT id
				FROM contingencias
				WHERE finalizado is null
				AND pedido = '$pedido'
				AND producto = '$producto'
				AND accion NOT IN(
					'Corregir portafolio',
					'OC Telefonia'
				)
			");

			$rst = $this->connseguimiento->query($query);

			if ($rst->num_rows > 0) {

				$resultado = array();

				while ($row = $rst->fetch_assoc()) {
					$resultado[] = $row;
					$id = $row['id'];
				}

				$sqlupdate = "UPDATE contingencias SET engestion = '$gestion', logincontingencia = '$login' " .
					", fechaClickMarca='$today' " .
					" WHERE id='$id' ";

				$rstupdate = $this->connseguimiento->query($sqlupdate);
			}
		}
	}

	/*******FUNCION PARA LA MARCA EN ESCALAMIENTO**********/
	private function marcaescalamiento() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		//$this->dbConnect();
		$today = date("Y-m-d H:i:s");
		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$datosguardar = $params['datos'];
		$login = $params['login'];
		$login = $login['LOGIN'];
		$pedido = $datosguardar['pedido'];
		$gestion = $datosguardar['bloqueo'];
		$producto = $datosguardar['producto'];

		if ($gestion == true) {
			$gestion = 1;
		} else {
			$gestion = 0;
		}

		$query = "SELECT id, login_gestion " .
			"FROM escalamiento_infraestructura where engestion = '1'  " .
			"and pedido = '$pedido' and producto = '$producto' AND (observacion_respuesta IS NULL OR tipificacion = 'Escalamiento ok nivel 2 Prioridad') ORDER BY fecha_solicitud DESC";

		$rst = $this->connseguimiento->query($query);

		if ($rst->num_rows > 0) {

			$resultado = array();

			while ($row = $rst->fetch_assoc()) {
				$resultado[] = $row;
				$login_gestion = $row['login_gestion'];
				$id = $row['id'];
			}
			if ($login == $login_gestion) {

				$sqlupdate = "UPDATE escalamiento_infraestructura SET engestion = '0', " .
					"login_gestion = '' " .
					" WHERE id='$id' ";

				$this->connseguimiento->query($sqlupdate);
				$this->response($this->json(array("desbloqueado")), 200);
			} else {
				$this->response($this->json(array($resultado)), 200);
			}

		} else {

			$query = ("	SELECT id
						FROM escalamiento_infraestructura
						WHERE pedido = '$pedido'
						AND producto = '$producto' AND (observacion_respuesta IS NULL OR tipificacion = 'Escalamiento ok nivel 2 Prioridad') ORDER BY fecha_solicitud DESC						
				");

			$rst = $this->connseguimiento->query($query);

			if ($rst->num_rows > 0) {

				$resultado = array();

				while ($row = $rst->fetch_assoc()) {
					$resultado[] = $row;
					$id = $row['id'];
				}

				$sqlupdate = "UPDATE escalamiento_infraestructura SET engestion = '$gestion', login_gestion = '$login' " .
					" WHERE id='$id' ";

				$rstupdate = $this->connseguimiento->query($sqlupdate);
			}
		}
	}

	/*****    INICIO DEL BLOQUE PARA GUARDAR Y MARCAR LA CONTINGENCIA PARA CORREGIR INVENTARIO   *****/

	/*QUERY PARA LA GUARDAR SI SE ACEPTA O SE RECHAZA LA CONTIGENCIA GESTIÓN CONTIGENCIA*/
	private function guardarPedidoContingenciaPortafolio() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		//$this->dbConnect();
		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$datosguardar = $params['datos'];
		$login = $params['login'];
		$login = $login['LOGIN'];
		$pedido = $datosguardar['pedido'];
		$producto = $datosguardar['producto'];
		$observContingenciaPortafolio = utf8_decode($datosguardar['observContingenciaPortafolio']);
		$ingresoClick = $datosguardar['ingresoClick'];
		$tipificacionPortafolio = utf8_decode($datosguardar['tipificacionPortafolio']);
		$horaContingenciaPortafolio = date("Y-m-d H:i:s");

		/*ORGANIZAR LO QUE SE RECHAZA DESDE CORREGIR PORTAFOLIO*/
		if ($tipificacionPortafolio == 'Ok') {
			$aceptaPortafolio = 'Acepta';
		} else {
			$aceptaPortafolio = 'Rechaza';
		}

		$query = ("SELECT id
					FROM contingencias
					WHERE pedido = '$pedido'
					AND producto= '$producto'
					AND finalizadoPortafolio IS NULL
					AND accion IN('Corregir portafolio', 'mesaOffline', 'OC Telefonia')
			");

		$rst = $this->connseguimiento->query($query);

		if ($rst->num_rows > 0) {

			$resultado = array();

			while ($row = $rst->fetch_assoc()) {
				$resultado[] = $row;
				$id = $row['id'];
			}

			/*ESTE QUERY ME ACTULIZA LA INFORMACION QUE ANALISTA A GESTIONADO*/
			$sqlupdate = "UPDATE contingencias SET horaContingenciaPortafolio = '$horaContingenciaPortafolio', " .
				"observContingenciaPortafolio = '$observContingenciaPortafolio', " .
				"ingresoEquipos = '$ingresoClick', tipificacionPortafolio='$tipificacionPortafolio', " .
				"aceptaPortafolio = '$aceptaPortafolio', finalizadoPortafolio = 'OK' " .
				" WHERE id='$id' ";

			$rstupdate = $this->connseguimiento->query($sqlupdate);

			$this->response($this->json(array()), 200);
		}
	}

	/*******FUNCION PARA LA MARCA EN TV E INTERNET**********/
	private function marcaPortafolio() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		//$this->dbConnect();
		$today = date("Y-m-d H:i:s");
		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$datosguardar = $params['datos'];
		$login = $params['login'];
		$login = $login['LOGIN'];
		$pedido = $datosguardar['pedido'];
		$gestion = $datosguardar['bloqueo'];
		$producto = $datosguardar['producto'];

		if ($gestion == true) {
			$gestion = 1;
		} else {
			$gestion = 0;
		}

		$query = ("	SELECT id, loginContingenciaPortafolio
						FROM contingencias
						WHERE finalizadoPortafolio is null
						AND enGestionPortafolio = '1'
						AND pedido = '$pedido' AND producto = '$producto'
				");

		$rst = $this->connseguimiento->query($query);

		if ($rst->num_rows > 0) {

			$resultado = array();

			while ($row = $rst->fetch_assoc()) {
				$resultado[] = $row;
				$logincontingencia = $row['loginContingenciaPortafolio'];
				$id = $row['id'];
			}
			if ($login == $logincontingencia) {

				$sqlupdate = "UPDATE contingencias SET enGestionPortafolio = '0', " .
					"loginContingenciaPortafolio = '' " .
					", fechaClickMarcaPortafolio='$today' " .
					" WHERE id='$id' ";

				$this->connseguimiento->query($sqlupdate);
				$this->response($this->json(array("desbloqueado")), 200);
			} else {
				$this->response($this->json(array($resultado)), 200);
			}

		} else {

			$query = ("	SELECT id
							FROM contingencias
							WHERE finalizadoPortafolio is null
							AND pedido = '$pedido'
							AND producto = '$producto'
							AND accion IN('Corregir portafolio', 'mesaOffline', 'OC Telefonia')
					");

			$rst = $this->connseguimiento->query($query);

			if ($rst->num_rows > 0) {

				$resultado = array();

				while ($row = $rst->fetch_assoc()) {
					$resultado[] = $row;
					$id = $row['id'];
				}

				$sqlupdate = "UPDATE contingencias SET enGestionPortafolio = '$gestion', loginContingenciaPortafolio = '$login' " .
					", fechaClickMarcaPortafolio='$today' " .
					" WHERE id='$id' ";

				$rstupdate = $this->connseguimiento->query($sqlupdate);
			}
		}
	}
	/*****    FIN DEL BLOQUE PARA GUARDAR Y MARCAR LA CONTINGENCIA PARA CORREGIR INVENTARIO   *****/

	/*FUNCION PARA CERRAR MASIVAMENTE LAS CONTIGENCIAS*/
	private function cerrarMasivamenteContingencias() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();


		$params = json_decode(file_get_contents('php://input'), true);
		$datosCierreMasivo = $params['datos'];

		$today = date("Y-m-d H:i:s");
		$tv = $datosCierreMasivo['TV'];
		$internet = $datosCierreMasivo['Internet'];
		$toip = $datosCierreMasivo['ToIP'];
		$internettoip = $datosCierreMasivo['InternetToIP'];
		$instalaciones = $datosCierreMasivo['Instalaciones'];
		$reparaciones = $datosCierreMasivo['Reparaciones'];
		$aproequipo = $datosCierreMasivo['AprovisionarContin'];
		$refresh = $datosCierreMasivo['Refresh'];
		$cambioequipo = $datosCierreMasivo['CambioEquipo'];
		$cambioeid = $datosCierreMasivo["CambioEID"];
		$registrostoip = $datosCierreMasivo['RegistrosToIP'] ;
		$observaciones = $datosCierreMasivo['observaciones'] ;


		$sqlNroRegistrosEliminar = "SELECT COUNT(id) AS Cantidad FROM contingencias WHERE acepta IS NULL AND logincontingencia IS NULL AND producto IN ('$tv','$internet','$toip','$internettoip') AND proceso IN ('$instalaciones','$reparaciones' ) AND accion IN ('$aproequipo','$refresh','$cambioequipo','$cambioeid','$registrostoip')";

			$nroRegistrosEliminar = $this->connseguimiento->query($sqlNroRegistrosEliminar);

			$counter = 0;
			if ($nroRegistrosEliminar->num_rows > 0) {
				$result = array();
				if ($row = $nroRegistrosEliminar->fetch_assoc()) {
					$counter = $row['Cantidad'];
				}
			}


			/*ESTE QUERY CIERRA DE FORMA MASIVA LAS CONTINGENCIAS*/
			$sqlupdate = ("UPDATE contingencias SET logincontingencia='cierremasivo', acepta='Rechaza', tipificacion='Error del sistema', engestion='1', finalizado='OK', fechaClickMarca='$today', horacontingencia = '$today', observContingencia='$observaciones' WHERE acepta IS NULL AND logincontingencia IS NULL AND producto IN ('$tv','$internet','$toip','$internettoip') AND proceso IN ('$instalaciones','$reparaciones' ) AND accion IN ('$aproequipo','$refresh','$cambioequipo','$cambioeid','$registrostoip')");


			$rstupdate = $this->connseguimiento->query($sqlupdate);

			$this->response($this->json(array($counter)), 200);
	}

	private function guardarEscalar() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		//$this->dbConnect();
		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$fechagestionAsesor = date('Y-m-d H:i:s');

		$gestionescalado = $params['gestionescalado'];

		$estadoActual = utf8_decode($gestionescalado['estado']);
		$PedidoDespacho = utf8_decode($gestionescalado['PedidoDespacho']);
		$fechaClick = $gestionescalado['fechaclick'];
		$observaciones = utf8_decode($gestionescalado['ObservacionAsesor']);

		if (preg_match("/^20\d{2}(-|\/)((0[1-9])|(1[0-2]))(-|\/)((0[1-9])|([1-2][0-9])|(3[0-1]))(T|\s)(([0-1][0-9])|(2[0-3])):([0-5][0-9]):([0-5][0-9])$/", $fechaClick)) {
			$fechaClick = "";
		} else {
			$fechaClick = ", fechaClick = STR_TO_DATE('$fechaClick', '%d/%m/%Y %T') ";
		}

		$query = "select idgestion, estado from BrutalForce where PedidoDespacho = '$PedidoDespacho' ";

		$rst = $this->connseguimiento->query($query);

		if ($rst->num_rows > 0) {

			$resultado = array();

			while ($row = $rst->fetch_assoc()) {
				$resultado[] = $row;
				$id = $row['idgestion'];
				$estadoAnterior = $row['estado'];
			}
			$estadoActual = $estadoAnterior . "/" . $estadoActual;

			$sqlupdate = "UPDATE BrutalForce SET estado = '$estadoActual', ObservacionAsesor = '$observaciones' $fechaClick " .
				" WHERE idgestion='$id' ";

			$rstupdate = $this->connseguimiento->query($sqlupdate);

			$this->response($this->json(array()), 200);
		}
	}

	private function desbloquear() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		//$this->dbConnect();
		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$datosFinal = $params['datos'];

		$PedidoDespacho = $datosFinal['PedidoDespacho'];

		$query = "select idgestion id from BrutalForce where PedidoDespacho = '$PedidoDespacho' ";

		$rst = $this->connseguimiento->query($query);

		if ($rst->num_rows > 0) {

			$resultado = array();

			while ($row = $rst->fetch_assoc()) {
				$resultado[] = $row;
				$id = $row['id'];
			}

			$sqlDesbloquear = "update BrutalForce SET pedidobloqueado = '0' WHERE idgestion='$id' ";

			$rst = $this->connseguimiento->query($sqlDesbloquear);

			$this->response($this->json(array()), 200);
		}
	}

	private function gestionBorrar() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		//$this->dbConnect();
		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$datosFinal = $params['datosBorrar'];

		$PedidoDespacho = $datosFinal['PedidoDespacho'];

		$query = "select idgestion id from BrutalForce where PedidoDespacho = '$PedidoDespacho' ";

		$rst = $this->connseguimiento->query($query);

		if ($rst->num_rows > 0) {

			$resultado = array();

			while ($row = $rst->fetch_assoc()) {
				$resultado[] = $row;
				$id = $row['id'];
			}

			$sqlDelete = "DELETE FROM BrutalForce WHERE idgestion='$id' ";

			$rstDelete = $this->connseguimiento->query($sqlDelete);

			$this->response($this->json(array()), 200);
		}
	}

	private function gestionAsesorFinal() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		//$this->dbConnect();
		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$datosFinal = $params['datosFinal'];

		$PedidoDespacho = $datosFinal['PedidoDespacho'];
		$PedidoNuevo = $datosFinal['pedidoNuevo'];
		$estadoFinalPedido = utf8_decode($datosFinal['estadoFinalPedido']);
		$ObservacionesFinales = utf8_decode($datosFinal['ObservacionesFinales']);

		$query = "select idgestion id from BrutalForce where PedidoDespacho = '$PedidoDespacho' ";

		$rst = $this->connseguimiento->query($query);

		if ($rst->num_rows > 0) {

			$resultado = array();

			while ($row = $rst->fetch_assoc()) {
				$resultado[] = $row;
				$id = $row['id'];
			}

			$sqlupdate = "UPDATE BrutalForce SET gestionAsesor='3', " .
				" ObservacionesFinales = '$ObservacionesFinales', estadoFinalPedido = '$estadoFinalPedido', " .
				" pedidoNuevo = '$PedidoNuevo' WHERE idgestion='$id' ";

			$rstupdate = $this->connseguimiento->query($sqlupdate);

			$this->response($this->json(array()), 200);
		}
	}

	private function gestiodespachoBrutal() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		//$this->dbConnect();
		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$infogestion = $params['datosguardar'];
		$pedido = $infogestion['pedido'];
		$accion = utf8_decode($infogestion['accion']);
		$correo = $infogestion['correo'];
		$observaciones = utf8_decode($infogestion['observaciones']);
		$cedula = $infogestion['cedula'];
		$tecnico = utf8_decode($infogestion['tecnico']);
		$celular = $infogestion['celular'];
		$zona = utf8_decode($infogestion['zona']);
		$idLlamada = $infogestion['idLlamada'];
		$supervisor = utf8_decode($infogestion['supervisor']);
		$tipoTrans = utf8_decode($infogestion['tipoTrans']);
		$numSAPEIni = utf8_decode($infogestion['numSAPEIni']);
		$numSAPEFin = utf8_decode($infogestion['numSAPEFin']);
		$prioridad = $infogestion['prioridad'];

		if ($infogestion['accion'] !== "Gestión AAA") {
			$numSAPEIni = "";
			$numSAPEFin = "";
		}

		$usuarioid = $params['login'];
		$login = $usuarioid['LOGIN'];

		$query = "select PedidoDespacho from BrutalForce where PedidoDespacho = '$pedido' ";
		$rst = $this->connseguimiento->query($query);

		if ($rst->num_rows > 0) {
			$error = array();
			$this->response($this->json($error), 400);

		} else {
			if ($login == 'lmontcre') {
				$accion = $accion . "__Valle";
			}

			$sql = " INSERT INTO BrutalForce " .
				" (PedidoDespacho, AccionDespacho, CorreoDespacho, ObservacionesDespacho, " .
				" LoginDespacho, gestionAsesor, zona, tipoTransaccion, idLlamada, supervisor, " .
				" celular, tecnico, cedula, numSAPEIni, numSAPEFin, prioridad) " .
				" values ( " .
				" '$pedido', '$accion', '$correo', '$observaciones', '$login', '1', " .
				"'$zona', '$tipoTrans', '$idLlamada' , '$supervisor', '$celular', " .
				"'$tecnico', '$cedula', '$numSAPEIni', '$numSAPEFin', '$prioridad') ";
			//   echo $sql;
			$rst = $this->connseguimiento->query($sql);

			$this->response($this->json(array()), 200);

		}
	}

	private function GuardarPedidoEncuesta() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		//$this->dbConnect();
		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$info_encuesta = $params['infoPedidoEncuesta'][0];
		$info_gestion = $params['gestionDolores'];
		$duracion = $params['counter'];
		$fechaInicial = $params['fechaInicial'];
		$fechaFinal = $params['fechaFinal'];
		$usuarioid = $params['login'];
		$login = $usuarioid['LOGIN'];

		$codigo = utf8_decode($info_gestion['codigo']);
		$resultado = utf8_decode($info_gestion['resultado']);
		$intentos = $info_gestion['intentos'];
		$observaciones = utf8_decode($info_gestion['observaciones']);

		$telefono = $info_encuesta['telefono'];
		$cedula = $info_encuesta['cedula'];
		$detalle = $info_encuesta['detalle'];
		$fecha_instalacion = $info_encuesta['fecha_instalacion'];
		$departamento = utf8_decode($info_encuesta['departamento']);
		$municipio = utf8_decode($info_encuesta['municipio']);
		$contratista = $info_encuesta['contratista'];
		$Interfaz = $info_encuesta['Interfaz'];
		$semana = $info_encuesta['semana'];

		if ($resultado == "ERROR SELECCIÓN DE RESPUESTA" || $resultado == "CLIENTE NO BRINDA INFORMACIÓN") {
			$agrupador = "Error selección rspuesta";
		} else if ($resultado == "DAÑOS LUEGO INSTALACIÓN" || $resultado == "INCUMPLIMIENTO AGENDA" || $resultado == "TECNICO NO DA INFORMACIÓN" || $resultado == "DESORDEN EN SITIO") {
			$agrupador = "Ejecución en campo";
		} else if ($resultado == "OFERTA DIFERENTE" || $resultado == "MALA ASESORIA" || $resultado == "MAL AGENDAMIENTO") {
			$agrupador = "Vendedor";
		}

		$agrupador = utf8_decode($agrupador);

		$sql = " INSERT INTO doloresClientes " .
			" (pedido, cedula, telefono, fecha_instalacion, " .
			" departamento, municipio, contratista, Interfaz, observaciones, codigo, resultado, " .
			"fecha_inicio_contacto, fecha_fin_contacto, duracion, usuario, semana, agrupador) " .
			" values ( " .
			" '$detalle', '$cedula', '$telefono', '$fecha_instalacion', '$departamento', '$municipio', '$contratista', '$Interfaz', " .
			" '$observaciones', '$codigo', '$resultado', '$fechaInicial', '$fechaFinal', '$duracion', '$login', '$semana', '$agrupador') ";

		$rst = $this->connseguimiento->query($sql);

		$this->response($this->json(array()), 200);
	}

	private function CsvNpsSemana() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$usuarioid = $params['login'];
		$usuarioid = $usuarioid['LOGIN'];
		$semana = $params['semana'];

		$fecha = date("Y") . "-" . date("m") . "-" . date("d");

		//echo "estos son los datos, usuario: ".$usuarioid." semana: ".$semana;
		//echo "estos son los otros tipo trabajo, usuario: ".$tipotrabajo." uen: ".$uen;

		$filename = "NPS_Semanal" . "_" . $semana . "_" . $usuarioid . ".csv";

		$query = "select campanaid, lanzamiento, idllamada, telefono, mensaje,accion, " .
			"fecha, idllamada2, estado, cedula, detalle, fecha2, fecha_carga, " .
			"fecha_instalacion, departamento, municipio, regional, contratista, interfaz, " .
			"producto, tipo_solicitud, pregunta, respuesta, presente " .
			"FROM nps where semana = '$semana' ";

		$rst = $this->connseguimiento->query($query) or die($this->connseguimiento->error . __LINE__);

		if ($rst->num_rows > 0) {

			$result = array();
			$fp = fopen("../tmp/$filename", 'w');
			//echo $fp;

			$columnas = array('CAMPANAID',
				'LANZAMIENTO',
				'ID LLAMADA',
				'TELEFONO',
				'MENSAJE',
				'ACCION',
				'FECHA',
				'IDLLAMADA',
				'ESTADO',
				'CEDULA',
				'DETALLE',
				'FECHA_2',
				'FECHA_CARGA',
				'FECHA_INSTALACION',
				'DEPARTAMENTO',
				'MUNICIPIO',
				'REGION',
				'CONTRATISTA_SSMM',
				'INTERFAZ',
				'PRODUCTO',
				'TIPO_SOLICITUD',
				'PREGUNTA',
				'RESPUESTA',
				'PRESENTE');

			fputcsv($fp, $columnas);
			//$carlitos=0;
			while ($row = $rst->fetch_assoc()) {

				//	$row['departamento']=utf8_decode($row['departamento']);
				//	$row['municipio']=utf8_decode($row['municipio']);
				//	$row['pregunta']=utf8_decode($row['pregunta']);
				//	$row['respuesta']=utf8_decode($row['respuesta']);
				//$result[] = $row;
				fputcsv($fp, $row);
				//if($carlitos==0){var_dump($row);$carlitos=1;};
			}

			fclose($fp);

			$this->response($this->json(array($filename)), 200);
		}
		$this->response('', 203); // If no records "No Content" status
		//descarga archivo estados click, activity feed
	}

	private function guardarPlan() {

		$usuarioid = "";
		$password = "";

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		//$this->dbConnect();
		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$login = $params['datosLogin'];
		$user = $login['LOGIN'];
		$planNPS = $params['datosPlan'];
		$responsable = $planNPS['responsable'];
		$regional = $planNPS['regional'];
		$plan = $planNPS['plan'];

		$sql = " INSERT INTO npsPlanTrabajo ( " .
			" responsable, " .
			" regional, " .
			" observaciones, usuario_carga) values ( " .
			" '$responsable', " .
			" '$regional', " .
			" '$plan', " .
			" '$user')";
		//echo $sql;
		$rst = $this->connseguimiento->query($sql);

		$this->response($this->json('Usuario creado'), 201);
		//crear pedido comercial, activity feed
	}

	private function cargar_datosNPS() {

		ini_set('max_execution_time', 1000);

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		//ini_set('display_errors', '1');
		$target_dir = "../uploads/";
		$target_file = $target_dir . basename($_FILES["fileUpload"]["name"]);
		//$name     = $_FILES['fileUpload']['name'];
		$tname = $_FILES['fileUpload']['tmp_name'];
		$type = $_FILES['fileUpload']['type'];

		$login = $this->_request['user'];
		$fecha = date("Y-m-d H:i:s");
		$tname1 = basename($_FILES["fileUpload"]["name"]);

		$sql = "insert into carga_archivos(nombre_archivo, tipo, login) values('$tname1', 'NPS', '$login')";
		$rst = $this->connseguimiento->query($sql);
		$id_carga = "0";
		$sql1 = "select max(id) as total_id from carga_archivos";
		$rr = $this->connseguimiento->query($sql1);
		$id_carga = 0;
		if ($rr->num_rows > 0) {
			$result = array();
			if ($row = $rr->fetch_assoc()) {
				$id_carga = $row['total_id'];
			}
		}

		//$target_file = basename($_FILES["fileUpload"]["name"]);
		$uploadOk = 1;
		// Check if $uploadOk is set to 0 by an error
		if ($uploadOk == 0) {
			echo "Lo sentimos , el archivo no se ha subido.";
			// if everything is ok, try to upload file
		} else {

			if (move_uploaded_file($_FILES["fileUpload"]["tmp_name"], $target_file)) {
				echo "El archivo " . basename($_FILES["fileUpload"]["name"]) . " se ha subido";

			} else {

				echo "Ha habido un error al subir el archivo.";

			}
		}

		$tname1 = basename($_FILES["fileUpload"]["name"]);

		if ($type == 'application/vnd.ms-excel') {
			// Extension excel 97
			$ext = 'xls';
		} else if ($type == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
			// Extension excel 2007 y 2010
			$ext = 'xlsx';
		} else {
			// Extension no valida
			echo -1;
			exit();
		}

		$xlsx = 'Excel2007';
		$xls = 'Excel5';

		//creando el lector
		$objReader = PHPExcel_IOFactory::createReader($$ext);

		//cargamos el archivo
		$objPHPExcel = $objReader->load($target_file);

		$dim = $objPHPExcel->getActiveSheet()->calculateWorksheetDimension();

		// list coloca en array $start y $end Lista Coloca en array $ inicio y final $
		list($start, $end) = explode(':', $dim);

		if (!preg_match('#([A-Z]+)([0-9]+)#', $start, $rslt)) {
			return false;
		}
		list($start, $start_h, $start_v) = $rslt;
		if (!preg_match('#([A-Z]+)([0-9]+)#', $end, $rslt)) {
			return false;
		}
		list($end, $end_h, $end_v) = $rslt;

		//empieza  lectura vertical
		$table = "<table  border='1'>";

		//   $truncate="TRUNCATE TABLE nps";

		//	$rrtruncate = $this->connseguimiento->query($truncate);

		for ($v = $start_v; $v <= $end_v; $v++) {
			//empieza lectura horizontal
			if ($v == 1) {
				continue;
			}

			$table .= "<tr>";

			for ($h = $start_h; ord($h) <= ord($end_h); $this->pp($h)) {
				$cellValue = $this->getCell($h . $v, $objPHPExcel);
				$table .= "<td>";
				$guardar .= " '$cellValue',";
				if ($cellValue !== null) {
					$table .= $cellValue;
				}

				if ($h == "A") {
					$campanaid = $cellValue;
				}
				if ($h == "B") {
					$lanzamiento = $cellValue;
				}
				if ($h == "C") {
					$idllamada = $cellValue;
				}
				if ($h == "D") {
					$telefono = $cellValue;
				}
				if ($h == "E") {
					$mensaje = $cellValue;
				}
				if ($h == "F") {
					$accion = $cellValue;
				}
				if ($h == "G") {
					$fecha = \PHPExcel_Style_NumberFormat::toFormattedString($cellValue, 'YYYY-MM-DD');
				}
				if ($h == "H") {
					$idllamada2 = $cellValue;
				}
				if ($h == "I") {
					$estado = $cellValue;
				}
				if ($h == "J") {
					$cedula = $cellValue;
				}
				if ($h == "K") {
					$detalle = $cellValue;
				}
				if ($h == "L") {
					$cedula2 = $cellValue;
				}
				if ($h == "M") {
					$fecha_carga = $cellValue;
				}
				if ($h == "N") {
					$fecha_instalacion = \PHPExcel_Style_NumberFormat::toFormattedString($cellValue, 'YYYY-MM-DD');
				}
				if ($h == "O") {
					$departamento = utf8_decode($cellValue);
				}
				if ($h == "P") {
					$municipio = utf8_decode($cellValue);
				}
				if ($h == "Q") {
					$contratista = utf8_decode($cellValue);
				}
				if ($h == "R") {
					$interfaz = $cellValue;
				}
				if ($h == "S") {
					$producto = $cellValue;
				}
				if ($h == "T") {
					$tipo_solicitud = $cellValue;
				}
				if ($h == "U") {
					$pregunta = utf8_decode($cellValue);
				}
				if ($h == "V") {
					$respuesta = utf8_decode($cellValue);
				}
				if ($h == "W") {
					$presente = $cellValue;
				}
				if ($h == "X") {
					$regional = utf8_decode($cellValue);
				}
			}

			$dia = substr($fecha_instalacion, 8, 2);
			$mes = substr($fecha_instalacion, 5, 2);
			$anio = substr($fecha_instalacion, 0, 4);

			$nom_mes = date('M', mktime(0, 0, 0, $mes, $dia, $anio));
			$semana = "Semana " . date('W', mktime(0, 0, 0, $mes, $dia, $anio));

			$num_pregunta = substr($pregunta, 0, 1);
			$num_respuesta = substr($respuesta, 0, 1);
			if ($contratista == "SERVTECK") {
				$contratista = "SERVTEK";
			}

			$sql = "insert into nps (campanaid, lanzamiento, " .
				"idllamada, telefono, mensaje, accion, fecha, idllamada2, estado, cedula, detalle, fecha2, fecha_carga, " .
				"fecha_instalacion, departamento, municipio, regional, contratista, interfaz, producto, tipo_solicitud, " .
				"pregunta, respuesta, num_pregunta, num_respuesta, presente, semana, mes) " .
				"values('$campanaid', '$lanzamiento', '$idllamada', " .
				"'$telefono', '$mensaje', '$accion', '$fecha','$idllamada2','$estado', " .
				"'$cedula', '$detalle', '$cedula2', '$fecha_carga','$fecha_instalacion','$departamento', " .
				"'$municipio', '$regional', '$contratista', '$interfaz', '$producto','$tipo_solicitud','$pregunta', " .
				"'$respuesta', '$num_pregunta', '$num_respuesta', '$presente', '$semana', '$nom_mes');";

			$rst = $this->connseguimiento->query($sql);
		}
		//carga archivo de NPS, activity feed
	}

	//---reparacion---

	private function cargar_datosNPSreparacion() {

		ini_set('max_execution_time', 1000);

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		//ini_set('display_errors', '1');
		$target_dir = "../uploads/";
		$target_file = $target_dir . basename($_FILES["fileUpload"]["name"]);
		//$name     = $_FILES['fileUpload']['name'];
		$tname = $_FILES['fileUpload']['tmp_name'];
		$type = $_FILES['fileUpload']['type'];

		$login = $this->_request['user'];
		$fecha = date("Y-m-d H:i:s");
		$tname1 = basename($_FILES["fileUpload"]["name"]);

		$sql = "insert into carga_archivos_reparacion(nombre_archivo, tipo, login) values('$tname1', 'NPS', '$login')";
		$rst = $this->connseguimiento->query($sql);
		$id_carga = "0";
		$sql1 = "select max(id) as total_id from carga_archivos_reparacion";
		$rr = $this->connseguimiento->query($sql1);
		$id_carga = 0;
		if ($rr->num_rows > 0) {
			$result = array();
			if ($row = $rr->fetch_assoc()) {
				$id_carga = $row['total_id'];
			}
		}

		//$target_file = basename($_FILES["fileUpload"]["name"]);
		$uploadOk = 1;
		// Check if $uploadOk is set to 0 by an error
		if ($uploadOk == 0) {
			echo "Lo sentimos , el archivo no se ha subido.";
			// if everything is ok, try to upload file
		} else {

			if (move_uploaded_file($_FILES["fileUpload"]["tmp_name"], $target_file)) {
				echo "El archivo " . basename($_FILES["fileUpload"]["name"]) . " se ha subido";

			} else {

				echo "Ha habido un error al subir el archivo.";

			}
		}

		$tname1 = basename($_FILES["fileUpload"]["name"]);

		if ($type == 'application/vnd.ms-excel') {
			// Extension excel 97
			$ext = 'xls';
		} else if ($type == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
			// Extension excel 2007 y 2010
			$ext = 'xlsx';
		} else {
			// Extension no valida
			echo -1;
			exit();
		}

		$xlsx = 'Excel2007';
		$xls = 'Excel5';

		//creando el lector
		$objReader = PHPExcel_IOFactory::createReader($$ext);

		//cargamos el archivo
		$objPHPExcel = $objReader->load($target_file);

		$dim = $objPHPExcel->getActiveSheet()->calculateWorksheetDimension();

		// list coloca en array $start y $end Lista Coloca en array $ inicio y final $
		list($start, $end) = explode(':', $dim);

		if (!preg_match('#([A-Z]+)([0-9]+)#', $start, $rslt)) {
			return false;
		}
		list($start, $start_h, $start_v) = $rslt;
		if (!preg_match('#([A-Z]+)([0-9]+)#', $end, $rslt)) {
			return false;
		}
		list($end, $end_h, $end_v) = $rslt;

		//empieza  lectura vertical
		$table = "<table  border='1'>";

		//   $truncate="TRUNCATE TABLE nps";

		//  $rrtruncate = $this->connseguimiento->query($truncate);

		for ($v = $start_v; $v <= $end_v; $v++) {
			//empieza lectura horizontal
			if ($v == 1) {
				continue;
			}

			$table .= "<tr>";

			for ($h = $start_h; ord($h) <= ord($end_h); $this->pp($h)) {
				$cellValue = $this->getCell($h . $v, $objPHPExcel);
				$table .= "<td>";
				$guardar .= " '$cellValue',";
				// echo $cellValue;
				if ($cellValue !== null) {
					$table .= $cellValue;
				}

				if ($h == "A") {
					$CAMPANAID = $cellValue;
				}
				if ($h == "B") {
					$LANZAMIENTO = $cellValue;
				}
				if ($h == "C") {
					$IDLLAMADA = $cellValue;
				}
				if ($h == "D") {
					$TELEFONO = $cellValue;
				}
				if ($h == "E") {
					$MENSAJE = $cellValue;
				}
				if ($h == "F") {
					$ACCION = $cellValue;
				}
				if ($h == "G") {
					$FECHA = \PHPExcel_Style_NumberFormat::toFormattedString($cellValue, 'YYYY-MM-DD');
				}
				if ($h == "H") {
					$IDLLAMADA2 = $cellValue;
				}
				if ($h == "I") {
					$ESTADO = $cellValue;
				}
				if ($h == "J") {
					$CEDULA = $cellValue;
				}
				if ($h == "K") {
					$DETALLE = $cellValue;
				}
				if ($h == "L") {
					$FECHA_CARGA = \PHPExcel_Style_NumberFormat::toFormattedString($cellValue, 'YYYY-MM-DD');
				}
				if ($h == "M") {
					$FECHA_2 = $cellValue;
				}
				if ($h == "N") {
					$FECHA_CUMPLIDO = \PHPExcel_Style_NumberFormat::toFormattedString($cellValue, 'YYYY-MM-DD');
				}
				if ($h == "O") {
					$DEPARTAMENTO_DANE = utf8_decode($cellValue);
				}
				if ($h == "P") {
					$MUNICIPIO_DANE = utf8_decode($cellValue);
				}
				if ($h == "Q") {
					$NOMBRE_EMPRESA = $cellValue;
				}
				if ($h == "R") {
					$DESCRIPCION_INTERFAZ = utf8_decode($cellValue);
				}
				if ($h == "S") {
					$PRODUCTO_HOMOLOGADO = $cellValue;
				}
				if ($h == "T") {
					$TIPO_SOLICITUD = $cellValue;
				}
				if ($h == "U") {
					$PREGUNTA = utf8_decode($cellValue);
				}
				if ($h == "V") {
					$RESPUESTA = utf8_decode($cellValue);
				}
				if ($h == "W") {
					$PRESENTE = $cellValue;
				}

			}

			$anio = substr($FECHA_2, 0, 4);
			$mes = substr($FECHA_2, 5, 2);
			$dia = substr($FECHA_2, 8, 2);

			$nom_mes = date('M', mktime(0, 0, 0, $mes, $dia, $anio));
			$SEMANA = "Semana " . date('W', mktime(0, 0, 0, $mes, $dia, $anio));

			$num_pregunta = substr($PREGUNTA, 0, 1);
			$num_respuesta = substr($RESPUESTA, 0, 1);
			if ($NOMBRE_EMPRESA == "SERVTECK") {
				$NOMBRE_EMPRESA = "SERVTEK";
			}

			$sql = "insert into npsreparaciones (CAMPANAID, LANZAMIENTO, " .
				"IDLLAMADA, TELEFONO, MENSAJE, ACCION, FECHA, IDLLAMADA2, ESTADO, CEDULA, DETALLE, FECHA_CARGA, FECHA_2, " .
				"FECHA_CUMPLIDO, DEPARTAMENTO_DANE, MUNICIPIO_DANE, NOMBRE_EMPRESA, DESCRIPCION_INTERFAZ, PRODUCTO_HOMOLOGADO, TIPO_SOLICITUD, " .
				"PREGUNTA, RESPUESTA, NUM_PREGUNTA, NUM_RESPUESTA,PRESENTE, SEMANA, mes) " .
				"values('$CAMPANAID', '$LANZAMIENTO', '$IDLLAMADA', " .
				"'$TELEFONO', '$MENSAJE', '$ACCION', '$FECHA','$IDLLAMADA2','$ESTADO', " .
				"'$CEDULA', '$DETALLE', '$FECHA_CARGA','$FECHA_2', '$FECHA_CUMPLIDO','$DEPARTAMENTO_DANE','$MUNICIPIO_DANE', " .
				"'$NOMBRE_EMPRESA', '$DESCRIPCION_INTERFAZ', '$PRODUCTO_HOMOLOGADO', '$TIPO_SOLICITUD','$PREGUNTA','$RESPUESTA', " .
				"'$num_pregunta', '$num_respuesta','$PRESENTE', '$SEMANA', '$nom_mes');";
			//echo $sql;
			$rst = $this->connseguimiento->query($sql);
		}
		//carga archivo de NPS, activity feed
	}

	//--- fin reparacion ---

	private function DemePedidoPendiInsta() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		//$this->dbConnect();
		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$today = date("Y") . "-" . date("m") . "-" . date("d");
		$usuarioid = $params['user'];
		///definir prioridades para entrega de pedidos

		$query = "SELECT id, Pedido,	Interfaz, Tipo_solicitud_orig, Departamento, SubZona, Concepto_ATC,	Detalle_concepto_Oracle, " .
			"Concepto_Oracle, Rango_ingreso_dias, Estado_agenda, Detalle_producto, Causa_raiz, Responsable,	" .
			"Observacion, Novedad_malo,	Pendi_agen,	Finalizado_click, update_concepto_oracle, " .
			"fecha_agenda, marcacion, Asesor_carga, " .
			"cast((case when RANGO_INGRESO_DIAS = 'Mayor de 30' then '0' " .
			"when RANGO_INGRESO_DIAS = 'Entre 15-30' then '1' " .
			"when RANGO_INGRESO_DIAS = 'Entre 10-15' then '2' " .
			"when RANGO_INGRESO_DIAS = 'Entre 5-10' then '3' " .
			"else 4 " .
			"end) as char) prioridad  " .
			"FROM gestion_pendientes " .
			"where Novedad_malo is not null " .
			"and Pendi_agen = '' " .
			"and (bloqueo is null or bloqueo = 'NO') " .
			"and fecha_update is null " .
			"and actualizado is null " .
			"and Fecha_carga BETWEEN ('$today 00:00:00') AND ('$today 23:59:59') " .
			"order by prioridad ASC " .
			"limit 1 ";
		$rst = $this->connseguimiento->query($query);

		//echo $this->mysqli->query($sqlLogin);
		//
		if ($rst->num_rows > 0) {

			$resultado = array();

			while ($row = $rst->fetch_assoc()) {
				$resultado[] = $row;
				$id = $row['id'];
				$pedido = $row['Pedido'];
			}

			$sqlupdate = "UPDATE gestion_pendientes SET bloqueo='SI', Asesor_carga='$usuarioid' WHERE id='$id' ";

			$rstupdate = $this->connseguimiento->query($sqlupdate);

			$this->response($this->json(array($resultado)), 201);
		} else {
			$error = array();

			$this->response($this->json($error), 400);
		}
	}

	private function GuardarPedidoPendiInsta() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		//$this->dbConnect();
		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$today = date("Y") . "-" . date("m") . "-" . date("d");
		$usuarioid = $params['user'];
		$datospedidos = $params['datosdelpedido'][0];
		$infoGuardar = $params['info'];
		$Causa_raiz = $infoGuardar['causaraiz'];
		$responsable = $infoGuardar['responsable'];
		$observacion = $infoGuardar['observaciones'];

		$pedido = $datospedidos['Pedido'];
		$id = $datospedidos['id'];
		$Novedad_malo = $datospedidos['Novedad_malo'];
		$Finalizado_click = $datospedidos['Finalizado_click'];
		$update_concepto_oracle = $datospedidos['update_concepto_oracle'];
		$fecha_agenda = $datospedidos['fecha_agenda'];

		$query = "INSERT INTO historicoGestionPendientes " .
			"(id_gestion, pedido, causa_raiz, responsable, observacion, novedad_malo, finalizado_click, " .
			"update_concepto_oracle, fecha_agenda) " .
			"VALUES ('$id', '$pedido', '$Causa_raiz', '$responsable', '$observacion', '$Novedad_malo', '$Finalizado_click', " .
			"'$update_concepto_oracle', '$fecha_agenda') ";

		$rst = $this->connseguimiento->query($query);

		$sqlupdate = "UPDATE gestion_pendientes SET bloqueo='NO', causa_raiz='$Causa_raiz', responsable ='$responsable', " .
			"observacion='$observacion', Asesor_carga='null', fecha_update='$today', " .
			"actualizado ='SI' WHERE id='$id' ";

		$rstupdate = $this->connseguimiento->query($sqlupdate);

		//echo $this->mysqli->query($sqlLogin);
		//*/
	}

	///Turnos
	private function usuariosTurnos() {

		if ($this->get_request_method() != "GET") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$query = " select distinct login from usuarios where perfil = '5' " .
			"order by login ";

		$rst = $this->connseguimiento->query($query);
		if ($rst->num_rows > 0) {

			$resultado = array();
			while ($row = $rst->fetch_assoc()) {
				$resultado[] = $row;
			}

			$this->response($this->json(array($resultado)), 201);

		} else {
			$error = array();

			$this->response($this->json($error), 400);
		} // If no records "No Content" status
	}

	private function listaTurnos() {

		if ($this->get_request_method() != "GET") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$fechaini = $this->_request['fechaini'];
		$fechaFin = $this->_request['fechafin'];

		$querylistaturnos = "SELECT * " .
			"from turnosSeguimiento " .
			"where fecha between ('$fechaini') and ('$fechaFin')  " .
			"order by usuario ASC";

		$rstlista = $this->connseguimiento->query($querylistaturnos);

		if ($rstlista->num_rows > 0) {
			$resultado = array();

			while ($row = $rstlista->fetch_assoc()) {
				$resultado[] = $row;
			}

			$this->response($this->json(array($resultado)), 201);

		} else {
			$this->response($this->json($error), 400);
		}
	}

	private function deleteTurno() {

		if ($this->get_request_method() != "GET") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$idTurno = $this->_request['idTurno'];

		$querydeteTurno = "delete from turnosSeguimiento " .
			"where idturnos = '$idTurno' ";

		$rst = $this->connseguimiento->query($querydeteTurno);
	}

	private function exportEscalamientos() {

		if ($this->get_request_method() != "GET") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$query = "SELECT * FROM escalamiento_infraestructura ORDER BY fecha_solicitud";

		$rst = $this->connseguimiento->query($query);

		if ($rst->num_rows > 0) {
			$resultado = array();

			while ($row = $rst->fetch_assoc()) {
				$resultado[] = $row;
			}

			$this->response($this->json(array($resultado)), 201);

		} else {
			$this->response($this->json($error), 400);
		}
	}

	private function cumpleTurnos() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$datos = $params['datos'];
		$fecha = $datos['fechaIni'];

		$query = "select fecha, usuario, horaInicio, horaFin, totaTurno, c.fechaing, c.fecha_salida, " .
			"(case when c.status = 'logged off' then 'Deslogueado'  " .
			"else 'Logueado' end) status, c.total_dia,  " .
			"(case when totaTurno <=  c.total_dia then 'Cumple'  " .
			"else 'No cumple' end) cumple, " .
			"(case when horaInicio >=  c.fechaing then 'ingreso'  " .
			"else 'No ingreso' " .
			"end) ingreso " .
			"from turnosSeguimiento tur  " .
			"join (SELECT a.idusuario, date_format(a.fecha_ingreso,'%H:%i:%s') " .
			"fechaing, date_format(a.fecha_salida,'%H:%i:%s') fecha_salida,  " .
			"a.status, a.total_dia " .
			"FROM registro_ingresoSeguimiento a  " .
			"where a.fecha_ingreso BETWEEN ('$fecha 00:00:00')  " .
			"AND ('$fecha 23:59:59') ) c   " .
			"on c.idusuario = tur.usuario  " .
			"and fecha = '$fecha' ";

		$rst = $this->connseguimiento->query($query);

		if ($rst->num_rows > 0) {
			$resultado = array();
			while ($row = $rst->fetch_assoc()) {
				$resultado[] = $row;
			}

			$queryAusencia = "SELECT usuario,  horaInicio, horaFin, novedades " .
				"from   turnosSeguimiento tur " .
				"where fecha = '$fecha' " .
				"and not EXISTS (SELECT idusuario  " .
				"from registro_ingresoSeguimiento ing " .
				"where  fecha_ingreso BETWEEN ('$fecha 00:00:00') " .
				"AND ('$fecha 23:59:59') " .
				"and  tur.usuario = ing.idusuario); ";

			$rstAusencia = $this->connseguimiento->query($queryAusencia);

			if ($rstAusencia->num_rows > 0) {
				while ($row2 = $rstAusencia->fetch_assoc()) {

					$row['fecha'] = $fecha;
					$row['usuario'] = $row2['usuario'];
					$row['horaInicio'] = $row2['horaInicio'];
					$row['horaFin'] = $row2['horaFin'];
					$row['fechaing'] = "00:00:00";
					$row['fecha_salida'] = "00:00:00";
					$row['ingreso'] = "Sin logueo";
					$row['cumple'] = $row2['novedades'];
					$row['status'] = "Sin logueo";
					$resultado[] = $row;
				}
			}

			$this->response($this->json(array($resultado)), 201);

		} else {
			$this->response($this->json($error), 400);
		}
	}

	private function guardarTurnos() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$datosTurnos = $params['datosTurnos'];

		$total = count($datosTurnos);

		for ($i = 0; $i < $total; $i++) {

			$fecha = $datosTurnos[$i]['fecha'];
			$horaFin = $datosTurnos[$i]['horaFin'];
			$horaIni = $datosTurnos[$i]['horaInicio'];
			$usuario = $datosTurnos[$i]['usuario'];
			$usuariocrea = $datosTurnos[$i]['usuariocrea'];
			$novedad = $datosTurnos[$i]['novedad'];

			if ($novedad == null) {
				$novedad = 'Turno';
			}

			$horaIni = date('H:i', strtotime($horaIni));
			$horaFin = date('H:i', strtotime($horaFin));
			$dif = date("H:i", strtotime("00:00:00") + strtotime($horaFin) - strtotime($horaIni));

			$insert = " INSERT INTO turnosSeguimiento " .
				" (fecha, usuario, horaInicio, horaFin, totaTurno, novedades, usuarioCrea) " .
				" values ('$fecha', '$usuario', '$horaIni','$horaFin', '$dif', '$novedad', '$usuariocrea')";

			$rst = $this->connseguimiento->query($insert);
		}
	}

	private function updateTurno() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$datosTurnos = $params['datos'];

		$id = $datosTurnos['idturnos'];
		$horaFin = $datosTurnos['horaFin'];
		$horaIni = $datosTurnos['horaInicio'];
		$novedad = $datosTurnos['novedades'];

		$update = "UPDATE turnosSeguimiento " .
			"SET horaInicio = '$horaIni', " .
			"horaFin = '$horaFin',   " .
			"novedades='$novedad' " .
			"WHERE idturnos='$id' ";

		$rst = $this->connseguimiento->query($update);
	}

	private function CsvExporteAdherencia() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$fechaIni = $params['fechaIni'];
		$fechaFin = $params['fechaFin'];
		$login = $params['login'];
		$login = $login['LOGIN'];

		$filename = "AdherenciaTurnos" . "_" . $fechaIni . "_" . $fechaFin . "_" . $login . ".csv";

		$query = "select  c.date, usuario, horaInicio, horaFin, totaTurno, " .
			"c.fechaing, c.fecha_salida,   " .
			"(case when c.status = 'logged off' then 'Deslogueado'   " .
			"else 'Logueado' end) status, c.total_dia,   " .
			"(case when totaTurno <=  c.total_dia then 'Cumple'   " .
			"else 'No cumple' end) cumple,  " .
			"(case when horaInicio >=  c.fechaing then 'OK'   " .
			"else 'Tarde' end) ingreso " .
			"from turnosSeguimiento tur  " .
			"	join (SELECT a.idusuario, date_format(a.fecha_ingreso,'%Y-%m-%d') date, " .
			"		date_format(a.fecha_ingreso,'%H:%i:%s') " .
			"		fechaing, date_format(a.fecha_salida,'%H:%i:%s') fecha_salida,  " .
			"		a.status, a.total_dia " .
			"		FROM registro_ingresoSeguimiento a " .
			"		where  a.fecha_ingreso BETWEEN ('$fechaIni 00:00:00')  " .
			"		AND ('$fechaFin 23:59:59')) c  " .
			"on c.idusuario = tur.usuario  " .
			"and fecha = c.date ";

		$rst = $this->connseguimiento->query($query) or die($this->connseguimiento->error . __LINE__);

		$counter = $rst->num_rows;

		if ($rst->num_rows > 0) {

			$result = array();
			$fp = fopen("../tmp/$filename", 'w');
			//echo $fp;

			$columnas = array('FECHA',
				'USUARIO',
				'INICIO_TURNO',
				'FIN_TURNO',
				'TOTAL_TURNO',
				'HORA_INGRESO',
				'HORA_SALIDA',
				'ESTADO_FINAL',
				'TOTAL_CONEXION',
				'CUMPLIMIENTO_HORARIO',
				'CUMPLIMIENTO_INGRESO');

			fputcsv($fp, $columnas);
			//$carlitos=0;
			while ($row = $rst->fetch_assoc()) {
				//$result[] = $row;
				fputcsv($fp, $row);
				//if($carlitos==0){var_dump($row);$carlitos=1;};
			}

			fclose($fp);

			$this->response($this->json(array($filename, $counter)), 200);
		}
		$this->response('', 203); // If no records "No Content" status
		//descarga archivo estados click, activity feed
	}

	private function guardarRecogerEquipos() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);
		$datos = $params['equipos'];

		$total=count($datos);

		for ($i = 0; $i < $total; $i++){

			$pedido = ($datos[$i]["pedido"]);
			$mac = ($datos[$i]["mac"]);
			$serial = ($datos[$i]["serial"]);
			$ciudad = ($datos[$i]["ciudad"]);
			$CedTecnico = ($datos[$i]["CedTecnico"]);
    		$NomTecnico = ($datos[$i]["NomTecnico"]);
    		$contratista = ($datos[$i]["contratista"]);
    		$fechahora = date("Y-m-d H:i:s");

			$valores='("'.$pedido.'","Recoger Equipo","'.$fechahora.'","'.$ciudad.'","'.$CedTecnico.'","'.$NomTecnico.'","'.$contratista.'","'.$mac.'","'.$serial.'"),';
			$valoresQ=$valoresQ.$valores;
		}

		$valoresQ[strlen($valoresQ)-1] = ";";

		 $sql = "INSERT INTO recogidaequipos (pedido, motivo, fecha, ciudad, CedTecnico, NomTecnico, contratista, mac, serialEq)
		 VALUES $valoresQ";
		 echo "$sql";

			$rst = $this->connseguimiento->query($sql);
	}

	// INICIO CONTRASEÑAS TECNICOS

	private function registrospwdTecnicos() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		//$this->dbConnect();
		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);
		$datos = $params['datos'];
		$concepto = $datos['concepto'];
		$buscar = $datos['buscar'];
		$parametro = " and $concepto = '$buscar'";


		$query = "SELECT c.cedula, c.login, c.nombre, c.password, c.expiraCuenta, c.expirapsw FROM cuentasTecnicos c where 1=1 $parametro ";
		// var_dump($query);

		$rst = $this->connseguimiento->query($query);

		if ($rst->num_rows > 0) {

			$resultado = array();

			while ($row = $rst->fetch_assoc()) {

				$row['cedula'] = utf8_encode($row['cedula']);
				$row['login'] = utf8_encode($row['login']);
				$row['nombre'] = utf8_encode($row['nombre']);
				$row['password'] = utf8_encode($row['password']);
				$row['expiraCuenta'] = utf8_encode($row['expiraCuenta']);
				$row['expirapsw'] = utf8_encode($row['expirapsw']);

				$resultado[] = $row;

			}

			$this->response($this->json(array($resultado)), 201);

		} else {
			$error = array();

			$this->response($this->json($error), 400);
		}
	}

	private function editarPwdTecnicos() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$datos = $params['datosEdicion'];
		$cedula = $datos['cedula'];
		$pwd = $datos['newpwd'];

		$sqlUsuario = "update cuentasTecnicos set password='$pwd' where cedula='$cedula'";

		$rst = $this->connseguimiento->query($sqlUsuario);

		$this->response($this->json('Usuario actualizado'), 201);
	}

	private function csvContrasenasTecnicos() {
		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$usuarioid = $params['datosLogin'];
		$usuarioid = $usuarioid['LOGIN'];
		$filename = "ContrasenasTecnicosClick" . "_" . $usuarioid . ".csv";


		$query = ("	SELECT c.cedula, c.login, c.nombre, c.password, c.expiraCuenta, c.expirapsw
						FROM cuentasTecnicos c
						WHERE 1=1
				");


		$rst = $this->connseguimiento->query($query) or die($this->connseguimiento->error . __LINE__);

		if ($rst->num_rows > 0) {

			$result = array();
			$fp = fopen("../tmp/$filename", 'w');

			$columnas = array('Fecha',
				'Login',
				'Nombre',
				'Password',
				'Expira Cuenta',
				'Expira PSW'
			);

			fputcsv($fp, $columnas);

			while ($row = $rst->fetch_assoc()) {

				$row['cedula'] = utf8_encode($row['cedula']);
				$row['login'] = ($row['login']);
				$row['nombre'] = ($row['nombre']);
				$row['password'] = ($row['password']);
				$row['expiraCuenta'] = ($row['expiraCuenta']);
				$row['expirapsw'] = utf8_encode($row['expirapsw']);

				//$result[] = $row;
				fputcsv($fp, $row);

			}

			fclose($fp);

			$this->response($this->json(array($filename)), 200);
		}
		$this->response('', 203);
	}

	// FIN CONTRASEÑAS TECNICOS

	//Turnos

	//Funcion para remover sibolos de los campos.
	function quitar_tildes($cadena) {
		//echo 'recibi cadena'.$cadena;
		$no_permitidas = array("á", "é", "í", "ó", "ú", "Á", "É", "Í", "Ó", "Ú", "ñ", "À", "Ã", "Ì", "Ò", "Ù", "Ã™", "Ã ", "Ã¨", "Ã¬", "Ã²", "Ã¹", "ç", "Ç", "Ã¢", "ê", "Ã®", "Ã´", "Ã»", "Ã‚", "ÃŠ", "ÃŽ", "Ã”", "Ã›", "ü", "Ã¶", "Ã–", "Ã¯", "Ã¤", "«", "Ò", "Ã", "Ã„", "Ã‹");
		$permitidas = array("a", "e", "i", "o", "u", "A", "E", "I", "O", "U", "n", "N", "A", "E", "I", "O", "U", "a", "e", "i", "o", "u", "c", "C", "a", "e", "i", "o", "u", "A", "E", "I", "O", "U", "u", "o", "O", "i", "a", "e", "U", "I", "A", "E");
		$texto = str_replace($no_permitidas, $permitidas, $cadena);
		//echo "\nsalida".$texto;
		return $texto;
	}

	function clean_chars($string) {
		return preg_replace('/^\s+|\n|\r|\s+$/m', '', $string);
	}

	function getCell($cell, $objPHPExcel) {
		//select one cell seleccionar una célda
		$objCell = ($objPHPExcel->getActiveSheet()->getCell($cell));
		//get cell value obtener valor de la celda
		return $objCell->getvalue();
	}

	function pp(&$var) {
		$var = chr(ord($var) + 1);
		return true;
	}

	/* ---------------------------------------- SOPORTE GPON ---------------------------------------- */

	private function getSoporteGponByTask() {

		if ($this->get_request_method() != "GET") {
			$this->response('', 406);
		}

		$task = $_GET['task'];

		/*CONEXION A LA BASE DE DATOS*/
		$this->response($this->json(array()), 201);
		die();

		$this->dbSeguimientoConnect();

		$hoy = date("Y-m-d H:i:s");

		/*LOGICA QUE LLEVA LA INFORMACION A TV, INTERNET-ToIp Y CORREGIR PORTAFOLIO*/
		$query = ("SELECT 
		wt.UNEPedido, 
		wt2.Name 'categoria', 
		wt.UNEMunicipio, 
		wt.UNEProductos, 
		wt.EngineerID, 
		wt.EngineerName, 
		we.MobilePhone, 
		wu2.SerialNo,
		wu2.MAC,
		wu2.TipoEquipo,
		wu3.VelocidadNavegacion, 
		wts.Name 'status', 
		wu.UNEPlanProducto
		FROM W6TASKS wt
		INNER JOIN W6TASK_STATUSES wts ON wts.W6Key = wt.Status
		LEFT JOIN W6TASKTYPECATEGORY wt2 ON wt2.W6Key = wt.TaskTypeCategory
		LEFT JOIN W6UNESERVICES wu ON wu.ParentTaskCallID = wt.CallID
		LEFT JOIN W6ENGINEERS we ON we.ID = wt.EngineerID
		LEFT JOIN W6UNEEQUIPMENTUSED wu2 ON wu2.TaskCallID = wt.CallID
		LEFT JOIN W6UNESERVICES wu3 ON wu3.ParentTaskCallID = wt.CallID
		WHERE wt.CallID = '$task' AND wts.Name = 'En Sitio' AND wu2.TipoEquipo IS NOT NULL AND wu3.VelocidadNavegacion IS NOT NULL
		GROUP BY wt.UNEPedido, wt2.Name, wt.UNEMunicipio, wt.UNEProductos, wt.EngineerID, wt.EngineerName, we.MobilePhone, wu2.SerialNo, wu2.MAC, wu2.TipoEquipo, wu3.VelocidadNavegacion, wts.Name, wu.UNEPlanProducto;");

		$rst = $this->connseguimiento->query($query) or die($this->connseguimiento->error . __LINE__);

		$this->response($this->json(array($rst)), 201);
		die();

		if ($rst->num_rows > 0) {

			$resultSoporteGpon = array();
			while ($row = $rst->fetch_assoc()) {
				// SE AGREGA LA DETENCION DE PORTAFOLIO PARA RECONOZCA EN VALOR
				$resultSoporteGpon[] = $row;
			}

			$this->response($this->json(array($resultSoporteGpon)), 201);

		} else {
			$error = array();
			$this->response($this->json($error), 400);
		}
	}

	private function postPendientesSoporteGpon() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$task = $params['task'];
		$arpon = $params['arpon'];
		$nap = $params['nap'];
		$hilo = $params['hilo'];
		$internet1 = $params['internet1'];
		$internet2 = $params['internet2'];
		$internet3 = $params['internet3'];
		$internet4 = $params['internet4'];
		$television1 = $params['television1'];
		$television2 = $params['television2'];
		$television3 = $params['television3'];
		$television4 = $params['television4'];
		$numeroContacto = $params['numeroContacto'];
		$nombreContacto = $params['nombreContacto'];
		$user_id = $params['user_id'];
		$request_id = $params['request_id'];
		$user_identification = $params['user_identification'];
		$fecha_solicitud = $params['fecha_solicitud'];
		$unepedido = $params['unepedido'];
		$tasktypecategory = $params['tasktypecategory'];
		$unemunicipio = $params['unemunicipio'];
		$uneproductos = $params['uneproductos'];
		$datoscola = $params['datoscola'];
		$engineer_id = $params['engineer_id'];
		$engineer_name = $params['engineer_name'];
		$mobile_phone = $params['mobile_phone'];
		$serial = $params['serial'];
		$mac = $params['mac'];
		$tipo_equipo = $params['tipo_equipo'];
		$velocidad_navegacion = $params['velocidad_navegacion'];
		$observacionTerreno = $params['observacionTerreno'];

		$fecha_creado = date('Y-m-d H:i:s');
		$hoy = date('Y-m-d');

		$query = "SELECT * FROM soporte_gpon WHERE tarea = '$task' AND status_soporte = '0' ";
		$select = $this->connseguimiento->query($query);

		if ($select->num_rows == 0) {

			$query = "INSERT INTO soporte_gpon (tarea, arpon, nap, hilo, port_internet_1, port_internet_2, port_internet_3, port_internet_4, port_television_1, port_television_2, port_television_3, port_television_4, numero_contacto, nombre_contacto, unepedido, tasktypecategory, unemunicipio, uneproductos, datoscola, engineer_id, engineer_name, mobile_phone, serial, mac, tipo_equipo, velocidad_navegacion, user_id_firebase, request_id_firebase, user_identification_firebase, status_soporte, fecha_solicitud_firebase, fecha_creado, observacion_terreno) VALUES  ('$task', '$arpon', '$nap', '$hilo', '$internet1', '$internet2', '$internet3', '$internet4', '$television1', '$television2', '$television3', '$television4', '$numeroContacto', '$nombreContacto', '$unepedido', '$tasktypecategory', '$unemunicipio', '$uneproductos', '$datoscola', '$engineer_id', '$engineer_name', '$mobile_phone', '$serial', '$mac', '$tipo_equipo', '$velocidad_navegacion', '$user_id', '$request_id', '$user_identification', '0', '$fecha_solicitud', '$fecha_creado', '$observacionTerreno');";
			$insert = $this->connseguimiento->query($query);

			if ($insert === TRUE) {
				$this->response($this->json(array('type' => 'success', 'msg' => 'OK')), 201);
			} else {
				$this->response($this->json(array('type' => 'Error', 'msg' => $this->connseguimiento->error)), 400);
			}

		} else {
			$idsupport = '';
			while($row = $select->fetch_assoc()) {
				$idsupport = $row["id_soporte"];
			}

			$query = "UPDATE soporte_gpon SET unepedido = '$unepedido', tasktypecategory = '$tasktypecategory', unemunicipio = '$unemunicipio', uneproductos = '$uneproductos', datoscola = '$datoscola', engineer_id = '$engineer_id', engineer_name = '$engineer_name', mobile_phone = '$mobile_phone', serial = '$serial', mac = '$mac', tipo_equipo = '$tipo_equipo', velocidad_navegacion = '$velocidad_navegacion', observacion_terreno = '$observacionTerreno' WHERE id_soporte = '$idsupport';";
			$update = $this->connseguimiento->query($query);

			if ($update === TRUE) {
				$this->response($this->json(array('type' => 'success', 'msg' => 'OK', 'query' => $query)), 201);
			} else {
				$this->response($this->json(array('type' => 'Error', 'msg' => $this->connseguimiento->error)), 400);
			}
		}
	}

	private function getListaPendientesSoporteGpon() {

		if ($this->get_request_method() != "GET") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$hoy = date("Y-m-d");

		/*LOGICA QUE LLEVA LA INFORMACION A TV, INTERNET-ToIp Y CORREGIR PORTAFOLIO*/
		$query = "SELECT * FROM soporte_gpon WHERE fecha_creado BETWEEN '$hoy 00:00:00' AND '$hoy 23:59:59' AND status_soporte != '1'";

		$rst = $this->connseguimiento->query($query) or die($this->connseguimiento->error . __LINE__);

		if ($rst->num_rows > 0) {

			$resultSoporteGpon = array();
			while ($row = $rst->fetch_assoc()) {
				// SE AGREGA LA DETENCION DE PORTAFOLIO PARA RECONOZCA EN VALOR
				$resultSoporteGpon[] = $row;
			}

			$this->response($this->json(array($resultSoporteGpon)), 201);

		} else {
			$error = array();
			$this->response($this->json($error), 400);
		}
	}

	private function gestionarSoporteGpon() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$id_soporte = $params['id_soporte'];
		$tipificacion = $params['tipificacion'];
		$observacion = $params['observacion'];
		$login = $params['login'];
		$login = $login['LOGIN'];

		$fecha_respuesta = date('Y-m-d H:i:s');

		$query = "UPDATE soporte_gpon SET respuesta_soporte = '$tipificacion', observacion = '$observacion', login = '$login', fecha_respuesta = '$fecha_respuesta', status_soporte = '1' WHERE id_soporte = '$id_soporte';";
		$insert = $this->connseguimiento->query($query);

		if ($insert === TRUE) {
			$this->response($this->json(array('type' => 'success', 'msg' => 'OK')), 201);
		} else {
			$this->response($this->json(array('type' => 'Error', 'msg' => $this->connseguimiento->error)), 400);
		}


	}

	private function validarLlenadoSoporteGpon() {

		if ($this->get_request_method() != "GET") {
			$this->response('', 406);
		}

		$task = $_GET['task'];

		$this->dbSeguimientoConnect();


		$query = ("SELECT id_soporte, tarea, unepedido FROM soporte_gpon WHERE tarea = '$task' ORDER BY fecha_creado DESC LIMIT 1;");

		$rst = $this->connseguimiento->query($query) or die($this->connseguimiento->error . __LINE__);

		if ($rst->num_rows > 0) {

			$resultSoporteGpon = array();
			while ($row = $rst->fetch_assoc()) {
				// SE AGREGA LA DETENCION DE PORTAFOLIO PARA RECONOZCA EN VALOR
				$resultSoporteGpon[] = $row;
			}

			$this->response($this->json(array($resultSoporteGpon)), 201);

		} else {
			$error = array();
			$this->response($this->json($error), 400);
		}


	}

	private function registrossoportegpon() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		//$this->dbConnect();
		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);
		$pagina = $params['page'];
		$datos = $params['datos'];

		$fechaini = (!isset($datos['fechaini'])) ? date("Y-m-d") : $datos['fechaini']; //CORRECCION DE VALIDACION DE FECHA
		$fechafin = (!isset($datos['fechafin'])) ? date("Y-m-d") : $datos['fechafin']; //CORRECCION DE VALIDACION DE FECHA

		if ($fechaini == "" || $fechafin == "") {
			$fechaini = date("Y-m-d");
			$fechafin = date("Y-m-d");
		}
		//$today = date("Y-m-d");

		if ($pagina == "undefined") {
			$pagina = "0";
		} else {
			$pagina = $pagina - 1;
		}

		$pagina = $pagina * 100;

		$query = "SELECT id_soporte, tarea, arpon, nap, hilo, port_internet_1, port_internet_2, port_internet_3, port_internet_4, port_television_1, port_television_2, port_television_3, port_television_4, numero_contacto, nombre_contacto, unepedido, tasktypecategory, unemunicipio, uneproductos, datoscola, engineer_id, engineer_name, mobile_phone, serial, mac, tipo_equipo, velocidad_navegacion, user_id_firebase, request_id_firebase, user_identification_firebase, status_soporte, fecha_solicitud_firebase, fecha_creado, respuesta_soporte, observacion, observacion_terreno, login, fecha_respuesta, fecha_marca 
		FROM soporte_gpon 
		WHERE fecha_respuesta BETWEEN '$fechaini 00:00:00' AND '$fechafin 23:59:59' AND status_soporte = '1'
		ORDER BY fecha_creado DESC
		LIMIT 100 offset $pagina;";

		$queryCount = "SELECT COUNT(tarea) as Cantidad
		FROM soporte_gpon 
		WHERE fecha_creado BETWEEN '$fechaini 00:00:00' AND '$fechafin 23:59:59'
		ORDER BY fecha_creado DESC;";

		//echo $queryCount;

		$rr = $this->connseguimiento->query($queryCount);

		$counter = 0;
		if ($rr->num_rows > 0) {
			$result = array();
			if ($row = $rr->fetch_assoc()) {
				$counter = $row['Cantidad'];
			}
		}

		$rst = $this->connseguimiento->query($query);

		//echo $this->mysqli->query($sqlLogin);
		//
		if ($rst->num_rows > 0) {

			$resultado = array();

			while ($row = $rst->fetch_assoc()) {

				//var_dump($row);
				$row['id_soporte'] = utf8_encode($row['id_soporte']);
				$row['tarea'] = utf8_encode($row['tarea']);
				$row['arpon'] = utf8_encode($row['arpon']);
				$row['nap'] = utf8_encode($row['nap']);
				$row['hilo'] = utf8_encode($row['hilo']);
				$row['port_internet_1'] = utf8_encode($row['port_internet_1']);
				$row['port_internet_2'] = utf8_encode($row['port_internet_2']);
				$row['port_internet_3'] = utf8_encode($row['port_internet_3']);
				$row['port_internet_4'] = utf8_encode($row['port_internet_4']);
				$row['port_television_1'] = utf8_encode($row['port_television_1']);
				$row['port_television_2'] = utf8_encode($row['port_television_2']);
				$row['port_television_3'] = utf8_encode($row['port_television_3']);
				$row['port_television_4'] = utf8_encode($row['port_television_4']);
				$row['numero_contacto'] = utf8_encode($row['numero_contacto']);
				$row['nombre_contacto'] = utf8_encode($row['nombre_contacto']);
				$row['unepedido'] = utf8_encode($row['unepedido']);
				$row['tasktypecategory'] = utf8_encode($row['tasktypecategory']);
				$row['unemunicipio'] = utf8_encode($row['unemunicipio']);
				$row['uneproductos'] = utf8_encode($row['uneproductos']);
				$row['datoscola'] = utf8_encode($row['datoscola']);
				$row['engineer_id'] = utf8_encode($row['engineer_id']);
				$row['engineer_name'] = utf8_encode($row['engineer_name']);
				$row['mobile_phone'] = utf8_encode($row['mobile_phone']);
				$row['serial'] = utf8_encode($row['serial']);
				$row['mac'] = utf8_encode($row['mac']);
				$row['tipo_equipo'] = utf8_encode($row['tipo_equipo']);
				$row['velocidad_navegacion'] = utf8_encode($row['velocidad_navegacion']);
				$row['user_id_firebase'] = utf8_encode($row['user_id_firebase']);
				$row['request_id_firebase'] = utf8_encode($row['request_id_firebase']);
				$row['user_identification_firebase'] = utf8_encode($row['user_identification_firebase']);
				$row['status_soporte'] = utf8_encode($row['status_soporte']);
				$row['fecha_solicitud_firebase'] = utf8_encode($row['fecha_solicitud_firebase']);
				$row['fecha_creado'] = utf8_encode($row['fecha_creado']);
				$row['respuesta_soporte'] = utf8_encode($row['respuesta_soporte']);
				$row['observacion'] = utf8_encode($row['observacion']);
				$row['observacion_terreno'] = utf8_encode($row['observacion_terreno']);
				$row['login'] = utf8_encode($row['login']);
				$row['fecha_respuesta'] = utf8_encode($row['fecha_respuesta']);

				//var_dump($row);
				$resultado[] = $row;

			}
			$this->response($this->json(array($resultado, $counter)), 201);

		} else {
			$error = array();

			$this->response($this->json($error), 400);
		}
	}

	private function csvRegistrosSoporteGpon() {
		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$usuarioid = $params['datosLogin'];
		$usuarioid = $usuarioid['LOGIN'];
		$datos = $params['datos'];
		$fechaini = $datos['fechaini'];
		$fechafin = $datos['fechafin'];

		if ($fechaini == "" && $fechafin == "") {
			$fechaini = date("Y") . "-" . date("m") . "-" . date("d");
			$fechafin = date("Y") . "-" . date("m") . "-" . date("d");
		}

		//echo "estos son los datos, usuario: ".$usuarioid." fechaini: ".$fechaini." y fechafin: ".$fechafin;
		//echo "estos son los otros concepto, buscar: ".$concepto." buscar: ".$buscar;
		if ($fechaini == $fechafin) {
			$filename = "Registros" . "_" . $fechaini . "_" . $concepto . "_" . $buscar . ".csv";
		} else {
			$filename = "Registros" . "_" . $fechaini . "_" . $fechafin . "_" . $concepto . "_" . $buscar . ".csv";
		}

		$query = "SELECT tarea, arpon, nap, hilo, port_internet_1, port_internet_2, port_internet_3, port_internet_4, port_television_1, port_television_2, port_television_3, port_television_4, numero_contacto, nombre_contacto, unepedido, tasktypecategory, unemunicipio, uneproductos, datoscola, engineer_id, engineer_name, mobile_phone, serial, mac, tipo_equipo, velocidad_navegacion, user_id_firebase, request_id_firebase, user_identification_firebase, status_soporte, fecha_solicitud_firebase, fecha_creado, respuesta_soporte, observacion, observacion_terreno, login, fecha_respuesta, fecha_marca 
		FROM soporte_gpon 
		WHERE fecha_creado BETWEEN '$fechaini 00:00:00' AND '$fechafin 23:59:59' /*AND status_soporte = '1'*/
		ORDER BY fecha_creado DESC;";

		$queryCount = "SELECT COUNT(tarea) as Cantidad
		FROM soporte_gpon 
		WHERE fecha_creado BETWEEN '$fechaini 00:00:00' AND '$fechafin 23:59:59'
		ORDER BY fecha_creado DESC;";

		//s    echo $queryCount;
		//

		$rr = $this->connseguimiento->query($queryCount);
		$counter = 0;
		if ($rr->num_rows > 0) {
			$result = array();
			if ($row = $rr->fetch_assoc()) {
				$counter = $row['Cantidad'];
			}
		}
		//echo $counter;

		$rst = $this->connseguimiento->query($query) or die($this->connseguimiento->error . __LINE__);

		if ($rst->num_rows > 0) {

			//Insert en log
			//$sql_log="insert into gestor_despacho.gopd_logoperaciones (USUARIO_ID, TIPO_ACTIVIDAD, DESCRIPCION, IDENTIFICADOR, IP, PC) values(UPPER('$usuarioid'),'EXPORTE','EXPORTO CSV_HISTORICO','LOGIN: $usuarioid','$usuarioIp','$usuarioPc')";

			//$rlog = $this->connemtel->query($sql_log) or die($this->connemtel->error.__LINE__);
			//Insert en log

			$result = array();
			$fp = fopen("../tmp/$filename", 'w');
			//echo $fp;

			$columnas = array(
				'TAREA',
				'ARPON',
				'NAP',
				'HILO',
				'PORT_INTERNET_1',
				'PORT_INTERNET_2',
				'PORT_INTERNET_3',
				'PORT_INTERNET_4',
				'PORT_TELEVISION_1',
				'PORT_TELEVISION_2',
				'PORT_TELEVISION_3',
				'PORT_TELEVISION_4',
				'NUMERO_CONTACTO',
				'NOMBRE_CONTACTO',
				'UNEPEDIDO',
				'TASKTYPECATEGORY',
				'UNEMUNICIPIO',
				'UNEPRODUCTOS',
				'DATOSCOLA',
				'ENGINEER_ID',
				'ENGINEER_NAME',
				'MOBILE_PHONE',
				'SERIAL',
				'MAC',
				'TIPO_EQUIPO',
				'VELOCIDAD_NAVEGACION',
				'USER_ID_FIREBASE',
				'REQUEST_ID_FIREBASE',
				'USER_IDENTIFICATION_FIREBASE',
				'STATUS_SOPORTE',
				'FECHA_SOLICITUD_FIREBASE',
				'FECHA_CREADO',
				'RESPUESTA_SOPORTE',
				'OBSERVACION',
				'OBSERVACION TERRENO',
				'LOGIN',
				'FECHA_RESPUESTA',
				'FECHA_MARCA',
			);

			fputcsv($fp, $columnas);
			//$carlitos=0;
			while ($row = $rst->fetch_assoc()) {

				//$row['observaciones'] = $row['observaciones'];
				//$result[] = $row;
				fputcsv($fp, $row);
				//if($carlitos==0){var_dump($row);$carlitos=1;};
			}

			fclose($fp);

			$this->response($this->json(array($filename, $counter)), 200);
		}
		$this->response('', 203); // If no records "No Content" status
		//descarga archivo registros, forma asesores, activity feed
	}

	private function marcarEngestionGpon() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		//$this->dbConnect();
		$today = date("Y-m-d H:i:s");
		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$datosguardar = $params['datos'];
		$login = $params['login'];
		$login = $login['LOGIN'];
		$id_soporte = $datosguardar['id_soporte'];
		$status_soporte = $datosguardar['status_soporte'];

		if ($status_soporte == '2') {
			$gestion = 1;
		} else {
			$gestion = 0;
		}

		$query = "SELECT id_soporte, login FROM soporte_gpon WHERE id_soporte = '$id_soporte' AND status_soporte = '2' AND login IS NOT NULL;";

		$rst = $this->connseguimiento->query($query);

		if ($rst->num_rows > 0) {

			$resultado = array();

			while ($row = $rst->fetch_assoc()) {
				$resultado[] = $row;
				$loginsoportegpon = $row['login'];
				$id = $row['id_soporte'];
			}

			if ($login == $loginsoportegpon) {

				$sqlupdate = "UPDATE soporte_gpon SET status_soporte = '0', login = NULL, fecha_marca = '$today' WHERE id_soporte ='$id'";

				$this->connseguimiento->query($sqlupdate);
				$this->response($this->json(array("desbloqueado")), 200);
			} else {
				$this->response($this->json(array($resultado)), 200);
			}

		} else {

			$query = "SELECT id_soporte, login FROM soporte_gpon WHERE id_soporte = '$id_soporte' AND status_soporte = '0' AND login IS NULL;";

			$rst = $this->connseguimiento->query($query);

			if ($rst->num_rows > 0) {

				$resultado = array();

				while ($row = $rst->fetch_assoc()) {
					$resultado[] = $row;
					$id = $row['id_soporte'];
				}

				$sqlupdate = "UPDATE soporte_gpon SET status_soporte = 2, login = '$login', fecha_marca = '$today' WHERE id_soporte = '$id';";

				$rstupdate = $this->connseguimiento->query($sqlupdate);
			}
		}
	}

	/* ---------------------------------------- SOPORTE GPON ---------------------------------------- */

	/* -------------------------------------- CODIGO INCOMPLETO ------------------------------------- */

	private function getListaCodigoIncompleto() {

		if ($this->get_request_method() != "GET") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$hoy = date("Y-m-d");

		/*LOGICA QUE LLEVA LA INFORMACION A TV, INTERNET-ToIp Y CORREGIR PORTAFOLIO*/
		$query = ("SELECT * FROM gestion_codigo_incompleto  WHERE fecha_creado BETWEEN '$hoy 00:00:00' AND '$hoy 23:59:59' AND status_soporte = '0'");

		$rst = $this->connseguimiento->query($query) or die($this->connseguimiento->error . __LINE__);

		if ($rst->num_rows > 0) {

			$resultSoporteGpon = array();
			while ($row = $rst->fetch_assoc()) {
				// SE AGREGA LA DETENCION DE PORTAFOLIO PARA RECONOZCA EN VALOR
				$resultSoporteGpon[] = $row;
			}

			$this->response($this->json(array($resultSoporteGpon)), 201);

		} else {
			$error = array();
			$this->response($this->json($error), 400);
		}
	}

	private function gestionarCodigoIncompleto() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$id_codigo_incompleto = $params['id_codigo_incompleto'];
		$tipificacion = $params['tipificacion'];
		$observacion = $params['observacion'];
		$login = $params['login'];
		$login = $login['LOGIN'];

		$fecha_respuesta = date('Y-m-d H:i:s');

		$query = "UPDATE gestion_codigo_incompleto SET status_soporte = 1, respuesta_gestion = '$tipificacion', observacion = '$observacion', login = '$login', fecha_respuesta = '$fecha_respuesta' WHERE id_codigo_incompleto = '$id_codigo_incompleto';
		";
		$insert = $this->connseguimiento->query($query);

		if ($insert === TRUE) {
			$this->response($this->json(array('type' => 'success', 'msg' => 'OK')), 201);
		} else {
			$this->response($this->json(array('type' => 'Error', 'msg' => $this->connseguimiento->error)), 400);
		}
	}

	private function registroscodigoincompleto() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		//$this->dbConnect();
		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);
		$pagina = $params['page'];
		$datos = $params['datos'];

		$fechaini = (!isset($datos['fechaini'])) ? date("Y-m-d") : $datos['fechaini']; //CORRECCION DE VALIDACION DE FECHA
		$fechafin = (!isset($datos['fechafin'])) ? date("Y-m-d") : $datos['fechafin']; //CORRECCION DE VALIDACION DE FECHA

		if ($fechaini == "" || $fechafin == "") {
			$fechaini = date("Y-m-d");
			$fechafin = date("Y-m-d");
		}
		//$today = date("Y-m-d");

		if ($pagina == "undefined") {
			$pagina = "0";
		} else {
			$pagina = $pagina - 1;
		}

		$pagina = $pagina * 100;

		$query = "SELECT id_codigo_incompleto, tarea, numero_contacto, nombre_contacto, unepedido, tasktypecategory, unemunicipio, uneproductos, engineer_id, engineer_name, mobile_phone, status_soporte, fecha_solicitud_firebase, fecha_creado, respuesta_gestion, observacion, login, fecha_respuesta 
		FROM gestion_codigo_incompleto
		WHERE fecha_respuesta BETWEEN '$fechaini 00:00:00' AND '$fechafin 23:59:59' AND status_soporte = '1'
		ORDER BY fecha_creado DESC
		LIMIT 100 offset $pagina;";

		$queryCount = "SELECT COUNT(tarea) as Cantidad
		FROM gestion_codigo_incompleto 
		WHERE fecha_respuesta BETWEEN '$fechaini 00:00:00' AND '$fechafin 23:59:59'
		ORDER BY fecha_creado DESC;";

		//echo $queryCount;

		$rr = $this->connseguimiento->query($queryCount);

		$counter = 0;
		if ($rr->num_rows > 0) {
			$result = array();
			if ($row = $rr->fetch_assoc()) {
				$counter = $row['Cantidad'];
			}
		}

		$rst = $this->connseguimiento->query($query);

		//echo $this->mysqli->query($sqlLogin);
		//
		if ($rst->num_rows > 0) {

			$resultado = array();

			while ($row = $rst->fetch_assoc()) {

				//var_dump($row);
				$row['id_codigo_incompleto'] = utf8_encode($row['id_codigo_incompleto']);
				$row['tarea'] = utf8_encode($row['tarea']);
				$row['numero_contacto'] = utf8_encode($row['numero_contacto']);
				$row['nombre_contacto'] = utf8_encode($row['nombre_contacto']);
				$row['unepedido'] = utf8_encode($row['unepedido']);
				$row['tasktypecategory'] = utf8_encode($row['tasktypecategory']);
				$row['unemunicipio'] = utf8_encode($row['unemunicipio']);
				$row['uneproductos'] = utf8_encode($row['uneproductos']);
				$row['engineer_id'] = utf8_encode($row['engineer_id']);
				$row['engineer_name'] = utf8_encode($row['engineer_name']);
				$row['mobile_phone'] = utf8_encode($row['mobile_phone']);
				$row['status_soporte'] = utf8_encode($row['status_soporte']);
				$row['fecha_solicitud_firebase'] = utf8_encode($row['fecha_solicitud_firebase']);
				$row['fecha_creado'] = utf8_encode($row['fecha_creado']);
				$row['respuesta_gestion'] = utf8_encode($row['respuesta_gestion']);
				$row['observacion'] = utf8_encode($row['observacion']);
				$row['login'] = utf8_encode($row['login']);
				$row['fecha_respuesta'] = utf8_encode($row['fecha_respuesta']);

				//var_dump($row);
				$resultado[] = $row;

			}
			$this->response($this->json(array($resultado, $counter)), 201);

		} else {
			$error = array();

			$this->response($this->json($error), 400);
		}
	}

	private function csvRegistrosCodigoIncompleto() {
		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		$this->dbSeguimientoConnect();

		$params = json_decode(file_get_contents('php://input'), true);

		$usuarioid = $params['datosLogin'];
		$usuarioid = $usuarioid['LOGIN'];
		$datos = $params['datos'];
		$fechaini = $datos['fechaini'];
		$fechafin = $datos['fechafin'];

		if ($fechaini == "" && $fechafin == "") {
			$fechaini = date("Y") . "-" . date("m") . "-" . date("d");
			$fechafin = date("Y") . "-" . date("m") . "-" . date("d");
		}

		//echo "estos son los datos, usuario: ".$usuarioid." fechaini: ".$fechaini." y fechafin: ".$fechafin;
		//echo "estos son los otros concepto, buscar: ".$concepto." buscar: ".$buscar;
		if ($fechaini == $fechafin) {
			$filename = "Registros" . "_" . $fechaini . "_" . $concepto . "_" . $buscar . ".csv";
		} else {
			$filename = "Registros" . "_" . $fechaini . "_" . $fechafin . "_" . $concepto . "_" . $buscar . ".csv";
		}

		$query = "SELECT id_codigo_incompleto, tarea, numero_contacto, nombre_contacto, unepedido, tasktypecategory, unemunicipio, uneproductos, engineer_id, engineer_name, mobile_phone, status_soporte, fecha_solicitud_firebase, fecha_creado, respuesta_gestion, observacion, login, fecha_respuesta 
		FROM gestion_codigo_incompleto
		WHERE fecha_respuesta BETWEEN '$fechaini 00:00:00' AND '$fechafin 23:59:59' AND status_soporte = '1'
		ORDER BY fecha_creado DESC;";

		$queryCount = "SELECT COUNT(tarea) as Cantidad
		FROM gestion_codigo_incompleto 
		WHERE fecha_respuesta BETWEEN '$fechaini 00:00:00' AND '$fechafin 23:59:59'
		ORDER BY fecha_creado DESC;";

		//s    echo $queryCount;
		//

		$rr = $this->connseguimiento->query($queryCount);
		$counter = 0;
		if ($rr->num_rows > 0) {
			$result = array();
			if ($row = $rr->fetch_assoc()) {
				$counter = $row['Cantidad'];
			}
		}
		//echo $counter;

		$rst = $this->connseguimiento->query($query) or die($this->connseguimiento->error . __LINE__);

		if ($rst->num_rows > 0) {

			//Insert en log
			//$sql_log="insert into gestor_despacho.gopd_logoperaciones (USUARIO_ID, TIPO_ACTIVIDAD, DESCRIPCION, IDENTIFICADOR, IP, PC) values(UPPER('$usuarioid'),'EXPORTE','EXPORTO CSV_HISTORICO','LOGIN: $usuarioid','$usuarioIp','$usuarioPc')";

			//$rlog = $this->connemtel->query($sql_log) or die($this->connemtel->error.__LINE__);
			//Insert en log

			$result = array();
			$fp = fopen("../tmp/$filename", 'w');
			//echo $fp;

			$columnas = array('ID_CODIGO_INCOMPLETO',
			'TAREA',
			'NUMERO_CONTACTO',
			'NOMBRE_CONTACTO',
			'UNEPEDIDO',
			'TASKTYPECATEGORY',
			'UNEMUNICIPIO',
			'UNEPRODUCTOS',
			'ENGINEER_ID',
			'ENGINEER_NAME',
			'MOBILE_PHONE',
			'STATUS_SOPORTE',
			'FECHA_SOLICITUD_FIREBASE',
			'FECHA_CREADO',
			'RESPUESTA_GESTION',
			'OBSERVACION',
			'LOGIN',
			'FECHA_RESPUESTA',);

			fputcsv($fp, $columnas);

			//$carlitos=0;
			while ($row = $rst->fetch_assoc()) {

				//$row['observaciones'] = $row['observaciones'];
				//$result[] = $row;
				fputcsv($fp, $row);
				//if($carlitos==0){var_dump($row);$carlitos=1;};
			}

			fclose($fp);

			$this->response($this->json(array($filename, $counter)), 200);
		}
		$this->response('', 203); // If no records "No Content" status
		//descarga archivo registros, forma asesores, activity feed
	}

	/* -------------------------------------- CODIGO INCOMPLETO ------------------------------------- */


	//FUNCION PARA LAS FUNCIONES DE BF EN LA VISTA DE LOS DESPACHADORES
	private function BFobservaciones() {

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		$params = json_decode(file_get_contents('php://input'), true);

		$login = $params['login'];
		$login = $login['LOGIN'];

		$this->dbSeguimientoConnect();

		$hoy = date("Y-m-d");

		$query = ("
					SELECT PedidoDespacho, observacionAsesor, pedidobloqueado, gestionAsesor, estado, AccionDespacho
						FROM BrutalForce
						WHERE loginDespacho = '$login'
						AND (FechaGestionDespacho BETWEEN ('$hoy 00:00:00') AND ('$hoy 23:59:59') OR fechagestionAsesor BETWEEN ('$hoy 00:00:00') AND ('$hoy 23:59:59'))
				");

		$rst = $this->connseguimiento->query(utf8_decode($query));

		if ($rst->num_rows > 0) {

			$resultado = array();

			while ($row = $rst->fetch_assoc()) {
				$row['observacionAsesor'] = utf8_encode($row['observacionAsesor']);
				$resultado[] = $row;
			}
			$this->response($this->json(array($resultado)), 201);
		} else {
			$error = array();

			$this->response($this->json($error), 400);
		}
	}


	private function saveNivelation()
	{
		$data = json_decode(file_get_contents('php://input'), true);
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


		$stmt = $this->_conbd->prepare("SELECT * FROM nivelacion WHERE ticket_id = :id and estado != 2");
		$stmt->execute(array(':id' => $data['ticket']));

		if ($stmt->rowCount()){
			$response = array('state' => 0, 'msj' => 'La tarea ingresada se encuentra en gestión y no se a dado respuesta');
			echo json_encode($response);
			exit();
		}



		$stmt = $this->_conbd->prepare("INSERT INTO nivelacion (ticket_id, nombre_tecnico, cc_tecnico, pedido, proceso, zona, zubzona, cc_nuevo_tecnico,
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

		$this->_conbd = null;
		echo json_encode($response);
	}

	private function en_genstion_nivelacion()
	{
		$data = json_decode(file_get_contents('php://input'), true);

		$login = $data['data']['LOGIN'];

		$fecha = date('Y-m-d');

		$stmt = $this->_conbd->prepare("SELECT COUNT(*) AS total, CASE estado WHEN 1 THEN 'gestion' WHEN 2 THEN 'realizado' WHEN 0 THEN 'pendiente' END as estado
                                            FROM nivelacion where fecha_ingreso BETWEEN ('$fecha 00:00:00') and ('$fecha 23:59:59') and creado_por = :login");
		$stmt->execute(array(':login' => $login));

		$tarea = $this->_conbd->prepare("SELECT ticket_id, observaciones, observacionVeedor, se_realiza_nivelacion
                                            FROM nivelacion 
                                            where fecha_ingreso BETWEEN ('$fecha 00:00:00') and ('$fecha 23:59:59') and creado_por = :login");

		$tarea->execute(array(':login' => $login));

		if ($stmt->rowCount()) {
			$result    = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$res_tarea = $tarea->fetchAll(PDO::FETCH_ASSOC);
			$response  = array('gestion' => $result, 'tarea' => $res_tarea);
		} else {
			$response = array('pendiente' => 0, 'realizado' => 0);
		}

		$this->_conbd = null;
		echo json_encode($response);
	}


	private function buscarhistoricoNivelacion()
	{
		$data = json_decode(file_get_contents('php://input'), true);

		$stmt = $this->_conbd->prepare("select * from nivelacion where ticket_id = :ticket");
		$stmt->execute(array(':ticket' => $data['data']));
		$stmt->execute();
		if ($stmt->rowCount()) {
			$result   = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$response = array('state' => 1, 'data' => $result);
		} else {
			$response = array('state' => 0, 'msj' => 'No se encontraron datos');
		}

		$this->_conbd = null;
		echo json_encode($response);
	}

	private function gestionarNivelacion()
	{

		$fecha = date('Y-m-d');
		$stmt = $this->_conbd->query("select n.creado_por,
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
                                                   n.observacionVeedor,
       											   n.en_gestion
                                            from nivelacion n where n.estado != 2 and n.fecha_ingreso BETWEEN ('$fecha 00:00:00') AND ('$fecha 23:59:59') order by n.id");
		$stmt->execute();
		if ($stmt->rowCount()) {
			$result   = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$response = array('state' => 1, 'data' => $result);
		} else {
			$response = array('state' => 0);
		}

		$this->_conbd = null;
		echo json_encode($response);
	}

	private function guardaNivelacion()
	{
		$data = json_decode(file_get_contents('php://input'), true);


		$id = $data['datos'];
		$login = $data['login'];

		if ($login){
			$stmt = $this->_conbd->prepare("select en_gestion, gestiona_por from nivelacion where id = :id");
			$stmt->execute(array(':id' => $id['id']));
			$result = $stmt->fetch(PDO::FETCH_OBJ);
			if ($result->gestiona_por != $login['LOGIN']) {
				$response = array('state' => 0, 'msj' => "La tarea no se encuentra en gentión por el usuario actual");
			} else{
				$stmt = $this->_conbd->prepare("update nivelacion set se_realiza_nivelacion = :nivelacion, observaciones = :observaciones, fecha_respuesta = :fecha_respuesta, estado = '2' where id = :id");
				$stmt->execute(array(
					':nivelacion'    => $id['tipificacion'],
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


		$this->_conbd = null;
		echo json_encode($response);
	}

	private function gestionarRegistrosNivelacion($data)
	{
		$data = json_decode(file_get_contents('php://input'), true);

		if (!empty($data['Registros'])){

			$fechaini = $data['Registros']['fechaini'];
			$fechafin = $data['Registros']['fechafin'];

			$stmt = $this->_conbd->query("select count(*) as total from nivelacion where 1 = 1 and fecha_ingreso between '$fechaini 00:00:00' and '$fechafin 23:59:59'");
			$stmt->execute();

			$resCount   = $stmt->fetch(PDO::FETCH_OBJ);
			$totalCount = $resCount->total;


			$page_number = $data['curPage'];
			$pageSize = $data['pageSize'];

			$initial_page = ($page_number - 1) * $data['pageSize'];
			$total_pages = ceil($totalCount / $data['pageSize']);

			$param = " where 1=1 and n.fecha_ingreso BETWEEN ('$fechaini 00:00:00') and ('$fechafin 23:59:59') order by fecha_ingreso desc limit $initial_page, $pageSize";
		}else{

			$stmt = $this->_conbd->query("select count(*) as total from nivelacion where 1 = 1");
			$stmt->execute();

			$resCount   = $stmt->fetch(PDO::FETCH_OBJ);
			$totalCount = $resCount->total;


			$page_number = $data['curPage'];
			$pageSize = $data['pageSize'];
			$initial_page = ($page_number - 1) * $data['pageSize'];
			$total_pages = ceil($totalCount / $data['pageSize']);
			$param = " where 1=1 order by fecha_ingreso desc limit $initial_page, $pageSize";
		}

		$stmt = $this->_conbd->query("select n.creado_por,
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
                                            from nivelacion n $param");
		$stmt->execute();
		if ($stmt->rowCount()) {
			$result   = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$response = array('state' => 1, 'data' => $result, 'total' => $total_pages, 'counter' => intval($totalCount));
		} else {
			$response = array('state' => 0);
		}

		$this->_conbd = null;
		echo json_encode($response);
	}

	private function marcarEnGestionNivelacion()
	{
		$data = json_decode(file_get_contents('php://input'), true);
		$id = $data['datos'];
		$login = $data['login'];

		/* $stmt = $this->_conbd->prepare("select en_gestion, gestiona_por from nivelacion where id = :id");
         $stmt->execute(array(':id' => $id));*/

		$fecha = date('Y-m-d H:i:s');

		$stmt = $this->_conbd->prepare("select en_gestion, gestiona_por from nivelacion where id = :id");
		$stmt->execute(array(':id' => $id['id']));

		$resul = $stmt->fetch(PDO::FETCH_ASSOC);

		if ($resul['gestiona_por'] == $login['LOGIN']) {

			$stmt = $this->_conbd->prepare("update nivelacion set en_gestion = 0, gestiona_por = '' where id = :id");
			if ($stmt->execute(array(':id' => $id['id']))) {
				$response = array('state' => 1, 'msj' => 'La tarea se encuentra desbloqueada');
			} else {
				$response = array( 'state' => 0, 'msj' => 'Ah ocurrido un error intentalo nuevamente');
			}

		} elseif ($resul['gestiona_por'] == '') {

			$stmt = $this->_conbd->prepare("update nivelacion set gestiona_por = :gestion, estado = 1, fecha_gestion = :fecha_gestion, en_gestion = 1 where id = :id");
			$stmt->execute(array(':gestion' => $login['LOGIN'], ':id' => $id['id'], ':fecha_gestion' => $fecha));
			if ($stmt->rowCount() == 1){
				$response = array('state' => 1, 'msj' => 'La tarea se encuentra Bloqueada');
			}

		} elseif ($resul['en_gestion'] != $login['LOGIN']) {
			$response = array('state' => 2, 'msj' => 'La tarea se encuentra en gestión');
		}

		/*if ($stmt->rowCount()) {
            $resul = $stmt->fetch(PDO::FETCH_OBJ);
            if ($resul->gestiona_por == $login) {
                $stmt = $this->_conbd->prepare("update nivelacion set en_gestion = '', gestiona_por = '', fecha_gestion = '' where id = :id");
                if ($stmt->execute(array(':id' => $id))) {
                    $response = array('state' => 1, 'msj' => 'La tarea se encuentra desbloqueada');
                } else {
                    $response = array('state' => 0, 'msj' => 'Ah ocurrido un error intentalo nuevamente');
                }

            } elseif ($resul->en_gestion == '') {
                $stmt = $this->_conbd->prepare("update nivelacion set gestiona_por = :gestion, estado = 1, fecha_gestion = :fecha_gestion, en_gestion = 1 where id = :id");
                $stmt->execute(array(':gestion' => $login, ':id' => $id, ':fecha_gestion' => date("Y-m-d h:i:s")));
                $response = array('state' => 1, 'msj' => 'La tarea se encuentra Bloqueada');
            } elseif ($resul->en_gestion != $login) {
                $response = array('state' => 1, 'msj' => 'La tarea se encuentra en gestión');
            }
        }*/

		$this->_conbd = null;
		echo json_encode($response);
	}

	private function csvNivelacion(){
		$data = json_decode(file_get_contents('php://input'), true);

		$data = $data['fechaini'];
		$fechaini = $data['fechaini'];
		$fechafin = $data['fechafin'];


		$stmt = $this->_conbd->query("select ticket_id,
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
		$this->_conbd = null;
		echo json_encode($response);

	}


	//Funciones Generales

} //cierre de la clase

// Initiiate Library

$api = new API;
$api->processApi();

?>
