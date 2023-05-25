<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Odontoprev\Agreement\Setup;
  
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
  
/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
  
        // Get quote_item_life table
        $tableName = $installer->getTable('checkout_agreement_customer');
        // Check if the table already exists
        if ($installer->getConnection()->isTableExists($tableName) != true) {
            // Create quote_item_life table
            $table = $installer->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'agreement_id',
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
                    'quote_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'unsigned' => true,
                        'nullable' => false,
                        'default' => '0'
                    ],
                    'Quote Id'
                )
                ->addColumn(
                    'datetime',
                    Table::TYPE_TIMESTAMP,
                    null,
                    [
                        'unsigned' => true,
                        'nullable' => false
                    ],
                    'Date Time of Agreement'
                )
                ->addColumn(
                    'ip',
                    Table::TYPE_TEXT,
                    255,
                    [
                        'unsigned' => true,
                        'nullable' => false
                    ],
                    'IP'
                )
                ->addIndex(
                    $installer->getIdxName('checkout_agreement_customer', ['quote_id']),
                    ['quote_id']
                )
                ->addForeignKey(
                    $installer->getFkName('checkout_agreement_customer', 'quote_id', 'quote', 'entity_id'),
                    'quote_id',
                    $installer->getTable('quote'),
                    'entity_id',
                    Table::ACTION_CASCADE
                )
                ->setComment('List of Agreement')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($table);
        }
  
        $installer->endSetup();
    }
}
