<?php
namespace SuttonSilver\CMSMenu\Model\ResourceModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class MenuItems extends AbstractDb
{

    protected $_storeId = null;

    protected $_eventManager = null;
    protected $_storeManager = null;

    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        $data = []
    )
    {

        parent::__construct(
            $context,
            $data
        );

        $this->_storeManager = $storeManager;
        $this->_eventManager = $eventManager;
        $this->connectionName  = 'cms_menuitems';
        $this->_idFieldName = 'suttonsilver_cmsmenu_menuitems_id';
    }

    protected function _construct()
    {
        $this->_init('cms_menuitems', 'suttonsilver_cmsmenu_menuitems_id');
    }

    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;
        return $this;
    }

    public function getStoreId()
    {
        if ($this->_storeId === null) {
            return $this->_storeManager->getStore()->getId();
        }
        return $this->_storeId;
    }

    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        parent::_beforeSave($object);

        if ($object->isObjectNew()) {
            //set title as original title
            if($object->getTitle() == null)
            {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $pages = $objectManager->create('\Magento\Cms\Model\Page')->getCollection();
                // add Filter if you want
                $pages->addFieldToFilter('is_active', \Magento\Cms\Model\Page::STATUS_ENABLED);
                $pages->addFieldToFilter('identifier', $object->getSlug());
                $object->setTitle($pages->getFirstItem()->getTitle());
            }

            if ($object->getPosition() === null) {
                $object->setPosition($this->_getMaxPosition($object->getPath()) + 1);
            }
            $path = explode('/', $object->getPath());
            $level = count($path)  - ($object->getId() ? 1 : 0);
            $toUpdateChild = array_diff($path, [$object->getId()]);

            if (!$object->hasPosition()) {
                $object->setPosition($this->_getMaxPosition(implode('/', $toUpdateChild)) + 1);
            }
            if (!$object->hasLevel()) {
                $object->setLevel($level);
            }
            if (!$object->hasParentId() && $level) {
                $object->setParentId($path[$level - 1]);
            }
            if (!$object->getId()) {
                $object->setPath($object->getPath() . '/');
            }

        }

        return $this;
    }

    protected function _getMaxPosition($path)
    {
        $connection = $this->getConnection();
        $positionField = $connection->quoteIdentifier('position');
        $level = count(explode('/', $path));
        $bind = ['c_level' => $level, 'c_path' => $path . '/%'];
        $select = $connection->select()->from(
            $this->getTable('cms_menuitems'),
            'MAX(' . $positionField . ')'
        )->where(
            $connection->quoteIdentifier('path') . ' LIKE :c_path'
        )->where(
            $connection->quoteIdentifier('level') . ' = :c_level'
        );

        $position = $connection->fetchOne($select, $bind);
        if (!$position) {
            $position = 0;
        }
        return $position;
    }

    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if (substr($object->getPath(), -1) == '/') {
            $object->setPath($object->getPath() . $object->getId());
            $this->_savePath($object);
        }
        return parent::_afterSave($object);
    }

    protected function _savePath($object)
    {
        if ($object->getId()) {
            $this->getConnection()->update(
                $this->getTable('cms_menuitems'),
                ['path' => $object->getPath()],
                ['entity_id = ?' => $object->getId()]
            );
            $object->unsetData('path_ids');
        }
        return $this;
    }
}
