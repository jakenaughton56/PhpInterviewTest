<?php

require '../api/handler/PurchasedOrders.php';

use API\Handler\PurchaseOrders;

$request = json_decode(file_get_contents('php://input'), true);
$purchase_order_ids = [];
foreach ($request['purchase_order_ids'] as $purchase_order_id) {
	$purchase_order_ids[] = $purchase_order_id;
}
$purchaseOrders = new PurchaseOrders();
$orders = $purchaseOrders->calculateOrders($purchase_order_ids);

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