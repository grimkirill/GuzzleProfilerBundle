<?php
/**
 * Created by PhpStorm.
 * User: ksk
 * Date: 27.01.16
 * Time: 18:25.
 */
namespace Kitchen\GuzzleProfilerBundle\Middleware;

use GuzzleHttp\Promise\RejectedPromise;
use GuzzleHttp\TransferStats;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class Profiler
{
    public $data = [];
    public $totalTime = 0;
    public $totalCount = 0;
    public $totalError = 0;

    /**
     * @var Stopwatch
     */
    protected $stopwatch;

    /**
     * Profiler constructor.
     * @param Stopwatch $stopwatch
     */
    public function __construct(Stopwatch $stopwatch)
    {
        $this->stopwatch = $stopwatch;
    }

    public function __invoke(callable $handler)
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            $info = [
                'start' => microtime(true),
                'method' => $request->getMethod(),
                'uri' => (string) $request->getUri(),
                'body' => (string) $request->getBody(),
            ];
            $requestId = 'Guzzle HTTP ' . $request->getMethod() . ' ' . $request->getUri();
            $event = $this->stopwatch->start($requestId);

            $originStats = null;
            if (array_key_exists('on_stats', $options)) {
                $originStats = $options['on_stats'];
            }
            $options['on_stats'] = function (TransferStats $stats) use ($info, $originStats) {
                $info['stats'] = $stats->getHandlerStats();
                if ($originStats && is_callable($originStats)) {
                    $originStats($stats);
                }
            };
            return $handler($request, $options)->then(
                function (ResponseInterface $response) use ($info, $event) {
                    $info['end'] = microtime(true);
                    $time = $info['end'] - $info['start'];
                    $info['time'] = round($time * 1000, 1);
                    $info['response_code'] = $response->getStatusCode();
                    $info['response_body'] = (string) $response->getBody();

                    $this->data[] = $info;
                    $this->totalTime += $time;
                    ++$this->totalCount;
                    $event->stop();

                    return $response;
                },
                function (\Exception $e) use ($info, $event) {
                    $info['end'] = microtime(true);
                    $time = $info['end'] - $info['start'];
                    $info['time'] = round($time * 1000, 1);
                    $info['exception'] = $e->getMessage();
                    $this->data[] = $info;
                    $this->totalTime += $time;
                    ++$this->totalCount;
                    ++$this->totalError;
                    $event->stop();
                    return new RejectedPromise($e);
                }
            );
        };
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return int
     */
    public function getTotalTime()
    {
        return $this->totalTime;
    }

    /**
     * @return int
     */
    public function getTotalCount()
    {
        return $this->totalCount;
    }

    /**
     * @return int
     */
    public function getTotalError()
    {
        return $this->totalError;
    }
}
