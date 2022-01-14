<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\QuoteRequest\Dependency\Service;

use InvalidArgumentException;

class QuoteRequestToUtilEncodingServiceBridge implements QuoteRequestToUtilEncodingServiceInterface
{
    /**
     * @var \Spryker\Service\UtilEncoding\UtilEncodingServiceInterface
     */
    protected $utilEncodingService;

    /**
     * @param \Spryker\Service\UtilEncoding\UtilEncodingServiceInterface $utilEncodingService
     */
    public function __construct($utilEncodingService)
    {
        $this->utilEncodingService = $utilEncodingService;
    }

    /**
     * @param array<mixed> $value
     * @param int|null $options
     * @param int|null $depth
     *
     * @return string
     */
    public function encodeJson($value, ?int $options = null, ?int $depth = null): string
    {
        return $this->utilEncodingService->encodeJson($value, $options, $depth) ?? '';
    }

    /**
     * @param string $jsonValue
     * @param bool $assoc Deprecated: `false` is deprecated, always use `true` for array return.
     * @param int|null $depth
     * @param int|null $options
     *
     * @throws \InvalidArgumentException
     *
     * @return array<mixed>
     */
    public function decodeJson(string $jsonValue, bool $assoc = false, ?int $depth = null, ?int $options = null): array
    {
        if ($assoc === false) {
            trigger_error('Param #2 `$assoc` must be `true` as return of type `object` is not accepted.', E_USER_DEPRECATED);
        }

        /** @var array|null $result */
        $result = $this->utilEncodingService->decodeJson($jsonValue, $assoc, $depth, $options);
        if ($result === null) {
            throw new InvalidArgumentException('Null returned, invalid value given.');
        }

        return $result;
    }
}
