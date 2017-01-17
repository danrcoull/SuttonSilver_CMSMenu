<?php
namespace SuttonSilver\CMSMenu\Model\ResourceModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class MenuItems extends AbstractDb
{


    protected function _construct()
    {
        $this->_init('cms_menuitems', 'suttonsilver_cmsmenu_menuitems_id');
    }

    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        parent::_beforeSave($object);


        if ($object->isObjectNew()) {
            //set title as original title
            if(!$object->getTitle())
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
            if (!$object->getId() || $object->getInitialSetupFlag() != true)
            {
                $object->setPath($object->getPath() . '/');
            }

        }



        return $this;
    }

    public function changeParent(
        \SuttonSilver\CMSMenu\Model\MenuItems $menuItem,
        \SuttonSilver\CMSMenu\Model\MenuItems $newParent,
        $afterMenuItemId = null
    ) {
        $table = $this->getTable('cms_menuitems');
        $connection = $this->getConnection();
        $levelFiled = $connection->quoteIdentifier('level');
        $pathField = $connection->quoteIdentifier('path');


        $position = $this->_processPositions($menuItem, $newParent, $afterMenuItemId);

        $newPath = sprintf('%s/%s', $newParent->getPath(), $menuItem->getId());
        $newLevel = $newParent->getLevel() + 1;
        $levelDisposition = $newLevel - $menuItem->getLevel();

        /**
         * Update children nodes path
         */
        $connection->update(
            $table,
            [
                'path' => new \Zend_Db_Expr(
                    'REPLACE(' . $pathField . ',' . $connection->quote(
                        $menuItem->getPath() . '/'
                    ) . ', ' . $connection->quote(
                        $newPath . '/'
                    ) . ')'
                ),
                'level' => new \Zend_Db_Expr($levelFiled . ' + ' . $levelDisposition)
            ],
            [$pathField . ' LIKE ?' => $menuItem->getPath() . '/%']
        );
        /**
         * Update moved category data
         */
        $data = [
            'path' => $newPath,
            'level' => $newLevel,
            'position' => $position,
            'parent_id' => $newParent->getId(),
        ];
        $connection->update($table, $data, ['suttonsilver_cmsmenu_menuitems_id = ?' => $menuItem->getId()]);

        // Update category object to new data
        $menuItem->addData($data);
        $menuItem->unsetData('path_ids');

        return $this;
    }

    protected function _processPositions($menuItem, $newParent, $afterCategoryId)
    {
        $table = $this->getTable('cms_menuitems');
        $connection = $this->getConnection();
        $positionField = $connection->quoteIdentifier('position');

        $bind = ['position' => new \Zend_Db_Expr($positionField . ' - 1')];
        $where = [
            'parent_id = ?' => $menuItem->getParentId(),
            $positionField . ' > ?' => $menuItem->getPosition(),
        ];
        $connection->update($table, $bind, $where);

        /**
         * Prepare position value
         */
        if ($afterCategoryId) {
            $select = $connection->select()->from($table, 'position')->where('suttonsilver_cmsmenu_menuitems_id = :suttonsilver_cmsmenu_menuitems_id');
            $position = $connection->fetchOne($select, ['suttonsilver_cmsmenu_menuitems_id' => $afterCategoryId]);
            $position += 1;
        } else {
            $position = 1;
        }

        $bind = ['position' => new \Zend_Db_Expr($positionField . ' + 1')];
        $where = ['parent_id = ?' => $newParent->getId(), $positionField . ' >= ?' => $position];
        $connection->update($table, $bind, $where);

        return $position;
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
        parent::_afterSave($object);

        if (substr($object->getPath(), -1) == '/') {
            $object->setPath($object->getPath() . $object->getId());
            if ($object->getId()) {
                $this->getConnection()->update(
                    $this->getTable('cms_menuitems'),
                    ['path' => $object->getPath()],
                    ['suttonsilver_cmsmenu_menuitems_id = ?' => $object->getId()]
                );
                $object->unsetData('path_ids');
            }
        }

        return $this;
    }


}
