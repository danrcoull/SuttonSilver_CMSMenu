<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SuttonSilver\CMSMenu\Controller\Adminhtml;

/**
 * Catalog category controller
 */
abstract class MenuItems extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'SuttonSilver_CMSMenu::menu';

    protected function _initMenuItem($getRootInstead = false)
    {
        $menuId = (int)$this->getRequest()->getParam('id', false);
        $storeId = (int)$this->getRequest()->getParam('store');
        $menuItem = $this->_objectManager->create('SuttonSilver\CMSMenu\Model\MenuItems');

        $menuItem->setStoreId($storeId);

        if ($menuId && $menuId != 0) {
            $menuItem->load($menuId);
            if ($storeId) {
                if ($getRootInstead) {
                    $menuItem->load(1);
                } else {
                    return false;
                }
            }
        }

        $this->_objectManager->get('Magento\Framework\Registry')->register('menuitem', $menuItem);
        $this->_objectManager->get('Magento\Framework\Registry')->register('current_menuitem', $menuItem);
        return $menuItem;
    }
}