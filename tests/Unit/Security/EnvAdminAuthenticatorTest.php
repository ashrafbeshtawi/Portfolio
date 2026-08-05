<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security;

use App\Security\EnvAdminAuthenticator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;

final class EnvAdminAuthenticatorTest extends TestCase
{
    private EnvAdminAuthenticator $authenticator;

    protected function setUp(): void
    {
        $urlGenerator = $this->createStub(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')->willReturnCallback(
            static fn (string $route): string => match ($route) {
                'app_login' => '/login',
                'admin_dashboard' => '/admin',
                default => '/'.$route,
            },
        );

        $this->authenticator = new EnvAdminAuthenticator('the-password', $urlGenerator);
    }

    public function testAuthenticateBuildsPassportForSubmittedUsername(): void
    {
        $passport = $this->authenticator->authenticate($this->createLoginRequest('the-admin', 'the-password'));

        $userBadge = $passport->getBadge(UserBadge::class);
        self::assertNotNull($userBadge);
        self::assertSame('the-admin', $userBadge->getUserIdentifier());
    }

    public function testAuthenticateAcceptsCorrectPassword(): void
    {
        $passport = $this->authenticator->authenticate($this->createLoginRequest('the-admin', 'the-password'));

        $credentials = $passport->getBadge(CustomCredentials::class);
        self::assertNotNull($credentials);
        $credentials->executeCustomChecker(new InMemoryUser('the-admin', null, ['ROLE_ADMIN']));
        self::assertTrue($credentials->isResolved());
    }

    public function testAuthenticateRejectsWrongPassword(): void
    {
        $passport = $this->authenticator->authenticate($this->createLoginRequest('the-admin', 'wrong'));

        $credentials = $passport->getBadge(CustomCredentials::class);
        self::assertNotNull($credentials);

        $this->expectException(BadCredentialsException::class);
        $credentials->executeCustomChecker(new InMemoryUser('the-admin', null, ['ROLE_ADMIN']));
    }

    public function testSupportsOnlyPostToLoginRoute(): void
    {
        self::assertTrue($this->authenticator->supports($this->createLoginRequest('a', 'b')));
        self::assertFalse($this->authenticator->supports(Request::create('/login', 'GET')));
        self::assertFalse($this->authenticator->supports(Request::create('/admin', 'POST')));
    }

    public function testSuccessRedirectsToAdminDashboard(): void
    {
        $response = $this->authenticator->onAuthenticationSuccess(
            $this->createLoginRequest('the-admin', 'the-password'),
            $this->createStub(TokenInterface::class),
            'main',
        );

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/admin', $response->getTargetUrl());
    }

    private function createLoginRequest(string $username, string $password): Request
    {
        $request = Request::create('/login', 'POST', [
            '_username' => $username,
            '_password' => $password,
            '_csrf_token' => 'irrelevant-in-unit-test',
        ]);
        $request->setSession(new Session(new MockArraySessionStorage()));

        return $request;
    }
}
