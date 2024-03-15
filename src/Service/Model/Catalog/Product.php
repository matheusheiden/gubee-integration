<?php

declare(strict_types=1);

namespace Gubee\Integration\Service\Model\Catalog;

use Gubee\Integration\Api\Enum\MainCategoryEnum;
use Gubee\Integration\Helper\Catalog\Attribute;
use Gubee\SDK\Resource\Catalog\Product\ValidateResource;
use Gubee\Integration\Model\Config;
use Magento\Catalog\Api\Data\ProductAttributeSearchResultsInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Eav\Api\Data\AttributeSearchResultsInterface;
use Gubee\Integration\Service\Model\Catalog\Product\Variation;
use Gubee\Integration\Model\ResourceModel\Catalog\Product\Attribute\CollectionFactory as AttributeCollectionFactory;
use Gubee\SDK\Enum\Catalog\Product\Attribute\OriginEnum;
use Gubee\SDK\Enum\Catalog\Product\StatusEnum;
use Gubee\SDK\Enum\Catalog\Product\TypeEnum;
use Gubee\SDK\Model\Catalog\Category;
use Gubee\SDK\Model\Catalog\Product\Attribute\AttributeValue;
use Gubee\SDK\Model\Catalog\Product\Attribute\Brand;
use Gubee\SDK\Resource\Catalog\ProductResource;
use InvalidArgumentException;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\ObjectManagerInterface;

use function array_filter;
use function count;

class Product
{
    protected ProductInterface $product;
    protected \Gubee\SDK\Model\Catalog\Product $gubeeProduct;
    protected ProductResource $productResource;
    protected Attribute $attribute;
    protected Config $config;
    protected ObjectManagerInterface $objectManager;
    /** @var AttributeSearchResultsInterface|ProductAttributeSearchResultsInterface */
    protected $attributeCollection;
    protected CollectionFactory $categoryCollectionFactory;
    protected StockItemInterface $stockItem;
    protected ValidateResource $validateResource;

    public function __construct(
        ProductInterface $product,
        Attribute $attribute,
        Config $config,
        ProductResource $productResource,
        ValidateResource $validateResource,
        ObjectManagerInterface $objectManager,
        CollectionFactory $categoryCollectionFactory,
        AttributeCollectionFactory $attributeCollectionFactory,
        StockRegistryInterface $stockRegistry
    )
    {
        $this->validateResource = $validateResource;
        $this->attributeCollection = $attributeCollectionFactory->create();
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->stockItem = $stockRegistry->getStockItem($product->getId());
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->objectManager = $objectManager;
        $this->attribute = $attribute;
        $this->config = $config;
        $this->product = $product;
        $this->productResource = $productResource;
        $this->gubeeProduct = $objectManager->create(
                \Gubee\SDK\Model\Catalog\Product::class,
            array_filter(
                [
                    'id' => $this->buildId(),
                    'mainCategory' => $this->buildMainCategory(),
                    'mainSku' => $this->buildMainSku(),
                    'origin' => $this->buildOrigin(),
                    'status' => $this->buildStatus(),
                    'type' => $this->buildType(),
                    'name' => $this->buildName(),
                    'nbm' => $this->buildNbm(),
                    'categories' => $this->buildCategories(),
                    'specifications' => $this->buildSpecifications(),
                    'variantAttributes' => $this->buildVariantAttributes(),
                    'brand' => $this->buildBrand(),
                    'variations' => $this->buildVariations(),
                ]
            )
        );
    }

    public function validate()
    {
        return $this->validateResource->create($this->gubeeProduct);
    }

    public function load(string $id, string $field = 'externalId'): self
    {
        switch ($field) {
            case 'externalId':
                $this->gubeeProduct = $this->productResource->getByExternalId($id);
                break;
            case 'id':
                $this->gubeeProduct = $this->productResource->getById($id);
                break;
            default:
                throw new InvalidArgumentException('Invalid field');
        }

        return $this;
    }

    public function save()
    {
        $this->productResource->save($this->gubeeProduct);
    }

    private function buildBrand()
    {
        $brand = $this->attribute->getAttributeValueLabel(
            $this->config->getBrandAttribute(),
            $this->product
        );
        if (!$brand) {
            return null;
        }

        return $this->getObjectManager()->create(
            Brand::class,
            [
                'name' => $brand,
            ]
        );
    }

    private function buildId()
    {
        return $this->product->getId();
    }

    private function buildMainCategory()
    {
        $categories = $this->product->getCategoryIds();
        $collection = $this->categoryCollectionFactory->create()
            ->addAttributeToFilter('entity_id', ['in' => $categories])
            ->addAttributeToSelect('*');
        $collection->getSelect()->limit(1);
        // change sort
        $collection->getSelect()->order(
            'level',
            $this->config->getMainCategoryPosition()
            ==
            MainCategoryEnum::DEEPER()
            ? 'DESC'
            : 'ASC'
        );
        $category = $collection->getFirstItem();
        if (!$category->getId()) {
            return null;
        }
        return $this->getObjectManager()->create(
            Category::class,
            [
                'id' => $category->getId(),
                'name' => $category->getName(),
            ]
        );
    }

