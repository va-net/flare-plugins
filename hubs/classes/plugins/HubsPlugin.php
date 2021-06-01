<?php

class HubsPlugin
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
    public static function init()
    {
        Plugin::adminMenu('Hubs Admin', [
            "link" => "/admin/hubs",
            "icon" => "fa-globe-americas",
            "permission" => "opsmanage",
        ]);
        Plugin::pilotMenu('My Hub', [
            "link" => "/hub",
            "icon" => "fa-map-signs"
        ]);

        self::$_listeners['user/created'] = Events::listen('user/created', 'HubsPlugin::setupPilot');
        Router::add('/hub', [new HubsPluginController, 'get']);
        Router::add('/hub', [new HubsPluginController, 'post'], 'post');
        Router::add('/admin/hubs', [new HubsPluginController, 'get_admin']);
        Router::add('/admin/hubs', [new HubsPluginController, 'post_admin'], 'post');
    }

    /**
     * @return null
     * @param Event $event
     */
    public static function setupPilot($event)
    {
        self::setup();

        $pilot = self::$_db->query("SELECT * FROM pilots ORDER BY id DESC LIMIT 1")->first();
        $defaultHub = Config::get('DEFAULT_HUB');
        if (empty($defaultHub)) return;

        self::$_db->insert('pilot_hubs', [
            'pilotId' => $pilot->id,
            'hub' => $defaultHub,
        ]);
    }

    /**
     * @return bool
     * @param int $pilot Pilot ID
     * @param string $hub Hub ICAO
     * @param bool $captain Whether the Pilot is a Hub Captain
     */
    public static function setHub($pilot, $hub, $captain = false)
    {
        self::setup();

        $captain = $captain ? 1 : 0;

        self::$_db->delete('pilot_hubs', ['pilotId', '=', $pilot]);
        $ret = self::$_db->insert('pilot_hubs', [
            'pilotId' => $pilot,
            'hub' => $hub,
            'isCaptain' => $captain
        ]);

        return !($ret->error());
    }

    /**
     * @return string
     * @param int $id Pilot ID
     */
    public static function pilotHub($id)
    {
        self::setup();

        $ret = self::$_db->get('pilot_hubs', ['pilotId', '=', $id]);
        if ($ret->count() == 0) {
            $defaultHub = Config::get('DEFAULT_HUB');
            if (empty($defaultHub)) return '';
            self::setHub($id, $defaultHub);
            return $defaultHub;
        }

        return $ret->first()->hub;
    }

    /**
     * @return array
     */
    public static function allPilots()
    {
        self::setup();

        $sql = "SELECT p.*, h.hub, h.isCaptain AS isHubCaptain FROM pilots p INNER JOIN pilot_hubs h ON p.id=h.pilotId WHERE p.status=1";
        $ret = self::$_db->query($sql);

        return $ret->results();
    }

    /**
     * @return array
     * @param string $hub Hub
     */
    public static function pendingPireps($hub)
    {
        self::setup();

        $sql = "SELECT p.* FROM pireps p INNER JOIN pilot_hubs h ON p.pilotid=h.pilotId WHERE p.status=0 AND h.hub=?";
        $ret = self::$_db->query($sql, [$hub]);

        return $ret->results();
    }

    /**
     * @return bool
     * @param int $id PIREP ID
     * @param string $hub Hub
     */
    public static function verifyPirepHub($id, $hub)
    {
        $pirep = self::$_db->query("SELECT h.hub FROM pireps p INNER JOIN pilot_hubs h ON p.pilotid=h.pilotId WHERE p.id=?", [$id]);
        if ($pirep->count() == 0) {
            return false;
        }

        return $pirep->first()->hub == $hub;
    }

    /**
     * @return object|bool
     * @param int $id Change ID
     */
    public static function findChange($id)
    {
        self::setup();

        $ret = self::$_db->get('hub_changes', ['id', '=', $id]);
        if ($ret->count() == 0) return false;

        return $ret->first();
    }

    /**
     * @return bool
     * @param int $id Pilot ID
     */
    public static function hubCaptain($id)
    {
        self::setup();

        $ret = self::$_db->get('pilot_hubs', ['pilotId', '=', $id]);
        if ($ret->count() == 0) {
            return false;
        }

        return $ret->first()->isCaptain == 1;
    }

    /**
     * @return bool
     * @param array $fields Change Fields
     */
    public static function requestChange($fields)
    {
        self::setup();

        $res = self::$_db->insert('hub_changes', $fields);
        return !($res->error());
    }

    /**
     * @return array
     */
    public static function pendingChanges()
    {
        self::setup();

        $ret = self::$_db->query("SELECT p.name AS pilot, c.* FROM hub_changes c INNER JOIN pilots p ON c.pilotId=p.id WHERE c.status=0 AND p.status=1");
        return $ret->results();
    }

    /**
     * @return bool
     * @param int $id Change ID
     * @param int $status Change Status
     */
    public static function setChangeStatus($id, $status)
    {
        self::setup();

        $res = self::$_db->update('hub_changes', $id, 'id', [
            "status" => $status
        ]);

        return !($res->error());
    }
}
