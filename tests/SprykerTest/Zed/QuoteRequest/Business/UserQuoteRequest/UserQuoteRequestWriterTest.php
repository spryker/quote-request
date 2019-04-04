<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\QuoteRequest\Business\UserQuoteRequest;

use Codeception\Test\Unit;
use DateInterval;
use DateTime;
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
use PHPUnit\Framework\MockObject\MockObject;
use Spryker\Shared\Kernel\Transfer\Exception\RequiredTransferPropertyException;
use Spryker\Shared\QuoteRequest\QuoteRequestConfig as SharedQuoteRequestConfig;
use Spryker\Zed\QuoteRequest\Business\QuoteRequest\QuoteRequestReferenceGeneratorInterface;
use Spryker\Zed\QuoteRequest\Business\UserQuoteRequest\UserQuoteRequestWriter;
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
 * @group UserQuoteRequest
 * @group UserQuoteRequestWriterTest
 * Add your own group annotations below this line
 */
class UserQuoteRequestWriterTest extends Unit
{
    protected const FAKE_CUSTOMER_REFERENCE = 'FAKE_CUSTOMER_REFERENCE';
    protected const FAKE_QUOTE_REQUEST_REFERENCE = 'FAKE_QUOTE_REQUEST_REFERENCE';

    /**
     * @uses \Spryker\Zed\QuoteRequest\Business\UserQuoteRequest\UserQuoteRequestWriter::GLOSSARY_KEY_QUOTE_REQUEST_NOT_EXISTS
     */
    protected const GLOSSARY_KEY_QUOTE_REQUEST_NOT_EXISTS = 'quote_request.validation.error.not_exists';

    /**
     * @uses \Spryker\Zed\QuoteRequest\Business\UserQuoteRequest\UserQuoteRequestWriter::GLOSSARY_KEY_QUOTE_REQUEST_WRONG_STATUS
     */
    protected const GLOSSARY_KEY_QUOTE_REQUEST_WRONG_STATUS = 'quote_request.validation.error.wrong_status';

    /**
     * @uses \Spryker\Zed\QuoteRequest\Business\UserQuoteRequest\UserQuoteRequestWriter::GLOSSARY_KEY_WRONG_QUOTE_REQUEST_VALID_UNTIL
     */
    protected const GLOSSARY_KEY_WRONG_QUOTE_REQUEST_VALID_UNTIL = 'quote_request.update.validation.error.wrong_valid_until';

    /**
     * @uses \Spryker\Zed\QuoteRequest\Business\UserQuoteRequest\UserQuoteRequestWriter::GLOSSARY_KEY_QUOTE_REQUEST_COMPANY_USER_NOT_FOUND
     */
    protected const GLOSSARY_KEY_QUOTE_REQUEST_COMPANY_USER_NOT_FOUND = 'quote_request.validation.error.company_user_not_found';

    /**
     * @var \Spryker\Zed\QuoteRequest\Business\UserQuoteRequest\UserQuoteRequestWriter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $userQuoteRequestWriter;

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

        $this->userQuoteRequestWriter = $this->createUserQuoteRequestWriterMock();
    }

    /**
     * @return void
     */
    public function testCancelQuoteRequestChangesQuoteRequestStatusToCanceled(): void
    {
        // Arrange
        $quoteRequestTransfer = (new QuoteRequestBuilder([
            QuoteRequestTransfer::STATUS => SharedQuoteRequestConfig::STATUS_WAITING,
        ]))->build();

        $this->userQuoteRequestWriter->expects($this->any())
            ->method('findQuoteRequestTransfer')
            ->willReturn($quoteRequestTransfer);

        $quoteRequestCriteriaTransfer = (new QuoteRequestCriteriaTransfer())
            ->setQuoteRequestReference($quoteRequestTransfer->getQuoteRequestReference());

        // Act
        $quoteRequestResponseTransfer = $this->userQuoteRequestWriter->cancelQuoteRequest($quoteRequestCriteriaTransfer);

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
        //Arrange
        $quoteRequestCriteriaTransfer = new QuoteRequestCriteriaTransfer();

        // Act
        $quoteRequestResponseTransfer = $this->userQuoteRequestWriter->cancelQuoteRequest($quoteRequestCriteriaTransfer);

        //Assert
        $this->assertFalse($quoteRequestResponseTransfer->getIsSuccessful());
    }

