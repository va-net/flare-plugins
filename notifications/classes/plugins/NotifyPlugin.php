<?php

class NotifyPlugin
{

    private static $_listeners = [];

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
        self::$_listeners['pirep/filed'] = Events::listen('pirep/filed', 'NotifyPlugin::pirepFiled');
        self::$_listeners['user/created'] = Events::listen('user/created', 'NotifyPlugin::userApplied');
        self::$_listeners['news/added'] = Events::listen('news/added', 'NotifyPlugin::newsAdded');
        self::$_listeners['site/updated'] = Events::listen('site/updated', 'NotifyPlugin::siteUpdated');
    }

    /**
     * @return null
     * @param Event $event
     */
    public static function pirepFiled($event)
    {
        $params = $event->params;
        $msg = "**New PIREP Filed**\r\n";
        $msg .= "> Route: {$params['departure']}-{$params['arrival']}\r\n";
        $msg .= "> Flight Time: " . Time::secsToString($params['flighttime']) . "\r\n";
        $msg .= "> Flight Number: {$params['flightnum']}";
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
     * @param string $message Message
     * @param string $isPrivate Whether to send to the Internal Memo Channel
     */
    private static function postMsg($message, $isPrivate = false)
    {
        // Configure Webhook URL & Payload
        $url = '';
        $payload = [
            "text" => $message
        ];
        if (!empty(Config::get('DISCORD_WEBHOOK'))) {
            $url = trim(Config::get('DISCORD_WEBHOOK'), '/') . '/slack';
            if ($isPrivate) {
                $url = trim(Config::get('DISCORD_WEBHOOK_PRIVATE'), '/') . '/slack';
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
            $pub = trim(Config::get('DISCORD_WEBHOOK'), '/') . '/slack';
            $prv = trim(Config::get('DISCORD_WEBHOOK_PRIVATE'), '/') . '/slack';
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
