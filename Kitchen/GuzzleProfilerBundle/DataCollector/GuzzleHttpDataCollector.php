<?php
/**
 * Created by PhpStorm.
 * User: ksk
 * Date: 27.01.16
 * Time: 13:32.
 */
namespace Kitchen\GuzzleProfilerBundle\DataCollector;

use Kitchen\GuzzleProfilerBundle\Middleware\Profiler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpKernel\DataCollector\LateDataCollectorInterface;

class GuzzleHttpDataCollector extends DataCollector implements LateDataCollectorInterface
{
    /**
     * @var Profiler
     */
    protected $profiler;

    /**
     * GuzzleHttpDataCollector constructor.
     *
     * @param $profiler
     */
    public function __construct(Profiler $profiler)
    {
        $this->profiler = $profiler;
    }

    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
    }

    public function lateCollect()
    {
        $this->data = $this->profiler;
    }

    public function getCount()
    {
        return $this->data->getTotalCount();
    }

    public function getErrors()
    {
        return $this->data->getTotalError();
    }

    public function getTime()
    {
        return round($this->data->getTotalTime() * 1000, 1);
    }

    /**
     * @return array
     */
    public function getRequests()
    {
        return $this->data->data;
    }

    public function getName()
    {
        return 'app.guzzle_http';
    }
}
