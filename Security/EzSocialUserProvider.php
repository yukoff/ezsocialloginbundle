<?php

namespace Crevillo\EzSocialLoginBundle\Security;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\MVC\Symfony\Security\User;
use eZ\Publish\Core\Repository\Values\User\User as APIUser;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Serializer\Exception\UnsupportedException;

/**
 * Class EzSocialUserProvider
 */
class EzSocialUserProvider implements UserProviderInterface,
    OAuthAwareUserProviderInterface
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var string
     */
    protected $class;

    /**
     * @var array
     */
    protected $properties = array(
        'identifier' => 'id',
    );

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
        $this->class = 'eZ\Publish\Core\MVC\Symfony\Security\User';
    }

    /**
     * @inheritdoc
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $user = $this->findUserFromResponse($response);

        return new User($user);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return $class === $this->class || is_subclass_of($class, $this->class);
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$this->supportsClass(get_class($user))) {
            throw new UnsupportedUserException(
                sprintf('Unsupported user class "%s"', get_class($user))
            );
        }
        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        return new User(
            new APIUser(
                array('login' => $username)
            )
        );
    }

    /**
     * @param UserResponseInterface $response
     *
     * @return object
     */
    protected function findUserFromResponse(UserResponseInterface $response)
    {
        try {
            $user = $this->repository->getUserService()->loadUserByLogin(
                $response->getUsername()
            );
        } catch (\eZ\Publish\API\Repository\Exceptions\NotFoundException $e) {
            $user = $this->createNewUserFromResponse($response);
        }

        return $user;
    }

    /**
     * Create a new user in the repository
     *
     * @param UserResponseInterface $response
     * @return \eZ\Publish\Core\MVC\Symfony\Security\User
     */
    private function createNewUserFromResponse(UserResponseInterface $response)
    {
        $userService = $this->repository->getUserService();
        $userCreateStruct = $userService->newUserCreateStruct(
            $response->getUsername(),
            $response->getEmail(),
            md5($response->getEmail() . $response->getNickname() . time()),
            'eng-GB', // @todo get default site language here
            $this->repository->getContentTypeService()->loadContentTypeByIdentifier('user')
        );

        $userCreateStruct->setField('first_name', $response->getFirstName());
        $userCreateStruct->setField('last_name', $response->getLastName());

        $repositoryUser = $this->repository->sudo(
            function () use ($userService, $userCreateStruct) {
                $userGroup = $userService->loadUserGroup('11'); // guest accounts
                return $userService->createUser(
                    $userCreateStruct,
                    array($userGroup)
                );
            }
        );

        return $repositoryUser;
    }
}
