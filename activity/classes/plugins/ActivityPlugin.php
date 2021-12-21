<?php
class ActivityPlugin
{

    /**
     * @var DB
     */
    private static $_db = null;
    private static $_activePeriod = null;
    private static $_newPeriod = null;

    public static $statuses = [
        [
            'label' => 'Pending',
            'badge' => 'warning'
        ],
        [
            'label' => 'Accepted',
            'badge' => 'success'
        ],
        [
            'label' => 'Denied',
            'badge' => 'danger'
        ]
    ];
    public static $tailwind_statuses = [
        [
            'label' => 'Pending',
            'badge' => 'bg-yellow-200 text-yellow-800',
        ],
        [
            'label' => 'Accepted',
            'badge' => 'bg-green-100 text-green-800 dark:bg-green-300 dark:text-green-900',
        ],
        [
            'label' => 'Denied',
            'badge' => 'bg-red-200 text-red-900',
        ]
    ];

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
        // Register Menu Items
        Plugin::adminMenu('Activity', [
            'link' => '/admin/activity',
            'icon' => 'fa-clock',
            'permission' => 'usermanage',
        ], 'User Management');
        Plugin::adminMenu('LOA Admin', [
            'link' => '/admin/leave',
            'icon' => 'fa-briefcase',
            'permission' => 'usermanage',
        ], 'User Management');
        Plugin::adminMenu('Activity Settings', [
            'link' => '/admin/settings/activity',
            'icon' => 'fa-users-cog',
            'permission' => 'usermanage',
        ]);
        Plugin::pilotMenu('Leave of Absence', [
            'link' => '/leave',
            'icon' => 'fa-laptop-house',
        ]);

        Router::add('/admin/activity', [new ActivityPluginController, 'get_admin']);
        Router::add('/admin/activity', [new ActivityPluginController, 'post_admin'], 'post');
        Router::add('/admin/leave', [new ActivityPluginController, 'get_leave_admin']);
        Router::add('/admin/leave', [new ActivityPluginController, 'post_leave_admin'], 'post');
        Router::add('/admin/settings/activity', [new ActivityPluginController, 'get_settings']);
        Router::add('/admin/settings/activity', [new ActivityPluginController, 'post_settings'], 'post');
        Router::add('/leave', [new ActivityPluginController, 'get_leave']);
        Router::add('/leave', [new ActivityPluginController, 'post_leave'], 'post');
    }

    /**
     * @return array
     */
    public static function activePilots()
    {
        self::setup();
        $activePeriod = self::$_activePeriod;

        $sql = "SELECT * FROM pilots WHERE id IN (SELECT pilotid FROM pireps WHERE DATEDIFF(NOW(), date) <= {$activePeriod} AND status=1) 
                AND status=1 AND NOT id IN (SELECT pilotid FROM leave_absence WHERE status=1 AND fromdate >= NOW() AND todate <= NOW())";
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

        $sql = 'SELECT * FROM pilots WHERE NOT id IN (SELECT pilotid FROM pireps WHERE DATEDIFF(NOW(), date) <= ? AND status=1) 
                AND status=1 AND DATEDIFF(NOW(), joined) > ? AND NOT id IN 
                (SELECT pilotid FROM leave_absence WHERE status=1 AND fromdate <= NOW() AND todate >= NOW())';
        $data = self::$_db->query($sql, [$activePeriod, $newPeriod]);

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
                (SELECT pilotid FROM leave_absence WHERE status=1 AND fromdate >= NOW() AND todate <= NOW())";
        $data = self::$_db->query($sql);

        return $data->results();
    }

    /**
     * @return array
     */
    public static function retiredPilots()
    {
        self::setup();

        $sql = 'SELECT * FROM pilots WHERE id IN (SELECT pilotid FROM pireps) AND status=2';
        $data = self::$_db->query($sql);

        return $data->results();
    }

    /**
     * @return array
     */
    public static function pilotsOnLeave()
    {
        self::setup();

        $sql = 'SELECT * FROM pilots WHERE id IN (SELECT pilotid FROM leave_absence WHERE status=1 AND fromdate <= NOW() AND todate >= NOW())';
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

        $ret = self::$_db->insert('leave_absence', $fields);
        if (class_exists('NotifyPlugin')) {
            $pilot = (new User)->getUser($fields['pilotid']);
            NotifyPlugin::postMsg("Pilot {$pilot->name} has requested leave from {$fields['fromdate']} to {$fields['todate']}", true);
        }

        return !($ret->error());
    }

    /**
     * @return array
     * @param int $user User ID
     */
    public static function userReqs($user)
    {
        self::setup();

        $res = self::$_db->get('leave_absence', array('pilotid', '=', $user));

        return $res->results();
    }

    /**
     * @return array
     */
    public static function pendingReqs()
    {
        self::setup();

        $res = self::$_db->query('SELECT l.*, p.name AS pilot FROM leave_absence l INNER JOIN pilots p ON l.pilotid=p.id WHERE l.status=0');

        return $res->results();
    }

    /**
     * @return array
     */
    public static function currentFutureLeave()
    {
        self::setup();

        $res = self::$_db->query('SELECT l.*, p.name AS pilot FROM leave_absence l INNER JOIN pilots p ON l.pilotid=p.id WHERE l.todate > NOW() AND l.status=1');

        return $res->results();
    }

    /**
     * @return bool
     * @param int $id Leave ID
     */
    public static function acceptLeave($id)
    {
        self::setup();

        $ret = self::$_db->update('leave_absence', $id, 'id', [
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

        $ret = self::$_db->update('leave_absence', $id, 'id', [
            'status' => 2
        ]);

        return !($ret->error());
    }
}
