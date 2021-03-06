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

namespace SnapDecision;

/**
 * Class to hold and execute routes for API URLs
 */
class Router
{
	/**
	 * Routes that are registered
	 *
	 * @private array
	 */
	private $routes;

	/**
	 * Construct the router
	 *
	 * @param \SnapDecision\DI $deps Dependency injection container
	 */
	public function __construct( DI $deps ) {
		$this->deps = $deps;
	}

	/**
	 * Register a route with the router.
	 *
	 * @param string $pattern Pseudo-regex pattern for the route
	 * @param string $controller Fully qualified class name for the controller
	 * @param array $queryParams Mapping of query parameter names
	 * @param array $headerParams Mapping of header names to parameters
	 */
	public function register( $pattern, $controller, array $queryParams = [ ], array $headerParams = [ ] ) {
		// Replace variables with regex capture patterns
		$pattern = preg_replace( '/:(\w+)\+/', '(?P<$1>.*)', $pattern );
		$pattern = preg_replace( '/:(\w+)/', '(?P<$1>[^/]*)', $pattern );
		$this->routes[$pattern] = [ $controller, $queryParams, $headerParams ];
	}

	/**
	 * Execute a route from the global state (i.e., $_SERVER and whatnot)
	 */
	public function executeMain() {
		try {
			list( $method, $url, $query, $headers, $data ) = $this->getRequestInfo();
			// $this->startSession();
			$this->performRequest( $method, $url, $query, $headers, $data );
		} catch ( HttpException $e ) {
			http_response_code( $e->getHttpCode() );
			header( 'Content-Type: application/json' );
			foreach ( $e->getHeaders() as $header => $value ) {
				header( "$header: $value" );
			}
			echo json_encode( $e->getMessage() );
		}
	}

	/**
	 * Get the URL, HTTP method, headers, and body from the global state
	 *
	 * @return array Array of (method, URL, headers, body)
	 */
	private function getRequestInfo() {
		$url = isset( $_SERVER['PATH_INFO'] ) ? $_SERVER['PATH_INFO'] : $_SERVER['PHP_SELF'];
		$method = strtolower( $_SERVER['REQUEST_METHOD'] );

		// Get a standard list of request headers
		$headers = [ ];
		$apache_headers = null;
		if ( function_exists( 'apache_request_headers' ) ) {
			$apache_headers = apache_request_headers();
		}

		if ( $apache_headers !== null && $apache_headers !== false ) {
			foreach ( $apache_headers as $name => $value ) {
				$headers[strtoupper( $name )] = $value;
			}
		} else {
			foreach ( $_SERVER as $name => $value ) {
				if ( strncmp( $name, 'HTTP_', 5 ) === 0 ) {
					$name = str_replace( '_', '-', substr( $name, 5 ) );
					$headers[$name] = $value;
				} elseif ( strncmp( $name, 'CONTENT_', 8 ) === 0 ) {
					$name = strtr( '_', '-', $name );
					$headers[$name] = $value;
				}
			}
		}

		if ( !isset( $headers['ACCEPT'] ) ) {
			$headers['ACCEPT'] = '*/*';
		}

		$rawData = file_get_contents( 'php://input' );
		$query = isset( $_SERVER['QUERY_STRING'] ) ? $_SERVER['QUERY_STRING'] : '';

		return [ $method, $url, $query, $headers, $rawData ];
	}

