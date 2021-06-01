<?php

class ExamplePlugin
{
    public static function init()
    {
        Plugin::adminMenu("Example Plugin", array(
            "link" => "/admin/example",
            "icon" => "fa-globe",
            "permission" => "admin"
        ));
        Router::add('/admin/example', [new ExamplePluginController, 'get']);
    }

    public static function sayhello()
    {
        echo 'Hello from the <code>ExamplePlugin</code> class!';
    }
}
