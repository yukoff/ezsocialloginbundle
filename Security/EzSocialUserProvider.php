<?php

namespace Crevillo\EzSocialLoginBundle\Security\Core\User;

use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use eZ\Publish\API\Repository\UserService;

class EzSocialUserProvider implements OAuthAwareUserProviderInterface
{
    /**
     * @var UserService
     */
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $repositoryUsers = $this->userService->loadUsersByEmail($response->getEmail());
        print_r($repositoryUsers);
        die();
    }
}