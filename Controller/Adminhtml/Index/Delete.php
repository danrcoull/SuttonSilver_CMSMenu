<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SuttonSilver\CMSMenu\Controller\Adminhtml\Index;

class Delete extends \SuttonSilver\CMSMenu\Controller\Adminhtml\MenuItems
{

    public function __construct(
        \Magento\Backend\App\Action\Context $context
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        $menuId = (int)$this->getRequest()->getParam('id');
        $parentId = null;
        if ($menuId) {
            try {
                $menu = $this->_initMenuItem(true);
                $parentId = $menu->getParentId();
                $this->_auth->getAuthStorage()->setDeletedPath($menu->getPath());
                $menu->delete();
                $this->messageManager->addSuccess(__('You deleted the menu item.'));
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('cmsmenu_menu/*/edit', ['_current' => true]);
            } catch (\Exception $e) {
                $this->messageManager->addError(__('Something went wrong while trying to delete the menu item.'));
                return $resultRedirect->setPath('cmsmenu_menu/*/edit', ['_current' => true]);
            }
        }
        return $resultRedirect->setPath('cmsmenu_menu/*/', ['_current' => true, 'id' => $parentId]);
    }
}
