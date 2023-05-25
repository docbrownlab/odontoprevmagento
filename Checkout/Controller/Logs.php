<?php
namespace Odontoprev\Checkout\Controller;
// class Logs extends \Magento\Framework\App\Action\Action
class Logs
{   
    /**
     * Add/update the specified plan life.
     *  
     * @param \Odontoprev\Checkout\Controller\Logs $life
     * @return void
     */
    public function execute()
    {   

        // save dados in database
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();

        //Insert Data into table
        $sql = "Insert Into odontoprev_log_details_propostal Values ('".$_GET['life']['quote_item_id']."', '".json_encode($_GET['life'])."',now())";
        $connection->query($sql);

        return true;
    }
}