    private function buildMainSku()
    {
        return $this->product->getSku();
    }

    private function buildOrigin()
    {
        return $this->attribute->getRawAttributeValue(
            'gubee_origin',
            $this->product
        ) ?: OriginEnum::NATIONAL();
    }

    private function buildStatus()
    {
        $status = StatusEnum::ACTIVE();
        if (!$this->product->isSalable()) {
            $status = StatusEnum::INACTIVE();
        }
        if (!$this->stockItem->getIsInStock()) {
            $status = StatusEnum::INACTIVE();
        }
        return $status;
    }

    private function buildType()
    {
        if ($this->product->getTypeId() == Configurable::TYPE_CODE) {
            return TypeEnum::VARIANT();
        }
        $parents = $this->product
            ->getTypeInstance()
            ->getParentIdsByChild(
                $this->product->getId()
            );
        if (count($parents) > 0) {
            return TypeEnum::VARIANT();
        }
        return TypeEnum::SIMPLE();
    }

    private function buildName()
    {
        return $this->product->getName();
    }

    private function buildNbm()
    {
        return $this->attribute->getAttributeValueLabel(
            $this->config->getNbmAttribute(),
            $this->product
        );
    }

    private function buildCategories()
    {
        $categories = $this->product->getCategoryIds();
        $collection = $this->categoryCollectionFactory->create()
            ->addAttributeToFilter('entity_id', ['in' => $categories])
            ->addAttributeToSelect('*');
        if (!$collection->count()) {
            return null;
        }
        $categories = [];
        foreach ($collection as $key => $category) {
            $categories[$key] = $this->getObjectManager()->create(
                Category::class,
                [
                    'id' => $category->getId(),
                    'name' => $category->getName(),
                ]
            );
        }

        return $categories;
    }

    private function buildSpecifications()
    {
        $specs = [];
        $attributes = $this->attributeCollection->getItems();
        $attributeCodes = array_map(
            function ($attribute) {
                return $attribute->getAttributeCode();
            },
            $attributes
        );
        foreach ($this->product->getAttributes() as $attribute) {
            if (!$attribute->getIsUserDefined()) {
                continue;
            }

            if (!in_array($attribute->getAttributeCode(), $attributeCodes)) {
                continue;
            }

            $value = $this->product->getData(
                $attribute->getAttributeCode()
            );
            if (!$value) {
                continue;
            }
            $specs[] = $this->objectManager->create(
                AttributeValue::class,
                [
                    'attribute' => $attribute->getAttributeCode(),
                    'values' => is_array($value) ? $value : [$value]
                ]
            );
        }

        return $specs;
    }

    private function buildVariantAttributes()
    {
        $specs = [];
        $attributes = $this->attributeCollection->getItems();
        $attributeCodes = array_map(
            function ($attribute) {
                return $attribute->getAttributeCode();
            },
            $attributes
        );
        foreach ($this->product->getAttributes() as $attribute) {
            if (!$attribute->getIsUserDefined()) {
                continue;
            }

            if (!in_array($attribute->getAttributeCode(), $attributeCodes)) {
                continue;
            }

            $value = $this->attribute->getRawAttributeValue(
                $attribute->getAttributeCode(),
                $this->product
            );
            if (!$value) {
                continue;
            }

            if (
                $this->attribute->isVariantAttribute(
                    $attribute->getAttributeCode(),
                    $this->product
                )
            ) {
                $specs[] = $this->objectManager->create(
                    AttributeValue::class,
                    [
                        'attribute' => $attribute->getAttributeCode(),
                    ]
                );
            }

            $specs[] = $this->objectManager->create(
                AttributeValue::class,
                [
                    'attribute' => $attribute->getAttributeCode(),
                ]
            );
        }

        return $specs;
    }

    private function buildVariations()
    {
        if ($this->product->getTypeId() != Configurable::TYPE_CODE) {
            return [
                $this->objectManager->create(
                    Variation::class,
                    [
                        'product' => $this->product,
                    ]
                )->getVariation(),
            ];
        }

        $variations = [];
        $children = $this->product
            ->getTypeInstance()
            ->getUsedProducts($this->product);
        foreach ($children as $child) {
            $variations[] = $this->objectManager->create(
                Variation::class,
                [
                    'product' => $child,
                    'parent' => $this->product,
                ]
            )->getVariation();

        }


        return $variations;
    }


    public function getObjectManager(): ObjectManagerInterface
    {
        return $this->objectManager;
    }

    public function getGubeeProduct(): \Gubee\SDK\Model\Catalog\Product
    {
        return $this->gubeeProduct;
    }
}