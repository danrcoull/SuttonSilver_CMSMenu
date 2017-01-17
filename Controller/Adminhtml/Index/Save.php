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
use Magento\Store\Model\StoreManagerInterface;

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
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    private $storeManager;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->layoutFactory = $layoutFactory;
        $this->storeManager = $storeManager;
    }

    protected function getParentItem($parentId, $storeId)
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
        $parentId = isset($menuItemData['parent']) ? $menuItemData['parent'] : null;
        if ($menuItemData) {
            $menuItem->addData($menuItemData);
            if ($isNewMenuItem) {
                $parentItem = $this->getParentItem($parentId, $storeId);
                $menuItem->setPath($parentItem->getPath());
                $menuItem->setParentId($parentItem->getId());
            }
        }


        try {
            $menuItem->save();
            $this->messageManager->addSuccessMessage(__('You saved the Menu Item.'));
        } catch (\Magento\Framework\Exception\AlreadyExistsException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
            $this->_getSession()->setMenuItemData($menuItemData);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
            $this->_getSession()->setMenuItemData($menuItemData);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Something went wrong while saving the menu item.'));
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
            $this->_getSession()->setMenuItemData($menuItemData);
        }

        $hasError = (bool)$this->messageManager->getMessages()->getCountByType(
            \Magento\Framework\Message\MessageInterface::TYPE_ERROR
        );

        if ($this->getRequest()->getPost('return_session_messages_only')) {
            $menuItem->load($menuItem->getId());
            // to obtain truncated category name
            /** @var $block \Magento\Framework\View\Element\Messages */
            $block = $this->layoutFactory->create()->getMessagesBlock();
            $block->setMessages($this->messageManager->getMessages(true));

            /** @var \Magento\Framework\Controller\Result\Json $resultJson */
            $resultJson = $this->resultJsonFactory->create();
            return $resultJson->setData(
                [
                    'messages' => $block->getGroupedHtml(),
                    'error' => $hasError,
                    'menuitem' => $menuItem->toArray(),
                ]
            );
        }

        $redirectParams = $this->getRedirectParams($isNewMenuItem, $hasError, $menuItem->getId(), $parentId, $storeId);

        return $resultRedirect->setPath(
            $redirectParams['path'],
            $redirectParams['params']
        );
    }

    protected function getRedirectParams($isNewCategory, $hasError, $menuId, $parentId, $storeId)
    {
        $params = ['_current' => true];
        if ($storeId) {
            $params['store'] = $storeId;
        }
        if ($isNewCategory && $hasError) {
            $path = 'cmsmenu_menu/*/new';
            $params['parent'] = $parentId;
        } else {
            $path = 'cmsmenu_menu/*/edit';
            $params['id'] = $menuId;

        }
        return ['path' => $path, 'params' => $params];
    }
}
