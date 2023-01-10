<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Company\Plugin\Catalog\Model\Product;

use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test for company reserved attribute list
 */
class ReservedAttributeListPluginTest extends TestCase
{
    /**
     * @var Attribute
     */
    private $attribute;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->attribute = $this->objectManager->get(Attribute::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->attribute = null;
        $this->objectManager = null;
    }

    /**
     * Test that user defined attribute codes used in Company model cannot be created
     *
     * Given an EAV attribute that is user defined
     * When its respective attribute code is set to a getter field used by Company model (e.g. company_name)
     * Then an exception is raised
     * And the attribute save does not proceed to completion
     *
     * @return void
     * @throws LocalizedException
     * @magentoAppArea adminhtml
     */
    public function testReservedAttributeCodesCannotBeUsedAsAUserDefinedAttributeCode(): void
    {
        $attributeCode = 'company_name';
        $this->expectException(LocalizedException::class);
        $expected = "The attribute code '{$attributeCode}' is reserved by system. Please try another attribute code";

        $this->attribute->setAttributeCode($attributeCode);
        $this->attribute->setIsUserDefined(true);
        $this->attribute->beforeSave();

        $this->expectExceptionMessage($expected);
    }
}
