<?php
/**
 * Created by PhpStorm.
 * User: ksk
 * Date: 27.01.16
 * Time: 18:21.
 */
namespace Kitchen\GuzzleProfilerBundle\Middleware;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;

class Configurator
{
    protected $middleware;

    /**
     * Configurator constructor.
     *
     * @param $middleware
     */
    public function __construct($middleware)
    {
        $this->middleware = $middleware;
    }

    public function configure(ClientInterface $client)
    {
        $stack = $client->getConfig('handler');
        if ($stack instanceof HandlerStack) {
            $stack->push($this->middleware);
        }
    }
}
