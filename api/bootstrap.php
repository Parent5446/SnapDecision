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

require 'lib/google-api-php-client/src/Google_Client.php';
require 'lib/google-api-php-client/src/contrib/Google_Oauth2Service.php';
require 'lib/google-api-php-client/src/contrib/Google_MirrorService.php';

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

$db = new \PDO( $config['db']['connect_string'], $config['db']['user'], $config['db']['password'] );
$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

$google = new \Google_Client( [
	'ioClass' => 'Google_HttpStreamIO',
	'cacheClass' => 'Google_MemcacheCache',
	// The memcached host and port do not matter since AppEngine
	// overrides them from app.yaml, but we need these values for the client
	// library to work
	'ioMemCacheCache_host' => 'invalid.domain',
	'ioMemCacheCache_port' => '37337',
] );
$google->setUseObjects( true );
$google->setApplicationName( 'SnapDecision' );
$google->setClientId( $config['snapdecision']['clientid'] );
$google->setClientSecret( $config['snapdecision']['clientsecret'] );
$google->setRedirectUri( $config['snapdecision']['redirecturi'] );
$google->setScopes( [
	'https://www.googleapis.com/auth/glass.timeline',
	'https://www.googleapis.com/auth/glass.location',
	'https://www.googleapis.com/auth/userinfo.profile',
	'https://www.googleapis.com/auth/userinfo.email',
] );

// Set up autoloader
require 'SnapDecision/Autoloader.php';
require 'SnapDecision/Util.php';
$autoloader = new \SnapDecision\Autoloader( $config['snapdecision']['root'] );
spl_autoload_register( [ $autoloader, 'autoload' ] );

$di = new SnapDecision\DI( $db, $google, $config );