<?php
/**
 * Override default ProductUrlPathGenerator to allow the use of a mask on the URL key
 */

namespace Werfu\UrlKeyMask\Model;

class ProductUrlPathGenerator extends \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator
{
    const XML_PATH_URL_KEY_MASK = 'catalog/fields_masks/url_key_mask';

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator $categoryUrlPathGenerator
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator $categoryUrlPathGenerator,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        parent::__construct($storeManager, $scopeConfig, $categoryUrlPathGenerator, $productRepository);
    }

    /**
     * Prepare URL Key with stored product data (fallback for "Use Default Value" logic)
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    protected function prepareProductDefaultUrlKey(\Magento\Catalog\Model\Product $product)
    {
        $key = $this->GetKeyDefault();

        if($key && !empty($key))
        {
            return $this->ProcessTokenizedKey($key, $product);
        } 
        else 
        {
            return parent::prepareProductDefaultUrlKey($product);
        }
    }

    /**
     * Prepare url key for product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    protected function prepareProductUrlKey(\Magento\Catalog\Model\Product $product)
    {
        $urlKey = $product->getUrlKey();
        return $product->formatUrlKey($urlKey === '' || $urlKey === null ? $this->prepareProductDefaultUrlKey($product) : $urlKey);
    }

    /**
     * Parse a default tokenized string to generate the final key
     * 
     * @param string $token
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    protected function ProcessTokenizedKey($token, $product) {
        
        $result = $token;

        preg_match_all('/\{\{([a-zA-Z1-9]*)\}\}/', $token, $preg_output);

        for($i = 0; $i < count($preg_output[1]); $i++)
        {
            $value = $product->getData($preg_output[1][$i]);
            if(is_null($value))
            {
                $value = '';
            }

            $result = str_replace($preg_output[0][$i], $value, $result);
        }

        return $product->formatUrlKey($result);
    }

    /**
     * Return stored default key
     * 
     * @return string
     */
    protected function GetKeyDefault() {
        if(!isset($this->keyDefault))
        {
            $this->keyDefault = $this->scopeConfig->getValue(
                self::XML_PATH_URL_KEY_MASK
            );
        }
        return $this->keyDefault;
    }    

}