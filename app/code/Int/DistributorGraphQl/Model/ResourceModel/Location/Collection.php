<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Int\DistributorGraphQl\Model\ResourceModel\Location;

class Collection extends \Amasty\Storelocator\Model\ResourceModel\Location\Collection
{
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Request
     *
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * Quote address model object
     *
     * @var \Magento\Quote\Model\Quote\Address
     */
    protected $_address;

    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\Request
     */
    protected $_httpRequest;

    protected $_allRules;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $scope,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Quote\Model\Quote\Address $address,
        \Magento\Framework\HTTP\PhpEnvironment\Request $httpRequest,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Amasty\Base\Model\Serializer $serializer,
        \Amasty\Geoip\Model\Geolocation $geolocation,
        \Amasty\Storelocator\Model\ConfigProvider $configProvider,
        \Amasty\Storelocator\Api\Validator\LocationProductValidatorInterface $locationProductValidator
    ) {
        $this->_storeManager = $storeManager;
        $this->_request = $request;
        $this->_coreRegistry = $registry;
        $this->_address = $address;
        $this->_scopeConfig = $scope;
        $this->_objectManager = $objectManager;
        $this->_httpRequest = $httpRequest;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager,$storeManager,$request, $registry,$scope, $httpRequest, $productRepository, $categoryRepository, $serializer, $geolocation, $configProvider, $locationProductValidator);
        //parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager,$storeManager,$request, $registry,$scope, $objectManager,$address, $httpRequest,$connection, $resource);
    }

    public function _construct()
    {
        parent::_construct();
        $this->_init('Amasty\Storelocator\Model\Location', 'Amasty\Storelocator\Model\ResourceModel\Location');
    }

    public function applyDefaultFilters()
    {
        $select = $this->getSelect();
        if (!$this->_storeManager->isSingleStoreMode()) {
            $store = $this->_storeManager->getStore(true)->getId();
            $this->getSelect()->where('stores=\',0,\' OR stores LIKE "%,' . $store . ',%"');
        }

        $select->where('main_table.status = 1');

        $lat = (float)$this->_request->getPost('lat');
        $lng = (float)$this->_request->getPost('lng');
        $sort = $this->getConnection()->quote($this->_request->getPost('sort'));
        $radius = (float)$this->_request->getPost('radius');

        $ip = $this->_httpRequest->getClientIp();
        if (($this->_scopeConfig->getValue('amlocator/geoip/use') == 1)
            && ($sort == "'distance'")
            && (!$lat)
        ) {
            $geoIpModel = $this->_objectManager->create('Amasty\Geoip\Model\Geolocation');
            $geodata = $geoIpModel->locate($ip);
            $lat = $geodata->getLatitude();
            $lng = $geodata->getLongitude();
        }

        if ($lat && $lng) {
            $select->columns(
                ['distance' => 'SQRT(POW(69.1 * (main_table.lat - ' . $lat . '), 2) + POW(69.1 * (' . $lng . ' - main_table.lng) * COS(main_table.lat / 57.3), 2))']
            );
            $select->order("distance");
        } else {
            $select->order('main_table.position ASC');
        }

        if ($radius && $lat && $lng) {
            switch ($this->_scopeConfig->getValue('amlocator/locator/distance')) {
                case "km":
                    $radius = $radius / 1.609344;
                    break;
                case "choose":
                    break;
            }
            $select->having('distance < ' . $radius);
        }
    }

    public function applyDefaultFiltersLocation($lat,$lng,$radius=null,$sorts=null)
    {
        $select = $this->getSelect();
        if (!$this->_storeManager->isSingleStoreMode()) {
            $store = $this->_storeManager->getStore(true)->getId();
            $this->getSelect()->where('stores=\',0,\' OR stores LIKE "%,' . $store . ',%"');
        }

        $select->where('main_table.status = 1');

        $lat = (float)$lat;
        $lng = (float)$lng;
        $sort = $this->getConnection()->quote($sorts);
        $radius = (float)$radius;

        $ip = $this->_httpRequest->getClientIp();
        if (($this->_scopeConfig->getValue('amlocator/geoip/use') == 1)
            && ($sort == "'distance'")
            && (!$lat)
        ) {
            $geoIpModel = $this->_objectManager->create('Amasty\Geoip\Model\Geolocation');
            $geodata = $geoIpModel->locate($ip);
            $lat = $geodata->getLatitude();
            $lng = $geodata->getLongitude();
        }

        if ($lat && $lng) {
            $select->columns(
                ['distance' => 'SQRT(POW(69.1 * (main_table.lat - ' . $lat . '), 2) + POW(69.1 * (' . $lng . ' - main_table.lng) * COS(main_table.lat / 57.3), 2))']
            );
            $select->order("distance");
        } else {
            $select->order('main_table.position ASC');
        }

        if ($radius && $lat && $lng) {
            switch ($this->_scopeConfig->getValue('amlocator/locator/distance')) {
                case "km":
                    $radius = $radius / 1.609344;
                    break;
                case "choose":
                    break;
            }
            $select->having('distance < ' . $radius);
        }
    }

    public function load($printQuery = false, $logQuery = false)
    {
        parent::load($printQuery, $logQuery);

        return $this;

    }

    /**
     * Apply filters to locations collection
     *
     * @param array $data
     * @return $this
     */
    public function applyAttributeFilters($data)
    {
        foreach ($data as $attributeId => $value) {
            $attributeId = (int)$attributeId;
            $attributeTableAlias = 'store_attribute_' . $attributeId;
            $this->getSelect()
                ->joinLeft(
                    [$attributeTableAlias => $this->getTable('amasty_amlocator_store_attribute')],
                    "main_table.id = $attributeTableAlias.store_id"
                )
                ->where("($attributeTableAlias.attribute_id IN (?)", $attributeId)
                ->where("FIND_IN_SET((?), $attributeTableAlias.value))", $value);
        }

        return $this;
    }
}
