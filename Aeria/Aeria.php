<?php
namespace Aeria;

use Aeria\Config\Config;
use Aeria\Container\Container;
use Aeria\Container\Interfaces\{
    ContainerInterface,
    ServiceProviderInterface
};
use Aeria\Config\ServiceProviders\ConfigProvider;
use Aeria\PostType\ServiceProviders\PostTypeProvider;
use Aeria\Taxonomy\ServiceProviders\TaxonomyProvider;
use Aeria\Field\ServiceProviders\FieldProvider;
use Aeria\Meta\ServiceProviders\MetaProvider;
use Aeria\OptionsPage\ServiceProviders\OptionsPageServiceProvider;
use Aeria\Kernel\ServiceProviders\KernelServiceProvider;
use Aeria\Action\ServiceProviders\ActionProvider;
use Aeria\Validator\ServiceProviders\ValidatorServiceProvider;
use Aeria\Router\ServiceProviders\RouterServiceProvider;
use Aeria\Query\ServiceProviders\QueryServiceProvider;
use Aeria\Updater\ServiceProviders\UpdaterServiceProvider;
use Aeria\Router\ServiceProviders\ControllerServiceProvider;
use Aeria\RenderEngine\ServiceProviders\RenderEngineServiceProvider;

/**
 * Aeria is a lightweight and modular WP development tool.
 * 
 * @category Action
 * @package  Aeria
 * @author   Caffeina Devs <dev@caffeinalab.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
class Aeria extends Container
{
    public const VERSION = '3.0.18';
    /**
     * Constructs the Aeria container
     * 
     * @return void
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function __construct()
    {
        $this->registerBindings();
        $this->registerServiceProviders();
    }
    /**
     * Registers Aeria's bindings
     * 
     * @return void
     *
     * @access protected
     * @since  Method available since Release 3.0.0
     */
    protected function registerBindings()
    {
        static::setInstance($this);

        $this->singleton('aeria', $this);

        $this->bind(Container::class, $this);
    }
    /**
     * Registers all the required ServiceProviders to the container
     *
     * @return void
     *
     * @access protected
     * @since  Method available since Release 3.0.0
     */
    protected function registerServiceProviders() // : void
    {
        $this->register(new ConfigProvider());
        $this->register(new PostTypeProvider());
        $this->register(new TaxonomyProvider());
        $this->register(new FieldProvider());
        $this->register(new MetaProvider());
        $this->register(new ActionProvider());
        $this->register(new KernelServiceProvider());
        $this->register(new ValidatorServiceProvider());
        $this->register(new QueryServiceProvider());
        $this->register(new RouterServiceProvider());
        $this->register(new ControllerServiceProvider());
        $this->register(new UpdaterServiceProvider());
        $this->register(new OptionsPageServiceProvider());
        $this->register(new RenderEngineServiceProvider());
    }
    /**
     * Returns Aeria's version
     * 
     * @return string the version
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function version() : string
    {
        return static::VERSION;
    }
    /**
     * Returns Aeria's instance
     * 
     * @return Aeria the instance
     *
     * @access public
     * @static
     * @since  Method available since Release 3.0.0
     */
    public static function getInstance() : ContainerInterface
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }

        return static::$instance;
    }
    /**
     * Sets Aeria's instance
     * 
     * @param ContainerInterface $container the container
     * 
     * @return ContainerInterface the instance we've set
     *
     * @access public
     * @static
     * @since  Method available since Release 3.0.0
     */
    public static function setInstance(
        ContainerInterface $container
    ) : ContainerInterface {
        static::$instance = $container;

        return static::$instance;
    }
}
