<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authentication-session for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authentication-session/blob/master/LICENSE.md New BSD License
 */
namespace ZendTest\Expressive\Authentication\Session;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Expressive\Authentication\Session\PhpSession;
use Zend\Expressive\Authentication\Session\PhpSessionFactory;
use Zend\Expressive\Authentication\UserRepositoryInterface;

class PhpSessionFactoryTest extends TestCase
{
    protected function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->factory = new PhpSessionFactory();
        $this->userRegister = $this->prophesize(UserRepositoryInterface::class);
        $this->responsePrototype = $this->prophesize(ResponseInterface::class);
    }

    /**
     * @expectedException Zend\Expressive\Authentication\Exception\InvalidConfigException
     */
    public function testInvokeWithEmptyContainer()
    {
        $phpSession = ($this->factory)($this->container->reveal());
    }

    /**
     * @expectedException Zend\Expressive\Authentication\Exception\InvalidConfigException
     */
    public function testInvokeWithContainerEmptyConfig()
    {
        $this->container
            ->has(UserRepositoryInterface::class)
            ->willReturn(true);
        $this->container
            ->get(UserRepositoryInterface::class)
            ->willReturn($this->userRegister->reveal());
        $this->container
            ->has(ResponseInterface::class)
            ->willReturn(true);
        $this->container
            ->get(ResponseInterface::class)
            ->willReturn($this->responsePrototype->reveal());
        $this->container
            ->get('config')
            ->willReturn([]);

        $phpSession = ($this->factory)($this->container->reveal());
    }

    public function testInvokeWithContainerAndConfig()
    {
        $this->container
            ->has(UserRepositoryInterface::class)
            ->willReturn(true);
        $this->container
            ->get(UserRepositoryInterface::class)
            ->willReturn($this->userRegister->reveal());
        $this->container
            ->has(ResponseInterface::class)
            ->willReturn(true);
        $this->container
            ->get(ResponseInterface::class)
            ->willReturn($this->responsePrototype->reveal());
        $this->container
            ->get('config')
            ->willReturn([
                'authentication' => ['redirect' => '/login'],
            ]);

        $phpSession = ($this->factory)($this->container->reveal());
        $this->assertInstanceOf(PhpSession::class, $phpSession);
    }
}
