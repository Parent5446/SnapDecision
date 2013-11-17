<?php
namespace SnapDecision\Backend;

class Google_API
{
	var $API_KEY = 'AIzaSyB0vHwjfMMy-OUuOZBxnzpYeNUj7VgSVoE';
	private function makeBookURL($params)
	{
		//https://www.googleapis.com/books/v1/volumes?q=isbn%3A1593271441&maxResults=1&printType=books&projection=full&key={YOUR_API_KEY}
		$uri = 'https://www.googleapis.com/books/v1/volumes?q=';
		switch($params['type'])
		{
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
		$uri .= urlencode($g_type) . urlencode($params['query']) . '&maxResults=1&printType=books&projection=full&key=' . $this->API_KEY;
		return $uri;
	}

	public function getISBN($params)
	{
		$uri = $this->makeBookURL($params);
		//echo $uri;
		return file_get_contents($uri, 0, stream_context_create(array('https' => array('timeout' => 1.5))));
	}

	public function urnQuery($params)
	{
		$uri = 'https://www.googleapis.com/books/v1/volumes?q=' . $params . '&maxResults=1&printType=books&projection=full&key=' . $this->API_KEY;
		return file_get_contents($uri, 0, stream_context_create(array('https' => array('timeout' => 1.5))));
	}
}
?>