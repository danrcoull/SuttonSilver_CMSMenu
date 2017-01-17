<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SuttonSilver\CMSMenu\Controller\Adminhtml\Index;

class Move extends \SuttonSilver\CMSMenu\Controller\Adminhtml\MenuItems
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var \Psr\Log\LoggerInterface $logger
     */
    protected $logger;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory,
     * @param \Psr\Log\LoggerInterface $logger,
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->layoutFactory = $layoutFactory;
        $this->logger = $logger;
    }

    /**
     * Move category action
     *
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        /**
         * New parent category identifier
         */
        $parentNodeId = $this->getRequest()->getPost('pid', false);
        /**
         * Category id after which we have put our category
         */
        $prevNodeId = $this->getRequest()->getPost('aid', false);

        /** @var $block \Magento\Framework\View\Element\Messages */
        $block = $this->layoutFactory->create()->getMessagesBlock();
        $error = false;

        try {
            $menuItem = $this->_initMenuItem();
            if ($menuItem === false) {
                throw new \Exception(__('Menu Item is not available for requested store.'));
            }
            $menuItem->move($parentNodeId, $prevNodeId);
        } catch (\Magento\Framework\Exception\AlreadyExistsException $e) {
            $error = true;
            $this->messageManager->addErrorMessage(__('There was a menu item move error. %1', $e->getMessage()));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $error = true;
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $error = true;
            $this->messageManager->addErrorMessage(__('There was a menu item move error.'));
            $this->logger->critical($e);
        }

        if (!$error) {
            $this->messageManager->addSuccessMessage(__('You moved the Menu Item'));
        }

        $block->setMessages($this->messageManager->getMessages(true));
        $resultJson = $this->resultJsonFactory->create();

        return $resultJson->setData([
            'messages' => $block->getGroupedHtml(),
            'error' => $error
        ]);
    }
}
