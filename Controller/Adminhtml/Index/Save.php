<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SuttonSilver\CMSMenu\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use SuttonSilver\CMSMenu\Model\MenuItems;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;

class Save extends \SuttonSilver\CMSMenu\Controller\Adminhtml\MenuItems
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'SuttonSilver_CMSMenu::menu';

    /**
     * @var PostDataProcessor
     */
    protected $dataProcessor;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @param Action\Context $context
     * @param PostDataProcessor $dataProcessor
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        DataPersistorInterface $dataPersistor,
        Action\Context $context
    ) {

        $this->dataPersistor = $dataPersistor;
        parent::__construct($context);
    }

    protected function getParentCategory($parentId, $storeId)
    {
        if (!$parentId) {
            $parentId = 1;
        }
        return $this->_objectManager->create(\SuttonSilver\CMSMenu\Model\MenuItems::class)->load($parentId);
    }

    /**
     * Save action
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        $menuItem = $this->_initMenuItem();

        if (!$menuItem) {
            return $resultRedirect->setPath('cmsmenu_menu/*/*', ['_current' => true, 'id' => null]);
        }

        $data['general'] = $this->getRequest()->getPostValue();
        $menuItemData = $data['general'];

        $isNewMenuItem = !isset($menuItemData['suttonsilver_cmsmenu_menuitems_id']);
        $storeId = isset($menuItemData['store_id']) ? $menuItemData['store_id'] : null;
        $store = $this->storeManager->getStore($storeId);
        $this->storeManager->setCurrentStore($store->getCode());
        $parentId = isset($menuItemData['parent']) ? $menuItemData['parent'] : null;
        if ($menuItemData) {
            if ($isNewMenuItem) {
                $parentCategory = $this->getParentCategory($parentId, $storeId);
                $menuItem->setPath($parentCategory->getPath());
                $menuItem->setParentId($parentCategory->getId());
            }
        }

        try {
            $menuItem->save();
            $this->messageManager->addSuccessMessage(__('You saved the Menu Item.'));
            $this->dataPersistor->clear('cmsmenu_menu');
            if ($this->getRequest()->getParam('back')) {
                return $resultRedirect->setPath('*/*/edit', ['suttonsilver_cmsmenu_menuitems_id' => $model->getId(), '_current' => true]);
            }
            return $resultRedirect->setPath('*/*/');
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {

            $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the page.'));
        }

        return $resultRedirect->setPath('*/*/');
    }
}
