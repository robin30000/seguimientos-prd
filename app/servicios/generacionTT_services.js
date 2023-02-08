var app = angular.module('seguimientopedidos.generaciontt', []);

app.factory('Generaciontt', ['$http', '$q', function ($http, $q) {
	
	var self = {

		'cargando'		: 	false,
		'err'			: 	false,
		'conteo'		: 	0,
		'generaciontt'	: 	[],
		'pagActual'		: 	1,
		'pagSiguiente'	: 	1,
		'pagAnterior'	: 	1,
		'totalPaginas'	: 	1,
		'paginas'		: 	[],

		cargarPaginas: function(){

			var d = $q.defer();

				$http({
					method: 'GET',
					url: 'services/api.php'
				}).then(function(data){

					self.err 			= data.err;
					self.conteo 		= data.conteo;
					self.generaciontt 	= data.generaciontt;
					self.pagActual 		= data.pagActual;
					self.pagSiguiente 	= data.pagSiguiente;
					self.pagAnterior 	= data.pagAnterior;
					self.totalPaginas 	= data.totalPaginas;
					self.paginas 		= data.paginas;

					//console.log("dataGeneracionTTServices: ",data);
					return d.resolve();

				});

			return d.promise;
		}
	};	

	return self;

}]);