<?php
namespace Crevillo\EzSocialLoginBundle\Core;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\MVC\Symfony\Security\User;

class EzSocialLoginUserManager
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
     * Find de user with login = username
     *
     * @param $username
     * @throws NotFoundException
     *
     * @return User
     */
    public function findUser($username)
    {
        try {
            $user = $this->repository->getUserService()->loadUserByLogin(
                $username
            );
        } catch (NotFoundException $e) {
            throw $e;
        }

        return $user;
    }

    /**
     * @param $login
     * @param $email
     * @param $firstName
     * @param $lastName

     * @return User
     */
    public function createNewUser(
        $login,
        $email,
        $firstName,
        $lastName
    ) {
        $userService = $this->repository->getUserService();
        $userCreateStruct = $userService->newUserCreateStruct(
            $login,
            $email,
            md5($email . $login . time() . rand(1, 10000)),
            'eng-GB', // @todo get default site language here
            $this->repository->getContentTypeService()->loadContentTypeByIdentifier('user')
        );

        $userCreateStruct->setField('first_name', $firstName);
        $userCreateStruct->setField('last_name', $lastName);

        $user = $this->repository->sudo(
            function () use ($userService, $userCreateStruct) {
                $userGroup = $userService->loadUserGroup('11'); // guest accounts
                return $userService->createUser(
                    $userCreateStruct,
                    array($userGroup)
                );
            }
        );

        return $user;
    }
}
