<?php

class SecurityPlugin {

    /**
     * @var DB
     */
    private static $_db;

    /**
     * @var array
     */
    private static $_listeners = [];

    /**
     * @var array
     */
    public static $weakpasswords;

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
            "link" => "/admin/security_plugin.php",
            "permission" => "staffmanage",
        ]);

        self::$weakpasswords = [
            "password" => 5, 
            "passw0rd" => 3, 
            "12345678" => 5, 
            "qwertyuio" => 2,
            str_replace(' ', '', strtolower(Config::get('va/name'))) => 3, 
            "flare" => 1,
            "vanet" => 1,
            "123456789" => 2,
            "1234567890" => 2,
            "infiniteflight" => 2,
            "virtualairline" => 3,
            "asdfghjk" => 2,
            "password!" => 4,
            "passw0rd!" => 3,
            "iamthebest" => 4
        ];

        if (date("i") % 30 == 0 || file_exists(__DIR__.'/../.development')) {
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
     * @return array
     */
    public static function activeTemps()
    {
        self::setup();
        self::deleteOld();

        $data = self::$_db->getAll('temppass');

        return $data->results();
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
            Redirect::to('/temppass.php');
        }
    }

    /**
     * @return array
     */
    public static function checkUsers()
    {
        self::setup();

        $users = self::$_db->get('pilots', ['status', '=', 1])->results();
        $users = array_map(function($u) {
            $u->vulnerable = 0;
            foreach (self::$weakpasswords as $pass => $threat) {
                if (Hash::check($pass, $u->password)) {
                    $u->vulnerable = $threat;
                }
            }

            $ret = new stdClass();
            $ret->name = $u->name;
            $ret->id = $u->id;
            $ret->threat = $u->vulnerable;
            return $ret;
        }, $users);

        return $users;
    }

    /**
     * @return array
     * @param string $string String to Check
     * @param bool $super Whether to run extra checks
     */
    public static function getThreat($string, $super = false)
    {
        foreach (self::$weakpasswords as $pass => $threat) {
            if (Hash::check($pass, $string)) {
                return [$threat, "Common Password"];
            }
        }

        if (preg_match("/[0-9]/", $string) != 1) {
            return [5, "No Number"];
        } elseif (preg_match("/[A-Z]/", $string) != 1) {
            return [4, "No Upper-Case Letter"];            
        } elseif ($super && preg_match("/\S/", $string) != 1) {
            return [3, "No Special Character"];
        } elseif (strlen($string) < 10 && $super) {
            return [3, "Less than 10 Characters Long"];
        } elseif (strlen($string) < 8 && !$super) {
            return [4, "Less than 8 Characters Long"];
        }

        return [0, "Low Threat"];
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