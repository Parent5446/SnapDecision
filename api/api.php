<?php

require "bootstrap.php";

// Make a DI container and setup the router
$di = new SnapDecision\DI( $db, $config );
$router = new SnapDecision\Router( $di );
$router->register( "/search?:urn", '\SnapDecision\Controllers\SearchController' );
$router->register( "/:item/:subresource", '\SnapDecision\Controllers\ItemController' );
$router->register( "/:item/:subresource?:id", '\SnapDecision\Controllers\PurchaseController' );

// Run the request
$router->executeMain();
echo 'test';