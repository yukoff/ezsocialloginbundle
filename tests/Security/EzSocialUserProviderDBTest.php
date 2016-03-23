<?php
/**
 * Created by PhpStorm.
 * User: carlosrevillo
 * Date: 23/03/16
 * Time: 10:34
 */

namespace Crevillo\EzSocialLoginBundle\Tests\Security;

use Crevillo\EzSocialLoginBundle\Security\EzSocialUserProvider;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\Core\Repository\Tests\Service\Integration\Base as BaseServiceTest;
use eZ\Publish\Core\Repository\Tests\Service\Integration\Legacy\Utils;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use Exception;

class EzSocialUserProviderDBTest extends BaseServiceTest
{
    protected function getRepository()
    {
        try {
            return Utils::getRepository();
        } catch (Exception $e) {
            $this->markTestIncomplete($e->getMessage());
        }
    }

    /**
     * Test provider won't create a new user if the user is already created
     */
    public function testEzSocialUserProviderShouldNotCreateNewUserIfAlreadyPresent()
    {
        $this->createUserVersion1();

        $query = new LocationQuery();

        $query->query = new Criterion\ContentTypeIdentifier( 'user' );

        $countUsers = $this->repository->getSearchService()->findLocations( $query )->totalCount;

        $provider = new EzSocialUserProvider($this->repository);
        $responseMock = $this->createUserResponseMock();
        $responseMock
            ->expects($this->atLeastOnce())
            ->method('getUserName')
            ->willReturn('user');

        $user = $provider->loadUserByOAuthUserResponse($responseMock);

        self::assertInstanceOf('\\eZ\\Publish\\Core\\MVC\\Symfony\\Security\\User', $user);
        self::assertEquals('user', $user->getUsername());

        $newCountUsers = $this->repository->getSearchService()->findLocations( $query )->totalCount;
        self::assertEquals($newCountUsers, $countUsers);
        
    }

    /**
     * Test provider will create a new user if the user is already created
     */
    public function testEzSocialUserProviderShouldCreateNewUserIfAlreadyPresent()
    {
        $this->createUserVersion1();

        $query = new LocationQuery();

        $query->query = new Criterion\ContentTypeIdentifier( 'user' );

        $countUsers = $this->repository->getSearchService()->findLocations( $query )->totalCount;

        $provider = new EzSocialUserProvider($this->repository);
        $responseMock = $this->createUserResponseMock();
        $responseMock
            ->expects($this->atLeastOnce())
            ->method('getUserName')
            ->willReturn('not_present_user');
        $responseMock
            ->expects($this->atLeastOnce())
            ->method('getEmail')
            ->willReturn('not_present_user@ez.no');
        $responseMock
            ->expects($this->once())
            ->method('getFirstName')
            ->willReturn('Not Present User Name');

        $responseMock
            ->expects($this->once())
            ->method('getLastName')
            ->willReturn('Not Present User Surname');

        $user = $provider->loadUserByOAuthUserResponse($responseMock);

        self::assertInstanceOf('\\eZ\\Publish\\Core\\MVC\\Symfony\\Security\\User', $user);
        self::assertEquals('not_present_user', $user->getUsername());

        $newCountUsers = $this->repository->getSearchService()->findLocations( $query )->totalCount;
        self::assertEquals($newCountUsers, $countUsers + 1);
    }

    protected function createUserResponseMock()
    {
        return $this->getMock(
            'HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface'
        );
    }
}
