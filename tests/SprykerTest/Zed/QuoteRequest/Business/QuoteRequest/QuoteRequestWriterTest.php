<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\QuoteRequest\Business\QuoteRequest;

use Codeception\Test\Unit;
use Generated\Shared\DataBuilder\CompanyUserBuilder;
use Generated\Shared\DataBuilder\ProductConcreteBuilder;
use Generated\Shared\DataBuilder\QuoteBuilder;
use Generated\Shared\DataBuilder\QuoteRequestBuilder;
use Generated\Shared\DataBuilder\QuoteRequestVersionBuilder;
use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\QuoteRequestCriteriaTransfer;
use Generated\Shared\Transfer\QuoteRequestTransfer;
use Generated\Shared\Transfer\QuoteRequestVersionTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use PHPUnit\Framework\MockObject\MockObject;
use Spryker\Shared\Kernel\Transfer\Exception\RequiredTransferPropertyException;
use Spryker\Shared\QuoteRequest\QuoteRequestConfig as SharedQuoteRequestConfig;
use Spryker\Zed\QuoteRequest\Business\QuoteRequest\QuoteRequestReferenceGeneratorInterface;
use Spryker\Zed\QuoteRequest\Business\QuoteRequest\QuoteRequestWriter;
use Spryker\Zed\QuoteRequest\Dependency\Facade\QuoteRequestToCalculationInterface;
use Spryker\Zed\QuoteRequest\Dependency\Facade\QuoteRequestToCartInterface;
use Spryker\Zed\QuoteRequest\Dependency\Facade\QuoteRequestToCompanyUserInterface;
use Spryker\Zed\QuoteRequest\Persistence\QuoteRequestEntityManager;
use Spryker\Zed\QuoteRequest\Persistence\QuoteRequestRepositoryInterface;
use Spryker\Zed\QuoteRequest\QuoteRequestConfig;

/**
 * Auto-generated group annotations
 * @group SprykerTest
 * @group Zed
 * @group QuoteRequest
 * @group Business
 * @group QuoteRequest
 * @group QuoteRequestWriterTest
 * Add your own group annotations below this line
 */
class QuoteRequestWriterTest extends Unit
{
    protected const FAKE_CUSTOMER_REFERENCE = 'FAKE_CUSTOMER_REFERENCE';
    protected const FAKE_ID_QUOTE_REQUEST_VERSION = 'FAKE_ID_QUOTE_REQUEST_VERSION';

    /**
     * @uses \Spryker\Zed\QuoteRequest\Business\QuoteRequest\QuoteRequestWriter::GLOSSARY_KEY_QUOTE_REQUEST_NOT_EXISTS
     */
    protected const GLOSSARY_KEY_QUOTE_REQUEST_NOT_EXISTS = 'quote_request.validation.error.not_exists';

    /**
     * @uses \Spryker\Zed\QuoteRequest\Business\QuoteRequest\QuoteRequestWriter::GLOSSARY_KEY_QUOTE_REQUEST_WRONG_STATUS
     */
    protected const GLOSSARY_KEY_QUOTE_REQUEST_WRONG_STATUS = 'quote_request.validation.error.wrong_status';

    /**
     * @uses \Spryker\Zed\QuoteRequest\Business\QuoteRequest\QuoteRequestWriter::GLOSSARY_KEY_QUOTE_REQUEST_COMPANY_USER_NOT_FOUND
     */
    protected const GLOSSARY_KEY_QUOTE_REQUEST_COMPANY_USER_NOT_FOUND = 'quote_request.validation.error.company_user_not_found';

    /**
     * @var \Spryker\Zed\QuoteRequest\Business\QuoteRequest\QuoteRequestWriter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteRequestWriter;

    /**
     * @var \Generated\Shared\Transfer\CompanyUserTransfer
     */
    protected $companyUserTransfer;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->companyUserTransfer = (new CompanyUserBuilder())
            ->withCustomer([
                CustomerTransfer::CUSTOMER_REFERENCE => static::FAKE_CUSTOMER_REFERENCE,
            ])
            ->build()
            ->setIdCompanyUser('');

