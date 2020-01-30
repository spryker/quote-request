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
    public function isQuoteRequestReferenceSet(QuoteTransfer $quoteTransfer): bool
    {
        return (bool)$quoteTransfer->getQuoteRequestReference();
    }
}
