<?php

namespace Odontoprev\BraspagIntegration\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\{
    UpgradeSchemaInterface,
    ModuleContextInterface,
    SchemaSetupInterface
};

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{

    private $nameTableAccountDebit = 'sales_order_payment_account_debit';

    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    )
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.1.0') < 0) {
            $this->createTableAccountDebit($setup);
        }

        $setup->endSetup();
    }

    private function createTableAccountDebit(SchemaSetupInterface $setup) {

        if($setup->tableExists($this->nameTableAccountDebit))
            return;

        $tableName = $setup->getTable($this->nameTableAccountDebit);

        $table = $setup->getConnection()->newTable(
            $tableName
        )
        ->addColumn(
            'entity_id',
            Table::TYPE_INTEGER,
            null,
            [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ],
            'ID'
        )
        ->addColumn(
            'order_id',
            Table::TYPE_INTEGER,
            null,
            [
                'unsigned' => true,
                'nullable' => false,
                'default' => '0'
            ],
            'Order ID'
        )
        ->addColumn(
            'account',
            Table::TYPE_TEXT,
            null,
            [
                'unsigned' => true,
                'nullable' => false
            ],
            'Account'
        )
        ->addColumn(
            'account_dv',
            Table::TYPE_TEXT,
            null,
            [
                'unsigned' => true,
                'nullable' => false
            ],
            'Account Verification Digit'
        )
        ->addColumn(
            'agency',
            Table::TYPE_TEXT,
            null,
            [
                'unsigned' => true,
                'nullable' => false
            ],
            'Agency'
        )
        ->addColumn(
            'agency_dv',
            Table::TYPE_TEXT,
            null,
            [
                'unsigned' => true,
                'nullable' => true
            ],
            'Agency Verification Digit'
        )
        ->addColumn(
            'bank_code',
            Table::TYPE_TEXT,
            null,
            [
                'unsigned' => true,
                'nullable' => false
            ],
            'Bank Code'
        )
        ->addIndex(
            $setup->getIdxName($tableName, ['order_id']),
            ['order_id']
        )
        ->addForeignKey(
            $setup->getFkName($tableName, 'order_id', 'sales_order', 'entity_id'),
            'order_id',
            $setup->getTable('sales_order'),
            'entity_id',
            Table::ACTION_CASCADE
        )
        ->setComment('Responsible customer account information')
        ->setOption('type', 'InnoDB')
        ->setOption('charset', 'utf8');

        $setup->getConnection()->createTable($table);
    }
}
