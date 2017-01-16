<?php
namespace SuttonSilver\CMSMenu\Model\Attribute\Source;

use \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use \Magento\Framework\Module\Manager as ModuleManager;

class Slugs implements \Magento\Framework\Data\OptionSourceInterface
{

    protected $moduleManager;

    public function __construct(
        ModuleManager $moduleManager
    ) {

        $this->moduleManager = $moduleManager;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $pages = $objectManager->get('\Magento\Cms\Model\ResourceModel\Page\CollectionFactory')->create();
        // add Filter if you want
        $pages->addFieldToFilter('is_active', \Magento\Cms\Model\Page::STATUS_ENABLED);
        $pages->setOrder('title','asc');

        $array = [
            [
                'value' => '',
                'label' => __('-- Please Select --'),
            ],
            [
                'value' => 'contact',
                'label' => __('Contact Us'),
            ]
        ];

        foreach ($pages as $page) {
            $array[] = [
                'value' => $page->getIdentifier(),
                'label' => __($page->getTitle()),
            ];
        }


        

        return $array;
    }
}
