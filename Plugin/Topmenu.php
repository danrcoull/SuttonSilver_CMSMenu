<?php
namespace SuttonSilver\CMSMenu\Plugin;
use Magento\Framework\Data\Tree\NodeFactory;
use Magento\Framework\Data\Collection;
use Magento\Framework\Data\Tree\Node;
class Topmenu
{

    private $collectionFactory;
    private $menuItem;
    private $storeManager;
    protected $nodeFactory;

    public function __construct(
        \SuttonSilver\CMSMenu\Model\ResourceModel\MenuItems\CollectionFactory $menuItemsCollectionFactory,
        \SuttonSilver\CMSMenu\Model\MenuItems $menuItems,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        NodeFactory $nodeFactory
    ) {
        $this->collectionFactory = $menuItemsCollectionFactory;
        $this->menuItem = $menuItems;
        $this->storeManager = $storeManager;
        $this->nodeFactory = $nodeFactory;
    }

    public function beforeGetHtml(
        \Magento\Theme\Block\Html\Topmenu $subject,
        $outermostClass = '',
        $childrenWrapClass = '',
        $limit = 0
    ) {

        $rootId = 1;
        $storeId = $this->storeManager->getStore()->getId();

        $collection = $this->getMenuItemTree($storeId, $rootId, 1);
        $mapping = [$rootId => $subject->getMenu()];

        foreach ($collection as $menuItem) {

            if (!isset($mapping[$menuItem->getParentId()])) {
                continue;
            }
            $parentMenuItem = $mapping[$menuItem->getParentId()];

            $menuItemNode = new Node(
                $this->getMenuItemAsArray($menuItem, $parentMenuItem),
                'id',
                $parentMenuItem->getTree(),
                $parentMenuItem
            );

            $parentMenuItem->addChild($menuItemNode);

            $mapping[$menuItem->getId()] = $menuItemNode; //add node in stack
        }
    }


    protected function getMenuItemTree($storeId, $rootId, $before)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToSelect('*');
        $collection->addFieldToFilter('path', ['like' => $rootId.'/%']); //load only from store root
        $collection->addFieldToFilter('is_active', 1);
        //$collection->addFieldToFilter('before_categories', $before);
        $collection->addOrder('level', 'ASC');
        $collection->addOrder('position', 'ASC');
        $collection->addOrder('parent_id', 'ASC');
        return $collection;
    }

    private function getMenuItemAsArray($menuItem, $currentMenuItem)
    {
        return [
            'name' => $menuItem->getTitle(),
            'id' => 'menu-node-' . $menuItem->getId(),
            'url' => $menuItem->getSlug(),
            'has_active' => in_array((string)$menuItem->getId(), explode('/', $menuItem->getPath()), true),
            'is_active' => false
        ];
    }


    public function afterGetHtml(\Magento\Theme\Block\Html\Topmenu $subject, $html)
    {

        $search = "<li class='level0 search-button'><a href='#' class='search-link'>
                    <i class=\"fa fa-search\" aria-hidden=\"true\"></i>
                   </a></li>";
        return $html.$search;
    }
}
