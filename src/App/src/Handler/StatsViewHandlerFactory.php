<?php

declare(strict_types=1);

namespace App\Handler;

use Mezzio\Router\RouterInterface;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Configuration;

use function get_class;

class StatsViewHandlerFactory
{
    public function __invoke(ContainerInterface $container) : RequestHandlerInterface
    {
        $router   = $container->get(RouterInterface::class);
        $template = $container->has(TemplateRendererInterface::class)
            ? $container->get(TemplateRendererInterface::class)
            : null;

        $credentials = $container->get('config')['db'];

        $connection = DriverManager::getConnection($credentials, new Configuration());

        return new StatsViewHandler(get_class($container), $router, $connection, $template);
    }
}
