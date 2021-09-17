<?php

class NotifyPluginController extends Controller
{
    public function get()
    {
        $data = new stdClass;
        $data->user = new User;
        $this->authenticate($data->user, true);

        $data->settings = NotifyPlugin::getSettings();
        $this->render('admin/notify_plugin', $data);
    }

    public function post()
    {
        $user = new User;
        $this->authenticate($user, true);
        if (Input::get('action') == 'save') {
            if (Input::get('platform') == 'Discord') {
                Config::replace('DISCORD_WEBHOOK', Input::get('public'));
                Config::replace('DISCORD_WEBHOOK_PRIVATE', Input::get('private'));
                Config::replace('SLACK_WEBHOOK', '');
                Config::replace('SLACK_WEBHOOK_PRIVATE', '');
            } elseif (Input::get('platform') == 'Slack') {
                Config::replace('SLACK_WEBHOOK', Input::get('public'));
                Config::replace('SLACK_WEBHOOK_PRIVATE', Input::get('private'));
                Config::replace('DISCORD_WEBHOOK', '');
                Config::replace('DISCORD_WEBHOOK_PRIVATE', '');
            }

            NotifyPlugin::postMsg('Channel Configured as Public Webhook Channel for Flare');
            NotifyPlugin::postMsg('Channel Configured as Private Webhook Channel for Flare', true);

            Session::flash('success', 'Settings Saved');
        }

        $this->get();
    }
}
