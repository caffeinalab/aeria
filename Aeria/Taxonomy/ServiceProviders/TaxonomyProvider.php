<?php

namespace Aeria\Taxonomy\ServiceProviders;

use Aeria\Container\Interfaces\ServiceProviderInterface;
use Aeria\Taxonomy\Taxonomy;
use Aeria\Container\Container;

/**
 * TaxonomyProvider is in charge of registering the taxonomy service
 * to the container
 * 
 * @category Taxonomy
 * @package  Aeria
 * @author   Jacopo Martinelli <jacopo.martinelli@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
class TaxonomyProvider implements ServiceProviderInterface
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
        $container->bind('taxonomy', Taxonomy::class);
    }
    /**
     * In charge of booting the service. Taxonomy doesn't need any additional operation.
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
