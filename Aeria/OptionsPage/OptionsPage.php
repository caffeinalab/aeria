<?php

namespace Aeria\OptionsPage;

use Aeria\OptionsPage\OptionsPageProcessor;
use Aeria\Field\FieldError;
use Aeria\RenderEngine\RenderEngine;

class OptionsPage
{

    private $optionPages = [];

    private static function nonceIDs($config)
    {
        return [
          'action' => 'update-',
          'field' => 'update_aeria_settings'
        ];
    }

    public function register($optionPage)
    {
        // Check if all the required fields are available
        if(isset($optionPage["title"])
            &&isset($optionPage["menu_title"])
            &&isset($optionPage["capability"])
            &&isset($optionPage["menu_slug"])
            &&isset($optionPage["config"])
            &&isset($optionPage["sections"])
            &&isset($optionPage["validator_service"])
            &&isset($optionPage["query_service"])
        )
            $this->optionPages[]=$optionPage;
            return null;
    }

    public function boot ($aeriaConfig, $render_service)
    {
        $default_icon_data = file_get_contents(dirname(__DIR__).'/aeria.svg');
        $default_icon = 'data:image/svg+xml;base64,'.base64_encode($default_icon_data);
        // Registering aeria's editor page
        if (empty($GLOBALS['admin_page_hooks']["aeria_options"])) {
            add_menu_page(
                "Aeria editor",
                "Aeria",
                "manage_options",
                "aeria_options",
                '',
                $default_icon
            );        
        }
        add_submenu_page(
            "aeria_options",
            "Editor",
            "Editor",
            "manage_options",
            "aeria_options",
            function () use ($aeriaConfig, $render_service) {
                static::renderHTML(
                    "aeria_editor",
                    $aeriaConfig,
                    $render_service
                );
            }
        );
        // Registering other option pages
        foreach ($this->optionPages as $singleOptionPage) {
        // Check if nav menu parent exists
            if (empty($GLOBALS['admin_page_hooks'][$singleOptionPage["parent"]])) {
                add_menu_page(
                    $singleOptionPage["title"],
                    $singleOptionPage["parent_title"],
                    $singleOptionPage["capability"],
                    $singleOptionPage["parent"],
                    '',
                    $singleOptionPage["parent_icon"]
                );
                $singleOptionPage["menu_slug"] = $singleOptionPage["parent"];
            }
            add_submenu_page(
                $singleOptionPage["parent"],
                $singleOptionPage["title"],
                $singleOptionPage["menu_title"],
                $singleOptionPage["capability"],
                $singleOptionPage["menu_slug"],
                function () use ($singleOptionPage, $render_service) {
                    // If we're saving the fields, POST will be instantiated
                    if (isset($_POST)) {
                        static::save(
                            $singleOptionPage["menu_slug"],
                            $singleOptionPage["config"],
                            $singleOptionPage["validator_service"],
                            $singleOptionPage["query_service"],
                            $singleOptionPage["sections"]
                        );
                    }
                    static::renderHTML(
                        $singleOptionPage["menu_slug"],
                        $singleOptionPage["config"],
                        $render_service,
                        $singleOptionPage["validator_service"],
                        $singleOptionPage["query_service"],
                        $singleOptionPage["sections"]
                    );
                }
            );
        }
    }

    public static function renderHTML($id, $config, $render_service, $validator_service = null,  $query_service = null, $sections = null)
    {
        if ($id == "aeria_editor") {
            $render_service->render(
                'editor_template',
                [
                    "config" => $config,
                ]
            );
            return null;
        }
        $nonceIDs = static::nonceIDs($config);
        $processor = new OptionsPageProcessor($id, $config, $sections);
        $config["fields"] = $processor->getAdmin();
                $render_service->render(
                    'option_template',
                    [
                        "config" => $config,
                        "nonceIDs" => $nonceIDs
                    ]
                );
    }


    public static function save($id, $metabox, $validator_service, $query_service, $sections)
    {
            if ($_POST==[])
            {
                return null;
            }
            $nonceIDs = static::nonceIDs($metabox);

            if ( (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
                || !check_admin_referer($nonceIDs['action'], $nonceIDs['field'])
                || !current_user_can("manage_options"))
            {
                return null;
            }
            $processor = new OptionsPageProcessor($id, $metabox, $sections, $_POST);
            $processor->set(
                $validator_service,
                $query_service
            );
    }
}
