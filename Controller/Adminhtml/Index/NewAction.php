<?php
/**
 * @author Daniel Coull <d.coull@suttonsilver.co.uk>
 */
namespace SuttonSilver\CMSMenu\Controller\Adminhtml\Index;

class NewAction extends \SuttonSilver\CMSMenu\Controller\Adminhtml\MenuItems
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'SuttonSilver_CMSMenu::menu';

    /**
     * @var \Magento\Backend\Model\View\Result\Forward
     */
    protected $resultForwardFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
    ) {
        $this->resultForwardFactory = $resultForwardFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $parentId = (int)$this->getRequest()->getParam('parent');
        $menu = $this->_initMenuItem(true);
        if (!$menu || !$parentId || $menu->getId()) {
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('cmsmenu_menu/*/', ['_current' => true, 'id' => null]);
        }


        $menuItemData = $this->_getSession()->getMenuItemData(true);
        if (is_array($menuItemData)) {
            $menu->addData($menuItemData);
        }



        $resultPageFactory = $this->_objectManager->get('Magento\Framework\View\Result\PageFactory');
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $resultPageFactory->create();

        if ($this->getRequest()->getQuery('isAjax')) {
            return $this->ajaxRequestResponse($menu, $resultPage);
        }

        return $resultPage;
    }
}
