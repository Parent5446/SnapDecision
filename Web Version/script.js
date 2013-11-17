function LoadXMLDoc($scope)
{
$scope.data;
    var xmlhttp;
	var ajax_json;
  /*  if (window.XMLHttpRequest)
    {// code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp=new XMLHttpRequest();
    }
    else
    {// code for IE6, IE5
        xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.onreadystatechange=function()
    {
        if (xmlhttp.readyState==4 && xmlhttp.status==200)
        {
            //Data Receved from server
			//var ajax_json=JSON.parse(xmlhttp.responseText);
			*/
			$scope.getData=function(){
			ajax_json=JSON.parse('{"title":"The Great Gatsby","isbn":[{"type":"ISBN_10","identifier":"1907832564"},{"type":"ISBN_13","identifier":"9781907832567"}],"image":"http:\/\/bks2.books.google.com\/books?id=JgxOs2GX86AC&printsec=frontcover&img=1&zoom=1&edge=curl&source=gbs_api","rating":3.5,"reviews":[{"review":{},"reviewer":{},"url":null,"perception":null}],"price":"$20.90"}');
			$scope = [
				{title: ajax_json["title"], isbn: ["isbn"]};
			]
			return ;
			}/*
			
	   }
        else
        {
            //Error getting Data
			var ajax_json=JSON.parse('{"title":"The Great Gatsby","isbn":[{"type":"ISBN_10","identifier":"1907832564"},{"type":"ISBN_13","identifier":"9781907832567"}],"image":"http:\/\/bks2.books.google.com\/books?id=JgxOs2GX86AC&printsec=frontcover&img=1&zoom=1&edge=curl&source=gbs_api","rating":3.5,"reviews":[{"review":{},"reviewer":{},"url":null,"perception":null}],"price":"$20.90"}');
			alert(ajax_json);
		}
    }
    if(is_value_isbn)
    {
        xmlhttp.open("GET","api.php?urn=isbn:" + value, true);
    }
    else
    {
        xmlhttp.open("GET","api.php?title=" + value, true);
    }
    xmlhttp.send();
}



*/

    function Ctrl($scope) {
    $scope.userType = 'Book Search';
    }
	$(".main").onepage_scroll({
   sectionContainer: "section", // sectionContainer accepts any kind of selector in case you don't want to use section
   easing: "ease", // Easing options accepts the CSS3 easing animation such "ease", "linear", "ease-in", "ease-out", "ease-in-out", or even cubic bezier value such as "cubic-bezier(0.175, 0.885, 0.420, 1.310)"
   animationTime: 1000, // AnimationTime let you define how long each section takes to animate
   pagination: true, // You can either show or hide the pagination. Toggle true for show, false for hide.
   updateURL: false, // Toggle this true if you want the URL to be updated automatically when the user scroll to each page.
   beforeMove: function(index) {}, // This option accepts a callback function. The function will be called before the page moves.
   afterMove: function(index) {}, // This option accepts a callback function. The function will be called after the page moves.
   loop: false, // You can have the page loop back to the top/bottom when the user navigates at up/down on the first/last page.
   responsiveFallback: false // You can fallback to normal page scroll by defining the width of the browser in which you want the responsive fallback to be triggered. For example, set this to 600 and whenever the browser's width is less than 600, the fallback will kick in.
});

// Define a new module. This time we declare a dependency on
// the ngResource module, so we can work with the Instagram API

var app = angular.module("switchableGrid", ['ngResource']);

// Create and register the new "instagram" service
app.factory('instagram', function($resource){

	return {
		fetchPopular: function(callback){

			// The ngResource module gives us the $resource service. It makes working with
			// AJAX easy. Here I am using the client_id of a test app. Replace it with yours.

			var api = $resource('https://api.instagram.com/v1/media/popular?client_id=:client_id&callback=JSON_CALLBACK',{
				client_id: '642176ece1e7445e99244cec26f4de1f'
			},{
				// This creates an action which we've chosen to name "fetch". It issues
				// an JSONP request to the URL of the resource. JSONP requires that the
				// callback=JSON_CALLBACK part is added to the URL.

				fetch:{method:'JSONP'}
			});

			api.fetch(function(response){

				// Call the supplied callback function
				callback(response.data);

			});
		}
	}

});

// The controller. Notice that I've included our instagram service which we
// defined below. It will be available inside the function automatically.

function SwitchableGridController($scope, instagram){

	// Default layout of the app. Clicking the buttons in the toolbar
	// changes this value.

	$scope.layout = 'grid';

	$scope.pics = [];

	// Use the instagram service and fetch a list of the popular pics
	instagram.fetchPopular(function(data){

		// Assigning the pics array will cause the view
		// to be automatically redrawn by Angular.
		$scope.pics = data;
	});

}