var app = angular.module('seguimientopedidos', [
    'ngRoute',
    'ngCookies',
    'ng-fusioncharts',
    'ngAnimate',
    'ngTouch',
    'ui.bootstrap',
    'angularjs-datetime-picker',
    'angularFileUpload',
    'ui',
    'jcs-autoValidate',
    'ui.grid',
    'ui.grid.pagination',
    'ui.grid.selection',
    'ui.grid.edit',
    'ui.grid.cellNav',
    'ui.grid.exporter',
    'ui.grid.autoResize'
]);

app.service('fileUpload', ['$http', '$cookieStore', function ($http, $cookieStore) {
    this.uploadFileToUrl = function (file, uploadUrl, login, tipocarga) {

        var fd = new FormData();
        var user = login;
        file['user'] = user + '6666666';

        fd.append('user', user);
        fd.append('tipocarga', tipocarga);

        //console.log (file['size']);
        fd.append('fileUpload', file);

        if (tipocarga == "vistaCliente" || tipocarga == "alarmados" || tipocarga == "SeguiClick") {
            $http.post('services/cargar_datos', fd, {
                withCredentials: false,
                transformRequest: angular.identity,
                headers: {'Content-Type': undefined},
                params: {'user': user, 'tipocarga': tipocarga},
                responseType: "arraybuffer"
            })
        } else {
            $http.post('services/cargar_datosNPS', fd, {
                withCredentials: false,
                transformRequest: angular.identity,
                headers: {'Content-Type': undefined},
                params: {'user': user},
                responseType: "arraybuffer"
            })
        }
        ;
    }
}]);

//-------------reparacion-----------------------

app.service('fileUploadrepa', ['$http', '$cookieStore', function ($http, $cookieStore) {
    this.uploadFileToUrl = function (file, uploadUrl, login) {

        var fd = new FormData();
        var user = login;
        file['user'] = user + '6666666';

        fd.append('user', user);
        //fd.append('tipocarga',tipocarga);

        //console.log (file['size']);
        fd.append('fileUpload', file);

        $http.post('services/cargar_datosNPSreparacion', fd, {
            withCredentials: false,
            transformRequest: angular.identity,
            headers: {'Content-Type': undefined},
            params: {'user': user},
            responseType: "arraybuffer"
        })

    }
}]);

app.service('cargaRegistros', ['$http', '$cookieStore', function ($http, $cookieStore) {
    this.uploadFileToUrl = function (file, uploadUrl, login) {

        var fd = new FormData();
        var user = login;
        file['user'] = user + '6666666';

        fd.append('user', user);
        //fd.append('tipocarga',tipocarga);

        //console.log (file['size']);
        fd.append('fileUpload', file);

        $http.post('services/cargaRegistros', fd, {
            withCredentials: false,
            transformRequest: angular.identity,
            headers: {'Content-Type': undefined},
            params: {'user': user},
            responseType: "arraybuffer"
        })

    }
}]);

//-------------------- fin reparacion ------------------


app.directive('fileModel', ['$parse', function ($parse) {
    return {
        restrict: 'A',
        link: function (scope, element, attrs) {
            var model = $parse(attrs.fileModel);
            var modelSetter = model.assign;

            element.bind('change', function () {
                scope.$apply(function () {
                    modelSetter(scope, element[0].files[0]);
                });
            });
        }
    };
}]);


app.factory("services", ['$http', '$timeout', function ($http, $q, $timeout) {
    var serviceBase = 'services/';
    //var serviceBase = 'http://netvm-ptctrl01/seguimientopedidos/api/controller/';
    var obj = {};

    obj.loginUser = function (datosAutenticacion) {
        return $http.post(serviceBase + 'loginUser', {'datosAutenticacion': datosAutenticacion});
    };

    obj.cerrarsesion = function (USUARIO_ID, PERFIl, tiempo) {
        return $http.post(serviceBase + 'logout', {'USUARIO_ID': USUARIO_ID, 'PERFIl': PERFIl, 'fecha': tiempo});
    };

    obj.editarUsuario = function (datosEdicion) {
        return $http.post(serviceBase + 'editarUsuario', {'datosEdicion': datosEdicion});
    };

    obj.editarRegistro = function (datosEdicion, datosLogin) {
        return $http.post(serviceBase + 'editarRegistro', {'datosEdicion': datosEdicion, 'datosLogin': datosLogin});
    };

    obj.pedidoComercial = function (datospedidoComercial, datosLogin) {
        return $http.post(serviceBase + 'CrearpedidoComercial', {'datospedidoComercial': datospedidoComercial, 'datosLogin': datosLogin});
    };

    obj.getGuardarPlan = function (datosLogin, datosPlan) {
        return $http.post(serviceBase + 'guardarPlan', {'datosLogin': datosLogin, 'datosPlan': datosPlan});
    };

    obj.pedidoOffline = function (datospedidoOffline, datosLogin) {
        return $http.post(serviceBase + 'CrearpedidoOffline', {'datospedidoOffline': datospedidoOffline, 'datosLogin': datosLogin});
    };

    obj.ingresarPedidoAsesor = function (datospedido, pedido, empresa, duracion_llamada, datosLogin, datosClick, plantilla, idcambioequipo) {
        return $http.post(serviceBase + 'ingresarPedidoAsesor', {
            'datospedido': datospedido,
            'pedido': pedido,
            'empresa': empresa,
            'duracion_llamada': duracion_llamada,
            'datosLogin': datosLogin,
            'datosClick': datosClick,
            'plantilla': plantilla,
            'idcambioequipo': idcambioequipo
        });
    };

    /*COREECION SE QUITO idcambioequipo PORQUE NO SE USA Y HAY AFECTAR EN LA AGRECACION EN FORMA DE PEDIDO SOPORTE TECNICO -> CAMBIO EQUIPO*/
    // obj.ingresarPedidoAsesor = function (datospedido, pedido, empresa, duracion_llamada, datosLogin, datosClick, plantilla) {
    //     return $http.post(serviceBase + 'ingresarPedidoAsesor', { 'datospedido': datospedido, 'pedido': pedido, 'empresa': empresa, 'duracion_llamada': duracion_llamada, 'datosLogin': datosLogin, 'datosClick': datosClick, 'plantilla': plantilla });
    // };

    obj.creaUsuario = function (datosCrearUsuario) {
        return $http.post(serviceBase + 'creaUsuario', {'datosCrearUsuario': datosCrearUsuario});
    };

    obj.creaAlarma = function (datosCrearAlarma) {
        return $http.post(serviceBase + 'nuevaAlarma', {'datosCrearAlarma': datosCrearAlarma});
    };

    obj.creaTecnico = function (datosCrearTecnico, id_tecnico) {
        return $http.post(serviceBase + 'creaTecnico', {'datosCrearTecnico': datosCrearTecnico, "id_tecnico": id_tecnico});
    };

    obj.insertarCambioEquipo = function (tecnologia, datoscambio, pedido) {
        return $http.post(serviceBase + 'insertarCambioEquipo', {'tecnologia': tecnologia, "datoscambio": datoscambio, "pedido": pedido});
    };

    obj.getGuardarPedidoEncuesta = function (infoPedidoEncuesta, gestionDolores, counter, fechaInicial, fechaFinal, login) {
        return $http.post(serviceBase + 'GuardarPedidoEncuesta', {
            'infoPedidoEncuesta': infoPedidoEncuesta,
            "gestionDolores": gestionDolores,
            "counter": counter,
            "fechaInicial": fechaInicial,
            "fechaFinal": fechaFinal,
            "login": login
        });
    };

    obj.getGuardargestiodespachoBrutal = function (datosguardar, login) {
        return $http.post(serviceBase + 'gestiodespachoBrutal', {"datosguardar": datosguardar, "login": login});
    };

    obj.datosGestionFinal = function () {
        return $http.post(serviceBase + 'gestionFinal');
    };

    obj.getDashBoard = function () {
        return $http.post(serviceBase + 'DashBoard');
    };

    obj.getGuardargestioAsesor = function (datosguardar, datosDespacho, login) {
        return $http.post(serviceBase + 'gestionAsesorBrutal', {"datosguardar": datosguardar, "datosDespacho": datosDespacho, "login": login});
    };

    obj.guardarContingencia = function (datosguardar, login) {
        return $http.post(serviceBase + 'savecontingencia', {"datosguardar": datosguardar, "login": login});
    };

    obj.guardarEscalamiento = function (datosguardar, login) {
        return $http.post(serviceBase + 'saveescalamiento', {"datosguardar": datosguardar, "login": login});
    };

    obj.CancelContingencia = function (datoscancelar, login) {
        return $http.post(serviceBase + 'CancelarContingencias', {"datoscancelar": datoscancelar, "login": login});
    };

    obj.getguardarEscalar = function (gestionescalado) {
        return $http.post(serviceBase + 'guardarEscalar', {"gestionescalado": gestionescalado});
    };

    obj.getGuardargestionFinal = function (datosFinal) {
        return $http.post(serviceBase + 'gestionAsesorFinal', {"datosFinal": datosFinal});
    };

    obj.getpedidosPendientes = function (login) {
        return $http.post(serviceBase + 'gestionPendientes', {"login": login});
    };

    obj.pedidopendientes = function (datos, login) {
        return $http.post(serviceBase + 'Pendientesxestado', {"datos": datos, "login": login});
    };

    obj.getBorrarRegistros = function (datosBorrar) {
        return $http.post(serviceBase + 'gestionBorrar', {"datosBorrar": datosBorrar});
    };

    obj.getDesbloquear = function (datos) {
        return $http.post(serviceBase + 'desbloquear', {"datos": datos});
    };

    obj.expCsvdatos = function (valor, datos, datosLogin) {
        return $http.post(serviceBase + 'csvPreagen', {"valor": valor, "datos": datos, "datosLogin": datosLogin});
    };

    obj.getexporteContingencias = function (fechaIni, fechafin, datosLogin) {
        return $http.post(serviceBase + 'csvContingencias', {"fechaIni": fechaIni, "fechafin": fechafin, "datosLogin": datosLogin});
    };

    obj.expCsvestados = function (datos, datosLogin) {
        return $http.post(serviceBase + 'csvEstadosClick', {"datos": datos, "datosLogin": datosLogin});
    };

    obj.expCsvpeniInsta = function (regional, datosLogin) {
        return $http.post(serviceBase + 'CsvpeniInsta', {"regional": regional, "datosLogin": datosLogin});
    };

    obj.getexpcsvRRHH = function (datosLogin) {
        return $http.get('http://10.100.66.254:7771/api/exportrrhh');
    };

    obj.expGestionPendientes = function (datosExporte, login) {
        return $http.post(serviceBase + 'CsvGestionPendientes', {"datosExporte": datosExporte, "login": login});
    };

    obj.expNpsSemana = function (semana, login) {
        return $http.post(serviceBase + 'CsvNpsSemana', {"semana": semana, "login": login});
    };

    obj.buscarPedido = function (url, pedidos) {
        return $http.post(serviceBase + 'buscarPedido', {"url": url, "pedidos": pedidos});
    };
    obj.buscarPedidoSeguimiento = function (pedido, producto, remite) {
        return $http.post(serviceBase + 'buscarPedidoSegui', {"pedido": pedido, "producto": producto, "remite": remite});
    };
    obj.expCsvRegistros = function (datos, datosLogin) {
        return $http.post(serviceBase + 'csvRegistros', {"datos": datos, "datosLogin": datosLogin});
    };

    obj.expBrutalForce = function (fechas, datosLogin) {
        return $http.post(serviceBase + 'expBrutal', {"fechas": fechas, "datosLogin": datosLogin});
    };

    obj.expCsvtecnico = function (datos, datosLogin) {
        return $http.post(serviceBase + 'Csvtecnico', {"datos": datos, "datosLogin": datosLogin});
    };

    obj.editarTecnico = function (datosTecnico) {
        return $http.post(serviceBase + 'editarTecnico', {'datosTecnico': datosTecnico});
    };

    obj.editAlarma = function (datosAlarma) {
        return $http.post(serviceBase + 'editAlarma', {'datosAlarma': datosAlarma});
    };

    obj.listadoUsuarios = function (page, concepto, usuario) {
        return $http.get(serviceBase + 'listadoUsuarios?page=' + page + '&concepto=' + concepto + '&usuario=' + usuario);
    };

    obj.getDiferenciasClick = function (fecha) {
        return $http.get(serviceBase + 'diferenciasClick?fecha=' + fecha);
    };

    obj.Verobservacionasesor = function (pedido) {
        return $http.get(serviceBase + 'observacionAsesor?pedido=' + pedido);
    };

    obj.contadorPendientesBrutalForce = function (cantPendientes) {
        return $http.get(serviceBase + 'contadorpedientesBF?cantPendientes=' + cantPendientes);
    };


    obj.getseguimientoClick = function (fecha) {
        return $http.get(serviceBase + 'seguimientoClick?fecha=' + fecha);
    };

    obj.registrosComercial = function (page, concepto, dato, inicial, final) {
        return $http.get(serviceBase + 'registrosComercial?page=' + page + '&concepto=' + concepto + '&dato=' + dato + '&inicial=' + inicial + '&final=' + final);
    };

    obj.registros = function (page, datos) {
        return $http.post(serviceBase + 'registros', {"page": page, "datos": datos});
    };

    /*===========================================================*/

    //SERVICIOS PARA PREMISAS INFRAESTRUCTURAS

    /*===========================================================*/

    /*****SERVICIOS PARA EL MODULO GENERACIONTT*****/

    //Servivio para subir la informacion de la tabla a la vista
    obj.premisasInfraestructuras = function (page, datos) {
        return $http.post(serviceBase + 'premisasInfraestructuras', {"page": page, "datos": datos});
    };

    obj.guardar = function (registrostt) {
        return $http.post(serviceBase + 'guardarGeneracionTT', {'datosEdicion': registrostt});
    };

    obj.expCsvGeneracionTT = function (datos, datosLogin) {
        return $http.post(serviceBase + 'csvGeneracionTT', {"datos": datos, "datosLogin": datosLogin});
    };

    /*****SERVICIOS PARA EL MODULO DE ESCALAMIENTO*****/

    //Servivio para subir la informacion de la tabla a la vista
    obj.premisasInfraestructurasEscalmiento = function (page, datos) {
        return $http.post(serviceBase + 'escalamientoInfraestructura', {"page": page, "datos": datos});
    };

    // SERVICIO PARA LLAMAR LA INFORMACION DE GRUPO COLA
    obj.getGrupoCola = function () {
        return $http.get(serviceBase + 'GrupoCola');
    };

    // SERVICIO PARA LLAMAR LA INFORMACION DE GESTION DE ESCALAMIENTO
    obj.getGestion = function () {
        return $http.get(serviceBase + 'gestionEscalimiento');
    };

    // SERVICIO PARA LLAMAR LA INFORMACION DE OBSERVACION DE ESCALAMIENTO
    obj.getObservacionesEscalamiento = function (gestion) {
        return $http.get(serviceBase + 'observacionEscalimiento?gestion=' + gestion);
    };

    // SERVICIO PARA LLAMAR LA INFORMACION DE NOTAS DE ESCALAMIENTO
    obj.getNotasEscalamiento = function (observacion) {
        return $http.get(serviceBase + 'notasEscalamiento?observacion=' + observacion);
    };

    obj.guardarFormEscalamiento = function (escalamiento) {
        return $http.post(serviceBase + 'infoEscalamiento', {'datosEdicion': escalamiento});
    };

    obj.expCsvEscalamiento = function (datos, datosLogin) {
        return $http.post(serviceBase + 'csvEscalamientoExp', {"datos": datos, "datosLogin": datosLogin});
    };

    /*------>SERVICIOS PARA EL MODULO DE VISITAS EN CONJUNTO<------*/

    //Servivio para subir la informacion de la tabla a la vista
    obj.premisasVisitasEnConjunto = function (page, datos) {
        return $http.post(serviceBase + 'visitasEnConjunto', {"page": page, "datos": datos});
    };

    // Servicio para llamar la informacion de grupo en las visitas en conjunto
    obj.getGrupoVisitasEnConjunto = function () {
        return $http.get(serviceBase + 'GrupoVisitasEnConjunto');
    };

    //servicio para guardar la información de visitas en conjunto
    obj.guardarFormVisitasEnConjunto = function (visitasEnConjunto) {
        return $http.post(serviceBase + 'infoVisitasEnConjunto', {'datosEdicion': visitasEnConjunto});
    };

    //Servicio para exportar la información de las vistas en conjunto
    obj.expCsvVisitasEnConjunto = function (datos, datosLogin) {
        return $http.post(serviceBase + 'csvVisitasEnConjuntoExp', {"datos": datos, "datosLogin": datosLogin});
    };

    // Servicio para llamar las Regiones en visitas en conjunto
    obj.RegionesVisConj = function () {
        return $http.get(serviceBase + 'RegionesVisConjunto');
    };

    // Servicio para llamar las ciudades en vistas en conjunto, frm registro nuevo
    obj.MunicipiosVisConj = function (region) {
        return $http.get(serviceBase + 'MunicipiosVisConjunto?region=' + region);
    };

    // Servicio para llamar la ciudad en vistas en conjunto, frm update
    obj.municipiovisconjupdate = function (idregistro) {
        return $http.get(serviceBase + 'MunicipioVisConjuntoUpdate?idregistro=' + idregistro);
    };

    /*===========================================================*/
    //INICIO SERVICIOS PARA CONTRASEÑAS TECNICOS
    /*===========================================================*/


    obj.registrosContrasenasTecnicos = function (datos) {
        return $http.post(serviceBase + 'registrospwdTecnicos', {"datos": datos});
    };

    obj.editarPasswordTecnicos = function (datosEdicion) {
        return $http.post(serviceBase + 'editarPwdTecnicos', {'datosEdicion': datosEdicion});
    };

    obj.expCsvContrasenasTecnicos = function (datosLogin) {
        return $http.post(serviceBase + 'csvContrasenasTecnicos', {"datosLogin": datosLogin});
    };

    /*===========================================================*/
    //FIN SERVICIOS PARA CONTRASEÑAS TECNICOS
    /*===========================================================*/
    /*===========================================================*/

    //SERVICIOS PARA NOVEDADES DE VISITAS DE LOS TECNICOS

    /*===========================================================*/

    obj.novedadesTecnicoService = function (page, datos) {
        return $http.post(serviceBase + 'novedadesTecnico', {"page": page, "datos": datos});
    };

    obj.guardarNovedadesTecnico = function (registrosTenicos, login) {
        return $http.post(serviceBase + 'guardarNovedadesTecnico', {'datosEdicion': registrosTenicos, "login": login});
    };

    obj.updateNovedadesTecnico = (observacionCCO, pedido) => {
        return $http.post(serviceBase + 'updateNovedadesTecnico', {'datosEditar': observacionCCO, "pedido": pedido});
    }

    obj.expCsvNovedadesTecnico = function (datos, datosLogin) {
        return $http.post(serviceBase + 'csvNovedadesTecnico', {"datos": datos, "datosLogin": datosLogin});
    };

    obj.getRegiones = function () {
        return $http.get(serviceBase + 'Regiones');
    };

    obj.getMunicipios = function (region) {
        return $http.get(serviceBase + 'Municipios?region=' + region);
    };

    obj.getSituacion = function () {
        return $http.get(serviceBase + 'SituacionNovedadesVisitas');
    };

    obj.getDetalle = function (situacion) {
        return $http.get(serviceBase + 'DetalleNovedadesVisitas?situacion=' + situacion);
    };

    /*------------->INICIO SERVICIOS PARA QUEJASGO<------------*/

    obj.listaQuejasGoDia = function (page, datos) {
        return $http.post(serviceBase + 'extraeQuejasGoDia', {"page": page, "datos": datos});
    };

    obj.expCsvQuejasGo = function (datos, datosLogin) {
        return $http.post(serviceBase + 'csvQuejasGo', {"datos": datos, "datosLogin": datosLogin});
    };

    obj.traerTecnico = function (cedula) {
        return $http.get(serviceBase + 'buscarTecnico?cedula=' + cedula);
    };

    obj.creaTecnicoQuejasGo = function (crearTecnicoquejasGoSel) {
        return $http.post(serviceBase + 'crearTecnicoQuejasGo', {'crearTecnicoquejasGoSel': crearTecnicoquejasGoSel});
    };

    obj.getCiudadesQuejasGo = function () {
        return $http.get(serviceBase + 'ciudadesQGo');
    };

    obj.guardarQuejaGo = function (dataquejago, duracion, login) {
        return $http.post(serviceBase + 'registrarQuejaGo', {'dataquejago': dataquejago, 'duracion': duracion, 'login': login});
    };

    obj.modiObserQuejasGo = function (observacion, idqueja) {
        return $http.post(serviceBase + 'ActualizarObserQuejasGo', {'observacion': observacion, 'idqueja': idqueja});
    };

    /*------------->FIN SERVICIOS PARA QUEJASGO<------------*/


    // /*RETORNO DE LA INFORMACION DEL APPI DE LA FUNCION datoscontingencias*/
    obj.datosgestioncontingencias = function () {
        return $http.post(serviceBase + 'datoscontingencias');
    };

    obj.datosgestionescalamientos = function () {
        return $http.get(serviceBase + 'datosescalamientos');
    };

    obj.datosgestionescalamientosprioridad2 = function () {
        return $http.get(serviceBase + 'datosescalamientosprioridad2');
    };

    obj.UpdatePedidosEngestion = function (login) {
        return $http.post(serviceBase + 'updateEnGestion', {"login": login});
    };

    /*Servicio para traer el estado y las observaciones de los pedidos en BrutalForce*/
    obj.ObsPedidosBF = function (login) {
        return $http.post(serviceBase + 'BFobservaciones', {"login": login});
    };

    obj.registrosOffline = function () {
        return $http.post(serviceBase + 'registrosOffline');
    };

    obj.getgraficaDepartamento = function (mes) {
        return $http.post(serviceBase + 'graficaDepartamento', {"mes": mes});
    };

    obj.marcarengestion = function (datos, login) {
        return $http.post(serviceBase + 'marca', {"datos": datos, "login": login});
    };

    obj.marcarengestionescalamiento = function (datos, login) {
        return $http.post(serviceBase + 'marcaescalamiento', {"datos": datos, "login": login});
    };

    /*********NUEVO CONECTOR PARA LA APPI marcarEnGestionPorta*********/
    obj.marcarEnGestionPorta = function (datos, login) {
        return $http.post(serviceBase + 'marcaPortafolio', {"datos": datos, "login": login});
    };

    // obj.marcarEnGestionCEQPorta = function (datos, login) {
    //     return $http.post(serviceBase + 'marcaCEQPortafolio', { "datos": datos, "login": login });
    // };

    obj.editarregistrocontingencia = function (datos, login) {
        return $http.post(serviceBase + 'guardarpedidocontingencia', {"datos": datos, "login": login});
    };

    obj.editarregistroescalamiento = function (datos, login) {
        return $http.post(serviceBase + 'guardarescalamiento', {"datos": datos, "login": login});
    };

    /*PARA CERRAR MASIVAMENTE LAS CONTINGENCIAS*/
    obj.cierreMasivoContingencia = function (dataCierreMasivoContin) {
        return $http.post(serviceBase + 'cerrarMasivamenteContingencias', {"datos": dataCierreMasivoContin});
    };

    /*********NUEVO CONECTOR PARA LA APPI editarRegistroContingenciaPortafolio*********/
    obj.editarRegistroContingenciaPortafolio = function (datos, login) {
        return $http.post(serviceBase + 'guardarPedidoContingenciaPortafolio', {"datos": datos, "login": login});
    };

    obj.getgarantiasInstalaciones = function (mes) {
        return $http.post(serviceBase + 'garantiasInstalaciones', {"mes": mes});
    };

    obj.getgraficaAcumulados = function (pregunta, mes) {
        return $http.post(serviceBase + 'graficaAcumulados', {"pregunta": pregunta, "mes": mes});
    };

    //------------------reparacion----
    obj.getgraficaAcumuladosrepa = function (pregunta, mes) {
        return $http.post(serviceBase + 'graficaAcumuladosrepa', {"pregunta": pregunta, "mes": mes});
    };

    //-------------fin reparacion

    obj.getDepartamentosContratos = function (mes) {
        return $http.post(serviceBase + 'DepartamentosContratos', {"mes": mes});
    };

    obj.insertData = function (lista) {
        return $http.post(serviceBase + 'insertData', {"lista": lista});
    };

    obj.getRegistrosCarga = function () {
        return $http.post(serviceBase + 'getRegistrosCarga');
    };

    obj.getDemePedidoEncuesta = function () {
        return $http.post(serviceBase + 'DemePedidoEncuesta');
    };

    obj.getresumenSemanas = function (pregunta, mes) {
        return $http.post(serviceBase + 'resumenSemanas', {"pregunta": pregunta, "mes": mes});
    };

    obj.listadoTecnicos = function (page, concepto, tecnico) {
        return $http.get(serviceBase + 'listadoTecnicos?page=' + page + '&concepto=' + concepto + '&tecnico=' + tecnico);
    };

    obj.getresumenContingencias = function (fechaini, fechafin) {
        return $http.get(serviceBase + 'resumencontingencias?fechaini=' + fechaini + '&fechafin=' + fechafin);
    };

    obj.getbuscarPedidoContingencia = function (pedido) {
        return $http.get(serviceBase + 'buscarPedidoContingencias?pedido=' + pedido);
    };

    obj.listadoAlarmas = function () {
        return $http.post(serviceBase + 'listadoAlarmas');
    };

    obj.deleteUsuario = function (id) {
        return $http.post(serviceBase + 'borrarUsuario', {"id": id});
    };


    obj.deleteAlarma = function (id) {
        return $http.post(serviceBase + 'borrarAlarma', {"id": id});
    };

    obj.deleteTecnico = function (id) {
        return $http.post(serviceBase + 'borrarTecnico', {"id": id});
    };

    obj.getCiudades = function () {
        return $http.get(serviceBase + 'ciudades');
    };

    obj.getRegionesTip = function () {
        return $http.get(serviceBase + 'regionesTip');
    };

    obj.getpedidosGestionBrutal = function (login, accion) {
        return $http.post(serviceBase + 'gestionBrutal', {"login": login, "accion": accion});
    };

    obj.getBuscarPedidoBrutal = function (pedido) {
        return $http.get(serviceBase + 'BuscarPedidoBrutal?pedido=' + pedido);
    };

    obj.getProcesos = function () {
        return $http.get(serviceBase + 'procesos');
    };

    obj.getCalcularMeses = function () {
        return $http.get(serviceBase + 'meses');
    };
    obj.getCalcularMesesrepa = function () {
        return $http.get(serviceBase + 'mesesrepa');
    };

    obj.getactualizarregion = function () {
        return $http.get(serviceBase + 'actualizarregion');
    };

    obj.getdepartamentos = function () {
        return $http.get(serviceBase + 'departamentos');
    };

    obj.getConceptosPendientes = function (interfaz) {
        return $http.get(serviceBase + 'conceptospendientes?interfaz=' + interfaz);
    };

    obj.getConceptosTotales = function (regional, interfaz) {
        return $http.get(serviceBase + 'getConceptosTotales?regional=' + regional + '&interfaz=' + interfaz);
    };

    obj.getResumenInsta = function (departamento) {
        return $http.get(serviceBase + 'ResumenInsta?departamento=' + departamento);
    };

    obj.gettipo_trabajoclick = function () {
        return $http.get(serviceBase + 'tipo_trabajoclick');
    };

    obj.getUenCargada = function () {
        return $http.get(serviceBase + 'UenCargada');
    };

    obj.getgestionComercial = function () {
        return $http.get(serviceBase + 'gestionComercial');
    };

    obj.getcausaRaiz = function () {
        return $http.get(serviceBase + 'causaRaiz');
    };

    obj.getResponsablePendiente = function (causaraiz) {
        return $http.get(serviceBase + 'ResponsablePendiente?causaraiz=' + causaraiz);
    };

    obj.getlistarPendientesCausaRaiz = function (causaRaiz, fecha) {
        return $http.get(serviceBase + 'listaCausaRaiz?causaRaiz=' + causaRaiz + '&fecha=' + fecha);
    };

    obj.getCausasraizinconsitencias = function () {
        return $http.get(serviceBase + 'Causasraizinconsitencias?causaRaiz=');
    };

    obj.pendientesBrutalForce = function () {
        return $http.post(serviceBase + 'pendiBrutal');
    };

    obj.getclasificacionComercial = function (gestion) {
        return $http.get(serviceBase + 'clasificacionComercial?gestion=' + gestion);
    };

    obj.getbuscarpedidoRegistros = function (pedido, fecha) {
        return $http.get(serviceBase + 'buscaregistros?pedido=' + pedido + '&fecha=' + fecha);
    };

    obj.listadoEstadosClick = function (listaClick) {
        return $http.post(serviceBase + 'listadoEstadosClick', {"listaClick": listaClick});
    };

    obj.getBuscarPedidoinsta = function (info, user) {
        return $http.post(serviceBase + 'BuscarPedidoinsta', {"info": info, "user": user});
    };

    obj.getGuardarPedidoPendiInsta = function (pedido, datosdelpedido, info, user) {
        return $http.post(serviceBase + 'GuardarPedidoPendiInsta', {"pedido": pedido, "datosdelpedido": datosdelpedido, "info": info, "user": user});
    };

    obj.deleteregistrosCarga = function (idCarga) {
        return $http.get(serviceBase + 'deleteregistrosCarga?idCarga=' + idCarga);
    };

    obj.getAccionesoffline = function (producto) {
        return $http.get(serviceBase + 'Accionesoffline?producto=' + producto);
    }

    obj.getAcciones = function (proceso) {
        return $http.get(serviceBase + 'acciones?proceso=' + proceso);
    };

    obj.getSubAcciones = function (proceso, accion) {
        return $http.get(serviceBase + 'SubAcciones?proceso=' + proceso + '&accion=' + accion);
    };

    obj.getCodigos = function (proceso, UNESourceSystem) {
        return $http.get(serviceBase + 'Codigos?proceso=' + proceso + '&UNESourceSystem=' + UNESourceSystem);
    };

    obj.getDiagnosticos = function (producto, accion) {
        return $http.get(serviceBase + 'Diagnosticos?producto=' + producto);
    };


    //Turnos
    obj.getusuariosTurnos = function () {
        return $http.get(serviceBase + 'usuariosTurnos');
    };

    obj.getlistaTurnos = function (fechaini, fechafin) {
        return $http.get(serviceBase + 'listaTurnos?fechaini=' + fechaini + '&fechafin=' + fechafin);
    };

    obj.getcumplmientoTurnos = function (datos) {
        return $http.post(serviceBase + 'cumpleTurnos', {"datos": datos});
    };

    obj.getguardarTurnos = function (datosTurnos) {
        return $http.post(serviceBase + 'guardarTurnos', {"datosTurnos": datosTurnos});
    };

    obj.updateTurnos = function (datos) {
        return $http.post(serviceBase + 'updateTurno', {"datos": datos});
    };

    obj.csvAdherenciaTurnos = function (fechaIni, fechaFin, login) {
        return $http.post(serviceBase + 'CsvExporteAdherencia', {"fechaIni": fechaIni, "fechaFin": fechaFin, "login": login});
    };

    obj.borrarTurno = function (idTurno) {
        return $http.get(serviceBase + 'deleteTurno?idTurno=' + idTurno);
    };

    //Servicio para guardar la información de recogidad de equipos
    obj.recogidaEquipos = function (equiposRecoger) {
        return $http.post(serviceBase + 'guardarRecogerEquipos', {'equipos': equiposRecoger});
    };

    obj.exportEscalamientos = function () {
        return $http.get(serviceBase + 'exportEscalamientos');
    }

    // obj.cambiarPWD = function (id, datos) {
    //     return $http.post(serviceBase + 'cambiarPWD',{"id":id,"datos":datos});
    // };

    // Turnos

    /* -------------------------------- SOPORTE GPON -------------------------------- */

    obj.getPendientesSoporteGpon = function (task) {
        return $http.get(serviceBase + 'getSoporteGponByTask?task=' + task);
    };

    obj.validarLlenadoSoporteGpon = function (task) {
        return $http.get(serviceBase + 'validarLlenadoSoporteGpon?task=' + task);
    };

    obj.postPendientesSoporteGpon = function (task, arpon, nap, hilo, internet1, internet2, internet3, internet4, television1, television2, television3, television4, numeroContacto, nombreContacto, user_id, request_id, user_identification, fecha_solicitud, unepedido, tasktypecategory, unemunicipio, uneproductos, datoscola, engineer_id, engineer_name, mobile_phone, serial, mac, tipo_equipo, velocidad_navegacion, observacionTerreno) {
        return $http.post(serviceBase + 'postPendientesSoporteGpon', {
            "task": task,
            "arpon": arpon,
            "nap": nap,
            "hilo": hilo,
            "internet1": internet1,
            "internet2": internet2,
            "internet3": internet3,
            "internet4": internet4,
            "television1": television1,
            "television2": television2,
            "television3": television3,
            "television4": television4,
            "numeroContacto": numeroContacto,
            "nombreContacto": nombreContacto,
            "user_id": user_id,
            "request_id": request_id,
            "user_identification": user_identification,
            "fecha_solicitud": fecha_solicitud,
            "unepedido": unepedido,
            "tasktypecategory": tasktypecategory,
            "unemunicipio": unemunicipio,
            "uneproductos": uneproductos,
            "datoscola": datoscola,
            "engineer_id": engineer_id,
            "engineer_name": engineer_name,
            "mobile_phone": mobile_phone,
            "serial": serial,
            "mac": mac,
            "tipo_equipo": tipo_equipo,
            "velocidad_navegacion": velocidad_navegacion,
            "observacionTerreno": observacionTerreno
        });
    };

    obj.getListaPendientesSoporteGpon = function (task) {
        return $http.get(serviceBase + 'getListaPendientesSoporteGpon');
    };

    obj.gestionarSoporteGpon = function (id_soporte, tipificacion, observacion, login) {
        return $http.post(serviceBase + 'gestionarSoporteGpon', {
            'id_soporte': id_soporte,
            'tipificacion': tipificacion,
            'observacion': observacion,
            'login': login
        });
    };

    obj.registrossoportegpon = function (page, datos) {
        return $http.post(serviceBase + 'registrossoportegpon', {"page": page, "datos": datos});
    };

    obj.expCsvRegistrosSoporteGpon = function (datos, datosLogin) {
        return $http.post(serviceBase + 'csvRegistrosSoporteGpon', {"datos": datos, "datosLogin": datosLogin});
    };

    obj.marcarEngestionGpon = function (datos, login) {
        return $http.post(serviceBase + 'marcarEngestionGpon', {"datos": datos, "login": login});
    };

    /* -------------------------------- SOPORTE GPON -------------------------------- */

    /* ------------------------------- CODIGO INCOMPLETO ---------------------------- */

    obj.getListaCodigoIncompleto = function () {
        return $http.get(serviceBase + 'getListaCodigoIncompleto');
    };

    obj.gestionarCodigoIncompleto = function (id_codigo_incompleto, tipificacion, observacion, login) {
        return $http.post(serviceBase + 'gestionarCodigoIncompleto', {
            'id_codigo_incompleto': id_codigo_incompleto,
            'tipificacion': tipificacion,
            'observacion': observacion,
            'login': login
        });
    };

    obj.registroscodigoincompleto = function (page, datos) {
        return $http.post(serviceBase + 'registroscodigoincompleto', {"page": page, "datos": datos});
    };

    obj.expCsvRegistrosCodigoIncompleto = function (datos, datosLogin) {
        return $http.post(serviceBase + 'csvRegistrosCodigoIncompleto', {"datos": datos, "datosLogin": datosLogin});
    };

    /* ------------------------------- CODIGO INCOMPLETO ---------------------------- */
    /**
     * services nivelacion
     */

    obj.searchTicket = function (datos) {
        return $http.post(serviceBase + 'saveTicket', {'data': datos});
    };

    obj.searchIdTecnic = function (datos) {

        return $http.post(serviceBase + 'searchIdTecnic', {'data':datos});
    };

    obj.saveNivelation = function (datos, login) {
        return $http.post(serviceBase + 'saveNivelation', {'datos': datos,'login': login});
    };

    obj.en_genstion_nivelacion = function (login) {
        return $http.post(serviceBase + 'en_genstion_nivelacion', {'data':login});
    };

    obj.buscarhistoricoNivelacion = function (datos) {
        return $http.post(serviceBase + 'buscarhistoricoNivelacion', {'data':datos});
    };

    obj.gestionarNivelacion = function () {
        return $http.post(serviceBase + 'gestionarNivelacion');
    };

    obj.gestionarRegistrosNivelacion = function (curPage, pageSize, Registros) {
        return $http.post(serviceBase + 'gestionarRegistrosNivelacion', {'curPage' :curPage, 'pageSize': pageSize, 'Registros': Registros});
    };

    obj.guardaNivelacion = function (datos, login) {
        return $http.post(serviceBase + 'guardaNivelacion', {'datos' :datos,'login': login});
    };

    obj.marcarEnGestionNivelacion = function (datos, login) {
        return $http.post(serviceBase + 'marcarEnGestionNivelacion', {'datos': datos,'login': login});
    };

    obj.csvNivelacion = function (fechaini, fechafin) {

        return $http.post(serviceBase + 'csvNivelacion', {'fechaini': fechaini,'fechafin': fechafin});
    };

    obj.microzona = function (data) {

        return $http.post(serviceBase + 'microzona', {'data': data});
    };


    return obj;
}]);


app.service('LoadingInterceptor', ['$q', '$rootScope', '$log',
    function ($q, $rootScope, $log) {
        'use strict';

        var xhrCreations = 0;
        var xhrResolutions = 0;

        function isLoading() {
            return xhrResolutions < xhrCreations;
        }

        function updateStatus() {
            $rootScope.loading = isLoading();
        }

        return {
            request: function (config) {
                xhrCreations++;
                updateStatus();
                return config;
            },
            requestError: function (rejection) {
                xhrResolutions++;
                updateStatus();
                $log.error('Request error:', rejection);
                return $q.reject(rejection);
            },
            response: function (response) {
                xhrResolutions++;
                updateStatus();
                return response;
            },
            responseError: function (rejection) {
                xhrResolutions++;
                updateStatus();
                $log.error('Response error:', rejection);
                return $q.reject(rejection);
            }
        };
    }
]);


app.controller('loginCtrl', function ($scope, $http, $rootScope, $location, $route, $routeParams, $cookies, $timeout, services) {
    $rootScope.permiso = false;
    $rootScope.error = "";
    $rootScope.msg_error = "";
    $scope.autenticacion = {};
    var today = new Date();
    $rootScope.year = today.getFullYear();
    //console.log($rootScope.permiso);
    $scope.login = function () {
        //console.log("autenticacion: ",$scope.autenticacion);

        services.loginUser($scope.autenticacion).then(
            function (data) {
                console.log("data: ", data);
                $errorDatos = null;
                $scope.respuesta = data.data;
                console.log("respuesta: ", $scope.respuesta);
                $rootScope.nombre = $scope.respuesta[0].NOMBRE;
                console.log("nombre: ", $rootScope.nombre);
                $location.path('/actividades/');

                $cookies.put("usuarioseguimiento", JSON.stringify(data.data[0]));

                var galleta = JSON.parse($cookies.get("usuarioseguimiento"));
                //
                //galleta = ($cookies.get("usuarioInfoDespacho"));

                $rootScope.galletainfo = galleta;
                $rootScope.permiso = true;
                //console.log("galletainfo: ",$rootScope.galletainfo);
                if ($rootScope.galletainfo.PERFIL === '1' && $rootScope.galletainfo.PERFIL === '2' && $rootScope.galletainfo.PERFIL === '5') {
                    $location.path('/actividades/');
                } else if ($rootScope.galletainfo.PERFIL === '3' || $rootScope.galletainfo.PERFIL === '8') {
                    $location.path('/registros/');
                } else if ($rootScope.galletainfo.PERFIL === '4') {
                    $location.path('/mesaoffline/mesaoffline/');
                } else if ($rootScope.galletainfo.PERFIL === '6') {
                    $location.path('/premisasInfraestructuras/');
                } else if ($rootScope.galletainfo.PERFIL === '12') {
                    $location.path('/novedadesVisita/');
                } else if ($rootScope.galletainfo.PERFIL === '13') {
                    $location.path('/quejasGo/');
                }

                $scope.autenticacion = {};
                //console.log(galleta);
                return data.data;
            },
            function errorCallback(response) {
                $rootScope.error = "Error";
                $rootScope.msg_error = "Usuario o contraseña incorrecta.";
            });
    };

    // $rootScope.changepwd = function(datos) {

    //     if (datos.pwdNueva == datos.pwdNuevaConfirm) {
    //         services.cambiarPWD($rootScope.galletainfo.ID, datos).then(
    //             function(data){

    //                 if (data.status == '201') {
    //                     $("#editarPassword").modal('hide');
    //                     swal("La contraseña se cambió exitosamente!", "La sesión se terminó para que inicies con la nueva contraseña.", "success")
    //                     $rootScope.logout();
    //                 } else{
    //                     swal({
    //                         title: "La contraseña actual no coincide!",
    //                         text: "Por favor valida, ya que la contraseña que has escrito no coincide con la contraseña actual.",
    //                         type: "error",
    //                         confirmButtonClass: "btn-danger",
    //                         confirmButtonText: "Aceptar",
    //                         closeOnConfirm: false
    //                     });
    //                 }
    //             }
    //         );

    //     }else{
    //             swal({
    //                 title: "El campo Nueva contraseña y Confirmar nueva contraseña no coinciden!",
    //                 type: "warning",
    //                 confirmButtonClass: "btn-danger",
    //                 confirmButtonText: "Aceptar",
    //                 closeOnConfirm: false
    //             });
    //     }
    // };


});

app.controller('actividadesCtrl', function ($scope, $http, $rootScope, $location, $route, $routeParams, $cookies, $timeout, services) {
    $scope.iniciaGestion = true;
    $scope.plantillaReparaciones = 0;
    $scope.selectSubAccion = false;
    $scope.errorconexion = "";
    $scope.registrocreado = false;
    $scope.myWelcome = {};
    $scope.listadoSubAcciones = {};
    $scope.planRescate = false;
    $scope.TVdigital1 = false;
    $scope.TVdigital2 = false;
    $scope.TVdigital3 = false;
    $scope.TVdigital4 = false;
    $scope.TVdigital5 = false;
    $scope.TVdigital6 = false;
    $scope.Internet = false;
    $scope.ToIP = false;
    $scope.verplantilla = false;
    $scope.ipServer = "10.100.66.254";
    var timer;

    $scope.usuarios = function (editarUser) {
        $scope.update = false;
        if (editarUser.PASSWORD == "") {
            alert("Por favor ingrese la contraseña");
            return;
        } else {
            services.editarUsuario(editarUser).then(
                function (data) {
                    // $errorDatos=null;
                    $scope.respuesta = "Usuario " + editarUser.LOGIN + " actualizado exitosamente";
                    //console.log($scope.respuesta);
                    //$rootScope.nombre=$scope.respuesta[0].NOMBRE;
                    $scope.update = true;
                    //$location.path('/home/');
                    return data.data;
                },
                function errorCallback(response) {
                    //$scope.error="Usuario no editado";
                }
            );
        }
    };

    $scope.editarModal = function () {
        $scope.errorDatos = null;
        $scope.Tecnico = {};
        $scope.idUsuario = $rootScope.galletainfo.ID;
        $scope.UsuarioNom = $rootScope.galletainfo.NOMBRE;
        $scope.TituloModal = "Editar Usuario con el ID:";
        //console.log($scope.editaInfo);
    };

    $scope.procesos = function () {
        $scope.validaraccion = false;
        $scope.validarsubaccion = false;
        services.getProcesos().then(function (data) {
            $scope.listadoProcesos = data.data[0];
            $scope.listadoAcciones = {};
            return data.data;
        });
    };

    $scope.calcularAcciones = function () {

        if ($scope.gestionmanual.proceso == 'Plan rescate') {
            $scope.planRescate = 1;
        } else {
            $scope.planRescate = 0;
        }

        if ($scope.gestionmanual.proceso == 'Reparaciones') {
            $scope.plantillaReparaciones = 1;
        } else {
            $scope.plantillaReparaciones = 0;
            $scope.gestionmanual.cod_familiar = "";
            $scope.gestionmanual.prueba_integra = "";
            $scope.gestionmanual.telefonia_tdm = "";
            $scope.gestionmanual.telev_hfc = "";
            $scope.gestionmanual.iptv = "";
            $scope.gestionmanual.internet = "";
            $scope.gestionmanual.toip = "";
            $scope.gestionmanual.smartPlay = "";
        }
        ;

        $scope.listadoAcciones = {};

        services.getAcciones($scope.gestionmanual.proceso).then(function (data) {
            $scope.listadoAcciones = data.data[0];
            $scope.validaraccion = true;
            $scope.validarsubaccion = false;
        });
    };

    $scope.calcularSubAcciones = function () {
        $scope.listadoSubAcciones = {};
        if ($scope.gestionmanual.proceso == "Plan rescate" && ($scope.gestionmanual.accion == "Pendiente" || $scope.gestionmanual.accion == "Incompleto")) {
            $scope.validarsubaccion = true;
            $scope.listadoSubAcciones = [
                {ID: '1011 - Fuera de cobertura', SUBACCION: '1011 - Fuera de cobertura'},
                {ID: '1019 - Mala asesoria', SUBACCION: '1019 - Mala asesoria'},
                {ID: '1020 - Incumplimiento contratista', SUBACCION: '1020 - Incumplimiento contratista'},
                {ID: '1021 - Imposibilidad técnica', SUBACCION: '1021 - Imposibilidad técnica'},
                {ID: '1022 - Tap copado', SUBACCION: '1022 - Tap copado'},
                {ID: '1025 - Cliente no desea', SUBACCION: '1025 - Cliente no desea'},
                {ID: '1026 - Casa sola', SUBACCION: '1026 - Casa sola'},
                {ID: '1028 - Aplazada por cliente', SUBACCION: '1028 - Aplazada por cliente'},
                {ID: '1209 - Zona de invasión', SUBACCION: '1209 - Zona de invasión'},
                {ID: '1217 - Equipo no engancha', SUBACCION: '1217 - Equipo no engancha'},
                {ID: '1505 - Dirección errada', SUBACCION: '1505 - Dirección errada'},
                {ID: '1506 - Cliente solicitó otro producto', SUBACCION: '1506 - Cliente solicitó otro producto'},
                {ID: '1508 - Ductos obstruídos', SUBACCION: '1508 - Ductos obstruídos'},
                {ID: '1510 - Cliente no contactado', SUBACCION: '1510 - Cliente no contactado'},
                {ID: '2898 - Requiere visita supervisor ETP', SUBACCION: '2898 - Requiere visita supervisor ETP'},
                {ID: '2899 - Aplazada por lluvia', SUBACCION: '2899 - Aplazada por lluvia'},
                {ID: '8383 - Problemas plataformas', SUBACCION: '8383 - Problemas plataformas'},
                {ID: 'O-01 - Red pendiente en edificios y urbanizaciones', SUBACCION: 'O-01 - Red pendiente en edificios y urbanizaciones'},
                {ID: 'O-02 - Pendiente cliente no autoriza', SUBACCION: 'O-02 - Pendiente cliente no autoriza'},
                {ID: 'O-06 - Gestión de instalaciones', SUBACCION: 'O-06 - Gestión de instalaciones'},
                {ID: 'O-09 - Pendiente por porteria madera', SUBACCION: 'O-09 - Pendiente por porteria madera'},
                {ID: 'O-11 - Pend tiene línea con otro operador', SUBACCION: 'O-11 - Pend tiene línea con otro operador'},
                {ID: 'O-13 - Red pendiente en exteriores', SUBACCION: 'O-13 - Red pendiente en exteriores'},
                {ID: 'O-14 - Ped solicitud repetida', SUBACCION: 'O-14 - Ped solicitud repetida'},
                {ID: 'O-15 - Pendiente por mala asignación', SUBACCION: 'O-15 - Pendiente por mala asignación'},
                {ID: 'O-20 - Pendi inconsistencias infraestructura', SUBACCION: 'O-20 - Pendi inconsistencias infraestructura'},
                {ID: 'O-40 - Pendiente x orden público y/o factores climát', SUBACCION: 'O-40 - Pendiente x orden público y/o factores climát'},
                {ID: 'O-48 - Red mal estado', SUBACCION: 'O-48 - Red mal estado'},
                {ID: 'O-49 - No desea el servicio', SUBACCION: 'O-49 - No desea el servicio'},
                {ID: 'O-50 - Cliente ilocalizado', SUBACCION: 'O-50 - Cliente ilocalizado'},
                {ID: 'O-51 - Pend tiene línea con otro operador', SUBACCION: 'O-51 - Pend tiene línea con otro operador'},
                {ID: 'O-53 - Inconsistencia información', SUBACCION: 'O-53 - Inconsistencia información'},
                {ID: 'O-69 - Pen cliente no contactado', SUBACCION: 'O-69 - Pen cliente no contactado'},
                {ID: 'O-85 - Red externa pendiente', SUBACCION: 'O-85 - Red externa pendiente'},
                {ID: 'O-86 - Pendiente por nodo xdsl', SUBACCION: 'O-86 - Pendiente por nodo xdsl'},
                {ID: 'O-100 - Pendiente solución con proyecto', SUBACCION: 'O-100 - Pendiente solución con proyecto'},
                {ID: 'O-101 - Renumerar o reconfigurar oferta', SUBACCION: 'O-101 - Renumerar o reconfigurar oferta'},
                {ID: 'O-103 - Pendiente por autorización de terceros', SUBACCION: 'O-103 - Pendiente por autorización de terceros'},
                {ID: 'O-112 - Pendiente por reparación de red', SUBACCION: 'O-112 - Pendiente por reparación de red'},
                {ID: 'OT-C01 - Cliente no autoriza', SUBACCION: 'OT-C01 - Cliente no autoriza'},
                {ID: 'OT-C04 - Orden público', SUBACCION: 'OT-C04 - Orden público'},
                {ID: 'OT-C08 - Reconfigurar pedido', SUBACCION: 'OT-C08 - Reconfigurar pedido'},
                {ID: 'OT-C10 - Validar condición instalación', SUBACCION: 'OT-C10 - Validar condición instalación'},
                {ID: 'OT-C12 - Reconfigurar motivo técnico', SUBACCION: 'OT-C12 - Reconfigurar motivo técnico'},
                {ID: 'OT-C14 - Orden del suscriptor', SUBACCION: 'OT-C14 - Orden del suscriptor'},
                {ID: 'OT-C17 - Autorización de terceros', SUBACCION: 'OT-C17 - Autorización de terceros'},
                {ID: 'OT-C19 - Factores climáticos', SUBACCION: 'OT-C19 - Factores climáticos'},
                {ID: 'OT-T01 - Red pendiente edif y urb', SUBACCION: 'OT-T01 - Red pendiente edif y urb'},
                {ID: 'OT-T04 - Red externa', SUBACCION: 'OT-T04 - Red externa'},
                {ID: 'OT-T05 - Mala asignación', SUBACCION: 'OT-T05 - Mala asignación'},
                {ID: 'OT-T10 - Reparación de red externa', SUBACCION: 'OT-T10 - Reparación de red externa'},
                {ID: 'P-CRM - Reagendado', SUBACCION: 'P-CRM - Reagendado'},
                {ID: 'O-08 - Pendiente por orden del suscriptor', SUBACCION: 'O-08 - Pendiente por orden del suscriptor'},
                {ID: 'O-23 - Pendiente no contestan', SUBACCION: 'O-23 - Pendiente no contestan'},
                {ID: 'OT-C02 - Cliente ilocalizado', SUBACCION: 'OT-C02 - Cliente ilocalizado'},
                {ID: 'OT-C06 - Inconsistencia información', SUBACCION: 'OT-C06 - Inconsistencia información'},
                {ID: 'OT-T02 - Gestión de instalaciones', SUBACCION: 'OT-T02 - Gestión de instalaciones'},
                {ID: 'O-34 - Pendiente por factores climáticos', SUBACCION: 'O-34 - Pendiente por factores climáticos'},
                {ID: 'OT-C15 - Por agendar', SUBACCION: 'OT-C15 - Por agendar'},
                {ID: 'OT-T19 - Plataforma caída', SUBACCION: 'OT-T19 - Plataforma caída'},
                {ID: '1014 - Poste averiado', SUBACCION: '1014 - Poste averiado'},
                {ID: 'O-24 - Pendi postería', SUBACCION: 'O-24 - Pendi postería'},
                {ID: 'OT-C05 - Gestión fraudes instalaciones', SUBACCION: 'OT-C05 - Gestión fraudes instalaciones'},
                {ID: 'OT-C11 - Cancelar motivo técnico', SUBACCION: 'OT-C11 - Cancelar motivo técnico'},
                {ID: 'OT-T17 - Solución con proyecto', SUBACCION: 'OT-T17 - Solución con proyecto'}
            ];
        } else {
            services.getSubAcciones($scope.gestionmanual.proceso, $scope.gestionmanual.accion).then(function (data) {
                $scope.listadoSubAcciones = data.data[0];
                $scope.validarsubaccion = true;
            }, function errorCallback(response) {

                if (response.status == "200") {
                    $scope.validarsubaccion = false;
                }
                var subAccion = "";
                $scope.mostrarModal();
            });
        }
        ;
    };

    $scope.calcularCodigos = function () {

        $scope.listadocodigos = {};
        services.getCodigos($scope.gestionmanual.proceso, $scope.gestionmanual.UNESourceSystem).then(function (data) {
            $scope.listadocodigos = data.data[0];
        }, function errorCallback(response) {
            if (response.status == "200") {

            }
        });
    };

    $scope.calcularDiagnostico = function (producto, accion) {

        if (accion == 'Enrutar') {
            $scope.listadodiagnosticos = {};
            services.getDiagnosticos($scope.gestionmanual.producto, $scope.gestionmanual.accion).then(function (data) {
                $scope.listadodiagnosticos = data.data[0];
            }, function errorCallback(response) {
                if (response.status == "200") {

                }
            });
        }
    };

    $scope.mostrarModal = function () {
        if ($scope.infopedido == true) {
            var producto = $scope.myWelcome.uNETecnologias;

            if ($scope.gestionmanual.producto != "" && $scope.gestionmanual.producto != undefined) {
                producto = $scope.gestionmanual.producto;
            }
        } else if ($scope.gestionmanual.producto == undefined) {
            alert("Por favor seleccione el producto");
            return;
        } else {
            var producto = $scope.gestionmanual.producto;
        }

        if (producto.indexOf("HFC") !== -1) {
            var tecnologia = "HFC";
        } else if (producto.indexOf("ADSL") !== -1 || producto.indexOf("REDCO") !== -1 || producto.indexOf("Telefonia_Basica") !== -1) {
            var tecnologia = "ADSL";
        } else if (producto.indexOf("GPON") !== -1) {
            var tecnologia = "GPON";
        } else if (producto.indexOf("DTH") !== -1) {
            var tecnologia = "DTH";
        } else if (producto.indexOf("LTE") !== -1) {
            var tecnologia = "LTE";
        }

        if ($scope.gestionmanual.accion == "Registrar materiales") {
            $scope.materiales = [{id: '1', tipoCable: 'No uso', inicio: '', fin: ''}];
            $('#Registrarmateriales').modal('show');
            $scope.OpenModal = "Registrarmateriales";
        }
        ;

        // if ($scope.gestionmanual.accion == "Soporte tecnico" && $scope.gestionmanual.subAccion == "General") {
        //     var tech = '';
        //     if ($scope.myWelcome.uNETecnologias != '' && $scope.myWelcome.uNETecnologias != undefined) {
        //         tech = $scope.myWelcome.uNETecnologias;
        //     } else {
        //         tech = $scope.gestionmanual.producto;
        //     }

        //     tech = tech.toUpperCase();

        //     if (tech.includes("HFC")) {
        //         $('#cumplirInstalacionHFC').modal('show');
        //         $scope.OpenModal = "cumplirInstalacionHFC";
        //     }
        //     if (tech.includes("ADSL")) {
        //         $('#cumplirInstalacionADSL').modal('show');
        //         $scope.OpenModal = "cumplirInstalacionADSL";
        //     }

        // }

        if (tecnologia == "HFC" && ($scope.gestionmanual.subAccion == "INFRAESTRUCTURA HFC" || $scope.gestionmanual.subAccion == "O-112 Pendiente Por Reparacion de Red")) {
            if (producto == undefined) {
                alert("Por favor seleccione el producto");
                return;
            } else {
                $('#PendiInfraHFC').modal('show'); ///hacer filtro de productos HFC para mostrar el respectivo modal utilizar $scope.infopedido=false para mostrar
                $scope.OpenModal = "PendiInfraHFC";
            }
        } else if (tecnologia == "ADSL" && ($scope.gestionmanual.subAccion == "INFRAESTRUCTURA COBRE" || $scope.gestionmanual.subAccion == "O-112 Pendiente Por Reparacion de Red")) {
            $('#PendiInfraADSL').modal('show');
            $scope.OpenModal = "PendiInfraADSL";
        }
        ;

        if ($scope.gestionmanual.subAccion == "Contingencia(solo en NCA)") {
            if (tecnologia == "DTH") {
                $('#ContingenciaDTH').modal('show');
                $scope.OpenModal = "ContingenciaDTH";
            } else {
                $('#ContingenciaOtros').modal('show');
                $scope.OpenModal = "ContingenciaOtros";
            }
            ;
        }
        ;

        if ($scope.gestionmanual.subAccion == "Normal") {
            $('#ContingenciaNormal').modal('show');
            $scope.OpenModal = "ContingenciaNormal";
        } else if ($scope.gestionmanual.subAccion == "Contingencia Cambio") {
            $('#ContingenciaCambio').modal('show');
            $scope.OpenModal = "ContingenciaCambio";
        } else if ($scope.gestionmanual.subAccion == "Contingencia Nuevo") {
            $('#ContingenciaNuevo').modal('show');
            $scope.OpenModal = "ContingenciaNuevo";
        } else if ($scope.gestionmanual.subAccion == "Contingencia Reuso") {
            $('#ContingenciaReuso').modal('show');
            $scope.OpenModal = "ContingenciaReuso";
        }

        if ($scope.gestionmanual.subAccion == "Cumple parametros de instalacion" || $scope.gestionmanual.subAccion == "Cumple parametros de reparacion") {
            if (tecnologia == "LTE" || tecnologia == "DTH") {
                alert("Para los productos DTH y LTE no aplica cumplir con parametros");
                return;
            } else if ($scope.gestionmanual.CIUDAD == "MESA DE AYUDA" || $scope.gestionmanual.CIUDAD == "MIGRACIONES" || $scope.gestionmanual.CIUDAD == "TECNICOS DE APOYO") {
                alert("Para los despachos Mesa, migraciones y técnicos de apoyo no aplica cumplir con parametros");
                return;
            } else {
                if ($scope.gestionmanual.subAccion == "Cumple parametros de instalacion") {
                    $scope.cumplirproceso = "Cumplir instalacion";
                } else {
                    $scope.cumplirproceso = "Cumplir reparacion";
                }
                //alert("guess what"+$scope.myWelcome.uNETecnologias);

                //$scope.gestionmanual.producto
                var tech = '';
                if ($scope.myWelcome.uNETecnologias != '' && $scope.myWelcome.uNETecnologias != undefined) {
                    tech = $scope.myWelcome.uNETecnologias;
                } else {
                    tech = $scope.gestionmanual.producto;
                }

                tech = tech.toUpperCase();

                if (tech.includes("HFC")) {
                    $('#cumplirInstalacionHFC').modal('show');
                    $scope.OpenModal = "cumplirInstalacionHFC";
                }
                if (tech.includes("ADSL")) {
                    $('#cumplirInstalacionADSL').modal('show');
                    $scope.OpenModal = "cumplirInstalacionADSL";
                }
            }

        }

        if ($scope.gestionmanual.accion == "Cumplir" && $scope.gestionmanual.subAccion == "Recoger Equipos") {
            $scope.equiposRecoger = [{
                id: '1',
                pedido: $scope.pedido,
                mac: '',
                serial: '',
                ciudad: $scope.gestionmanual.CIUDAD,
                CedTecnico: $scope.gestionmanual.tecnico,
                NomTecnico: $scope.tecnico,
                contratista: $scope.empresa
            }];
            $('#recogerEquipos').modal('show');
            $scope.OpenModal = "recogerEquipos";
        }
        ;

        if (tecnologia == "HFC" && ($scope.gestionmanual.subAccion == "OT-T10-Reparacion de red externa")) {
            if (producto == undefined) {
                alert("Por favor seleccione el producto");
                return;
            } else {
                $('#PendiInstaHFC-OT-T10').modal('show'); ///hacer filtro de productos HFC para mostrar el respectivo modal utilizar $scope.infopedido=false para mostrar
                $scope.OpenModal = "PendiInstaHFC-OT-T10";
            }
        }
    };

    //este bloque añade al modal de materiales los inputs deseados
    $scope.addNuevoMaterial = function () {
        var newItemNo = $scope.materiales.length + 1;
        $scope.materiales.push({'id': +newItemNo, tipoCable: 'No uso'});
        //console.log(usuario);
        //$scope.crearnovedad();
    };

    //este bloque añade al modal de recogida de equipos los inputs deseados
    $scope.addEquipoRecoger = function () {
        var newEquiporecoger = $scope.equiposRecoger.length + 1;
        $scope.equiposRecoger.push({
            'id': +newEquiporecoger,
            pedido: $scope.pedido,
            ciudad: $scope.gestionmanual.CIUDAD,
            CedTecnico: $scope.gestionmanual.tecnico,
            NomTecnico: $scope.tecnico,
            contratista: $scope.empresa,
            celular: $scope.celular
        });
    };

    //este bloque elimina al modal de materiales los inputs deseados
    $scope.removeNuevoMaterial = function () {
        var lastItem = $scope.materiales.length - 1;
        if (lastItem != 0) {
            $scope.materiales.splice(lastItem);
            //console.log($scope.novedades);
        }
    }

    //este bloque elimina al modal de recogida de equipos los inputs deseados
    $scope.removeEquipoRecoger = function () {
        var lastEquipoRecoger = $scope.equiposRecoger.length - 1;
        if (lastEquipoRecoger != 0) {
            $scope.equiposRecoger.splice(lastEquipoRecoger);
        }
    }

    $scope.guardarModal = function (materiales) {
        $scope.verplantilla = true;
        if ($scope.OpenModal == "Registrarmateriales") {
            var total = materiales.length;
            $scope.observacion = "";
            for (var i = 0; i < total; i++) {
                $scope.observacion = $scope.observacion + "Tipo cable: " + materiales[i].tipoCable + ", Inicio: " + materiales[i].inicio + ", Fin: " + materiales[i].fin + "/";
            }
        }

        if ($scope.OpenModal == "CambioEquipoDTH") {
            $scope.observacion = "Cuenta Domiciliaria: " + $scope.equipoDTH.cuenta + ", ID Cuenta: " + $scope.equipoDTH.IdCuenta + ", Motivo: " + $scope.equipoDTH.motivoCambio + ", Chip ID Entra: " + $scope.equipoDTH.chipEntra + ", Chip ID Sale: " + $scope.equipoDTH.chipSale + ", SmartCard Entra: " + $scope.equipoDTH.SmartEntra + ", SmartCard Sale: " + $scope.equipoDTH.SmartSale

            services.insertarCambioEquipo('DTH', $scope.equipoDTH, $scope.pedido).then(
                function (data) {
                    $scope.datoscambioEquipo = data.data[0];
                    console.log("id cambio equipo DTH: " + $scope.datoscambioEquipo);
                }
            );

        }

        if ($scope.OpenModal == "CambioEquipoHFC") {
            $scope.observacion = "Cuenta Domiciliaria: " + $scope.equipoHFC.cuenta + ", ID Cuenta: " + $scope.equipoHFC.IdCuenta + ", Servicio: " + $scope.equipoHFC.servicio + ", Motivo: " + $scope.equipoHFC.motivoCambio + ", Equipo Entra: " + $scope.equipoHFC.equipoEntra + ", Equipo Sale: " + $scope.equipoHFC.equipoSale + ", MAC Entra: " + $scope.equipoHFC.macEntra + ", MAC Sale: " + $scope.equipoHFC.macSale

            services.insertarCambioEquipo('HFC', $scope.equipoHFC, $scope.pedido).then(
                function (data) {
                    //console.log("data: ", data);
                    $scope.datoscambioEquipo = data.data[0];
                    //console.log("id cambio equipo HFC: " + $scope.datoscambioEquipo);
                }
            );
        }

        if ($scope.OpenModal == "CambioEquipoOtros") {
            $scope.observacion = "Motivo del cambio: " + $scope.equipoOtros.motivoCambio + ", Serial sale: " + $scope.equipoOtros.Serialsale + ", Serial entra: " + $scope.equipoOtros.Serialentra + ", Marca sale: " + $scope.equipoOtros.Marcasale + ", Marca entra: " + $scope.equipoOtros.Marcaentra + ", Referencia sale: " + $scope.equipoOtros.Refentra + ", Referencia entra: " + $scope.equipoOtros.Refsale

            services.insertarCambioEquipo('ADSL', $scope.equipoOtros, $scope.pedido).then(x |
                function (data) {
                    $scope.datoscambioEquipo = data.data[0];
                    //console.log("id cambio equipo ADSL: " + $scope.datoscambioEquipo);
                }
            );
        }

        if ($scope.OpenModal == "PendiInfraADSL") {

            $scope.observacion = ""; //Limpia la plantilla para cuando se actualiza algún dato

            //Captura el técnico si la BD Stanby_Click esta por en funcionamiento o por fuera.
            if ($scope.gestionmanual.NomTec == undefined) {
                $scope.NombreTecnico = $scope.tecnico;
            } else {
                $scope.NombreTecnico = $scope.gestionmanual.NomTec;
            }

            var label = [
                'Daño: ',
                ', Prod: ',
                ', Sape Dist pri: ',
                ', Smpro Dist pri: ',
                ', VAC AT Dist pri: ',
                ', VDC AT Dist pri: ',
                ', Resist AT Dist pri: ',
                ', Cap AT Dist pri: ',
                ', VAC BT Dist pri: ',
                ', VDC BT Dist pri: ',
                ', Resist BT Dist pri: ',
                ', Cap BT Dist pri: ',
                ', VAC AB Dist pri: ',
                ', VDC AB Dist pri: ',
                ', Resist AB Dist pri: ',
                ', Cap AB Dist pri: ',
                ', Sape Arm pri: ',
                ', Smpro Arm pri: ',
                ', VAC AT Arm pri: ',
                ', VDC AT Arm pri: ',
                ', Resist AT Arm pri: ',
                ', Cap AT Arm pri: ',
                ', VAC BT Arm pri: ',
                ', VDC BT Arm pri: ',
                ', Resist BT Arm pri: ',
                ', Cap BT Arm pri: ',
                ', VAC AB Arm pri: ',
                ', VDC AB Arm pri: ',
                ', Resist AB Arm pri: ',
                ', Cap AB Arm pri: ',
                ', Sape caja sec: ',
                ', Smpro caja sec: ',
                ', VAC AT caja sec: ',
                ', VDC AT caja sec: ',
                ', Resist AT caja sec: ',
                ', Cap AT caja sec: ',
                ', VAC BT caja sec: ',
                ', VDC BT caja sec: ',
                ', Resist BT caja sec: ',
                ', Cap BT caja sec: ',
                ', VAC AB caja sec: ',
                ', VDC AB caja sec: ',
                ', Resist AB caja sec: ',
                ', Cap AB caja sec: ',
                ', Sape Arm sec: ',
                ', Smpro Arm sec: ',
                ', VAC AT Arm sec: ',
                ', VDC AT Arm sec: ',
                ', Resist AT Arm sec: ',
                ', Cap AT Arm sec: ',
                ', VAC BT Arm sec: ',
                ', VDC BT Arm sec: ',
                ', Resist BT Arm sec: ',
                ', Cap BT Arm sec: ',
                ', VAC AB Arm sec: ',
                ', VDC AB Arm sec: ',
                ', Resist AB Arm sec: ',
                ', Cap AB Arm sec: ',
                ', Tec: ',
                ', Cel: ',
                ', Ciud: ',
                ', Eq med: ',
                ', Observaciones: '
            ];

            var value = [
                $scope.pendiInfraCobre.IdDano,
                $scope.pendiInfraCobre.producto,

                //************************************
                $scope.pendiInfraCobre.priSapeDis,
                $scope.pendiInfraCobre.priSmproDis,

                $scope.pendiInfraCobre.priDisVACAT,
                $scope.pendiInfraCobre.priDisVDCAT,
                $scope.pendiInfraCobre.priDisResisAT,
                $scope.pendiInfraCobre.priDisCapaAT,

                $scope.pendiInfraCobre.priDisVACBT,
                $scope.pendiInfraCobre.priDisVDCBT,
                $scope.pendiInfraCobre.priDisResisBT,
                $scope.pendiInfraCobre.priDisCapaBT,

                $scope.pendiInfraCobre.priDisVACAB,
                $scope.pendiInfraCobre.priDisVDCAB,
                $scope.pendiInfraCobre.priDisResisAB,
                $scope.pendiInfraCobre.priDisCapaAB,

                //***********************************
                $scope.pendiInfraCobre.priSapeArm,
                $scope.pendiInfraCobre.priSmproArm,

                $scope.pendiInfraCobre.priArmVACAT,
                $scope.pendiInfraCobre.priArmVDCAT,
                $scope.pendiInfraCobre.priArmResisAT,
                $scope.pendiInfraCobre.priArmCapaAT,

                $scope.pendiInfraCobre.priArmVACBT,
                $scope.pendiInfraCobre.priArmVDCBT,
                $scope.pendiInfraCobre.priArmResisBT,
                $scope.pendiInfraCobre.priArmCapaBT,

                $scope.pendiInfraCobre.priArmVACAB,
                $scope.pendiInfraCobre.priArmVDCAB,
                $scope.pendiInfraCobre.priArmResisAB,
                $scope.pendiInfraCobre.priArmCapaAB,

                //***********************************
                $scope.pendiInfraCobre.SecSapeDis,
                $scope.pendiInfraCobre.SecSmproDis,

                $scope.pendiInfraCobre.SecDisVACAT,
                $scope.pendiInfraCobre.SecDisVDCAT,
                $scope.pendiInfraCobre.SecDisResisAT,
                $scope.pendiInfraCobre.SecDisCapaAT,

                $scope.pendiInfraCobre.SecDisVACBT,
                $scope.pendiInfraCobre.SecDisVDCBT,
                $scope.pendiInfraCobre.SecDisResisBT,
                $scope.pendiInfraCobre.SecDisCapaBT,

                $scope.pendiInfraCobre.SecDisVACAB,
                $scope.pendiInfraCobre.SecDisVDCAB,
                $scope.pendiInfraCobre.SecDisResisAB,
                $scope.pendiInfraCobre.SecDisCapaAB,

                //************************************
                $scope.pendiInfraCobre.SecSapeArm,
                $scope.pendiInfraCobre.SecSmproArm,

                $scope.pendiInfraCobre.SecArmVACAT,
                $scope.pendiInfraCobre.SecArmVDCAT,
                $scope.pendiInfraCobre.SecArmResisAT,
                $scope.pendiInfraCobre.SecArmCapaAT,

                $scope.pendiInfraCobre.SecArmVACBT,
                $scope.pendiInfraCobre.SecArmVDCBT,
                $scope.pendiInfraCobre.SecArmResisBT,
                $scope.pendiInfraCobre.SecArmCapaBT,

                $scope.pendiInfraCobre.SecArmVACAB,
                $scope.pendiInfraCobre.SecArmVDCAB,
                $scope.pendiInfraCobre.SecArmResisAB,
                $scope.pendiInfraCobre.SecArmCapaAB,

                //************************************
                $scope.NombreTecnico,
                $scope.pendiInfraCobre.CelTec,
                $scope.gestionmanual.CIUDAD,
                $scope.pendiInfraCobre.EqMedicion,
                $scope.pendiInfraCobre.observaciones
            ];

            //Concatena únicamente los campos que no están vacíos
            for (var i = 0; i < value.length; i += 1) {
                if (value[i] != undefined) {
                    $scope.observacion += label[i] + value[i];
                }
            }
            $scope.observacion = $scope.observacion.replace(/undefined/g, "");

        }

        if ($scope.OpenModal == "PendiInfraHFC") {

            if ($scope.gestionmanual.producto == 'HFC-Internet' || $scope.gestionmanual.producto == 'HFC-ToIP' || $scope.gestionmanual.producto == 'HFC-TV_Digital') {
                //Este bloque valida en que estado esta CMobsoleto si está en si, no cierra el modal, si está en no, cierra el modal
                var CMobsoleto = document.getElementById("CMobsoleto").value;
                if (CMobsoleto == "Si") {
                    $scope.modal = ""
                    Swal(
                        'No se puede guardar la plantilla!',
                        'Se requiere cambiar el equipo ya que es obsoleto'
                    );
                } else {
                    $scope.modal = "modal"
                }

                //los array label y  value me almacenan los textos de los labels y los valores que toman en la plantilla

                var label = [
                    'Señal: ',
                    ', v tap: ',
                    ', Marcación TAP: ',
                    ', Dir TAP:',
                    ', Id pru: ',
                    ', Mac CM: ',
                    ', Mac DSAM: ',
                    ', Técnico:  ',
                    ', Cel: ',
                    ', City: ',
                    ', Id p vecinos: ',
                    '/',
                    '/',
                    ', T Red: ',
                    ', RF-14: ',
                    ' dBm, RF-120: ',
                    ' dBm, RF-135: ',
                    ' dBm, RF-157: ',
                    ' dBm, CH: ',
                    ', CH: ',
                    ', RF 1: ',
                    ' dBm, RF 2: ',
                    ' dBm, Perdida de pq 1: ',
                    ', Perdida de pq 2: ',
                    ', MER 1: ',
                    ' dB, MER 2: ',
                    ' dB, BER 1: ',
                    ', BER 2: ',
                    ', P UP 1: ',
                    ' dB, P UP 2: ',
                    ' dB, P DOWN 1: ',
                    ' dB, P DOWN 2: ',
                    ' dB, RF CH 89: ',
                    ', MER 89: ',
                    ', BER 89: ',
                    ', RF CH 73: ',
                    ' dBm, MER 73: ',
                    ', BER 73: ',
                    ', CH: ',
                    ', RF: ',
                    ' dBm, MER: ',
                    ', BER: ',
                    ', CH: ',
                    ', RF: ',
                    ' dBm, MER: ',
                    ', BER: ',
                    ', # sin enlace en der: ',
                    ', # sin enlace en amp: ',
                    ', # afect en der: ',
                    ', # afect en amp: ',
                    ', # cltes TDR amp: ',
                    ', Img adj PNM CRM: ',
                    ', Img adj falla CRM: ',
                    ', Ruido: ',
                    ', Marquilla: ',
                    ', Nodo/Cmts: ',
                    ', Observaciones: '
                ];

                var value = [
                    $scope.pendiInfraInternet.repaProvisional,
                    $scope.pendiInfraInternet.valortab,
                    $scope.pendiInfraInternet.MarcacionTap,
                    $scope.pendiInfraInternet.DireccionTap,
                    $scope.pendiInfraInternet.idPrueba,
                    $scope.pendiInfraInternet.macEquipo,
                    $scope.pendiInfraInternet.macDsam,
                    $scope.tecnico,
                    $scope.pendiInfraInternet.Celular,
                    $scope.gestionmanual.CIUDAD,
                    $scope.pendiInfraInternet.idVecinos,
                    $scope.pendiInfraInternet.idVecinos1,
                    $scope.pendiInfraInternet.idVecinos2,
                    $scope.pendiInfraInternet.tipored,
                    $scope.pendiInfraInternet.RF14,
                    $scope.pendiInfraInternet.RF120,
                    $scope.pendiInfraInternet.RF135,
                    $scope.pendiInfraInternet.RF157,
                    $scope.pendiInfraInternet.Ch1,
                    $scope.pendiInfraInternet.Ch2,
                    $scope.pendiInfraInternet.RF1,
                    $scope.pendiInfraInternet.RF22,
                    $scope.pendiInfraInternet.PerdidaPaquete1,
                    $scope.pendiInfraInternet.PerdidaPaquete2,
                    $scope.pendiInfraInternet.Mer1,
                    $scope.pendiInfraInternet.Mer2,
                    $scope.pendiInfraInternet.Ber1,
                    $scope.pendiInfraInternet.Ber2,
                    $scope.pendiInfraInternet.PotenciaUp1,
                    $scope.pendiInfraInternet.PotenciaUp2,
                    $scope.pendiInfraInternet.PotenciaDW1,
                    $scope.pendiInfraInternet.PotenciaDW2,
                    $scope.pendiInfraInternet.RFCH89,
                    $scope.pendiInfraInternet.Mer89,
                    $scope.pendiInfraInternet.Ber89,
                    $scope.pendiInfraInternet.RFCH73,
                    $scope.pendiInfraInternet.Mer73,
                    $scope.pendiInfraInternet.Ber73,
                    $scope.pendiInfraInternet.CHMalo1,
                    $scope.pendiInfraInternet.RFMalo1,
                    $scope.pendiInfraInternet.MerMalo1,
                    $scope.pendiInfraInternet.BerMalo1,
                    $scope.pendiInfraInternet.CHMalo2,
                    $scope.pendiInfraInternet.RFMalo2,
                    $scope.pendiInfraInternet.MerMalo2,
                    $scope.pendiInfraInternet.BerMalo2,
                    $scope.pendiInfraInternet.NroCliSinEnlace,
                    $scope.pendiInfraInternet.NroCliSinAmplificador,
                    $scope.pendiInfraInternet.NroCliAfecDerivador,
                    $scope.pendiInfraInternet.NroCliAfecAmplificador,
                    $scope.pendiInfraInternet.NroCliTDRAmplificador,
                    $scope.pendiInfraInternet.ImgPnm,
                    $scope.pendiInfraInternet.IMGFallaSiebel,
                    $scope.pendiInfraInternet.ProbRuido,
                    $scope.pendiInfraInternet.InstCorreaPlastica,
                    $scope.pendiInfraInternet.NodoAAA,
                    $scope.pendiInfraInternet.observaciones
                ];

                //Se limpia la variable observacion para cada que cierren  y vuelvan a abrir el modal para agregar o quitar información
                $scope.observacion = "";

                //se recorren los array y se concatenan unicamente los que tiene información diligenciada

                if (CMobsoleto != "Si") {
                    for (var i = 0; i < value.length; i += 1) {
                        if (value[i] != undefined) {
                            $scope.observacion += label[i] + value[i];
                        }
                    }
                } else {
                    $scope.observacion = "";
                }

                $scope.observacion = $scope.observacion.replace(/undefined/g, "");

            } else if ($scope.gestionmanual.producto == 'HFC-TV_Basica') {
                $scope.observacion = "reparación provisional?: " + $scope.pendiInfraTvBas.repaProvisional + ", RF canal 2: " + $scope.pendiInfraTvBas.RF2 + ", RF canal 110: " + $scope.pendiInfraTvBas.RF110 + ", Ciudad: " + $scope.pendiInfraTvBas.Ciudad + ", Celular: " + $scope.pendiInfraTvBas.Celular + ", Técnico: " + $scope.pendiInfraTvBas.nomTecnico + ", Observaciones: " + $scope.pendiInfraTvBas.observaciones
            } /*else if ($scope.gestionmanual.producto == 'HFC-TV_Digital') {
                $scope.observacion = "reparación provisional?: " + $scope.pendiInfraTvDig.repaProvisional + ", Canal 75 TVD Fcia. Mhz 531, RF:  " + $scope.pendiInfraTvDig.RF75 + ", MER 75: " + $scope.pendiInfraTvDig.MER75 + ", VER 75: " + $scope.pendiInfraTvDig.VER75 + ", Canal 82 TVD Fcia. Mhz 573: " + $scope.pendiInfraTvDig.RF82 + ", MER 82: " + $scope.pendiInfraTvDig.MER82 + ", VER 82: " + $scope.pendiInfraTvDig.VER82 + ", Canal 89 TVD Fcia. Mhz 615, RF: " + $scope.pendiInfraTvDig.RF89 + ", MER 89: " + $scope.pendiInfraTvDig.MER89 + ", VER 89: " + $scope.pendiInfraTvDig.VER89 + ", Canal 73 AudioD Fcia. Mhz 519, RF: " + $scope.pendiInfraTvDig.RF73 + ", MER 73: " + $scope.pendiInfraTvDig.MER73 + ", VER 73: " + $scope.pendiInfraTvDig.VER73 + ", Técnico: " + $scope.pendiInfraTvDig.Nomtecnico + ", Movil: " + $scope.pendiInfraTvDig.Celular + ", Ciudad: " + $scope.pendiInfraTvDig.Ciudad + ", Observaciones: " + $scope.pendiInfraTvDig.observaciones
            }*/
            ;
        }

        if ($scope.OpenModal == "ContingenciaDTH") {

            $scope.observacion = "Se aprovisiona ";

            if ($scope.contingenciaDTH.aprovi != undefined) {
                $scope.observacion += "-Deco TV Digital por " + $scope.contingenciaDTH.aprovi + " con el Chip ID " + $scope.contingenciaDTH.chip + " y SmartCard " + $scope.contingenciaDTH.smart;
            }

            if ($scope.contingenciaDTH.aprovi2 != undefined) {
                $scope.observacion += "-Deco TV Digital por " + $scope.contingenciaDTH.aprovi2 + " con el Chip ID " + $scope.contingenciaDTH.chip2 + " y SmartCard " + $scope.contingenciaDTH.smart2;
            }

            if ($scope.contingenciaDTH.aprovi3 != undefined) {
                $scope.observacion += "-Deco TV Digital por " + $scope.contingenciaDTH.aprovi3 + " con el Chip ID " + $scope.contingenciaDTH.chip3 + " y SmartCard " + $scope.contingenciaDTH.smart3;
            }

            if ($scope.contingenciaDTH.aprovi4 != undefined) {
                $scope.observacion += "-Deco TV Digital por " + $scope.contingenciaDTH.aprovi4 + " con el Chip ID " + $scope.contingenciaDTH.chip4 + " y SmartCard " + $scope.contingenciaDTH.smart4;
            }

            if ($scope.contingenciaDTH.observaciones != undefined) {
                $scope.observacion += "-Queda en pediente: " + $scope.contingenciaDTH.observaciones + "--";
            }
        }

        if ($scope.OpenModal == "ContingenciaOtros") {

            $scope.observacion = "Se aprovisiona ";

            if ($scope.contingenciaNCA.aproviInternet != undefined) {
                $scope.observacion += "-Internet por " + $scope.contingenciaNCA.aproviInternet + " con la MAC " + $scope.contingenciaNCA.MACinternet;
            }

            if ($scope.contingenciaNCA.aproviToIP != undefined) {
                $scope.observacion += "-ToIP por " + $scope.contingenciaNCA.aproviToIP + " con la MAC " + $scope.contingenciaNCA.MACToIP;
            }

            if ($scope.contingenciaNCA.aprovi1 != undefined) {
                $scope.observacion += "-Deco TV Digital por " + $scope.contingenciaNCA.aprovi1 + " con la MAC " + $scope.contingenciaNCA.MACTV1;
            }

            if ($scope.contingenciaNCA.aprovi2 != undefined) {
                $scope.observacion += "-Deco TV Digital por " + $scope.contingenciaNCA.aprovi2 + " con la MAC " + $scope.contingenciaNCA.MACTV2;
            }

            if ($scope.contingenciaNCA.aprovi3 != undefined) {
                $scope.observacion += "-Deco TV Digital por " + $scope.contingenciaNCA.aprovi3 + " con la MAC " + $scope.contingenciaNCA.MACTV3;
            }

            if ($scope.contingenciaNCA.aprovi4 != undefined) {
                $scope.observacion += "-Deco TV Digital por " + $scope.contingenciaNCA.aprovi4 + " con la MAC " + $scope.contingenciaNCA.MACTV4;
            }

            if ($scope.contingenciaNCA.aprovi5 != undefined) {
                $scope.observacion += "-Deco TV Digital por " + $scope.contingenciaNCA.aprovi5 + " con la MAC " + $scope.contingenciaNCA.MACTV5;
            }

            if ($scope.contingenciaNCA.aprovi6 != undefined) {
                $scope.observacion += "-Deco TV Digital por " + $scope.contingenciaNCA.aprovi6 + " con la MAC " + $scope.contingenciaNCA.MACTV6;
            }

            if ($scope.contingenciaNCA.observaciones != undefined) {
                $scope.observacion += "-Queda en pediente: " + $scope.contingenciaNCA.observaciones + "--";
            }

        }

        if ($scope.OpenModal == "cumplirInstalacionHFC") {

            var diagnostico = "";
            var sep = "";

            //console.log($scope.cumplir);

            if ($scope.cumplir.sinalarmas != undefined && $scope.cumplir.sinalarmas) {
                diagnostico = "0";
            } else {
                if ($scope.cumplir.potenciaup != undefined && $scope.cumplir.potenciaup) diagnostico = "1";
                if ($scope.cumplir.snrup != undefined && $scope.cumplir.snrup) diagnostico = diagnostico + "/2";
                if ($scope.cumplir.potenciadown != undefined && $scope.cumplir.potenciadown) diagnostico = diagnostico + "/3";
                if ($scope.cumplir.snrdown != undefined && $scope.cumplir.snrdown) diagnostico = diagnostico + "/4";
                if ($scope.cumplir.paquetesnocorregidosup != undefined && $scope.cumplir.paquetesnocorregidosup) diagnostico = diagnostico + "/5";
                if ($scope.cumplir.paquetesnocorregidosdown != undefined && $scope.cumplir.paquetesnocorregidosdown) diagnostico = diagnostico + "/6";
                if ($scope.cumplir.modoparcialenportadoras != undefined && $scope.cumplir.modoparcialenportadoras) diagnostico = diagnostico + "/7";
                if ($scope.cumplir.ajustesdepotencia != undefined && $scope.cumplir.ajustesdepotencia) diagnostico = diagnostico + "/8";
                if ($scope.cumplir.porcentajemiss != undefined && $scope.cumplir.porcentajemiss) diagnostico = diagnostico + "/9";
            }

            $scope.observacion = "*TID : " + $scope.cumplir.transaccionid +
                "*LDAP : " + $scope.cumplir.validacionldap +
                "*Estado CM : " + $scope.cumplir.estadocm +
                "*IP Navegación : " + $scope.cumplir.tieneipnavegacion +
                "*Diagnostico: {" + diagnostico + "}" +
                //"*Estado EMTA : "+$scope.cumplir.estadoemta+
                "*IP EMTA : " + $scope.cumplir.tieneipemta +
                "*Archivo de Config : " + $scope.cumplir.tienearchivoconfiguracion +
                "*Linea registrada : " + $scope.cumplir.registradaims +
                "*ID Llamada : " + $scope.cumplir.idllamadaentrante +
                "*Config. Plataforma : " + $scope.cumplir.configuradoplataformatv +
                "*ONETV : " + $scope.cumplir.esonetv +
                "*Estado CM Deco : " + $scope.cumplir.estadocmembebido;

            /*$scope.observacion="Cambió equipo? "+$scope.cumplir.cambioEquipo+", Realizo Acometida? "+$scope.cumplir.Acometida+", Cambió Infraestructura? "+$scope.cumplir.cambioInfra+", ID Prueba SMPRO(ADSL): "+$scope.cumplir.IdPrueba+", Nombre de quien recibe: "+$scope.cumplir.recibe+",  Código de Autorización Completo: "+$scope.cumplir.codAutoriza;*/

            /*if ($scope.cumplir.SNRDown != "") {
              $scope.observacion=$scope.observacion+" --Parametros HFC-- SNR Down: "+$scope.cumplir.SNRDown+",Potencia Down: "+$scope.cumplir.PotenciaDown+", SNR UP: "+$scope.cumplir.SNRup+", Potencia UP: "+$scope.cumplir.PotenciaUp
            };*/
        }

        if ($scope.OpenModal == "cumplirInstalacionADSL") {

            $scope.observacion = "*PI: " + $scope.cumplir.pruebaintegrada +
                "*Est. OSS: " + $scope.cumplir.oss +
                "*Est. Acce: " + $scope.cumplir.acceso +
                "*Est. CPE: " + $scope.cumplir.cpe +
                "*Est. Plata: " + $scope.cumplir.plataformas;

        }


        if ($scope.OpenModal == "ContingenciaNormal") {
            $scope.observacion = "Reutilizó equipos? " + $scope.contingenciaNormal.reuEquipos + ", Equipo aprovisionado: " + $scope.contingenciaNormal.equipo
        }

        if ($scope.OpenModal == "ContingenciaCambio") {
            var producto = "";
            var puertos = "";
            if ($scope.contingenciaCambio.BA == true) {
                producto = producto + "-BA-";
            }
            if ($scope.contingenciaCambio.ToIP == true) {
                producto = producto + "-ToIP-";
            }
            if ($scope.contingenciaCambio.TV == true) {
                producto = producto + "-TV-";
            }
            ;
            if ($scope.contingenciaCambio.puerto1 == true) {
                puertos = puertos + "-1-";
            }
            if ($scope.contingenciaCambio.puerto2 == true) {
                puertos = puertos + "-2-";
            }
            if ($scope.contingenciaCambio.puerto3 == true) {
                puertos = puertos + "-3-";
            }
            if ($scope.contingenciaCambio.puerto4 == true) {
                puertos = puertos + "-4-";
            }
            ;

            $scope.observacion = "Productos: " + producto + ", CR o Autoriza: " + $scope.contingenciaCambio.autoriza + ", MAC de datos entra: " + $scope.contingenciaCambio.MacdatosEntra + ", MAC de datos sale: " + $scope.contingenciaCambio.MacdatosSale + ", Línea: " + $scope.contingenciaCambio.linea + ", MAC de voz entra: " + $scope.contingenciaCambio.MacvozEntra + ", MAC de voz sale: " + $scope.contingenciaCambio.MacvozSale + ", Deco(s) entra: " + $scope.contingenciaCambio.decoEntra + ", Deco(s) Sale: " + $scope.contingenciaCambio.decoSale + ", Puertos: " + puertos
            console.log($scope.observacion);
        }

        if ($scope.OpenModal == "ContingenciaNuevo") {
            var producto = "";
            if ($scope.contingenciaNuevo.BA == true) {
                producto = producto + "-BA-";
            }
            if ($scope.contingenciaNuevo.ToIP == true) {
                producto = producto + "-ToIP-";
            }
            if ($scope.contingenciaNuevo.TV == true) {
                producto = producto + "-TV-";
            }
            ;

            $scope.observacion = "Productos: " + producto + ", CR o Autoriza: " + $scope.contingenciaNuevo.autoriza + ", Decos: " + $scope.contingenciaNuevo.Decos + ", línea: " + $scope.contingenciaNuevo.linea + ", MAC de datos: " + $scope.contingenciaNuevo.MacDatos + ", MAC de voz: " + $scope.contingenciaNuevo.MacVoz
            console.log($scope.observacion);
        }

        if ($scope.OpenModal == "ContingenciaReuso") {
            var producto = "";
            var puertos = "";
            if ($scope.contingenciaReuso.BA == true) {
                producto = producto + "-BA-";
            }
            if ($scope.contingenciaReuso.ToIP == true) {
                producto = producto + "-ToIP-";
            }
            if ($scope.contingenciaReuso.TV == true) {
                producto = producto + "-TV-";
            }
            ;
            if ($scope.contingenciaReuso.puerto1 == true) {
                puertos = puertos + "-1-";
            }
            if ($scope.contingenciaReuso.puerto2 == true) {
                puertos = puertos + "-2-";
            }
            if ($scope.contingenciaReuso.puerto3 == true) {
                puertos = puertos + "-3-";
            }
            if ($scope.contingenciaReuso.puerto4 == true) {
                puertos = puertos + "-4-";
            }
            ;

            $scope.observacion = "Productos: " + producto + ", CR o Autoriza: " + $scope.contingenciaReuso.autoriza + ", MAC de datos: " + $scope.contingenciaReuso.Macdatos + ", MAC de voz: " + $scope.contingenciaReuso.Macvoz + ", Decos: " + $scope.contingenciaReuso.decos + ", Línea: " + $scope.contingenciaReuso.linea + ", Puertos: " + puertos
        }

        if ($scope.OpenModal == "formaDsam") {
            $scope.observacion = "ID SMNET: " + $scope.DSAM.idSmnet + ", DQI: " + $scope.DSAM.DQI + ", DS SNR: " + $scope.DSAM.SNR + ", CH 2: " + $scope.DSAM.CH2 + ", BER: " + $scope.DSAM.BER + ", POT UP: " + $scope.DSAM.POTUP + ", CH 119: " + $scope.DSAM.CH119 + ", MER: " + $scope.DSAM.MER + ", POTDOWN: " + $scope.DSAM.POTDOWN
        }

        // Inicio Modal OT-T10

        if ($scope.OpenModal == "PendiInstaHFC-OT-T10") {

            if ($scope.gestionmanual.producto == 'HFC-Internet' || $scope.gestionmanual.producto == 'HFC-ToIP' || $scope.gestionmanual.producto == 'HFC-TV_Digital') {

                var CMobsoleto = document.getElementById("CMobsoleto2").value;
                // var msg="Se requiere cambiar el equipo";
                if (CMobsoleto == "Si") {
                    $scope.modal = ""
                    Swal(
                        'No se puede guardar la plantilla!',
                        'Se requiere cambiar el equipo ya que es obsoleto'
                    )
                } else {
                    $scope.modal = "modal"
                }

                var label = [
                    'Señal: ',
                    ', v tap: ',
                    ', Marcación TAP: ',
                    ', Dir TAP:',
                    ', Id pru: ',
                    ', Mac CM: ',
                    ', Técnico:  ',
                    ', Cel: ',
                    ', City: ',
                    ', Id p vecinos: ',
                    '/',
                    '/',
                    ', T Red: ',
                    ', RF-14: ',
                    ' dBm, RF-120: ',
                    ' dBm, RF-135: ',
                    ' dBm, RF-157: ',
                    ' dBm, CH: ',
                    ', CH: ',
                    ', RF 1: ',
                    ' dBm, RF 2: ',
                    ' dBm, MER 1: ',
                    ' dB, MER 2: ',
                    ' dB, BER 1: ',
                    ', BER 2: ',
                    ', P DOWN 1: ',
                    ' dB, P DOWN 2: ',
                    ' dB, RF CH 89: ',
                    ', MER 89: ',
                    ', BER 89: ',
                    ', RF CH 73: ',
                    ' dBm, MER 73: ',
                    ', BER 73: ',
                    ', CH: ',
                    ', RF: ',
                    ' dBm, MER: ',
                    ', BER: ',
                    ', CH: ',
                    ', RF: ',
                    ' dBm, MER: ',
                    ', BER: ',
                    ', # sin enlace en der: ',
                    ', # sin enlace en amp: ',
                    ', # afect en der: ',
                    ', # afect en amp: ',
                    ', # cltes TDR amp: ',
                    ', Img adj PNM CRM: ',
                    ', Img adj falla CRM: ',
                    ', Ruido: ',
                    ', Marquilla: ',
                    ', Nodo/Cmts: ',
                    ', Observaciones: '
                ];

                var value = [
                    $scope.PendiInstaHFCOTT10.repaProvisional,
                    $scope.PendiInstaHFCOTT10.valortab,
                    $scope.PendiInstaHFCOTT10.MarcacionTap,
                    $scope.PendiInstaHFCOTT10.DireccionTap,
                    $scope.PendiInstaHFCOTT10.idPrueba,
                    $scope.PendiInstaHFCOTT10.macEquipo,
                    $scope.tecnico,
                    $scope.PendiInstaHFCOTT10.Celular,
                    $scope.gestionmanual.CIUDAD,
                    $scope.PendiInstaHFCOTT10.idVecinos,
                    $scope.PendiInstaHFCOTT10.idVecinos1,
                    $scope.PendiInstaHFCOTT10.idVecinos2,
                    $scope.PendiInstaHFCOTT10.tipored,
                    $scope.PendiInstaHFCOTT10.RF14,
                    $scope.PendiInstaHFCOTT10.RF120,
                    $scope.PendiInstaHFCOTT10.RF135,
                    $scope.PendiInstaHFCOTT10.RF157,
                    $scope.PendiInstaHFCOTT10.Ch1,
                    $scope.PendiInstaHFCOTT10.Ch2,
                    $scope.PendiInstaHFCOTT10.RF1,
                    $scope.PendiInstaHFCOTT10.RF22,
                    $scope.PendiInstaHFCOTT10.Mer1,
                    $scope.PendiInstaHFCOTT10.Mer2,
                    $scope.PendiInstaHFCOTT10.Ber1,
                    $scope.PendiInstaHFCOTT10.Ber2,
                    $scope.PendiInstaHFCOTT10.PotenciaDW1,
                    $scope.PendiInstaHFCOTT10.PotenciaDW2,
                    $scope.PendiInstaHFCOTT10.RFCH89,
                    $scope.PendiInstaHFCOTT10.Mer89,
                    $scope.PendiInstaHFCOTT10.Ber89,
                    $scope.PendiInstaHFCOTT10.RFCH73,
                    $scope.PendiInstaHFCOTT10.Mer73,
                    $scope.PendiInstaHFCOTT10.Ber73,
                    $scope.PendiInstaHFCOTT10.CHMalo1,
                    $scope.PendiInstaHFCOTT10.RFMalo1,
                    $scope.PendiInstaHFCOTT10.MerMalo1,
                    $scope.PendiInstaHFCOTT10.BerMalo1,
                    $scope.PendiInstaHFCOTT10.CHMalo2,
                    $scope.PendiInstaHFCOTT10.RFMalo2,
                    $scope.PendiInstaHFCOTT10.MerMalo2,
                    $scope.PendiInstaHFCOTT10.BerMalo2,
                    $scope.PendiInstaHFCOTT10.NroCliSinEnlace,
                    $scope.PendiInstaHFCOTT10.NroCliSinAmplificador,
                    $scope.PendiInstaHFCOTT10.NroCliAfecDerivador,
                    $scope.PendiInstaHFCOTT10.NroCliAfecAmplificador,
                    $scope.PendiInstaHFCOTT10.NroCliTDRAmplificador,
                    $scope.PendiInstaHFCOTT10.ImgPnm,
                    $scope.PendiInstaHFCOTT10.IMGFallaSiebel,
                    $scope.PendiInstaHFCOTT10.ProbRuido,
                    $scope.PendiInstaHFCOTT10.InstCorreaPlastica,
                    $scope.PendiInstaHFCOTT10.NodoAAA,
                    $scope.PendiInstaHFCOTT10.observaciones
                ];

                //Se limpia la variable observacion para cada que cierren  y vuelvan a abrir el modal para agregar o quitar información
                $scope.observacion = "";

                //se recorren los array y se concatenan unicamente los que tiene información diligenciada
                if (CMobsoleto != "Si") {
                    for (var i = 0; i < value.length; i += 1) {
                        if (value[i] != undefined) {
                            $scope.observacion += label[i] + value[i];
                        }
                    }
                } else {
                    $scope.observacion = "";
                }

                $scope.observacion = $scope.observacion.replace(/undefined/g, "");
            }
        }
        // Fin Modal OT-T10
    }


    $scope.guardarModalRecogerEq = function (equiposRecoger) {

        if ($scope.gestionmanual.tecnico == "" || $scope.gestionmanual.tecnico == undefined) {
            alert("Por favor ingresar el tecnico.");
            return;
        }

        if ($scope.gestionmanual.producto == "" || $scope.gestionmanual.producto == undefined) {
            alert("Por favor ingresar el producto.");
            return;
        }

        services.recogidaEquipos(equiposRecoger).then(
            function (respuesta) {
                if (respuesta.status == '201' || respuesta.status == '200') {
                    Swal(
                        'Los equipos a recoger fueron guardados!',
                        'Bien Hecho'
                    )
                }
                /*PARA QUE EL MODAL SE OCULTE SOLO*/
                $("#recogerEquipos").modal('hide');
                $scope.equipo = {};
            },
            function errorCallback(response) {
                if (response.status == '400') {
                    Swal({
                        type: 'error',
                        title: 'Oops...',
                        text: 'Este pedido ya está registrado!'
                    })
                }
            }
        );
    }

    $scope.selectProductoHFC = function (producto) {
        if (producto == 'Internet-ToIP') {
            $scope.gestionmanual.producto = 'HFC-Internet';
        } else {
            $scope.gestionmanual.producto = producto;
        }
    }

    $scope.selectProductocontingencia = function (producto) {
        if (producto == 'TV digital 1') {
            $scope.TVdigital1 = true;
            $scope.TVdigital2 = false;
            $scope.TVdigital3 = false;
            $scope.TVdigital4 = false;
            $scope.TVdigital5 = false;
            $scope.TVdigital6 = false;
            $scope.Internet = false;
            $scope.ToIP = false;
        } else if (producto == 'TV digital 2') {
            $scope.TVdigital1 = false;
            $scope.TVdigital2 = true;
            $scope.TVdigital3 = false;
            $scope.TVdigital4 = false;
            $scope.TVdigital5 = false;
            $scope.TVdigital6 = false;
            $scope.Internet = false;
            $scope.ToIP = false;
        } else if (producto == 'TV digital 3') {
            $scope.TVdigital1 = false;
            $scope.TVdigital2 = false;
            $scope.TVdigital3 = true;
            $scope.TVdigital4 = false;
            $scope.TVdigital5 = false;
            $scope.TVdigital6 = false;
            $scope.Internet = false;
            $scope.ToIP = false;
        } else if (producto == 'TV digital 4') {
            $scope.TVdigital1 = false;
            $scope.TVdigital2 = false;
            $scope.TVdigital3 = false;
            $scope.TVdigital4 = true;
            $scope.TVdigital5 = false;
            $scope.TVdigital6 = false;
            $scope.Internet = false;
            $scope.ToIP = false;
        } else if (producto == 'TV digital 5') {
            $scope.TVdigital1 = false;
            $scope.TVdigital2 = false;
            $scope.TVdigital3 = false;
            $scope.TVdigital4 = false;
            $scope.TVdigital5 = true;
            $scope.TVdigital6 = false;
            $scope.Internet = false;
            $scope.ToIP = false;
        } else if (producto == 'TV digital 6') {
            $scope.TVdigital1 = false;
            $scope.TVdigital2 = false;
            $scope.TVdigital3 = false;
            $scope.TVdigital4 = false;
            $scope.TVdigital5 = false;
            $scope.TVdigital6 = true;
            $scope.Internet = false;
            $scope.ToIP = false;
        } else if (producto == 'Internet') {
            $scope.TVdigital1 = false;
            $scope.TVdigital2 = false;
            $scope.TVdigital3 = false;
            $scope.TVdigital4 = false;
            $scope.TVdigital5 = false;
            $scope.TVdigital6 = false;
            $scope.Internet = true;
            $scope.ToIP = false;
        } else if (producto == 'ToIP') {
            $scope.TVdigital1 = false;
            $scope.TVdigital2 = false;
            $scope.TVdigital3 = false;
            $scope.TVdigital4 = false;
            $scope.TVdigital5 = false;
            $scope.TVdigital6 = false;
            $scope.Internet = false;
            $scope.ToIP = true;
        }
    }

    $scope.BuscarPedido = function (pedido) {
        $scope.plantillaReparaciones = 0;
        $scope.iniciaGestion = false;
        $scope.errorconexion1 = false;
        $scope.gestionmanual = {};
        $scope.pedido = pedido;
        $scope.gestionmanual.producto = "";
        $scope.collapse = 0;
        $scope.counter = 0;

        $scope.startCounter = function () {
            if (timer === null) {
                updateCounter();
            }
        };
        var updateCounter = function () {
            $scope.counter++;
            timer = $timeout(updateCounter, 1000);
        };
        updateCounter();

        if (pedido == "" || pedido == undefined) {
            alert("Ingrese un pedido para buscar");
            $scope.sininfopedido = false;
            return;
        } else {
            /*else{
                $scope.infopedido=false;
                $scope.errorconexion1=false;
                $scope.myWelcome={};
            }*/

            $scope.sininfopedido = true;
            $scope.url = "http://" + $scope.ipServer + ":8080/HCHV/Buscar/" + pedido;
            $http.get($scope.url, {timeout: 2000})
                .then(function (data) {
                        $scope.myWelcome = data.data;
                        if ($scope.myWelcome.pEDIDO_UNE == null) {
                            $scope.infopedido = false;
                            $scope.errorconexion1 = false;
                            $scope.myWelcome = {};
                        } else if ($scope.myWelcome.engineerID == null) {
                            $scope.infopedido = false;
                            $scope.errorconexion1 = false;
                            $scope.myWelcome = {};
                        } else if ($scope.myWelcome.pEDIDO_UNE == "TIMEOUT") {
                            $scope.infopedido = false;
                            $scope.errorconexion1 = true;
                            $scope.myWelcome = {};
                            $scope.errorconexion = "No hay conexión con Click, ingrese datos manualmente";
                        } else {
                            $scope.infopedido = true;
                            $scope.gestionmanual.tecnico = $scope.myWelcome.engineerID;
                            $scope.gestionmanual.CIUDAD = $scope.myWelcome.uNEMunicipio.toUpperCase();
                            $scope.BuscarTecnico();
                        }
                        ;
                        return data.data;
                    },

                    function (err) {
                        $scope.ipServer = "10.100.66.254";

                        /*
                        * REINTENTO CON EL NOMBRE DE MAQUINA DE
                        * */

                        $scope.url = "http://" + $scope.ipServer + ":8080/HCHV/Buscar/" + pedido;
                        $http.get($scope.url, {timeout: 2000})
                            .then(function (data) {
                                $scope.myWelcome = data.data;
                                if ($scope.myWelcome.pEDIDO_UNE == null) {
                                    $scope.infopedido = false;
                                    $scope.errorconexion1 = false;
                                    $scope.myWelcome = {};
                                } else if ($scope.myWelcome.pEDIDO_UNE == "TIMEOUT") {
                                    $scope.infopedido = false;
                                    $scope.errorconexion1 = true;
                                    $scope.myWelcome = {};
                                    $scope.errorconexion = "No hay conexión con Click, ingrese datos manualmente";
                                } else {
                                    $scope.infopedido = true;
                                    $scope.gestionmanual.tecnico = $scope.myWelcome.engineerID;
                                    $scope.gestionmanual.CIUDAD = $scope.myWelcome.uNEMunicipio.toUpperCase();
                                    $scope.BuscarTecnico();
                                }
                                ;
                                return data.data;
                            }, function (err) {
                                console.log("ERROR DE CONEXION: NO PUEDO ALCANZAR EL SERVIDOR!!!");
                                $scope.infopedido = false;
                                $scope.errorconexion1 = true;
                                $scope.myWelcome = {};
                                $scope.errorconexion = "No hay conexión con Web Service, ingrese datos manualmente";
                            });
                        /**
                         console.log("ERROR DE CONEXION: NO PUEDO ALCANZAR EL SERVIDOR!!!");
                         $scope.infopedido=false;
                         $scope.errorconexion1=true;
                         $scope.myWelcome={};
                         $scope.errorconexion="No hay conexión con Web Service, ingrese datos manualmente";
                         **/
                    },
                    function errorCallback(response) {
                        console.log("ERRORRRR");
                    }
                );

        }
        ;
    };

    $scope.BuscarTecnico = function () {
        var concepto = "identificacion";
        var pagina = undefined;

        //alert("t1: "+$scope.gestionmanual.tecnico+", t2: "+$scope.myWelcome.engineerID);
        if ($scope.gestionmanual.tecnico == undefined || $scope.gestionmanual.tecnico == "") {
            $scope.gestionmanual.tecnico = $scope.myWelcome.engineerID;
        }

        if ($scope.gestionmanual.tecnico == undefined || $scope.gestionmanual.tecnico == "") {
            return;
        }

        services.listadoTecnicos(pagina, concepto, $scope.gestionmanual.tecnico).then(
            function (data) {
                $scope.Tecnico = data.data[0];
                $scope.tecnico = $scope.Tecnico[0].NOMBRE;
                $scope.empresa = $scope.Tecnico[0].NOM_EMPRESA;
                if ($scope.Tecnico[0].CELULAR == "") {
                    alert("El número de celular del tecnico no existe!");
                    $scope.celular = "0000000000";
                } else {
                    $scope.celular = $scope.Tecnico[0].CELULAR;
                }
                //   console.log($scope.Tecnico[0]);
                $scope.creaTecnico = true;
                return;
            },
            function errorCallback(response) {
                //$scope.errorDatos=concepto+" "+$scope.gestionmanual.tecnico+" no existe.";
                $('#NuevoTecnico').modal('show');
                $scope.creaTecnico = false;
            }
        );
    };

    $scope.createTecnico = function () {
        // console.log($scope.gestionmanual.tecnico);
        services.creaTecnico($scope.crearTecnico, $scope.gestionmanual.tecnico).then(
            function (data) {
                // console.log("la lista "+$scope.listaUsuarios);
                $scope.BuscarTecnico();
                return data.data;
                // $scope.creaTecnico=true;
            },
            function errorCallback(response) {
                // console.log($scope.errorDatos);
            }
        );
    };


    $scope.guardarPedido = function () {

        console.log($scope.gestionmanual.accion, $scope.gestionmanual.subAccion);

        if ($scope.gestionmanual.interaccion == "" || $scope.gestionmanual.interaccion == undefined) {
            alert("Debe seleccionar el tipo de interacción.");
            return;
        }

        if (($scope.gestionmanual.interaccion == "llamada" && $scope.gestionmanual.id_llamada == "") || ($scope.gestionmanual.interaccion == "llamada" && $scope.gestionmanual.id_llamada == undefined)) {
            alert("Por favor ingresar el ID de llamada.");
            return;
        }

        if ($scope.gestionmanual.interaccion == "llamada" && $scope.gestionmanual.id_llamada.length > 40) {
            alert("Por favor ingrese un Id de Llamada válido.");
            return;
        }

        /* if ($scope.gestionmanual.interaccion == "llamada" ) {
            valor = $scope.gestionmanual.id_llamada;
            if( isNaN(valor) ) {
                alert("Por favor ingrese un Id de Llamada válido.");
                return;
            }
        } */

        if ($scope.gestionmanual.tecnico == "" || $scope.gestionmanual.tecnico == undefined) {
            alert("Por favor ingresar el tecnico.");
            return;
        }

        if ($scope.gestionmanual.producto == "" || $scope.gestionmanual.producto == undefined) {
            alert("Por favor ingresar un producto.");
            return;
        }

        if ($scope.gestionmanual.proceso == "" || $scope.gestionmanual.proceso == undefined) {
            alert("Por favor ingresar el proceso.");
            return;
        }

        if ($scope.gestionmanual.accion == "" || $scope.gestionmanual.accion == undefined) {
            alert("Por favor ingresar una accion.");
            return;
        }

        if ($scope.gestionmanual.accion == "Soporte GPON" && ($scope.gestionmanual.subAccion == "" || $scope.gestionmanual.subAccion == undefined)) {
            alert("Por favor ingresar una subaccion.");
            return;
        }

        if ($scope.gestionmanual.proceso == "Reparaciones" && $scope.gestionmanual.accion == "Cambio Equipo") {
            if ($scope.gestionmanual.macEntra == "" || $scope.gestionmanual.macEntra == undefined || $scope.gestionmanual.macSale == "" || $scope.gestionmanual.macSale == undefined) {
                alert("Debes de ingresar al menos una MAC Entra y una MAC Sale.");
                return;
            }
        }

        // 22-05-2017: quito esta forma de guardar el pedido ya que genera un doble click al momento de guardar...
        // if ($scope.creaTecnico==true) {

        $timeout.cancel(timer);
        timer = null;

        var hours = Math.floor($scope.counter / 3600),
            minutes = Math.floor(($scope.counter % 3600) / 60),
            seconds = Math.floor($scope.counter % 60);

        if (hours < 10) {
            hours = "0" + hours;
        }
        ;
        if (minutes < 10) {
            minutes = "0" + minutes;
        }
        ;
        if (seconds < 10) {
            seconds = "0" + seconds;
        }
        ;

        $scope.counter = hours + ":" + minutes + ":" + seconds;

        // console.log("id cambio equipo1: ",$scope.datoscambioEquipo);

        /*SE QUITA datoscambioEquipo POR LA OBSERVAION QUE SE HIZO EN LA LINEA 148*/
        services.ingresarPedidoAsesor(
            $scope.gestionmanual,
            $scope.pedido,
            $scope.empresa,
            $scope.counter,
            $rootScope.galletainfo,
            $scope.myWelcome,
            $scope.observacion,
            $scope.datoscambioEquipo = 0
        ).then(
            function (data) {
                //console.log('data: ', data);
                //if ($scope.creaTecnico==true) {
                //
                $route.reload();
                //location.reload();
                //};
            },
            function errorCallback(response) {
                $scope.errorDatos = "Registros no fue ingresado.";
                //alert($scope.errorDatos);
                //console.log('errorDatos: ', $scope.errorDatos);
            }
        );

        //console.log("ingresarPedidoAsesor: ", services.ingresarPedidoAsesor);
        //console.log("datospedido: ",$scope.gestionmanual);
        //console.log("pedido: ",$scope.pedido);
        //console.log("datosLogin: ",$scope.galletainfo);
        //console.log("datosClick: ",$scope.myWelcome);
        //console.log("plantilla: ",$scope.observacion);
        // console.log("id cambio equipo2: ",$scope.datoscambioEquipo);


        /* }else{
            $scope.BuscarTecnico();
        }*/

    };

    $scope.limpiar = function () {
        location.reload();
    }

    $scope.procesos();
});

/* ---------------------------------------------------------------------------------------------- */
/*                            CONTROLADOR PARA PREMISASINFRAESTRUCTURA                            */
/* ---------------------------------------------------------------------------------------------- */

app.controller('premisasInfraestructurasCtrl', function ($scope, $rootScope, $location, $route, $routeParams, $cookies, $cookieStore, $timeout, services, fileUpload) {

    $scope.isInfraestructureFromField = false;
    $scope.isInfraestructureFromIntranet = false;
    $scope.isLoadingData = true;
    $scope.dataEscalamientoInfraestructura = [];
    $scope.dataEscalamientoInfraestructuraPrioridad2 = [];

    var database = firebase.firestore();

    $scope.listarescalamientosinfraestructura = () => {

        database.collection("infraestructure").where("status", "==", 0).orderBy("dateCreated", "asc").get().then((querySnapshot) => {
            $scope.isInfraestructureFromField = false;
            $scope.listaEscalamientosInfraestructura = [];
            querySnapshot.forEach((doc) => {
                let dataQuerySnapshot = {};
                dataQuerySnapshot = {
                    _id: doc.id,
                    addressTap: doc.data().addressTap,
                    observaciones: doc.data().comments,
                    dateCreated: doc.data().dateCreated,
                    correa_marcacion: doc.data().isMarkInstalledSif,
                    isPhoto: doc.data().isPhoto,
                    isSmnetTestSif: doc.data().isSmnetTestSif,
                    mac_real_cpe: doc.data().macRealCPE,
                    markTap: doc.data().markTap,
                    netType: doc.data().netType,
                    proceso: doc.data().process,
                    producto: doc.data().product,
                    motivo: doc.data().subject,
                    status: doc.data().status,
                    pedido: doc.data().task,
                    user_ID: doc.data().user_ID,
                    user_identification: doc.data().user_identification,
                    vTap: doc.data().vTap,
                    informacion_adicional: doc.data().concatData
                }

                var date = dataQuerySnapshot.dateCreated.toDate();
                var year = date.getFullYear();
                var month = date.getMonth();
                var day = "0" + date.getDate();
                var hours = "0" + date.getHours();
                var minutes = "0" + date.getMinutes();
                var seconds = "0" + date.getSeconds();

                var formattedTime = year + '-' + (month + 1) + '-' + day.substr(-2) + ' ' + hours.substr(-2) + ':' + minutes.substr(-2) + ':' + seconds.substr(-2);

                dataQuerySnapshot.fecha_solicitud = formattedTime;
                $scope.listaEscalamientosInfraestructura.push(dataQuerySnapshot);
            });

            if ($scope.flagOnlyPSData) {
                $scope.dataEscalamientoInfraestructura = $scope.listaEscalamientosInfraestructura.concat($scope.dataGestionEscalamiento);
                $scope.isLoadingData = false;
            }
        }).catch((err) => {
            console.log(err);
        });

    }

    $scope.gestionescalamiento = () => {
        isLoadingData = true;
        $scope.flagOnlyPSData = false;
        $scope.dataGestionEscalamiento = [];

        services.datosgestionescalamientos().then(function (data) {
            $scope.listarescalamientosinfraestructura();
            $scope.dataGestionEscalamiento = data.data[0];
            if ($scope.listaEscalamientosInfraestructura) {
                if ($scope.listaEscalamientosInfraestructura.length > 0) {
                    $scope.dataEscalamientoInfraestructura = $scope.listaEscalamientosInfraestructura.concat($scope.dataGestionEscalamiento);
                    $scope.isLoadingData = false;
                } else {
                    $scope.flagOnlyPSData = true;
                }
            } else {
                $scope.flagOnlyPSData = true;
            }
        }).catch((err) => {
            $scope.listarescalamientosinfraestructura();
            console.log(err)
        });

        services.datosgestionescalamientosprioridad2().then(function (data) {
            $scope.dataEscalamientoInfraestructuraPrioridad2 = data.data[0];
            console.log($scope.dataEscalamientoInfraestructuraPrioridad2);
        }).catch((err) => {
            console.log(err)
        });
    }

    $scope.exportarRegistros = () => {
        services.exportEscalamientos().then((res) => {
            var data = res.data[0];
            console.log(data);
            var array = typeof data != 'object' ? JSON.parse(data) : data;
            var str = '';
            var column = `ID, Pedido, Tarea, Tecnico, ID Tecnico, Fecha Solicitud, Fecha Gestion, Fecha Respuesta, Login Gestion, En Gestion, Proceso, Producto, Motivo, Area, Region, Tipo Tarea, Tecnologia, CRM, Departamento, Prueba SMNET, Foto?, Marcacion TAP, Direccion TAP, Valor TAP, Informacion Adicional, MAC Real CPE, Correa Marcacion, Observacion, Respuesta, ID Terreno, Tipificacion, Estado, ANS \r\n`;
            str += column;
            for (var i = 0; i < array.length; i++) {
                var line = '';
                for (var index in array[i]) {
                    if (line != '') line += ','
                    line += array[i][index];
                }

                str += line + '\r\n';
            }
            var dateCsv = new Date();
            var yearCsv = dateCsv.getFullYear();
            var monthCsv = (dateCsv.getMonth() + 1 <= 9) ? '0' + (dateCsv.getMonth() + 1) : (dateCsv.getMonth() + 1);
            var dayCsv = (dateCsv.getDate() <= 9) ? '0' + dateCsv.getDate() : dateCsv.getDate();
            var fullDateCsv = yearCsv + "-" + monthCsv + "-" + dayCsv;


            var blob = new Blob([str]);
            var elementToClick = window.document.createElement("a");
            elementToClick.href = window.URL.createObjectURL(blob, {type: 'text/csv'});
            elementToClick.download = "Escalamientos-" + fullDateCsv + ".csv";
            elementToClick.click();
            console.log(str);
        });
    }


    $scope.mostrarModalConcatenacion = (data) => {
        data = data.replaceAll("||", "<br>");
        Swal({
            type: 'info',
            title: 'Información Adicional',
            html: '<div style="text-align:justify;font-size: 12px;">' + data + '</div>',
            footer: 'Puedes copiar esta información si lo deseas'
        });
    }

    $scope.mostrarModalEscalamiento = function (data) {
        //console.log("guardarcontingencia: ",data);
        if (data.engestion == null || data.engestion == '0') {
            alert("Debes bloquear el pedido");
            return;
        } else if (data.tipificacion == undefined || data.tipificacion == '') {
            alert("Recuerda seleccionar todas las opciones!!");
            return;
        } else {
            $scope.gestionescala = data;
            //console.log("gestioncontin: ",$scope.gestioncontin);
            $('#editarModal').modal('show');
            return data.data;
        }
    }

    $scope.marcarEngestionEscalamiento = async (data) => {
        if (data._id) {
            try {
                await $scope.autocompletarEscalamiento(data);
            } catch (error) {
                return swal({
                    title: "Aviso Importante: ",
                    html: "El pedido no fue desbloqueado.",
                    type: "error",
                });
            }
        } else {
            services.marcarengestionescalamiento(data, $rootScope.galletainfo).then(function (data) {

                if (data.data !== "") {
                    if (data.data[0] == "desbloqueado") {
                        $scope.respuestaMarca = data.data[0][0];
                        $scope.gestionescalamiento();
                        alert("Pedido desbloqueado!!");
                        return;
                    } else {
                        $scope.respuestaMarca = data.data[0][0];
                        $scope.gestionescalamiento();
                        alert("El pedido se encuentra bloqueado.");
                        return;
                    }
                } else if (data.data == "") {
                    $scope.respuestaMarca = "";
                    $scope.gestionescalamiento();
                    alert("Pedido bloqueado!!!");
                    return;
                }
            })
                .catch(err => console.log(err));
        }
    }

    $scope.autocompletarEscalamiento = async (data) => {
        try {
            var autocompleteQuery = await fetch('http://10.100.66.254:8080/HCHV_DEV/Buscar/' + data.pedido);
            var autocompleteData = await autocompleteQuery.json();
            data.engineerID = autocompleteData.engineerID;
            data.engineer = autocompleteData.engineerName;
            data.dateCreated = data.dateCreated.toDate();
            data.area = autocompleteData.Area;
            data.taskType = autocompleteData.TaskType;
            data.region = autocompleteData.Region;
            data.task = autocompleteData.tAREA_ID;
            data.tech = autocompleteData.uNETecnologias;
            data.department = autocompleteData.uNEDepartamento;
            data.status = autocompleteData.Estado;
            data.crm = autocompleteData.Crm;
            data.ans = null;

            if (autocompleteData.TaskType == 'Reparacion') {
                var ansQuery = await fetch('http://10.100.66.254:7771/api/ans/' + data.pedido);
                var ansData = await ansQuery.json();
                data.ans = ansData.horas;
            }
            console.log(data);

            if (data.status == "" || data.status == undefined || data.status == null) {
                swal({
                    title: "El Pedido Debe Cancelarse: ",
                    html: `El pedido ${data.pedido} que ha seleccionado, no existe en click.`,
                    type: "warning"
                });
            } else {
                if (data.status != "En Sitio") {
                    swal({
                        title: "El Pedido Debe Cancelarse: ",
                        html: `El pedido ${data.pedido} que ha seleccionado, no se encuentra en sitio, proceda a cancelarlo.`,
                        type: "warning"
                    });
                }

                if (data.tech != "HFC") {
                    swal({
                        title: "El Pedido Debe Cancelarse: ",
                        html: `El pedido ${data.pedido} que ha seleccionado, no es de tecnología HFC.`,
                        type: "warning"
                    });
                }
            }
            var queryIsAlreadyToken = database.collection("infraestructure").doc(data._id);
            var querySnapshotAT = await queryIsAlreadyToken.get();
            if (querySnapshotAT.data().status == 1) {
                swal({
                    title: "Este pedido ya ha sido tomado: ",
                    html: `El pedido ${data.pedido} que ha seleccionado, ya ha sido tomado.`,
                    type: "warning"
                });
            } else {
                console.log(querySnapshotAT.data());
                // var queryUpdateStatus = await database.collection("infraestructure").doc(data._id).update({status: 1});
                await fetch('https://autogestionterreno.com.co/api/state-infraestructure/', {
                    method: 'PUT',
                    mode: 'cors',
                    body: JSON.stringify({infraestructure_ID: data._id}),
                    headers: {"Content-type": "application/json;charset=UTF-8"}
                });

                var querySaveScale = await services.guardarEscalamiento(data, $rootScope.galletainfo);
                $scope.gestionescalamiento();
            }

        } catch (error) {
            swal({
                title: "Información Pedido: ",
                html: "No encontrado",
                type: "warning"
            });
            console.log(error);
            return;
        }

    }

    $scope.guardarescalamiento = async function (data) {
        if (data.login_gestion == null) {
            alert("Debes de marcar la solicitud, antes de guardar!");
        } else {
            if (!data.observacionesescalamiento) {
                alert("Debes ingresar las observaciones.");
                return;
            } else {
                try {
                    alert("Pedido guardado, recuerda actualizar!!");
                    console.log(data);
                    var currentTimeDate = new Date().toLocaleString();
                    var statusInfraestructure = (data.tipificacion == "Escalamiento realizado ok" || data.tipificacion == "Escalamiento ok nivel 2" || data.tipificacion == "Escalamiento ok nivel 2 Prioridad") ? "Aprobado" : "Rechazado";

                    await fetch('https://autogestionterreno.com.co/api/update-infraestructure/', {
                        method: 'PUT',
                        mode: 'cors',
                        body: JSON.stringify({infraestructure_ID: data.id_terreno, infraestructure_Status: statusInfraestructure, dateAswered: currentTimeDate}),
                        headers: {"Content-type": "application/json;charset=UTF-8"}
                    });

                    //   database.collection("infraestructure").doc(data.id_terreno).update({
                    //     answer: data.observacionesescalamiento,
                    //     dateAswered: currentTimeDate,
                    //     infraestructure_Status: statusInfraestructure
                    //   });

                    services.editarregistroescalamiento(data, $rootScope.galletainfo)
                        .then(function (data) {

                        })
                        .catch(err => alert(err));
                    $scope.gestionescalamiento();
                    //$scope.gestioncontingenciasPrueba();
                } catch (error) {
                    swal({
                        title: "Hay problemas al almacenar la gestión ",
                        html: "Consulte con desarrollo para más información",
                        type: "warning"
                    });
                }
            }
        }
    }

    $scope.CopyPortaPapelesEscalamientoInfraestructura = (data) => {
        var copyTextEI = document.createElement("input");
        copyTextEI.value = data;
        document.body.appendChild(copyTextEI);
        copyTextEI.select();
        document.execCommand("copy");
        document.body.removeChild(copyTextEI);
        Swal({
            type: 'info',
            title: 'Aviso',
            text: "El texto seleccionado fue copiado"
        });
    }

    $scope.gestionescalamiento();

});

/*====================================================================================================*/
/*====================================================================================================*/
/*------------------------>FIN DEL BLOQUE PARA VISITAS EN CONJUNTO<-----------------------------------*/
/*====================================================================================================*/
/*====================================================================================================*/

// =========================================================

/*CONTROLADOR PARA NOVEDADES VISITAS*/

// =========================================================

app.controller('novedadesVisitaCtrl', function ($scope, $http, $rootScope, $location, $route, $routeParams, $cookies, $timeout, services, cargaRegistros) {

    /*INCLUDE DE LOS MODULOS*/
    $scope.novedadesTecnico = "partial/novedadesVisita/novedadesTecnico.html";

    /*INCLUDE DE LOS MODALES*/
    $scope.mostarModalNovedadesVisitas = "partial/novedadesVisita/modal/modalNovedadVisitas.html";

    $scope.novedadesVisitasTecnicos = {};
    $scope.Registros = {};
    $scope.novedadesVisitasSel = {};
    $scope.observacionCCO = '';
    $scope.pedidoElegido = '';

    $scope.pageChanged = function () {
        $scope.RegistrosTecnicos($scope.datapendientes.currentPage);
    }

    //=====================================================================
    /*SUBIR LA DATA A LA VISTA DEL USUARIO DE NOVEDAES DE TECNICOS*/
    //=====================================================================

    $scope.RegistrosTecnicos = function (datos) {
        $scope.novedadesVisitasTecnicos = {};

        services.novedadesTecnicoService($scope.datapendientes.currentPage, datos).then(
            function (data) {

                //console.log('novedadesTecnicoService: ',data);

                $scope.novedadesVisitasTecnicos = data.data.data;
                $scope.cantidad = data.data.data.length;
                $scope.counter = data.data.contador;

                return data.data;
            },
            function errorCallback(response) {
                $scope.errorDatos = "No hay datos";
            });
    }

    $scope.maxSize = 4;
    $scope.datapendientes = {maxSize: 4, currentPage: 1, numPerPage: 100, totalItems: 0};
    $scope.RegistrosTecnicos($scope.datapendientes.currentPage);

    // =================================================
    //                 MOSTRAR MODAL
    // =================================================

    $scope.mostraModal = function (registrosTenicos) {
        //console.log("registrosTenicos: ",registrosTenicos);
        angular.copy(registrosTenicos, $scope.novedadesVisitasSel);

        $("#modalNovedadVisita").modal();
    }

    $scope.abrirAgregarObservacion = (pedido) => {
        $("#novedadesVisitaObservacion").modal();
        $scope.pedidoElegido = pedido;
    };

    $scope.agregarObservacion = (observacionCCO) => {

        services.updateNovedadesTecnico(observacionCCO, $scope.pedidoElegido)
            .then(res => {
                console.log(res);
                Swal(
                    'Tu Novedad fue Actualizada!',
                    'Bien Hecho'
                )
                $('#observacionCCO').val('');
                $scope.pedidoElegido = '';
                $scope.observacionCCO = '';
            })
            .catch(err => {
                console.log(err);
                Swal(
                    'Tu Novedad tuvo un Error!',
                    'Vuelve a intentar'
                )
                $('#observacionCCO').val('');
                $scope.pedidoElegido = '';
                $scope.observacionCCO = '';
            });

    };

    // =================================================
    //      FUNCION PARA CAMBIAR CAMPOS DE NOVEDADES DINAMICAMENTE
    // =================================================

    $scope.refrescarCamposNovedadesMotivos = () => {
        if ($scope.novedadesVisitasSel.situaciontriangulo == 'No cumple políticas de tiempos') {
            $scope.optionsMotivo = ["Respuesta mesas de soporte", "Retrasos premisas", "Problemas en las plataformas", "Fallas fisicas en la red"];
        }
        if ($scope.novedadesVisitasSel.situaciontriangulo == 'Malos procedimientos') {
            $scope.optionsMotivo = ["Logísticos", "Conocimiento"];
        }
        if ($scope.novedadesVisitasSel.situaciontriangulo == 'Riesgo incumplimiento AM' || $scope.novedadesVisitasSel.situaciontriangulo == 'Riesgo incumplimiento PM') {
            $scope.optionsMotivo = ["Capacidad operativa", "Novedades Click"];
        }

    }

    $scope.refrescarCamposNovedadesSubmotivos = () => {
        if ($scope.novedadesVisitasSel.motivotriangulo == 'Respuesta mesas de soporte') {
            $scope.optionsSubmotivo = ["Infraestructura AAA", "Linea GPON", "Linea Asignaciones", "Linea Bloqueo y desbloqueo", "Brutal Force", "Contingencias"];
        }
        if ($scope.novedadesVisitasSel.motivotriangulo == 'Retrasos premisas') {
            $scope.optionsSubmotivo = ["demora de escalera", "distancia de la instalación", "en actividades en bodega", "demora auxiliar de distribuidor", "técnico sin materiales", "técnico sin herramienta", "demoras por ubicación", "demoras en aprovisionamiento de equipos", "problemas dispositivo móvil o señal", "técnico en espera de apoyo de supervisor", "técnico sin equipos", "técnico y supervisor no contestan"];
        }
        if ($scope.novedadesVisitasSel.motivotriangulo == 'Problemas en las plataformas') {
            $scope.optionsSubmotivo = [];
        }
        if ($scope.novedadesVisitasSel.motivotriangulo == 'Fallas fisicas en la red') {
            $scope.optionsSubmotivo = [];
        }
        if ($scope.novedadesVisitasSel.motivotriangulo == 'Logísticos') {
            $scope.optionsSubmotivo = ["técnico no marca estado", "técnico no finaliza", "técnico no está en sitio", "abandono de pedido", "no sigue ruta asignada"];
        }
        if ($scope.novedadesVisitasSel.motivotriangulo == 'Conocimiento') {
            $scope.optionsSubmotivo = ["Mal aprovisionamiento", "No usó el bot Sara", "No adjuntó fotos requeridas", "Mal uso de click mobile"];
        }
        if ($scope.novedadesVisitasSel.motivotriangulo == 'Capacidad operativa') {
            $scope.optionsSubmotivo = ["Sin tecnicos con la habilidad", "Supervisor no aprueba desplazamiento", "Supervisor garantiza visita"];
        }
        if ($scope.novedadesVisitasSel.motivotriangulo == 'Novedades Click') {
            $scope.optionsSubmotivo = ["Cargado tarde", "Microzona errada"];
        }
    }

    // =================================================
    //      FUNCION PARA SUBIR REGIONES Y MUNICIPIOS
    // =================================================

    $scope.regiones = function () {
        $scope.validaraccion = false;
        services.getRegiones().then(function (res) {
            //console.log("respuestaRegiones: ",res);
            $scope.listadoRegiones = res.data[0];
            $scope.listadoMunicipios = {};
            //return data.data;

        });
    };

    $scope.calcularAcciones = function () {
        $scope.listadoMunicipios = {};
        services.getMunicipios($scope.novedadesVisitasSel.region).then(function (data) {
            //console.log('peticionMunicipios: ', data);
            $scope.listadoMunicipios = data.data[0];
            $scope.validaraccion = true;
        });
    };

    // =================================================
    //      FUNCION PARA SUBIR SITUACION Y DETALLE
    // =================================================

    $scope.situacion = function () {
        $scope.validaraccion = false;
        services.getSituacion().then(function (res) {
            console.log("respuestaRegiones: ", res);
            $scope.listadoSituacion = res.data[0];
            $scope.listadoDetalle = {};
            //return data.data;

        });
    };

    $scope.calcularDetalle = function () {
        $scope.listadoDetalle = {};
        services.getDetalle($scope.novedadesVisitasSel.situacion).then(function (data) {
            console.log('peticionMunicipios: ', data);
            $scope.listadoDetalle = data.data[0];
            $scope.validaraccion = true;
        });
    };

    // =================================================================================================================
    //        INICIO BUSCAR LOS DATOS DEL PEDIDO PARA LA NOVEDAD
    // =================================================================================================================


    $scope.BuscarPedidoNovedad = function (pedido) {

        $scope.errorconexion1 = false;
        $scope.gestionmanual = {};
        $scope.pedido = pedido;
        $scope.gestionmanual.producto = "";

        if (pedido == "" || pedido == undefined) {
            alert("Ingrese un pedido para buscar");
            $scope.sininfopedido = false;
            return;
        } else {
            $scope.ipServer = "10.100.66.254";
            $scope.sininfopedido = true;
            $scope.url = "http://" + $scope.ipServer + ":8080/HCHV/Buscar/" + pedido;
            $http.get($scope.url, {timeout: 2000})
                .then(function (data) {
                    $scope.myWelcome = data.data;
                    if ($scope.myWelcome.pEDIDO_UNE == null) {
                        $scope.infopedido = false;
                        $scope.errorconexion1 = false;
                        $scope.myWelcome = {};
                    } else if ($scope.myWelcome.pEDIDO_UNE == "TIMEOUT") {
                        $scope.infopedido = false;
                        $scope.errorconexion1 = true;
                        $scope.myWelcome = {};
                        $scope.errorconexion = "No hay conexión con Click, ingrese datos manualmente";
                    } else {
                        $scope.infopedido = true;
                        $scope.novedadesVisitasSel.contrato2 = $scope.myWelcome.uNEProvisioner;
                        $scope.novedadesVisitasSel.cedulaTecnico2 = $scope.myWelcome.engineerID;
                        $scope.novedadesVisitasSel.nombreTecnico2 = $scope.myWelcome.engineerName;
                        $scope.novedadesVisitasSel.proceso2 = $scope.myWelcome.engineer_Type;
                        $scope.novedadesVisitasSel.municipio2 = $scope.myWelcome.uNEMunicipio;
                    }
                    ;
                    return data.data;
                }, function (err) {
                    $scope.ipServer = "10.100.66.254";

                    //REINTENTO CON EL NOMBRE DE LA MAQUINA

                    $scope.url = "http://" + $scope.ipServer + ":8080/HCHV/Buscar/" + pedido;
                    $http.get($scope.url, {timeout: 2000})
                        .then(function (data) {
                            $scope.myWelcome = data.data;
                            if ($scope.myWelcome.pEDIDO_UNE == null) {
                                $scope.infopedido = false;
                                $scope.errorconexion1 = false;
                                $scope.myWelcome = {};
                            } else if ($scope.myWelcome.pEDIDO_UNE == "TIMEOUT") {
                                $scope.infopedido = false;
                                $scope.errorconexion1 = true;
                                $scope.myWelcome = {};
                                $scope.errorconexion = "No hay conexión con Click, ingrese los datos manualmente";
                            } else {
                                $scope.infopedido = true;
                                $scope.novedadesVisitasSel.contrato2 = $scope.myWelcome.uNEProvisioner;
                                $scope.novedadesVisitasSel.cedulaTecnico2 = $scope.myWelcome.engineerID;
                                $scope.novedadesVisitasSel.nombreTecnico2 = $scope.myWelcome.engineerName;
                                $scope.novedadesVisitasSel.proceso2 = $scope.myWelcome.engineer_Type;
                                $scope.novedadesVisitasSel.municipio2 = $scope.myWelcome.uNEMunicipio;
                            }
                            ;
                            return data.data;
                        }, function (err) {
                            $scope.infopedido = false;
                            $scope.errorconexion1 = true;
                            $scope.myWelcome = {};
                            $scope.errorconexion = "No hay conexión con el Web Service, ingrese los datos manualmente";
                        });

                }, function errorCallback(response) {

                    // console.log("ERRORRRR");
                });

        }
        ;

    };
    //==================================================================================================================
    //         FIN BUSCAR LOS DATOS DEL PEDIDO PARA LA NOVEDAD
    //==================================================================================================================

    // =================================================
    //                 FUNCION PARA GUARDAR
    // =================================================

    $scope.guardar = function (registrosTenicos, frmNovedadVisita) {

        services.guardarNovedadesTecnico(registrosTenicos, $rootScope.galletainfo).then(
            function (respuesta) {
                //console.log("guardar: ",respuesta);
                if (respuesta.status == '201') {
                    Swal(
                        'Tu Novedad fue Guardada!',
                        'Bien Hecho'
                    )
                }

                /*PARA QUE EL MODAL SE OCULTE SOLO*/
                $("#modalNovedadVisita").modal('hide');
                $scope.novedadesVisitasSel = {};

                /*LIMPIEZA DEL MODAL*/
                frmNovedadVisita.autoValidateFormOptions.resetForm();

            }, function errorCallback(response) {

                if (response.status == '400') {

                    Swal({
                        type: 'error',
                        title: 'Oops...',
                        text: 'El formulario no se guardó',
                        footer: '¡Intenta de nuevo o reporta con el administrador!'
                    })
                }

            });
        $scope.RegistrosTecnicos(registrosTenicos);
    }

    // =================================================
    //                 FUNCION PARA EXPORTAR EN CSV
    // =================================================

    $scope.csvNovedadesTecnicos = function () {
        $scope.csvPend = false;
        if ($scope.Registros.fechaini > $scope.Registros.fechafin) {
            alert("La fecha inicial debe ser menor que la inicial");
            return;
        } else {

            services.expCsvNovedadesTecnico($scope.Registros, $rootScope.galletainfo).then(
                function (data) {
                    // console.log(data.data[0]);
                    window.location.href = "tmp/" + data.data[0];
                    $scope.csvPend = true;
                    $scope.counter = data.data[1];
                    //console.log(data.data);
                    return data.data;
                },
                function errorCallback(response) {
                    $scope.errorDatos = "No hay datos.";
                    $scope.csvPend = false;
                }
            );
        }
    }

    $scope.regiones();
    $scope.situacion();

});

app.controller('contrasenasClickCtrl', function ($scope, $http, $rootScope, $location, $route, $routeParams, $cookies, $timeout, services, cargaRegistros) {

    /*INCLUDE DE LOS MODULOS*/
    $scope.rutaContrasenasClick = "partial/contrasenaClick/contrasenasClick.html";

    /*INCLUDE DE LOS MODALES*/
    $scope.mostarModalNovedadesVisitas = "partial/novedadesVisita/modal/modalNovedadVisitas.html";


    // =========================================================
    //      FUNCIÓN PARA BUSCAR EL TÉCNICO POR LÓGIN O CÉDULA
    // =========================================================

    $scope.BuscarcontrasenasTecnicos = function (datos) {

        $scope.errorDatos = null;
        $scope.listaContrasenasClick = {};
        if (datos == undefined) {
            swal({
                title: "Debe completar los criterios de búsqueda",
                type: "warning",
                confirmButtonClass: "btn-danger",
            });
            return;
        } else if (datos.concepto == undefined && datos.buscar != null) {
            swal({
                title: "Debe Seleccionar el criterio de búsqueda",
                type: "warning",
                confirmButtonClass: "btn-danger",
            });
            return;
        } else if (datos.concepto == 'cedula' && (datos.buscar == null || datos.buscar == "")) {
            swal({
                title: "Debe Ingresar la cédula a buscar",
                type: "warning",
                confirmButtonClass: "btn-danger",
            });
            return;
        } else if (datos.concepto == 'login' && datos.buscar == null || datos.buscar == "") {
            swal({
                title: "Debe Ingresar el lógin a buscar",
                type: "warning",
                confirmButtonClass: "btn-danger",
            });
            return;
        } else {
            services.registrosContrasenasTecnicos(datos).then(
                function (data) {

                    $scope.listaContrasenasClick = data.data[0];


                    if ($scope.listaContrasenasClick == "<") {

                        swal({
                            title: "El técnico " + datos.buscar + " no existe en la Base de Datos.",
                            type: "warning",
                            confirmButtonClass: "btn-danger",
                        });

                    }

                    return data.data;

                },

                function errorCallback(response) {

                    if (response.status == '400') {

                        swal({
                            title: "El técnico " + datos.buscar + " no existe en la Base de Datos.",
                            type: "warning",
                            confirmButtonClass: "btn-danger",
                        });
                    }
                    ;


                });
        }
    }


    // ====================================================
    //         FUNCION PARA EXPORTAR LA DATA EN CSV
    // ====================================================


    $scope.csvcontrasenasTecnicos = function () {
        //console.log(datoExportar+$scope.indicadores.fecha);
        services.expCsvContrasenasTecnicos($rootScope.galletainfo).then(
            function (data) {
                // console.log(data.data[0]);
                window.location.href = "tmp/" + data.data[0];
                return data.data;
            },
            function errorCallback(response) {
            }
        );

    };


    // =================================================
    //            MODAL PARA CAMBIO CONTRASEÑA
    // =================================================

    $scope.updateModalContrasenasClick = function (data) {
        $rootScope.datos = data;
    };

    // =====================================================
    //            FUNCION PARA CAMBIAR LA CONTRASEÑA
    // =====================================================

    $scope.editPwdTecnicos = function (datos) {

        if (datos.newpwd == undefined || datos.confnewpwd == undefined) {
            swal({
                title: "Por favor ingrese la contraseña",
                type: "warning",
                confirmButtonClass: "btn-danger",
            });
            return;
        } else if (datos.newpwd != datos.confnewpwd) {

            swal({
                title: "Las contraseñas no coinciden, por favor digitar de nuevo",
                type: "warning",
                confirmButtonClass: "btn-danger",
            });
            return;
        } else {

            services.editarPasswordTecnicos(datos).then(
                function (data) {

                    Swal(
                        'Se actualizó la contraseña!',
                        'Bien Hecho'
                    )
                    /*PARA QUE EL MODAL SE OCULTE SOLO*/
                    $("#updateModalContrasenasClick").modal('hide');
                    return data.data;
                },

                function errorCallback(response) {
                });
        }
    };
});

app.controller('quejasGoCtrl', function ($scope, $http, $rootScope, $location, $route, $routeParams, $cookies, $timeout, services, cargaRegistros) {

    /*INCLUDE RUTA DEL HTML DE LA VISTA PPAL*/
    $scope.rutaQuejasGo = "partial/quejasGo/QuejasGestOper.html";

    /*INCLUDE RUTA DEL MODAL*/
    $scope.rutaModalQuejasGo = "partial/quejasGo/modal/modalQuejasGo.html";

    $scope.listaQuejasGo = {};
    $scope.Registros = {};
    $scope.quejasGoSel = {};
    var timer;
    var idqueja;

    $scope.pageChanged = function () {

        $scope.LoadQuejasGo($scope.datapendientes.currentPage);
    };

    /* FUNCION PARA VALIDAR LOS DATOS ANTES DE BUSCAR */
    $scope.validarDatos = function (datos) {

        if (datos.columnaBusqueda == undefined || datos.valorBusqueda == undefined) {
            datos.columnaBusqueda = "";
            datos.valorBusqueda = "";
        }

        if (datos.fechaini == undefined || datos.fechafin == undefined) {
            Swal({
                type: 'error',
                title: 'Oops...',
                text: 'Debe seleccionar un rango de fecha!',
            })
        } else {

            $scope.LoadQuejasGo(datos);
        }
    };

    /* FUNCION PARA CARGAR LA DATA DEL DIA EN LA VISTA PRINCIPAL */
    $scope.LoadQuejasGo = function (datos) {

        $scope.listaQuejasGo = {};

        services.listaQuejasGoDia($scope.datapendientes.currentPage, datos).then(
            function (data) {
                $scope.listaQuejasGo = data.data.data;
                $scope.cantidad = data.data.data.length;
                $scope.counterpag = data.data.contador;
                return data.data;
            },

            function errorCallback(response) {
                if (response.status == "400") {
                    $scope.counterpag = 0;
                }
            });
    };

    $scope.maxSize = 4;
    $scope.datapendientes = {maxSize: 4, currentPage: 1, numPerPage: 100, totalItems: 0};
    $scope.LoadQuejasGo($scope.datapendientes.currentPage);

    /* FUNCION PARA LLAMAR EL MODAL */
    $scope.mostraModal = function () {

        $scope.counter = 0;

        $scope.startCounter = function () {
            if (timer === null) {
                updateCounter();
            }
        };
        var updateCounter = function () {
            $scope.counter++;
            timer = $timeout(updateCounter, 1000);
        };
        updateCounter();

        $scope.quejasGoSel.observacion = "";

        angular.copy();
        $("#modalQuejasGo").modal();
    };

    /* FUNCION PARA LLAMAR LAS CIUDADES */
    $scope.ciudadesQuejasGo = function () {

        services.getCiudadesQuejasGo().then(function (respuesta) {
            $scope.listadoCiudadesQGo = respuesta.data[0];
        });
    };

    /* FUNCION PARA LLAMAR LAS BUSCAR AL TECNICO */
    $scope.BuscarTecnico = function () {

        var cedula = $scope.quejasGoSel.cedtecnico;

        if (cedula == undefined) {

            Swal({
                type: 'error',
                title: 'Oops...',
                text: 'Debe ingresar la cédula del técnico!',
            })

            return;

        }

        services.traerTecnico(cedula).then(function (data) {

                if (data.status == '201') {
                    $scope.Tecnico = data.data[0];
                    $scope.quejasGoSel.tecnico = $scope.Tecnico[0].nombre;
                    $scope.quejasGoSel.region = $scope.Tecnico[0].ciudad;
                    $scope.infoTecnico = true;
                    return;

                } else if (data.status == '200') {
                    $scope.ciudadesQuejasGo();
                    $('#crearTecnicoQuejasGo').modal('show');
                    $scope.infoTecnico = false;
                }
            },
            function errorCallback(data) {

                if (data.status == '400') {
                    Swal({
                        type: 'error',
                        title: 'Oops...',
                        text: 'No fue posible buscar el técnico!',
                        footer: '¡Reporta con el administrador!'
                    })
                }
            });
    };

    /* FUNCION PARA CREAR TECNICO */
    $scope.crearTecQuejasGo = function (crearTecnicoquejasGoSel, frmCrearTecnicoQuejasGo) {

        services.creaTecnicoQuejasGo(crearTecnicoquejasGoSel).then(
            function (data) {
                Swal(
                    'El técnico fue Creado!',
                    'Bien Hecho'
                )
                /*PARA ACTUALIZAR LA VISTA PRINCIPAL*/
                $scope.LoadQuejasGo($scope.datapendientes.currentPage);
                /*PARA QUE EL MODAL SE OCULTE SOLO UNA VEZ GUARDE*/
                $("#crearTecnicoQuejasGo").modal('hide');
                /*PARA LIMPIAR EL MODAL*/
                frmCrearTecnicoQuejasGo.autoValidateFormOptions.resetForm();
                return data.data;
            },

            function errorCallback(response) {

                Swal({
                    type: 'error',
                    title: 'Oops...',
                    text: 'Debe seleccionar un rango de fecha!',
                })

            });
    };

    /* FUNCION PARA GUARDAR LA QUEJA */
    $scope.guardar = function (quejasGoSel, frmQuejasGo) {

        $timeout.cancel(timer);
        timer = null;

        var hours = Math.floor($scope.counter / 3600),
            minutes = Math.floor(($scope.counter % 3600) / 60),
            seconds = Math.floor($scope.counter % 60);

        if (hours < 10) {
            hours = "0" + hours;
        }
        ;
        if (minutes < 10) {
            minutes = "0" + minutes;
        }
        ;
        if (seconds < 10) {
            seconds = "0" + seconds;
        }
        ;

        $scope.counter = hours + ":" + minutes + ":" + seconds;

        services.guardarQuejaGo(quejasGoSel, $scope.counter, $rootScope.galletainfo).then(
            function (respuesta) {

                if (respuesta.status == '201') {
                    Swal(
                        'La Queja fue Guardada!',
                        'Bien Hecho'
                    )
                }
                $scope.infoTecnico = false;

                /*PARA QUE EL MODAL SE OCULTE SOLO UNA VEZ GUARDE*/
                $("#modalQuejasGo").modal('hide');
                $scope.quejasGoSel = {};

                /*LIMPIAR EL MODAL*/
                frmGenereacionTT.autoValidateFormOptions.resetForm();

            }, function errorCallback(response) {

                if (response.status == '400') {

                    Swal({
                        type: 'error',
                        title: 'Oops...',
                        text: 'No fue posible guardar la queja!',
                        footer: '¡Reporta con el administrador!'
                    })
                }
            });

        $scope.LoadQuejasGo($scope.datapendientes.currentPage);
    };

    /* FUNCION PARA EXPORTAR LAS QUEJAS EN CSV */
    $scope.csvQuejasGo = function () {
        $scope.csvPend = false;

        if ($scope.Registros.columnaBusqueda == undefined || $scope.Registros.valorBusqueda == undefined) {
            $scope.Registros.columnaBusqueda = "";
            $scope.Registros.valorBusqueda = "";
        }

        if ($scope.Registros.fechaini == undefined || $scope.Registros.fechafin == undefined) {
            $scope.Registros.fechaini = "";
            $scope.Registros.fechafin = "";
        }

        if ($scope.Registros.fechaini > $scope.Registros.fechafin) {
            alert("La fecha inicial debe ser menor que la inicial");
            return;
        } else {

            services.expCsvQuejasGo($scope.Registros, $rootScope.galletainfo).then(
                function (data) {
                    window.location.href = "tmp/" + data.data[0];
                    $scope.csvPend = true;
                    $scope.counter = data.data[1];

                    return data.data;
                },
                function errorCallback(response) {
                    $scope.errorDatos = "No hay datos.";
                    $scope.csvPend = false;
                }
            );
        }
    };

    /* FUNCION PARA LLAMAR EL MODAL PARA MODIFICAR LAS OBSERVACIONES DE LA QUEJAGO */
    $scope.abrirModalModificarObs = function (id, observacion) {

        $scope.quejasGoSel.observacion = observacion;
        idqueja = id;

        angular.copy();
        $("#modObserQuejasGo").modal();
    };

    /* FUNCION PARA MODIFICAR LAS OBSERVACIONES DE LA QUEJAGO */
    $scope.modificarObservacionQuejasGo = function (quejasGoSel, frmModObserQuejasGo, id) {

        services.modiObserQuejasGo(quejasGoSel, idqueja).then(function (respuesta) {

            if (respuesta.status == '201') {
                Swal(
                    'Las observaciones fueron modificadas!',
                    'Bien Hecho'
                )
            }
            /*PARA ACTUALIZAR LA VISTA PRINCIPAL*/
            $scope.LoadQuejasGo($scope.datapendientes.currentPage);

            /*PARA QUE EL MODAL SE OCULTE SOLO UNA VEZ GUARDE*/
            $("#modObserQuejasGo").modal('hide');
            $scope.quejasGoSel = {};

            /*LIMPIAR EL MODAL*/
            frmModObserQuejasGo.autoValidateFormOptions.resetForm();

        }, function errorCallback(response) {

            if (response.status == '400') {

                Swal({
                    type: 'error',
                    title: 'Oops...',
                    text: 'No fue posible actualizar las observaciones!',
                    footer: '¡Reporta con el administrador!'
                })
            }
        });
    };
});


app.controller('saraCtrl', function ($scope, $http, $rootScope, $location, $route, $routeParams, $cookies, $timeout, services) {

    $scope.rutaConsultaSara = "partial/consultaSara/consulSara.html";
    $scope.dataSara = {};
    $scope.horasTranscurridas = 0;
    $scope.minutosTranscurridos = 0;
    $scope.segundosTranscurridos = 0;

    $scope.buscarDataSara = function (datos) {

        var tareaSara = datos.tarea;
        $scope.urlServicio = "http://10.100.66.254:8080/SARA/Buscar/" + tareaSara;

        $http.get($scope.urlServicio, {timeout: 8000}).then(function (data) {

                $scope.dataSara = data.data;

                if ($scope.dataSara.Error == "No hay datos para mostrar") {

                    $scope.horasTranscurridas = 0;
                    $scope.minutosTranscurridos = 0;
                    $scope.segundosTranscurridos = 0;

                    Swal({
                        type: 'error',
                        title: 'Oops...',
                        text: 'Aún no se hace la solicitud a SARA',
                    })
                }

                $scope.indiceSara = (Object.keys($scope.dataSara.SolicitudesSara).length) - 1;
                var tiempoSara = $scope.dataSara.SolicitudesSara[$scope.indiceSara].TiempoRespuesta;
                $scope.horasTranscurridas = tiempoSara.substr(0, 2);
                $scope.minutosTranscurridos = tiempoSara.substr(3, 2);
                $scope.segundosTranscurridos = tiempoSara.substr(6, 2);
                return data.data;
            },

            function (Error) {

                Swal({
                    type: 'error',
                    title: 'Oops...',
                    text: 'Se presentan problemas con el Web Service, reporta con el administrador',
                })
            });
    }

    $scope.csvexportarRRHH = function () {
        services.getexpcsvRRHH($rootScope.galletainfo).then((response) => {
            var data = response.data;
            var array = typeof data != 'object' ? JSON.parse(data) : data;
            var str = '';
            var column = 'Cedula, Login, Nombre, Telefono, Region, Distrito, Tipo Tecnico, Contratista, Latitud, Longitud, Calendario, No Disponibilidad, Fecha Registro \r\n';
            str += column;
            for (var i = 0; i < array.length; i++) {
                var line = '';
                for (var index in array[i]) {
                    if (line != '') line += ','
                    line += array[i][index];
                }

                str += line + '\r\n';
            }
            var dateCsv = new Date();
            var yearCsv = dateCsv.getFullYear();
            var monthCsv = (dateCsv.getMonth() + 1 <= 9) ? '0' + (dateCsv.getMonth() + 1) : (dateCsv.getMonth() + 1);
            var dayCsv = (dateCsv.getDate() <= 9) ? '0' + dateCsv.getDate() : dateCsv.getDate();
            var fullDateCsv = yearCsv + "-" + monthCsv + "-" + dayCsv;


            var blob = new Blob([str]);
            var elementToClick = window.document.createElement("a");
            elementToClick.href = window.URL.createObjectURL(blob, {type: 'text/csv'});
            elementToClick.download = "Disponibilidad-" + fullDateCsv + ".csv";
            elementToClick.click();
            console.log(str);

        }).catch(error => console.log(error));
    }


});


app.controller('registrosCtrl', function ($scope, $http, $rootScope, $location, $route, $routeParams, $cookies, $timeout, services, cargaRegistros) {
    $scope.listaRegistros = {};
    $scope.Registros = {};
    $scope.listadoAcciones = {};
    $scope.datosRegistros = {};
    $scope.verplantilla = false;

    if ($scope.Registros.fechaini == undefined || $scope.Registros.fechafin == undefined) {
        var tiempo = new Date().getTime();
        var date1 = new Date();
        var year = date1.getFullYear();
        var month = (date1.getMonth() + 1 <= 9) ? '0' + (date1.getMonth() + 1) : (date1.getMonth() + 1);
        var day = (date1.getDate() <= 9) ? '0' + date1.getDate() : date1.getDate();

        tiempo = year + "-" + month + "-" + day;

        $scope.fechaini = tiempo;
        $scope.fechafin = tiempo;

        //console.log("fechaini: ",$scope.fechaini);
        //console.log("fechafin: ",$scope.fechafin);
    }

    $scope.setPage = function (pageNo) {
        $scope.datapendientes.currentPage = pageNo;
    };

    $scope.pageChanged = function () {
        $scope.BuscarRegistros($scope.datapendientes.currentPage);
    };

    $scope.BuscarRegistros = function (datos) {
        console.log("BuscarRegistros: ", datos);
        $scope.errorDatos = null;
        $scope.csvPend = false;
        $scope.listaRegistros = {};

        if (datos.fechaini > datos.fechafin) {
            alert("La fecha inicial debe ser menor que la inicial");
            return;
        } else {

            services.registros($scope.datapendientes.currentPage, datos).then(
                function (data) {
                    console.log("registros: ", data);
                    $scope.listaRegistros = data.data[0];
                    //console.log("listaRegistros: ", $scope.listaRegistros);
                    $scope.cantidad = data.data[0].length;
                    $scope.counter = data.data[1];

                    return data.data;
                },
                function errorCallback(response) {
                    if (datos.concepto == undefined || datos.buscar == undefined) {
                        if (datos.fechaini == datos.fechafin) {
                            $scope.errorDatos = "No hay datos para el día:  " + datos.fechaini;
                        } else {
                            $scope.errorDatos = "No hay datos entre " + datos.fechaini + " - " + datos.fechafin;
                        }
                    } else
                        $scope.errorDatos = datos.concepto + " " + datos.buscar + " no existe.";
                });
        }
    }

    $scope.muestraNotas = function (datos) {

        $scope.pedido = datos.pedido;
        $scope.TituloModal = "Observaciones para el pedido:";
        $scope.observaciones = datos.observaciones;
        // console.log( $scope.observaciones);
    }


    $scope.calcularSubAcciones = function (proceso, accion) {
        //    console.log(proceso);
        //  console.log(accion);
        $scope.listadoSubAcciones = {};
        $scope.validarsubaccion = true;

        services.getSubAcciones(proceso, accion).then(function (data) {
            $scope.listadoSubAcciones = data.data[0];
            $scope.validarsubaccion = true;
        }, function errorCallback(response) {
            $scope.validarsubaccion = false;
        });
    };

    $scope.calcularAcciones = function (proceso) {
        //console.log(proceso);
        if (proceso == "") {
            $scope.validaraccion = false;
            $scope.validarsubaccion = false;
        } else {

            services.getAcciones(proceso).then(function (data) {
                $scope.listadoAcciones = data.data[0];
                $scope.validaraccion = true;
                $scope.validarsubaccion = false;
            });
        }
    };

    $scope.editarRegistros = function (datos) {
        $scope.datosRegistros = datos;
        if ($scope.datosRegistros.plantilla != "") {
            $scope.verplantilla = true;
        } else {
            $scope.verplantilla = false;
        }
        console.log("datosRegistros: ", $scope.datosRegistros);
        $scope.TituloModal = "Editar pedido:";
        $scope.pedido = datos.pedido;
        $scope.calcularAcciones($scope.datosRegistros.proceso);
        $scope.calcularSubAcciones($scope.datosRegistros.proceso, $scope.datosRegistros.accion);
    }

    $scope.editRegistro = function (datos) {
        $scope.errorDatos = null;
        $scope.respuestaupdate = null;
        $scope.respuestadelete = null;
        //  console.log(datos);

        services.editarRegistro(datos, $rootScope.galletainfo).then(
            function (data) {

                console.log("editarRegistro: ", data);
                // $errorDatos=null;
                $scope.respuestaupdate = "Pedido " + datos.pedido + " actualizado exitosamente";
                //$rootScope.nombre=$scope.respuesta[0].NOMBRE;
                //$location.path('/home/');
                return data.data;
            },
            function errorCallback(response) {
            });

        $scope.BuscarRegistros(datos);
    };

    $scope.csvRegistros = function () {
        $scope.csvPend = false;
        if ($scope.Registros.fechaini > $scope.Registros.fechafin) {
            alert("La fecha inicial debe ser menor que la inicial");
            return;
        } else {

            services.expCsvRegistros($scope.Registros, $rootScope.galletainfo).then(
                function (data) {
                    // console.log(data.data[0]);
                    window.location.href = "tmp/" + data.data[0];
                    $scope.csvPend = true;
                    $scope.counter = data.data[1];
                    //console.log(data.data);
                    return data.data;
                },
                function errorCallback(response) {
                    $scope.errorDatos = "No hay datos.";
                    $scope.csvPend = false;
                }
            );
        }
    };

    $scope.csvtecnico = function () {
        // console.log(datoExportar+$scope.indicadores.fecha);

        services.expCsvtecnico($scope.Registros, $rootScope.galletainfo).then(
            function (data) {
                // console.log(data.data[0]);
                window.location.href = "tmp/" + data.data[0];
                $scope.csvPend = true;
                $scope.counter = data.data[1];
                //console.log(data.data);
                return data.data;
            },
            function errorCallback(response) {
                $scope.errorDatos = "No hay datos.";
                $scope.csvPend = false;
            }
        );
    };

    $scope.maxSize = 5;
    $scope.datapendientes = {maxSize: 5, currentPage: 1, numPerPage: 100, totalItems: 0};
    $scope.BuscarRegistros($scope.datapendientes.currentPage);

    $scope.uploadFile = function () {
        $scope.carga_ok = true;
        var file = $scope.myFile;
        $scope.user = $rootScope.galletainfo.LOGIN;
        $scope.name = '';
        //   console.log('file is ');
        // console.dir(file);
        // console.dir($scope.tipoCarga);
        $scope.delete_ok = false;

        var uploadUrl = 'services/cargaRegistros';
        cargaRegistros.uploadFileToUrl(file, uploadUrl, $scope.user);
        $scope.msg = "Se cargo el archivo: " + file.name;

        Swal(
            'El Archivo fue cargado correctamente!',
            'Bien Hecho'
        )


    };
});


app.controller('registrosOfflineCtrl', function ($scope, $http, $rootScope, $location, $route, $routeParams, $cookies, $timeout, services) {
    $scope.listaRegistrosOffline = {};


    $scope.RegistrosOffline = function () {

        services.registrosOffline().then(
            function (data) {
                $scope.listaRegistrosOffline = data.data[0];
                //console.log(data.data);
                $scope.cantidad = data.data[0].length;
                $scope.counter = data.data[1];

                return data.data;
            },
            function errorCallback(response) {
                $scope.errorDatos = "No hay datos";

            });
    }
    $scope.RegistrosOffline();
});

app.controller('mesaofflineCtrl', function ($scope, $http, $rootScope, $location, $route, $routeParams, $cookies, $timeout, services) {
    $scope.validarproducto = false;
    $scope.validaractividad = false;


    $scope.calcularAccionOffline = function () {
        var producto = $scope.offline.PRODUCTO;
        $scope.validarproducto = true;
        services.getAccionesoffline(producto).then(function (data) {
            $scope.listadoAcciones = data.data[0];
        });
    };

    $scope.calcularActividad2offline = function () {
        if ($scope.offline.ACTIVIDAD == "Patinaje") {
            $scope.validaractividad = true;
            $scope.actividades2 = [
                {ID: 'Asesor reiterativo', ACTIVIDAD2: 'Asesor reiterativo'},
                {ID: 'Asesor AHT alto', ACTIVIDAD2: 'Asesor AHT alto'},
                {ID: 'Requiere intervencion - Supervisor', ACTIVIDAD2: 'Requiere intervencion - Supervisor'},
                {ID: 'Requiere intervención – Formacion ', ACTIVIDAD2: 'Requiere intervención – Formacion '},
            ];
        } else
            $scope.validaractividad = false;
        ;
    };

    $scope.guardarPedidoOffline = function () {
        services.pedidoOffline($scope.offline, $rootScope.galletainfo).then(
            function (data) {
                // console.log("la lista "+$scope.listaUsuarios);
                $scope.respuestaupdate = "Pedido creado.";
                ;
                return data.data;
            },
            function errorCallback(response) {

                $scope.errorDatos = "Pedido no fue creado.";

                // console.log($scope.errorDatos);
            });
    };

});

// ESTA FUNCIÓN RECIBE EL VALOR DE LA LISTA DEL CM, SI OBSOLETO ES "SI" DISPARA EL POP UP
function fn_ValidarObsoleto() {
    var CMobsoleto = document.getElementById("CMobsoleto").value;
    var CMobsoleto2 = document.getElementById("CMobsoleto2").value;
    // var msg="Se requiere cambiar el equipo";
    if (CMobsoleto == "Si" || CMobsoleto2 == "Si") {
        Swal(
            'Se requiere cambiar el equipo'
        )
        CMobsoleto = document.getElementById("CMobsoleto").reset();
        CMobsoleto2 = document.getElementById("CMobsoleto2").reset();
    }
}

/*FUNCIONES PARA ACTIVAR LOS MENSAJES EMERGENTES DE CORREGIR PORTAFOLIO*/
function fn_popup1() {
    var opc1 = document.getElementById("idaccionTVHFCGPON").value;
    var action1 = "Corregir portafolio";
    var msg = "Esta opción es solo para hacer correcciones en portafolio o inventario";
    if (opc1 == action1) {
        alert(msg);
        opc1 = document.getElementById("idaccionTVHFCGPON").reset();
    }
}

function fn_popup2() {
    var opc2 = document.getElementById("idaccionTVCobre").value;
    var action2 = "Corregir portafolio";
    var msg = "Esta opción es solo para hacer correcciones en portafolio o inventario";
    if (opc2 == action2) {
        alert(msg);
        opc2 = document.getElementById("idaccionTVCobre").reset();
    }
}

function fn_popup3() {
    var opc3 = document.getElementById("idaccionTVDTH").value;
    var action3 = "Corregir portafolio";
    var msg = "Esta opción es solo para hacer correcciones en portafolio o inventario";
    if (opc3 == action3) {
        alert(msg);
        opc3 = document.getElementById("idaccionTVDTH").reset();
    }
}

function fn_popup4() {
    var opc4 = document.getElementById("idaccionINTTOIP").value;
    var action4 = "Corregir portafolio";
    var msg = "Esta opción es solo para hacer correcciones en portafolio o inventario";
    if (opc4 == action4) {
        alert(msg);
        opc4 = document.getElementById("idaccionINTTOIP").reset();
    }
}

function fn_popup5() {
    var opc5 = document.getElementById("idaccionTOIP").value;
    var action5 = "Corregir portafolio";
    var msg = "Esta opción es solo para hacer correcciones en portafolio o inventario";
    if (opc5 == action5) {
        alert(msg);
        opc5 = document.getElementById("idaccionTOIP").resest();
    }
}

app.controller('nivelacionCtrl', function ($scope, $http, $rootScope, $location, $route, $routeParams, $cookies, $cookieStore, $timeout, services) {
    $scope.nivelacion = {};
    $scope.nivelacion.ticket = '';
    $scope.nivelacion.newIdTecnic = '';
    $scope.visible = false;
    $scope.newTec = false;
    $scope.tec = false

    en_genstion_nivelacion();

    function en_genstion_nivelacion() {
        services.en_genstion_nivelacion($rootScope.galletainfo).then(complete).catch(failed)

        function complete(data) {
                console.log('robbin ',data.data.tarea)
                $scope.nivelacion.proceso_terminado = data.data.tarea;
                if (data.data.gestion[0].total !== 'undefined') {
                    $scope.nivelacion.pendienteTotal = data.data.gestion[0].total;
                } else {
                    $scope.nivelacion.pendienteTotal = 0;
                }

                if (data.data.gestion[1].total !== 'undefined') {
                    $scope.nivelacion.realizadoTotal = data.data.gestion[1].total;
                } else {
                    $scope.nivelacion.realizadoTotal = 0;
                }
        }

        function failed(data) {
            console.log(data)
        }
    }

    $scope.buscarhistoricoNivelacion = function () {
        if ($scope.nivelacion.historico == '' || $scope.nivelacion.historico == undefined) {
            swal({
                type: 'error',
                text: 'Ingrese la tarea a buscar'
            })
        } else {
            services.buscarhistoricoNivelacion($scope.nivelacion.historico).then(complete).catch(failed)

            function complete(data) {

                $scope.nivelacion.databsucarPedido = data.data.data;
                $('#modalHistoricoNivelacion').modal('show');
                return data.data;

            }

            function failed(data) {
                console.log(data)
            }
        }
    }

    $scope.searchTicket = function () {
        if ($scope.nivelacion.ticket === "" || $scope.nivelacion.ticket === undefined) {
            Swal({
                type: 'error',
                title: 'Ingrese una tarea'
            })

        } else {
            $scope.url = "http://10.100.66.254:8080/HCHV_DEV/BuscarC/" + $scope.nivelacion.ticket;
            $http.get($scope.url, {timeout: 2000})
                .then(function (data) {

                        if (data.data.state === 0) {
                            Swal({
                                type: 'error',
                                title: 'No se encontraron datos'
                            })
                        } else {
                            $scope.nivelacion.pedido = data.data[0].UNEpedido
                            $scope.nivelacion.subZona = data.data[0].district
                            $scope.nivelacion.nombreTecnico = data.data[0].EngineerName
                            $scope.nivelacion.idTecnico = data.data[0].EngineerID
                            $scope.nivelacion.proceso = data.data[0].tasktypecategory
                            $scope.nivelacion.zona = data.data[0].region
                            $scope.visible = true;

                            $scope.status = data.data[0].status;
                            $scope.fecha_res = data.data[0].unefechacita;
                            $scope.fecha_res = $scope.fecha_res.split(" ");
                            if ($scope.fecha_res[2]) {
                                $scope.fecha_res = $scope.fecha_res[0];
                                $scope.fecha_res = $scope.fecha_res.split("/");
                                $scope.fecha_res = $scope.fecha_res[2] + '-' + $scope.fecha_res[1] + '-' + $scope.fecha_res[0]
                            } else {
                                $scope.fecha_res = data.data[0].unefechacita;
                            }

                            $scope.searchIdTecnic = function () {
                                services.searchIdTecnic($scope.nivelacion.newIdTecnic).then(complete).catch(failed);

                                function complete(data) {
                                    if (data.data.state === 0) {
                                        Swal({
                                            type: 'error',
                                            title: 'No se encontraron datos'
                                        })
                                    } else {
                                        $scope.nivelacion.newTecName = data.data.data.nombre;
                                        $scope.newTec = true;
                                    }
                                }

                                function failed(data) {
                                    console.log(data)
                                }
                            }

                            $scope.saveNivelation = function () {
                                var today = new Date();
                                var day = today.getDate();
                                var month = today.getMonth() + 1;
                                var year = today.getFullYear();
                                var hoy = `${year}-${month}-${day}`
                                $scope.case6 = 0;
                                $scope.case7 = 0;
                                //$scope.fecha_res = '2022-12-16';
                                if ($scope.nivelacion.motivo == 1) {

                                    if (($scope.nivelacion.motivo == 1) && ($scope.status == 'Abierto' || $scope.status == 'Asignado')) {
                                        Swal({
                                            type: 'error',
                                            title: 'La tarea esta en estado de asignacion automatica',
                                            timer: 4000
                                        }).then(function () {
                                            $route.reload();
                                        })
                                    } else if (($scope.nivelacion.motivo == 1) && ($scope.status == 'Finalizada' || $scope.status == 'Suspendido' || $scope.status == 'Suspendido-Abierto' || $scope.status == 'Incompleto' || $scope.status == 'Pendiente' || $scope.status == 'Abierto' || $scope.status == 'Asignado')) {
                                        Swal({
                                            type: 'error',
                                            title: 'La tarea esta en estado no valido',
                                            timer: 4000
                                        }).then(function () {
                                            $route.reload();
                                        })
                                    } else {

                                        services.saveNivelation($scope.nivelacion, $rootScope.galletainfo).then(complete).catch(failed);

                                        function complete(data) {
                                            if (data.data.state == 99) {
                                                swal({
                                                    type: 'error',
                                                    title: data.data.title,
                                                    text: data.data.text,
                                                    timer: 4000
                                                }).then(function () {
                                                    $cookies.remove('usuarioseguimiento');
                                                    $location.path('/');
                                                    $rootScope.galletainfo = undefined;
                                                    $rootScope.permiso = false;
                                                    $route.reload();
                                                })
                                            } else if (data.data.state === 0) {
                                                Swal({
                                                    type: 'error',
                                                    title: data.data.msj,
                                                    timer: 4000
                                                }).then(function () {
                                                    $route.reload();
                                                })
                                            } else {
                                                Swal({
                                                    type: 'success',
                                                    title: 'La solicitud de nivelación se ha creado correctamente',
                                                    timer: 4000
                                                }).then(function () {
                                                    $route.reload();
                                                })

                                            }
                                        }

                                        function failed(data) {
                                            console.log(data)
                                        }
                                    }

                                } else {

                                    if ($scope.nivelacion.submotivo == 6 || $scope.nivelacion.submotivo == 7) {
                                        if (($scope.nivelacion.submotivo == 6)) {
                                            $scope.url = "http://10.100.66.254:8080/HCHV_DEV/BuscarF/" + $scope.nivelacion.ticket;
                                            $http.get($scope.url, {timeout: 2000})
                                                .then(function (data) {
                                                    if (data.data.state == 1) {
                                                        Swal({
                                                            type: 'error',
                                                            title: data.data.data,
                                                            timer: 4000
                                                        }).then(function () {
                                                            $route.reload();
                                                        })
                                                    } else if (data.data.state == 0) {
                                                        if (($scope.nivelacion.submotivo == 6) && ($scope.status != 'Incompleto')) {

                                                            if (($scope.nivelacion.submotivo == 6 && ($scope.status != 'Pendiente'))) {

                                                                Swal({
                                                                    type: 'error',
                                                                    title: 'La tarea esta en estado no valido',
                                                                    timer: 4000
                                                                }).then(function () {
                                                                    $route.reload();
                                                                })
                                                            } else if (($scope.nivelacion.submotivo == 6) && ($scope.fecha_res != hoy)) {
                                                                Swal({
                                                                    type: 'error',
                                                                    title: 'La tarea tiene una fecha diferente a hoy',
                                                                    timer: 4000
                                                                }).then(function () {
                                                                    $route.reload();
                                                                })
                                                            } else {
                                                                services.saveNivelation($scope.nivelacion, $rootScope.galletainfo).then(complete).catch(failed);

                                                                function complete(data) {
                                                                    if (data.data.state == 99) {
                                                                        swal({
                                                                            type: 'error',
                                                                            title: data.data.title,
                                                                            text: data.data.text,
                                                                            timer: 4000
                                                                        }).then(function () {
                                                                            $cookies.remove('usuarioseguimiento');
                                                                            $location.path('/');
                                                                            $rootScope.galletainfo = undefined;
                                                                            $rootScope.permiso = false;
                                                                            $route.reload();
                                                                        })
                                                                    } else if (data.data.state === 0) {
                                                                        Swal({
                                                                            type: 'error',
                                                                            title: data.data.msj,
                                                                            timer: 4000
                                                                        }).then(function () {
                                                                            $route.reload();
                                                                        })
                                                                    } else {
                                                                        Swal({
                                                                            type: 'success',
                                                                            title: 'La solicitud de nivelación se ha creado correctamente',
                                                                            timer: 4000
                                                                        }).then(function () {
                                                                            $route.reload();
                                                                        })

                                                                    }
                                                                }
                                                            }


                                                        } else if (($scope.nivelacion.submotivo == 6) && ($scope.fecha_res != hoy)) {

                                                            Swal({
                                                                type: 'error',
                                                                title: 'La tarea tiene una fecha diferente a hoy',
                                                                timer: 4000
                                                            }).then(function () {
                                                                $route.reload();
                                                            })
                                                        } else {

                                                            services.saveNivelation($scope.nivelacion, $rootScope.galletainfo).then(complete).catch(failed);

                                                            function complete(data) {
                                                                if (data.data.state == 99) {
                                                                    swal({
                                                                        type: 'error',
                                                                        title: data.data.title,
                                                                        text: data.data.text,
                                                                        timer: 4000
                                                                    }).then(function () {
                                                                        $cookies.remove('usuarioseguimiento');
                                                                        $location.path('/');
                                                                        $rootScope.galletainfo = undefined;
                                                                        $rootScope.permiso = false;
                                                                        $route.reload();
                                                                    })
                                                                } else if (data.data.state === 0) {
                                                                    Swal({
                                                                        type: 'error',
                                                                        title: data.data.msj,
                                                                        timer: 4000
                                                                    }).then(function () {
                                                                        $route.reload();
                                                                    })
                                                                } else {
                                                                    Swal({
                                                                        type: 'success',
                                                                        title: 'La solicitud de nivelación se ha creado correctamente',
                                                                        timer: 4000
                                                                    }).then(function () {
                                                                        $route.reload();
                                                                    })

                                                                }
                                                            }
                                                        }


                                                        function failed(data) {
                                                            console.log(data)
                                                        }
                                                    }
                                                });
                                        } else if (($scope.nivelacion.submotivo == 7)) {
                                            $scope.url = "http://10.100.66.254:8080/HCHV_DEV/BuscarF/" + $scope.nivelacion.ticket;
                                            $http.get($scope.url, {timeout: 2000})
                                                .then(function (data) {
                                                    if (data.data.state == 1) {
                                                        Swal({
                                                            type: 'error',
                                                            title: data.data.data,
                                                            timer: 4000
                                                        }).then(function () {
                                                            $route.reload();
                                                        })
                                                    } else if (data.data.state == 0) {

                                                        if (($scope.nivelacion.submotivo == 7) && ($scope.status != 'Incompleto')) {

                                                            if (($scope.nivelacion.submotivo == 7) && ($scope.status != 'Pendiente')) {

                                                                Swal({
                                                                    type: 'error',
                                                                    title: 'La tarea esta en estado no valido',
                                                                    timer: 4000
                                                                }).then(function () {
                                                                    $route.reload();
                                                                })
                                                            } else if (($scope.nivelacion.submotivo == 7) && ($scope.fecha_res >= hoy)) {
                                                                Swal({
                                                                    type: 'error',
                                                                    title: 'La tarea tiene una fecha mayor',
                                                                    timer: 4000
                                                                }).then(function () {
                                                                    $route.reload();
                                                                })
                                                            } else {
                                                                services.saveNivelation($scope.nivelacion, $rootScope.galletainfo).then(complete).catch(failed);

                                                                function complete(data) {
                                                                    if (data.data.state == 99) {
                                                                        swal({
                                                                            type: 'error',
                                                                            title: data.data.title,
                                                                            text: data.data.text,
                                                                            timer: 4000
                                                                        }).then(function () {
                                                                            $cookies.remove('usuarioseguimiento');
                                                                            $location.path('/');
                                                                            $rootScope.galletainfo = undefined;
                                                                            $rootScope.permiso = false;
                                                                            $route.reload();
                                                                        })
                                                                    } else if (data.data.state === 0) {
                                                                        Swal({
                                                                            type: 'error',
                                                                            title: data.data.msj,
                                                                            timer: 4000
                                                                        }).then(function () {
                                                                            $route.reload();
                                                                        })
                                                                    } else {
                                                                        Swal({
                                                                            type: 'success',
                                                                            title: 'La solicitud de nivelación se ha creado correctamente',
                                                                            timer: 4000
                                                                        }).then(function () {
                                                                            $route.reload();
                                                                        })

                                                                    }
                                                                }
                                                            }

                                                        } else if (($scope.nivelacion.submotivo == 7) && ($scope.fecha_res >= hoy)) {

                                                            Swal({
                                                                type: 'error',
                                                                title: 'La tarea tiene una fecha mayor',
                                                                timer: 4000
                                                            }).then(function () {
                                                                $route.reload();
                                                            })
                                                        } else {
                                                            services.saveNivelation($scope.nivelacion, $rootScope.galletainfo).then(complete).catch(failed);

                                                            function complete(data) {
                                                                if (data.data.state == 99) {
                                                                    swal({
                                                                        type: 'error',
                                                                        title: data.data.title,
                                                                        text: data.data.text,
                                                                        timer: 4000
                                                                    }).then(function () {
                                                                        $cookies.remove('usuarioseguimiento');
                                                                        $location.path('/');
                                                                        $rootScope.galletainfo = undefined;
                                                                        $rootScope.permiso = false;
                                                                        $route.reload();
                                                                    })
                                                                } else if (data.data.state === 0) {
                                                                    Swal({
                                                                        type: 'error',
                                                                        title: data.data.msj,
                                                                        timer: 4000
                                                                    }).then(function () {
                                                                        $route.reload();
                                                                    })
                                                                } else {
                                                                    Swal({
                                                                        type: 'success',
                                                                        title: 'La solicitud de nivelación se ha creado correctamente',
                                                                        timer: 4000
                                                                    }).then(function () {
                                                                        $route.reload();
                                                                    })

                                                                }
                                                            }
                                                        }


                                                        function failed(data) {
                                                            console.log(data)
                                                        }

                                                    }
                                                })
                                        }

                                    } else {
                                        services.saveNivelation($scope.nivelacion, $rootScope.galletainfo).then(complete).catch(failed);

                                        function complete(data) {
                                            if (data.data.state == 99) {
                                                swal({
                                                    type: 'error',
                                                    title: data.data.title,
                                                    text: data.data.text,
                                                    timer: 4000
                                                }).then(function () {
                                                    $cookies.remove('usuarioseguimiento');
                                                    $location.path('/');
                                                    $rootScope.galletainfo = undefined;
                                                    $rootScope.permiso = false;
                                                    $route.reload();
                                                })
                                            } else if (data.data.state === 0) {
                                                Swal({
                                                    type: 'error',
                                                    title: data.data.msj,
                                                    timer: 4000
                                                }).then(function () {
                                                    $route.reload();
                                                })
                                            } else {
                                                Swal({
                                                    type: 'success',
                                                    title: 'La solicitud de nivelación se ha creado correctamente',
                                                    timer: 4000
                                                }).then(function () {
                                                    $route.reload();
                                                })

                                            }
                                        }

                                        function failed(data) {
                                            console.log(data)
                                        }
                                    }
                                }

                            }
                        }
                    }
                    ,

                    function (failed) {
                        console.log(2, failed)
                    }

                    ,
                )
            ;
        }
    }
});

app.filter('mapNivelacion', function () {
    var genderHash = {
        'SI': 'SI',
        'NO': 'NO'
    };

    return function (input) {
        if (!input) {
            return '';
        } else {
            return genderHash[input];
        }
    };
});

app.controller('GestionNivelacionCtrl', ['$scope', '$rootScope', '$location', '$route', '$routeParams', '$cookies', '$cookieStore', '$timeout', 'services', 'i18nService', function ($scope, $rootScope, $location, $route, $routeParams, $cookies, $cookieStore, $timeout, services, i18nService) {
    $scope.GestionNivelacion = {};
    $scope.Registros = {};
    $scope.nivelacion = {};
    i18nService.setCurrentLang('es')
    //$scope.userLog = $rootScope.galletainfo.login
    init();

    function init() {
        getGrid();
        registrosTecnicos();
    }

    function getGrid(){
        services.gestionarNivelacion().then(complete).catch(failed)

        function complete(data) {
            $scope.datos = data.data.data;

            /*$scope.gridOptions.totalItems = counter;
            var firstRow = (curPage - 1) * datos
            $scope.gridOptions.data = datos*/
        }

        function failed(error) {
            console.log(error);
        }
    }

    /*function getGrid() {

        Date.prototype.addMins = function (m) {
            this.setTime(this.getTime() + (m * 60 * 1000));  // minutos * seg * milisegundos
            return this;
        }

        var fechaI2 = new Date();
        //fechaI2.addMins(15);

        var columnDefs = [
            {
                name: "Marcar",
                cellTemplate: "<div style='text-align: center'><input ng-checked={{row.entity.en_gestion}} value='{{row.entity.en_gestion}}' ng-model='row.entity.en_gestion' type='checkbox' ng-click='grid.appScope.engestion(row)'></div>",
                minWidth: 70,
                width: "1%",
                enableCellEdit: false,
                enableFiltering: false,
                enableRowHeaderSelection: true
            },
            {
                name: "Login",
                field: "gestiona_por",
                /!*cellTemplate: "<div style='text-align: center' ng-show='(row.entity.gestiona_por !== \"0\")'>" +
                    "<span class='label label-primary label-xsmall' ng-if='(row.entity.gestiona_por ==  grid.appScope.userLog)' style='vertical-align: sub'>{{grid.appScope.userLog}}</span>" +
                    "<span class='label label-primary label-xsmall' ng-if='(row.entity.gestiona_por != grid.appScope.userLog)' style='vertical-align: sub'>En gestion</span>" +
                    "</div>",*!/
                minWidth: 80,
                width: "3%",
                enableCellEdit: false,
            }, {
                name: "Tarea",
                field: "ticket_id",
                cellTemplate: '<div style="text-align: center;"><button type="button" style="padding: 0; border: none" className="btn btn-default btn-xs ng-binding" ng-click="grid.appScope.CopyPortaPapeles(row.entity.ticket_id)" tooltip="" title="" id="tv0" data-original-title="Copiar pedido">{{row.entity.ticket_id}}</button></div>',
                minWidth: 120,
                width: "3%",
                enableCellEdit: false,

            }, {
                name: "Fecha ingreso",
                field: "fecha_ingreso",
                cellStyle: {"text-align": "center"},
                minWidth: 70,
                width: "10%",
                enableCellEdit: false,
                cellClass: function (grid, row, col, rowRenderIndex, colRenderIndex) {
                    var date = new Date(row.entity.fecha_ingreso);
                    //Add two hours var dd = date.setHours(date.getHours() + 2);
                    // Go back 3 days var dd = date.setDate(date.getDate() - 3);
                    // One minute ago... var dd = date.setMinutes(date.getMinutes() - 1);
                    date.addMins(15);
                    //var dd = date.setMinutes(15);
                    if (date <= fechaI2) {
                        return 'blue';
                    }
                }
            }, {
                name: "Proceso",
                field: "proceso",
                cellStyle: {"text-align": "center"},
                minWidth: 70,
                width: "10%",
                enableCellEdit: false,
                cellTooltip:
                    function (row, col) {
                        return row.entity.proceso;
                    }

            }, {
                name: "Zona",
                field: "zona",
                cellStyle: {"text-align": "center"},
                minWidth: 80,
                width: "8%",
                enableCellEdit: false,
            }, {
                name: "Sub zona",
                field: "zubzona",
                cellStyle: {"text-align": "center"},
                minWidth: 70,
                width: "8%",
                enableCellEdit: false,
            }, {
                name: "Nombre técnico",
                field: "nombre_tecnico",
                cellStyle: {"text-align": "center"},
                width: "11%",
                enableCellEdit: false,
            }, {
                name: "cc técnico",
                field: "cc_tecnico",
                cellStyle: {"text-align": "center"},
                minWidth: 70,
                width: "6%",
                enableCellEdit: false,
            }, {
                name: "Tipo solicitud",
                field: "solicitud",
                cellStyle: {"text-align": "center"},
                width: "6%",
                enableCellEdit: false,
                cellTooltip:
                    function (row, col) {
                        return row.entity.solicitud;
                    }
            },
            {
                name: "Motivo",
                field: "motivo",
                cellStyle: {"text-align": "center"},
                width: "9%",
                enableCellEdit: false,
            }, {
                name: "Submotivo",
                field: "submotivo",
                cellStyle: {"text-align": "center"},
                width: "6%",
                enableCellEdit: false,
            }, {
                name: "N. nuevo técnico",
                field: "nombre_nuevo_tecnico",
                cellStyle: {"text-align": "center"},
                width: "11%",
                enableCellEdit: false,
            }, {
                name: "c. n. técnico",
                field: "cc_nuevo_tecnico",
                cellStyle: {"text-align": "center"},
                minWidth: 70,
                width: "6%",
                suppressSizeToFit: true,
                enableColumnResizing: true,
                cellFilter: 'currency:"":0',
            }, {
                name: "nivelacion",
                editType: 'dropdown',
                cellFilter: 'mapNivelacion',
                enableCellEdit: true,
                cellTemplate: "<div style='text-align: center'><select ng-model='row.entity.nivelacion' class='btn btn-default btn-xs grupo-select'>" +
                    "<option value=''>Selec</option>" +
                    "<option value='SI'>SI</option>" +
                    "<option value='NO'>NO</option>" +
                    "</select>" +
                    "</div>",

                /!*editableCellTemplate: 'ui-grid/dropdownEditor',
                editDropdownOptionsArray: [{
                    ID: 'SI',
                    type: 'SI'
                }, {
                    ID: 'NO',
                    type: 'NO'
                }],
                editDropdownIdLabel: 'ID',
                editDropdownValueLabel: 'type',*!/
                minWidth: 50,
                width: "6%",
                enableColumnResizing: true,

            }, {
                name: "Obs.",
                cellTemplate: 'partial/modals/template.html',
                width: "3%",
                enableFiltering: false,
                enableCellEdit: false,
                cellStyle: {"text-align": "center"},
            }, {
                name: "Acc.",
                cellTemplate: "<div style='text-align: center'>" +
                    '<button type="button" class="btn btn-default btn-xs" ng-click="grid.appScope.guardagestion(row)">' +
                    '<i class="fa fa-floppy-o" aria-hidden="true"> </i>' +
                    '</button>',
                minWidth: 50,
                width: "3%",
                enableFiltering: false
            }];

        var paginationOptions = {
            sort: null
        };

        $scope.gridOptions = {
            enableFiltering: false,
            enablePagination: true,
            pageSize: 200,
            enableHorizontalScrollbar: false,
            enablePaginationControls: true,
            columnDefs: columnDefs,
            paginationPageSizes: [200, 500, 1000],
            paginationPageSize: 200,
            enableRowHeaderSelection: true,

            exporterMenuPdf: false,
            enableGridMenu: false,

            useExternalPagination: true,
            useExternalSorting: true,
            enableRowSelection: true,

            exporterCsvFilename: 'Registros.csv',
            /!*            exporterPdfDefaultStyle: {fontSize: 9},
                        exporterPdfTableStyle: {margin: [30, 30, 30, 30]},
                        exporterPdfTableHeaderStyle: {fontSize: 10, bold: true, italics: true, color: 'red'},
                        exporterPdfHeader: {text: "Registros", style: 'headerStyle'},
                        exporterPdfFooter: function (currentPage, pageCount) {
                            return {text: currentPage.toString() + ' of ' + pageCount.toString(), style: 'footerStyle'};
                        },
                        exporterPdfCustomFormatter: function (docDefinition) {
                            docDefinition.styles.headerStyle = {fontSize: 22, bold: true};
                            docDefinition.styles.footerStyle = {fontSize: 10, bold: true};
                            return docDefinition;
                        },
                        exporterPdfOrientation: 'portrait',
                        exporterPdfPageSize: 'LETTER',
                        exporterPdfMaxGridWidth: 500,*!/
            exporterCsvLinkElement: angular.element(document.querySelectorAll(".custom-csv-link-location")),
            exporterExcelFilename: 'Registros.xlsx',
            exporterExcelSheetName: 'Sheet1',

            onRegisterApi: function (gridApi) {
                $scope.gridApi = gridApi;
                $scope.gridApi.core.on.sortChanged($scope, function (grid, sortColumns) {
                    if (getPage) {
                        if (sortColumns.length > 0) {
                            paginationOptions.sort = sortColumns[0].sort.direction;
                        } else {
                            paginationOptions.sort = null;
                        }
                        getPage(grid.options.paginationCurrentPage, grid.options.paginationPageSize, paginationOptions.sort)
                    }
                });
                gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
                    if (getPage) {
                        getPage(newPage, pageSize, paginationOptions.sort);
                    }
                });
            }
        };

        var getPage = function (curPage, pageSize, sort) {
            services.gestionarNivelacion(curPage, pageSize, sort).then(complete).catch(failed)

            function complete(data) {
                var datos = data.data.data;
                var counter = data.data.counter;

                $scope.gridOptions.totalItems = counter;
                var firstRow = (curPage - 1) * datos
                $scope.gridOptions.data = datos
            }

            function failed(error) {
                console.log(error);
            }

        };

        getPage(1, $scope.gridOptions.paginationPageSize, paginationOptions.sort);
    }*/

    function registrosTecnicos() {

        var columnDefs = [
            {
                name: "Tarea",
                cellTemplate: '<div style=\'text-align: center;\'><button style="border: none" type="button" className="btn btn-default btn-xs" ng-click="grid.appScope.CopyPortaPapeles(row.entity.ticket_id)" tooltip title="Copiar pedido">' +
                    '{{row.entity.ticket_id}}' +
                    '</button></div>',
                field: "ticket_id",
                minWidth: 80,
                width: "5%",
            },
            {
                name: "Proceso",
                field: "proceso",
                minWidth: 80,
                width: "10%",
            }, {
                name: "Nombre Teçnico",
                field: "nombre_tecnico",
                minWidth: 70,
                width: "15%",
            }, {
                name: "CC Técnico",
                field: "cc_tecnico",
                minWidth: 80,
                width: "7%",
            }, {
                name: "Tipo Solicitud",
                field: "solicitud",
                minWidth: 70,
                width: "8%",
            }, {
                name: "Motivo",
                field: "motivo",
                width: "13%",
            }, {
                name: "Sub Motivo",
                field: "submotivo",
                minWidth: 70,
                width: "7%",
            }, {
                name: "CC Nuevo Téc.",
                field: "cc_nuevo_tecnico",
                minWidth: 70,
                width: "8%",
            },
            {
                name: "Nombre Nuevo Tec.",
                field: "nombre_nuevo_tecnico",
                minWidth: 70,
                width: "12%",
            }, {
                name: "Nivelacion",
                field: "se_realiza_nivelacion",
                minWidth: 70,
                width: "10%",
            },
            {
                name: "Detalles",
                cellTemplate: "<div style='text-align: center'>" +
                    '<button type="button" class="btn btn-default btn-xs" ng-click="grid.appScope.DetalleTotal(row)">' +
                    '<i class="fa fa-info-circle" aria-hidden="true"> </i>' +
                    '</button>',
                minWidth: 70,
                width: "5%",
                enableFiltering: false,
            }];

        var paginationOptions = {
            sort: null
        };

        $scope.gridOptionsRegistros = {
            enableFiltering: true,
            enablePagination: true,
            pageSize: 200,
            enableHorizontalScrollbar: false,
            enablePaginationControls: true,
            columnDefs: columnDefs,
            paginationPageSizes: [200, 500, 1000],
            paginationPageSize: 200,

            useExternalPagination: true,
            useExternalSorting: true,
            enableRowSelection: true,

            enableGridMenu: true,
            enableSelectAll: true,
            exporterCsvFilename: 'Registros-nivelacion.csv',
            exporterMenuPdf: false,
            exporterCsvLinkElement: angular.element(document.querySelectorAll(".custom-csv-link-location")),
            exporterExcelFilename: 'Registros-nivelacion.xlsx',
            exporterExcelSheetName: 'Sheet1',
            onRegisterApi: function (gridApi) {
                $scope.gridApi = gridApi;
                $scope.gridApi.core.on.sortChanged($scope, function (grid, sortColumns) {
                    if (getPage) {
                        if (sortColumns.length > 0) {
                            paginationOptions.sort = sortColumns[0].sort.direction;
                        } else {
                            paginationOptions.sort = null;
                        }
                        getPage(grid.options.paginationCurrentPage, grid.options.paginationPageSize, $scope.Registros)
                    }
                });
                gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
                    if (getPage) {
                        getPage(newPage, pageSize, $scope.Registros);
                    }
                });
            }
        };

        var getPage = function (curPage, pageSize, Registros) {
            services.gestionarRegistrosNivelacion(curPage, pageSize, $scope.Registros).then(complete).catch(failed)
            function complete(data) {

                    var datos = data.data.data;
                    var counter = data.data.counter;

                    $scope.gridOptionsRegistros.totalItems = counter;
                    var firstRow = (curPage - 1) * datos
                    $scope.gridOptionsRegistros.data = datos
            }

            function failed(error) {
                console.log(error);
            }

        };

        getPage(1, $scope.gridOptionsRegistros.paginationPageSize,  $scope.Registros);
    }

    $scope.gestion_nivelacion = function () {
        getGrid();
    }

    $scope.registros_nivelacion = function () {
        window.setTimeout(function () {
            registrosTecnicos();
            $(window).resize();
            $(window).resize();

        }, 1000);
        /*setTimeout(function () {

        }, 1000);*/
    }

    $scope.DetalleTotal = function (row) {
        services.buscarhistoricoNivelacion(row.entity.ticket_id).then(complete).catch(failed)

        function complete(data) {
            if (data.data.state === 0) {
                Swal({
                    type: 'error',
                    title: data.data.msj
                })
            } else {
                $scope.nivelacion.databsucarPedido = data.data.data;
                $('#modalHistoricoNivelacion').modal('show');
                return data.data;
            }
        }

        function failed(data) {
            console.log(data)
        }
    }


    $scope.reloaddata = function () {
        getGrid();
    }

    $scope.delete = function (row) {
        console.log(row.entity)
    };

    $scope.engestion = function (row) {
        services.marcarEnGestionNivelacion(row, $rootScope.galletainfo).then(complete).catch(failed)

        function complete(data) {
            console.log(data)
            if(data.data.state == 1){
                Swal({
                    type: 'success',
                    title: data.data.msj,
                    timer: 4000
                }).then(function (){
                    $route.reload();
                })
            }else{
                Swal({
                    type: 'warning',
                    title: data.data.msj,
                    timer: 4000
                })
            }
        }

        function failed(error) {
            console.log(error)
        }
    }

    $scope.guardagestion = function (row) {
        console.log(row)
        if (!row.tipificacion) {
            Swal('Selecciona el estado de nivelación');
            return;
        }
        $scope.GestionNivelacion.observacionesNivelacion = '';
        $scope.datos = row;
        $('#editarModal').modal('show');
    }

    $scope.buscarhistoricoNivelacion = function () {
        services.buscarhistoricoNivelacion($scope.nivelacion.tarea).then(complete).catch(failed)

        function complete(data) {
            if (data.data.state === 0) {
                Swal({
                    type: 'error',
                    title: data.data.msj
                })
            } else {
                $scope.nivelacion.databsucarPedido = data.data.data;
                $('#modalHistoricoNivelacion').modal('show');
                return data.data;
            }
        }

        function failed(data) {
            console.log(data)
        }
    }

    $scope.CopyPortaPapeles = function (data) {
        var copyTextTV = document.createElement("input");
        copyTextTV.value = data;
        document.body.appendChild(copyTextTV);
        copyTextTV.select();
        document.execCommand("copy");
        document.body.removeChild(copyTextTV);
        Swal({
            type: 'info',
            title: 'Aviso',
            text: "El texto seleccionado fue copiado",
            timer: 2000
        });
    }

    $scope.guardarGestionObsNivelacion = function (data) {
        if (!data.nivelacion) {
            Swal('Selecciona el estado de nivelación');
            return;
        }
        $scope.GestionNivelacion.observacionesNivelacion = '';
        $scope.datos = data;
        $('#editarModal').modal('show');
    }

    $scope.guardaNivelacion = function () {
        $scope.datos.observaciones = $scope.GestionNivelacion.observacionesNivelacion;
        services.guardaNivelacion($scope.datos, $rootScope.galletainfo).then(complete).catch(failed)

        function complete(data) {
            if (data.data.state != 1) {
                Swal({
                    type: 'error',
                    text: data.data.msj,
                    timer: 4000
                })
            } else {
                Swal({
                    type: 'success',
                    title: data.data.msj,
                    timer: 4000
                }).then(function () {
                    $route.reload();
                })
            }
        }

        function failed(errs) {
            console.log(errs)
        }
    }

    $scope.registrosNivelacion = function () {

        var fechaini = new Date($scope.fechaini);
        var fechafin = new Date($scope.fechafin);
        var diffMs = (fechafin - fechaini);
        var diffDays = Math.round(diffMs / 86400000);

        if ($scope.Registros.fechaini === '' || $scope.Registros.fechaini === undefined) {
            Swal({
                type: 'error',
                text: 'Ingrese la fecha inicial'
            });
        } else if ($scope.Registros.fechafin === '' || $scope.Registros.fechafin === undefined) {
            Swal({
                type: 'error',
                text: 'Ingrese la fecha final'
            });
        } else if ($scope.Registros.fechafin < $scope.Registros.fechaini) {
            Swal({
                type: 'error',
                text: 'La fecha final no puede ser menor que la inicial'
            });
        } else {
            services.gestionarRegistrosNivelacion(1, 200, $scope.Registros).then(
                function (data) {
                    console.log(data, ' kokokokokokoko')
                    var datos = data.data.data;
                    var counter = data.data.counter;

                    $scope.gridOptionsRegistros.totalItems = counter;
                    //var firstRow = (curPage - 1) * datos
                    $scope.gridOptionsRegistros.data = datos
                },
                function errorCallback(response) {
                    console.log(response)
                }
            )
        }
    }

    $scope.csvNivelacion = function () {
        var fechaini = new Date($scope.fechaini);
        var fechafin = new Date($scope.fechafin);
        var diffMs = (fechafin - fechaini);
        var diffDays = Math.round(diffMs / 86400000);


        if ($scope.Registros.fechaini === '' || $scope.Registros.fechaini === undefined) {
            Swal({
                type: 'error',
                text: 'Ingrese la fecha inicial'
            });
        } else if ($scope.Registros.fechafin === '' || $scope.Registros.fechafin === undefined) {
            Swal({
                type: 'error',
                text: 'Ingrese la fecha final'
            });
        } else if ($scope.Registros.fechafin < $scope.Registros.fechaini) {
            Swal({
                type: 'error',
                text: 'La fecha final no puede ser menor que la inicial'
            });
        } else {
            services.csvNivelacion($scope.Registros).then(
                function (datos) {
                    var data = datos.data[0];
                    var array = typeof data != 'object' ? JSON.parse(data) : data;
                    var str = '';
                    var column = `ticket_id|| fecha_ingreso|| fecha_gestion|| fecha_respuesta|| nombre_tecnico|| cc_tecnico|| pedido|| proceso|| motivo|| submotivo|| zona|| subzona|| nombre_nuevo_tecnico|| cc_nuevo_tecnico|| creado_por|| gestiona_por||observaciones|| se_realiza_nivelacion \r\n`;
                    str += column;
                    for (var i = 0; i < array.length; i++) {
                        var line = '';
                        for (var index in array[i]) {
                            if (line != '') line += '||'
                            line += array[i][index];
                        }

                        str += line + '\r\n';
                    }
                    var dateCsv = new Date();
                    var yearCsv = dateCsv.getFullYear();
                    var monthCsv = (dateCsv.getMonth() + 1 <= 9) ? '0' + (dateCsv.getMonth() + 1) : (dateCsv.getMonth() + 1);
                    var dayCsv = (dateCsv.getDate() <= 9) ? '0' + dateCsv.getDate() : dateCsv.getDate();
                    var fullDateCsv = yearCsv + "-" + monthCsv + "-" + dayCsv;


                    var blob = new Blob([str]);
                    var elementToClick = window.document.createElement("a");
                    elementToClick.href = window.URL.createObjectURL(blob, {type: 'text/csv'});
                    elementToClick.download = "csvNivelacion-" + fullDateCsv + ".csv";
                    elementToClick.click();
                },

                function errorCallback(response) {
                    $scope.errorDatos = "No hay datos.";
                    $scope.csvPend = false;
                }
            )
        }
    }

    $scope.reloadNivelacion = function () {
        getGrid();
    }

}])


app.controller('contingenciasCtrl', function ($scope, $rootScope, $location, $route, $routeParams, $cookies, $cookieStore, $timeout, services, fileUpload) {
    $scope.contingencias = {};
    $scope.pedidoexiste = false;
    $scope.pedidoguardado = false;
    $scope.haypedido = false;
    $scope.equiposEntran = [];
    $scope.equiposEntran.push({}); //incializo con un input...
    $scope.equiposSalen = [];
    $scope.equiposSalen.push({});


    $scope.BuscarPedidoContingencia = function () {
        //console.log("contingencias: ",$scope.contingencias);
        //emailRegex = /^[-\w.%+]{1,64}@(?:[A-Z0-9-]{1,63}\.){1,125}[A-Z]{2,63}$/i;

        //if (emailRegex.test($scope.contingencias.correo)) {

        services.buscarPedidoSeguimiento($scope.contingencias.pedido, $scope.contingencias.producto, $scope.contingencias.remite).then(function (data) {

            /*AQUI PUNTO DE CONTROL QUE SE ESTA GUARDANDO*/
            console.log("buscarPedidoSeguimiento: ", data);
            console.log($scope.contingencias);
            $scope.GuardarContingencia($scope.contingencias);
            $scope.contingencias = {};

        }, function errorCallback(response) {
            $scope.pedidoexiste = true;
            $scope.pedidoguardado = false;

            console.log("status: ", response.status);

            if (response.status == '401') {
                $scope.mensaje = "El pedido con el producto: " + $scope.contingencias.producto + ", se encuentra pendiente, no es posible gestionar nuevamente."
            } else {
                $scope.mensaje = "El pedido se encuentra sin gestión, no es posible guardar.";
            }
            return;
        });
        //} else {
        //    alert("El correo debe tener el formato de E-mail: correo@dominio.com");
        //    return;
        //}
    }

    $scope.buscarhistoricoPedidoContingencia = function (pedido) {

        if (pedido == undefined || pedido == "") {
            swal("Debe ingresar un pedido.");
            return;
        } else {
            services.getbuscarPedidoContingencia(pedido).then(function (data) {
                    $scope.databsucarPedido = data.data[0];
                    //$scope.hayDatosPedido = true;
                    //console.log("paso->1");
                    //console.log($scope.databsucarPedido);
                    if ($scope.databsucarPedido == "<") {
                        swal("No hay contingencias para el pedido: " + pedido);
                    } else {

                        $('#modalHistoricoContingencias').modal('show');
                        //console.log("paso-->2");
                    }

                    return data.data;

                    //console.log("paso-->3");

                },
                function errorCallback(response) {

                    swal("No hay contingencias para el pedido: " + pedido);

                });

        }
    }

    $scope.addEquipoEntra = function () {
        $scope.equiposEntran.push({});
    }


    $scope.addEquipoSale = function () {
        $scope.equiposSalen.push({});
    }


    $scope.updateEnGestion = function () {
        services.UpdatePedidosEngestion($rootScope.galletainfo).then(function (data) {
            $scope.haypedido = true;
            $scope.pedidosEngestion = data.data[0];

            /*AQUI PARA REVISAR COMO ESTA LLEGANDO LA INFORMACION AL DESPACHADOR*/
            //console.log("pedidosEngestion: ",$scope.pedidosEngestion);

            var tam = $scope.pedidosEngestion.length;
            $scope.contingenciaOK = 0;
            $scope.contingenciaPend = 0;
            $scope.contingenciaNO = 0;
            $scope.contingenciaNOCP = 0;

            for (var i = 0; i < tam; i++) {

                if ($scope.pedidosEngestion[i].acepta == 'Acepta') {
                    $scope.contingenciaOK = +$scope.contingenciaOK + 1;
                }
                ;
                if ($scope.pedidosEngestion[i].acepta == 'Rechaza') {
                    $scope.contingenciaNO = +$scope.contingenciaNO + 1;
                }
                ;
                if ($scope.pedidosEngestion[i].aceptaPortafolio == 'Rechaza') {
                    $scope.contingenciaNOCP = +$scope.contingenciaNOCP + 1;
                }
                ;
                if ($scope.pedidosEngestion[i].acepta == 'Pendiente' && $scope.pedidosEngestion[i].aceptaPortafolio == 'Pendiente') {
                    $scope.contingenciaPend = +$scope.contingenciaPend + 1;
                }
                ;
                if ($scope.pedidosEngestion[i].acepta == 'Pendiente' && $scope.pedidosEngestion[i].aceptaPortafolio == 'Acepta') {
                    $scope.contingenciaOK = +$scope.contingenciaOK + 1;
                }
                ;
            }
            return data.data;
        }, function errorCallback(response) {
            $scope.haypedido = false;
            $scope.mensaje = "No tiene pedidos pendientes!!!"
        });
    }


    $scope.GuardarContingencia = function (contingencias) {
        $scope.pedidoguardado = true;
        $scope.pedidoexiste = false;
        $scope.mensaje = "Pedido guardado con exito.";

        var equiposIn = "";

        //console.log("equiposEntran: ", $scope.equiposEntran);
        var sep = "";

        for (var equipo of $scope.equiposEntran) {
            if (equipo.value == undefined || equipo.value == "undefined") continue;
            else {
                equiposIn = equiposIn + sep + equipo.value;
                sep = "-";
            }
        }
        contingencias.macEntra = equiposIn;
        $scope.equiposEntran = [];
        $scope.equiposEntran.push({});
        //console.log("Listado final de equipos entran: " + equiposIn);


        var equiposOut = "";

        sep = "";
        for (var equipo of $scope.equiposSalen) {
            if (equipo.value == undefined || equipo.value == "undefined") continue;
            else {
                equiposOut = equiposOut + sep + equipo.value;
                sep = "-";
            }
        }
        contingencias.macSale = equiposOut;
        $scope.equiposSalen = [];
        $scope.equiposSalen.push({});
        //console.log("equiposSalen: ", $scope.equiposSalen);

        services.guardarContingencia(contingencias, $rootScope.galletainfo).then(function (data) {
            console.log("guardarContingencia: ", services.guardarContingencia);
        });

        $scope.updateEnGestion();
    }
    //console.log("GuardarContingencia: ",$scope.GuardarContingencia);

    //INICIO CANCELAR CONTINGENCIA POR EL DESPACHADOR

    $scope.CancelarContingencia = function (data) {

        Swal.fire({
            title: '¿Está seguro que desea cancelar la contigencia?',
            text: "no prodrás revertir esto!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, Borrar Ahora!'
        }).then((result) => {
            if (result.value) {

                services.CancelContingencia(data, $rootScope.galletainfo).then(
                    function (respuesta) {

                        if (respuesta.status == 201) {

                            Swal.fire(
                                'Cancelada!',
                                'La contingencia ha sido Cancelada. Actualiza la página',
                                'success'
                            )

                        } else if (respuesta.status == 200) {
                            Swal({
                                type: 'error',
                                title: 'La Contigencia no se puede cancelar',
                                text: 'Ya se inicio gestión por parte del personal encargado',
                                footer: 'Debes esperar que finalice la gestión'
                            })
                        }
                    },
                    function errorCallback(response) {

                        if (response.status == 400) {
                            Swal({
                                type: 'error',
                                title: 'Oops...',
                                text: 'La Contingencia no se Canceló',
                                footer: '¡Intenta de nuevo si persiste falla reporta con el administrador!'
                            })
                        }
                    }
                )
            }
        })
    }

    //FIN CANCELAR CONTINGENCIA POR EL DESPACHADOR

    $scope.updateContingencias = setInterval(function () {
        //console.log($scope.indicadores);
        $scope.updateEnGestion();
    }, 300000);

    $scope.$on(
        "$destroy",
        function (event) {
            $timeout.cancel();
            clearInterval($scope.updateContingencias);
        });

    $scope.updateEnGestion();

});


app.controller('GestioncontingenciasCtrl', function ($scope, $rootScope, $location, $route, $routeParams, $cookies, $cookieStore, $timeout, services, fileUpload) {
    $scope.rutaCierreMasivoContin = "partial/modals/cierreMasivoContingencias.html";
    $scope.haypedidoOtros = false;
    $scope.haypedidoTV = false;
    $scope.loadingData = false;
    $scope.haypedidoPortafolio = false;
    $scope.haypedidoCEQPortafolio = false;
    $scope.status = true;
    $scope.sinPedido = false;
    $scope.isContingenciesFromField = false;
    $scope.contingenciesDataTV = [];
    $scope.contingenciesDataInternetToIP = [];
    $scope.contingenciasTV = [];
    $scope.contingenciasOTROS = [];
    //var database = firebase.firestore();
    $scope.cantidadContingenciasTV = 0;
    $scope.cantidadContingenciasINT = 0;


    $scope.listarcontingenciasterreno = () => {

        /* database.collection("contingencies").where("status","==", 0).orderBy("contingencies_Date","asc").get().then((querySnapshot) => {
            $scope.isContingenciesFromField = false;
            $scope.contingenciesDataTV = [];
            $scope.contingenciesDataInternetToIP = [];
            $scope.cantidadContingenciasTV = 0;
            $scope.cantidadContingenciasINT = 0;
            querySnapshot.forEach((doc) => {
                let dataQuerySnapshot = {};
                dataQuerySnapshot = {
                    _id: doc.id,
                    references: doc.data().references,
                    macIn: doc.data().macIn,
                    macOut: doc.data().macOut,
                    details: doc.data().details,
                    contingencies_Type: doc.data().contingencies_Type,
                    contingencies_Date: doc.data().contingencies_Date,
                    contingencies_State: doc.data().contingencies_State,
                    product: doc.data().product,
                    user_email: doc.data().user_email,
                    user_identification: doc.data().user_identification
                }
                special_access = [98644514,15335939,16268236,70191900
                    ,71638399,10137123,71789293,98621522,70254182
                    ,98567429,71733363,71669597,98593889,71653347
                    ,70753885,70954170,70384636,1094947317,71625390];

                dataQuerySnapshot.correo = dataQuerySnapshot.user_email;
                var date = dataQuerySnapshot.contingencies_Date.toDate();
                var year = date.getFullYear();
                var month = "0" + (date.getMonth() + 1);
                var day = "0" + date.getDate();
                var hours = "0" + date.getHours();
                var minutes = "0" + date.getMinutes();
                var seconds = "0" + date.getSeconds();
                var formattedTime = year + '-' + month.substr(-2) + '-' + day.substr(-2) + ' ' + hours.substr(-2) + ':' + minutes.substr(-2) + ':' + seconds.substr(-2);
                dataQuerySnapshot.horagestion = formattedTime;
                dataQuerySnapshot.fecha = date;
                dataQuerySnapshot.macEntra = dataQuerySnapshot.macIn;
                dataQuerySnapshot.macSale = dataQuerySnapshot.macOut;
                dataQuerySnapshot.observacion = dataQuerySnapshot.details;
                dataQuerySnapshot.pedido = dataQuerySnapshot.references;
                dataQuerySnapshot.producto = dataQuerySnapshot.product;
                dataQuerySnapshot.accion = dataQuerySnapshot.contingencies_Type;

                if(special_access.indexOf(dataQuerySnapshot.user_identification) != -1){
                    dataQuerySnapshot.remite = "Teléfonos Públicos";
                } else {
                    dataQuerySnapshot.remite = "Terreno";
                }

                if(dataQuerySnapshot.product == "TV"){
                    $scope.contingenciasTV.push(dataQuerySnapshot);
                    $scope.contingenciesDataTV.push(dataQuerySnapshot);
                    $scope.cantidadContingenciasTV++;
                }
                else if(dataQuerySnapshot.product == "Internet" || dataQuerySnapshot.product == "ToIP" || dataQuerySnapshot.product == "Internet+ToIP"){
                    $scope.contingenciasOTROS.push(dataQuerySnapshot);
                    $scope.contingenciesDataInternetToIP.push(dataQuerySnapshot);
                    $scope.cantidadContingenciasINT++;
                }
            });
            $scope.isContingenciesFromField = true;

            if($scope.contingenciasTV.length == 0 ){
                $scope.contingenciasTV = $scope.contingenciasTV.concat($scope.contingenciesDataTV);
            }
            if($scope.contingenciasOTROS.length == 0){
                $scope.contingenciasOTROS = $scope.contingenciasOTROS.concat($scope.contingenciesDataInternetToIP);
            }
        }).catch( (err) => {
            console.log(err);
        }); */


    }

    var tiempo = new Date().getTime();
    var date1 = new Date();
    var year = date1.getFullYear();
    var month = (date1.getMonth() + 1 <= 9) ? '0' + (date1.getMonth() + 1) : (date1.getMonth() + 1);
    var day = (date1.getDate() <= 9) ? '0' + date1.getDate() : date1.getDate();

    tiempo = year + "-" + month + "-" + day;

    $scope.fechaupdateInical = tiempo;
    $scope.fechaupdateFinal = tiempo;


    $scope.changeStatus = function (data) {
        //console.log("Aceptado/Rechazado: ",data);
    }

    /*$scope.palancatv = function(data,idxtv)
    {
        var TIPITV = $scope.contingenciasTV.map((doc)=>doc.tipificacion);
        var PALANCATV= $scope.contingenciasTV.map((doc)=>doc.acepta);

        if(TIPITV[idxtv]=="Ok") //Si la tipificación es "Ok" pone el acepta en "Si"
        {
            acepta[idxtv].click();
        }
        else if(PALANCATV[idxtv]==true) //cuando se dio ok y luego se rechaza por cualquier motivo pone el acepta en no
        {
            acepta[idxtv].click();
        }
        else //Cuando se rechaza directamente por cualquier motivo pone el acepta en no
        {
            acepta[idxtv].checked=false;
        }
    }

    $scope.palancaotro = function(data,idxotros)
    {
        var TIPIOTROS = $scope.contingenciasOTROS.map((doc)=>doc.tipificacion);
        var PALANCAOTROS= $scope.contingenciasOTROS.map((doc)=>doc.acepta);

        if(TIPIOTROS[idxotros]=="Ok") //Si la tipificación es "Ok" pone el acepta en "Si"
        {
            aceptaotro[idxotros].click();
        }
        else if(PALANCAOTROS[idxotros]==true) //cuando se dio ok y luego se rechaza por cualquier motivo pone el acepta en no
        {
            aceptaotro[idxotros].click();
        }
        else //Cuando se rechaza directamente por cualquier motivo pone el acepta en no
        {
            aceptaotro[idxotros].checked=false;
        }
    }

    $scope.palancacp = function(data,idxcp)
    {
        var TIPICP = $scope.contingenciasPortafolio.map((doc)=>doc.tipificacionPortafolio);
        var PALANCACP= $scope.contingenciasPortafolio.map((doc)=>doc.aceptaPortafolio);

        if(TIPICP[idxcp]=="Ok") //Si la tipificación es "Ok" pone el acepta en "Si"
        {
            aceptacp[idxcp].click();
        }
        else if(PALANCACP[idxcp]==true) //cuando se dio ok y luego se rechaza por cualquier motivo pone el acepta en no
        {
            aceptacp[idxcp].click();
        }
        else //Cuando se rechaza directamente por cualquier motivo pone el acepta en no
        {
            aceptacp[idxcp].checked=false;
        }
    }*/

    $scope.gestioncontingencias = function () {
        $scope.loadingData = true;

        // $scope.contingenciasTV = [];
        // $scope.contingenciasOTROS = [];
        $scope.listarcontingenciasterreno();

        services.datosgestioncontingencias().then(function (data) {

            //console.log("datosgestioncontingencias: ", data);
            $scope.loadingData = false;

            $scope.contingenciasTV = $scope.contingenciesDataTV.concat(data.data[0]);
            $scope.contingenciasOTROS = $scope.contingenciesDataInternetToIP.concat(data.data[1]);

            $scope.contingenciasPortafolio = data.data[2];

            var TV = $scope.contingenciasTV.map((doc) => doc.horagestion);
            var OTROS = $scope.contingenciasOTROS.map((doc) => doc.horagestion);
            var CPORTAFOLIO = $scope.contingenciasPortafolio.map((doc) => doc.horagestion);

            /*Se formatea la hora del sistema*/
            function js_yyyy_mm_dd_hh_mm_ss() {
                now = new Date();
                year = "" + now.getFullYear();
                month = "" + (now.getMonth() + 1);
                if (month.length == 1) {
                    month = "0" + month;
                }
                day = "" + now.getDate();
                if (day.length == 1) {
                    day = "0" + day;
                }
                hour = "" + now.getHours();
                if (hour.length == 1) {
                    hour = "0" + hour;
                }
                minute = "" + now.getMinutes();
                if (minute.length == 1) {
                    minute = "0" + minute;
                }
                second = "" + now.getSeconds();
                if (second.length == 1) {
                    second = "0" + second;
                }
                return year + "-" + month + "-" + day + " " + hour + ":" + minute + ":" + second;
            }

            $scope.hora_sistema = js_yyyy_mm_dd_hh_mm_ss();

            /*Se recorre el arreglo restando la hora del sistema con la hora de llegada,
            si es mayor a 15 minutos se almacena el indice de la contingencia mas reciente
            que cumplio los 15 minutos*/
            TV.forEach(function (valor, indice) {

                $scope.diferencia = new Date(js_yyyy_mm_dd_hh_mm_ss()) - new Date(TV[indice]);

                if ($scope.diferencia > 900000) {
                    $scope.indice = (indice);
                    $scope.quinceminutos = new Array();
                    $scope.quinceminutos[$scope.indice] = TV[$scope.indice];

                }
            });

            OTROS.forEach(function (valor, indice) {

                $scope.diferencia = new Date(js_yyyy_mm_dd_hh_mm_ss()) - new Date(OTROS[indice]);

                if ($scope.diferencia > 900000) {
                    $scope.indice = (indice);
                    $scope.quinceminutos = new Array();
                    $scope.quinceminutos[$scope.indice] = OTROS[$scope.indice];
                }
            });

            CPORTAFOLIO.forEach(function (valor, indice) {

                $scope.diferencia = new Date(js_yyyy_mm_dd_hh_mm_ss()) - new Date(CPORTAFOLIO[indice]);

                if ($scope.diferencia > 900000) {
                    $scope.indice = (indice);
                    $scope.quinceminutos = new Array();
                    $scope.quinceminutos[$scope.indice] = CPORTAFOLIO[$scope.indice];
                }
            });

            if ($scope.contingenciasTV.length !== 0) {
                //console.log("contingenciasTV: ",$scope.contingenciasTV[0]);
                $scope.haypedidoTV = true;
            } else {
                $scope.haypedidoTV = false;
                $scope.mensaje = "No hay pedidos para gestionar!!!";
            }

            if ($scope.contingenciasOTROS.length !== 0) {
                //console.log("contingenciasOTROS: ",$scope.contingenciasOTROS[0]);
                $scope.haypedidoOtros = true;
            } else {
                $scope.haypedidoOtros = false;
                $scope.mensajeotros = "No hay pedidos para gestionar!!!";
            }

            /*CONDICIONAL PARA RECORRER TODA LA DATA*/
            if ($scope.contingenciasPortafolio.length !== 0) {
                //console.log("contingenciasPortafolio: ",$scope.contingenciasPortafolio[0]);
                $scope.haypedidoPortafolio = true;
            } else {
                $scope.haypedidoPortafolio = false;
                $scope.mensajeotros = "No hay pedidos prioritarios!!!";
            }


            return data.data;
        }).catch(function (err) {
            console.log(err);
            $scope.contingenciasTV = [];
            $scope.contingenciasOTROS = [];
            $scope.listarcontingenciasterreno();
            $scope.loadingData = false;
        });

    }


    $scope.CopyPortaPapeles = function (data) {
        var copyTextTV = document.createElement("input");
        copyTextTV.value = data;
        document.body.appendChild(copyTextTV);
        copyTextTV.select();
        document.execCommand("copy");
        document.body.removeChild(copyTextTV);
        Swal({
            type: 'info',
            title: 'Aviso',
            text: "El texto seleccionado fue copiado",
            timer: 2000
        });
    }

    $scope.autocompletarContingencia = async (data) => {
        var contingencia = {};
        try {
            var autocompleteQuery = await fetch('http://10.100.66.254:8080/HCHV_DEV/BuscarB/' + data.references);
            var autocompleteData = await autocompleteQuery.json();
            var equiposIn = "";
            var equiposOut = "";
            var sep = "";
            // servicios bb8
            // if (data.product == "TV"){
            //     var querytvmss = await fetch('http://10.100.66.254:7776/api/plan/tvmss/' + data.references);
            //     var querytvmssData = await querytvmss.json();
            //     contingencia.paquete = querytvmssData[0][0];

            // }
            // else if(data.product == "Internet"){
            //     var querytoipmss = await fetch('http://10.100.66.254:7776/api/plan/toipmss/' + data.references);
            //     var querytoipmssData = await querytoipmss.json();
            //     var queryBaMSS = await fetch('http://10.100.66.254:7776/api/plan/bamss/' + data.references);
            //     var queryBaMSSData = await queryBaMSS.json();
            //     contingencia.paquete = querytoipmssData[1][3];
            //     contingencia.perfil = queryBaMSSData[0][0];
            // }
            // Homologación
            contingencia.accion = data.contingencies_Type;
            contingencia.ciudad = (autocompleteData.uNEMunicipio) ? autocompleteData.uNEMunicipio.toUpperCase() : "";
            contingencia.correo = data.user_email;
            contingencia.fecha = data.contingencies_Date.toDate();
            contingencia.macEntra = data.macIn;
            contingencia.macSale = data.macOut;
            contingencia.observacion = data.details;
            contingencia.pedido = data.references;
            contingencia.proceso = autocompleteData.TaskType;
            contingencia.remite = data.remite;

            contingencia.producto = data.product;
            contingencia.uen = autocompleteData.uNEUen;
            contingencia._id = data._id;

            for (var equipo of contingencia.macEntra) {
                if (equipo.name == undefined || equipo.name == "undefined") continue;
                else {
                    equiposIn = equiposIn + sep + equipo.name;
                    sep = "-";
                }
            }
            sep = "";
            contingencia.macEntra = equiposIn;

            for (var equipo of contingencia.macSale) {
                if (equipo.name == undefined || equipo.name == "undefined") continue;
                else {
                    equiposOut = equiposOut + sep + equipo.name;
                    sep = "-";
                }
            }
            contingencia.macSale = equiposOut;

            if (data.remite != "Teléfonos Públicos") {
                if ((autocompleteData.Type == "Install" || autocompleteData.Type == "Traslado") && (autocompleteData.RTA == 'NA' || autocompleteData.RTA == 'N')) {
                    if (data.user_identification != autocompleteData.engineerID) {
                        swal({
                            title: "Aviso Importante: ",
                            html: "El pedido no concuerda con el técnico que solicita su gestión ni tampoco ha sido gestionado a través de Click.",
                            type: "warning",
                            showCancelButton: false,
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'Sí, Lo tengo presente!'
                        });
                    } else {
                        swal({
                            title: "Aviso Importante: ",
                            html: "El pedido no ha sido gestionado a través Click.",
                            type: "warning",
                            showCancelButton: false,
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'Sí, Lo tengo presente!'
                        });
                    }
                } else if (autocompleteData.Type == "Repair" && (autocompleteData.MAC == '' || autocompleteData.MAC == null) && autocompleteData.RTA3 == null) {
                    if (data.user_identification != autocompleteData.engineerID) {
                        swal({
                            title: "Aviso Importante: ",
                            html: "El pedido no concuerda con el técnico que solicita su gestión ni tampoco ha sido gestionado a través de Click.",
                            type: "warning",
                            showCancelButton: false,
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'Sí, Lo tengo presente!'
                        });
                    } else {
                        swal({
                            title: "Aviso Importante: ",
                            html: "El pedido no ha sido gestionado a través Click.",
                            type: "warning",
                            showCancelButton: false,
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'Sí, Lo tengo presente!'
                        });
                    }
                } else {
                    if (autocompleteData.Description == null || autocompleteData.LaborType == '1166073856') {
                        if (data.user_identification != autocompleteData.engineerID) {
                            swal({
                                title: "Aviso Importante: ",
                                html: "El pedido no concuerda con el técnico que solicita su gestión ni tampoco ha sido gestionado a través de Sara.",
                                type: "warning",
                                showCancelButton: false,
                                confirmButtonColor: '#3085d6',
                                confirmButtonText: 'Sí, Lo tengo presente!'
                            });
                        } else {
                            swal({
                                title: "Aviso Importante: ",
                                html: "El pedido no ha sido gestionado a través de Sara.",
                                type: "warning",
                                showCancelButton: false,
                                confirmButtonColor: '#3085d6',
                                confirmButtonText: 'Sí, Lo tengo presente!'
                            });
                        }
                    } else if (data.user_identification != autocompleteData.engineerID) {
                        swal({
                            title: "Aviso Importante: ",
                            html: "El pedido no concuerda con el técnico que solicita su gestión.",
                            type: "warning",
                            showCancelButton: false,
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'Sí, Lo tengo presente!'
                        });
                    }
                }

                if (autocompleteData.Type == null) {
                    if (data.user_identification != autocompleteData.engineerID) {
                        swal({
                            title: "Aviso Importante: ",
                            html: "El pedido no concuerda con el técnico que solicita su gestión y no ha sido diligenciado en click.",
                            type: "warning",
                            showCancelButton: false,
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'Sí, Lo tengo presente!'
                        });
                    } else {
                        swal({
                            title: "Aviso Importante: ",
                            html: "El pedido no ha sido diligenciado en click.",
                            type: "warning",
                            showCancelButton: false,
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'Sí, Lo tengo presente!'
                        });
                    }
                }
            }

            var queryIsAlreadyToken = database.collection("contingencies").doc(data._id);
            var querySnapshotAT = await queryIsAlreadyToken.get();
            if (querySnapshotAT.data().status == 1) {
                swal({
                    title: "Este pedido ya ha sido tomado: ",
                    html: `El pedido ${data.pedido} que ha seleccionado, ya ha sido tomado.`,
                    type: "warning"
                });
            } else {
                var queryUpdateStatus = await database.collection("contingencies").doc(data._id).update({status: 1});
                var querySaveContingency = await services.guardarContingencia(contingencia, $rootScope.galletainfo);
                $scope.contingenciasTV = [];
                $scope.contingenciasOTROS = [];
                // $scope.gestioncontingencias();
            }

        } catch (error) {
            swal({
                title: "Información Pedido: ",
                html: "No encontrado",
                type: "warning"
            });
            console.log(error);
            return;
        }


    }

    $scope.guardarcontingencia = function (data) {
        //console.log("guardarcontingencia: ",data);
        if (data.engestion == null) {
            alert("Debes bloquear el pedido");
            return;
        } else if (data.tipificacion == undefined) {
            alert("Recuerda seleccionar todas las opciones!!");
            return;
        } else {
            $scope.gestioncontin = data;
            //console.log("gestioncontin: ",$scope.gestioncontin);
            $('#editarModal').modal('show');
            return data.data;
        }
    }

    $scope.guardarpedido = function (data) {
        //console.log("guardarpedido: ",data);
        console.log(data);
        if (data.logincontingencia == null) {
            alert("Debes de marcar la contingencia, antes de guardar!");
        } else {
            if (!data.observacionescontingencia) {
                alert("Debes ingresar las observaciones.");
                return;
            } else {
                alert("Pedido guardado, recuerda actualizar!!");
                //console.log("Antes de pasar a appi: ",data);
                if (data.id_terreno != null && data.id_terreno != undefined && data.id_terreno != "") {
                    var currentTimeDate = new Date().toLocaleString();
                    var statusContingencieField = (data.tipificacion == "Ok") ? "Aprobado" : "Rechazado";
                    /*database.collection("contingencies").doc(data.id_terreno).update({
                        answer: data.observacionescontingencia,
                        answer_Date: currentTimeDate,
                        contingencies_State: statusContingencieField
                    });*/
                }
                services.editarregistrocontingencia(data, $rootScope.galletainfo)
                    .then(function (data) {

                    })
                    .catch(err => alert(err));
                $scope.gestioncontingencias();
                //$scope.gestioncontingenciasPrueba();
            }
        }
    }

    $scope.marcarEngestion = async (data) => {

        // Bloque de condición para contingencias desde terreno.
        if (data._id != null && data._id != undefined) {
            try {
                data.pedido = data.references;
                data.producto = data.product;
                await $scope.autocompletarContingencia(data);
            } catch (error) {
                return swal({
                    title: "Aviso Importante: ",
                    html: "El pedido no fue desbloqueado.",
                    type: "error",
                });
            }
        }

        services.marcarengestion(data, $rootScope.galletainfo).then(function (data) {

            //console.log("marcarengestion: ",data);

            if (data.data !== "") {
                if (data.data[0] == "desbloqueado") {
                    $scope.respuestaMarca = data.data[0][0];
                    //console.log("respuestaMarcaDesbloqueado: ",$scope.respuestaMarca);
                    swal({
                        title: "Pedido Desbloqueado",
                        type: "success",
                        position: 'center',
                        showConfirmButton: false,
                        timer: 3000
                    });
                    $scope.gestioncontingencias();
                    //$scope.gestioncontingenciasPrueba();
                    return;
                } else {
                    $scope.respuestaMarca = data.data[0][0];
                    //console.log("respuestaMarcaOcupado: ",$scope.respuestaMarca);
                    swal({
                        title: "El pedido se encuentra bloqueado",
                        type: "warning",
                        position: 'center',
                        showConfirmButton: false,
                        timer: 3000
                    });
                    $scope.gestioncontingencias();
                    //$scope.gestioncontingenciasPrueba();
                    return;
                }
            } else if (data.data == "") {
                $scope.respuestaMarca = "";
                swal({
                    title: "Pedido Bloqueado",
                    type: "success",
                    position: 'center',
                    showConfirmButton: false,
                    timer: 3000
                });
                $scope.gestioncontingencias();
                //$scope.gestioncontingenciasPrueba();
                return;
            }
        })
            .catch(err => console.log(err));
    }

    /*INICIO DEL BLOQUE QUE GUARDAR LA GESTION DE CONTINGENCIA CORREGIR PORTAFOLIO*/

    $scope.guardarContingenciaPortafolio = function (data) {
        //console.log("guardarContingenciaPortafolio: ",data);
        if (data.enGestionPortafolio == 0) {
            alert("Debes bloquear el pedido");
            return;
        } else if (data.tipificacionPortafolio == "") {
            alert("Recuerda seleccionar todas las opciones!!");
            return;
        } else {
            $scope.gestioncontinPortafilio = data;
            //console.log("gestioncontinPortafilio: ",$scope.gestioncontinPortafilio);
            $('#editarModalPortafolio').modal('show');
            return data.data;
        }
    }

    $scope.guardarpedidoPortafolio = function (data) {
        //console.log("guardarpedidoPortafolio: ",data);
        if (!data.observContingenciaPortafolio) {
            alert("Debes ingresar las observaciones.");
            return;
        } else {
            alert("Pedido guardado, recuerda actualizar!!");
            //console.log("Antes de pasar a appi: ",data);
            services.editarRegistroContingenciaPortafolio(data, $rootScope.galletainfo).then(function (data) {
            });
            $scope.gestioncontingencias();
            //$scope.gestioncontingenciasPrueba();
        }
    }

    $scope.marcarEnGestionPortafolio = function (data) {
        //console.log("marcarEnGestionPortafolio: ",data);
        services.marcarEnGestionPorta(data, $rootScope.galletainfo).then(function (data) {

            //console.log("marcarEnGestionPorta: ",data.data[0]);
            //console.log("marcarEnGestionPorta: ",data.config.data.login.LOGIN);

            if (data.data !== "") {
                if (data.data[0] == "desbloqueado") {
                    $scope.respuestaMarca = data.data[0][0];
                    //console.log("respuestaMarcaDesbloqueado: ",$scope.respuestaMarca);
                    alert("Pedido desbloqueado!!");
                    $scope.gestioncontingencias();
                    return;
                } else {
                    $scope.respuestaMarca = data.data[0][0];
                    //console.log("respuestaMarcaOcupado: ",$scope.respuestaMarca);

                    if ($scope.respuestaMarca.logincontingencia) {
                        alert("El pedido se encuentra bloqueado.");
                    } else {
                        alert("El pedido se encuentra bloqueado.");
                    }

                    $scope.gestioncontingencias();
                    //$scope.gestioncontingenciasPrueba();
                    return;
                }
            } else if (data.data == "") {
                $scope.respuestaMarca = "";
                alert("Pedido bloqueado!!!");
                $scope.gestioncontingencias();
                //$scope.gestioncontingenciasPrueba();
                return;
            }
        });
    }

    /*POR SI SE ME DAÑA EL BUENO*/
    // $scope.marcarEnGestionPortafolio = function (data) {
    //     console.log("marcarEnGestionPortafolio: ",data);
    //     services.marcarEnGestionPorta(data, $rootScope.galletainfo).then(function (data) {

    //         console.log("marcarEnGestionPorta: ",data.data[0]);

    //         if (data.data !== "") {
    //             if (data.data[0] == "desbloqueado") {
    //                 $scope.respuestaMarca = data.data[0][0];
    //                 console.log("respuestaMarcaDesbloqueado: ",$scope.respuestaMarca);
    //                 alert("Pedido desbloqueado!!");
    //                 $scope.gestioncontingencias();
    //                 //$scope.gestioncontingenciasPrueba();
    //                 return;
    //             } else {
    //                 $scope.respuestaMarca = data.data[0][0];
    //                 console.log("respuestaMarcaOcupado: ",$scope.respuestaMarca);
    //                 alert("El pedido se encuentra bloqueado por: " + $scope.respuestaMarca.logincontingencia);
    //                 $scope.gestioncontingencias();
    //                 //$scope.gestioncontingenciasPrueba();
    //                 return;
    //             }
    //         } else if (data.data == "") {
    //             $scope.respuestaMarca = "";
    //             alert("Pedido bloqueado!!!");
    //             $scope.gestioncontingencias();
    //             //$scope.gestioncontingenciasPrueba();
    //             return;
    //         }
    //     });
    // }

    // $scope.marcarEnGestionCEQPortafolio = function (data) {
    //      console.log("marcarEnGestionCEQPortafolio: ",data);
    //      services.marcarEnGestionCEQPorta(data, $rootScope.galletainfo).then(function (data) {

    //          //console.log("marcarEnGestionPorta: ",data.data[0]);
    //          console.log("marcarEnGestionCEQPorta: ",data.config.data.login.LOGIN);

    //          if (data.data !== "") {
    //              if (data.data[0] == "desbloqueado") {
    //                  $scope.respuestaMarca = data.data[0][0];
    //                  console.log("respuestaMarcaCEQDesbloqueado: ",$scope.respuestaMarca);
    //                  alert("Pedido desbloqueado!!");
    //                  $scope.gestioncontingencias();
    //                  return;
    //              } else {
    //                  $scope.respuestaMarca = data.data[0][0];
    //                  console.log("respuestaMarcaOcupadoCEQ: ",$scope.respuestaMarca);

    //                  if ($scope.respuestaMarca.logincontingencia ){
    //                    alert("El pedido se encuentra bloqueado por: " + $scope.respuestaMarca.logincontingencia);
    //                  } else {
    //                    alert("El pedido se encuentra bloqueado por: " + $scope.respuestaMarca.loginContingenciaPortafolio);
    //                  }

    //                  $scope.gestioncontingencias();
    //                  //$scope.gestioncontingenciasPrueba();
    //                  return;
    //              }
    //          } else if (data.data == "") {
    //              $scope.respuestaMarca = "";
    //              alert("Pedido bloqueado!!!");
    //              $scope.gestioncontingencias();
    //              //$scope.gestioncontingenciasPrueba();
    //              return;
    //          }
    //      });
    //  }

    /*FIN DEL BLOQUE QUE GUARDAR LA GESTION DE CONTINGENCIA CORREGIR PORTAFOLIO*/

    $scope.buscarPedidoContingencia = function (pedido) {
        console.log("pedido: ", pedido);

        if (pedido == undefined || pedido == "") {
            alert("Debe ingresar un pedido");
            return;
        } else {
            services.getbuscarPedidoContingencia(pedido).then(function (data) {
                console.log("getbuscarPedidoContingencia: ", data);
                $scope.databsucarPedido = data.data[0];
                console.log("databsucarPedido: ", $scope.databsucarPedido);
                $scope.sinPedido = true;
                return data.data;
            }, function errorCallback(response) {
                $scope.sinPedido = false;
                $scope.mensajeBuscar = "No hay contingencia para el pedido: " + pedido;
            });

        }
    }


    $scope.resumenContingencias = function (fechaInicial, fechafinal) {
        services.getresumenContingencias(fechaInicial, fechafinal).then(function (data) {

            //console.log("resumenContingencias: ",data);

            $scope.dataresumenContingencias = data.data[0];
            //console.log("dataresumenContingencias: ",$scope.dataresumenContingencias);

            $scope.dataresumenContingenciasCP = data.data[5];
            //console.log("dataresumenContingenciasCP: ",$scope.dataresumenContingenciasCP);

            $scope.dataresumenContingenciasTV = data.data[6];
            //console.log("dataresumenContingenciasTV: ", $scope.dataresumenContingenciasTV);

            $scope.dataresumenContingenciasInTo = data.data[7];
            //console.log("dataresumenContingenciasInTo: ",$scope.dataresumenContingenciasInTo);

            /*TRAE LA ARRAY CON EL ESTADO Y LA CANTIDAD $resultadoestadosMes*/
            $scope.estados = data.data[1];
            //console.log("estados: ",$scope.estados);

            /*TRAE LA ARRAY CON EL ESTADO Y LA CANTIDAD $queryestadosMesCP*/
            $scope.estadosCP = data.data[3];
            //console.log("estadosCP: ",$scope.estados);

            $scope.dia = data.data[2];
            //console.log("dia: ",$scope.dia);
            $scope.diaCP = data.data[4];
            //console.log("diaCP: ",$scope.diaCP);

            /*CONTADOR DIARIO PARA GENERAL*/
            var tam = $scope.dataresumenContingencias.length;
            $scope.Totaltotal_pedidos_aceptados = 0;
            $scope.Totaltotal_pedidos_pendientes = 0;
            $scope.Totaltotal_pedidos_rechazados = 0;

            for (var i = 0; i < tam; i++) {

                if ($scope.dataresumenContingencias[i].estado == 'Acepta') {
                    $scope.Totaltotal_pedidos_aceptados = +$scope.Totaltotal_pedidos_aceptados + 1;
                }
                ;
                if ($scope.dataresumenContingencias[i].estado == 'Rechaza') {
                    $scope.Totaltotal_pedidos_rechazados = +$scope.Totaltotal_pedidos_rechazados + 1;
                }
                ;
                if ($scope.dataresumenContingencias[i].estado == 'Pendiente') {
                    $scope.Totaltotal_pedidos_pendientes = +$scope.Totaltotal_pedidos_pendientes + 1;
                }
                ;
            }

            /*CONTADOR DIARIO PARA TV*/
            var tam1 = $scope.dataresumenContingenciasTV.length;
            $scope.Totaltotal_pedidos_aceptadosTV = 0;
            $scope.Totaltotal_pedidos_pendientesTV = 0;
            $scope.Totaltotal_pedidos_rechazadosTV = 0;
            $scope.Total_Personas_GestionandoTV = 0;
            $scope.LoginsGestionandoTV = [];
            var indiceTV = 0;


            for (var i = 0; i < tam1; i++) {
                if ($scope.dataresumenContingenciasTV[i].estado == 'Acepta') {
                    $scope.Totaltotal_pedidos_aceptadosTV = +$scope.Totaltotal_pedidos_aceptadosTV + 1;
                }
                ;
                if ($scope.dataresumenContingenciasTV[i].estado == 'Rechaza') {
                    $scope.Totaltotal_pedidos_rechazadosTV = +$scope.Totaltotal_pedidos_rechazadosTV + 1;
                }
                ;
                if ($scope.dataresumenContingenciasTV[i].estado == 'Pendiente') {
                    $scope.Totaltotal_pedidos_pendientesTV = +$scope.Totaltotal_pedidos_pendientesTV + 1;
                }
                ;
            }
            $scope.Totaltotal_pedidos_pendientesTV += $scope.cantidadContingenciasTV;
            /*SE RECORREO EL ARREGLO IDENTIFICNADO Y ALMACENANDO LOS LOGINES DE QUIENES TIENEN MARCADAS CONTINGENCIAS PARA GESTIONAR*/
            for (var i = 0; i < tam1; i++) {
                if ($scope.dataresumenContingenciasTV[i].estado == 'Pendiente' && $scope.dataresumenContingenciasTV[i].logincontingencia !== null && $scope.dataresumenContingenciasTV[i].estado == 'Pendiente' && $scope.dataresumenContingenciasTV[i].logincontingencia !== "") {
                    $scope.LoginsGestionandoTV[indiceTV] = $scope.dataresumenContingenciasTV[i].logincontingencia;
                    indiceTV = indiceTV + 1;
                }
                ;
            }
            /*SE CUENTAN LOS USUARIOS ÚNICOS, PARA SABER LA CANTIDAD REAL DE PERSONAS QUE ESTAN GESTIONANDO CONTINGENCIAS*/
            $scope.Total_Personas_GestionandoTV = $scope.LoginsGestionandoTV.filter((v, i, a) => a.indexOf(v) === i).length;

            /*SE HACE EL CONTEO POR LOGIN YA QUE HAY PERSONAS QUE MARCAN PARA GESTIONAR MAS DE UNA CONTINGENCIA*/
            const cantAnalistasTV = $scope.LoginsGestionandoTV.reduce((contadorAnalistasTV, indiceTV) => {
                contadorAnalistasTV[indiceTV] = (contadorAnalistasTV[indiceTV] || 0) + 1;
                return contadorAnalistasTV;
            }, {});

            $scope.cantidadAnalistasTV = cantAnalistasTV;

            /*CONTADOR DIARIO PARA Internet-ToIp*/
            var tam2 = $scope.dataresumenContingenciasInTo.length;
            $scope.Totaltotal_pedidos_aceptadosInTo = 0;
            $scope.Totaltotal_pedidos_pendientesInTo = 0;
            $scope.Totaltotal_pedidos_rechazadosInTo = 0;
            $scope.Total_Personas_GestionandoInternet = 0;
            $scope.LoginsGestionandoInternet = [];
            var indiceInternet = 0;

            for (var i = 0; i < tam2; i++) {
                if ($scope.dataresumenContingenciasInTo[i].estado == 'Acepta') {
                    $scope.Totaltotal_pedidos_aceptadosInTo = +$scope.Totaltotal_pedidos_aceptadosInTo + 1;
                }
                ;
                if ($scope.dataresumenContingenciasInTo[i].estado == 'Rechaza') {
                    $scope.Totaltotal_pedidos_rechazadosInTo = +$scope.Totaltotal_pedidos_rechazadosInTo + 1;
                }
                ;
                if ($scope.dataresumenContingenciasInTo[i].estado == 'Pendiente') {
                    $scope.Totaltotal_pedidos_pendientesInTo = +$scope.Totaltotal_pedidos_pendientesInTo + 1;
                }
                ;
            }
            $scope.Totaltotal_pedidos_pendientesInTo += $scope.cantidadContingenciasINT;
            /*SE RECORREO EL ARREGLO IDENTIFICNADO Y ALMACENANDO LOS LOGINES DE QUIENES TIENEN MARCADAS CONTINGENCIAS PARA GESTIONAR*/
            for (var i = 0; i < tam2; i++) {
                if ($scope.dataresumenContingenciasInTo[i].estado == 'Pendiente' && $scope.dataresumenContingenciasInTo[i].logincontingencia !== null && $scope.dataresumenContingenciasInTo[i].estado == 'Pendiente' && $scope.dataresumenContingenciasInTo[i].logincontingencia !== "") {
                    $scope.LoginsGestionandoInternet[indiceInternet] = $scope.dataresumenContingenciasInTo[i].logincontingencia;
                    indiceInternet = indiceInternet + 1;
                }
                ;
            }
            /*SE CUENTAN LOS USUARIOS ÚNICOS, PARA SABER LA CANTIDAD REAL DE PERSONAS QUE ESTAN GESTIONANDO CONTINGENCIAS*/
            $scope.Total_Personas_GestionandoInternet = $scope.LoginsGestionandoInternet.filter((v, i, a) => a.indexOf(v) === i).length;

            /*SE HACE EL CONTEO POR LOGIN YA QUE HAY PERSONAS QUE MARCAN PARA GESTIONAR MAS DE UNA CONTINGENCIA*/
            const cantAnalistasInt = $scope.LoginsGestionandoInternet.reduce((contadorAnalistasInternet, indiceInternet) => {
                contadorAnalistasInternet[indiceInternet] = (contadorAnalistasInternet[indiceInternet] || 0) + 1;
                return contadorAnalistasInternet;
            }, {});

            $scope.cantidadAnalistasInt = cantAnalistasInt;

            /*CONTADOR DIARIO PARA CORREGIR PORTAFOLIO*/
            var tam = $scope.dataresumenContingenciasCP.length;
            $scope.Totaltotal_pedidos_aceptadosCP = 0;
            $scope.Totaltotal_pedidos_pendientesCP = 0;
            $scope.Totaltotal_pedidos_rechazadosCP = 0;
            $scope.Total_Personas_GestionandoCP = 0;

            for (var i = 0; i < tam; i++) {

                if ($scope.dataresumenContingenciasCP[i].estado == 'Acepta') {
                    $scope.Totaltotal_pedidos_aceptadosCP = +$scope.Totaltotal_pedidos_aceptadosCP + 1;
                }
                ;
                if ($scope.dataresumenContingenciasCP[i].estado == 'Rechaza') {
                    $scope.Totaltotal_pedidos_rechazadosCP = +$scope.Totaltotal_pedidos_rechazadosCP + 1;
                }
                ;
                if ($scope.dataresumenContingenciasCP[i].estado == 'Pendiente') {
                    $scope.Totaltotal_pedidos_pendientesCP = +$scope.Totaltotal_pedidos_pendientesCP + 1;
                }
                ;
            }

            /*CONTADOR DE PERSONAS GESTIONANDO CONTINGENCIAS DE CP*/
            for (var i = 0; i < tam; i++) {
                if ($scope.dataresumenContingenciasCP[i].estado == 'Pendiente' && $scope.dataresumenContingenciasCP[i].loginContingenciaPortafolio !== null && $scope.dataresumenContingenciasCP[i].estado == 'Pendiente' && $scope.dataresumenContingenciasCP[i].loginContingenciaPortafolio !== "") {
                    $scope.Total_Personas_GestionandoCP = +$scope.Total_Personas_GestionandoCP + 1;
                }
                ;
            }

            return data.data;
        }, function errorCallback(response) {

        });
    }

    $scope.descargarContingencias = function (fechaInicial, fechafinal) {
        //console.log(datoExportar+$scope.indicadores.fecha);
        services.getexporteContingencias(fechaInicial, fechafinal, $rootScope.galletainfo).then(
            function (data) {
                // console.log(data.data[0]);
                window.location.href = "tmp/" + data.data[0];
                return data.data;
            },
            function errorCallback(response) {
            }
        );

    };

    /*MOSTRAR MODAL PARA CIERRE MASIVO DE CONTINGENCIAS*/

    $scope.callModalCierreMasivoConti = function () {

        angular.copy();
        $("#cierreMasivoContingencias").modal();
    }

    /*FUNCION PARA CERRAR DE FORMA MASIVA LAS OONTINGENCIAS*/
    $scope.cierreMasivoContingencias = function (dataCierreMasivoContin, frmCierreMasivoContin) {

        if (dataCierreMasivoContin.TV != true && dataCierreMasivoContin.Internet != true && dataCierreMasivoContin.ToIP != true && dataCierreMasivoContin.InternetToIP != true) {
            swal("Debe seleccionar mínimo un producto.");
            return;
        }


        if (dataCierreMasivoContin.Instalaciones != true && dataCierreMasivoContin.Reparaciones != true) {
            console.log("Punto " + dataCierreMasivoContin.Instalaciones);
            swal("Debe seleccionar mínimo un proceso.");
            return;
        }

        if (dataCierreMasivoContin.AprovisionarContin != true && dataCierreMasivoContin.Refresh != true && dataCierreMasivoContin.CambioEquipo != true && dataCierreMasivoContin.CambioEID != true && dataCierreMasivoContin.RegistrosToIP != true) {
            swal("Debe seleccionar mínimo una acción.");
            return;
        }

        Swal.fire({
            title: '¿Está seguro que desea cancelar de forma masiva las contigencias?',
            text: "no prodrás revertir esto!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, Ejecutar Ahora!'
        }).then((result) => {
            if (result.value) {


                if (frmCierreMasivoContin.TV.$modelValue == undefined || frmCierreMasivoContin.TV.$modelValue == false) {
                    dataCierreMasivoContin.TV = 'Sin Informacion';
                    console.log("SIN SELECCIONAR: " + dataCierreMasivoContin.TV);
                } else {
                    dataCierreMasivoContin.TV = $("#TV:checked").val();
                    console.log("SELECCIONADO: " + dataCierreMasivoContin.TV);
                }

                if (frmCierreMasivoContin.Internet.$modelValue == undefined || frmCierreMasivoContin.Internet.$modelValue == false) {
                    dataCierreMasivoContin.Internet = 'Sin Informacion';
                } else {
                    dataCierreMasivoContin.Internet = $("#Internet:checked").val();
                }

                if (frmCierreMasivoContin.ToIP.$modelValue == undefined || frmCierreMasivoContin.ToIP.$modelValue == false) {
                    dataCierreMasivoContin.ToIP = 'Sin Informacion';
                } else {
                    dataCierreMasivoContin.ToIP = $("#ToIP:checked").val();
                }

                if (frmCierreMasivoContin.InternetToIP.$modelValue == undefined || frmCierreMasivoContin.InternetToIP.$modelValue == false) {
                    dataCierreMasivoContin.InternetToIP = 'Sin Informacion';
                } else {
                    dataCierreMasivoContin.InternetToIP = $("#InternetToIP:checked").val();
                }

                if (frmCierreMasivoContin.Instalaciones.$modelValue == undefined || frmCierreMasivoContin.Instalaciones.$modelValue == false) {
                    dataCierreMasivoContin.Instalaciones = 'Sin Informacion';
                } else {
                    dataCierreMasivoContin.Instalaciones = $("#Instalaciones:checked").val();
                }

                if (frmCierreMasivoContin.Reparaciones.$modelValue == undefined || frmCierreMasivoContin.Reparaciones.$modelValue == false) {
                    dataCierreMasivoContin.Reparaciones = 'Sin Informacion';
                } else {
                    dataCierreMasivoContin.Reparaciones = $("#Reparaciones:checked").val();
                }

                if (frmCierreMasivoContin.AprovisionarContin.$modelValue == undefined || frmCierreMasivoContin.AprovisionarContin.$modelValue == false) {
                    dataCierreMasivoContin.AprovisionarContin = 'Sin Informacion';
                } else {
                    dataCierreMasivoContin.AprovisionarContin = $("#AprovisionarContin:checked").val();
                }

                if (frmCierreMasivoContin.Refresh.$modelValue == undefined || frmCierreMasivoContin.Refresh.$modelValue == false) {
                    dataCierreMasivoContin.Refresh = 'Sin Informacion';
                } else {
                    dataCierreMasivoContin.Refresh = $("#Refresh:checked").val();
                }

                if (frmCierreMasivoContin.CambioEquipo.$modelValue == undefined || frmCierreMasivoContin.CambioEquipo.$modelValue == false) {
                    dataCierreMasivoContin.CambioEquipo = 'Sin Informacion';
                } else {
                    dataCierreMasivoContin.CambioEquipo = $("#CambioEquipo:checked").val();
                }

                if (frmCierreMasivoContin.CambioEID.$modelValue == undefined || frmCierreMasivoContin.CambioEID.$modelValue == false) {
                    dataCierreMasivoContin.CambioEID = 'Sin Informacion';
                } else {
                    dataCierreMasivoContin.CambioEID = $("#CambioEID:checked").val();
                }

                if (frmCierreMasivoContin.RegistrosToIP.$modelValue == undefined || frmCierreMasivoContin.RegistrosToIP.$modelValue == false) {
                    dataCierreMasivoContin.RegistrosToIP = 'Sin Informacion';
                    console.log("SIN SELECCIONART: " + dataCierreMasivoContin.RegistrosToIP);
                } else {
                    dataCierreMasivoContin.RegistrosToIP = $("#RegistrosToIP:checked").val();
                    console.log("SELECCIONADOT: " + dataCierreMasivoContin.RegistrosToIP);
                }

                console.log(dataCierreMasivoContin);

                services.cierreMasivoContingencia(dataCierreMasivoContin).then(
                    function (respuesta) {

                        $scope.counter = respuesta.data[0];

                        if (respuesta.status == '200') {

                            if ($scope.counter == 0) {

                                Swal(
                                    'No se encontraron contingencias con esas condiciones para eliminar!',
                                    'Por favor revisar'
                                )

                            } else {

                                Swal(
                                    'Se rechazaron masivamente ' + $scope.counter + ' contingencias!',
                                    'Por favor actualizar'
                                )
                            }
                        }

                        /*PARA QUE EL MODAL SE OCULTE SOLO*/
                        $("#cierreMasivoContingencias").modal('hide');
                        $scope.cierreMasivoSel = {};

                        /*LIMPIEZA DEL MODAL*/
                        frmCierreMasivoContin.autoValidateFormOptions.resetForm();

                    }, function errorCallback(response) {

                        if (response.status == '400') {

                            Swal({
                                type: 'error',
                                title: 'Oops...',
                                text: 'Hubo un error',
                                footer: '¡Escalarlo al administrador!'
                            })
                        }
                    });
                $scope.gestioncontingencias();

            }
        })
    }

    $scope.gestioncontingencias();
    //$scope.gestioncontingenciasPrueba();
    $scope.resumenContingencias($scope.fechaupdateInical, $scope.fechaupdateFinal);
});

app.controller('pendientesBrutalCtrl', function ($scope, $http, $rootScope, $location, $route, $routeParams, $cookies, $timeout, $uibModal, services) {

    $scope.abrirModalPendientes = function () {

        var modalInstance = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            size: 'md',
            templateUrl: 'partial/PendientesBrutal.html',
            controller: function ($scope, $uibModalInstance) {
                $scope.tituloModalPausa = "Pendientes Brutal force";
                services.pendientesBrutalForce().then(function (data) {
                    $scope.pendientesBrutal = data.data[0];
                    $scope.total = $scope.pendientesBrutal.length;
                    return data.data;
                });
                $scope.cerrar = function () {
                    $uibModalInstance.dismiss('cancel');
                }

            }
        });

    };
});


/* ---------------------------------------------------------------- */
/*                           SOPORTE GPON                           */
/* ---------------------------------------------------------------- */


app.controller('GestionsoportegponCtrl', function ($scope, $rootScope, $location, $route, $routeParams, $cookies, $cookieStore, $timeout, services) {

    $scope.isSoporteGponFromField = false;
    $scope.isSoporteGponFromIntranet = false;
    $scope.isLoadingData = true;
    $scope.dataSoporteGpon = [];

    //var database = firebase.firestore();

    // $scope.listarsoportegpon = () => {

    //     $scope.isLoadingData = false;

    //     database.collection("support-gpon")
    //         .where("status", "==", 0)
    //         .orderBy("date_created", "asc")
    //         .get()
    //         .then((querySnapshot) => {

    //             $scope.listaSoporteGpon = [];

    //             querySnapshot.forEach(async (doc) => {
    //                 let dataQuerySnapshot = {};
    //                 dataQuerySnapshot = {
    //                     _id: doc.id,
    //                     task: doc.data().task,
    //                     arpon: doc.data().arpon,
    //                     nap: doc.data().nap,
    //                     hilo: doc.data().hilo,
    //                     internet1: doc.data().internet1,
    //                     internet2: doc.data().internet2,
    //                     internet3: doc.data().internet3,
    //                     internet4: doc.data().internet4,
    //                     television1: doc.data().television1,
    //                     television2: doc.data().television2,
    //                     television3: doc.data().television3,
    //                     television4: doc.data().television4,
    //                     numeroContacto: doc.data().numeroContacto,
    //                     nombreContacto: doc.data().nombreContacto,
    //                     observacion: doc.data().observacion,
    //                     userId: doc.data().userId,
    //                     emailUsr: doc.data().emailUsr,
    //                     userIdentification: doc.data().userIdentification,
    //                     status: doc.data().status,
    //                     date_created: doc.data().date_created,
    //                 }

    //                 var date = dataQuerySnapshot.date_created.toDate();
    //                 var year = date.getFullYear();
    //                 var month = ((date.getMonth() + 1) < 10) ? "0" + (date.getMonth() + 1) : (date.getMonth() + 1);
    //                 var day = (date.getDate() < 10) ? "0" + date.getDate() : date.getDate();
    //                 var hours = (date.getHours() < 10) ? "0" + date.getHours() : date.getHours();
    //                 var minutes = (date.getMinutes() < 10) ? "0" + date.getMinutes() : date.getMinutes();
    //                 var seconds = (date.getSeconds() < 10) ? "0" + date.getSeconds() : date.getSeconds();

    //                 var formattedTime = year + '-' + month + '-' + day + ' ' + hours + ':' + minutes + ':' + seconds;

    //                 dataQuerySnapshot.fecha_solicitud = formattedTime;
    //                 $scope.listaSoporteGpon.push(dataQuerySnapshot);
    //             });

    //             console.log('$scope.listaSoporteGpon', $scope.listaSoporteGpon);

    //             $scope.listaSoporteGpon.forEach(async (val) => {

    //                 let task = val.task;
    //                 let arpon = val.arpon;
    //                 let nap = val.nap;
    //                 let hilo = val.hilo;
    //                 let internet1 = val.internet1;
    //                 let internet2 = val.internet2;
    //                 let internet3 = val.internet3;
    //                 let internet4 = val.internet4;
    //                 let television1 = val.television1;
    //                 let television2 = val.television2;
    //                 let television3 = val.television3;
    //                 let television4 = val.television4;
    //                 let numeroContacto = val.numeroContacto;
    //                 let nombreContacto = val.nombreContacto;
    //                 let observacionTerreno = val.observacion;
    //                 let user_id = val.userId;
    //                 let request_id = val._id;
    //                 let user_identification = val.userIdentification;
    //                 let fecha_solicitud = val.fecha_solicitud;
    //                 let unepedido = '';
    //                 let tasktypecategory = '';
    //                 let unemunicipio = '';
    //                 let uneproductos = '';
    //                 let datoscola = '';
    //                 let engineer_id = '';
    //                 let engineer_name = '';
    //                 let mobile_phone = '';
    //                 let serial = '';
    //                 let mac = '';
    //                 let tipo_equipo = '';
    //                 let velocidad_navegacion = '';

    //                 var autocompleteQuery = await fetch('http://10.100.66.254:8080/HCHV_DEV/BuscarTaskGpon/' + task);
    //                 var autocompleteData = await autocompleteQuery.json();

    //                 if (autocompleteData.length != 0) {

    //                     unepedido = autocompleteData[0].UNEPedido
    //                     tasktypecategory = autocompleteData[0].categoria
    //                     unemunicipio = autocompleteData[0].UNEMunicipio
    //                     uneproductos = autocompleteData[0].UNEProductos
    //                     engineer_id = autocompleteData[0].EngineerID
    //                     engineer_name = autocompleteData[0].EngineerName
    //                     mobile_phone = autocompleteData[0].MobilePhone
    //                     velocidad_navegacion = autocompleteData[0].VelocidadNavegacion

    //                     var serialsArray = [];
    //                     autocompleteData.forEach((item) => { if (serialsArray.indexOf(item.SerialNo) < 0) { serialsArray.push(item.SerialNo) } });

    //                     var macsArray = [];
    //                     autocompleteData.forEach((item) => { if (macsArray.indexOf(item.MAC) < 0) { macsArray.push(item.MAC) } });

    //                     var tipoEqArray = [];
    //                     autocompleteData.forEach((item) => { if (tipoEqArray.indexOf(item.TipoEquipo) < 0) { tipoEqArray.push(item.TipoEquipo) } });

    //                     var planProdArray = [];
    //                     autocompleteData.forEach((item) => { if (planProdArray.indexOf(item.UNEPlanProducto) < 0) { planProdArray.push(item.UNEPlanProducto) } });


    //                     autocompleteData.forEach((item) => {
    //                         if (item.UNEPlanProducto.indexOf('TO') >= 0) {
    //                             let splitdatoscolas = item.DatosCola1.split('*');
    //                             let datanumero = splitdatoscolas.find((element) => {
    //                                 if (element.indexOf('Numero') >= 0) {
    //                                     return element;
    //                                 }
    //                             });

    //                             if (planProdArray.indexOf(datanumero) < 0) { planProdArray.push(datanumero) }
    //                         }
    //                     });

    //                     serial = serialsArray.join(', ');
    //                     mac = macsArray.join(', ');
    //                     tipo_equipo = tipoEqArray.join(', ');
    //                     datoscola = planProdArray.join(', ');

    //                 }

    //                 /* await services.validarLlenadoSoporteGpon(task)
    //                 .then(async function(data) {

    //                     if (data.data.length > 0) {
    //                         let validarllenado = data.data[0][0]['unepedido'];

    //                         if (validarllenado == '' || validarllenado == null) {
    //                             console.log('INGRESO AL LLENADO');
    //                             await services.postPendientesSoporteGpon(task, arpon, nap, hilo, internet1, internet2, internet3, internet4, television1, television2, television3, television4, numeroContacto, nombreContacto, user_id, request_id, user_identification, fecha_solicitud, unepedido, tasktypecategory, unemunicipio, uneproductos, datoscola, engineer_id, engineer_name, mobile_phone, serial, mac, tipo_equipo, velocidad_navegacion).then(function(data) {
    //                                 console.log('successPOST', data);
    //                             }).catch( (err) => {
    //                                 //$scope.listarsoportegpon();
    //                                 console.log('err', err);
    //                             });
    //                         } else {
    //                             console.log('NO LLENO NADA');
    //                         }
    //                     } else {
    //                         await services.postPendientesSoporteGpon(task, arpon, nap, hilo, internet1, internet2, internet3, internet4, television1, television2, television3, television4, numeroContacto, nombreContacto, user_id, request_id, user_identification, fecha_solicitud, unepedido, tasktypecategory, unemunicipio, uneproductos, datoscola, engineer_id, engineer_name, mobile_phone, serial, mac, tipo_equipo, velocidad_navegacion).then(function(data) {
    //                             console.log('successPOST', data);
    //                         }).catch( (err) => {
    //                             //$scope.listarsoportegpon();
    //                             console.log('err', err);
    //                         });
    //                     }

    //                 }).catch( (err) => {
    //                     //$scope.listarsoportegpon();
    //                     console.log('errValLlenado', err);
    //                 }); */

    //                 await services.postPendientesSoporteGpon(task, arpon, nap, hilo, internet1, internet2, internet3, internet4, television1, television2, television3, television4, numeroContacto, nombreContacto, user_id, request_id, user_identification, fecha_solicitud, unepedido, tasktypecategory, unemunicipio, uneproductos, datoscola, engineer_id, engineer_name, mobile_phone, serial, mac, tipo_equipo, velocidad_navegacion, observacionTerreno).then(function (data) {
    //                     console.log('successPOST', data);
    //                 }).catch((err) => {
    //                     //$scope.listarsoportegpon();
    //                     console.log('err', err);
    //                 });


    //                 /* services.getPendientesSoporteGpon(val.task).then(function(data) {

    //                     if (data.data.length != 0) {

    //                         unepedido = data.data[0].UNEPedido
    //                         tasktypecategory = data.data[0].categoria
    //                         unemunicipio = data.data[0].UNEMunicipio
    //                         uneproductos = data.data[0].UNEProductos
    //                         engineer_id = data.data[0].EngineerID
    //                         engineer_name = data.data[0].EngineerName
    //                         mobile_phone = data.data[0].MobilePhone
    //                         velocidad_navegacion = data.data[0].VelocidadNavegacion

    //                         var serialsArray = [];
    //                         data.data.forEach((item) => { if (serialsArray.indexOf(item.SerialNo) < 0) {serialsArray.push(item.SerialNo)} });

    //                         var macsArray = [];
    //                         data.data.forEach((item) => { if (macsArray.indexOf(item.MAC) < 0) {macsArray.push(item.MAC)} });

    //                         var tipoEqArray = [];
    //                         data.data.forEach((item) => { if (tipoEqArray.indexOf(item.TipoEquipo) < 0) {tipoEqArray.push(item.TipoEquipo)} });

    //                         var planProdArray = [];
    //                         data.data.forEach((item) => { if (planProdArray.indexOf(item.UNEPlanProducto) < 0) {planProdArray.push(item.UNEPlanProducto)} });

    //                         serial = serialsArray.join(', ');
    //                         mac = macsArray.join(', ');
    //                         tipo_equipo = tipoEqArray.join(', ');
    //                         datoscola = planProdArray.join(', ');

    //                     }

    //                     services.postPendientesSoporteGpon(task, arpon, nap, hilo, internet1, internet2, internet3, internet4, television1, television2, television3, television4, numeroContacto, nombreContacto, user_id, request_id, user_identification, fecha_solicitud, unepedido, tasktypecategory, unemunicipio, uneproductos, datoscola, engineer_id, engineer_name, mobile_phone, serial, mac, tipo_equipo, velocidad_navegacion).then(function(data) {
    //                         console.log('successPOST', data);
    //                     }).catch( (err) => {
    //                         //$scope.listarsoportegpon();
    //                         console.log('err', err);
    //                     });

    //                     //return data;
    //                 }).catch( (err) => {
    //                     //$scope.listarsoportegpon();
    //                     console.log('err', err);
    //                 }); */

    //             });

    //             services.getListaPendientesSoporteGpon().then(function (data) {

    //                 if (data.data.length > 0) {

    //                     console.log('data', data.data[0]);

    //                     $scope.dataSoporteGpon = data.data[0];
    //                     $scope.isLoadingData = false;
    //                 }
    //                 else {
    //                     $scope.flagOnlyPSData = true;
    //                 }

    //                 return data;
    //             }).catch((err) => {
    //                 console.log(err)
    //             });

    //             $scope.isLoadingData = false;

    //             //$scope.gestionsoportegpon();

    //             /* if($scope.flagOnlyPSData){
    //                 $scope.dataSoporteGpon = $scope.listaSoporteGpon.concat($scope.dataGestionEscalamiento);
    //                 $scope.isLoadingData = false;
    //             } */
    //         }).catch((err) => {
    //             console.log(err);
    //         });
    // }

    $scope.listarsoportegpon = () => {

        $scope.isLoadingData = true;

        services.getListaPendientesSoporteGpon()
            .then(function (data) {

                if (data.data.length > 0) {

                    console.log('data', data.data[0]);

                    $scope.dataSoporteGpon = data.data[0];
                    $scope.isLoadingData = false;
                } else {
                    $scope.flagOnlyPSData = true;
                }

                return data;
            }).catch((err) => {
            console.log(err)
        });

        $scope.isLoadingData = false;
    }

    $scope.marcarEngestionGpon = async (data) => {

        services.marcarEngestionGpon(data, $rootScope.galletainfo).then(function (data) {

            //console.log("marcarengestion: ",data);

            if (data.data !== "") {
                if (data.data[0] == "desbloqueado") {
                    $scope.respuestaMarca = data.data[0][0];
                    //console.log("respuestaMarcaDesbloqueado: ",$scope.respuestaMarca);
                    swal({
                        title: "Pedido Desbloqueado",
                        type: "success",
                        position: 'center',
                        showConfirmButton: false,
                        timer: 3000
                    });
                    $scope.listarsoportegpon();
                    //$scope.gestioncontingenciasPrueba();
                    return;
                } else {
                    $scope.respuestaMarca = data.data[0][0];
                    //console.log("respuestaMarcaOcupado: ",$scope.respuestaMarca);
                    swal({
                        title: "El pedido se encuentra bloqueado",
                        type: "warning",
                        position: 'center',
                        showConfirmButton: false,
                        timer: 3000
                    });
                    $scope.listarsoportegpon();
                    //$scope.gestioncontingenciasPrueba();
                    return;
                }
            } else if (data.data == "") {
                $scope.respuestaMarca = "";
                swal({
                    title: "Pedido Bloqueado",
                    type: "success",
                    position: 'center',
                    showConfirmButton: false,
                    timer: 3000
                });
                $scope.listarsoportegpon();
                //$scope.gestioncontingenciasPrueba();
                return;
            }
        })
            .catch(err => console.log(err));
    }

    $scope.gestionarSoporteGpon = async (id_soporte, id_firebase) => {

        let tipificacion = $('#tipificacion' + id_soporte).val();

        const {value: observacion} = await Swal({
            title: 'Gestión Soporte GPON',
            input: 'textarea',
            inputPlaceholder: 'Gestion...',
            inputAttributes: {
                'aria-label': 'Gestion'
            },
            showCancelButton: true,

        });

        if (observacion) {

            Swal('Cargando...')

            if (tipificacion == "") {
                Swal({
                    title: 'Error',
                    text: 'Debes de seleccionar una tipificación.',
                    type: 'error'
                });
                return false;
            }

            services.gestionarSoporteGpon(id_soporte, tipificacion, observacion, $rootScope.galletainfo).then(function (data) {
                console.log('successPUT', data);
                //database.collection("support-gpon").doc(id_firebase).update({status: 1});

                $scope.listarsoportegpon();

                Swal({
                    title: 'Excelente',
                    text: data.data.msg,
                    type: 'success'
                });

            }).catch((err) => {
                //$scope.listarsoportegpon();
                console.log('err', err);
            });

        } else {
            Swal({
                title: 'Error',
                text: 'Debes ingresar una observacion.',
                type: 'error'
            });
            return false;
        }

    }

    //$scope.gestionsoportegpon();
    $scope.listarsoportegpon();

});

app.controller('registrossoportegponCtrl', function ($scope, $http, $rootScope, $location, $route, $routeParams, $cookies, $timeout, services, cargaRegistros) {

    $scope.listaRegistros = {};
    $scope.RegistrosSoporteGpon = {};
    $scope.listadoAcciones = {};
    $scope.datosRegistros = {};
    $scope.verplantilla = false;

    if ($scope.RegistrosSoporteGpon.fechaini == undefined || $scope.RegistrosSoporteGpon.fechafin == undefined) {
        var tiempo = new Date().getTime();
        var date1 = new Date();
        var year = date1.getFullYear();
        var month = (date1.getMonth() + 1 <= 9) ? '0' + (date1.getMonth() + 1) : (date1.getMonth() + 1);
        var day = (date1.getDate() <= 9) ? '0' + date1.getDate() : date1.getDate();

        tiempo = year + "-" + month + "-" + day;

        $scope.fechaini = tiempo;
        $scope.fechafin = tiempo;

        //console.log("fechaini: ",$scope.fechaini);
        //console.log("fechafin: ",$scope.fechafin);
    }

    $scope.setPage = function (pageNo) {
        $scope.datapendientes.currentPage = pageNo;
    };

    $scope.pageChanged = function () {
        $scope.BuscarRegistros($scope.datapendientes.currentPage);
    };

    $scope.BuscarRegistrosSoporteGpon = function (datos) {
        console.log("BuscarRegistrosSoporteGpon: ", datos);
        $scope.errorDatos = null;
        $scope.csvPend = false;
        $scope.listaRegistros = {};

        if (datos.fechaini > datos.fechafin) {
            alert("La fecha inicial debe ser menor que la inicial");
            return;
        } else {

            services.registrossoportegpon($scope.datapendientes.currentPage, datos).then(
                function (data) {
                    console.log("registros: ", data);
                    $scope.listaRegistros = data.data[0];
                    //console.log("listaRegistros: ", $scope.listaRegistros);
                    $scope.cantidad = data.data[0].length;
                    $scope.counter = data.data[1];

                    return data.data;
                },

                function errorCallback(response) {
                    if (datos.concepto == undefined || datos.buscar == undefined) {
                        if (datos.fechaini == datos.fechafin) {
                            $scope.errorDatos = "No hay datos para el día:  " + datos.fechaini;
                        } else {
                            $scope.errorDatos = "No hay datos entre " + datos.fechaini + " - " + datos.fechafin;
                        }
                    } else
                        $scope.errorDatos = datos.concepto + " " + datos.buscar + " no existe.";
                });
        }
    }

    $scope.muestraNotas = function (datos) {

        /* $scope.pedido = datos.pedido;
        $scope.TituloModal = "Observaciones para el pedido:";
        $scope.observaciones = datos.observaciones; */
        // console.log( $scope.observaciones);

        console.log("datos", datos);

        $scope.TituloModal = 'Detalle soporte gpon';
        $scope.pedido = datos.unepedido;
        $scope.tarea = datos.tarea;
        $scope.velocidadnavegacion = datos.velocidad_navegacion;
        $scope.arpon = datos.arpon;
        $scope.nap = datos.nap;
        $scope.hilo = datos.hilo;
        $scope.intenet1 = (datos.port_internet_1 == '1') ? 'X' : '';
        $scope.intenet2 = (datos.port_internet_2 == '1') ? 'X' : '';
        $scope.intenet3 = (datos.port_internet_3 == '1') ? 'X' : '';
        $scope.intenet4 = (datos.port_internet_4 == '1') ? 'X' : '';
        $scope.television1 = (datos.port_television_1 == '1') ? 'X' : '';
        $scope.television2 = (datos.port_television_2 == '1') ? 'X' : '';
        $scope.television3 = (datos.port_television_3 == '1') ? 'X' : '';
        $scope.television4 = (datos.port_television_4 == '1') ? 'X' : '';

        let listaseriales = datos.serial.split(',');
        let listamacs = datos.mac.split(',');

        $scope.listaSeriales = listaseriales;
        $scope.listaMacs = listamacs;
        $scope.observaciones = datos.observacion;
    }


    $scope.csvRegistros = function () {
        $scope.csvPend = false;
        if ($scope.RegistrosSoporteGpon.fechaini > $scope.RegistrosSoporteGpon.fechafin) {
            alert("La fecha inicial debe ser menor que la inicial");
            return;
        } else {

            services.expCsvRegistrosSoporteGpon($scope.RegistrosSoporteGpon, $rootScope.galletainfo).then(
                function (data) {
                    // console.log(data.data[0]);
                    window.location.href = "tmp/" + data.data[0];
                    $scope.csvPend = true;
                    $scope.counter = data.data[1];
                    //console.log(data.data);
                    return data.data;
                },
                function errorCallback(response) {
                    $scope.errorDatos = "No hay datos.";
                    $scope.csvPend = false;
                }
            );
        }
    };

    $scope.maxSize = 5;
    $scope.datapendientes = {maxSize: 5, currentPage: 1, numPerPage: 100, totalItems: 0};
    $scope.BuscarRegistrosSoporteGpon($scope.datapendientes.currentPage);
});


/* ---------------------------------------------------------------- */
/*                         FIN SOPORTE GPON                         */
/* ---------------------------------------------------------------- */


/* ---------------------------------------------------------------- */
/*                             CODIGO INCOMPLETO                    */
/* ---------------------------------------------------------------- */


app.controller('GestioncodigoincompletoCtrl', function ($scope, $rootScope, $location, $route, $routeParams, $cookies, $cookieStore, $timeout, services) {

    $scope.isSoporteGponFromField = false;
    $scope.isSoporteGponFromIntranet = false;
    $scope.isLoadingData = true;
    $scope.dataCodigoIncompleto = [];

    //var database = firebase.firestore();

    $scope.listarcodigoincompleto = () => {

        services.getListaCodigoIncompleto().then(function (data) {

            if (data.data.length > 0) {

                console.log('data', data.data[0]);

                $scope.dataCodigoIncompleto = data.data[0];
                $scope.isLoadingData = false;
            } else {
                $scope.isLoadingData = true;
            }

            return data;
        }).catch((err) => {
            $scope.isLoadingData = true;
            console.log(err)
        });

        $scope.isLoadingData = false;

    }

    $scope.gestionarCodigoIncompleto = async (id_codigo_incompleto) => {

        let tipificacion = $('#tipificacion' + id_codigo_incompleto).val();

        const {value: observacion} = await Swal({
            title: 'Gestión Código Incompleto',
            input: 'textarea',
            inputPlaceholder: 'Gestion...',
            inputAttributes: {
                'aria-label': 'Gestion'
            },
            showCancelButton: true,

        });

        if (observacion) {

            Swal('Cargando...')

            if (tipificacion == "") {
                Swal({
                    title: 'Error',
                    text: 'Debes de seleccionar una tipificación.',
                    type: 'error'
                });
                return false;
            }

            services.gestionarCodigoIncompleto(id_codigo_incompleto, tipificacion, observacion, $rootScope.galletainfo).then(function (data) {
                console.log('successPUT', data);

                $scope.listarcodigoincompleto();

                Swal({
                    title: 'Excelente',
                    text: data.data.msg,
                    type: 'success'
                });

            }).catch((err) => {
                //$scope.listarcodigoincompleto();
                console.log('err', err);
            });

        } else {
            Swal({
                title: 'Error',
                text: 'Debes ingresar una observacion.',
                type: 'error'
            });
            return false;
        }

    }

    //$scope.gestionsoportegpon();
    $scope.listarcodigoincompleto();

});

app.controller('registroscodigoincompletoCtrl', function ($scope, $http, $rootScope, $location, $route, $routeParams, $cookies, $timeout, services, cargaRegistros) {

    $scope.listaRegistros = {};
    $scope.RegistrosCodigoIncompleto = {};
    $scope.listadoAcciones = {};
    $scope.datosRegistros = {};
    $scope.verplantilla = false;

    if ($scope.RegistrosCodigoIncompleto.fechaini == undefined || $scope.RegistrosCodigoIncompleto.fechafin == undefined) {
        var tiempo = new Date().getTime();
        var date1 = new Date();
        var year = date1.getFullYear();
        var month = (date1.getMonth() + 1 <= 9) ? '0' + (date1.getMonth() + 1) : (date1.getMonth() + 1);
        var day = (date1.getDate() <= 9) ? '0' + date1.getDate() : date1.getDate();

        tiempo = year + "-" + month + "-" + day;

        $scope.fechaini = tiempo;
        $scope.fechafin = tiempo;

        //console.log("fechaini: ",$scope.fechaini);
        //console.log("fechafin: ",$scope.fechafin);
    }

    $scope.setPage = function (pageNo) {
        $scope.datapendientes.currentPage = pageNo;
    };

    $scope.pageChanged = function () {
        $scope.BuscarRegistros($scope.datapendientes.currentPage);
    };

    $scope.BuscarRegistrosCodigoIncompleto = function (datos) {
        console.log("BuscarRegistrosCodigoIncompleto: ", datos);
        $scope.errorDatos = null;
        $scope.csvPend = false;
        $scope.listaRegistros = {};

        if (datos.fechaini > datos.fechafin) {
            alert("La fecha inicial debe ser menor que la inicial");
            return;
        } else {

            services.registroscodigoincompleto($scope.datapendientes.currentPage, datos).then(
                function (data) {
                    console.log("registros: ", data);
                    $scope.listaRegistros = data.data[0];
                    //console.log("listaRegistros: ", $scope.listaRegistros);
                    $scope.cantidad = data.data[0].length;
                    $scope.counter = data.data[1];

                    return data.data;
                },

                function errorCallback(response) {
                    if (datos.concepto == undefined || datos.buscar == undefined) {
                        if (datos.fechaini == datos.fechafin) {
                            $scope.errorDatos = "No hay datos para el día:  " + datos.fechaini;
                        } else {
                            $scope.errorDatos = "No hay datos entre " + datos.fechaini + " - " + datos.fechafin;
                        }
                    } else
                        $scope.errorDatos = datos.concepto + " " + datos.buscar + " no existe.";
                }
            );
        }
    }

    $scope.muestraNotas = function (datos) {

        /* $scope.pedido = datos.pedido;
        $scope.TituloModal = "Observaciones para el pedido:";
        $scope.observaciones = datos.observaciones; */
        // console.log( $scope.observaciones);

        console.log("datos", datos);

        $scope.TituloModal = 'Detalle código incompleto';
        $scope.pedido = datos.unepedido;
        $scope.observaciones = datos.observacion;
    }


    $scope.csvRegistros = function () {
        $scope.csvPend = false;
        if ($scope.RegistrosCodigoIncompleto.fechaini > $scope.RegistrosCodigoIncompleto.fechafin) {
            alert("La fecha inicial debe ser menor que la inicial");
            return;
        } else {

            services.expCsvRegistrosCodigoIncompleto($scope.RegistrosCodigoIncompleto, $rootScope.galletainfo).then(
                function (data) {
                    // console.log(data.data[0]);
                    window.location.href = "tmp/" + data.data[0];
                    $scope.csvPend = true;
                    $scope.counter = data.data[1];
                    //console.log(data.data);
                    return data.data;
                },
                function errorCallback(response) {
                    $scope.errorDatos = "No hay datos.";
                    $scope.csvPend = false;
                }
            );
        }
    };

    $scope.maxSize = 5;
    $scope.datapendientes = {maxSize: 5, currentPage: 1, numPerPage: 100, totalItems: 0};
    $scope.BuscarRegistrosCodigoIncompleto($scope.datapendientes.currentPage);
});


/* ---------------------------------------------------------------- */
/*                         FIN CODIGO INCOMPLETO                    */
/* ---------------------------------------------------------------- */


app.controller('brutalForceCtrl', function ($scope, $rootScope, $location, $route, $routeParams, $cookies, $cookieStore, $timeout, services, fileUpload) {
    $scope.formularioBrutal = {};
    $scope.pedidoexiste = false;
    $scope.pedidoNoexiste = false;


    $scope.validaTrans = function () {
        if ($scope.formularioBrutal.tipoTrans == "Reconfigurar" && $scope.formularioBrutal.accion == "Gestión AAA") {
            $scope.vernumSape = true;
        } else {
            $scope.vernumSape = false;
        }
    }


    $scope.buscarObservaciones = function () {

        services.Verobservacionasesor($scope.formularioBrutal.pedido).then(function (data) {
            $scope.observacion = data.data[0][0];
            // console.log($scope.observacion );
            if ($scope.observacion.ObservacionAsesor == "") {
                alert("El pedido se encuentra en gestión")
                return;
            } else {
                alert($scope.observacion.ObservacionAsesor);
                return;
            }

        }, function errorCallback(response) {
            alert("No hay información del pedido");
            return;
        });
    }


    $scope.ruta = "#!/actividades";

    /*$scope.validarHorarioBF = function(pedido = '') {

        if(window.location.hash != "#!/brutalForce"){
            return;
        }

        var tiempo = new Date();
        var hora = tiempo.getHours();
        var dia = tiempo.getDay();

        if (hora >= 7 && hora < 19 && dia != 7) {
            if($rootScope.galletainfo.PERFIL == 7){
                return;
            }
            services.contadorPendientesBrutalForce().then(function(data) {
                $scope.respuesta = data.data[0][0];
                var filtersEx = ['1 - B2B', 'B2B', 'C2', 'C3', 'CORPORATE','Corporativo','CORPORATIVO GOBIERNO','CORPORATIVO PRIVADO', 'Pymes', 'PYMES'];
                var countFilterEx = null;
                if(pedido != ''){
                    fetch(`http://10.100.66.254:8080/HCHV/Buscar/${pedido}`)
                    .then( (data) => data.json())
                    .then( (response) => {
                        if ($scope.respuesta.Pendientes > 20) {
                            if(response){
                                countFilterEx = filtersEx.indexOf(response.uNEUENcalculada);
                                if(response.uNERutaTrabajo == 'YAYA' || countFilterEx != -1){
                                    swal({
                                        title: "El pedido corresponde a la categoría priorizada",
                                        type: "success",
                                        position: 'top-end',
                                        showConfirmButton: false,
                                        timer: 3000
                                    });
                                }
                                else{
                                    swal({
                                        title: "Tu pedido no corresponde a la categoría priorizada:",
                                        html: '<div><font size=4><b>ETP:</b> Tipo Trabajo 2037<br/><b>Siebel:</b> OT-C08-Reconfigurar pedido<br/><b>Fénix:</b> O-101 Renumerar o Reconfigurar Oferta<br/><b>Elite:</b> O-101 Renumerar o Reconfigurar Oferta<br/><b>Siebel: </b>OT-C06-Inconsistencia información</font></div>',
                                        type: "warning"
                                    });
                                    window.location = "#!/actividades";
                                }
                            }
                        }
                        else {
                            if(response.uNERutaTrabajo == 'PREMISAS'){
                                swal({
                                    title: "Tu pedido es una premisa y no corresponde a la categoría priorizada:",
                                    html: '<div><font size=4><b>ETP:</b> Tipo Trabajo 2037<br/><b>Siebel:</b> OT-C08-Reconfigurar pedido<br/><b>Fénix:</b> O-101 Renumerar o Reconfigurar Oferta<br/><b>Elite:</b> O-101 Renumerar o Reconfigurar Oferta<br/><b>Siebel: </b>OT-C06-Inconsistencia información</font></div>',
                                    type: "warning"
                                });
                                window.location = "#!/actividades";
                            }
                        }
                    })
                    .catch( (err) => {
                        console.warn(err);
                    });
                }
                else{
                    if ($scope.respuesta.Pendientes > 20){
                        Swal.fire({
                            title: "En el momento hay " + $scope.respuesta.Pendientes + " transacciones en proceso, los tiempos de atención son altos, procede con el pendiente que corresponda:",
                            html: '<div><font size=4><div class="label-premisas" style="text-align: left; padding-top: 20px;">Si tienes un pedido prioritario por favor valida tu ingreso especial.</div></font></div>',
                            type: "warning",
                            customClass: 'custom-sweet-alert',
                            input: 'text',
                            inputPlaceholder: 'Introduzca el pedido a comprobar',
                            showCancelButton: true,
                            confirmButtonText: 'Comprobar',
                            cancelButtonText: 'Cancelar',
                            showLoaderOnConfirm: true,
                            preConfirm: (pedido) => {
                                return fetch(`http://10.100.66.254:8080/HCHV/Buscar/${pedido}`)
                                .then(response => {
                                    if (!response.ok) {
                                        throw new Error(response.statusText)
                                    }
                                    return response.json()
                                })
                                .catch(error => {
                                    Swal.showValidationMessage(`Petición Fallida: ${error}`)
                                })
                            },
                            allowOutsideClick: () => !Swal.isLoading()
                            })
                            .then((result) => {
                                if (result.value != undefined) {
                                    countFilterEx = filtersEx.indexOf(result.value.uNEUENcalculada);
                                    if(result.value.uNERutaTrabajo == 'YAYA' || countFilterEx != -1){
                                        swal({
                                            title: "El pedido corresponde a la categoría priorizada",
                                            type: "success",
                                            position: 'top-end',
                                            showConfirmButton: false,
                                            timer: 3000
                                        });
                                    }
                                    else{
                                        swal({
                                            title: "Tu pedido no corresponde a la categoría priorizada:",
                                            html: '<div><font size=4><b>ETP:</b> Tipo Trabajo 2037<br/><b>Siebel:</b> OT-C08-Reconfigurar pedido<br/><b>Fénix:</b> O-101 Renumerar o Reconfigurar Oferta<br/><b>Elite:</b> O-101 Renumerar o Reconfigurar Oferta<br/><b>Siebel: </b>OT-C06-Inconsistencia información</font></div>',
                                            type: "warning"
                                        });
                                        window.location = "#!/actividades";
                                    }
                                }
                                else{
                                    window.location = "#!/actividades";
                                }
                            });
                    }
                }


            }, function errorCallback(response) {
                alert("Por favor reportar con el Administrador de la página");
                return;
            });


        }  else {
                    Swal(
                            'El ingreso de solicitudes solo esta disponible de lunes a sábado entre las 7 a.m. y las 7 p.m.',
                        )
                        window.location = "#!/actividades";
                }
    }*/

    $scope.validarHorarioBF = function (pedido = '') {

        if (window.location.hash != "#!/brutalForce") {
            return;
        }

        var tiempo = new Date();
        var hora = tiempo.getHours();
        var dia = tiempo.getDay();

        //if (hora >= 7 && hora < 19 && dia != 7) {
        if (hora >= 7 && hora < 19) {
            if ($rootScope.galletainfo.PERFIL == 7) {
                return;
            }
            services.contadorPendientesBrutalForce().then(function (data) {
                $scope.respuesta = data.data[0][0];
                if (pedido != '') {
                    fetch(`http://10.100.66.254:8080/HCHV/Buscar/${pedido}`)
                        .then((data) => data.json())
                        .then((response) => {
                            if (response) {
                                if (response.taskType.indexOf('Cambio_Domicilio') !== -1 || response.uNERutaTrabajo.indexOf('NUEVO CON TRASLADO') !== -1) {
                                    swal({
                                        title: "El pedido corresponde a la categoría priorizada",
                                        type: "success",
                                        position: 'top-end',
                                        showConfirmButton: false,
                                        timer: 3000
                                    });
                                } else {
                                    swal({
                                        title: "Tu pedido no corresponde a la categoría priorizada:",
                                        html: '<div>Solo se reciben solicitudes de traslado</div>',
                                        type: "warning"
                                    });
                                    window.location = "#!/actividades";
                                }
                            }
                        })
                        .catch((err) => {
                            console.warn(err);
                        });
                } else {

                    //return false; // DESAHABILITAR VENTANA EMERGENTE EN BRUTAL FORCE

                    Swal.fire({
                        title: "Los tiempos de atención son altos, procede con el pendiente que corresponda:",
                        html: '<div><font size=4><div class="label-premisas" style="text-align: left; padding-top: 20px;">Si tienes un pedido prioritario por favor valida tu ingreso especial.</div></font></div>',
                        type: "warning",
                        customClass: 'custom-sweet-alert',
                        input: 'text',
                        inputPlaceholder: 'Introduzca el pedido a comprobar',
                        showCancelButton: true,
                        confirmButtonText: 'Comprobar',
                        cancelButtonText: 'Cancelar',
                        showLoaderOnConfirm: true,
                        preConfirm: (pedido) => {
                            return fetch(`http://10.100.66.254:8080/HCHV/Buscar/${pedido}`)
                                .then(response => {
                                    if (!response.ok) {
                                        throw new Error(response.statusText)
                                    }
                                    return response.json()
                                })
                                .catch(error => {
                                    Swal.showValidationMessage(`Petición Fallida: ${error}`)
                                })
                        },
                        allowOutsideClick: () => !Swal.isLoading()
                    })
                        .then((result) => {
                            if (result.value != undefined) {

                                let tasktype = (result.value.taskType == null) ? '' : result.value.taskType;
                                let unerutatrabajo = (result.value.uNERutaTrabajo == null) ? '' : result.value.uNERutaTrabajo;

                                if (tasktype.indexOf('Cambio_Domicilio') !== -1 || unerutatrabajo.indexOf('NUEVO CON TRASLADO') !== -1) {
                                    swal({
                                        title: "El pedido corresponde a la categoría priorizada",
                                        type: "success",
                                        position: 'top-end',
                                        showConfirmButton: false,
                                        timer: 3000
                                    });
                                } else {
                                    swal({
                                        title: "Tu pedido no corresponde a la categoría priorizada:",
                                        html: '<div>Solo se reciben solicitudes de traslado</div>',
                                        type: "warning"
                                    });
                                    window.location = "#!/actividades";
                                }
                            } else {
                                window.location = "#!/actividades";
                            }
                        });
                }

            }, function errorCallback(response) {
                alert("Por favor reportar con el Administrador de la página");
                return;
            });


        } else {
            Swal(
                //'El ingreso de solicitudes solo esta disponible de lunes a sábado entre las 7 a.m. y las 7 p.m.',
                'El ingreso de solicitudes solo esta disponible entre las 7 a.m. y las 7 p.m.',
            )
            window.location = "#!/actividades";
        }
    }

    $scope.validarHorarioBF();

    $scope.GuardarGestion = async function () {

        emailRegex = /^[-\w.%+]{1,64}@(?:[A-Z0-9-]{1,63}\.){1,125}[A-Z]{2,63}$/i;
        celRegex = /^3[\d]{9}$/;
        var tiempo = new Date();
        var hora = tiempo.getHours();
        var dia = tiempo.getDay();

        var pedido = $scope.formularioBrutal.pedido;

        var filtersEx = ['1 - B2B', 'B2B', 'C2', 'C3', 'CORPORATE', 'Corporativo', 'CORPORATIVO GOBIERNO', 'CORPORATIVO PRIVADO', 'Pymes', 'PYMES'];
        var countFilterEx = null;

        try {
            var prioridadBFQuery = await fetch(`http://10.100.66.254:8080/HCHV/Buscar/${pedido}`);
            var prioridadBF = await prioridadBFQuery.json();
            countFilterEx = filtersEx.indexOf(prioridadBF.uNEUENcalculada);
            if (countFilterEx != -1) {
                $scope.formularioBrutal.prioridad = prioridadBF.uNEUENcalculada;
            } else if (prioridadBF.uNERutaTrabajo == 'PREMISAS' || prioridadBF.uNERutaTrabajo == 'YAYA') {
                $scope.formularioBrutal.prioridad = prioridadBF.uNERutaTrabajo;
            } else {
                $scope.formularioBrutal.prioridad = 'Otro Concepto';
            }
        } catch (error) {
            console.log(error);
            swal({
                title: "Tu pedido esta presentando inconvenientes",
                type: "warning",
                showConfirmButton: false,
                timer: 3000
            });
            return;
        }


        if (hora >= 7 && hora < 19 && dia != 7) {

            if (emailRegex.test($scope.formularioBrutal.correo)) {

                if (celRegex.test($scope.formularioBrutal.celular)) {

                    services.getGuardargestiodespachoBrutal($scope.formularioBrutal, $rootScope.galletainfo).then(function (data) {

                        if (data.status == '200') {
                            $scope.formularioBrutal = {};
                            $scope.pedidoexiste = false;
                            $scope.mensaje = "Gestión guardada correctamente";
                            $scope.pedidoNoexiste = true;
                        }

                    }, function errorCallback(response) {
                        $scope.mensaje = "El pedido ya existe, no es posible guardar nueva gestión.";
                        $scope.pedidoexiste = true;
                        $scope.pedidoNoexiste = false;
                    });

                } else {
                    alert("El Número celular del técnico debe ser formato de celular");
                    return;
                }

            } else {
                alert("El correo debe tener el formato de E-mail: correo@dominio.com");
                return;
            }

        } else {
            Swal(
                'El ingreso de solicitudes solo esta disponible de lunes a sábado entre las 7 a.m. y las 7 p.m.',
            )
            return;
        }

    }

    $scope.ObservacionesBF = function () {
        services.ObsPedidosBF($rootScope.galletainfo).then(function (data) {
            $scope.haypedido = true;
            $scope.datosBF = data.data[0];

            return data.data;
        }, function errorCallback(response) {
            $scope.haypedido = false;
            $scope.mensaje = "No tiene pedidos pendientes!!!"
        });
    }

    $scope.ObservacionesBF();

});

app.controller('usuariosCtrl', function ($scope, $http, $rootScope, $location, $route, $routeParams, $cookies, $timeout, services) {
    $scope.listaUsuarios = {};
    $scope.Usuarios = {};
    $scope.crearuser = {};

    $scope.setPage = function (pageNo) {
        $scope.datapendientes.currentPage = pageNo;
    };

    $scope.pageChanged = function () {
        $scope.buscarUsuario($scope.datapendientes.currentPage);
    };

    $scope.buscarUsuario = function (concepto, usuario) {
        $scope.errorDatos = null;
        $scope.respuestaupdate = null;
        $scope.respuestadelete = null;
        $scope.listaUsuarios = {};
        //console.log($scope.Usuarios);
        services.listadoUsuarios($scope.datapendientes.currentPage, concepto, usuario).then(
            function (data) {
                $scope.listaUsuarios = data.data[0];
                // console.log("la lista "+$scope.listaUsuarios);
                $scope.cantidad = data.data[0].length;
                $scope.counter = data.data[1];

                return data.data;
            },
            function errorCallback(response) {

                $scope.errorDatos = concepto + " " + usuario + " no existe.";

                // console.log($scope.errorDatos);

            });
    };

    $scope.editarModal = function (datos) {
        // console.log(datos);
        $rootScope.datos = datos;
        $scope.idUsuario = datos.ID;
        $scope.UsuarioNom = datos.NOMBRE;
        $rootScope.TituloModal = "Editar Usuario con el ID:";
        //console.log($scope.editaInfo);
    };

    $scope.createUser = function (concepto, tecnico) {
        $scope.errorDatos = null;
        $scope.respuestaupdate = null;
        $scope.respuestadelete = null;
        //console.log($scope.crearuser);
        services.creaUsuario($scope.crearuser).then(
            function (data) {
                // console.log("la lista "+$scope.listaUsuarios);
                $scope.respuestaupdate = "Usuario creado.";
                ;
                return data.data;
            },
            function errorCallback(response) {

                $scope.errorDatos = "Usuario no fue creado.";

                // console.log($scope.errorDatos);
            });
        $scope.buscarUsuario();
    };


    $scope.editUser = function (datos) {
        $scope.errorDatos = null;
        $scope.respuestaupdate = null;
        $scope.respuestadelete = null;
        if (datos.PASSWORD == "") {
            alert("Por favor ingrese la contraseña");
            return;
        } else {
            services.editarUsuario(datos).then(
                function (data) {
                    // $errorDatos=null;
                    $scope.respuestaupdate = "Usuario " + datos.LOGIN + " actualizado exitosamente";
                    //  console.log(datos);
                    //$rootScope.nombre=$scope.respuesta[0].NOMBRE;
                    //$location.path('/home/');
                    return data.data;
                },
                function errorCallback(response) {
                });
        }

        $scope.buscarUsuario();
    };

    $scope.borrarUsuario = function (id) {
        $scope.idBorrar = id;
        $scope.Usuarios = {};
        $scope.errorDatos = null;
        $scope.respuestaupdate = null;
        $scope.respuestadelete = null;
        swal({
            title: "Aviso",
            text: "Esta función ha sido deshabilitada, para eliminar usuarios de la plataforma debe comunicarse con desarrollo.",
            type: "error",
            confirmButtonClass: "btn-danger",
            confirmButtonText: "Aceptar",
            closeOnConfirm: false
        });
        // Temporalmente deshabilitado
        // services.deleteUsuario($scope.idBorrar).then(
        //     function(data) {
        //         $scope.respuestadelete = "Usuario " + $rootScope.datos.LOGIN + " eliminado exitosamente";
        //     },
        //     function errorCallback(response) {
        //         $scope.errorDatos = "No se borro";
        //     }
        // );
        $scope.buscarUsuario($scope.datapendientes.currentPage);
    };

    $scope.maxSize = 5;
    $scope.datapendientes = {maxSize: 5, currentPage: 1, numPerPage: 100, totalItems: 0};
    $scope.buscarUsuario($scope.datapendientes.currentPage);

});

app.controller('tecnicosCtrl', function ($scope, $http, $rootScope, $location, $route, $routeParams, $cookies, $timeout, services) {
    $scope.listaTecnicos = {};
    $scope.tecnico = null;
    $scope.concepto = null;
    $scope.crearTecnico = {};


    $scope.setPage = function (pageNo) {
        $scope.datapendientes.currentPage = pageNo;
    };

    $scope.pageChanged = function () {
        $scope.buscarTecnico($scope.datapendientes.currentPage);
    };

    $scope.buscarTecnico = function (concepto, tecnico) {
        $scope.listaTecnicos = [];
        $scope.errorDatos = null;
        $scope.respuestaupdate = null;
        $scope.respuestadelete = null;
        //console.log($scope.Usuarios);
        services.listadoTecnicos($scope.datapendientes.currentPage, concepto, tecnico).then(
            function (data) {
                $scope.listaTecnicos = data.data[0];
                // console.log("la lista "+$scope.listaUsuarios);
                $scope.cantidad = data.data[0].length;
                $scope.counter = data.data[1];

                return data.data;
            },
            function errorCallback(response) {

                $scope.errorDatos = concepto + " " + tecnico + " no existe.";

                // console.log($scope.errorDatos);

            });
    };

    $scope.createTecnico = function () {
        var id_tecnico = "";
        $scope.errorDatos = null;
        $scope.respuestaupdate = null;
        $scope.respuestadelete = null;
        //console.log($scope.crearTecnico);
        services.creaTecnico($scope.crearTecnico, id_tecnico).then(
            function (data) {
                // console.log("la lista "+$scope.listaUsuarios);
                $scope.respuestaupdate = "Técnico creado.";
                ;
                return data.data;
            },
            function errorCallback(response) {

                $scope.errorDatos = "Técnico no fue creado.";

                // console.log($scope.errorDatos);

            });
    };


    $scope.editarModal = function (datos) {
        console.log(datos);
        $rootScope.datos = datos;
        $scope.idTecnico = datos.ID;
        $scope.TecnicoNom = datos.NOMBRE;
        $scope.UsuarioLog = datos.LOGIN;
        $rootScope.TituloModal = "Editar Técnico con el ID:";
        //console.log($scope.editaInfo);
    };

    $scope.edittecnico = function (datos) {
        $scope.errorDatos = null;
        $scope.respuestaupdate = null;
        $scope.respuestadelete = null;
        console.log(datos);
        services.editarTecnico(datos).then(
            function (data) {
                // $errorDatos=null;
                $scope.respuestaupdate = "Técnico " + datos.NOMBRE + " actualizado exitosamente";
                //console.log(datos);
                //$rootScope.nombre=$scope.respuesta[0].NOMBRE;
                //$location.path('/home/');
                return data.data;
            },
            function errorCallback(response) {
            });

    };


    $scope.borrarTecnico = function (id) {
        $scope.idBorrar = id;
        $scope.Tecnico = {};
        $scope.errorDatos = null;
        $scope.respuestaupdate = null;
        $scope.respuestadelete = null;
        services.deleteTecnico($scope.idBorrar).then(
            function (data) {

                $scope.respuestadelete = "Técnico " + $rootScope.datos.NOMBRE + " eliminado exitosamente";
            },
            function errorCallback(response) {

                $scope.errorDatos = "No se borro";

                //console.log($scope.errorDatos);

            }
        );
        $scope.buscarTecnico($scope.datapendientes.currentPage);
    };
    $scope.maxSize = 5;
    $scope.datapendientes = {maxSize: 5, currentPage: 1, numPerPage: 100, totalItems: 0};

    $scope.buscarTecnico($scope.datapendientes.currentPage);
});

app.controller('turnosCtrl', function ($scope, $http, $rootScope, $location, $route, $routeParams, $cookies, $cookieStore, $timeout, services, fileUpload) {
    $scope.errorDatos = null;
    $scope.turnos = [{id: '1', fecha: '', horaInicio: '', horaFin: '', usuariocrea: $rootScope.galletainfo.LOGIN}];
    $scope.cumple = {};
    $scope.editar = false;
    var tiempo = new Date().getTime();
    var date1 = new Date();
    var year = date1.getFullYear();
    var month = (date1.getMonth() + 1 <= 9) ? '0' + (date1.getMonth() + 1) : (date1.getMonth() + 1);
    var day = (date1.getDate() <= 9) ? '0' + date1.getDate() : date1.getDate();

    $scope.fechaIni = year + "-" + month + "-" + day;
    $scope.fechaFin = year + "-" + month + "-" + day;
    $scope.cumple.fechaIni = year + "-" + month + "-" + day;

    $scope.ingresoTurnos = function () {
        services.getguardarTurnos($scope.turnos).then(function (data) {
            $scope.turnos = [{id: '1', fecha: '', horaInicio: '', horaFin: '', usuariocrea: $rootScope.galletainfo.LOGIN}];
            $scope.obtenerlistaTurnos();
        });
    }

    $scope.obtenercumplmientoTurnos = function () {
        services.getcumplmientoTurnos($scope.cumple).then(function (data) {
            $scope.cumplimientoTurno = data.data[0];
            $scope.nohaycumplimiento = null;
            return data.data;
        }, function errorCallback(response) {
            $scope.nohaycumplimiento = "No hay datos!!";
        });
    }

    $scope.obtenerlistaTurnos = function () {
        services.getlistaTurnos($scope.fechaIni, $scope.fechaFin).then(function (data) {
            $scope.historicoturnos = data.data[0];
            $scope.errorDatos = null;
            return data.data;
        }, function errorCallback(response) {
            $scope.errorDatos = "No hay datos!!";
            $scope.historicoturnos = {};
        });
    }


    $scope.desacargarAdherencia = function () {
        //console.log($scope.gestion_Pendientes);
        services.csvAdherenciaTurnos($scope.fechaIni, $scope.fechaFin, $rootScope.galletainfo).then(
            function (data) {
                //  console.log(data.data[0]);
                if (data.data[0] !== undefined) {
                    window.location.href = "tmp/" + data.data[0];
                    $scope.csvPend = true;
                    $scope.counter = "Se exportaron: " + data.data[1] + " Registros";
                    console.log($scope.counter);
                    return data.data;
                } else {
                    $scope.counter = "No hay datos para exportar";
                }
            });
    };


    $scope.borrarTurno = function (idturno) {
        services.borrarTurno(idturno).then(function (data) {
            $scope.obtenerlistaTurnos();
        });
    }


    $scope.usuariosTurnosSeguimiento = function () {
        services.getusuariosTurnos().then(function (data) {
            $scope.usuarios = data.data[0];
            return data.data;
        });
    }


    $scope.addNuevaNovedad = function (usuario) {
        var newItemNo = $scope.turnos.length + 1;

        $scope.turnos.push({'id': +newItemNo, fecha: '', horaIni: '', horaFin: '', usuariocrea: $rootScope.galletainfo.LOGIN});
        console.log($scope.turnos);
        //console.log(usuario);
        //$scope.crearnovedad();
    };

    $scope.updateStatus = function (data) {
        services.updateTurnos(data).then(function (data) {
            $scope.obtenerlistaTurnos();
        });
    };

    $scope.statuses = [
        {value: 'Turno', novedades: 'Turno'},
        {value: 'Vacaciones', novedades: 'Vacaciones'},
        {value: 'Licencia', novedades: 'Licencia'},
        {value: 'Incapacidad', novedades: 'Incapacidad'}
    ];

    $scope.removeNuevaNovedad = function () {
        var lastItem = $scope.turnos.length - 1;
        if (lastItem != 0) {
            $scope.turnos.splice(lastItem);
            //console.log($scope.novedades);
        }

    };

    $scope.usuariosTurnosSeguimiento();
    $scope.obtenerlistaTurnos();
    $scope.obtenercumplmientoTurnos();
});


app.controller('AlarmasCtrl', function ($scope, $http, $rootScope, $location, $route, $routeParams, $cookies, $timeout, services) {
    $scope.crearAlarma = {};
    $scope.listaAlarmas = {};

    $scope.listadoAlarmas = function () {
        services.listadoAlarmas().then(
            function (data) {
                $scope.listaAlarmas = data.data;
                //console.log("la lista "+$scope.listaAlarmas);

                return data.data;
            });
    }

    $scope.crearAlarma = function (info) {
        services.creaAlarma(info).then(
            function (data) {
                // console.log("la lista "+$scope.listaUsuarios);
                $scope.respuestaupdate = "Alarma creado.";
                services.listadoAlarmas().then(
                    function (data) {
                        $scope.listaAlarmas = data.data;
                        //console.log("la lista "+$scope.listaAlarmas);

                        return data.data;
                    });
            },
            function errorCallback(response) {
                $scope.errorDatos = "Alarma no fue creada.";
            });
        $scope.listadoAlarmas();
    };


    $scope.procesos = function () {
        $scope.validaraccion = false;
        $scope.validarsubaccion = false;
        services.getProcesos().then(function (data) {
            $scope.listadoProcesos = data.data[0];
            $scope.listadoAcciones = {};
        });
        //console.log($scope.listadoProcesos);
    };

    $scope.editarModal = function (datos) {
        //console.log(datos);
        $scope.datosAlarmas = datos;

    };

    $scope.EditarDatosAlarma = function () {
        $scope.errorDatos = null;
        $scope.respuestaupdate = null;
        $scope.respuestadelete = null;

        //     console.log($scope.datosAlarmas);
        services.editAlarma($scope.datosAlarmas).then(
            function (data) {
                // $errorDatos=null;
                $scope.respuestaupdate = "Alarma " + $scope.datosAlarmas.nombre_alarma + " actualizado exitosamente";
                //console.log(datos);
                //$rootScope.nombre=$scope.respuesta[0].NOMBRE;
                //$location.path('/home/');
                services.listadoAlarmas().then(
                    function (data) {
                        $scope.listaAlarmas = data.data;
                        //console.log("la lista "+$scope.listaAlarmas);

                        return data.data;
                    });
            },
            function errorCallback(response) {
            });

    };

    $scope.borrarAlarma = function (id) {
        $scope.idBorrar = id;
        $scope.errorDatos = null;
        $scope.respuestaupdate = null;
        $scope.respuestadelete = null;
        services.deleteAlarma($scope.idBorrar).then(
            function (data) {

                $scope.respuestadelete = "Alarma eliminada exitosamente";
                services.listadoAlarmas().then(
                    function (data) {
                        $scope.listaAlarmas = data.data;
                        //console.log("la lista "+$scope.listaAlarmas);

                        return data.data;
                    });
            },
            function errorCallback(response) {
                $scope.errorDatos = "No se borro";
                //console.log($scope.errorDatos);
            }
        );
    };

    $scope.calcularAcciones = function (proceso) {
        console.log("entro calcularAcciones");
        $scope.listadoAcciones = {};
        $scope.validarsubaccion = false;
        if (proceso == "") {
            $scope.validaraccion = false;
            $scope.validarsubaccion = false;
        } else {
            services.getAcciones(proceso).then(function (data) {
                $scope.listadoAcciones = data.data[0];
                $scope.validaraccion = true;
                $scope.validarsubaccion = false;
            });
        }
    };

    $scope.calcularSubAcciones = function (proceso, accion) {
        console.log("entro calcularSubAcciones");
        $scope.listadoSubAcciones = {};
        $scope.validarsubaccion = true;
        // console.log(proceso, accion);
        services.getSubAcciones(proceso, accion).then(function (data) {
            $scope.listadoSubAcciones = data.data[0];
            $scope.validarsubaccion = true;
        }, function errorCallback(response) {
            $scope.validarsubaccion = false;
        });
    };

    $scope.procesos();
    $scope.listadoAlarmas();
});

app.controller('MicrozonasCtrl',function ($scope, $http, $rootScope, $location, $route, $routeParams, $cookies, $timeout, services){
    console.log('Hola');

    function microzona(data){
        let response = {};
        services.microzona(data).then(
            function (data) {
                console.log(data,' RRRR');
                $scope.data = data.data;
                $("#microzonas").modal('show');
            },
            function errorCallback(response) {
                response = response
            });
    }

    $scope.searchMicrozona = async (data) => {
        try {
            var autocompleteQuery = await fetch("http://10.100.66.254:8080/HCHV_DEV/microzona/" + data);
            var autocompleteData = await autocompleteQuery.json();

            console.log(autocompleteData, '  kokoko');


        } catch (error) {
            console.log(error);
        }

    }

    $scope.descargar = function (datos){
        console.log(datos, ' kokoko')
        var data = datos;
        var array = typeof data != 'object' ? JSON.parse(data) : data;
        var str = '';
        var column = `Tarea_consultada|| Microzona_actual_click|| Micro_infra|| Micro_barrio|| Municipio|| Coordenadas|| Direccion|| Obseervaciones_infra \r\n`;
        str += column;
        for (var i = 0; i < array.length; i++) {
            var line = '';
            for (var index in array[i]) {
                if (line != '') line += '||'
                line += array[i][index];
            }

            str += line + '\r\n';
        }
        var dateCsv = new Date();
        var yearCsv = dateCsv.getFullYear();
        var monthCsv = (dateCsv.getMonth() + 1 <= 9) ? '0' + (dateCsv.getMonth() + 1) : (dateCsv.getMonth() + 1);
        var dayCsv = (dateCsv.getDate() <= 9) ? '0' + dateCsv.getDate() : dateCsv.getDate();
        var fullDateCsv = yearCsv + "-" + monthCsv + "-" + dayCsv;


        var blob = new Blob([str]);
        var elementToClick = window.document.createElement("a");
        elementToClick.href = window.URL.createObjectURL(blob, {type: 'text/csv'});
        elementToClick.download = "csv_microzonas-" + fullDateCsv + ".csv";
        elementToClick.click();

    }

    $scope.tarea = ''
    //http://10.100.66.254:8080/BB8/contingencias/Buscar/GetClick/15151515
    $scope.searchMicrozona = function (data){

        $scope.url = "http://10.100.66.254:8080/HCHV_DEV/microzona/" + data;
        $http.get($scope.url, {timeout: 2000})
            .then(function (data) {
                //console.log(data.data)
                $scope.data = data.data;
                microzona($scope.data);
                //$("#microzonas").modal('show');
            }).catch(function (error){
                console.log(error);
        })

    }
});

app.directive('cookie', function ($rootScope, $cookies) {

    return {

        link: function ($scope, el, attr, ctrl) {


            if ($cookies.get('usuarioseguimiento') !== undefined) {
                //console.log("carlitos");
                $scope.galletainfo = JSON.parse($cookies.get("usuarioseguimiento"));
                //$rootScope.galletainfo = $rootScope.galletainfo;
                $rootScope.permiso = true;
                //console.log($rootScope.galletainfo);
                /* if($scope.galletainfo.CARGO_ID==1){
                      $rootScope.perfil="ADMIN";
                 }else{ $rootScope.perfil="ASESOR";} */

            }

        },

        templateUrl: 'partial/navbar.html'

    };
});

app.directive('popover', function () {
    return function (scope, elem) {
        elem.popover();
    }
});

app.directive('tooltip', function () {
    return function (scope, elem) {
        elem.tooltip();
    }
});

app.config([
    '$compileProvider',
    function ($compileProvider) {
        $compileProvider.aHrefSanitizationWhitelist(/^\s*(https?|ftp|mailto|sip):/);
        // Angular before v1.2 uses $compileProvider.urlSanitizationWhitelist(...)
    }
]);

app.config(['$httpProvider', function ($httpProvider) {
    $httpProvider.interceptors.push('LoadingInterceptor');
}]);

app.config(['$routeProvider',
    function ($routeProvider) {

        $routeProvider
            .when('/', {
                title: 'Login',
                templateUrl: 'partial/login.html',
                controller: 'loginCtrl'
            })

            .when('/actividades', {
                title: 'Documentación de Pedidos',
                templateUrl: 'partial/actividades.html',
                controller: 'actividadesCtrl',
                authorize: true
            })

            .when('/registros', {
                title: 'Registros',
                templateUrl: 'partial/registros.html',
                controller: 'registrosCtrl',
                authorize: true
            })

            .when('/registrossoportegpon', {
                title: 'Registros Soporte GPON',
                templateUrl: 'partial/registrossoportegpon.html',
                controller: 'registrossoportegponCtrl',
                authorize: true
            })

            .when('/registroscodigoincompleto', {
                title: 'Registros Soporte GPON',
                templateUrl: 'partial/registroscodigoincompleto.html',
                controller: 'registroscodigoincompletoCtrl',
                authorize: true
            })

            .when('/usuarios', {
                title: 'Usuarios',
                templateUrl: 'partial/usuarios.html',
                controller: 'usuariosCtrl',
                authorize: true
            })

            .when('/tecnicos', {
                title: 'Tecnicos',
                templateUrl: 'partial/tecnicos.html',
                controller: 'tecnicosCtrl',
                authorize: true
            })

            .when('/listadoAlarmas', {
                title: 'Alarmas',
                templateUrl: 'partial/listadoAlarmas.html',
                controller: 'AlarmasCtrl',
                authorize: true
            })

            .when('/mesaoffline/mesaoffline', {
                title: 'Mesa Offline',
                templateUrl: 'partial/mesaoffline/mesaoffline.html',
                controller: 'mesaofflineCtrl',
                authorize: true
            })

            .when('/mesaoffline/registrosOffline', {
                title: 'Registros Offline',
                templateUrl: 'partial/mesaoffline/registrosOffline.html',
                controller: 'registrosOfflineCtrl',
                authorize: true
            })

            .when('/brutalForce', {
                title: 'Brutal Force',
                templateUrl: 'partial/brutalForce.html',
                controller: 'brutalForceCtrl',
                authorize: true
            })

            .when('/contingencias', {
                title: 'Contingencias aprovisionamiento',
                templateUrl: 'partial/contingencias.html',
                controller: 'contingenciasCtrl',
                authorize: true
            })

            .when('/nivelacion', {
                title: 'Gestión Nivelación',
                templateUrl: 'partial/nivelacion.html',
                controller: 'nivelacionCtrl',
                authorize: true
            })

            .when('/GestionNivelacion', {
                title: 'Gestión Nivelación',
                templateUrl: 'partial/GestionNivelacion.html',
                controller: 'GestionNivelacionCtrl',
                authorize: true
            })

            .when('/Gestioncontingencias', {
                title: 'Gestión Contingencias',
                templateUrl: 'partial/Gestioncontingencias.html',
                controller: 'GestioncontingenciasCtrl',
                authorize: true
            })

            .when('/gestionsoportegpon', {
                title: 'Gestión Soporte Gpon',
                templateUrl: 'partial/Gestionsoportegpon.html',
                controller: 'GestionsoportegponCtrl',
                authorize: true
            })

            .when('/gestioncodigoincompleto', {
                title: 'Gestión Código Incompleto',
                templateUrl: 'partial/Gestioncodigoincompleto.html',
                controller: 'GestioncodigoincompletoCtrl',
                authorize: true
            })

            .when('/premisasInfraestructuras', {
                title: 'Premisas Infraestructuras',
                templateUrl: 'partial/premisasInfraestructuras.html',
                controller: 'premisasInfraestructurasCtrl',
                authorize: true
            })

            .when('/novedadesVisita', {
                title: 'Novedades Visita',
                templateUrl: 'partial/novedadesVisita.html',
                controller: 'novedadesVisitaCtrl',
                authorize: true
            })

            .when('/contrasenaClick', {
                title: 'Contraseñas ClickSoftware',
                templateUrl: 'partial/contrasenaClick.html',
                controller: 'contrasenasClickCtrl',
                authorize: true
            })

            .when('/turnos', {
                title: 'Gestión turnos',
                templateUrl: 'partial/turnos.html',
                controller: 'turnosCtrl',
                authorize: true
            })

            .when('/quejasGo', {
                title: 'Quejas Gestión Operativa',
                templateUrl: 'partial/quejasGo.html',
                controller: 'quejasGoCtrl',
                authorize: true
            })

            .when('/consultaSara', {
                title: 'Consulta SARA',
                templateUrl: 'partial/consultaSara.html',
                controller: 'saraCtrl',
                authorize: true
            })

            .when('/microzonas', {
                title: 'microzonas',
                templateUrl: 'partial/microzonas.html',
                controller: 'MicrozonasCtrl',
                authorize: true
            })


    }
]);

app.run(function ($rootScope, services) {

    $rootScope.fechaProceso = function () {
        var tiempo = new Date().getTime();
        var date1 = new Date();
        var year = date1.getFullYear();
        var month = (date1.getMonth() + 1 <= 9) ? '0' + (date1.getMonth() + 1) : (date1.getMonth() + 1);
        var day = (date1.getDate() <= 9) ? '0' + date1.getDate() : date1.getDate();
        var hour = (date1.getHours() <= 9) ? '0' + date1.getHours() : date1.getHours();
        var minute = (date1.getMinutes() <= 9) ? '0' + date1.getMinutes() : date1.getMinutes();
        var seconds = (date1.getSeconds() <= 9) ? '0' + date1.getSeconds() : date1.getSeconds();

        tiempo = year + "-" + month + "-" + day + " " + hour + ":" + minute + ":" + seconds;
        return tiempo;
    };


})

app.run(['$location', '$rootScope', '$route', '$cookies', 'services', function ($location, $rootScope, $route, $cookies, services) {

    $rootScope.$on("$routeChangeStart", function (evt, to, from) {
        if ($cookies.get('usuarioseguimiento') == undefined) {
            $location
                .path("/")
        }
        ;
    });

    $rootScope.$on('$routeChangeSuccess', function (event, current, previous) {

        $rootScope.title = current.$$route.title;
        $rootScope.tituloPagina = 'Seguimiento a pedidos - ' + current.$$route.title;

    });

    $rootScope.$on("$routeChangeError", function (evt, to, from, error) {

    });


    $rootScope.executeCopy = function executeCopy(text) {
        var input = document.createElement('textarea');
        document.body.appendChild(input);
        input.value = (text);
        //input.focus();
        input.select();
        document.execCommand('Copy');
        input.remove();
    };

}]);

app.run(['$rootScope', 'services', function ($rootScope, services) {

    $rootScope.ciudades = function () {
        services.getCiudades().then(function (data) {
            $rootScope.listadoCiudades = data.data[0];
            $rootScope.listadoDepartamentos = data.data[1];
        });
    };


    $rootScope.regionesTip = function () {
        services.getRegionesTip().then(function (data) {
            $rootScope.listadoRegiones = data.data[0];
        });
    };


    $rootScope.Listapreguntas = [
        {ID: 1, PREGUNTA: '1. ¿Estuvo con el técnico en el momento de la Instalación?'},
        {ID: 2, PREGUNTA: '2. ¿Qué tan satisfecho te encuentras con la  Instalación Realizada?'},
        {ID: 3, PREGUNTA: '3. ¿Qué tan fácil te pareció el proceso de Instalación de tu servicio o servicios?'},
        {ID: 4, PREGUNTA: '4.  ¿Según tu experiencia en el proceso de instalación, recomendarías a UNE a otra persona?'},
        {ID: 5, PREGUNTA: '5. ¿Como calificarías, el cumplimiento de la cita pactada para realizar tu instalación?'},
        {ID: 6, PREGUNTA: '6. ¿Consideras que la oferta que se te ofreció en la venta, fué la misma que se te instaló?'},
        {ID: 7, PREGUNTA: '7. ¿Después de realizada la instalación se te ha presentado algúna falla o inconveniente con el servicio de UNE?'},
        {ID: 8, PREGUNTA: '8. ¿El tecnico te dio información sobre el funcionamiento de los servicios instalados?'}
    ];

    $rootScope.Listapreguntasrepa = [
        {ID: 1, PREGUNTA: '1. ¿Estuvo con el técnico en el momento de la reparación?'},
        {ID: 2, PREGUNTA: '2. ¿Qué tan satisfecho te encuentras con la reparación Realizada?'},
        {ID: 3, PREGUNTA: '3. ¿Qué tan fácil te pareció el proceso de reparación de tu servicio o servicios?'},
        {ID: 4, PREGUNTA: '4. ¿Según tu experiencia en el proceso de reparación, recomendarías a UNE, a otra persona?'},
        {ID: 5, PREGUNTA: '5. ¿Como calificarías, el cumplimiento de la cita pactada para realizar tu reparación?'},
        {ID: 6, PREGUNTA: '6. ¿El técnico te demostró que los servicios quedaron funcionando correctamente?'},
        {ID: 7, PREGUNTA: '7. ¿El técnico revisó el correcto funcionamiento de cada uno de los servicios instalados en el hogar?'},
        {ID: 8, PREGUNTA: '8. ¿Después de realizada la reparación se te ha presentado algúna falla o inconveniente con el servicio de UNE?'}
    ];

    $rootScope.empresas = [
        {ID: 1, EMPRESA: 'UNE'},
        {ID: 0, EMPRESA: 'SIN EMPRESA'},
        {ID: 3, EMPRESA: 'REDES Y EDIFICACIONES'},
        {ID: 4, EMPRESA: 'ENERGIA INTEGRAL ANDINA'},
        {ID: 6, EMPRESA: 'EAGLE'},
        {ID: 7, EMPRESA: 'SERVTEK'},
        {ID: 8, EMPRESA: 'FURTELCOM'},
        {ID: 9, EMPRESA: 'EMTELCO'},
        {ID: 10, EMPRESA: 'CONAVANCES'},
        {ID: 11, EMPRESA: 'TECHCOM'}
    ];

    $rootScope.procesosoffline = [
        {ID: "Instalaciones", PROCESO: 'Instalaciones'},
        {ID: "Reparaciones", PROCESO: 'Reparaciones'}
    ];

    $rootScope.estadosComercial = [
        {ID: 'Cobertura 3G', ESTADO: 'Cobertura 3G'},
        {ID: 'Decisión Usuario 42', ESTADO: 'Decisión Usuario 42'},
        {ID: 'Estudio Legal 82', ESTADO: 'Estudio Legal 82'},
        {ID: 'Oferta Economica', ESTADO: 'Oferta Económica'},
        {ID: 'PEREP', ESTADO: 'PEREP'},
        {ID: 'PETEC', ESTADO: 'PETEC'},
        {ID: 'PFACT', ESTADO: 'PFACT'},
        {ID: 'PORDE', ESTADO: 'PORDE'}
    ];

    $rootScope.productos = [
        {ID: 'ADSL-Internet', PRODUCTO: 'ADSL-Internet'},
        {ID: 'ADSL-IPTV', PRODUCTO: 'ADSL-IPTV'},
        {ID: 'ADSL-ToIP', PRODUCTO: 'ADSL-ToIP'},
        {ID: 'HFC-Internet', PRODUCTO: 'HFC-Internet'},
        {ID: 'HFC-ToIP', PRODUCTO: 'HFC-ToIP'},
        {ID: 'HFC-TV_Basica', PRODUCTO: 'HFC-TV Basica'},
        {ID: 'HFC-TV_Digital', PRODUCTO: 'HFC-TV Digital'},
        {ID: 'GPON', PRODUCTO: 'GPON'},
        // { ID: 'GPON-Internet', PRODUCTO: 'GPON-Internet' },
        // { ID: 'GPON-IPTV', PRODUCTO: 'GPON-IPTV' },
        // { ID: 'GPON-ToIP', PRODUCTO: 'GPON-ToIP' },
        //{ID:'4GLTE-Internet', PRODUCTO: '4GLTE-Internet'},
        //{ID:'4GLTE-ToIP', PRODUCTO: '4GLTE-ToIP'},
        //{ID:'Smart-Play', PRODUCTO: 'Smart-Play'},
        {ID: 'Telefonia_Basica', PRODUCTO: 'Telefonia Basica'},
        {ID: 'DTH-Television', PRODUCTO: 'DTH-Television'}
    ];

    $rootScope.conceptosRegistros = [
        {ID: '', CONCEPTO: '--Seleccione--'}, {ID: 'pedido', CONCEPTO: 'Pedido'}, {ID: 'asesor', CONCEPTO: 'Asesor'}, {ID: 'accion', CONCEPTO: 'Accion'}, {
            ID: 'piloto',
            CONCEPTO: 'Piloto'
        }, {ID: 'ciudad', CONCEPTO: 'Ciudad'}, {ID: 'proceso', CONCEPTO: 'Proceso'}, {ID: 'producto', CONCEPTO: 'Producto'}
    ];

    $rootScope.conceptosRegistrosComercial = [
        {ID: '', CONCEPTO: '--Seleccione--'}, {ID: 'pedido_actual', CONCEPTO: 'Pedido'}, {ID: 'login_asesor', CONCEPTO: 'Asesor'}, {
            ID: 'gestion',
            CONCEPTO: 'Gestión'
        }, {ID: 'clasificacion', CONCEPTO: 'Clasificación'}, {ID: 'ciudad', CONCEPTO: 'Ciudad'}, {ID: 'estado', CONCEPTO: 'Estado'}
    ];

    $rootScope.conceptosBuscar = [
        {ID: '', CONCEPTO: '--Seleccione--'}, {ID: 'nombre', CONCEPTO: 'Nombre'}, {ID: 'login', CONCEPTO: 'Login'}
    ];

    $rootScope.perfiles = [
        {ID: 1, PERFIL: 'Supervisor'},
        {ID: 2, PERFIL: 'Creador de Experiencia'},
        {ID: 3, PERFIL: 'Perfil Regional'},
        {ID: 4, PERFIL: 'Mesa Offline'},
        {ID: 5, PERFIL: 'Creador de Experiencia Plus'},
        {ID: 6, PERFIL: 'Premisas Infraestructuras'},
        {ID: 7, PERFIL: 'Asesor VIP'},
        {ID: 9, PERFIL: 'Brutal Force'},
        {ID: 10, PERFIL: 'Gestión Brutal'},
        {ID: 13, PERFIL: 'Quejas GO'}
    ];

    $rootScope.conceptosBuscartecnico = [
        {ID: '', CONCEPTO: '--Seleccione--'}, {ID: 'nombre', CONCEPTO: 'Nombre'},
        {ID: 'identificacion', CONCEPTO: 'Cedula'}, {ID: 'ciudad', CONCEPTO: 'Ciudad'}, {ID: 'celuar', CONCEPTO: 'Celuar'}
    ];

    $rootScope.contrato = [
        {ID: '', CONCEPTO: 'Seleccione'},
        {ID: 'EMTELCO', CONCEPTO: 'EMTELCO'},
        {ID: 'RYE', CONCEPTO: 'RYE'},
        {ID: 'EIA', CONCEPTO: 'EIA'},
        {ID: 'ETP', CONCEPTO: 'ETP'}
    ];

    $rootScope.tecnologia = [
        {ID: '', CONCEPTO: 'Seleccione'},
        {ID: 'HFC', CONCEPTO: 'HFC'},
        {ID: 'GPON', CONCEPTO: 'GPON'},
        {ID: 'COBRE', CONCEPTO: 'COBRE'},
        {ID: 'DTH', CONCEPTO: 'DTH'}
    ];

    $rootScope.ciudadesContingencias = [
        {ID: '', CONCEPTO: '--Seleccione--'}
        , {ID: 'ARMENIA', CONCEPTO: 'ARMENIA'}
        , {ID: 'BARRANCABERMEJA', CONCEPTO: 'BARRANCABERMEJA'}
        , {ID: 'BARRANQUILLA', CONCEPTO: 'BARRANQUILLA'}
        , {ID: 'BOGOTA', CONCEPTO: 'BOGOTA'}
        , {ID: 'BUCARAMANGA', CONCEPTO: 'BUCARAMANGA'}
        , {ID: 'BUGA', CONCEPTO: 'BUGA'}
        , {ID: 'CALI', CONCEPTO: 'CALI'}
        , {ID: 'CARTAGENA', CONCEPTO: 'CARTAGENA'}
        // , { ID: 'CIUDAD_DTH', CONCEPTO: 'CIUDAD_DTH' }
        , {ID: 'CUCUTA', CONCEPTO: 'CUCUTA'}
        , {ID: 'IBAGUE', CONCEPTO: 'IBAGUE'}
        , {ID: 'MANIZALES', CONCEPTO: 'MANIZALES'}
        , {ID: 'MEDELLIN', CONCEPTO: 'MEDELLIN'}
        , {ID: 'MONTERIA', CONCEPTO: 'MONTERIA'}
        , {ID: 'PALMIRA', CONCEPTO: 'PALMIRA'}
        , {ID: 'PEREIRA', CONCEPTO: 'PEREIRA'}
        , {ID: 'POPAYAN', CONCEPTO: 'POPAYAN'}
        , {ID: 'SANTA MARTA', CONCEPTO: 'SANTA MARTA'}
        , {ID: 'SINCELEJO', CONCEPTO: 'SINCELEJO'}
        , {ID: 'TUNJA', CONCEPTO: 'TUNJA'}
        , {ID: 'VALLEDUPAR', CONCEPTO: 'VALLEDUPAR'}
        , {ID: 'VILLAVICENCIO', CONCEPTO: 'VILLAVICENCIO'}
    ];

    $rootScope.paquetescontingencias = [
        {ID: 'N/A', CONCEPTO: 'N/A'},
        {ID: 'BasicoAXM', CONCEPTO: 'BasicoAXM'},
        {ID: 'BasicoBGA', CONCEPTO: 'BasicoBGA'},
        {ID: 'BasicoBOG', CONCEPTO: 'BasicoBOG'},
        {ID: 'BasicoBQA', CONCEPTO: 'BasicoBQA'},
        {ID: 'BasicoBUG', CONCEPTO: 'BasicoBUG'},
        {ID: 'BasicoCLO', CONCEPTO: 'BasicoCLO'},
        {ID: 'BasicoCTG', CONCEPTO: 'BasicoCTG'},
        {ID: 'BasicoCUC', CONCEPTO: 'BasicoCUC'},
        {ID: 'BasicoEJA', CONCEPTO: 'BasicoEJA'},
        {ID: 'BasicoIBE', CONCEPTO: 'BasicoIBE'},
        {ID: 'BasicoMED', CONCEPTO: 'BasicoMED'},
        {ID: 'BasicoMTR', CONCEPTO: 'BasicoMTR'},
        {ID: 'BasicoMTR', CONCEPTO: 'BasicoMTR'},
        {ID: 'BasicoMZL', CONCEPTO: 'BasicoMZL'},
        {ID: 'BasicoPEI', CONCEPTO: 'BasicoPEI'},
        {ID: 'BasicoPPN', CONCEPTO: 'BasicoPPN'},
        // { ID: 'BasicoMED', CONCEPTO: 'BasicoMED' },
        {ID: 'BasicoSIN', CONCEPTO: 'BasicoSIN'},
        {ID: 'BasicoSMR', CONCEPTO: 'BasicoSMR'},
        {ID: 'BasicoVUP', CONCEPTO: 'BasicoVUP'},
        {ID: 'BasicoVVC', CONCEPTO: 'BasicoVVC'},
        {ID: 'BasicoCLO', CONCEPTO: 'BasicoCLO'},
        {ID: 'BasicoHD', CONCEPTO: 'BasicoHD'},
        {ID: 'BLACK', CONCEPTO: 'BLACK'},
        {ID: 'BRONZE', CONCEPTO: 'BRONZE'},
        {ID: 'CO_CLASICAHD', CONCEPTO: 'CO_CLASICAHD'},
        {ID: 'CO_CLASICAHD_GP', CONCEPTO: 'CO_CLASICAHD_GP'},
        {ID: 'CO_CLAHDPLUSONE', CONCEPTO: 'CO_CLAHDPLUSONE'},
        {ID: 'CO_ESPECIAL_GP', CONCEPTO: 'CO_ESPECIAL_GP'},
        {ID: 'CO_ANDROIONE', CONCEPTO: 'CO_ANDROIONE'},
        {ID: 'WINPREM', CONCEPTO: 'WINPREM'},
        {ID: 'FOXMAS', CONCEPTO: 'FOXMAS'},
        {ID: 'GOLD', CONCEPTO: 'GOLD'},
        {ID: 'HBO-MAX', CONCEPTO: 'HBO-MAX'},
        {ID: 'HBOPACK', CONCEPTO: 'HBOPACK'},
        {ID: 'HD-NalAXM', CONCEPTO: 'HD-NalAXM'},
        {ID: 'HD-NalBGA', CONCEPTO: 'HD-NalBGA'},
        {ID: 'HD-NalBOG', CONCEPTO: 'HD-NalBOG'},
        {ID: 'HD-NalBUG', CONCEPTO: 'HD-NalBUG'},
        {ID: 'HD-NalCLO', CONCEPTO: 'HD-NalCLO'},
        {ID: 'HD-NalCTG', CONCEPTO: 'HD-NalCTG'},
        {ID: 'HD-NalCUC', CONCEPTO: 'HD-NalCUC'},
        {ID: 'HD-NalEJA', CONCEPTO: 'HD-NalEJA'},
        {ID: 'HD-NalMED', CONCEPTO: 'HD-NalMED'},
        {ID: 'HD-NalMZL', CONCEPTO: 'HD-NalMZL'},
        {ID: 'HD-NalPEI', CONCEPTO: 'HD-NalPEI'},
        {ID: 'HOTELES', CONCEPTO: 'HOTELES'},
        {ID: 'HOTPACK', CONCEPTO: 'HOTPACK'},
        {ID: 'LIFESTYLE', CONCEPTO: 'LIFESTYLE'},
        {ID: 'MINIPACK', CONCEPTO: 'MINIPACK'},
        {ID: 'MOVIECITY', CONCEPTO: 'MOVIECITY'},
        {ID: 'MUSIC', CONCEPTO: 'MUSIC'},
        {ID: 'PLAYBOY', CONCEPTO: 'PLAYBOY'},
        {ID: 'Premium', CONCEPTO: 'Premium'},
        {ID: 'PRIVATEGOLD', CONCEPTO: 'PRIVATEGOLD'},
        {ID: 'SILVER', CONCEPTO: 'SILVER'},
        {ID: 'SPORTS', CONCEPTO: 'SPORTS'},
        {ID: 'TVDIG-HDBAS', CONCEPTO: 'TVDIG-HDBAS'},
        {ID: 'TVDIG-HDPREM', CONCEPTO: 'TVDIG-HDPREM'},
        {ID: 'TVDIGITAL', CONCEPTO: 'TVDIGITAL'},
        {ID: 'UFC', CONCEPTO: 'UFC'},
        {ID: 'FOX-TEMP', CONCEPTO: 'FOX-TEMP'},
        {ID: 'ESCENCIAL', CONCEPTO: 'ESCENCIAL'},
        {ID: 'ESCENCIAL-PLUS', CONCEPTO: 'ESCENCIAL-PLUS'},
        {ID: 'IDEAL', CONCEPTO: 'IDEAL'},
        {ID: 'IDEAL-PLUS', CONCEPTO: 'IDEAL-PLUS'},
        {ID: 'ONE', CONCEPTO: 'ONE'},
        {ID: 'ONE PLUS', CONCEPTO: 'ONE PLUS'},
        {ID: 'ONE ELITE', CONCEPTO: 'ONE ELITE'},
        {ID: 'DTHCOL-BASICO', CONCEPTO: 'DTHCOL-BASICO'},
        {ID: 'DTHCOL-AVANZADO', CONCEPTO: 'DTHCOL-AVANZADO'},
        {ID: 'BASICO MIPYME', CONCEPTO: 'BASICO MIPYME'}

    ];


    $rootScope.PendientesBrutalerrorDatos = true;

    $rootScope.pendientesBrutalIncial = function () {
        services.pendientesBrutalForce().then(function (data) {
            $rootScope.pendientesBrutal = data.data[0];
            $rootScope.total = $rootScope.pendientesBrutal.length;
            $rootScope.PendientesBrutalerrorDatos = false;
            return data.data;
        });
    }

    $rootScope.pendientesBrutalIncial();
    $rootScope.ciudades();
    $rootScope.regionesTip();
}]);

app.run(['$location', '$rootScope', '$cookies', 'services', function ($location, $rootScope, $cookies, services) {

    $rootScope.logout = function () {
        var tiempo = $rootScope.fechaProceso();
        services.cerrarsesion($rootScope.galletainfo.LOGIN, $rootScope.galletainfo.PERFIL, tiempo);
        /* if($cookies.get('usuarioInfo')!=undefined){ */
        $cookies.remove('usuarioseguimiento');
        $location.path('/');
        $rootScope.galletainfo = undefined;
        $rootScope.permiso = false;
        //$rootScope.error="Desconectado";
        console.log("Desconectado");
        //console.log($rootScope.galletainfo);
        //$route.reload();

        //}
    };
}]);
//fin de la APP AngularJS
