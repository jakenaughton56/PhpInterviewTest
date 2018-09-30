<?php

require dirname(dirname(__FILE__)) . '/classes/PurchasedOrders.php';
use API\Classes\PurchasedOrders;

$request = json_decode(file_get_contents('php://input'), true);
$purchaseOrderIds = [];
$idRequests = $request['purchase_order_ids'];

if($idRequests == NULL) {
	$errorMessage = array(
	   'error' => true,
	   'code' => '1',
	   'message' => 'No Puchase Order Ids Provided.'
	);
	echo json_encode($errorMessage);
	return;
}

foreach ($idRequests as $purchaseOrderId) {
	$purchaseOrderIds[] = $purchaseOrderId;
}

$purchasedOrders = new PurchasedOrders();
$orders = $purchasedOrders->calculateOrders($purchaseOrderIds);

$jsonObject = new stdClass();
$jsonObject->result = [];
$i = 0;
foreach ($orders as $productId => $total) {
	$jsonObject->result[$i] = new stdClass();
	$jsonObject->result[$i]->product_type_id = $productId;
	$jsonObject->result[$i]->total = floatval(number_format($total,1));
	$i++;
}

echo json_encode($jsonObject);

// WARNING!!!
// Terrible code below.
// This Code would be used to achieve the total as shown in the test requirements:
// "total": 25.0
// As it is impossible to maintain the decimal point with json encode (using a float variable.)


// $jsonStr = '{"result":[';
// $firstLoop = true;
// foreach ($orders as $productId => $total) {
// 	if(!$firstLoop) {
// 		$jsonStr .= '},';
// 	} else {
// 		$firstLoop = false;
// 	}
// 	$jsonStr .= '{"product_type_id":';
// 	$jsonStr .= $productId;
// 	$jsonStr .= ',"total":';
// 	$jsonStr .= number_format($total,1);
// }
// $jsonStr .= '}]}';
// echo $jsonStr;