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
        $groupArray = [
            'label' => 'Magento Pages',
            'value' => []
        ];

        foreach ($pages as $page) {
            $groupArray['value'] = [
                'value' => $page->getId(),
                'label' => __($page->getTitle()),
            ];
        }

        $array[] = $groupArray;



        return $array;
    }
}
