<?php

namespace Crevillo\EzSocialLoginBundle\Tests\Security;

use Crevillo\EzSocialLoginBundle\Core\EzSocialLoginUserManager;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\Repository\Values\User\UserCreateStruct;
use PHPUnit_Framework_TestCase;
use Crevillo\EzSocialLoginBundle\Security\EzSocialUserProvider;
use eZ\Publish\Core\Repository\Tests\Service\Mock\Base as BaseServiceMockTest;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\User;
use eZ\Publish\Core\MVC\Symfony\Security\User as eZUser;
use eZ\Publish\Core\Repository\Values\User\User as APIUser;

class EzSocialUserProviderTest extends BaseServiceMockTest
{
    /**
     * @var EzSocialUserProvider
     */
    protected $provider;

    public function setUp()
    {
        $this->provider = new EzSocialUserProvider(
            new EzSocialLoginUserManager(
                $this->getRepository()
            )
        );
    }

    public function testLoadUserByUsername()
    {
        $user = $this->provider->loadUserByUsername('asm89');
        $this->assertInstanceOf(
            '\eZ\Publish\Core\MVC\Symfony\Security\User', $user
        );
        $this->assertEquals('asm89', $user->getUsername());
    }

    public function testRefreshUser()
    {
        $user = $this->provider->loadUserByUsername('asm89');
        $freshUser = $this->provider->refreshUser($user);
        $this->assertEquals($user, $freshUser);
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\UnsupportedUserException
     * @expectedExceptionMessage Unsupported user class "Symfony\Component\Security\Core\User\User"
     */
    public function testRefreshUserUnsupportedClass()
    {
        $user = new User('asm89', 'foo');
        $this->provider->refreshUser($user);
    }

    public function testSupportsClass()
    {
        $class = get_class(
            new eZUser(
                new APIUser(
                    array('login' => 'asm89')
                )
            )
        );
        $this->assertTrue($this->provider->supportsClass($class));
        $this->assertFalse($this->provider->supportsClass('\Some\Other\Class'));
    }

    public function testLoadUserByOAuthUserResponseWhenUserIsPresent()
    {
        $responseMock = $this->createUserResponseMock();
        $responseMock
            ->expects($this->atLeastOnce())
            ->method('getUsername')
            ->will($this->returnValue('asm89'));

        $eZSocialUserManagerMock = $this->getEzSocialUserManagerMock();
        $eZSocialUserManagerMock
            ->expects($this->once())
            ->method('findUser')
            ->willReturn(
                new APIUser(
                    array(
                        'login' => 'asm89'
                    )
                )
            );

        $provider = new EzSocialUserProvider($eZSocialUserManagerMock);
        $user = $provider->loadUserByOAuthUserResponse($responseMock);

        $this->assertInstanceOf('eZ\Publish\Core\MVC\Symfony\Security\User', $user);
        $this->assertEquals('asm89', $user->getUsername());
    }

    public function testLoadUserByOAuthUserResponseWhenUserIsNotPresent()
    {
        $responseMock = $this->createUserResponseMock();
        $responseMock
            ->expects($this->atLeastOnce())
            ->method('getUsername')
            ->will($this->returnValue('asm89'));

        $eZSocialUserManagerMock = $this->getEzSocialUserManagerMock();
        $eZSocialUserManagerMock
            ->expects($this->once())
            ->method('findUser')
            ->willThrowException(
                new NotFoundException('user', 'asm89')
            );

        $eZSocialUserManagerMock
            ->expects($this->once())
            ->method('createNewUser')
            ->willReturn(
                new APIUser(
                    array(
                        'login' => 'asm89'
                    )
                )
            );

        $provider = new EzSocialUserProvider($eZSocialUserManagerMock);
        $user = $provider->loadUserByOAuthUserResponse($responseMock);

        $this->assertInstanceOf('eZ\Publish\Core\MVC\Symfony\Security\User', $user);
        $this->assertEquals('asm89', $user->getUsername());
    }


    protected function createUserResponseMock()
    {
        return $this->getMock(
            'HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface'
        );
    }

    protected function getRepositoryMock() {
        return $this->getMock(
            'eZ\\Publish\\Core\\Repository\\Repository',
            array(),
            array(
                $this->getPersistenceMock(),
                $this->getSPIMockHandler('Search\\Handler'),
                array(),
                $this->getStubbedUser(14)
            )
        );
    }

    protected function getEzSocialUserManagerMock() {
        return $this->getMock(
            'Crevillo\\EzSocialLoginBundle\\Core\\EzSocialLoginUserManager',
            array(),
            array(
                $this->getRepository()
            )
        );
    }
}
