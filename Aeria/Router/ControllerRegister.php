<?php

namespace Aeria\Router;

use Aeria\Structure\Traits\DictionaryTrait;

/**
 * ControllerRegister manages a register of controllers.
 *
 * @category Router
 *
 * @author   Jacopo Martinelli <jacopo.martinelli@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 *
 * @see     https://github.com/caffeinalab/aeria
 */
class ControllerRegister
{
    use DictionaryTrait;

    /**
     * Registers a controller to the register.
     *
     * @param string $namespace the controller's namespace
     *
     * @throws \Exception when the controller was already registered
     *
     * @since  Method available since Release 3.0.0
     */
    public function register(string $namespace)
    {
        $name = $this->classNameFromNamespace($namespace);
        if ($this->exists($name)) {
            throw new \Exception("The controller named {$name} has been already registered");
        }
        $this->set($name, $namespace);
    }

    /**
     * Helper method that gets the classname.
     *
     * @param string $namespace the controller's namespace
     *
     * @since  Method available since Release 3.0.0
     */
    private function classNameFromNamespace(string $namespace): string
    {
        $list = explode('\\', $namespace);

        return $list[\count($list) - 1];
    }

    /**
     * Calls a method on a controller.
     *
     * @param Request $request the request object
     * @param string  $name    the controller name
     * @param string  $method  the method name
     *
     * @return mixed the method's response
     *
     * @throws \Exception if the controller isn't found
     * @throws \Exception if the controller doesn't provide the requested method
     *
     * @since  Method available since Release 3.0.0
     */
    public function callOn(Request $request, string $name, string $method)
    {
        if (!$this->exists($name)) {
            throw new \Exception("The controller named {$name} has not been registered");
        }
        $namespace = $this->get($name);
        $controller = new $namespace($request);

        if (!method_exists($controller, $method)) {
            throw new \Exception("The controller named {$name} does not have the method called {$method}");
        }

        return $controller->{$method}($request);
    }

    /**
     * Get a prefix from a controller.
     *
     * @param string $name the controller's name
     *
     * @return string the controller prefix
     *
     * @since  Method available since Release 3.0.0
     */
    public function getControllerPrefix(string $name)
    {
        if (!$this->exists($name)) {
            throw new \Exception("The controller named {$name} has not been registered");
        }
        $namespace = $this->get($name);

        return call_user_func("{$namespace}::getPrefix");
    }
}
