<?php

require 'vendor/autoload.php';
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;

class PurchaseOrderService 
{

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

	public function calculateTotals(array $ids) {
		$purchaseOrders = $this->getPurchaseOrders($ids);
		$totals = [];

		foreach($purchaseOrders as $purchaseOrder) {
			$purchaseOrderProducts =  $purchaseOrder->data->PurchaseOrderProduct;
			foreach ($purchaseOrderProducts as $purchaseOrderProduct) {
				if(!isset($totals[$purchaseOrderProduct->product_type_id])) {
			        $totals[$purchaseOrderProduct->product_type_id] = $purchaseOrderProduct->unit_quantity_initial * $purchaseOrderProduct->Product->weight;
			    } else {
			    	$totals[$purchaseOrderProduct->product_type_id] += $purchaseOrderProduct->unit_quantity_initial * $purchaseOrderProduct->Product->weight;
			    }
			}
		}
		return $totals;
	}

}
$purchaseOrderService = new PurchaseOrderService();
$request = $purchaseOrderService->calculateTotals([2344, 2345, 2346]);