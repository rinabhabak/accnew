<?php

 /**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_ForgotPasswordGraphQl
 * @author    Indusnet
 */
namespace Int\InvitationGraphQl\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;


class EmailByInvitation implements ResolverInterface
{
    /**
     * @var InvitationFactory
     */
    protected $invitationFactory;

    /**
     * @var \Magento\Framework\Url\DecoderInterface
     */
    protected $urlDecoder;

    public function __construct(
        \Magento\Invitation\Model\InvitationFactory $invitationFactory,
        \Magento\Framework\Url\DecoderInterface $urlDecoder
    ){
        $this->invitationFactory = $invitationFactory;
        $this->urlDecoder = $urlDecoder;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ){
        
        try {
            if (!isset($args['invitationToken'])) {
              throw new GraphQlInputException(__('Token should be specified'));
            return false;
        }
            

            $invitation = $this->invitationFactory->create();
            $invitation->loadByInvitationCode(
                $this->urlDecoder->decode(
                    $args['invitationToken']
                )
            )->makeSureCanBeAccepted();
            //echo json_encode($invitation->getData());die;
            if($invitation->getData()) {
                 return [
                        "email" =>$invitation->getEmail()
                    ];  
            }

           
          
        }catch (\Exception $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()));
        }
    }

   
}