	/**
	 * Perform a request based on the given method, URL, headers, and body
	 *
	 * @param string $method HTTP method
	 * @param string $url URL
	 * @param string $query Query string
	 * @param array $headers HTTP headers
	 * @param string $rawData Raw data from the request body
	 *
	 * @return \SnapDecision\Response A response object
	 * @throws HttpException for various errors in input data
	 */
	public function performRequest( $method, $url, $query, array $headers, $rawData ) {
		// Check the MD5 hash if available
		if ( isset( $headers['CONTENT-MD5'] ) ) {
			$hash = base64_decode( $headers['CONTENT-MD5'] );
			if ( md5( $rawData ) !== $hash ) {
				throw new HttpException( 400 );
			}
		}

		$request = new HttpRequest();
		$request->addHeaders( $headers );
		$request->parseBody( $rawData );

		// Execute the request
		$response = $this->runController( $method, $url, $query, $request );
		if ( !( $response instanceof Response ) ) {
			$response = new Response( $response );
		}

		// Check if we need to send the request body based on the conditional
		// HTTP headers
		$allowWeak = $method === 'get' && !isset( $headers['RANGE'] );
		$notModified = null;
		if ( isset( $headers['IF-NONE-MATCH'] ) ) {
			foreach ( explode( ',', $headers['IF-NONE-MATCH'] ) as $etag ) {
				$etag = trim( $etag );
				if ( $response->matchEtag( $etag, $allowWeak ) ) {
					$notModified = true;
				} else {
					$notModified = false;
					break;
				}
			}
		}
		if ( isset( $headers['IF-MODIFIED-SINCE'] ) ) {
			$headerTime = new \DateTime( $headers['IF-MODIFIED-SINCE'] );
			if ( $response->matchLastModified( $headerTime, $allowWeak ) ) {
				$notModified = $notModified === false ? false : true;
			} else {
				$notModified = false;
			}
		}
		if ( isset( $headers['IF-UNMODIFIED-SINCE'] ) ) {
			$headerTime = new \DateTime( $headers['IF-MODIFIED-SINCE'] );
			if ( !$response->matchLastModified( $headerTime, $allowWeak ) ) {
				$notModified = $notModified === false ? false : true;
			} else {
				$notModified = false;
			}
		}

		if ( $notModified ) {
			if ( $method === 'get' || $method === 'head' ) {
				throw new HttpException( 304, '', $response );
			} else {
				throw new HttpException( 412, '', $response );
			}
		}

		// Encode the response appropriately for the client
		$responseData = '';
		foreach ( array_map( 'trim', explode( ',', $headers['ACCEPT'] ) ) as $accept ) {
			switch ( $accept ) {
				case '*/*':
				case 'text/*':
				case 'application/*':
				case 'text/html':
				case 'application/json':
					$response->addHeader( 'Content-Type', 'application/json' );
					$responseData = json_encode( $response->getContents() );
					break 2;

				case 'application/x-www-form-urlencoded':
					$response->addHeader( 'Content-Type', 'application/x-www-form-urlencoded' );
					$responseData = http_build_query( $response->getContents() );
					break 2;

				case '':
				case null:
				case 'undefined':
					$response->addHeader( 'Content-Type', 'text/plain' );
					$responseData = $response->getContents();
					break;

				default:
					throw new HttpException( 406 );
			}
		}

		// If output compression isn't enabled in the PHP config, try doing it
		// manually if the client wants it
		if ( !in_array( strtolower( \ini_get( 'zlib.output_compression' ) ),
			[ 'on', 'true', 'yes' ] )
		) {
			$encodings = explode( ',', $headers['ACCEPT'] );
			foreach ( $encodings as $encoding ) {
				switch ( trim( $encoding ) ) {
					case 'deflate':
					case 'gzip':
						if ( \extension_loaded( 'zlib' ) ) {
							\ob_start( 'ob_gzhandler' );
							break 2;
						}

					case 'bzip2':
						if ( \function_exists( 'bzcompress' ) ) {
							$responseData = \bzcompress( $responseData );
							$response->addHeader( 'Content-Encoding', 'bzip2' );
							break 2;
						}
				}
			}
		}

		$response->addHeader( 'Content-Length', strlen( $responseData ) );
		foreach ( $response->getHeaders() as $key => $val ) {
			header( "$key: $val" );
		}
		if ( $method !== 'head' && $method !== 'options' ) {
			echo $responseData;
		}

		return true;
	}

	/**
	 * Run a controller given request information using the internal routes
	 *
	 * @param string $method HTTP method
	 * @param string $url URL
	 * @param string $query Query string
	 * @param HttpRequest $request Parsed data from the request body
	 *
	 * @return \SnapDecision\Response A response object
	 * @throws HttpException for various errors
	 */
	private function runController( $method, $url, $query, HttpRequest $request ) {
		$controller = null;
		$matches = [ ];
		$queryParamsDef = [ ];
		$headerParamsDef = [ ];

		// Find a matching route
		foreach ( $this->routes as $pattern => $info ) {
			list( $class, $queryParamsDef, $headerParamsDef ) = $info;
			if ( preg_match( "!^{$pattern}$!", $url, $matches ) ) {
				array_shift( $matches );
				$controller = new $class( $this->deps );
				break;
			}
		}

		if ( $controller === null ) {
			throw new HttpException( 404, 'Controller not found' );
		}

		// Check if the method is valid for this controller
		if ( !method_exists( $controller, $method ) ) {
			$allowedMethods = array_map( 'strtoupper', get_class_methods( $controller ) );
			$allowedMethods = array_intersect( $allowedMethods,
				[ 'GET', 'HEAD', 'POST', 'PUT', 'DELETE', 'OPTIONS' ] );

			throw new HttpException( 405, '', [ 'Allow' => implode( ', ', $allowedMethods ) ] );
		}

		// Parse the query and generate a list of final parameters
		$rawParams = [ ];
		parse_str( (string)$query, $rawParams );
		foreach ( $rawParams as $key => $val ) {
			if ( isset( $queryParamsDef[$key] ) ) {
				$matches[$queryParamsDef[$key]] = $val;
			}
		}

		foreach ( $headerParamsDef as $headerName => $key ) {
			$matches[$key] = $request->getHeader( $headerName );
		}

		// Run the controller
		return $controller->$method( $matches, $request );
	}
}
