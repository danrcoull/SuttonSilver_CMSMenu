<?php
/**
 * @author Daniel Coull <d.coull@suttonsilver.co.uk>
 */
namespace SuttonSilver\CMSMenu\Setup;
class UpgradeSchema implements \Magento\Framework\Setup\UpgradeSchemaInterface
{
    public function upgrade(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        if (version_compare($context->getVersion(), '0.0.2') < 0) {

            $installer->getConnection()->changeColumn(
                $installer->getTable('cms_menuitems'),
                'parent','parent_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'unsigned' => true,
                    'default' => '0',
                    'nullable' => false
                ]
            );
            $installer->getConnection()->addColumn(
                $installer->getTable('cms_menuitems'),
                'path',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' =>false,
                    'comment' => 'Tree Path'
                ]

            );
            $installer->getConnection()->addColumn(
                $installer->getTable('cms_menuitems'),
                'level',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => false,
                    'default' => '0',
                    'length'  => null,
                    'comment' => 'Level'
                ]

            );
            $installer->getConnection()->addColumn(
                $installer->getTable('cms_menuitems'),
                'position',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => false,
                    'default' => '0',
                    'length'  => null,
                    'comment' => 'Position'
                ]

            );
            $installer->getConnection()->addIndex(
                $installer->getTable('cms_menuitems'),
                $installer->getIdxName('cms_menuitems', ['level']),
                ['level']
            );

        }


        //END   table setup
        $installer->endSetup();
    }
}
