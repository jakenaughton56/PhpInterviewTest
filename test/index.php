<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require dirname(dirname(__FILE__)) . '/app/api/handler/PurchasedOrders.php';

use API\Handler\PurchasedOrders;

$request = json_decode(file_get_contents('php://input'), true);
$purchaseOrderIds = [];
$idRequests = $request['purchase_order_ids'];

if($idRequests == NULL) {
	$message = array(
	   'error' => true,
	   'code' => '1',
	   'message' => 'No Puchase Order Ids Provided.'
	);
	echo json_encode($message);
	return;
}

foreach ($idRequests as $purchaseOrderId) {
	$purchaseOrderIds[] = $purchaseOrderId;
}
$purchasedOrders = new PurchasedOrders();
$orders = $purchasedOrders->calculateOrders($purchaseOrderIds);

$jsonStr = '{"result":[';
$firstLoop = true;
foreach ($orders as $productId => $total) {
	if(!$firstLoop) {
		$jsonStr .= '},';
	} else {
		$firstLoop = false;
	}

	$jsonStr .= '{"product_type_id":';
	$jsonStr .= $productId;
	$jsonStr .= ',"total":';
	$jsonStr .= number_format($total,1);
}
$jsonStr .= '}]}';

echo $jsonStr;