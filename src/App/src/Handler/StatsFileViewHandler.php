<?php

declare(strict_types=1);

namespace App\Handler;

use Doctrine\DBAL\Connection;
use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Router;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class StatsFileViewHandler implements RequestHandlerInterface
{
    /** @var string */
    private $containerName;

    /** @var Router\RouterInterface */
    private $router;

    /** @var null|TemplateRendererInterface */
    private $template;

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(
        string $containerName,
        Router\RouterInterface $router,
        Connection $connection,
        ?TemplateRendererInterface $template = null
    ) {
        $this->containerName = $containerName;
        $this->router        = $router;
        $this->connection    = $connection;
        $this->template      = $template;
    }

    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        $filename = $request->getAttribute('filename') . ".mp3";

        $queryBuilder = $this->connection->createQueryBuilder();

        $queryBuilder->select('date', 'COUNT(id) as downloads')
            ->from('logs')
            ->where('file = ?')
            ->groupBy('date')
            ->setParameter(0, $filename);

        $logs = $queryBuilder->execute();

        $queryBuilder->select('date', 'COUNT(DISTINCT(ip)) as unique_downloads')
            ->from('logs')
            ->where('file = ?')
            ->groupBy('date')
            ->setParameter(0, $filename);

        $logsUnique = $queryBuilder->execute();

        $data = ['logs' => $logs, 'logsUnique' => $logsUnique, 'filename' => $filename];

        return new HtmlResponse($this->template->render('app::stats-file-view', $data));
    }
}
