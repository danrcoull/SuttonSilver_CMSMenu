<?php
/**
 * @author Daniel Coull <d.coull@suttonsilver.co.uk>
 */
namespace SuttonSilver\CMSMenu\Ui\Component\Listing\Column\Suttonsilvercmsmenu;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Cms\Block\Adminhtml\Page\Grid\Renderer\Action\UrlBuilder;
use Magento\Framework\UrlInterface;

class PageActions extends \Magento\Ui\Component\Listing\Columns\Column
{

    const CMS_URL_PATH_EDIT = 'cmsmenu_menu/index/edit';
    const CMS_URL_PATH_DELETE = 'cmsmenu_menu/index/delete';

    /** @var UrlBuilder */
    protected $actionUrlBuilder;

    /** @var UrlInterface */
    protected $urlBuilder;

    /**
     * @var string
     */
    private $editUrl;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlBuilder $actionUrlBuilder,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = [],
        $editUrl = self::CMS_URL_PATH_EDIT
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->actionUrlBuilder = $actionUrlBuilder;
        $this->editUrl = $editUrl;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                if (isset($item['suttonsilver_cmsmenu_menuitems_id'])) {
                    $item[$name]['edit'] = [
                        'href' => $this->urlBuilder->getUrl($this->editUrl, ['suttonsilver_cmsmenu_menuitems_id' => $item['suttonsilver_cmsmenu_menuitems_id']]),
                        'label' => __('Edit')
                    ];
                    $item[$name]['delete'] = [
                        'href' => $this->urlBuilder->getUrl(self::CMS_URL_PATH_DELETE, ['suttonsilver_cmsmenu_menuitems_id' => $item['suttonsilver_cmsmenu_menuitems_id']]),
                        'label' => __('Delete'),
                        'confirm' => [
                            'title' => __('Delete ${ $.$data.title }'),
                            'message' => __('Are you sure you wan\'t to delete  ${ $.$data.title } record?')
                        ]
                    ];
                }

            }
        }

        return $dataSource;
    }    
    
}
