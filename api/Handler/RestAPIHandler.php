<?php

require 'vendor/autoload.php';
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;

class RestAPIHandler 
{

	/**
	 * Retrieve purchase orders from Carton Cloud API
	 *
     * @param array $ids
     *
     * @return array $responses
     */
	public function getPurchaseOrders(array $ids) {
		$promises = [];
		$responses = [];
		$credentials = base64_encode('interview-test@cartoncloud.com.au:test123456');

		$client = new GuzzleHttp\Client(
			[
	            'headers' => [
	                'Authorization' => 'Basic ' . $credentials,
	            ],
	        ]
		);

		// Start all the request calls asynchronously
		foreach ($ids as $id) {
			$request = new \GuzzleHttp\Psr7\Request('GET', 'https://api.cartoncloud.com.au/CartonCloud_Demo/PurchaseOrders/'. $id . '?version=5&associated=true');
			$promise = $client->sendAsync($request)->then(function ($response) {
			    return $response->getBody();
			}, function ($exception) {
		    	return $exception->getMessage();
			});
			$promises[] = $promise;
		}

		// Get all the returned data.
		foreach ($promises as $promise) {
			$data = $promise->wait();
			$response = json_decode($data);
			$responses[] = $response;
		}
		
		return $responses;
	}

	/**
	 * Retrieve and caclulate all purchase orders
	 *
     * @param array $ids
     *
     * @return array $totals
     */
	public function calculatePurchaseOrders(array $ids) {
		$totals = [];
		$weightProducts = [1,3];
		$volumeProducts = [2];

		$purchaseOrders = $this->getPurchaseOrders($ids);

		foreach($purchaseOrders as $purchaseOrder) {
			$purchaseOrderProducts =  $purchaseOrder->data->PurchaseOrderProduct;
			foreach ($purchaseOrderProducts as $purchaseOrderProduct) {
				if(in_array($purchaseOrderProduct->product_type_id, $weightProducts)) {
					$this->calculateWeightProduct($purchaseOrderProduct, $totals);
				} else if (in_array($purchaseOrderProduct->product_type_id, $volumeProducts)) {
					$this->calculateVolumeProduct($purchaseOrderProduct, $totals);
				} else {
					// Skip, unknown product type
				}
			}
		}
		return $totals;
	}

	/**
	 * @param stdClass $purchaseOrderProduct
     * @param array &$totals
     */
	public function calculateWeightProduct(stdClass $purchaseOrderProduct, array &$totals) {
		$totalWeight = $purchaseOrderProduct->unit_quantity_initial * $purchaseOrderProduct->Product->weight;

		if(!isset($totals[$purchaseOrderProduct->product_type_id])) {
	        $totals[$purchaseOrderProduct->product_type_id] = $totalWeight;
	    } else {
	    	$totals[$purchaseOrderProduct->product_type_id] += $totalWeight;
	    }
	}

	/**
	 * @param stdClass $purchaseOrderProduct
     * @param array &$totals
     */
	public function calculateVolumeProduct(stdClass $purchaseOrderProduct, array &$totals) {
		$totalVolume = $purchaseOrderProduct->unit_quantity_initial * $purchaseOrderProduct->Product->volume;

		if(!isset($totals[$purchaseOrderProduct->product_type_id])) {
	        $totals[$purchaseOrderProduct->product_type_id] = $totalVolume;
	    } else {
	    	$totals[$purchaseOrderProduct->product_type_id] += $totalVolume;
	    }
	}

}

?>