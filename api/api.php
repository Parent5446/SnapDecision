<?php

/**
 * Copyright (C) 2013 Tyler Romeo, Krzysztof Jordan, Nicholas Bevaqua
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 */

error_reporting( E_ALL | E_STRICT );

define( 'SNAPDECISION', 1.0 );

if ( file_exists( 'vendor/autoload.php' ) ) {
	/** @noinspection PhpIncludeInspection */
	include 'vendor/autoload.php';
}

// Parse configuration and load database
$config = parse_ini_file( 'config.ini', true );
$config['snapdecision'] += [ 'hostname' => 'localhost', 'root' => __DIR__, ];
if ( !isset( $config['snapdecision']['fileroot'] ) ) {
	$config['snapdecision']['fileroot'] = "{$config['snapdecision']['root']}/filesystem";
}

$db = new PDO( "{$config['db']['type']}:dbname={$config['db']['name']};host={$config['db']['host']}", $config['db']['user'], $config['db']['password'] );
$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

// Set up autoloader
require 'SnapDecision/Autoloader.php';
require 'SnapDecision/Util.php';
$autoloader = new SnapDecision\Autoloader( $config['snapdecision']['root'] );
spl_autoload_register( [ $autoloader, 'autoload' ] );

// Make a DI container and setup the router
$di = new SnapDecision\DI( $db, $config );
$router = new SnapDecision\Router( $di );

// Run the request
$router->executeMain();
