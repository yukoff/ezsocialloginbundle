<?php

namespace Crevillo\EzSocialLoginBundle\Security;

use Crevillo\EzSocialLoginBundle\Core\EzSocialLoginUserManager;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\MVC\Symfony\Security\User;
use eZ\Publish\Core\Repository\Values\User\User as APIUser;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Serializer\Exception\UnsupportedException;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;

/**
 * Class EzSocialUserProvider
 */
class EzSocialUserProvider implements UserProviderInterface, OAuthAwareUserProviderInterface
{
    /**
     * @var EzSocialLoginUserManager
     */
    private $userManager;

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

    public function __construct(EzSocialLoginUserManager $userManager)
    {
        $this->userManager = $userManager;
        $this->class = 'eZ\Publish\Core\MVC\Symfony\Security\User';
    }

    /**
     * @inheritdoc
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        try {
            $user = $this->userManager->findUser($response->getEmail());
        } catch (\Exception $e) {
            $username = $response->getEmail();
            $firstName = $response->getFirstName() != '' ? $response->getFirstName() : $response->getNickName();
            $lastName =  $response->getLastName() != '' ? $response->getLastName() : '';

            try {
                $user = $this->userManager->createNewUser(
                    $username,
                    $response->getEmail(),
                    $firstName,
                    $lastName
                );
            } catch (\Exception $e) {
                throw $e;
            }
        }

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
}
