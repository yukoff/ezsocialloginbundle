<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Crevillo\EzSocialLoginBundle\Tests\OAuth\ResourceOwner;

use Crevillo\EzSocialLoginBundle\OAuth\ResourceOwner\LinkedinResourceOwner;
use HWI\Bundle\OAuthBundle\Tests\OAuth\ResourceOwner\LinkedinResourceOwnerTest as HwiLinkedinResourceOwnerTest;

class LinkedinResourceOwnerTest extends HwiLinkedinResourceOwnerTest
{
    protected $userResponse = <<<json
{
    "id": "1",
    "formattedName": "bar"
}
json;
    protected $paths        = array(
        'identifier'     => 'id',
        'nickname'       => 'formattedName',
        'realname'       => 'formattedName',
        'email'          => 'emailAddress',
        'profilepicture' => 'pictureUrl',
        'firstname'      => 'firstName',
        'lastname'       => 'lastName'
    );
}
