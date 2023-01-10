<?php 
namespace Int\Cardtypes\Api;
 
 
interface CardtypesInterface {


	/**
     * Get country List.
     *
     * @return string $message on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
	public function getPost();
}
