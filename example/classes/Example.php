<?php

class Example extends Plugin {
    public static function init() {
        self::adminMenu("Example Plugin", array(
            "link" => "exampleplugin_admin.php",
            "icon" => "fa-globe",
            "permission" => "admin"
        ));
    }

    public static function sayhello() {
        echo 'Hello from the <code>ExamplePlugin</code> class!';
    }
}