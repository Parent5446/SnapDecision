angular.module('controllers', [])
    .controller('SearchCtrl', function($scope) {});
	angular.module('controllers', [])
    .controller('SearchCtrl', function($scope, ejsResource) {

        var ejs = ejsResource('http://localhost:9200');

    });
	
