<?php
namespace SnapDecision\Backend;

class Amazon_API
{
	// public key
	var $publicKey = "AKIAIQZXB36TAYJF3KWA";
	// private key
	var $privateKey = "6Z43hnO5UjIC6Jjto4nCDRzDSgTA8V0zGLxTpkAM";
	// affiliate tag
	var $associateTag = 'onmygoing-23';
	//Default Region
	var $region = 'com';

	var $MarketplaceId = 'ATVPDKIKX0DER';
	var $SellerId = 'A135KKEKJAIBJ56';
	/*
	 *          String accessKeyId = "AKIAJWFNWBM2EQHFUWIQ";
				String secretAccessKey = "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";
				String merchantId = "A19CO4YJKYS0K";
				String marketplaceId = "ATVPDKIKX0DER";
	 */

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

	/*
	 *  var $privateKey = 'CSp65n5xD4FBjmUAxvfke8HfNPmS3otnNZn9fxLY'
		var $publicKey = '1KSNPZST1QKCEWJ2KSG2';
		var $associateTag= 'worldwidebe04-20'
	 */
	/**
	 *Get a signed URL
	 *
	 * @param string $region used to define country
	 * @param array $param used to build url
	 *
	 * @return array $signature returns the signed string and its components
	 */
	private function generateSignature( $param ) {
		// url basics
		$signature['method'] = 'GET';
		$signature['host'] = 'ecs.amazonaws.' . $param['region'];
		$signature['uri'] = '/onca/xml';

		// necessary parameters
		//$param['ResponceGroup'] = 'Large';

		$param['AssociateTag'] = $this->associateTag;
		$param['AWSAccessKeyId'] = $this->publicKey;
		$param['Timestamp'] = gmdate( "Y-m-d\TH:i:s\Z" );
		$param['Version'] = '2011-08-01';
		ksort( $param );
		foreach ( $param as $key => $value ) {
			$key = str_replace( "%7E", "~", rawurlencode( $key ) );
			$value = str_replace( "%7E", "~", rawurlencode( $value ) );
			$queryParamsUrl[] = $key . "=" . $value;
		}
		// glue all the  "params=value"'s with an ampersand
		$signature['queryUrl'] = implode( "&", $queryParamsUrl );

		// we'll use this string to make the signature
		$StringToSign = $signature['method'] . "\n" . $signature['host'] . "\n" . $signature['uri'] . "\n" . $signature['queryUrl'];
		// make signature
		$signature['string'] = str_replace( "%7E",
			"~",
			rawurlencode( base64_encode( hash_hmac( "sha256",
						$StringToSign,
						$this->privateKey,
						true ) ) ) );

		return $signature;
	}

	/**
	 * Get signed url response
	 *
	 * @param string $region
	 * @param array $params
	 *
	 * @return string $signedUrl a query url with signature
	 */
	private function getSignedUrl( $params ) {
		$params['region'] = $this->region;
		$signature = $this->generateSignature( $params );

		$uri = $signedUrl = "https://" . $signature['host'] . $signature['uri'] . '?' . $signature['queryUrl'] . '&Signature=' . $signature['string'];

		//echo $uri . "\n";
		return $uri;
	}

	public function getISBNXML( $params ) {
		//Paramaters needed
		$params['IdType'] = 'ISBN';
		$params['ProductGroup'] = 'Book';
		$params['Condition'] = 'New';
		$params['SearchIndex'] = 'All';
		$params['Service'] = 'AWSECommerceService';
		$params['Operation'] = 'ItemLookup';
		$params['ResponseGroup'] = 'ItemAttributes,OfferSummary';

		if ( !isset( $params['ItemId'] ) ) {
			//Error
			return 'Invalid paramaters';

		}
		$xml = file_get_contents( $this->getSignedUrl( $params ),
			0,
			stream_context_create( array( 'https' => array( 'timeout' => 1.5 ) ) ) );
		$xml = simplexml_load_string( $xml );

		//print_r($xml->Items);
		if ( isset( $xml ) && isset( $xml->Items ) && isset( $xml->Items->Request ) && $xml->Items->Request->IsValid == 'True' ) {
			return $xml->Items->Item;
		} else {
			return "The request timed out";
		}
	}

	public function getISBNPrice( $params ) {
		$params['Action'] = 'GetLowestOfferListingsForASIN';
		$params['SearchIndex'] = 'All';
		$params['SellerId'] = $this->SellerId;
		$params['MarketplaceId'] = $this->MarketplaceId;

		$xml = file_get_contents( $this->getSignedUrl( $params ),
			0,
			stream_context_create( array( 'https' => array( 'timeout' => 1.5 ) ) ) );
		$xml = simplexml_load_string( $xml );

		//print_r($xml->Items);
		if ( isset( $xml ) ) {
			return $xml;
		} else {
			return "The request timed out";
		}
	}
}

?>
