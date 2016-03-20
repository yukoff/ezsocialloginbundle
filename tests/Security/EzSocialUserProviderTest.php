<?php

namespace Crevillo\EzSocialLoginBundle\Tests\Security;

use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\Repository\Values\ContentType\ContentType;
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
            $this->getRepository()
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

    public function testLoadUserByOAuthUserResponseWhenUserIsPResent()
    {
        $responseMock = $this->getMock('HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface');
        $responseMock
            ->expects($this->atLeastOnce())
            ->method('getUsername')
            ->will($this->returnValue('asm89'))
        ;

        $userServiceMock = $this->getPartlyMockedUserService(
            array('loadUserByLogin')
        );
        $userServiceMock
            ->expects($this->once())
            ->method('loadUserByLogin')
            ->with($responseMock->getUserName())
            ->willReturn(
                new APIUser(
                    array('login' => 'asm89')
                )
            );

        $repositoryMock = $this->getMock(
            'eZ\\Publish\\Core\\Repository\\Repository',
            array(),
            array(
                $this->getPersistenceMock(),
                $this->getSPIMockHandler('Search\\Handler'),
                array(),
                $this->getStubbedUser(14)
            )
        );
        $repositoryMock->expects($this->atLeastOnce())
            ->method('getUserService')
            ->willReturn($userServiceMock);


        $provider = new EzSocialUserProvider($repositoryMock);
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

    protected function getUserServiceMock()
    {
        return $this->getMock(
            'eZ\Publish\API\Repository\UserService'
        );
    }

    protected function getContentTypeServiceMock()
    {
        return  $this->getMock(
            'eZ\Publish\API\Repository\ContentTypeService'
        );
    }

    protected function getUserCreateStructMock()
    {
        return $this->getMock(
            'eZ\Publish\API\Repository\Values\User\UserCreateStruct'
        );
    }

    /**
     * Returns the User service to test with $methods mocked.
     *
     * Injected Repository comes from {@see getRepositoryMock()} and persistence handler from {@see getPersistenceMock()}
     *
     * @param string[] $methods
     *
     * @return \eZ\Publish\Core\Repository\UserService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getPartlyMockedUserService(array $methods = null)
    {
        return $this->getMock(
            'eZ\\Publish\\Core\\Repository\\UserService',
            $methods,
            array(
                $this->getRepositoryMock(),
                $this->getPersistenceMock()->userHandler(),
            )
        );
    }
}
