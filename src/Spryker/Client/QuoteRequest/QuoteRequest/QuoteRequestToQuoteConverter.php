<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\QuoteRequest\QuoteRequest;

use Generated\Shared\Transfer\QuoteErrorTransfer;
use Generated\Shared\Transfer\QuoteRequestTransfer;
use Generated\Shared\Transfer\QuoteResponseTransfer;
use Spryker\Client\QuoteRequest\Dependency\Client\QuoteRequestToPersistentCartClientInterface;
use Spryker\Client\QuoteRequest\Dependency\Client\QuoteRequestToQuoteClientInterface;

class QuoteRequestToQuoteConverter implements QuoteRequestToQuoteConverterInterface
{
    protected const MESSAGE_ERROR_WRONG_QUOTE_REQUEST_STATUS = 'quote_request.checkout.validation.error.wrong_status';

    /**
     * @var \Spryker\Client\QuoteRequest\Dependency\Client\QuoteRequestToPersistentCartClientInterface
     */
    protected $persistentCartClient;

    /**
     * @var \Spryker\Client\QuoteRequest\Dependency\Client\QuoteRequestToQuoteClientInterface
     */
    protected $quoteClient;

    /**
     * @var \Spryker\Client\QuoteRequest\QuoteRequest\QuoteRequestCheckerInterface
     */
    protected $quoteRequestChecker;

    /**
     * @param \Spryker\Client\QuoteRequest\Dependency\Client\QuoteRequestToPersistentCartClientInterface $persistentCartClient
     * @param \Spryker\Client\QuoteRequest\Dependency\Client\QuoteRequestToQuoteClientInterface $quoteClient
     * @param \Spryker\Client\QuoteRequest\QuoteRequest\QuoteRequestCheckerInterface $quoteRequestChecker
     */
    public function __construct(
        QuoteRequestToPersistentCartClientInterface $persistentCartClient,
        QuoteRequestToQuoteClientInterface $quoteClient,
        QuoteRequestCheckerInterface $quoteRequestChecker
    ) {
        $this->persistentCartClient = $persistentCartClient;
        $this->quoteClient = $quoteClient;
        $this->quoteRequestChecker = $quoteRequestChecker;
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteRequestTransfer $quoteRequestTransfer
     *
     * @return \Generated\Shared\Transfer\QuoteResponseTransfer
     */
    public function convertQuoteRequestToLockedQuote(QuoteRequestTransfer $quoteRequestTransfer): QuoteResponseTransfer
    {
        if (!$this->quoteRequestChecker->isQuoteRequestReady($quoteRequestTransfer)) {
            return $this->getErrorResponse(static::MESSAGE_ERROR_WRONG_QUOTE_REQUEST_STATUS);
        }

        $latestQuoteRequestVersionTransfer = $quoteRequestTransfer->getLatestVersion();
        $quoteTransfer = $latestQuoteRequestVersionTransfer->getQuote();

        $quoteTransfer->setName($latestQuoteRequestVersionTransfer->getVersionReference());
        $quoteTransfer->setQuoteRequestVersionReference($latestQuoteRequestVersionTransfer->getVersionReference());

        $quoteTransfer = $this->quoteClient->lockQuote($quoteTransfer);

        return $this->persistentCartClient->persistCustomerQuote($quoteTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteRequestTransfer $quoteRequestTransfer
     *
     * @return \Generated\Shared\Transfer\QuoteResponseTransfer
     */
    public function convertQuoteRequestToQuote(QuoteRequestTransfer $quoteRequestTransfer): QuoteResponseTransfer
    {
        if (!$this->quoteRequestChecker->isQuoteRequestDraft($quoteRequestTransfer)) {
            return $this->getErrorResponse(static::MESSAGE_ERROR_WRONG_QUOTE_REQUEST_STATUS);
        }

        $latestQuoteRequestVersionTransfer = $quoteRequestTransfer->requireLatestVersion()
            ->getLatestVersion();

        $quoteTransfer = $latestQuoteRequestVersionTransfer->getQuote()
            ->setQuoteRequestVersionReference($latestQuoteRequestVersionTransfer->getVersionReference())
            ->setQuoteRequestReference($quoteRequestTransfer->getQuoteRequestReference())
            ->setName($quoteRequestTransfer->getQuoteRequestReference());

        return $this->persistentCartClient->persistCustomerQuote($quoteTransfer);
    }

    /**
     * @param string $message
     *
     * @return \Generated\Shared\Transfer\QuoteResponseTransfer
     */
    protected function getErrorResponse(string $message): QuoteResponseTransfer
    {
        return (new QuoteResponseTransfer())
            ->setIsSuccessful(false)
            ->addError((new QuoteErrorTransfer())->setMessage($message));
    }
}
