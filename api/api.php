<?php

require "bootstrap.php";

// Make the router
$router = new SnapDecision\Router( $di );
$router->register( "/search?:urn", '\SnapDecision\Controllers\SearchController' );
$router->register( "/:item/:subresource", '\SnapDecision\Controllers\ItemController' );
$router->register( "/:item/:subresource?:id", '\SnapDecision\Controllers\PurchaseController' );

// Run the request
$router->executeMain();