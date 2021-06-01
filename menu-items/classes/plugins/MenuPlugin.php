<?php

class MenuPlugin
{

    /**
     * @var DB
     */
    private static $_db = null;

    public static $itemTypes = [
        "pilot" => "Pilot Menu",
        "top.pilots" => "Top Menu (Pilots)",
        "top.public" => "Top Menu (Public)",
    ];

    private static function setup()
    {
        self::$_db = DB::getInstance();
    }

    public static function init()
    {
        self::setup();
        Plugin::adminMenu('Custom Menu Items', [
            "link" => "/admin/menus",
            "icon" => "fa-list",
            "permission" => "admin",
        ]);
        Router::add('/admin/menus', [new MenuPluginController, 'get']);
        Router::add('/admin/menus', [new MenuPluginController, 'post'], 'post');

        $items = self::getItems();
        foreach ($items as $i) {
            if ($i->type == 'pilot') {
                Plugin::pilotMenu($i->label, [
                    "link" => $i->link,
                    "icon" => $i->icon,
                ]);
            } elseif ($i->type == 'top.public') {
                Plugin::topMenu($i->label, [
                    "link" => $i->link,
                    "icon" => $i->icon,
                    "loginOnly" => false,
                    "mobileHidden" => false,
                ]);
            } elseif ($i->type == 'top.pilots') {
                Plugin::topMenu($i->label, [
                    "link" => $i->link,
                    "icon" => $i->icon,
                    "loginOnly" => true,
                    "mobileHidden" => true,
                ]);
            }
        }
    }

    /**
     * @return bool
     * @param array $fields Item Fields
     */
    public static function addItem($fields)
    {
        self::setup();

        $res = self::$_db->insert('menu_items', $fields);
        return !($res->error());
    }

    /**
     * @return bool
     * @param int $id Item ID
     * @param array $fields Updated Item Fields
     */
    public static function editItem($id, $fields)
    {
        self::setup();

        $res = self::$_db->update('menu_items', $id, 'id', $fields);
        return !($res->error());
    }

    /**
     * @return bool
     * @param int $id Item ID
     */
    public static function deleteItem($id)
    {
        self::setup();

        $res = self::$_db->delete('menu_items', ['id', '=', $id]);
        return !($res->error());
    }

    /**
     * @return array
     */
    public static function getItems()
    {
        self::setup();

        $ret = self::$_db->getAll('menu_items');
        return $ret->results();
    }
}
