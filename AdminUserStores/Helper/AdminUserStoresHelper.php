<?php

namespace Odontoprev\AdminUserStores\Helper;

use Magento\Framework\App\ResourceConnection;
use Odontoprev\AdminUserStores\Model\ShippingLogModel;

class AdminUserStoresHelper
{
	private $userId;
	private $storesId;
	private $table;
	private $connection;

	const TABLE_NAME = 'admin_user_stores';

	public function __construct(
		ShippingLogModel $log,
		ResourceConnection $resourceConnection
	)
	{
		$this->userId = $log->userId;
		$this->storesId = $log->storesId;
		$this->table = $resourceConnection->getTableName(self::TABLE_NAME);
		$this->connection = $resourceConnection->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
	}

	public function getUserId()
	{
		return $this->userId;
	}

	public function getStoresId()
	{
		return $this->storesId;
	}

	public function save()
	{
		try
		{
	        $this->connection->delete($this->table, ['user_id = ?' => $this->userId]);

	        if (!empty($this->storesId)) {
	        	foreach ($this->storesId as $key => $storeId) {
	        		$insertData[] = ['user_id' => $this->userId, 'store_id' => $storeId];
	        	}

	        	$this->connection->insertMultiple($this->table, $insertData);
	        }

	        return 0;
	    }
	    catch (\Exception $ex)
	    {
	    	return 1;
	    }
	}

	public function getUserStores()
	{
		$sql = $this->connection
			->select()
			->from($this->table)
			->where('user_id = ?', $this->userId);

		return $this->connection->fetchAll($sql);
	}
}