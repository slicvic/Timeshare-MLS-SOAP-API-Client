<?php

/**
 * Search Query Parameters
 *
 * @author Victor Lantigua <vmlantigua@gmail.com>
 */
class SearchParameters {
    public $OwnershipUsage      = '';
    public $ResordCode          = '';
    public $Type                = '';
    public $Resort              = '';
    public $City                = '';
    public $PointsQualifyer     = '';
    public $PointsValue         = '';
    public $PriceRange          = '';
    public $Season              = '';
    public $Region              = '';
    public $State               = '';
    public $Week                = '';
    public $FirstUse            = '';
    public $Bed                 = '';
    public $Bath                = '';
    public $PriceLow            = '';
    public $PriceHigh           = '';

    /**
     * Creates an instance of SearchParameters and initializes properties based on given criteria.
     * @param  array $criteria
     * @return SearchParameters
     */
    public static function factory(array $criteria = [])
    {
        $instance = new SearchParameters();

        foreach($criteria as $name => $value) {
            $instance->$name = $value;
        }

        return $instance;
    }
}

/**
 * MLS SOAP API Client
 *
 * @author Victor Lantigua <vmlantigua@gmail.com>
 */
class MlsApiClient {
    /**
     * API URL.
     * @var string
     */
    private $apiUrl;

    /**
     * API key.
     * @var string
     */
    private $apiKey;

    /**
     * API Member ID.
     * @var string
     */
    private $apiMemberId;

    /**
     * An instance of SoapClient.
     * @var SoapClient
     */
    private $soapClient;

    /**
     * Converts a boolean string to 'Yes' or 'No'.
     *
     * @param string $value
     * @return string
     */
    public static function bool2Text(string $value)
    {
        return ($value === 'True') ? 'Yes' : 'No';
    }

    /**
     * Constructor.
     * @param string $apiKey
     * @param string $apiMemberId
     * @param string $apiUrl
     * @throws Exception
     */
    public function __construct(string $apiKey, string $apiMemberId, string $apiUrl = 'http://silverlightapi.timesharebrokersmls.com/tsbmlsws.asmx?wsdl')
    {
        $this->apiUrl = $apiUrl;
        $this->apiKey = $apiKey;
        $this->apiMemberId = $apiMemberId;

        try {
            $this->soapClient = new SoapClient($this->apiUrl);
        } catch(Exception $e) {
            throw $e;
        }
    }

    /**
     * Gets properties grouped by resort name.
     *
     * @param  array $criteria
     * @return array [results[], total]
     * @throws Exception
     */
    public function getProperties(array $criteria = [])
    {
        try {
            $result = $this->soapClient->GetProperties([
                'Search'    => SearchParameters::factory($criteria),
                'Key'       => $this->apiKey,
                'MemberID'  => $this->apiMemberId
            ]);

            if (!empty($result->GetPropertiesResult->SearchResults)) {
                $results = $result->GetPropertiesResult->SearchResults;

                // Group by resort name
                $groupedResults = [];
                foreach($results as $listing) {
                    $groupedResults[$listing->Resort][] = $listing;
                }

                // Sort by key
                ksort($groupedResults);

                return [$groupedResults, count($results)];
            } else {
                return [[], 0];
            }
        } catch(Exception $e) {
            throw $e;
        }
    }

    /**
     * Gets property details.
     *
     * @param string $propertyId
     * @return null|stdClass
     */
    public function getPropertyDetails(string $propertyId)
    {
        $result = $this->soapClient->GetPropertyDetails([
            'PropertyID' => $propertyId,
            'Key'        => $this->apiKey
        ]);

        if (!empty($result->GetPropertyDetailsResult)) {
            return $result->GetPropertyDetailsResult;
        }

        return null;
    }

    /**
     * Requests more info about a property.
     *
     * @param array $info
     * @param string $mailTo
     * @return bool
     */
    public function requestInfo(array $info, string $mailTo)
    {
        $result = $this->soapClient->RequestInfo([
            'Key'   => $this->apiKey,
            'oInfo' => $info,
            'MailTo' => $mailTo
        ]);

        if (isset($result->RequestInfoResult)) {
            return $result->RequestInfoResult;
        }

        return false;
    }

    /**
     * Submits an offer for a property.
     *
     * @param array $offer
     * @param string $mailTo
     * @return bool
     */
    public function submitOffer(array $offer, string $mailTo)
    {
        $result = $this->soapClient->SubmitOffer([
            'Key'    => $this->apiKey,
            'oOffer' => $offer,
            'MailTo' => $mailTo
        ]);

        if (isset($result->SubmitOfferResult)) {
            return $result->SubmitOfferResult;
        }

        return false;
    }
}
