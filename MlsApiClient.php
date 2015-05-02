<?php

/**
 * Search
 *
 * @author Victor Lantigua <vmlantigua@gmail.com>
 */
class Search {
	public $OwnershipUsage 		= '';
	public $ResordCode 			= '';
	public $Type 				= '';
	public $Resort 				= '';
	public $City 				= '';
	public $PointsQualifyer 	= '';
	public $PointsValue 		= '';
	public $PriceRange 			= '';
	public $Season 				= '';
	public $Region 				= '';
	public $State 				= '';
	public $Week 				= '';
	public $FirstUse 			= '';
	public $Bed 				= '';
	public $Bath 				= '';
	public $PriceLow 			= '';
	public $PriceHigh 			= '';

	/**
	 * Returns a new Search object and initializes properties based on search criteria.
	 *
	 * @param 	array 	$criteria
	 * @return 	Search
	 */
	public static function factory($criteria = array())
	{
		$search = new Search();
		foreach($criteria as $name => $value) {
			$search->$name = $value;
		}
		return $search;
	}
}

/**
 * MlsApiClient
 *
 * @author Victor Lantigua <vmlantigua@gmail.com>
 */
class MlsApiClient {
	const URL 		= 'http://silverlightapi.timesharebrokersmls.com/tsbmlsws.asmx?wsdl';
	const KEY 		= 'f53bbab34cb7461893799d04b0f2f465';
	const MEMBER_ID = '1000459';
	const MAIL_TO 	= 'michellehernandez2008@gmail.com';

	private $soapClient;

	/**
	 * Converts a boolean string to 'Yes' or 'No'.
	 *
	 * @param 	string  $value
	 * @return 	string
	 */
	public static function bool2Text($value)
	{
		return ($value == 'True') ? 'Yes' : 'No';
	}

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		try {
			$this->soapClient = new SoapClient(self::URL);
		}
		catch(Exception $e) {
			echo 'Error' . $e->getMessage();
		}
	}

	/**
	 * Gets properties grouped by resort name.
	 *
	 * @param  array  $criteria
	 * @return array  [results[], total]
	 */
	public function getProperties($criteria = array())
	{
		try {
			$result = $this->soapClient->GetProperties(array(
				'Search' 	=> Search::factory($criteria),
				'Key' 		=> self::KEY,
				'MemberID' 	=> self::MEMBER_ID
			));

			if ( ! empty($result->GetPropertiesResult->SearchResults)) {
				$results = $result->GetPropertiesResult->SearchResults;

				// Group by resort name
				$resultsGrouped = array();
				foreach($results as $listing) {
					$resultsGrouped[$listing->Resort][] = $listing;
				}

				// Sort by key
				ksort($resultsGrouped);

				return array($resultsGrouped, count($results));
			}
		}
		catch(Exception $e) {
			echo 'Error' . $e->getMessage();
		}

		return array(array(), 0);
	}

	/**
	 * Gets property details.
	 *
	 * @param string $property_id
	 * @return NULL|stdClass
	 */
	public function getPropertyDetails($property_id)
	{
		$result = $this->soapClient->GetPropertyDetails(array(
			'PropertyID' => (string) $property_id,
			'Key' 		 => self::KEY
		));

		if ( ! empty($result->GetPropertyDetailsResult))
			return $result->GetPropertyDetailsResult;

		return NULL;
	}

	/**
	 * Requests more info about a property.
	 *
	 * @param  array 		$data
	 * @return bool
	 */
	public function requestInfo($data)
	{
		$result = $this->soapClient->RequestInfo(array(
			'Key' 	=> self::KEY,
			'oInfo' => $data,
			'MailTo' => self::MAIL_TO
		));

		if (isset($result->RequestInfoResult))
			return $result->RequestInfoResult;

		return FALSE;
	}

	/**
	 * Submits an offer for a property.
	 *
	 * @param  array 	$data
	 * @return bool
	 */
	public function submitOffer($data)
	{
		$result = $this->soapClient->SubmitOffer(array(
			'Key' 	 => self::KEY,
			'oOffer' => $data,
			'MailTo' => self::MAIL_TO
		));

		if (isset($result->SubmitOfferResult))
			return $result->SubmitOfferResult;

		return FALSE;
	}
}
