<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SuttonSilver\CMSMenu\Model\ResourceModel\MenuItems;

use Magento\Framework\Data\Tree\Dbp;
use SuttonSilver\CMSMenu\Model\MenuItemsInterface;
use Magento\Framework\EntityManager\MetadataPool;
/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Tree extends Dbp
{
    const ID_FIELD = 'id';
    const PATH_FIELD = 'path';
    const ORDER_FIELD = 'order';
    const LEVEL_FIELD = 'level';

    private $_eventManager;
    private $_collectionFactory;
    protected $_collection;
    protected $_inactiveMenuItemIds = null;
    protected $_storeId = null;
    protected $_coreResource;
    protected $_storeManager;
    protected $_cache;
    protected $_menuItems;
    protected $metadataPool;

    public function __construct(
        \SuttonSilver\CMSMenu\Model\ResourceModel\MenuItems $menuItems,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \SuttonSilver\CMSMenu\Model\ResourceModel\MenuItems\CollectionFactory $collectionFactory
    ) {
        $this->_menuItems = $menuItems;
        $this->_cache = $cache;
        $this->_storeManager = $storeManager;
        $this->_coreResource = $resource;
        parent::__construct(
            $resource->getConnection('cms_menuitems'),
            $resource->getTableName('cms_menuitems'),
            [
                Dbp::ID_FIELD => 'suttonsilver_cmsmenu_menuitems_id',
                Dbp::PATH_FIELD => 'path',
                Dbp::ORDER_FIELD => 'position',
                Dbp::LEVEL_FIELD => 'level'
            ]
        );
        $this->_eventManager = $eventManager;
        $this->_collectionFactory = $collectionFactory;
    }

    public function setStoreId($storeId)
    {
        $this->_storeId = (int)$storeId;
        return $this;
    }

    public function getStoreId()
    {
        if ($this->_storeId === null) {
            $this->_storeId = $this->_storeManager->getStore()->getId();
        }
        return $this->_storeId;
    }

    public function addCollectionData(
        $collection = null,
        $sorted = false,
        $exclude = [],
        $toLoad = true,
        $onlyActive = false
    ) {
        if ($collection === null) {
            $collection = $this->getCollection($sorted);
        } else {
            $this->setCollection($collection);
        }

        if (!is_array($exclude)) {
            $exclude = [$exclude];
        }

        $nodeIds = [];
        foreach ($this->getNodes() as $node) {
            if (!in_array($node->getId(), $exclude)) {
                $nodeIds[] = $node->getId();
            }
        }
        $collection->addIdFilter($nodeIds);
        if ($onlyActive) {
            $collection->addFieldToFilter('is_active', 1);
        }

        if ($toLoad) {
            $collection->load();

            foreach ($collection as $menuitem) {
                if ($this->getNodeById($menuitem->getId())) {
                    $this->getNodeById($menuitem->getId())->addData($menuitem->getData());
                }
            }

            foreach ($this->getNodes() as $node) {
                if (!$collection->getItemById($node->getId()) && $node->getParent()) {
                    $this->removeNode($node);
                }
            }
        }

        return $this;
    }

    public function setCollection($collection)
    {
        if ($this->_collection !== null) {
            $this->_clean($this->_collection);
        }
        $this->_collection = $collection;
        return $this;
    }

    protected function _clean($object)
    {
        if (is_array($object)) {
            foreach ($object as $obj) {
                $this->_clean($obj);
            }
        }
        unset($object);
    }

    public function getCollection($sorted = false)
    {
        if ($this->_collection === null) {
            $this->_collection = $this->_getDefaultCollection($sorted);
        }
        return $this->_collection;
    }

    protected function _getDefaultCollection($sorted = false)
    {
        $collection = $this->_collectionFactory->create();
        $collection->addFieldToSelect('*');

        if ($sorted) {
            if (is_string($sorted)) {
                // $sorted is supposed to be attribute name
                $collection->addFieldToSort($sorted);
            } else {
                $collection->addFieldToSort('title');
            }
        }

        return $collection;
    }

    private function getMetadataPool()
    {
        if (null === $this->metadataPool) {
            $this->metadataPool = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Magento\Framework\EntityManager\MetadataPool');
        }
        return $this->metadataPool;
    }
}
