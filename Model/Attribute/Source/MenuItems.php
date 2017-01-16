<?php
namespace SuttonSilver\CMSMenu\Model\Attribute\Source;

class MenuItems implements \Magento\Framework\Data\OptionSourceInterface
{


    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $pages = $objectManager->get('\SuttonSilver\CMSMenu\Model\ResourceModel\MenuItems\CollectionFactory')->create();
        // add Filter if you want
        $pages->addFieldToFilter('is_active', array('eq' => 1));
        $array = [
            [
                'value' => '',
                'label' => __('-- Please Select --'),
            ]
        ];

        foreach ($pages as $page) {
            $array[] = [
                'value' => $page->getId(),
                'label' => __($page->getTitle()),
            ];
        }



        return $array;
    }
}