    /**
     * @return void
     */
    public function testCancelQuoteRequestNotChangesQuoteRequestStatusToCanceledWithWrongReference(): void
    {
        // Arrange
        $this->userQuoteRequestWriter->expects($this->any())
            ->method('findQuoteRequestTransfer')
            ->willReturn(null);

        $quoteRequestCriteriaTransfer = (new QuoteRequestCriteriaTransfer())
            ->setQuoteRequestReference(static::FAKE_QUOTE_REQUEST_REFERENCE);

        // Act
        $quoteRequestResponseTransfer = $this->userQuoteRequestWriter->cancelQuoteRequest($quoteRequestCriteriaTransfer);

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
    public function testCancelQuoteRequestNotChangesQuoteRequestStatusToCanceledWithAlreadyCanceledStatus(): void
    {
        // Arrange
        $quoteRequestTransfer = (new QuoteRequestBuilder([
            QuoteRequestTransfer::STATUS => SharedQuoteRequestConfig::STATUS_CANCELED,
        ]))->build();

        $this->userQuoteRequestWriter->expects($this->any())
            ->method('findQuoteRequestTransfer')
            ->willReturn($quoteRequestTransfer);

        $quoteRequestCriteriaTransfer = (new QuoteRequestCriteriaTransfer())
            ->setQuoteRequestReference(static::FAKE_QUOTE_REQUEST_REFERENCE);

        // Act
        $quoteRequestResponseTransfer = $this->userQuoteRequestWriter->cancelQuoteRequest($quoteRequestCriteriaTransfer);

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
    public function testReviseQuoteRequestChangesQuoteRequestStatusToInProgress(): void
    {
        // Arrange
        $quoteRequestTransfer = (new QuoteRequestBuilder([
            QuoteRequestTransfer::STATUS => SharedQuoteRequestConfig::STATUS_WAITING,
        ]))->build();

        $quoteTransfer = (new QuoteBuilder())
            ->withItem([ItemTransfer::SKU => (new ProductConcreteBuilder())->build()->getSku(), ItemTransfer::UNIT_PRICE => 1])
            ->build();

        $quoteRequestVersionTransfer = (new QuoteRequestVersionBuilder([
            QuoteRequestVersionTransfer::QUOTE => $quoteTransfer,
        ]))->build();

        $quoteRequestTransfer->setLatestVersion($quoteRequestVersionTransfer);

        $this->userQuoteRequestWriter->expects($this->any())
            ->method('findQuoteRequestTransfer')
            ->willReturn($quoteRequestTransfer);

        $quoteRequestCriteriaTransfer = (new QuoteRequestCriteriaTransfer())
            ->setQuoteRequestReference($quoteRequestTransfer->getQuoteRequestReference());

        // Act
        $quoteRequestResponseTransfer = $this->userQuoteRequestWriter->reviseQuoteRequest($quoteRequestCriteriaTransfer);

        // Assert
        $this->assertTrue($quoteRequestResponseTransfer->getIsSuccessful());
        $this->assertEquals(
            SharedQuoteRequestConfig::STATUS_IN_PROGRESS,
            $quoteRequestResponseTransfer->getQuoteRequest()->getStatus()
        );
    }

    /**
     * @return void
     */
    public function testReviseQuoteRequestFailsWithoutReference(): void
    {
        // Arrange
        $quoteRequestCriteriaTransfer = new QuoteRequestCriteriaTransfer();

        // Act
        $quoteRequestResponseTransfer = $this->userQuoteRequestWriter->reviseQuoteRequest($quoteRequestCriteriaTransfer);

        //Assert
        $this->assertFalse($quoteRequestResponseTransfer->getIsSuccessful());
    }

    /**
     * @return void
     */
    public function testReviseQuoteRequestChangesQuoteRequestStatusToInProgressWithWrongReference(): void
    {
        // Arrange
        $this->userQuoteRequestWriter->expects($this->any())
            ->method('findQuoteRequestTransfer')
            ->willReturn(null);

        $quoteRequestCriteriaTransfer = (new QuoteRequestCriteriaTransfer())
            ->setQuoteRequestReference(static::FAKE_QUOTE_REQUEST_REFERENCE);

        // Act
        $quoteRequestResponseTransfer = $this->userQuoteRequestWriter->reviseQuoteRequest($quoteRequestCriteriaTransfer);

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
    public function testReviseQuoteRequestChangesQuoteRequestStatusToInProgressWithAlreadyInProgressStatus(): void
    {
        // Arrange
        $quoteRequestTransfer = (new QuoteRequestBuilder([
            QuoteRequestTransfer::STATUS => SharedQuoteRequestConfig::STATUS_IN_PROGRESS,
        ]))->build();

        $this->userQuoteRequestWriter->expects($this->any())
            ->method('findQuoteRequestTransfer')
            ->willReturn($quoteRequestTransfer);

        $quoteRequestCriteriaTransfer = (new QuoteRequestCriteriaTransfer())
            ->setQuoteRequestReference(static::FAKE_QUOTE_REQUEST_REFERENCE);

        // Act
        $quoteRequestResponseTransfer = $this->userQuoteRequestWriter->reviseQuoteRequest($quoteRequestCriteriaTransfer);

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
    public function testSendQuoteRequestToCustomerCreatesLatestVersionWithReadyStatus(): void
    {
        // Arrange
        $quoteTransfer = (new QuoteBuilder())
            ->withItem([ItemTransfer::SKU => (new ProductConcreteBuilder())->build()->getSku(), ItemTransfer::UNIT_PRICE => 1])
            ->build();

        $quoteRequestTransfer = (new QuoteRequestBuilder([
            QuoteRequestTransfer::STATUS => SharedQuoteRequestConfig::STATUS_IN_PROGRESS,
            QuoteRequestTransfer::LATEST_VERSION => (new QuoteRequestVersionBuilder([QuoteRequestVersionTransfer::QUOTE => $quoteTransfer]))->build(),
            QuoteRequestTransfer::VALID_UNTIL => (new DateTime())->add(new DateInterval("PT1H"))->format('Y-m-d H:i:s'),
        ]))->build();

        $this->userQuoteRequestWriter->expects($this->any())
            ->method('findQuoteRequestTransfer')
            ->willReturn($quoteRequestTransfer);

        $quoteRequestCriteriaTransfer = (new QuoteRequestCriteriaTransfer())
            ->setQuoteRequestReference($quoteRequestTransfer->getQuoteRequestReference());

        // Act
        $quoteRequestResponseTransfer = $this->userQuoteRequestWriter->sendQuoteRequestToCustomer($quoteRequestCriteriaTransfer);
        $storedQuoteRequestTransfer = $quoteRequestResponseTransfer->getQuoteRequest();

        // Assert
        $this->assertTrue($quoteRequestResponseTransfer->getIsSuccessful());
        $this->assertFalse($storedQuoteRequestTransfer->getIsLatestVersionHidden());
        $this->assertEquals(SharedQuoteRequestConfig::STATUS_READY, $storedQuoteRequestTransfer->getStatus());
        $this->assertEquals($quoteTransfer, $storedQuoteRequestTransfer->getLatestVersion()->getQuote());
    }

    /**
     * @return void
     */
    public function testSendQuoteRequestToCustomerFailsWithoutQuoteRequestReference(): void
    {
        // Arrange
        $quoteRequestCriteriaTransfer = new QuoteRequestCriteriaTransfer();

        // Act
        $quoteRequestResponseTransfer = $this->userQuoteRequestWriter->sendQuoteRequestToCustomer($quoteRequestCriteriaTransfer);

        //Assert
        $this->assertFalse($quoteRequestResponseTransfer->getIsSuccessful());
    }

    /**
     * @return void
     */
    public function testSendQuoteRequestToCustomerWithoutQuoteRequest(): void
    {
        // Arrange
        $this->userQuoteRequestWriter->expects($this->any())
            ->method('findQuoteRequestTransfer')
            ->willReturn(null);

        $quoteRequestCriteriaTransfer = (new QuoteRequestCriteriaTransfer())
            ->setQuoteRequestReference(static::FAKE_QUOTE_REQUEST_REFERENCE);

        // Act
        $quoteRequestResponseTransfer = $this->userQuoteRequestWriter->sendQuoteRequestToCustomer($quoteRequestCriteriaTransfer);

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
    public function testSendQuoteRequestToCustomerWithWrongQuoteRequestStatus(): void
    {
        // Arrange
        $quoteTransfer = (new QuoteBuilder())
            ->withItem([ItemTransfer::SKU => (new ProductConcreteBuilder())->build()->getSku(), ItemTransfer::UNIT_PRICE => 1])
            ->build();

        $quoteRequestTransfer = (new QuoteRequestBuilder([
            QuoteRequestTransfer::STATUS => SharedQuoteRequestConfig::STATUS_WAITING,
            QuoteRequestTransfer::LATEST_VERSION => (new QuoteRequestVersionBuilder([QuoteRequestVersionTransfer::QUOTE => $quoteTransfer]))->build(),
            QuoteRequestTransfer::VALID_UNTIL => (new DateTime())->add(new DateInterval("PT1H"))->format('Y-m-d H:i:s'),
        ]))->build();

        $this->userQuoteRequestWriter->expects($this->any())
            ->method('findQuoteRequestTransfer')
            ->willReturn($quoteRequestTransfer);

        $quoteRequestCriteriaTransfer = (new QuoteRequestCriteriaTransfer())
            ->setQuoteRequestReference($quoteRequestTransfer->getQuoteRequestReference());

        // Act
        $quoteRequestResponseTransfer = $this->userQuoteRequestWriter->sendQuoteRequestToCustomer($quoteRequestCriteriaTransfer);

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
    public function testSendQuoteRequestToCustomerWithWrongQuoteRequestValidUntil(): void
    {
        // Arrange
        $quoteTransfer = (new QuoteBuilder())
            ->withItem([ItemTransfer::SKU => (new ProductConcreteBuilder())->build()->getSku(), ItemTransfer::UNIT_PRICE => 1])
            ->build();

        $quoteRequestTransfer = (new QuoteRequestBuilder([
            QuoteRequestTransfer::STATUS => SharedQuoteRequestConfig::STATUS_IN_PROGRESS,
            QuoteRequestTransfer::LATEST_VERSION => (new QuoteRequestVersionBuilder([QuoteRequestVersionTransfer::QUOTE => $quoteTransfer]))->build(),
            QuoteRequestTransfer::VALID_UNTIL => (new DateTime())->sub(new DateInterval("PT1H"))->format('Y-m-d H:i:s'),
        ]))->build();

        $this->userQuoteRequestWriter->expects($this->any())
            ->method('findQuoteRequestTransfer')
            ->willReturn($quoteRequestTransfer);

        $quoteRequestCriteriaTransfer = (new QuoteRequestCriteriaTransfer())
            ->setQuoteRequestReference($quoteRequestTransfer->getQuoteRequestReference());

        // Act
        $quoteRequestResponseTransfer = $this->userQuoteRequestWriter->sendQuoteRequestToCustomer($quoteRequestCriteriaTransfer);

        // Assert
        $this->assertFalse($quoteRequestResponseTransfer->getIsSuccessful());
        $this->assertCount(1, $quoteRequestResponseTransfer->getMessages());
        $this->assertEquals(
            static::GLOSSARY_KEY_WRONG_QUOTE_REQUEST_VALID_UNTIL,
            $quoteRequestResponseTransfer->getMessages()[0]->getValue()
        );
    }

    /**
     * @return void
     */
    public function testCreateQuoteRequestCreatesUserQuoteRequest(): void
    {
        // Arrange
        $quoteRequestTransfer = (new QuoteRequestBuilder([
            QuoteRequestTransfer::COMPANY_USER => $this->companyUserTransfer,
        ]))->build();

        $this->userQuoteRequestWriter->expects($this->any())
            ->method('findCustomerReference')
            ->willReturn($this->companyUserTransfer->getCustomer()->getCustomerReference());

        $quoteRequestVersionTransfer = (new QuoteRequestVersionBuilder())->build();

        $this->userQuoteRequestWriter->expects($this->once())
            ->method('createQuoteRequestVersionTransfer')
            ->willReturn(
                $quoteRequestVersionTransfer
            );

        // Act
        $quoteRequestResponseTransfer = $this->userQuoteRequestWriter->createQuoteRequest($quoteRequestTransfer);
        $storedQuoteRequestTransfer = $quoteRequestResponseTransfer->getQuoteRequest();

        // Assert
        $this->assertTrue($quoteRequestResponseTransfer->getIsSuccessful());
        $this->assertTrue($storedQuoteRequestTransfer->getIsLatestVersionHidden());
        $this->assertEquals(SharedQuoteRequestConfig::STATUS_IN_PROGRESS, $storedQuoteRequestTransfer->getStatus());
    }

    /**
     * @return void
     */
    public function testCreateQuoteRequestFailsWithoutIdCompanyUser(): void
    {
        // Arrange

        // Assert
        $this->expectException(RequiredTransferPropertyException::class);

        // Act
        $this->userQuoteRequestWriter->createQuoteRequest(new QuoteRequestTransfer());
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

        $this->userQuoteRequestWriter->expects($this->any())
            ->method('findCustomerReference')
            ->willReturn(null);

        // Act
        $quoteRequestResponseTransfer = $this->userQuoteRequestWriter->createQuoteRequest($quoteRequestTransfer);

        // Assert
        $this->assertFalse($quoteRequestResponseTransfer->getIsSuccessful());
        $this->assertCount(1, $quoteRequestResponseTransfer->getMessages());
        $this->assertEquals(
            static::GLOSSARY_KEY_QUOTE_REQUEST_COMPANY_USER_NOT_FOUND,
            $quoteRequestResponseTransfer->getMessages()[0]->getValue()
        );
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function createUserQuoteRequestWriterMock(): MockObject
    {
        $userQuoteRequestWriterMock = $this->getMockBuilder(UserQuoteRequestWriter::class)
            ->setMethods(['findQuoteRequestTransfer', 'findCustomerReference', 'addQuoteRequestVersion', 'createQuoteRequestVersionTransfer'])
            ->setConstructorArgs([
                $this->createQuoteRequestConfigMock(),
                $this->createQuoteRequestEntityManagerMock(),
                $this->createQuoteRequestRepositoryInterfaceMock(),
                $this->createQuoteRequestReferenceGeneratorInterfaceMock(),
                $this->createQuoteRequestToCompanyUserInterfaceMock(),
                $this->createQuoteRequestToCartFacadeInterfaceMock(),
            ])
            ->getMock();

        $userQuoteRequestWriterMock
            ->method('addQuoteRequestVersion')
            ->willReturnCallback(function (QuoteRequestTransfer $quoteRequestTransfer) {
                return (new QuoteRequestVersionTransfer())->setQuote($quoteRequestTransfer->getLatestVersion()->getQuote());
            });

        return $userQuoteRequestWriterMock;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function createQuoteRequestEntityManagerMock(): MockObject
    {
        $quoteRequestEntityManager = $this->getMockBuilder(QuoteRequestEntityManager::class)
            ->setMethods([
                'createQuoteRequest',
                'updateQuoteRequest',
            ])
            ->disableOriginalConstructor()
            ->getMock();

        $quoteRequestEntityManager
            ->method('updateQuoteRequest')
            ->willReturnCallback(function (QuoteRequestTransfer $quoteRequestTransfer) {
                return $quoteRequestTransfer;
            });

        $quoteRequestEntityManager
            ->method('createQuoteRequest')
            ->willReturnCallback(function (QuoteRequestTransfer $quoteRequestTransfer) {
                $quoteRequestTransfer->setIdQuoteRequest(1)
                    ->setQuoteRequestReference(static::FAKE_QUOTE_REQUEST_REFERENCE);

                return $quoteRequestTransfer;
            });

        return $quoteRequestEntityManager;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function createQuoteRequestConfigMock(): MockObject
    {
        $quoteRequestConfigMock = $this->getMockBuilder(QuoteRequestConfig::class)
            ->setMethods([
                'getUserCancelableStatuses',
                'getUserRevisableStatuses',
            ])
            ->disableOriginalConstructor()
            ->getMock();

        $quoteRequestConfigMock
            ->method('getUserCancelableStatuses')
            ->willReturn([
                SharedQuoteRequestConfig::STATUS_DRAFT,
                SharedQuoteRequestConfig::STATUS_WAITING,
                SharedQuoteRequestConfig::STATUS_IN_PROGRESS,
                SharedQuoteRequestConfig::STATUS_READY,
            ]);

        $quoteRequestConfigMock->method('getUserRevisableStatuses')
            ->willReturn([
                SharedQuoteRequestConfig::STATUS_WAITING,
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
    protected function createQuoteRequestToCartFacadeInterfaceMock(): MockObject
    {
        return $this->createMock(QuoteRequestToCartInterface::class);
    }
}