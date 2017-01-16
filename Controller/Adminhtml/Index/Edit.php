<?php
/**
 * @author Daniel Coull <d.coull@suttonsilver.co.uk>
 */
namespace SuttonSilver\CMSMenu\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;

class Edit extends \SuttonSilver\CMSMenu\Controller\Adminhtml\MenuItems
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'SuttonSilver_CMSMenu::menu';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Registry $registry
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('SuttonSilver_CMSMenu::menu')
            ->addBreadcrumb(__('CMS'), __('CMS'))
            ->addBreadcrumb(__('Manage Menu Item'), __('Manage Menu Items'));
        return $resultPage;
    }

    /**
     * Edit CMS page
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $storeId = (int)$this->getRequest()->getParam('store');
        $store = $this->getStoreManager()->getStore($storeId);
        $this->getStoreManager()->setCurrentStore($store->getCode());

        // 1. Get ID and create model
        $menuId = (int)$this->getRequest()->getParam('id');
        if(!$menuId)
        {
            $this->getRequest()->setParam('id', 1);
            $menuId = $this->getRequest()->getParam('id');
        }

        $menu = $this->_initMenuItem(true);
        if (!$menu || $menuId != $menu->getId() || !$menu->getId()) {
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('cmsmenu_menu/*/*', ['_current' => true, 'id' => null]);
        }

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();

        if ($this->getRequest()->getQuery('isAjax')) {
            return $this->ajaxRequestResponse($menu, $resultPage);
        }

        // 5. Build edit form
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $menuId ? __('Edit Menu Item') : __('New Menu Item'),
            $menuId ? __('Edit Menu Item') : __('New Menu Item')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Menu Item'));
        $resultPage->getConfig()->getTitle()
            ->prepend($menu->getId() ? $menu->getTitle() : __('New Menu Item'));

        return $resultPage;
    }

    private function getStoreManager()
    {
        if (null === $this->storeManager) {
            $this->storeManager = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Magento\Store\Model\StoreManagerInterface');
        }
        return $this->storeManager;
    }
}
