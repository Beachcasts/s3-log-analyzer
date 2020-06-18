<?php

declare(strict_types=1);

namespace App\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Router;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class StatsViewHandler implements RequestHandlerInterface
{
    /** @var string */
    private $containerName;

    /** @var Router\RouterInterface */
    private $router;

    /** @var null|TemplateRendererInterface */
    private $template;

    private $connection;

    public function __construct(
        string $containerName,
        Router\RouterInterface $router,
        $connection,
        ?TemplateRendererInterface $template = null
    ) {
        $this->containerName = $containerName;
        $this->router        = $router;
        $this->connection    = $connection;
        $this->template      = $template;
    }

    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        $logs = $this->connection->query(
            "SELECT `date`, COUNT(`id`) as `downloads` FROM `logs` GROUP BY `date`"
        );

        $files = $this->connection->query(
            "SELECT `file`, COUNT(`id`) as `downloads` FROM `logs` GROUP BY `file` ORDER BY `file` DESC"
        );

        $filesUnique = $this->connection->query(
            "SELECT `file`, COUNT(DISTINCT(`ip`)) as `downloads` FROM `logs` GROUP BY `file` ORDER BY `file` DESC"
        );

        $data = ['logs' => $logs, 'files' => $files, 'filesUnique' => $filesUnique];

        return new HtmlResponse($this->template->render('app::stats-view', $data));
    }
}
