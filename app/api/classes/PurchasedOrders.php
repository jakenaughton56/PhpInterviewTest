<?php
namespace API\Classes;

require dirname(dirname(dirname(dirname(__FILE__)))) . '/vendor/autoload.php';
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;

class PurchasedOrders
{
	const CCCREDENTIALS = 'interview-test@cartoncloud.com.au:test123456';
	const CCAPIURLSTART = 'https://api.cartoncloud.com.au/CartonCloud_Demo/PurchaseOrders/';
	const CCAPIURLFINISH = '?version=5&associated=true';
	const ERROR = 'ERROR';
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
		$productTotals = [];
		$purchaseOrders = $this->getOrders($ids);

		foreach($purchaseOrders as $purchaseOrder) {
			if($purchaseOrder->info == self::ERROR){
				// Skip, no data was available for order
				continue;
			}
			$purchaseOrderProducts =  $purchaseOrder->data->PurchaseOrderProduct;
			foreach ($purchaseOrderProducts as $purchaseOrderProduct) {

				if(in_array($purchaseOrderProduct->product_type_id, self::WEIGHTPRODUCTS)) {
					$unitAmount = $purchaseOrderProduct->unit_quantity_initial * $purchaseOrderProduct->Product->weight;
					$this->calculateProductUnitAmount($purchaseOrderProduct->product_type_id, $unitAmount, $productTotals);
				} else if (in_array($purchaseOrderProduct->product_type_id, self::VOLUMEPRODUCTS)) {
					$unitAmount = $purchaseOrderProduct->unit_quantity_initial * $purchaseOrderProduct->Product->volume;
					$this->calculateProductUnitAmount($purchaseOrderProduct->product_type_id, $unitAmount, $productTotals);
				} else {
					// Skip, unknown product type
				}
			}
		}
		return $productTotals;
	}

	/**
	 * Retrieve purchase orders from Carton Cloud API
	 *
     * @param array $ids
     *
     * @return array $responses
     */
	public function getOrders(array $ids) {
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
			$request = new Request('GET', self::CCAPIURLSTART . $id . self::CCAPIURLFINISH);
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
			if(is_string($data)) {
				$response = new \stdClass();
				$response->info = self::ERROR;
				$responses[] = $response;
			} else {
				$response = json_decode($data);	
				$responses[] = $response;
			}
		}
		return $responses;
	}

	/**
	 * @param int $productTypeId
	 * @param float $unitAmount
     * @param array &$totals
     */
	public function calculateProductUnitAmount(int $productTypeId, float $unitAmount, array &$productTotals) {
		if(!isset($productTotals[$productTypeId])){
	        $productTotals[$productTypeId] = $unitAmount;
	    } else {
	    	$productTotals[$productTypeId] += $unitAmount;
	    }
	}
}

?>