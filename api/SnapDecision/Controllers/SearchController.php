<?php
/**
 * Created by PhpStorm.
 * User: parent5446
 * Date: 11/16/13
 * Time: 3:29 PM
 */

namespace SnapDecision\Controllers;

use SnapDecision\Backend;

/**
 * Class SearchController
 *
 * @package SnapDecision\Controllers
 */
class SearchController
{
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

	/**
	 * Fetch information from a URN
	 *
	 * @param array $params URL parameters
	 * @param mixed $data Request data
	 */
	public function get( array $params, $data ) {
		if(!isset($params))
		{
			throw new HttpException( 400 ); // Return 400
		}
		$query = Array();
		$responce = Array();
		$g_api = new Backend\Google_API();
		$a_api = new Backend\Amazon_API();
		$r_api = new Backend\Reviews_API();

		if(isset($params['title']))
		{
			//Google API
			$query['type'] = 'title';
			$query['query'] = $params['title'];

			$json = $g_api->getISBN($query);
			$json = json_decode($json);

		}
		else if(isset($params['urn']))
		{
			//If no space put in space
			$json = $g_api->urnQuery($params['title']);
			$json = json_decode($json);

		}
		else
		{
			throw new HttpException( 400 );
		}

		if($json['totalItems'] > 0)
		{
			return ;//0
		}


		$responce['title'] = $json['volumeInfo']['title']; //Possoble $json["volumeInfo"]['subtitle']
		$responce['isbn'] = [$json['volumeInfo']['industryIdentifiers']['ISBN_10'], $json['volumeInfo']['industryIdentifiers']['ISBN_13']];
		$responce['image'] = $json['volumeInfo']['imageLinks']['thumbnail'];
		$responce['rating'] = $json['volumeInfo']['averageRating'];
		$responce['reviews'] = [];

		//Amazon API
		$paramaters = [];
		$paramaters['ItemId'] = $responce['isbn'][0];

		$xml = $a_api->getISBNXML($paramaters);
		if(isset($xml->OfferSummary))
			$responce['price'] = $xml->OfferSummary->LowestNewPrice->FormattedPrice;

		//Review API
		$xml = $r_api->getReviews($responce['isbn'][0]);
		if(isset($xml))
		{
			foreach ($xml as $ratings)
			{
				$temp = [];

				$temp['review'] = $ratings->snippet;
				$temp['reviewer'] = $ratings->source;
				$temp['url'] = $ratings{'review-link'};
				$temp['perception'] = $ratings{'pos-or-neg'};

				$responce['reviews'][] = $temp;
			}
		}

		return $responce;


	}
}