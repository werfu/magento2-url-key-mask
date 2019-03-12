<?php
namespace Werfu\UrlKeyMask\Model;

use Magento\Catalog\Api\CategoryRepositoryInterface;

class CategoryUrlPathGenerator extends \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator
{
    const XML_PATH_URL_KEY_MASK = 'catalog/category_fields_masks/url_key_mask';

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        CategoryRepositoryInterface $categoryRepository
    ) {
        parent::__construct($storeManager, $scopeConfig, $categoryRepository);
    }

    /**
     * Prepare URL Key with stored category data (fallback for "Use Default Value" logic)
     *
     * @param \Magento\Catalog\Model\Category $category
     * @return string
     */
    protected function prepareCategoryDefaultUrlKey(\Magento\Catalog\Model\Category $category)
    {
        $key = $this->GetKeyDefault();

        if($key && !empty($key))
        {
            return $this->ProcessTokenizedKey($key, $category);
        } 
        else 
        {
            return parent::getUrlKey($category);
        }
    }

    /**
     * Generate category url key
     *
     * @param \Magento\Catalog\Model\Category $category
     * @return string
     */
    public function getUrlKey($category)
    {
        $urlKey = $category->getUrlKey();
        return $category->formatUrlKey($urlKey === '' || $urlKey === null ? $this->prepareCategoryDefaultUrlKey() : $urlKey);
    }

    /**
     * Parse a default tokenized string to generate the final key
     * 
     * @param string $token
     * @param \Magento\Catalog\Model\Category $category
     * @return string
     */
    protected function ProcessTokenizedKey($token, $category) {
        
        $result = $token;

        preg_match_all('/\{\{([a-zA-Z1-9]*)\}\}/', $token, $preg_output);

        for($i = 0; $i < count($preg_output[1]); $i++)
        {
            $value = $category->getData($preg_output[1][$i]);
            if(is_null($value))
            {
                $value = '';
            }

            $result = str_replace($preg_output[0][$i], $value, $result);
        }

        return $category->formatUrlKey($result);
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