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


class Aeria extends Container
{
    public const VERSION = '3.0.2';

    public function __construct()
    {
        $this->registerBindings();
        $this->registerServiceProviders();
    }

    protected function registerBindings()
    {
        static::setInstance($this);

        $this->singleton('aeria', $this);

        $this->bind(Container::class, $this);
    }

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

    public function version() : string
    {
        return static::VERSION;
    }

    public static function getInstance() : ContainerInterface
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    public static function setInstance(
        ContainerInterface $container
    ) : ContainerInterface {
        static::$instance = $container;

        return static::$instance;
    }
}
