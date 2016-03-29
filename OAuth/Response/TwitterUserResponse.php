<?php
/**
 * Created by PhpStorm.
 * User: carlosrevillo
 * Date: 29/03/16
 * Time: 18:02
 */

namespace Crevillo\EzSocialLoginBundle\OAuth\Response;

use HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse;

class TwitterUserResponse extends PathUserResponse
{
    /**
     * Generate dummy email
     */
    public function getEmail()
    {
        return $this->getUsername() . '@dummy-twitter.com';
    }
}
