<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SuttonSilver\CMSMenu\Block\Adminhtml\MenuItems;

use SuttonSilver\CMSMenu\Model\ResourceModel\MenuItems\Collection;
use Magento\Framework\Data\Tree\Node;
use Magento\Store\Model\Store;

class Tree extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'tree.phtml';

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_backendSession;

    /**
     * @var \Magento\Framework\DB\Helper
     */
    protected $_resourceHelper;

    protected $_menuItemsTree;

    protected $_menuItemsFactory;

    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \SuttonSilver\CMSMenu\Model\ResourceModel\MenuItems\Tree $menuItemsTree,
        \Magento\Framework\Registry $registry,
        \SuttonSilver\CMSMenu\Model\MenuItemsFactory $menuItemsFactory,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\DB\Helper $resourceHelper,
        \Magento\Backend\Model\Auth\Session $backendSession,
        array $data = []
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        $this->_resourceHelper = $resourceHelper;
        $this->_backendSession = $backendSession;
        $this->_menuItemsTree = $menuItemsTree;
        $this->_menuItemsFactory = $menuItemsFactory;
        $this->_coreRegistry = $registry;

        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setUseAjax(0);
    }

    protected function _prepareLayout()
    {
        $newUrl = $this->getUrl("*/*/new", ['_current' => false, 'id' => null, '_query' => false]);

        $this->addChild(
            'add_sub_button',
            'Magento\Backend\Block\Widget\Button',
            [
                'label' => __('Add Subcategory'),
                'onclick' => "addNew('" . $newUrl . "', false)",
                'class' => 'add',
                'id' => 'add_subcategory_button'
            ]
        );

        return parent::_prepareLayout();
    }

    public function getAddSubButtonHtml()
    {
        return $this->getChildHtml('add_sub_button');
    }

    /**
     * @return string
     */
    public function getExpandButtonHtml()
    {
        return $this->getChildHtml('expand_button');
    }

    /**
     * @return string
     */
    public function getCollapseButtonHtml()
    {
        return $this->getChildHtml('collapse_button');
    }

    /**
     * @return string
     */
    public function getStoreSwitcherHtml()
    {
        return $this->getChildHtml('store_switcher');
    }

    /**
     * @return string
     */
    public function getSwitchTreeUrl()
    {
        return $this->getUrl(
            'cmsmenu_menu/*/tree',
            ['_current' => true, 'store' => null, '_query' => false, 'id' => null, 'parent' => null]
        );
    }

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsWasExpanded()
    {
        return $this->_backendSession->getIsTreeWasExpanded();
    }

    /**
     * @return string
     */
    public function getMoveUrl()
    {
        return $this->getUrl('cmsmenu_menu/*/move', ['store' => $this->getRequest()->getParam('store')]);
    }
    public function getMenuItem(){
        return $this->_coreRegistry->registry('menuitem');
    }

    public function getMenuItemId()
    {
        if ($this->getMenuItem()) {
            return $this->getMenuItem()->getId();
        }
        return 1;
    }

    public function getStore()
    {
        $storeId = (int)$this->getRequest()->getParam('store');
        return $this->_storeManager->getStore($storeId);
    }

    public function getRoot($parentNodeCategory = null, $recursionLevel = 3)
    {
        if ($parentNodeCategory !== null && $parentNodeCategory->getId()) {
            return $this->getNode($parentNodeCategory, $recursionLevel);
        }
        $root = $this->_coreRegistry->registry('root');
        if ($root === null) {
            $storeId = (int)$this->getRequest()->getParam('store');

            $rootId = \SuttonSilver\CMSMenu\Model\MenuItems::TREE_ROOT_ID;

            $tree = $this->_menuItemsTree->load(null, $recursionLevel);


            if ($this->getMenuItem()) {
                $tree->loadEnsuredNodes($this->getMenuItem(), $tree->getNodeById($rootId));
            }

            $tree->addCollectionData($this->getMenuItemCollection());

            $root = $tree->getNodeById($rootId);

            if ($root && $rootId != \SuttonSilver\CMSMenu\Model\MenuItems::TREE_ROOT_ID) {
                $root->setIsVisible(true);
            } elseif ($root && $root->getId() == \SuttonSilver\CMSMenu\Model\MenuItems::TREE_ROOT_ID) {
                $root->setTitle(__('Main Menu'));
            }

            $this->_coreRegistry->register('root', $root);
        }


        return $root;
    }

    public function getMenuItemCollection()
    {
        $storeId = 0;
        $collection = $this->getData('menuitem_collection');
        if ($collection === null) {
            $collection = $this->_menuItemsFactory->create()->getCollection();

            $collection->addFieldToSelect(
                '*'
            );

            $this->setData('menuitem_collection', $collection);
        }
        return $collection;
    }

    public function getTree($parenNodeCategory = null)
    {
        $rootArray = $this->_getNodeJson($this->getRoot($parenNodeCategory));
        $tree = isset($rootArray['children']) ? $rootArray['children'] : [];
        return $tree;
    }

    public function getTreeJson($parenNodeCategory = null)
    {
        $rootArray = $this->_getNodeJson($this->getRoot($parenNodeCategory));
        $json = $this->_jsonEncoder->encode(isset($rootArray['children']) ? $rootArray['children'] : []);
        return $json;
    }

    protected function _getNodeJson($node, $level = 0)
    {
        // create a node from data array
        if (is_array($node)) {
            $node = new Node($node, 'suttonsilver_cmsmenu_menuitems_id', new \Magento\Framework\Data\Tree());
        }

        $item = [];
        $item['text'] = $this->escapeHtml($node->getTitle());

        $rootForStores = in_array($node->getData('suttonsilver_cmsmenu_menuitems_id'), $this->getRootIds());

        $item['id'] = $node->getId();
        $item['store'] = (int)$this->getStore()->getId();
        $item['path'] = $node->getData('path');

        $item['cls'] = 'folder ' . ($node->getIsActive() ? 'active-category' : 'no-active-category');
        //$item['allowDrop'] = ($level<3) ? true : false;
        $item['allowDrop'] = true;
        // disallow drag if it's first level and category is root of a store
        $item['allowDrag'] = true;

        $item['children'] = [];

        $isParent = $this->_isParentSelectedMenuItem($node);

        if ($node->hasChildren()) {
            $item['children'] = [];
            if (!($this->getUseAjax() && $node->getLevel() > 1 && !$isParent)) {
                foreach ($node->getChildren() as $child) {
                    $item['children'][] = $this->_getNodeJson($child, $level + 1);
                }
            }
        }

        if ($isParent || $node->getLevel() < 2) {
            $item['expanded'] = true;
        }


        return $item;
    }

    protected function _isParentSelectedMenuItem($node)
    {
        if ($node && $this->getMenuItem()) {
            $pathIds = $this->getMenuItem()->getPathIds();
            if (in_array($node->getId(), $pathIds)) {
                return true;
            }
        }

        return false;
    }

    public function getRootIds()
    {
        $ids = $this->getData('root_ids');
        if ($ids === null) {
            $ids = [1];
            $this->setData('root_ids', $ids);
        }
        return $ids;
    }

    public function isClearEdit()
    {
        return (bool)$this->getRequest()->getParam('clear');
    }

    public function getEditUrl()
    {
        return $this->getUrl(
            'cmsmenu_menu/*/edit',
            ['store' => null, '_query' => false, 'id' => null, 'parent' => null]
        );
    }
}
