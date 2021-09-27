<?php

namespace Codilar\CategoryForGTM\Model;

use Magento\Framework\MessageQueue\PublisherInterface;

/**
 * Class CategoryPathPublisher
 * @package Codilar\CategoryForGTM\Model
 */
class CategoryPathPublisher{

    const TOPIC_NAME = 'category.path.topic';
    /**
     * @var PublisherInterface
     */
    private $publisher;

    /**
     * @param PublisherInterface $publisher
     */
    public function __construct(
        PublisherInterface $publisher
    )
    {
        $this->publisher = $publisher;
    }

    /**
     * @param $productId
     */
    public function execute($productId)
    {
        $this->publisher->publish(self::TOPIC_NAME,$productId);
    }
}
