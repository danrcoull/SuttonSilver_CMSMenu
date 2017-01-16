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
    protected function _initMenuItem($getRootInstead = false)
    {
        $menuId = (int)$this->getRequest()->getParam('id', false);
        $storeId = (int)$this->getRequest()->getParam('store');
        $menuItem = $this->_objectManager->create('SuttonSilver\CMSMenu\Model\MenuItems');
        $menuItem->setStoreId($storeId);

        if ($menuId) {
            $menuItem->load($menuId);
            if ($storeId) {
                $rootId = 1;
                if (!in_array($rootId, $menuItem->getPathIds())) {
                    // load root category instead wrong one
                    if ($getRootInstead) {
                        $menuItem->load($rootId);
                    } else {
                        return false;
                    }
                }
            }
        }

        $this->_objectManager->get('Magento\Framework\Registry')->register('menuitem', $menuItem);
        $this->_objectManager->get('Magento\Framework\Registry')->register('current_menuitem', $menuItem);
        $this->_objectManager->get('Magento\Cms\Model\Wysiwyg\Config')
            ->setStoreId($this->getRequest()->getParam('store'));
        return $menuItem;
    }
}