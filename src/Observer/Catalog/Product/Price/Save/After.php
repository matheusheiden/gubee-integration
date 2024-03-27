<?php

declare(strict_types=1);

namespace Gubee\Integration\Observer\Catalog\Product\Price\Save;

use Gubee\Integration\Command\Catalog\Product\Price\SendCommand;
use Gubee\Integration\Observer\Catalog\Product\AbstractProduct;

class After extends AbstractProduct
{
    protected function process(): void
    {
        $this->queueManagement->append(
            SendCommand::class,
            [
                'sku' => $this->getProduct()->getSku(),
            ],
            (int) $this->getProduct()->getId()
        );
    }

    /**
     * Validate if the observer is allowed to run
     */
    protected function isAllowed(): bool
    {
        $product = $this->getProduct();
        if (
            ! $this->attribute->getRawAttributeValue(
                'gubee',
                $product
            )
        ) {
            return false;
        }

        if (
            ! $this->getProduct()
            ->dataHasChangedFor(
                $this->config->getPriceAttribute()
            )
        ) {
            return false;
        }

        return true;
    }
}
