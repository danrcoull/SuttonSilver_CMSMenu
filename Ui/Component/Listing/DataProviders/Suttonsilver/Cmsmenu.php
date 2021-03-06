<?php
/**
 * @author Daniel Coull <d.coull@suttonsilver.co.uk>
 */
namespace SuttonSilver\CMSMenu\Ui\Component\Listing\DataProviders\Suttonsilver;

use SuttonSilver\CMSMenu\Model\ResourceModel\MenuItems\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;

class Cmsmenu extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Magento\Cms\Model\ResourceModel\Block\Collection
     */
    protected $collection;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var array
     */
    protected $loadedData;

    protected $request;

    /**
     * Constructor
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $blockCollectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $blockCollectionFactory,
        \Magento\Framework\App\RequestInterface $request,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $blockCollectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->request = $request;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->meta = $this->prepareMeta($this->meta);
    }


    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        foreach ($items as $menuitem) {
            $this->loadedData[$menuitem->getId()] = $menuitem->getData();
        }

        $data = $this->dataPersistor->get('cmsmenu_menu');
        if (!empty($data)) {
            $menuitem = $this->collection->getNewEmptyItem();
            $menuitem->setData($data);
            $this->loadedData[$menuitem->getId()] = $menuitem->getData();
            $this->dataPersistor->clear('cmsmenu_menu');
        }

        return $this->loadedData;
    }

    public function prepareMeta($meta)
    {
        $meta['general']['children']['parent']['arguments']['data']['config']['default'] = (int)$this->request->getParam('parent');
        $meta['general']['children']['store_id']['arguments']['data']['config']['default'] = (int)$this->request->getParam('store');

        return $meta;
    }
}
