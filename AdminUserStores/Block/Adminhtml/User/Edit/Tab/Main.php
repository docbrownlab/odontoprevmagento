<?php 

namespace Odontoprev\AdminUserStores\Block\Adminhtml\User\Edit\Tab;

use Odontoprev\AdminUserStores\Model\ShippingLogModel;
use Odontoprev\AdminUserStores\Helper\AdminUserStoresHelper as Helper;

class Main
{
    protected $_objectManager;
    protected $_storeManager;
    protected $_userStores;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\User\Block\User\Edit\Tab\Main $subject,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->_storeManager = $storeManager;
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        
        $userId = $subject->getRequest("user_id")->getParam("user_id");
        $connection = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');

        if ($userId) {
            $logModel = new ShippingLogModel($userId);
            $helper = new Helper($logModel, $connection);

            $this->_userStores = $helper->getUserStores();
        }
    }

    /**
     * Get form HTML
     *
     * @return string
     */

    public function aroundGetFormHtml(
        \Magento\User\Block\User\Edit\Tab\Main $subject,
        \Closure $proceed
    )
    {
        $checked = [];
        $stores_list = [];
        $storeManagerDataList = $this->_storeManager->getStores();

        foreach ($storeManagerDataList as $key => $value) {
            $stores_list[] = ['value' => $key, 'label' => str_replace(' View', '', $value['name'])];
        }

        $form = $subject->getForm();

        if (is_object($form)) { 
            $user_id = $subject->getRequest("user_id")->getParam("user_id");
            
            if ($user_id) {
                foreach ($this->_userStores as $key => $value) {
                    $checked[] = $value['store_id'];
                }
                
                $fieldset = $form->addFieldset('all_stores', ['legend' => __('Lojas')]);

                $fieldset->addField('stores_selected',
                    'checkboxes',
                    [
                        'name' => 'stores_selected[]',
                        'values' => $stores_list,
                        'checked' => $checked
                    ]
                );

                $subject->setForm($form);
            }
        }

        return $proceed();
    }
}
