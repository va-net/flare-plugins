<?php

class Example {
    public static function init() {
        Plugin::adminMenu("Example Plugin", array(
            "link" => "/admin/exampleplugin.php",
            "icon" => "fa-globe",
            "permission" => "admin"
        ));
    }

    public static function sayhello() {
        echo 'Hello from the <code>ExamplePlugin</code> class!';
    }
}