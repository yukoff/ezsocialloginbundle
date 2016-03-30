<?php
/**
 * Created by PhpStorm.
 * User: carlosrevillo
 * Date: 29/03/16
 * Time: 11:14
 */

namespace Crevillo\EzSocialLoginBundle\OAuth\ResourceOwner;

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\LinkedinResourceOwner as HwiLinkedinResourceOwner;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Buzz\Message\RequestInterface as HttpRequestInterface;

class LinkedinResourceOwner extends HwiLinkedinResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'identifier'     => 'id',
        'nickname'       => 'formattedName',
        'realname'       => 'formattedName',
        'email'          => 'emailAddress',
        'profilepicture' => 'pictureUrl',
        'firstname'      => 'firstName',
        'lastname'       => 'lastName'
    );

    /**
     * {@inheritDoc}
     */
    protected function configureOptions(OptionsResolverInterface $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'authorization_url'        => 'https://www.linkedin.com/uas/oauth2/authorization',
            'access_token_url'         => 'https://www.linkedin.com/uas/oauth2/accessToken',
            'infos_url'                => 'https://api.linkedin.com/v1/people/~:(id,formatted-name,email-address,picture-url,first-name,last-name)?format=json',

            'csrf'                     => true,

            'use_bearer_authorization' => false,
        ));
    }
}
