<?php

namespace Aeria\Action\Enqueuers;

use Aeria\Action\Interfaces\EnqueuerInterface;
use Aeria\Container\Container;
use Closure;

/**
 * ScriptsEnqueuer is in charge of enqueuing scripts to WP
 * 
 * @category Action
 * @package  Aeria
 * @author   Jacopo Martinelli <jacopo.martinelli@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
class ScriptsEnqueuer implements EnqueuerInterface
{

    protected $name;
    protected $uri;
    protected $deps;
    protected $ver;
    protected $in_footer;
    /**
     * Constructs the ScriptsEnqueuer object
     * 
     * @param string           $name      the handle
     * @param string           $path      the scripts path
     * @param array            $deps      the script dependencies
     * @param string|bool|null $ver       the script version number
     * @param bool             $in_footer whether the script has to be in 
     *                                    the head, or footer
     *
     * @return void
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
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
    /**
     * Constructs the ScriptsEnqueuer object
     * 
     * @param Container $container Aeria's container
     *
     * @return Closure the script enqueuer
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
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
