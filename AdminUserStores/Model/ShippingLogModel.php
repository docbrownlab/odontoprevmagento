<?php

namespace Odontoprev\AdminUserStores\Model;

class ShippingLogModel
{
	public $userId;
	public $storesId;

	public function __construct($userId, $storesId = [])
	{
		$this->userId = $userId;
		$this->storesId = $storesId;
	}

	public function getUserId()
	{
		return $this->userId;
	}

	public function getStoresId()
	{
		return $this->storesId;
	}
}