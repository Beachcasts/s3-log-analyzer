<?php

declare(strict_types=1);

namespace App\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Router;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Aws\S3\S3Client;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\Filesystem;

class LogsFetchHandler implements RequestHandlerInterface
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
        $s3Client = new S3Client([
            'credentials' => [
                'key'    => $_ENV['AWS_KEY'],
                'secret' => $_ENV['AWS_SECRET'],
            ],
            'region' => $_ENV['AWS_REGION'],
            'version' => $_ENV['AWS_VERSION'],
        ]);

        $adapter = new AwsS3Adapter($s3Client, $_ENV['AWS_S3_BUCKET_NAME']);

        $filesystem = new Filesystem($adapter);

        $logs = $filesystem->listContents('log/');

        if (!empty($logs)) {
            $i = 0;
            foreach ($logs as $log) {
                $logEntries = $this->parseLog($filesystem->read($log['path']));

                if ($this->insertLogToDb($logEntries)) {
                    $filesystem->delete($log['path']);
                }

                $i++;
            }

            $data = ['contents' => 'success', 'processed' => $i];
        } else {
            $data = ['contents' => 'failed'];
        }

        return new HtmlResponse($this->template->render('app::logs-fetch', $data));
    }

    private function parseLog($log)
    {
        $logLines = [];
        $rows = explode("\n", $log);

        foreach ($rows as $row) {
            $pattern = '/(?P<owner>\S+) (?P<bucket>\S+) (?P<time>\[[^]]*\]) (?P<ip>\S+) (?P<requester>\S+) (?P<reqid>\S+) (?P<operation>\S+) (?P<key>\S+) (?P<request>"[^"]*") (?P<status>\S+) (?P<error>\S+) (?P<bytes>\S+) (?P<size>\S+) (?P<totaltime>\S+) (?P<turnaround>\S+) (?P<referrer>"[^"]*") (?P<useragent>"[^"]*") (?P<version>\S)/';

            preg_match($pattern, $row, $matches);

            if (!empty($matches)) {
                $logLines[] = $matches;
            }
        }

        return $logLines;
    }

    private function insertLogToDb($logEntries)
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $today = date('Y-m-d H:i:s');
        $result = true;

        // insert the successful request from the log into DB
        foreach ($logEntries as $logEntry) {
            if ($logEntry['status'] >= 200 && $logEntry['status'] < 300) {
                $dateTime = $this->cleanDate($logEntry['time']);

                try {
                    $queryBuilder
                        ->insert('logs')
                        ->values(
                            [
                                'bucket' => '"'.$logEntry['bucket'].'"',
                                'date' => '"'.date('Y-m-d', $dateTime).'"',
                                'time' => '"'.date('H:i:s', $dateTime).'"',
                                'datetime' => '"'.date('Y-m-d H:i:s', $dateTime).'"',
                                'ip' => '"'.$logEntry['ip'].'"',
                                'file' => '"'.$logEntry['key'].'"',
                                'useragent' => $logEntry['useragent'],
                                'created' => '"'.$today.'"',
                            ]
                        );

                    $queryBuilder->execute();

                } catch (\Exception $e) {
                    echo 'Could not insert record: ',  $e->getMessage(), "\n";
                    $result = false;
                }
            }
        }

        return $result;
    }

    private function cleanDate($AwsDateTime)
    {
        return strtotime(str_replace(['[', ']'], '', $AwsDateTime) . ' + 5 hours');
    }
}
