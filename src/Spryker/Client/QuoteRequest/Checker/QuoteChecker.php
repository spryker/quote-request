<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\QuoteRequest\Checker;

use Generated\Shared\Transfer\QuoteTransfer;

class QuoteChecker implements QuoteCheckerInterface
{
    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return bool
     */
    public function isQuoteRequestVersionReferenceAndCustomShipmentPriceSet(QuoteTransfer $quoteTransfer): bool
    {
        return $this->isQuoteRequestVersionReferenceSet($quoteTransfer) && $this->isCustomShipmentPriceSet($quoteTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return bool
     */
    protected function isCustomShipmentPriceSet(QuoteTransfer $quoteTransfer): bool
    {
        foreach ($quoteTransfer->getItems() as $itemTransfer) {
            if ($itemTransfer->getShipment()->getMethod()->getSourcePrice()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return bool
     */
    protected function isQuoteRequestVersionReferenceSet(QuoteTransfer $quoteTransfer): bool
    {
        return (bool)$quoteTransfer->getQuoteRequestVersionReference();
    }
}
