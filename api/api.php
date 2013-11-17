<?php

require "bootstrap.php";

// Make the router
$router = new SnapDecision\Router( $di );
$router->register( "/oauth2", '\SnapDecision\Controllers\Oauth2Controller', [ 'code' => 'code' ] );
$router->register( "/search", '\SnapDecision\Controllers\SearchController', [ 'title' => 'title', 'urn' => 'urn' ] );
$router->register( "/:item/:subresource", '\SnapDecision\Controllers\ItemController' );
$router->register( "/:item/:subresource?:id", '\SnapDecision\Controllers\PurchaseController' );

// Run the request
$router->executeMain();