        $this->quoteRequestWriter = $this->createQuoteRequestWriterMock();
    }

    /**
     * @return void
     */
    public function testCancelQuoteRequestChangesQuoteRequestStatusToCanceled(): void
    {
        // Arrange
        $quoteRequestTransfer = (new QuoteRequestBuilder([
            QuoteRequestTransfer::STATUS => SharedQuoteRequestConfig::STATUS_WAITING,
            QuoteRequestTransfer::COMPANY_USER => $this->companyUserTransfer,
        ]))->build();

        $this->quoteRequestWriter->expects($this->any())
            ->method('findQuoteRequestTransfer')
            ->willReturn($quoteRequestTransfer);

        $quoteRequestCriteriaTransfer = (new QuoteRequestCriteriaTransfer())
            ->setIdCompanyUser($this->companyUserTransfer->getIdCompanyUser())
            ->setQuoteRequestReference($quoteRequestTransfer->getQuoteRequestReference());

        // Act
        $quoteRequestResponseTransfer = $this->quoteRequestWriter->cancelQuoteRequest($quoteRequestCriteriaTransfer);

        // Assert
        $this->assertTrue($quoteRequestResponseTransfer->getIsSuccessful());
        $this->assertEquals(
            SharedQuoteRequestConfig::STATUS_CANCELED,
            $quoteRequestResponseTransfer->getQuoteRequest()->getStatus()
        );
    }

    /**
     * @return void
     */
    public function testCancelQuoteRequestFailsWithoutReference(): void
    {
        // Arrange
        $quoteRequestCriteriaTransfer = (new QuoteRequestCriteriaTransfer());

        // Act
        $quoteRequestResponseTransfer = $this->quoteRequestWriter->cancelQuoteRequest($quoteRequestCriteriaTransfer);

        //Assert
        $this->assertFalse($quoteRequestResponseTransfer->getIsSuccessful());
    }

    /**
     * @return void
     */
    public function testCancelQuoteRequestFailsWithoutCompanyUser(): void
    {
        // Arrange
        $quoteRequestCriteriaTransfer = (new QuoteRequestCriteriaTransfer())
            ->setQuoteRequestReference(static::FAKE_ID_QUOTE_REQUEST_VERSION);

        // Act
        $quoteRequestResponseTransfer = $this->quoteRequestWriter->cancelQuoteRequest($quoteRequestCriteriaTransfer);

        //Assert
        $this->assertFalse($quoteRequestResponseTransfer->getIsSuccessful());
    }

    /**
     * @return void
     */
    public function testCancelQuoteRequestFailsWithWrongReference(): void
    {
        // Arrange
        $this->quoteRequestWriter->expects($this->any())
            ->method('findQuoteRequestTransfer')
            ->willReturn(null);

        $quoteRequestCriteriaTransfer = (new QuoteRequestCriteriaTransfer())
            ->setIdCompanyUser($this->companyUserTransfer->getIdCompanyUser())
            ->setQuoteRequestReference(static::FAKE_ID_QUOTE_REQUEST_VERSION);

        // Act
        $quoteRequestResponseTransfer = $this->quoteRequestWriter->cancelQuoteRequest($quoteRequestCriteriaTransfer);

        // Assert
        $this->assertFalse($quoteRequestResponseTransfer->getIsSuccessful());
        $this->assertCount(1, $quoteRequestResponseTransfer->getMessages());
        $this->assertEquals(
            static::GLOSSARY_KEY_QUOTE_REQUEST_NOT_EXISTS,
            $quoteRequestResponseTransfer->getMessages()[0]->getValue()
        );
    }

    /**
     * @return void
     */
    public function testCancelQuoteRequestFailsWithWrongStatus(): void
    {
        // Arrange
        $quoteRequestTransfer = (new QuoteRequestBuilder([
            QuoteRequestTransfer::STATUS => SharedQuoteRequestConfig::STATUS_IN_PROGRESS,
            QuoteRequestTransfer::COMPANY_USER => $this->companyUserTransfer,
        ]))->build();

        $this->quoteRequestWriter->expects($this->any())
            ->method('findQuoteRequestTransfer')
            ->willReturn($quoteRequestTransfer);

        $quoteRequestCriteriaTransfer = (new QuoteRequestCriteriaTransfer())
            ->setIdCompanyUser($this->companyUserTransfer->getIdCompanyUser())
            ->setQuoteRequestReference($quoteRequestTransfer->getQuoteRequestReference());

        // Act
        $quoteRequestResponseTransfer = $this->quoteRequestWriter->cancelQuoteRequest($quoteRequestCriteriaTransfer);

        // Assert
        $this->assertFalse($quoteRequestResponseTransfer->getIsSuccessful());
        $this->assertCount(1, $quoteRequestResponseTransfer->getMessages());
        $this->assertEquals(
            static::GLOSSARY_KEY_QUOTE_REQUEST_WRONG_STATUS,
            $quoteRequestResponseTransfer->getMessages()[0]->getValue()
        );
    }

    /**
     * @return void
     */
    public function testCreateQuoteRequestCreatesQuoteRequestWithWaitingStatus(): void
    {
        // Arrange
        $quoteRequestTransfer = (new QuoteRequestBuilder([
            QuoteRequestTransfer::COMPANY_USER => $this->companyUserTransfer,
        ]))->build();

        $this->quoteRequestWriter->expects($this->any())
            ->method('findCustomerReference')
            ->willReturn($this->companyUserTransfer->getCustomer()->getCustomerReference());

        $quoteTransfer = (new QuoteBuilder())
            ->withItem([ItemTransfer::SKU => (new ProductConcreteBuilder())->build()->getSku(), ItemTransfer::UNIT_PRICE => 1])
            ->build();

        $quoteRequestVersionTransfer = (new QuoteRequestVersionBuilder([
            QuoteRequestVersionTransfer::QUOTE => $quoteTransfer,
        ]))->build();

        $quoteRequestTransfer->setLatestVersion($quoteRequestVersionTransfer);

        // Act
        $quoteRequestResponseTransfer = $this->quoteRequestWriter->createQuoteRequest($quoteRequestTransfer);

        // Assert
        $this->assertTrue($quoteRequestResponseTransfer->getIsSuccessful());
        $this->assertEquals(
            SharedQuoteRequestConfig::STATUS_WAITING,
            $quoteRequestResponseTransfer->getQuoteRequest()->getStatus()
        );
    }

    /**
     * @return void
     */
    public function testCreateQuoteRequestFailsWithoutCustomer(): void
    {
        // Arrange
        $quoteRequestTransfer = (new QuoteRequestBuilder([
            QuoteRequestTransfer::COMPANY_USER => $this->companyUserTransfer,
        ]))->build();

        $this->quoteRequestWriter->expects($this->any())
            ->method('findCustomerReference')
            ->willReturn(null);

        // Act
        $quoteRequestResponseTransfer = $this->quoteRequestWriter->createQuoteRequest($quoteRequestTransfer);

        // Assert
        $this->assertFalse($quoteRequestResponseTransfer->getIsSuccessful());
        $this->assertCount(1, $quoteRequestResponseTransfer->getMessages());
        $this->assertEquals(
            static::GLOSSARY_KEY_QUOTE_REQUEST_COMPANY_USER_NOT_FOUND,
            $quoteRequestResponseTransfer->getMessages()[0]->getValue()
        );
    }

    /**
     * @return void
     */
    public function testCreateQuoteRequestFailsWithoutQuote(): void
    {
        // Arrange
        $quoteRequestTransfer = (new QuoteRequestBuilder([
            QuoteRequestTransfer::COMPANY_USER => $this->companyUserTransfer,
        ]))->build();

        $quoteRequestTransfer->setLatestVersion((new QuoteRequestVersionBuilder())->build());

        $this->quoteRequestWriter->expects($this->any())
            ->method('findCustomerReference')
            ->willReturn($this->companyUserTransfer->getCustomer()->getCustomerReference());

        // Assert
        $this->expectException(RequiredTransferPropertyException::class);

        // Act
        $this->quoteRequestWriter->createQuoteRequest($quoteRequestTransfer);
    }

    /**
     * @return void
     */
    public function testCreateQuoteRequestFailsWithEmptyQuote(): void
    {
        // Arrange
        $quoteRequestTransfer = (new QuoteRequestBuilder([
            QuoteRequestTransfer::COMPANY_USER => $this->companyUserTransfer,
        ]))->build();

        $this->quoteRequestWriter->expects($this->any())
            ->method('findCustomerReference')
            ->willReturn($this->companyUserTransfer->getCustomer()->getCustomerReference());

        $quoteRequestVersionTransfer = (new QuoteRequestVersionBuilder([
            QuoteRequestVersionTransfer::QUOTE => (new QuoteBuilder())->build(),
        ]))->build();

        $quoteRequestTransfer->setLatestVersion($quoteRequestVersionTransfer);

        // Assert
        $this->expectException(RequiredTransferPropertyException::class);

        // Act
        $this->quoteRequestWriter->createQuoteRequest($quoteRequestTransfer);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function createQuoteRequestWriterMock(): MockObject
    {
        $quoteRequestWriter = $this->getMockBuilder(QuoteRequestWriter::class)
            ->setMethods(['findQuoteRequestTransfer', 'findCustomerReference'])
            ->setConstructorArgs([
                $this->createQuoteRequestConfigMock(),
                $this->createQuoteRequestEntityManagerMock(),
                $this->createQuoteRequestRepositoryInterfaceMock(),
                $this->createQuoteRequestReferenceGeneratorInterfaceMock(),
                $this->createQuoteRequestToCompanyUserInterfaceMock(),
                $this->createQuoteRequestToCalculationInterfaceMock(),
                $this->createQuoteRequestToCartInterfaceMock(),
            ])
            ->getMock();

        return $quoteRequestWriter;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function createQuoteRequestEntityManagerMock(): MockObject
    {
        $quoteRequestEntityManagerMock = $this->getMockBuilder(QuoteRequestEntityManager::class)
            ->setMethods([
                'createQuoteRequest',
                'updateQuoteRequest',
                'createQuoteRequestVersion',
            ])
            ->disableOriginalConstructor()
            ->getMock();

        $quoteRequestEntityManagerMock
            ->method('updateQuoteRequest')
            ->willReturnCallback(function (QuoteRequestTransfer $quoteRequestTransfer) {
                return $quoteRequestTransfer;
            });

        $quoteRequestEntityManagerMock
            ->method('createQuoteRequest')
            ->willReturnCallback(function (QuoteRequestTransfer $quoteRequestTransfer) {
                return $quoteRequestTransfer;
            });

        return $quoteRequestEntityManagerMock;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function createQuoteRequestConfigMock(): MockObject
    {
        $quoteRequestConfigMock = $this->getMockBuilder(QuoteRequestConfig::class)
            ->setMethods(['getInitialStatus', 'getCancelableStatuses'])
            ->disableOriginalConstructor()
            ->getMock();

        $quoteRequestConfigMock
            ->method('getInitialStatus')
            ->willReturn(SharedQuoteRequestConfig::STATUS_WAITING);

        $quoteRequestConfigMock
            ->method('getCancelableStatuses')
            ->willReturn([
                SharedQuoteRequestConfig::STATUS_DRAFT,
                SharedQuoteRequestConfig::STATUS_WAITING,
                SharedQuoteRequestConfig::STATUS_READY,
            ]);

        return $quoteRequestConfigMock;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function createQuoteRequestRepositoryInterfaceMock(): MockObject
    {
        return $this->getMockBuilder(QuoteRequestRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function createQuoteRequestReferenceGeneratorInterfaceMock(): MockObject
    {
        return $this->getMockBuilder(QuoteRequestReferenceGeneratorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function createQuoteRequestToCompanyUserInterfaceMock(): MockObject
    {
        return $this->getMockBuilder(QuoteRequestToCompanyUserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function createQuoteRequestToCalculationInterfaceMock(): MockObject
    {
        $quoteRequestToCalculationInterfaceMock = $this->getMockBuilder(QuoteRequestToCalculationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $quoteRequestToCalculationInterfaceMock
            ->method('recalculate')
            ->willReturnCallback(function (QuoteTransfer $quoteTransfer) {
                return $quoteTransfer;
            });

        return $quoteRequestToCalculationInterfaceMock;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function createQuoteRequestToCartInterfaceMock(): MockObject
    {
        $quoteRequestToCartInterfaceMock = $this->getMockBuilder(QuoteRequestToCartInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $quoteRequestToCartInterfaceMock
            ->method('reloadItems')
            ->willReturnCallback(function (QuoteTransfer $quoteTransfer) {
                return $quoteTransfer;
            });

        return $quoteRequestToCartInterfaceMock;
    }
}