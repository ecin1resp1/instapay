<?php
/* Copyright (C) EC Brands Corporation - All Rights Reserved
** Contact Licensing@ECInternet.com for use guidelines
*/

namespace ECInternet\InstaPAY\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $installer->getConnection()->addColumn(
            $installer->getTable('quote'),
            'instapay_payment_id',
            [
                'type' => 'text',
                'nullable' => false,
                'comment' => 'InstaPAY Payment ID',
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order'),
            'instapay_payment_id',
            [
                'type' => 'text',
                'nullable' => false,
                'comment' => 'InstaPAY Payment ID',
            ]
        );

        $setup->endSetup();
    }
}
