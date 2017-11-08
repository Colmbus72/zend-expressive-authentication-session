<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authentication-session for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authentication-session/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Expressive\Authentication\Session;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Expressive\Authentication\AuthenticationInterface;
use Zend\Expressive\Authentication\UserInterface;
use Zend\Expressive\Authentication\UserRepositoryInterface;

class PhpSession implements AuthenticationInterface
{
    /**
     * @var UserRepositoryInterface
     */
    protected $repository;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var ResponseInterface
     */
    protected $responsePrototype;

    /**
     * Constructor
     *
     * @param UserRepositoryInterface $repository
     * @param array $config
     * @param ResponseInterface $responsePrototype
     */
    public function __construct(
        UserRepositoryInterface $repository,
        array $config,
        ResponseInterface $responsePrototype
    ) {
        $this->repository = $repository;
        $this->config = $config;
        $this->responsePrototype = $responsePrototype;
    }

    /**
     * {@inheritDoc}
     * @todo Refactor to use zend-expressive-session
     */
    public function authenticate(ServerRequestInterface $request) : ?UserInterface
    {
        $cookies = $request->getCookieParams();
        if (isset($cookies[AuthenticationInterface::class])) {
            $this->setSessionId($cookies[AuthenticationInterface::class]);
            if (isset($_SESSION[UserInterface::class]) &&
                $_SESSION[UserInterface::class] instanceof UserInterface) {
                return $_SESSION[UserInterface::class];
            }
            return null;
        }

        if ('POST' !== strtoupper($request->getMethod())) {
            return null;
        }

        $params = $request->getParsedBody();
        $username = $this->config['username'] ?? 'username';
        $password = $this->config['password'] ?? 'password';
        if (!isset($params[$username]) || !isset($params[$password])) {
            return null;
        }

        $user = $this->repository->authenticate(
            $params[$username],
            $params[$password]
        );

        if (null !== $user) {
            $this->setSessionId(bin2hex(random_bytes(20)));
            $_SESSION[UserInterface::class] = $user;
        }

        return $user;
    }

    /**
     * {@inheritDoc}
     */
    public function unauthorizedResponse(ServerRequestInterface $request): ResponseInterface
    {
        return $this->responsePrototype->withHeader(
            'Location',
            $this->config['redirect']
        )->withStatus(301);
    }

    /**
     * Set the PHP SESSION ID
     *
     * @param string $id
     * @return void
     */
    private function setSessionId(string $id): void
    {
        session_name(AuthenticationInterface::class);
        session_id($id);
        session_start();
    }
}
