<?php

namespace Klevu\ProductOverride\Model\Product;

use Klevu\Search\Model\Product\Product as Klevuproduct;

class Product extends Klevuproduct
{
	public function getGroupPricesData($item){
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
    protected function getGroupPrices($proData) {
      $helper = \Magento\Framework\App\ObjectManager::getInstance()->get('Klevu\Search\Helper\Data');
      $customer = \Magento\Framework\App\ObjectManager::getInstance()->create('Magento\Customer\Model\ResourceModel\Group\Collection');
      $priceGroupData = array();

      foreach ($customer as $type) {
		  
		$helper->log(\Zend\Log\Logger::DEBUG, sprintf("Rule group price Product ID: %d", $proData->getId()));

        $product = \Magento\Framework\App\ObjectManager::getInstance()->create('\Magento\Catalog\Model\Product')->setCustomerGroupId($type->getCustomerGroupId())->load($proData->getId());


        $final_price = $product->getFinalprice();
        $processed_final_price = $this->_priceHelper->processPrice($final_price, 'final_price', $product, $this->_storeModelStoreManagerInterface->getStore());

		$helper->log(\Zend\Log\Logger::DEBUG, sprintf("Rule group price ProductPriceProcessAfter: : %d", $processed_final_price));
         if ($processed_final_price) {
             $result['label'] = $type->getCustomerGroupCode();
             $result['values'] = $processed_final_price;
             $priceGroupData[$product->getCustomerGroupId()] = $result;
         }
     }
     return $priceGroupData;
   }

}
