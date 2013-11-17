<?php
/**
 * Created by PhpStorm.
 * User: generalzero
 * Date: 11/16/13
 * Time: 5:51 PM
 */
namespace SnapDecision\Backend;

class Reviews_API
{
	var $APIkey = '5029bdd7f86ed9df3c1bd31c43906b1ae9dcfa89';

	public function getReviews($isbn)
	{
		$uri = 'http://idreambooks.com/api/books/reviews.xml?q=' . $isbn . '&key=' . $this->APIkey;
		$contents = file_get_contents($uri, 0, stream_context_create(array('http' => array('timeout' => 1.5))));
		$xml = simplexml_load_string($contents);
		print_r($xml->book->{'critic-reviews'});
		if(isset($xml) && isset($xml->book))
		{
			return $xml->book->{'critic-reviews'};
		}
	}
}
?>