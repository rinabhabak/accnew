<?php
namespace Int\CybersourceGraphQl\Model\Resolver\DataProvider;

class SavedCards
{

    public function __construct(
        \Magedelight\Cybersource\Model\CardsFactory $cardsFactory
    )
    {
        $this->_cardsFactory = $cardsFactory;
    }

    public function getSavedCards(int $customerId)
    {
        if(!isset($customerId)){
            return null;
        }

        $cardData = [];

        $cardCollection = $this->_cardsFactory->create()->getCollection()
                            ->addFieldToFilter('customer_id', $customerId)
                            ->setOrder('card_id', "ASC")
                            ->load();

        if($cardCollection->count() > 0){
            foreach($cardCollection as $key => $card){
                $cardData[$key] = [
                    "cc_exp_month" => sprintf("%02d", $card->getCcExpMonth()),
                    "cc_exp_year" => $card->getCcExpYear(),
                    "cc_last_4" => 'XXXX-XXXX-XXXX-' . $card->getCcLast4(),
                    "cc_type" => $card->getCcType()
                ];
            }
        }

        return $cardData;
    }
}
