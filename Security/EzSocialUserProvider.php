<?php

namespace Crevillo\EzSocialLoginBundle\Security;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\MVC\Symfony\Security\User;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;

class EzSocialUserProvider implements OAuthAwareUserProviderInterface
{
    /**
     * @var Repository
     */
    private $repository;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @inheritdoc
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $repository = $this->repository;
        $userService = $repository->getUserService();
        $repositoryUsers = $userService->loadUsersByEmail($response->getEmail());

        if (!empty($repositoryUsers)) {
            return new User($repositoryUsers[0]);
        }

        $userCreateStruct = $userService->newUserCreateStruct(
            $response->getNickName(),
            $response->getEmail(),
            md5($response->getEmail() . $response->getNickname() . time()),
            'eng-GB', // @todo get default language here
            $repository->getContentTypeService()->loadContentTypeByIdentifier('user')
        );

        $userCreateStruct->setField('first_name', $response->getFirstName());
        $userCreateStruct->setField('last_name', $response->getLastName());

        $repositoryUser = $repository->sudo(
            function($repository) use($userService, $userCreateStruct) {
                $userGroup = $userService->loadUserGroup('11'); // guest accounts
                return $userService->createUser($userCreateStruct, array($userGroup));
            }
        );

        return new User($repositoryUser);
    }
}
