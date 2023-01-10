<?php 
namespace Int\Cardtypes\Model;
 
 
class Cardtype {

	protected $cybersourceHelper;


	/**
     * 
     * @param \Magedelight\Cybersource\Helper\Data $cybersourceHelper
     */
    public function __construct(
        \Magedelight\Cybersource\Helper\Data $cybersourceHelper
     ) {
        $this->cybersourceHelper = $cybersourceHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getPost()
    {
        try{

            $cards = $this->cybersourceHelper->getCcAvailableCardTypes();
            $availableCards = [];
            $i = 0;
            foreach ($cards as $key => $value) {
                $availableCards[$i]['code'] = $key;
                $availableCards[$i]['name'] = $value;
                $i++;
            }

            $returnArray['availableCardTypes'] = $this->cybersourceHelper->getCcAvailableCardTypes();
            $response = $availableCards;
        }
        catch (\Magento\Framework\Exception\LocalizedException $e) {
            $response = array(0 => array('code'=>201, 'message' => $e->getMessage()));
        }

        return $response;
    }
}
