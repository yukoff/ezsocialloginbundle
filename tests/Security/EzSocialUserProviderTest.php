<?php

namespace Crevillo\EzSocialLoginBundle\Tests\Security;

use PHPUnit_Framework_TestCase;
use Crevillo\EzSocialLoginBundle\Security\EzSocialUserProvider;
use eZ\Publish\Core\Repository\Tests\Service\Mock\Base as BaseServiceMockTest;

class EzSocialUserProviderTest extends BaseServiceMockTest
{
    public function testLoadUserByOAuthUserResponseShouldReturnAUser()
    {
        $responseMock = $this->getMock(
            'HWI\\Bundle\\OAuthBundle\\OAuth\\Response\\UserResponseInterface'
        );
        $repositoryMock = $this->getRepositoryMock();

        $userServiceMock = $this->getMock(
            'eZ\\Publish\\API\\Repository\\UserService'
        );

        $contentTypeServiceMock = $this->getMock(
            'eZ\\Publish\\API\\Repository\\ContentTypeService'
        );

        $userCreateStructMock = $this->getMock(
            'eZ\\Publish\\API\\Repository\\Values\\User\\UserCreateStruct'
        );

        $repositoryMock->expects($this->once())
            ->method('getUserService')
            ->will($this->returnValue($userServiceMock));

        $repositoryMock->expects($this->once())
            ->method('getContentTypeService')
            ->will($this->returnValue($contentTypeServiceMock));

        $userServiceMock->expects($this->once())
            ->method('newUserCreateStruct')
            ->will($this->returnValue($userCreateStructMock));

        $ezSocialUserProvider = new EzSocialUserProvider($repositoryMock);
        $user = $ezSocialUserProvider->loadUserByOauthUserResponse($responseMock);
        self::assertInstanceOf('eZ\Publish\Core\MVC\Symfony\Security\User', $user);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getRepositoryMock()
    {
        return $this->getMockBuilder(
            'eZ\\Publish\\Core\\Repository\\Repository'
        )->disableOriginalConstructor()->getMock();
    }
}