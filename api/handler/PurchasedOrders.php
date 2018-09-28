<?php
namespace API\Handler;

require '../vendor/autoload.php';
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;

class PurchaseOrders
{
	const CCCREDENTIALS = 'interview-test@cartoncloud.com.au:test123456';
	const WEIGHTPRODUCTS = [1,3];
	const VOLUMEPRODUCTS = [2];

	/**
	 * Retrieve and caclulate all purchase orders
	 *
     * @param array $ids
     *
     * @return array $totals
     */
	public function calculateOrders(array $ids) {
		$totals = [];

		$purchaseOrders = $this->getOrders($ids);

		foreach($purchaseOrders as $purchaseOrder) {
			$purchaseOrderProducts =  $purchaseOrder->data->PurchaseOrderProduct;
			foreach ($purchaseOrderProducts as $purchaseOrderProduct) {
				if(in_array($purchaseOrderProduct->product_type_id, self::WEIGHTPRODUCTS)) {
					$this->calculateWeightProduct($purchaseOrderProduct, $totals);
				} else if (in_array($purchaseOrderProduct->product_type_id, self::VOLUMEPRODUCTS)) {
					$this->calculateVolumeProduct($purchaseOrderProduct, $totals);
				} else {
					// Skip, unknown product type
				}
			}
		}
		return $totals;
	}

	/**
	 * Retrieve purchase orders from Carton Cloud API
	 *
     * @param array $ids
     *
     * @return array $responses
     */
	private function getOrders(array $ids) {
		$promises = [];
		$responses = [];
		$credentials = base64_encode(self::CCCREDENTIALS);

		$client = new Client(
			[
	            'headers' => [
	                'Authorization' => 'Basic ' . $credentials,
	            ],
	        ]
		);

		// Start all the request calls asynchronously
		foreach ($ids as $id) {
			$request = new Request('GET', 'https://api.cartoncloud.com.au/CartonCloud_Demo/PurchaseOrders/'. $id . '?version=5&associated=true');
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
	 * @param stdClass $purchaseOrderProduct
     * @param array &$totals
     */
	private function calculateWeightProduct(\stdClass $purchaseOrderProduct, array &$totals) {
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
	private function calculateVolumeProduct(\stdClass $purchaseOrderProduct, array &$totals) {
		$totalVolume = $purchaseOrderProduct->unit_quantity_initial * $purchaseOrderProduct->Product->volume;

		if(!isset($totals[$purchaseOrderProduct->product_type_id])) {
	        $totals[$purchaseOrderProduct->product_type_id] = $totalVolume;
	    } else {
	    	$totals[$purchaseOrderProduct->product_type_id] += $totalVolume;
	    }
	}

}

?>