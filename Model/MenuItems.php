<?php
namespace SuttonSilver\CMSMenu\Model;

use SuttonSilver\CMSMenu\Model\ResourceModel\MenuItems as Item;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;

class MenuItems extends AbstractModel implements \SuttonSilver\CMSMenu\Model\MenuItemsInterface, IdentityInterface
{
    const CACHE_TAG = 'suttonsilver_cmsmenu_menuitems';

    const ROOT_MENU_ITEM_ID = 1;
    const TREE_ROOT_ID = 1;

    protected $interfaceAttributes = [
        'suttonsilver_cmsmenu_menuitems_id',
        self::SLUG,
        self::TITLE,
        self::PARENT,
        self::PATH,
        self::LEVEL,
        self::CREATION_TIME,
        self::UPDATE_TIME,
        self::SORT_ORDER,
        self::POSITION,
        self::IS_ACTIVE
    ];

    protected $_storeCollectionFactory;
    protected $_storeManager;

    protected $_eventPrefix = 'suttonsilver_cmsmenu';

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Store\Model\ResourceModel\Store\CollectionFactory $storeCollectionFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_storeCollectionFactory = $storeCollectionFactory;
        $this->_storeManager = $storeManager;
        $this->_idFieldName = 'suttonsilver_cmsmenu_menuitems_id';
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    protected function _construct()
    {
        $this->_init('SuttonSilver\CMSMenu\Model\ResourceModel\MenuItems');
    }

    public function getStores()
    {
        return $this->hasData('stores') ? $this->getData('stores') : (array)$this->getData('store_id');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getStoreId()
    {
        if ($this->hasData('store_id')) {
            return (int)$this->_getData('store_id');
        }
        return (int)$this->_storeManager->getStore()->getId();
    }

    public function setStoreId($storeId)
    {
        if (!is_numeric($storeId)) {
            $storeId = $this->_storeManager->getStore($storeId)->getId();
        }
        $this->setData('store_id', $storeId);
        return $this;
    }

    public function getParentId()
    {
        $parentId = $this->getData(self::PARENT);
        if (isset($parentId)) {
            return $parentId;
        }
        $parentIds = $this->getParentIds();
        return intval(array_pop($parentIds));
    }

    public function getParentIds()
    {
        return array_diff($this->getPathIds(), [$this->getId()]);
    }

    public function getPathInStore()
    {
        $result = [];
        $path = array_reverse($this->getPathIds());
        foreach ($path as $itemId) {
            if ($itemId == self::ROOT_MENU_ITEM_ID) {
                break;
            }
            $result[] = $itemId;
        }
        return implode(',', $result);
    }

    public function checkId($id)
    {
        return $this->_getResource()->checkId($id);
    }

    public function getPathIds()
    {
        $ids = $this->getData('path_ids');
        if ($ids === null) {
            $ids = explode('/', $this->getPath());
            $this->setData('path_ids', $ids);
        }
        return $ids;
    }

    public function getLevel()
    {
        if (!$this->hasLevel()) {
            return count(explode('/', $this->getPath())) - 1;
        }
        return $this->getData(self::LEVEL);
    }

    public function verifyIds(array $ids)
    {
        return $this->getResource()->verifyIds($ids);
    }

    public function beforeDelete()
    {
        if ($this->getId() == 1) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Can\'t delete root category.'));
        }
        return parent::beforeDelete();
    }



    public function __toArray()
    {
        $data = $this->_data;
        $hasToArray = function ($model) {
            return is_object($model) && method_exists($model, '__toArray') && is_callable([$model, '__toArray']);
        };
        foreach ($data as $key => $value) {
            if ($hasToArray($value)) {
                $data[$key] = $value->__toArray();
            } elseif (is_array($value)) {
                foreach ($value as $nestedKey => $nestedValue) {
                    if ($hasToArray($nestedValue)) {
                        $value[$nestedKey] = $nestedValue->__toArray();
                    }
                }
                $data[$key] = $value;
            }
        }
        return $data;
    }


    public function getSlug()
    {
        return $this->getData(self::SLUG);
    }

    public function setSlug($slug)
    {
        $this->setData(self::SLUG, $slug);
        return $this;
    }

    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    public function setTitle($title)
    {
        $this->setData(self::TITLE, $title);
        return $this;
    }

    public function getPath()
    {
        return $this->getData(self::PATH);
    }

    public function setPath($path)
    {
        $this->setData(self::PATH, $path);
        return $this;
    }

    public function setLevel($level)
    {
        $this->setData(self::LEVEL, $level);
        return $this;
    }

    public function getCreatedAt()
    {
       return  $this->getData(self::CREATION_TIME);
    }

    public function setCreatedAt($creationTime)
    {
        $this->setData(self::CREATION_TIME, $creationTime);
        return $this;
    }

    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATE_TIME);
    }

    public function setUpdatedAt($updateTime)
    {
        $this->setData(self::UPDATE_TIME, $updateTime);
        return $this;
    }

    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER);
    }

    public function setSortOrder($sortOrder)
    {
        $this->setData(self::SORT_ORDER, $sortOrder);
        return $this;
    }

    public function getPosition()
    {
       return $this->getData(self::POSITION);
    }

    public function setPosition($position)
    {
        $this->setData(self::POSITION, $position);
        return $this;
    }

    public function getIsActive()
    {
       return $this->getData(self::IS_ACTIVE);
    }

    public function setIsActive($isActive)
    {
        $this->setData(self::IS_ACTIVE, $isActive);
        return $this;
    }

    public function setParentId($parent)
    {
        $this->setData(self::PARENT, $parent);
        return $this;
    }

    public function move($parentId, $afterItemId)
    {
        /**
         * Validate new parent category id. (category model is used for backward
         * compatibility in event params)
         */
        try {
            $parent = $this->getCollection()
                ->addFieldToFilter('suttonsilver_cmsmenu_menuitems_id', $parentId)
                ->getFirstItem();
            if(!$parent->getId()) {
                throw new NoSuchEntityException('doesnt exist');
            }
        } catch (NoSuchEntityException $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __(
                    'Sorry, but we can\'t find the new parent menu item you selected.'
                ),
                $e
            );
        }

        if (!$this->getId()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Sorry, but we can\'t find the new menu item you selected.')
            );
        } elseif ($parent->getId() == $this->getId()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __(
                    'We can\'t move the menu item because the parent category name matches the child category name.'
                )
            );
        }


        $oldParentId = $this->getParentId();

        $eventParams = [
            $this->_eventObject => $this,
            'parent' => $parent,
            'category_id' => $this->getId(),
            'prev_parent_id' => $oldParentId,
            'parent_id' => $parentId,
        ];

        $this->_getResource()->beginTransaction();
        try {
            $this->_eventManager->dispatch($this->_eventPrefix . '_move_before', $eventParams);
            $this->getResource()->changeParent($this, $parent, $afterItemId);
            $this->_eventManager->dispatch($this->_eventPrefix . '_move_after', $eventParams);
            $this->_getResource()->commit();

        } catch (\Exception $e) {
            $this->_getResource()->rollBack();
            throw $e;
        }
        $this->_eventManager->dispatch('menu_item_move', $eventParams);


        return $this;
    }

}
