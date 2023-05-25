<?php

namespace Odontoprev\AdminUserStores\Test\Unit\Helper;

use Odontoprev\AdminUserStores\Model\ShippingLogModel;
use Odontoprev\AdminUserStores\Helper\AdminUserStoresHelper;

class AdminUserStoresHelperTest extends \PHPUnit_Framework_TestCase
{
	private $shippingLogModel;
	private $adminUserStoresHelper;
	private $resourceConnection;

	const USER = 100;
	const STORES = [200, 300, 400];

	public function setUp()
	{
		$this->resourceConnection = $this->getMockBuilder('\Magento\Framework\App\ResourceConnection')
		                            ->disableOriginalConstructor()
		                            ->setMethods([
		                            	'getConnection', 'getTableName', 'from', 'select',
		                            	'where', 'fetchAll', 'delete', 'insertMultiple'
		                            ])
		                            ->getMock();

		$this->resourceConnection->method('getConnection')->will($this->returnSelf());
		$this->resourceConnection->method('getTableName')->will($this->returnSelf());
		$this->resourceConnection->method('select')->will($this->returnSelf());
		$this->resourceConnection->method('from')->will($this->returnSelf());
		$this->resourceConnection->method('where')->will($this->returnSelf());
		$this->resourceConnection->method('delete')->will($this->returnSelf());
		$this->resourceConnection->method('insertMultiple')->will($this->returnSelf());

		$this->shippingLogModel = new ShippingLogModel(self::USER);
		$this->adminUserStoresHelper = new AdminUserStoresHelper($this->shippingLogModel, $this->resourceConnection);
	}

	public function testTypeAndInstance()
	{
		$this->assertInternalType('object', $this->adminUserStoresHelper);
		$this->assertInstanceOf('Odontoprev\AdminUserStores\Helper\AdminUserStoresHelper', $this->adminUserStoresHelper);
		$this->assertEquals(100, $this->adminUserStoresHelper->getUserId());
	}

	public function testSaveUserWithoutStores()
	{
		$this->assertEquals(0, $this->adminUserStoresHelper->save());
	}

	public function testSaveUserWithOneStore()
	{
		$this->shippingLogModel->storesId = [self::STORES[0]];
		$this->adminUserStoresHelper = new AdminUserStoresHelper($this->shippingLogModel, $this->resourceConnection);

		$this->assertEquals(0, $this->adminUserStoresHelper->save());
	}

	public function testSaveUserMultipleStores()
	{
		$this->shippingLogModel->storesId = self::STORES;
		$this->adminUserStoresHelper = new AdminUserStoresHelper($this->shippingLogModel, $this->resourceConnection);

		$this->assertEquals(0, $this->adminUserStoresHelper->save());
	}
}