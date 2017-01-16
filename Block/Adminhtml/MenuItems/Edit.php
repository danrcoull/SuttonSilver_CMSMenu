<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SuttonSilver\CMSMenu\Block\Adminhtml\MenuItems;

class Edit extends \Magento\Framework\View\Element\Template
{
    /**
     * Return URL for refresh input element 'path' in form
     *
     * @return string
     */
    public function getRefreshPathUrl()
    {
        return $this->getUrl('cmsmenu_menu/*/refreshPath', ['_current' => true]);
    }
}
