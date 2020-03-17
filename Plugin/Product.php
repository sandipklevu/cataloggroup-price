<?php

namespace Klevu\ProductOverride\Plugin;

use Magento\Store\Model\StoreManagerInterface as StoreManager;
use Magento\Catalog\Model\Product as MagentoProduct;
use Magento\Customer\Model\ResourceModel\Group\Collection as MagentoCustomerGroups;
use Klevu\Search\Model\Product\Product as KlevuProduct;
use Klevu\Search\Helper\Data as SearchHelperData;
use Klevu\Search\Helper\Price as SearchHelperPrice;
use Psr\Log\LoggerInterface as Logger;

class Product
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * Product constructor.
     * @param StoreManager $storeManager
     * @param MagentoCustomerGroups $magentoCustomerGroups
     * @param MagentoProduct $magentoProduct
     * @param KlevuProduct $klevuProduct
     * @param SearchHelperData $searchHelperData
     * @param SearchHelperPrice $searchHelperPrice
     * @param Logger $logger
     */
    public function __construct(
        StoreManager $storeManager,
        MagentoCustomerGroups $magentoCustomerGroups,
        MagentoProduct $magentoProduct,
        KlevuProduct $klevuProduct,
        SearchHelperData $searchHelperData,
        SearchHelperPrice $searchHelperPrice,
        Logger $logger

    )
    {
        $this->_storeManager = $storeManager;
        $this->_magentoCustomerGroups = $magentoCustomerGroups;
        $this->_magentoProduct = $magentoProduct;
        $this->_klevuProduct = $klevuProduct;
        $this->_searchHelperData = $searchHelperData;
        $this->_searchHelperPrice = $searchHelperPrice;
        $this->_logger = $logger;
    }

    /**
     * @param $item
     * @return array|mixed|string
     */

    public function afterGetGroupPricesData(
        \Klevu\Search\Model\Product\Product $subject, $result, $item)
    {
        //$this->_searchHelperData->log(\Zend\Log\Logger::DEBUG, sprintf("Item ID: %s", $item->getId()));
        if ($item) {
            $product['groupPrices'] = $this->getGroupPrices($item);
        } else {
            $product['groupPrices'] = "";
        }
        return $product['groupPrices'];
    }

    /**
     * Get the list of prices based on customer group
     *
     * @param object $item OR $parent
     *
     * @return array
     */
    protected function getGroupPrices($proData)
    {
        $priceGroupData = array();
        foreach ($this->_magentoCustomerGroups as $type) {
            $product = $this->_magentoProduct->setCustomerGroupId($type->getCustomerGroupId())->load((int)$proData->getId());
            if ($product instanceOf \Magento\Catalog\Model\Product) {
                $final_price = $product->getFinalprice();
                $processed_final_price = $this->_searchHelperPrice->processPrice($final_price, 'final_price', $product, $this->_storeManager->getStore());
                if ($processed_final_price) {
                    $result['label'] = $type->getCustomerGroupCode();
                    $result['values'] = $processed_final_price;
                    $priceGroupData[$product->getCustomerGroupId()] = $result;
                }
            }
            //$this->_searchHelperData->log(\Zend\Log\Logger::DEBUG, sprintf("Rule group price ProductPriceProcessAfter: : %d", $processed_final_price));
        }
        return $priceGroupData;
    }
}

