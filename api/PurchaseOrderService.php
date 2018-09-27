<?php

require 'vendor/autoload.php';
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;

class PurchaseOrderService 
{

	public function getPurchaseOrder($id) {

		$credentials = base64_encode('interview-test@cartoncloud.com.au:test123456');
		$client = new GuzzleHttp\Client(
			[
	            'headers' => [
	                'Authorization' => 'Basic ' . $credentials,
	            ],
	        ]
		);

		$request = new \GuzzleHttp\Psr7\Request('GET', 'https://api.cartoncloud.com.au/CartonCloud_Demo/PurchaseOrders/'. $id . '?version=5&associated=true');
		$promise = $client->sendAsync($request)->then(function ($response) {
		    echo 'I completed! ' . $response->getBody();
		});
		$promise->wait();
		return '';
		
	}
}
$purchaseOrderService = new PurchaseOrderService();
$request = $purchaseOrderService->getPurchaseOrder(2344);
// var_dump($request);