<?php

require dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/app/api/classes/PurchasedOrders.php';
use PHPUnit\Framework\TestCase;

class PuchasedOrdersTest extends TestCase
{
	protected $puchasedOrders;

	public function setUp() {
		$this->puchasedOrders = new API\Classes\PurchasedOrders();
	}

	public function testCalculateWeightProductEmptyArray() {
		$productTypeIdOne = 1;
		$unitAmountOne = 5.5;
		$productTotals = [];

		$this->puchasedOrders->calculateProductUnitAmount($productTypeIdOne, $unitAmountOne, $productTotals);

		$this->assertEquals(count($productTotals), 1);
		$this->assertTrue(isset($productTotals[$productTypeIdOne]));
		$this->assertEquals($productTotals[$productTypeIdOne], $unitAmountOne);
	}

	public function testCalculateWeightProductNewProductType() {
		$productTypeIdOne = 1;
		$productTypeIdTwo = 2;
		$unitAmountOne = 5.5;
		$unitAmountTwo = 8.3;
		$productTotals = [];

		$this->puchasedOrders->calculateProductUnitAmount($productTypeIdOne, $unitAmountOne, $productTotals);
		$this->puchasedOrders->calculateProductUnitAmount($productTypeIdTwo, $unitAmountTwo, $productTotals);

		$this->assertEquals(count($productTotals), 2);
		$this->assertTrue(isset($productTotals[$productTypeIdTwo]));
		$this->assertEquals($productTotals[$productTypeIdTwo], $unitAmountTwo);
	}
	
	public function testCalculateWeightProductAddingToExistingProductType() {
		$productTypeIdOne = 1;
		$unitAmountOne = 5.5;
		$unitAmountTwo = 8.3;
		$productTotals = [];

		$this->puchasedOrders->calculateProductUnitAmount($productTypeIdOne, $unitAmountOne, $productTotals);
		$this->puchasedOrders->calculateProductUnitAmount($productTypeIdOne, $unitAmountTwo, $productTotals);

		$this->assertEquals($productTotals[$productTypeIdOne], $unitAmountOne + $unitAmountTwo );
	}

	public function testGetOrdersUsingGoodData() {
		$data = [2344,2345,2346];
		$resps = $this->puchasedOrders->getOrders($data);
		foreach ($resps as $resp) {
			$this->assertEquals($resp->info, 'SUCCESS');
		}
	}

	public function testGetOrdersUsingBadData() {
		$data = [1244123,23734234,23289433];
		$resps = $this->puchasedOrders->getOrders($data);
		foreach ($resps as $resp) {
			$this->assertEquals($resp->info, 'ERROR');
		}
	}

	public function testCalculateOrdersUsingGoodData() {
		$data = [2344,2345,2346];
		$resps = $this->puchasedOrders->calculateOrders($data);

		$this->assertEquals(count($resps), 3);

		foreach ($resps as $key => $resp) {
			switch ($key) {
				case 1:
					$this->assertEquals($resp, 41.5);
					break;
				case 2:
					$this->assertEquals($resp, 13.8);
					break;
				case 3:
					$this->assertEquals($resp, 25.0);
					break;
				default:
					break;
			}
		}
	}

	public function testCalculateOrdersUsingBadData() {
		$data = [1244123,23734234,23289433];
		$resps = $this->puchasedOrders->calculateOrders($data);

		$this->assertEquals(count($resps), 0);
	}

	public function testCalculateOrdersUsingMixedData() {
		$data = [2344,23734234,2346];
		$resps = $this->puchasedOrders->calculateOrders($data);

		$this->assertEquals(count($resps), 3);

		foreach ($resps as $key => $resp) {
			switch ($key) {
				case 1:
					$this->assertEquals($resp, 31);
					break;
				case 2:
					$this->assertEquals($resp, 13.8);
					break;
				case 3:
					$this->assertEquals($resp, 12.5);
					break;
				default:
					break;
			}
		}
	}

}