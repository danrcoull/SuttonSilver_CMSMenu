<?php
namespace SuttonSilver\CMSMenu\Setup;
class UpgradeData implements \Magento\Framework\Setup\UpgradeDataInterface
{
    public function upgrade(\Magento\Framework\Setup\ModuleDataSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        if (version_compare($context->getVersion(), '0.0.2') < 0) {
            $rootId = \SuttonSilver\CMSMenu\Model\MenuItems::ROOT_MENU_ITEM_ID;
            $ob = \Magento\Framework\App\ObjectManager::getInstance();
            $menuItems = $ob->get('\SuttonSilver\CMSMenu\Model\MenuItemsFactory')->create();
            $menuItems
                ->setStoreId(0)
                ->setPath($rootId)
                ->setLevel(0)
                ->setPosition(0)
                ->setSortOrder(0)
                ->setTitle('Main Menu')
                ->setSlug('Main Menu')
                ->setInitialSetupFlag(true)
                ->save();
        }

        $installer->endSetup();
    }
}
