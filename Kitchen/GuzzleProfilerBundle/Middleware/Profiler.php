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

    protected $maxSize = 65536;
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

    /**
     * @param int $maxSize
     */
    public function setMaxSize($maxSize)
    {
        $this->maxSize = $maxSize;
    }

    public function __invoke(callable $handler)
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            $id = count($this->data);
            $this->data[$id] = [
                'start' => microtime(true),
                'method' => $request->getMethod(),
                'uri' => (string) $request->getUri(),
                'body' => (string) $request->getBody(),
                'req_headers' => $request->getHeaders()
            ];
            $requestId = 'Guzzle HTTP ' . $request->getMethod() . ' ' . $request->getUri();
            $event = $this->stopwatch->start($requestId);

            $originStats = null;
            if (array_key_exists('on_stats', $options)) {
                $originStats = $options['on_stats'];
            }
            $options['on_stats'] = function (TransferStats $stats) use ($id, $originStats) {
                $this->data[$id]['stats'] = $stats->getHandlerStats();
                if ($originStats && is_callable($originStats)) {
                    $originStats($stats);
                }
            };
            return $handler($request, $options)->then(
                function (ResponseInterface $response) use ($id, $event) {
                    $this->data[$id]['end'] = microtime(true);
                    $time = $this->data[$id]['end'] - $this->data[$id]['start'];
                    $this->data[$id]['time'] = round($time * 1000, 1);
                    $this->data[$id]['response_code'] = $response->getStatusCode();
                    $this->data[$id]['response_body'] = (string) $response->getBody();
                    $this->data[$id]['response_headers'] = $response->getHeaders();

                    $this->totalTime += $time;
                    ++$this->totalCount;
                    $event->stop();

                    return $response;
                },
                function (\Exception $e) use ($id, $event) {
                    $this->data[$id]['end'] = microtime(true);
                    $time = $this->data[$id]['end'] - $this->data[$id]['start'];
                    $this->data[$id]['time'] = round($time * 1000, 1);
                    $this->data[$id]['exception'] = $e->getMessage();
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
