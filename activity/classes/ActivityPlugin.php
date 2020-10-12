<?php
class ActivityPlugin {

    /**
     * @var DB
     */
    private static $_db = null;
    private static $_activePeriod = null;
    private static $_newPeriod = null;
    
    private static function setup()
    {
        if (!isset(self::$_db)) self::$_db = DB::getInstance();
        if (!isset(self::$_activePeriod)) self::$_activePeriod = Config::get('ACTIVE_DAYS');
        if (!isset(self::$_newPeriod)) self::$_newPeriod = Config::get('NEW_DAYS');
    }
    
    /**
     * @return null
     */
    public static function init()
    {
        $GLOBALS['admin-menu']['User Management']['Activity'] = [
            "link" => "/admin/activity.php",
            "icon" => "fa-clock",
            "permission" => "usermanage",
            "needsGold" => false
        ];
        $GLOBALS['admin-menu']['User Management']['LOA Admin'] = [
            "link" => "/admin/loa.php",
            "icon" => "fa-briefcase",
            "permission" => "usermanage",
            "needsGold" => false
        ];
        Plugin::adminMenu('Activity Settings', [
            "link" => "/admin/activity_settings.php",
            "icon" => "fa-users-cog",
            "permission" => "usermanage",
        ]);
        Plugin::pilotMenu('Leave of Absence', [
            "link" => "/leave.php",
            "icon" => "fa-laptop-house",
        ]);
    }

    /**
     * @return array
     */
    public static function activePilots()
    {
        self::setup();
        $activePeriod = self::$_activePeriod;

        $sql = "SELECT * FROM pilots WHERE id IN (SELECT pilotid FROM pireps WHERE DATEDIFF(NOW(), date) <= {$activePeriod} AND status=1) 
                AND status=1 AND NOT id IN (SELECT pilotid FROM leave_absense WHERE status=1 AND fromdate >= NOW() AND todate <= NOW())";
        $data = self::$_db->query($sql);

        return $data->results();
    }

    /**
     * @return array;
     */
    public static function inactivePilots()
    {
        self::setup();
        $activePeriod = self::$_activePeriod;
        $newPeriod = self::$_newPeriod;

        $sql = "SELECT * FROM pilots WHERE NOT id IN (SELECT pilotid FROM pireps WHERE DATEDIFF(NOW(), date) <= {$activePeriod} AND status=1) 
                AND status=1 AND DATEDIFF(NOW(), joined) > {$newPeriod} AND NOT id IN 
                (SELECT pilotid FROM leave_absense WHERE status=1 AND fromdate <= NOW() AND todate >= NOW())";
        $data = self::$_db->query($sql);

        return $data->results();
    }

    /**
     * @return array
     */
    public static function newPilots()
    {
        self::setup();
        $activePeriod = self::$_activePeriod;
        $newPeriod = self::$_newPeriod;

        $sql = "SELECT * FROM pilots WHERE NOT id IN (SELECT pilotid FROM pireps WHERE DATEDIFF(NOW(), date) <= {$activePeriod} AND status=1) 
                AND status=1 AND DATEDIFF(NOW(), joined) <= {$newPeriod} AND NOT id IN 
                (SELECT pilotid FROM leave_absense WHERE status=1 AND fromdate >= NOW() AND todate <= NOW())";
        $data = self::$_db->query($sql);

        return $data->results();
    }

    /**
     * @return array
     */
    public static function retiredPilots()
    {
        self::setup();

        $sql = "SELECT * FROM pilots WHERE id IN (SELECT pilotid FROM pireps) AND status=2";
        $data = self::$_db->query($sql);

        return $data->results();
    }

    /**
     * @return array
     */
    public static function pilotsOnLeave()
    {
        self::setup();

        $sql = "SELECT * FROM pilots WHERE id IN (SELECT pilotid FROM leave_absense WHERE status=1 AND fromdate <= NOW() AND todate >= NOW())";
        $data = self::$_db->query($sql);

        return $data->results();
    }

    /**
     * @return null
     * @param int $pirep Updated ACTIVE_DAYS Value
     * @param int $new Updated NEW_DAYS Value
     */
    public static function updateSettings($pirep, $new)
    {
        Config::replace('ACTIVE_DAYS', $pirep);
        Config::replace('NEW_DAYS', $new);
    }

    /**
     * @return bool
     * @param array $fields Leave Request Fields
     */
    public static function fileLeave($fields)
    {
        self::setup();

        $ret = self::$_db->insert('leave_absense', $fields);

        return !($ret->error());
    }

    /**
     * @return array
     * @param int $user User ID
     */
    public static function userReqs($user)
    {
        self::setup();

        $res = self::$_db->get('leave_absense', array('pilotid', '=', $user));

        return $res->results();
    }

    /**
     * @return array
     */
    public static function pendingReqs()
    {
        self::setup();

        $res = self::$_db->query("SELECT l.*, p.name AS pilot FROM leave_absense l INNER JOIN pilots p ON l.pilotid=p.id WHERE l.status=0");

        return $res->results();
    }

    /**
     * @return array
     */
    public static function currentFutureLeave()
    {
        self::setup();

        $res = self::$_db->query("SELECT l.*, p.name AS pilot FROM leave_absense l INNER JOIN pilots p ON l.pilotid=p.id WHERE l.todate > NOW() AND l.status=1");

        return $res->results();
    }

    /**
     * @return bool
     * @param int $id Leave ID
     */
    public static function acceptLeave($id)
    {
        self::setup();

        $ret = self::$_db->update('leave_absense', $id, 'id', [
            'status' => 1
        ]);

        return !($ret->error());
    }

    /**
     * @return bool
     * @param int $id Leave ID
     */
    public static function denyLeave($id)
    {
        self::setup();

        $ret = self::$_db->update('leave_absense', $id, 'id', [
            'status' => 2
        ]);

        return !($ret->error());
    }

}