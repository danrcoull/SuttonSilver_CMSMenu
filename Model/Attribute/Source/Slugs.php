<?php
namespace SuttonSilver\CMSMenu\Model\Attribute\Source;

use \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use \Magento\Framework\Module\Manager as ModuleManager;

class Slugs implements \Magento\Framework\Data\OptionSourceInterface
{

    protected $moduleManager;

    private $options = [];

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

        array_push($this->options,['value'=>'','label'=>'-- Please Select--']);

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $pages = $objectManager->get('\Magento\Cms\Model\ResourceModel\Page\CollectionFactory')->create();
        // add Filter if you want
        $pages->addFieldToFilter('is_active', \Magento\Cms\Model\Page::STATUS_ENABLED);
        $pages->setOrder('title','asc');

        $groupArray = [
            'label' => 'Magento Pages',
            'value' => []
        ];

        $array1 =
            [
                'value' => '/',
                'label' => 'Home',
            ];

        $array2=
            [
                'value' => 'contact',
                'label' => 'Contact Us',
            ];

        array_push($groupArray['value'],$array1);
        array_push($groupArray['value'],$array2);

        foreach ($pages as $page) {
            $pageArray = [
                'value' => $page->getIdentifier(),
                'label' => $page->getTitle(),
            ];

            array_push($groupArray['value'],$pageArray);
        }

        array_push($this->options,$groupArray);

        $this->getWordpress();
        $this->getArchives();

        return $this->options;
    }

    public function getWordpress() {

        if($this->moduleManager->isEnabled('FishPig_WordPress')) {

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $wordpress = $objectManager->create('\FishPig\WordPress\Model\App');
            $postTypes = $wordpress->getPostTypes();


            foreach ($postTypes as $type) {
                $typeKey = $type->getPostType();
                $posts = $objectManager->get('\FishPig\WordPress\Model\ResourceModel\Post\CollectionFactory')->create();
                $posts->addStatusFilter('publish');
                $posts->addPostTypeFilter($typeKey);
                if($posts->count() > 0) {
                    $typeName = $type->getName();
                    $groupArray = [
                        'label' => 'WordPress '.$typeName,
                        'value' => []
                    ];
                    foreach ($posts as $post) {
                        $postArray = [
                            'value' => $post->getUrl(),
                            'label' => $post->getName(),
                        ];

                        array_push($groupArray['value'],$postArray);
                    }
                    array_push($this->options,$groupArray);
                }
            }
        }

        return $this;
    }

    public function getArchives() {
        if($this->moduleManager->isEnabled('FishPig_WordPress')) {

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $wordpress = $objectManager->create('\FishPig\WordPress\Model\App');
            $postTypes = $wordpress->getPostTypes();
            $groupArray = [
                'label' => 'WordPress Archives (Lists)',
                'value' => []
            ];
            foreach($postTypes as $post)
            {
                $postArray = [
                    'value' => $post->getUrl(),
                    'label' => $post->getName(),
                ];

                array_push($groupArray['value'],$postArray);
            }
            array_push($this->options,$groupArray);
        }
        return $this;
    }
}
