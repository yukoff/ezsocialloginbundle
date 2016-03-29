<?php
/**
 * Created by PhpStorm.
 * User: carlosrevillo
 * Date: 29/03/16
 * Time: 17:57
 */

namespace Crevillo\EzSocialLoginBundle\OAuth\ResourceOwner;

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\TwitterResourceOwner as HWITwitterResourceOwner;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TwitterResourceOwner extends  HWITwitterResourceOwner
{
    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolverInterface $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'authorization_url' => 'https://api.twitter.com/oauth/authenticate',
            'request_token_url' => 'https://api.twitter.com/oauth/request_token',
            'access_token_url'  => 'https://api.twitter.com/oauth/access_token',
            'user_response_class'      => '\Crevillo\EzSocialLoginBundle\OAuth\Response\TwitterUserResponse',
            'infos_url'         => 'https://api.twitter.com/1.1/account/verify_credentials.json',
            'include_email'     => false,
        ));

        // Symfony <2.6 BC
        if (method_exists($resolver, 'setDefined')) {
            $resolver->setDefined('x_auth_access_type');
            // @link https://dev.twitter.com/oauth/reference/post/oauth/request_token
            $resolver->setAllowedValues('x_auth_access_type', array('read', 'write'));
            // @link https://dev.twitter.com/rest/reference/get/account/verify_credentials
            $resolver->setAllowedTypes('include_email', 'bool');
        } else {
            $resolver->setOptional(array(
                'x_auth_access_type',
            ));
            $resolver->setAllowedValues(array(
                'x_auth_access_type' => array('read', 'write'),
                'include_email' => array(true, false),
            ));
        }
    }
}
