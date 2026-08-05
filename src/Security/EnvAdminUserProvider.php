<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Provides the single admin user defined by the ADMIN_USERNAME environment
 * variable. Also used to refresh the user from the session on every request —
 * changing ADMIN_USERNAME therefore invalidates existing sessions.
 *
 * @implements UserProviderInterface<InMemoryUser>
 */
final class EnvAdminUserProvider implements UserProviderInterface
{
    public function __construct(
        #[Autowire(env: 'ADMIN_USERNAME')]
        private readonly string $adminUsername,
    ) {
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        if (!hash_equals($this->adminUsername, $identifier)) {
            throw new UserNotFoundException('Invalid credentials.');
        }

        return new InMemoryUser($identifier, null, ['ROLE_ADMIN']);
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof InMemoryUser) {
            throw new UnsupportedUserException(\sprintf('Instances of "%s" are not supported.', $user::class));
        }

        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    public function supportsClass(string $class): bool
    {
        return InMemoryUser::class === $class || is_subclass_of($class, InMemoryUser::class);
    }
}
