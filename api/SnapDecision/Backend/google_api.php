<?php
namespace SnapDecision\Backend;

class Google_API
{
	var $API_KEY = 'AIzaSyAlimP1duVdbwWoJRnb7IEs1mMuKiED52U';
//`https://www.googleapis.com/books/v1/volumes?q=${isbn_number}+isbn`
	private function makeURL($params)
	{
		//https://www.googleapis.com/books/v1/volumes?q=isbn%3A1593271441&maxResults=1&printType=books&projection=full&key={YOUR_API_KEY}
		$uri = 'https://www.googleapis.com/books/v1/volumes?q=';
		switch($params['type'])
		{
			case 'intitle':
			case 'inauthor':
			case 'inpublisher':
			case 'isbn';
			case 'lccn':
			case 'oclc':
				$uri .= $params['type']  . ':' . $params['query']. '&maxResults=1&printType=books&projection=full&key=' . $this->API_KEY;
				break;
			default:
				return 'Error with Paramaters';
				break;
		}
		return $uri;
	}

	public function getISBN($params)
	{
		$uri = $this->makeURL($params);
		echo $uri;
		return file_get_contents($uri, 0, stream_context_create(array('https' => array('timeout' => 1.5))));
	}
}

$test = new Google_API();
$params = Array();
$params['type'] = 'isbn';
$params['query'] = '1593271441';

print_r($test->getISBN($params));

?>