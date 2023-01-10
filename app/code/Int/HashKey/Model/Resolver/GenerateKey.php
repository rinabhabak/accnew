<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_HashKey
 * @author    Indusnet
 */

namespace Int\HashKey\Model\Resolver;

//resolver section
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;

/**
 * Class ConfiguratorSave
 * @package Int\ConfiguratorGraphQl\Model\Resolver
 */
class GenerateKey implements ResolverInterface
{
    public function resolve(Field $field,$context,ResolveInfo $info,array $value = null,array $args = null) 
    {
        if (empty($args['input']) || !is_array($args['input'])) {
            throw new GraphQlInputException(__('"input" value should be specified'));
        }
        
        if (!isset($args['input']['identifier'])) {
            throw new GraphQlInputException(__('"Identifier" value should be specified'));
        }
        
        $userIp = $args['input']['identifier'];
        
        if (!filter_var($userIp, FILTER_VALIDATE_IP)) {
            throw new GraphQlInputException(__('Invalid IP'));
        }
        
        try {
            $key  = md5($userIp);
            
            $hashKeyArray= array('hashkey' => $key);
            return $hashKeyArray;
        
        } catch (\Exception $e) {
            throw new GraphQlInputException(__('Error ! %1', $e->getMessage()));
        }
    }
}