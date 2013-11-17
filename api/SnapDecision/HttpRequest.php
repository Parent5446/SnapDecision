<?php
/**
 * Created by PhpStorm.
 * User: parent5446
 * Date: 11/16/13
 * Time: 11:54 PM
 */

namespace SnapDecision;


/**
 * Parses and represents an HTTP request and headers
 *
 * @package SnapDecision
 */
class HttpRequest {
	/**
	 * Mapping of header name to values
	 * @var array
	 */
	private $headers = [];

	/**
	 * Either a list of sub-requests or the string body
	 * @var string|HttpRequest[]
	 */
	private $body = null;

	/**
	 * Add an array of known headers to the request object
	 *
	 * @param array $headers Mapping of header key to value
	 */
	public function addHeaders( array $headers ) {
		foreach ( $headers as $key => $val ) {
			$this->headers[strtoupper( $key )] = (array)$val;
		}
	}

	/**
	 * Get a header from the request object
	 *
	 * @param string $name Header name to get
	 *
	 * @return mixed Header value or null if it doesn't exist
	 */
	public function getHeader( $name ) {
		$name = strtoupper( $name );
		if ( isset( $this->headers[$name] ) ) {
			return $this->headers[$name][0];
		} else {
			return null;
		}
	}

	/**
	 * Get the parsed body of the request
	 *
	 * @return HttpRequest[]|string
	 */
	public function getBody() {
		return $this->body;
	}

	/**
	 * Parse the body of the request, applying any necessary encoding
	 *
	 * @param string $body
	 * @throws HttpException if the data is not well-formed
	 */
	public function parseBody( $body ) {
		if ( isset( $this->headers['CONTENT-TRANSFER-ENCODING'] ) &&
			$this->headers['CONTENT-TRANSFER-ENCODING'] === 'base64'
		) {
			$body = base64_decode( $body );
		}

		if ( isset( $this->headers['CONTENT-TYPE'][0] ) ) {
			$contentType = $this->headers['CONTENT-TYPE'][0];
		} else {
			$contentType = 'text/plain';
		}

		$data = null;
		if ( $contentType === 'application/json' ) {
			$data = json_decode( $body, true );
		} elseif ( $contentType === 'application/x-www-form-urlencoded' ) {
			parse_str( $body, $data );
		} elseif ( $contentType === 'text/plain' || $contentType == 'text/html' ) {
			$data = $body;
		} elseif ( $contentType === 'multipart/alternative' ) {
			if ( !isset( $this->headers['CONTENT-TYPE']['boundary'] ) ) {
				throw new HttpException( 400 );
			}

			$boundary = $this->headers['CONTENT-TYPE']['boundary'];
			$parts = explode( "--$boundary", $body );
			$data = [];
			foreach ( $parts as $part ) {
				$part = trim( $part );
				if ( strpos( $part, "\r\n" ) === false ) {
					continue;
				}
				$subRequest = new HttpRequest();
				$subRequest->parseHeadersAndBody( $part );
				$data[$subRequest->getHeader( 'Content-Type' )] = $subRequest;
			}
		} elseif ( $contentType === 'message/rfc822' ) {
			$subRequest = new HttpRequest();
			$subRequest->parseHeadersAndBody( $body );
			$data = $subRequest;
		} else {
			throw new HttpException( 415, "Invalid content type: $contentType" );
		}

		$this->body = $data;
	}

	/**
	 * Parse a string containing all the headers for a request
	 *
	 * @param string $data
	 * @throws HttpException if the data is not well-formed
	 */
	public function parseHeaders( $data ) {
		$headers = [];
		$rawHeaders = explode( "\r\n", $data );
		$rawHeadersLen = count( $rawHeaders );
		for ( $i = 0; $i < $rawHeadersLen; ++$i ) {
			$split = explode( ':', $rawHeaders[$i], 2 );
			if ( count( $split ) !== 2 ) {
				throw new HttpException( 400 );
			}

			list( $key, $rawValue ) = $split;
			while ( $i + 1 < $rawHeadersLen &&
				( $rawHeaders[$i + 1][0] == ' ' || $rawHeaders[$i + 1][0] == "\t"
			) ) {
				$rawValue .= $rawHeaders[$i++];
			}

			$value = [];
			foreach ( explode( ';', $rawValue ) as $param ) {
				$split = explode( '=', $param, 2 );
				if ( count( $split ) == 2 ) {
					$value[trim( $split[0] )] = trim( $split[1] );
				} else {
					$value[] = trim( $split[0] );
				}
			}
			$headers[trim( $key )] = $value;
		}

		$this->addHeaders( $headers );
	}

	/**
	 * Parse a string containing both headers and body
	 *
	 * @param string $data
	 * @throws HttpException if the data is not well-formed
	 */
	public function parseHeadersAndBody( $data ) {
		$split = explode( "\r\n\r\n", $data, 2 );
		if ( count( $split ) !== 2 ) {
			throw new HttpException( 400 );
		}

		list( $rawHeaders, $rawBody ) = $split;
		$this->parseHeaders( $rawHeaders );
		$this->parseBody( $rawBody );
	}
} 