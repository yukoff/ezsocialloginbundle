<?php

namespace Crevillo\EzSocialLoginBundle\Tests\Core;

use Crevillo\EzSocialLoginBundle\Core\EzSocialLoginUserManager;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\Repository\Values\User\UserCreateStruct;
use eZ\Publish\API\Repository\Tests\BaseTest;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\User;
use eZ\Publish\Core\MVC\Symfony\Security\User as eZUser;
use eZ\Publish\Core\Repository\Values\User\User as APIUser;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Repository\Tests\Service\Integration\Legacy\Utils;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use Exception;

class EzSocialUserManagerTest extends BaseTest
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
     * Test manager will return the user by its login
     */
    public function testFindUserByLogin()
    {
        $this->createUserVersion1();

        $userManager = new EzSocialLoginUserManager($this->repository);

        $user = $userManager->findUser('user');

        self::assertInstanceOf('\\eZ\\Publish\\Core\\Repository\\Values\\User\\User', $user);
        self::assertEquals('user', $user->login);
        self::assertEquals('user@example.com', $user->email);
    }

    /**
     * Test manager will return the user by its login
     */
    public function testShouldGetTheUserByEmailWhenNotFoundByLogin()
    {
        $this->createUserVersion1();

        $userManager = new EzSocialLoginUserManager($this->repository);

        $user = $userManager->findUser('user@example.com');

        self::assertInstanceOf('\\eZ\\Publish\\Core\\Repository\\Values\\User\\User', $user);
        self::assertEquals('user', $user->login);
        self::assertEquals('user@example.com', $user->email);
    }

    public function testCreateUser()
    {
        $query = new LocationQuery();
        $query->query = new Criterion\ContentTypeIdentifier('user');
        $countUsers = $this->repository->getSearchService()->findLocations( $query )->totalCount;

        $userManager = new EzSocialLoginUserManager($this->repository);
        $user = $userManager->createNewUser(
            'new_user_login',
            'new_user_email@ez.no',
            'New',
            'User'
        );

        $newCountUsers = $this->repository->getSearchService()->findLocations( $query )->totalCount;

        self::assertInstanceOf('\\eZ\\Publish\\Core\\Repository\\Values\\User\\User', $user);
        self::assertEquals($countUsers + 1, $newCountUsers);
        self::assertEquals('new_user_login', $user->login);
        self::assertEquals('new_user_email@ez.no', $user->email);
    }
}
