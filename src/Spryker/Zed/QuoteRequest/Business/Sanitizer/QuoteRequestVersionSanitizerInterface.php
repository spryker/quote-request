<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\QuoteRequest\Business\Sanitizer;

use Generated\Shared\Transfer\QuoteRequestVersionTransfer;

interface QuoteRequestVersionSanitizerInterface
{
    /**
     * @param \Generated\Shared\Transfer\QuoteRequestVersionTransfer $quoteRequestVersionTransfer
     *
     * @return \Generated\Shared\Transfer\QuoteRequestVersionTransfer
     */
    public function cleanUpQuoteRequestVersionQuote(QuoteRequestVersionTransfer $quoteRequestVersionTransfer): QuoteRequestVersionTransfer;

    /**
     * @param \Generated\Shared\Transfer\QuoteRequestVersionTransfer $quoteRequestVersionTransfer
     *
     * @return \Generated\Shared\Transfer\QuoteRequestVersionTransfer
     */
    public function reloadQuoteRequestVersionItems(QuoteRequestVersionTransfer $quoteRequestVersionTransfer): QuoteRequestVersionTransfer;

    /**
     * @param \Generated\Shared\Transfer\QuoteRequestVersionTransfer $quoteRequestVersionTransfer
     *
     * @return \Generated\Shared\Transfer\QuoteRequestVersionTransfer
     */
    public function recalculateQuoteRequestVersionQuote(QuoteRequestVersionTransfer $quoteRequestVersionTransfer): QuoteRequestVersionTransfer;
}
