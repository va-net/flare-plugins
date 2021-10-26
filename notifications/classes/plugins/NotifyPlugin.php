<?php

class NotifyPlugin
{

    private static $_listeners = [];

    private static $_handlers = [
        'pirep/filed' => 'NotifyPlugin::pirepFiled',
        'user/created' => 'NotifyPlugin::userApplied',
        'news/added' => 'NotifyPlugin::newsAdded',
        'site/updated' => 'NotifyPlugin::siteUpdated',
        'user/promoted' => 'NotifyPlugin::userPromoted',
    ];

    private static $is_ready = false;

    public static function init()
    {
        // Add to Menu
        Plugin::adminMenu('Messages', [
            "link" => "/admin/messages",
            "icon" => "fa-comments",
            "permission" => "admin",
        ]);

        // Register Routes
        Router::add('/admin/messages', [new NotifyPluginController, 'get']);
        Router::add('/admin/messages', [new NotifyPluginController, 'post'], 'post');

        // Event Subscriptions
        foreach (self::$_handlers as $evName => $callable) {
            self::$_listeners[$evName] = Events::listen($evName, $callable);
        }

        self::$is_ready = true;
    }

    /**
     * @return null
     * @param Event $event
     */
    public static function pirepFiled($event)
    {
        $params = $event->params;
        $aircraft = Aircraft::fetch($params['aircraftid']);
        $pilot = (new User)->getUser($params['pilotid']);

        $msg = "**New PIREP Filed**\r\n";
        $msg .= "> Pilot: {$pilot->name} ({$pilot->callsign})\r\n";
        $msg .= "> Aircraft: {$aircraft->name} ({$aircraft->liveryname})\r\n";
        $msg .= "> Route: {$params['departure']}-{$params['arrival']}\r\n";
        $msg .= "> Flight Time: " . Time::secsToString($params['flighttime']) . "\r\n";
        $msg .= "> Flight Number: {$params['flightnum']}\r\n";
        $msg .= "> Fuel Used: {$params['fuelused']}kg";

        self::postMsg($msg);
    }

    /**
     * @return null
     * @param Event $event
     */
    public static function userApplied($event)
    {
        $params = $event->params;
        $msg = "**New Pilot Application:** {$params['name']}";
        self::postMsg($msg, true);
    }

    /**
     * @return null
     * @param Event $event
     */
    public static function newsAdded($event)
    {
        $msg = "**New News Item - {$event->params['subject']}**";
        self::postMsg($msg);
    }

    /**
     * @return null
     * @param Event $event
     */
    public static function siteUpdated($event)
    {
        $params = $event->params;
        $msg = "**Flare has been updated to {$params['tag']}**\r\n\r\n";
        $msg .= "_Release Notes_\r\n";
        $msg .= $params['notes'];
        self::postMsg($msg, true);
    }

    /**
     * @return null
     * @param Event $event
     */
    public static function userPromoted($event)
    {
        $params = $event->params;
        $pilot = (new User)->getUser($params['pilot']);
        $msg = "**{$pilot->name} has been promoted to {$params['rank']->name}!**";
        self::postMsg($msg);
    }

    /**
     * @return null
     * @param string $message Message
     * @param string $isPrivate Whether to send to the Internal Memo Channel
     */
    public static function postMsg($message, $isPrivate = false)
    {
        // Configure Webhook URL & Payload
        $url = '';
        $payload = [
            "text" => $message
        ];
        if (!empty(Config::get('DISCORD_WEBHOOK'))) {
            $url = trim(Config::get('DISCORD_WEBHOOK'), '/') . '/slack';
            $url = str_replace('/slack/slack', '/slack', $url);
            if ($isPrivate) {
                $url = Config::get('DISCORD_WEBHOOK_PRIVATE') . '/slack';
                $url = str_replace('/slack/slack', '/slack', $url);
            }
        } elseif (!empty(Config::get('SLACK_WEBHOOK'))) {
            $url = Config::get('SLACK_WEBHOOK');
            if ($isPrivate) {
                $url = Config::get('SLACK_WEBHOOK_PRIVATE');
            }
        } else {
            return;
        }

        // Send HTTP Request
        $options = array(
            'http' => array(
                'header'  => "Content-type: application/json\r\n",
                'method'  => 'POST',
                'content' => Json::encode($payload)
            )
        );
        $context = stream_context_create($options);
        file_get_contents($url, false, $context);
    }

    /**
     * @return array
     */
    public static function getSettings()
    {
        $ret = [];
        if (!empty(Config::get('DISCORD_WEBHOOK'))) {
            $pub = Config::get('DISCORD_WEBHOOK');
            $prv = Config::get('DISCORD_WEBHOOK_PRIVATE');
            $ret = ['Discord', $pub, $prv];
        } elseif (!empty(Config::get('SLACK_WEBHOOK'))) {
            $pub = Config::get('SLACK_WEBHOOK');
            $prv = Config::get('SLACK_WEBHOOK_PRIVATE');
            $ret = ['Slack', $pub, $prv];
        } else {
            $ret = ['', '', ''];
        }
        return $ret;
    }
}
