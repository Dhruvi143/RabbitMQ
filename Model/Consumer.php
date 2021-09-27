<?php
namespace Codilar\CategoryForGTM\Model;

use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Model\ProductRepository;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Model\ResourceModel\Category\Collection;

/**
 * Class Consumer
 * @package Codilar\CategoryForGTM\Model
 */
class Consumer{
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var ProductRepository
     */
    private $productRepository;
    /**
     * @var CategoryRepository
     */
    private $categoryRepository;
    /**
     * @var Collection
     */
    private $collection;


    /**
     * @param LoggerInterface $logger
     * @param ProductRepository $productRepository
     * @param CategoryRepository $categoryRepository
     * @param Collection $collection
     */
    public function __construct(
        LoggerInterface $logger,
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository,
        Collection $collection
    )
    {
        $this->logger = $logger;
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->collection = $collection;
    }


    /**
     * @param $productId
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function processMessage($productId)
    {
        $this->logger->info('Initiated for Product:'.$productId);
        $product = $this->productRepository->getById($productId);
        $pathArray = [];
        if(empty($product->getCategoryPath())) {
            $productTypeInstance = $product->getTypeInstance();
            $usedProducts = $productTypeInstance->getUsedProducts($product);
            foreach ($usedProducts as $childProduct) {
                $this->logger->info($childProduct->getId());
                $categoryIds = $childProduct->getCategoryIds();
                foreach ($categoryIds as $categoryId) {
                    $categoryInstance = $this->categoryRepository->get($categoryId);
                    $level = $categoryInstance->getLevel();
                    if ($level == 5) {
                        $categories = $this->collection->addAttributeToSelect('name')->getItems();
                        foreach ($categories as $category) {
                            $path = array_slice(explode('/', $category->getPath()), 2);
                            foreach ($path as $key => $value) {
                                $path[$key] = str_replace('/', '\/', $categories[$value]->getName());
                            }
                            $pathArray[$category->getId()] = strtolower(join('/', $path));
                        }
                        $result = $pathArray[$categoryId];
                        if ($product->getCustomAttribute('static_product_name')) {
                            $value = $product->getCustomAttribute('static_product_name')->getValue();
                            $fullCategoryPath = $result . '/' . $value;
                            $this->logger->info('Category Path:' . $fullCategoryPath);
                            $product->setCategoryPath($fullCategoryPath);
                            $this->productRepository->save($product);
                            $this->logger->info('saved');
                        } else {
                            $this->logger->info('Static Product Name is not Set');
                        }

                        break;
                    }

                }
                break;
            }
        }
        else{
            $this->logger->info('Category Path already exists');
        }

    }
}
