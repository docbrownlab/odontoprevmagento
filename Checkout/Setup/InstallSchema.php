<?php

namespace Odontoprev\Checkout\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    
    private $installer;
    
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->installer = $setup;
        $this->installer->startSetup();
        
        $this->createLogTable();
        
        $this->installer->endSetup();
    }
    
    private function createLogTable()
    {
        $table = $this->installer->getConnection()
        ->newTable($this->installer->getTable('odontoprev_log_details_propostal'))
        ->addColumn(
            'order_id_propostal',
            Table::TYPE_TEXT,
            '2M',
            [
                'nullable' => false,
            ],
            'ID checkout log'
        )->addColumn('log',
            Table::TYPE_TEXT,
            '2M',
            [
                'nullable' => false,
            ],
            'Log propostal details'
        );
        $this->installer->getConnection()->createTable($table);
    }
    
}
