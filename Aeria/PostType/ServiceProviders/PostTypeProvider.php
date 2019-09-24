<?php

namespace Aeria\PostType\ServiceProviders;

use Aeria\Container\Interfaces\ServiceProviderInterface;
use Aeria\PostType\PostType;
use Aeria\Container\Container;
use Aeria\Config\Config;
use Aeria\PostType\Models\PostTypeModel;
/**
 * PostTypeProvider is in charge of registering the post type service
 * to the container
 * 
 * @category PostType
 * @package  Aeria
 * @author   Jacopo Martinelli <jacopo.martinelli@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
class PostTypeProvider implements ServiceProviderInterface
{
    /**
     * Registers the service to the provided container
     *
     * @param Container $container Aeria's container
     *
     * @return void
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function register(Container $container)
    {
        $container->bind('post_type', PostType::class);
    }
    /**
     * In charge of booting the service. PostType doesn't need any additional operation.
     *
     * @param Container $container Aeria's container
     *
     * @return bool true: service booted
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function boot(Container $container): bool
    {
        return true;
    }
}
