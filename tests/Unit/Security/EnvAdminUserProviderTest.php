<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security;

use App\Security\EnvAdminUserProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Symfony\Component\Security\Core\User\UserInterface;

final class EnvAdminUserProviderTest extends TestCase
{
    private EnvAdminUserProvider $provider;

    protected function setUp(): void
    {
        $this->provider = new EnvAdminUserProvider('the-admin');
    }

    public function testLoadsAdminUserWithAdminRole(): void
    {
        $user = $this->provider->loadUserByIdentifier('the-admin');

        self::assertSame('the-admin', $user->getUserIdentifier());
        self::assertContains('ROLE_ADMIN', $user->getRoles());
    }

    public function testRejectsUnknownUsername(): void
    {
        $this->expectException(UserNotFoundException::class);
        $this->provider->loadUserByIdentifier('intruder');
    }

    public function testRefreshReturnsEquivalentUser(): void
    {
        $user = new InMemoryUser('the-admin', null, ['ROLE_ADMIN']);

        $refreshed = $this->provider->refreshUser($user);

        self::assertSame('the-admin', $refreshed->getUserIdentifier());
        self::assertContains('ROLE_ADMIN', $refreshed->getRoles());
    }

    public function testRefreshRejectsUserThatNoLongerMatchesEnv(): void
    {
        // Simulates ADMIN_USERNAME having been rotated: old sessions must die.
        $this->expectException(UserNotFoundException::class);
        $this->provider->refreshUser(new InMemoryUser('old-admin', null, ['ROLE_ADMIN']));
    }

    public function testRefreshRejectsForeignUserImplementations(): void
    {
        $foreignUser = $this->createStub(UserInterface::class);

        $this->expectException(UnsupportedUserException::class);
        $this->provider->refreshUser($foreignUser);
    }

    public function testSupportsOnlyInMemoryUser(): void
    {
        self::assertTrue($this->provider->supportsClass(InMemoryUser::class));
        self::assertFalse($this->provider->supportsClass(UserInterface::class));
    }
}
