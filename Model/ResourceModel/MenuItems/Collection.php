<?php
namespace SuttonSilver\CMSMenu\Model\ResourceModel\MenuItems;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    protected $_idFieldName = 'suttonsilver_cmsmenu_menuitems_id';

    protected function _construct()
    {
        $this->_init('SuttonSilver\CMSMenu\Model\MenuItems','SuttonSilver\CMSMenu\Model\ResourceModel\MenuItems');
    }

    public function addPathFilter($regexp)
    {
        $this->addFieldToFilter('path', ['regexp' => $regexp]);
        return $this;
    }

    public function addRootLevelFilter()
    {
        $this->addFieldToFilter('path', ['neq' => '0']);
        $this->addLevelFilter(1);
        return $this;
    }

    public function addTitleToResult()
    {
        $this->addFieldToSelect('title');
        return $this;
    }

    public function addPathsFilter($paths)
    {
        if (!is_array($paths)) {
            $paths = [$paths];
        }
        $connection = $this->getResource()->getConnection();
        $cond = [];
        foreach ($paths as $path) {
            $cond[] = $connection->quoteInto('e.path LIKE ?', "{$path}%");
        }
        if ($cond) {
            $this->getSelect()->where(join(' OR ', $cond));
        }
        return $this;
    }

    public function addLevelFilter($level)
    {
        $this->addFieldToFilter('level', ['lteq' => $level]);
        return $this;
    }

    public function addOrderField($field)
    {
        $this->setOrder($field, self::SORT_ORDER_ASC);
        return $this;
    }

    public function addIdFilter($menuItemIds)
    {
        if (is_array($menuItemIds)) {
            if (empty($menuItemIds)) {
                $condition = '';
            } else {
                $condition = ['in' => $menuItemIds];
            }
        } elseif (is_numeric($menuItemIds)) {
            $condition = $menuItemIds;
        } elseif (is_string($menuItemIds)) {
            $ids = explode(',', $menuItemIds);
            if (empty($ids)) {
                $condition = $menuItemIds;
            } else {
                $condition = ['in' => $ids];
            }
        }
        $this->addFieldToFilter('suttonsilver_cmsmenu_menuitems_id', $condition);
        return $this;
    }



}
