<?php
/**
 * Copyright © Indus Net Technologies All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Int\UpdateAttribute\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\ObjectManager;

class ProductForSales extends Command
{
    /** @var \Magento\Framework\App\State **/
    private $state;

    /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection **/
    protected $_productCollection;

    /** @var \Magento\Catalog\Model\ProductRepository **/
    protected $_productRepository;

    public function __construct(
        \Magento\Framework\App\State $state,
        \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection,
        \Magento\Catalog\Model\ProductRepository $productRepository
    ) {
        $this->state = $state;
        $this->_productCollection = $productCollection;
        $this->_productRepository = $productRepository;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output) 
    {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);

        $collection = $this->_productCollection->addAttributeToSelect('*') ->load();

        foreach ($collection as $_product)
        {
            if($_product->getTypeId() != \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE){
                continue;
            }
            
            $output->writeln($_product->getName().'- Parent Product - '. $_product->getProductForSales());

            $productForSaleValue =  $_product->getProductForSales();
            
            $_children = $_product->getTypeInstance()->getUsedProducts($_product);
            
            foreach ($_children as $child)
            {
                if($productForSaleValue == $child->getProductForSales()){
                    continue;
                }

                if($productForSaleValue == 1){
                    $this->updateAttributeValue($output, $child->getId(), 1);
                } else {
                    $this->updateAttributeValue($output, $child->getId(), 0);
                }
                
            }
        }
    }

    /**
     * @param ObjectManager $objectManager
     * @param int $product_id
     * @param int $value
     * @return void
     */
    protected function updateAttributeValue($output, $product_id, $value)
    {
        $product = $this->_productRepository->getById($product_id);
        $product->setProductForSales($value);
        $this->_productRepository->save($product);
        $output->writeln($product->getName().'- Child Product Updated - '. $product->getProductForSales());
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName("int_updateattribute:productforsales");
        $this->setDescription("Update Product For Sale Attribute");

        parent::configure();
    }
}

