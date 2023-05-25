<?php

namespace Odontoprev\AdminUserStores\Controller\Adminhtml\User;

use Odontoprev\AdminUserStores\Model\ShippingLogModel;
use Odontoprev\AdminUserStores\Helper\AdminUserStoresHelper as Helper;

class Save extends \Magento\User\Controller\Adminhtml\User\Save
{
	private $userId;
	private $userData;
	private $helper;
	private $logModel;

	public function __construct(
		\Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\User\Model\UserFactory $userFactory
	)
	{
		parent::__construct($context, $coreRegistry, $userFactory);

		$this->userId = (int) $this->getRequest()->getParam('user_id');
        $this->userData = $this->getRequest()->getPostValue();

        if (!isset($this->userData['stores_selected'])) {
        	$this->userData['stores_selected'] = [];
        }

		$connection = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');

        $this->logModel = new ShippingLogModel($this->userId, $this->userData['stores_selected']);
		$this->helper = new Helper($this->logModel, $connection);
	}

	public function execute()
	{
		$model = $this->_userFactory
			->create()
			->load($this->userId)
			->setData(
				$this->_getAdminUserData(
					$this->userData
				));

		$currentUser = $this->_objectManager->get('Magento\Backend\Model\Auth\Session')->getUser();

		$currentUserPasswordField = \Magento\User\Block\User\Edit\Tab\Main::CURRENT_USER_PASSWORD_FIELD;
		$isCurrentUserPasswordValid = isset($this->userData[$currentUserPasswordField])
		    && !empty($this->userData[$currentUserPasswordField])
		    && is_string($this->userData[$currentUserPasswordField]);

		try
		{
			if (!($isCurrentUserPasswordValid)) {
			    throw new AuthenticationException(__('You have entered an invalid password for current user.'));
			}

			$currentUser->performIdentityCheck($this->userData[$currentUserPasswordField]);

			parent::execute();

			if (!empty($this->userId)) {
				$this->helper->save();
			}
		}
		catch (\Magento\Framework\Exception\AuthenticationException $e) 
		{
            $this->messageManager->addError(__('You have entered an invalid password for current user.'));
            $this->redirectToEdit($model, $this->userData);
        }
	}
}