	angular.module('demo', []);
	angular.module('demo', [
    'controllers', 
    'elasticjs.service'
]);
	angular.module('demo', [
    'controllers', 
    'elasticjs.service'
]);
	angular.module('controllers', [])
    .controller('SearchCtrl', function($scope, ejsResource) {

        var ejs = ejsResource('http://localhost:9200');

    });
	
angular.module('controllers', [])
    .controller('SearchCtrl', function($scope, ejsResource) {

        var ejs = ejsResource('http://localhost:9200');

        var oQuery = ejs.QueryStringQuery().defaultField('Title');

        var client = ejs.Request()
            .indices('stackoverflow')
            .types('question');

        $scope.search = function() {
            $scope.results = client
                .query(oQuery.query($scope.queryTerm || '*'))
                .doSearch();
        };

    });