<?php

class SecurityPlugin
{

    /**
     * @var DB
     */
    private static $_db;

    /**
     * @var array
     */
    private static $_listeners = [];

    private static function setup()
    {
        self::$_db = DB::getInstance();
    }

    /**
     * @return null
     */
    private static function deleteOld()
    {
        self::setup();

        $sql = "DELETE FROM temppass WHERE DATEDIFF(NOW(), dateIssued) > ?";
        self::$_db->query($sql, [Config::get('TEMPPASS_EXPIRY')]);
    }

    /**
     * @return null
     */
    public static function init()
    {
        Plugin::adminMenu('Security', [
            "icon" => "fa-shield-alt",
            "link" => "/admin/security",
            "permission" => "usermanage",
        ]);

        Router::add('/admin/security', [new SecurityPluginController, 'get_admin']);
        Router::add('/admin/security', [new SecurityPluginController, 'post_admin'], 'post');
        Router::add('/set-password', [new SecurityPluginController, 'get']);
        Router::add('/set-password', [new SecurityPluginController, 'post'], 'post');

        if (date("i") % 30 == 0 || file_exists(__DIR__ . '/../.development')) {
            self::deleteOld();
        }

        self::$_listeners['user/logged-in'] = Events::listen('user/logged-in', 'SecurityPlugin::checkTemp');
    }

    /**
     * @return bool
     * @param int $user User ID
     * @param string $pass New Password
     */
    public static function issueTemp($user, $pass)
    {
        self::setup();

        self::$_db->delete('temppass', ['pilotId', '=', $user]);
        $ins = self::$_db->insert('temppass', [
            "pilotId" => $user
        ]);

        $upd = self::$_db->update('pilots', $user, 'id', [
            'password' => Hash::make($pass)
        ]);

        if ($upd->error()) {
            self::$_db->delete('temppass', ['pilotId', '=', $user]);
        }

        return !$ins->error() && !$upd->error();
    }

    /**
     * @return null
     * @param int $id Temp ID
     * @param bool $resetpw Whether to Reset the User's Password
     */
    public static function revokeTemp($id, $resetpw = true)
    {
        self::setup();

        self::$_db->delete('temppass', ['id', '=', $id]);
        // Lock User out of their Account
        if ($resetpw) {
            self::$_db->update('pilots', $id, 'id', [
                'password' => uniqid('locked-', true)
            ]);
        }
    }

    /**
     * @return object|null
     * @param int $user User ID
     */
    public static function tempForUser($user)
    {
        self::setup();

        $ret = self::$_db->query("SELECT * FROM temppass WHERE pilotId=? AND DATEDIFF(NOW(), dateIssued) < ?", [$user, Config::get('TEMPPASS_EXPIRY')]);
        if ($ret->count() == 0) return null;

        return $ret->first();
    }

    /**
     * @return null
     * @param Event $ev
     */
    public static function checkTemp($ev)
    {
        $usr = $ev->params;
        if (self::tempForUser($usr['id']) != null) {
            Redirect::to('/set-password');
        }
    }

    /**
     * @return string
     */
    public static function randomPass($len = 10)
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890!@#$%^&*';
        $pass = [];
        $alphaLength = strlen($alphabet) - 1;
        for ($i = 0; $i < $len; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass);
    }
}
