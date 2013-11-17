<?php
namespace SnapDecision\Backend;

class Google_API
{
	private $API_KEY = 'AIzaSyB0vHwjfMMy-OUuOZBxnzpYeNUj7VgSVoE';

	/**
	 * Dependency injection container
	 *
	 * @var \SnapDecision\DI
	 */
	private $deps;

	/**
	 * Construct the controller with the DI container
	 *
	 * @param \SnapDecision\DI $deps
	 */
	public function __construct( \SnapDecision\DI $deps ) {
		$this->deps = $deps;
	}

	public function getISBN( $params ) {

		$books = new \Google_BooksService( $this->deps->google );
		switch ( $params['type'] ) {
			case 'title':
				$g_type = 'intitle: ';
				break;
			case 'author':
				$g_type = 'inauthor: ';
				break;
			case 'publisher':
				$g_type = 'inpublisher: ';
				break;
			case 'urn:isbn';
				$g_type = 'isbn: ';
				break;
			case 'urn:lccn':
				$g_type = 'lccn: ';
				break;
			case 'urn:oclc':
				$g_type = 'oclc: ';
				break;
			default:
				return 'Error with Paramaters';
				break;
		}
		$vars = [ 'maxResults' => 1, 'printType' => 'books', 'projection' => 'full' ];

		$volumes = $books->volumes->listVolumes( $g_type . $params['query'], $vars );
	}

	public function urnQuery( $params ) {
		$uri = 'https://www.googleapis.com/books/v1/volumes?q=' . $params . '&maxResults=1&printType=books&projection=full&key=' . $this->API_KEY;

		return file_get_contents( $uri, 0, stream_context_create( [ 'https' => [ 'timeout' => 1.5 ] ] ) );
	}
}

?>