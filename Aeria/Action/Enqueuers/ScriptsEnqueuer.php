<?php

namespace Aeria\Action\Enqueuers;

use Aeria\Action\Interfaces\EnqueuerInterface;
use Aeria\Container\Container;
use Closure;

class ScriptsEnqueuer implements EnqueuerInterface
{

    protected $name;
    protected $uri;
    protected $deps;
    protected $ver;
    protected $in_footer;

    public function __construct(
        string $name,
        string $path,
        array $deps = null,
        $ver = null,
        bool $in_footer = false
    ) {
        $this->name = $name;
        $this->uri = $path;
        $this->deps = $deps;
        $this->ver = $ver;
        $this->in_footer = $in_footer;
    }

    public function getEnqClosure(Container $container): Closure
    {
        $name = $this->name;
        $uri = $this->uri;
        $deps = $this->deps;
        $ver = $this->ver;
        $in_footer = $this->in_footer;

        $cloj = function () use ($name, $uri, $deps, $ver, $in_footer) {
            wp_enqueue_script($name, $uri, $deps, $ver, $in_footer);
        };
        return $cloj;
    }
}
