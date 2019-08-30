<?php

namespace Aeria\Input;
use Closure;
class Input
{
    public function render(array $config): Closure
    {
        $conf_json = json_encode($config);
        return function ($post) use ($conf_json) {
            return "
                <div
                    aeria-settings={$conf_json}
                >
                </div>
            ";
        };
    }
}
