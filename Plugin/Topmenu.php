<?php
namespace SuttonSilver\CMSMenu\Plugin;

class Topmenu
{
    function buildTree( $ar, $pid = null ) {
        $op = array();
        foreach( $ar as $item ) {
            if( $item['parent'] == $pid ) {
                $op[$item['suttonsilver_cmsmenu_menuitems_id']] = array(
                    'title' => $item['title'],
                    'slug'  => $item['slug'],
                    'parent' => $item['parent']
                );
                // using recursion
                $children =  $this->buildTree( $ar, $item['suttonsilver_cmsmenu_menuitems_id'] );
                if( $children ) {
                    $op[$item['suttonsilver_cmsmenu_menuitems_id']]['children'] = $children;
                }
            }
        }
        return $op;
    }

    public function printTree($tree, $level=0) {
        $output = '';
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
        $base = $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK);
        if(!is_null($tree) && count($tree) > 0) {
            $liClass = isset($tree['children']) ? 'nav-1 first last level-top parent ' : 'nav-1 level-top ui-menu-item ';
            $title = $tree['title'];
            $slug = $tree['slug'];
            $output .= "<li class='level$level $liClass'>"."<a href='$base$slug' title='$title'>".$tree['title']."</a>";

            if (isset($tree['children']) ) {
                $output .= "<ul class='level$level submenu'>";
                foreach ($tree['children'] as $child) {
                    $output .= $this->printTree($child, $level+1);
                }
                $output .= "</ul>";
            }
            $output .= "</li>";
        }
        return $output;
    }



    public function afterGetHtml(\Magento\Theme\Block\Html\Topmenu $topmenu, $html)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        //create the collection and add store filter


        $pages = $objectManager->get('\SuttonSilver\CMSMenu\Model\ResourceModel\MenuItems\CollectionFactory')->create();
        $pages->addFieldToFilter('is_active', array('eq'=>1));
        $pages->setOrder('sort_order','ASC');
        $tree = $this->buildTree($pages->getData());

        $before = "<li class='level0 nav-1 level-top ui-menu-item'><a href='/' title='Home'>Home</a></li>";
        $after = '';
        foreach($tree as $key => $node)
        {
            $pages = $objectManager->get('\SuttonSilver\CMSMenu\Model\MenuItems');
            $_pages = $pages->load($key);
            if($_pages->getData('before_categories')):
               $before .=$this->printTree($node);
            else :
                $after .=$this->printTree($node);
            endif;

        }

        $htmlNew = $before.$html.$after;
        //empty arrays php 5.6+

        return $htmlNew;
    }
}
