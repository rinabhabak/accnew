<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Company\Query;

use Magento\TestFramework\TestCase\GraphQl\ResponseContainsErrorsException;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test company admin email resolver
 */
class CompanyAdminEmailTest extends GraphQlAbstract
{
    /**
     * @magentoApiDataFixture Magento/Company/_files/company.php
     * @magentoConfigFixture btob/website_configuration/company_active 1
     */
    public function testCompanyAdminEmailValid(): void
    {
        $query = <<<QUERY
{
    isCompanyAdminEmailAvailable(email: "test@test.com") {
      is_email_available
    }
}
QUERY;

        $response = $this->graphQlQuery($query);
        self::assertTrue($response['isCompanyAdminEmailAvailable']['is_email_available']);
    }

    /**
     * @magentoApiDataFixture Magento/Company/_files/company.php
     * @magentoConfigFixture btob/website_configuration/company_active 1
     */
    public function testCompanyAdminEmailInvalid(): void
    {
        $query = <<<QUERY
{
    isCompanyAdminEmailAvailable(email: "admin@magento.com") {
      is_email_available
    }
}
QUERY;

        $response = $this->graphQlQuery($query);
        self::assertFalse($response['isCompanyAdminEmailAvailable']['is_email_available']);
    }

    /**
     * @magentoApiDataFixture Magento/Company/_files/company.php
     * @magentoConfigFixture btob/website_configuration/company_active 1
     */
    public function testCompanyAdminEmailFormatInvalid(): void
    {
        $expectedMessage = 'Invalid value of "admin@magento" provided for the email field.';
        $query = <<<QUERY
{
    isCompanyAdminEmailAvailable(email: "admin@magento") {
      is_email_available
    }
}
QUERY;

        try {
            $this->graphQlQuery($query);
            self::fail('Response should contains errors.');
        } catch (ResponseContainsErrorsException $e) {
            $responseData = $e->getResponseData();
            self::assertEquals($expectedMessage, $responseData['errors'][0]['message']);
        }
    }

    /**
     * @magentoApiDataFixture Magento/Company/_files/company.php
     * @magentoConfigFixture btob/website_configuration/company_active 0
     */
    public function testCompanyInActive(): void
    {
        $expectedMessage = 'Company feature is not available.';
        $query = <<<QUERY
{
    isCompanyAdminEmailAvailable(email: "admin@magento.com") {
      is_email_available
    }
}
QUERY;

        try {
            $this->graphQlQuery($query);
            self::fail('Response should contains errors.');
        } catch (ResponseContainsErrorsException $e) {
            $responseData = $e->getResponseData();
            self::assertEquals($expectedMessage, $responseData['errors'][0]['message']);
        }
    }
}
