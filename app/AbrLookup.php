<?php

namespace App;

use SoapClient;
use stdClass;

class AbrLookup
{

	/**
	 * Lookup for party name based on ABN
	 *
	 * @param  int $abn
	 * @return string
	 */
	public function getRecordFromAbrNumber($abn)
	{
		$client = new SoapClient('http://abr.business.gov.au/abrxmlsearch/ABRXMLSearch.asmx?wsdl', ['connection_timeout' => 3]);

		$params = new stdClass;
		$params->searchString = $abn;
		$params->includeHistoricalDetails = 'N';
		$params->authenticationGuid = '7ce68f24-188d-4f4e-9fd6-0f479b215173';

		$results =  [
			'abn'  => '',
			'acn'  => '',
			'name' => '',
		];

		if (strlen($abn) == 11) {
			$response = $client->ABRSearchByABN($params);
		} else {
			$response = $client->ABRSearchByASIC($params);
		}

		if (isset($response->ABRPayloadSearchResults->response->businessEntity)) {
			$business_entity = $response->ABRPayloadSearchResults->response->businessEntity;

			if (is_array($business_entity->ABN)) {
				$abn = end($business_entity->ABN);
			} else {
				$abn = $business_entity->ABN;
			}

			$results =  [
				'abn'  => $abn ? $abn->identifierValue : '',
				'acn'  => $business_entity->ASICNumber ? $business_entity->ASICNumber : '',
				'name' => isset($business_entity->mainName) ? $business_entity->mainName->organisationName : '',
			];
		}

		return $results;
	}

	/**
	 * AJAX request for getting ABR results for the above endpoint.
	 */
	public function searchByName($phrase)
	{
		$client = new SoapClient('http://abr.business.gov.au/abrxmlsearch/ABRXMLSearch.asmx?wsdl');

		$params = new stdClass;
		$params->name = $phrase;
		$params->authenticationGuid = '7ce68f24-188d-4f4e-9fd6-0f479b215173';

		$response = $client->ABRSearchByNameSimpleProtocol($params);

		if (!isset($response->ABRPayloadSearchResults->response->searchResultsList)) {
			return;
		}

		return $response->ABRPayloadSearchResults->response->searchResultsList->searchResultsRecord;
	}

}