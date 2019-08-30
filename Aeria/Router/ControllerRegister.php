<?php

namespace Aeria\Router;

use Aeria\Structure\Traits\DictionaryTrait;

class ControllerRegister
{
    use DictionaryTrait;

    public function register(string $namespace)
    {
        $name = $this->classNameFromNamespace($namespace);
        if ($this->exists($name)) {
          throw new \Exception("The controller named {$name} has been already registered");
        }
        $this->set($name, $namespace);
    }

    private function classNameFromNamespace(string $namespace): string
    {
        $list = explode('\\', $namespace);
        return $list[\count($list) - 1];
    }

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
        return $controller->{$method}();
    }

    public function getControllerPrefix(string $name)
    {
        if (!$this->exists($name)) {
            throw new \Exception("The controller named {$name} has not been registered");
        }
        $namespace = $this->get($name);
        return call_user_func("{$namespace}::getPrefix");
    }

}
