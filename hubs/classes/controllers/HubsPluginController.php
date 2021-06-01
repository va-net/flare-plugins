<?php

class HubsPluginController extends Controller
{
    public function get()
    {
        $data = new stdClass;
        $data->user = new User;
        $this->authenticate($data->user);

        $data->myhub = HubsPlugin::pilotHub($data->user->data()->id);
        $data->isHubCaptain = HubsPlugin::hubCaptain($data->user->data()->id);
        $data->hub_info = [];
        $data->hub_pireps = [];
        if ($data->myhub != '') $data->hub_info = VANet::getAirport($data->myhub);
        if ($data->isHubCaptain) $data->hub_pireps = HubsPlugin::pendingPireps($data->myhub);

        $this->render('plugin_hubs', $data);
    }

    public function post()
    {
        $user = new User;
        $this->authenticate($user);
        $hub = HubsPlugin::pilotHub($user->data()->id);
        $isHubCaptain = HubsPlugin::hubCaptain($user->data()->id);

        if (Input::get('action') === 'acceptpirep' && $isHubCaptain) {
            if (HubsPlugin::verifyPirepHub(Input::get('accept'), $hub)) {
                Pirep::accept(Input::get('accept'));
                Session::flash('success', 'PIREP Accepted');
            } else {
                Session::flash('error', 'You are not allowed to do that!');
                echo '<script>window.location.href="/hub.php";</script>';
                die();
            }
        } elseif (Input::get('action') === 'declinepirep' && $isHubCaptain) {
            if (HubsPlugin::verifyPirepHub(Input::get('decline'), $hub)) {
                Pirep::accept(Input::get('decline'));
                Session::flash('success', 'PIREP Declined');
            } else {
                Session::flash('error', 'You are not allowed to do that!');
            }
        } elseif (Input::get('action') === 'reqchange' && !$isHubCaptain) {
            HubsPlugin::requestChange([
                "pilotId" => $user->data()->id,
                "before" => $hub,
                "after" => Input::get('new')
            ]);
            Session::flash('success', 'Change Requested Successfully');
        }

        $this->get();
    }

    public function get_admin()
    {
        $data = new stdClass;
        $data->user = new User;
        $this->authenticate($data->user, true, 'opsmanage');

        $data->default_hub = Config::get('DEFAULT_HUB');
        $data->pilot_hubs = HubsPlugin::allPilots();
        $data->pending_changes = HubsPlugin::pendingChanges();

        $this->render('admin/plugin_hubs', $data);
    }

    public function post_admin()
    {
        $user = new User;
        $this->authenticate($user, true, 'opsmanage');

        if (Input::get('action') === 'savesettings') {
            Config::replace('DEFAULT_HUB', Input::get('defaulthub'));
            Session::flash('success', 'Settings Saved');
        } elseif (Input::get('action') === 'savehubs') {
            $pilots = HubsPlugin::allPilots();
            foreach ($pilots as $p) {
                $wasCap = $p->isHubCaptain == 1 ? 'on' : '';
                if (Input::get('hub' . $p->id) != $p->hub || Input::get('captain' . $p->id) != $wasCap) {
                    HubsPlugin::setHub($p->id, Input::get('hub' . $p->id), Input::get('captain' . $p->id) == 'on');
                }
            }

            Session::flash('success', 'Pilot Hubs Saved');
        } elseif (Input::get('action') === 'denychange') {
            HubsPlugin::setChangeStatus(Input::get('id'), 2);
            Session::flash('success', 'Hub Change Denied');
        } elseif (Input::get('action') === 'acceptchange') {
            $change = HubsPlugin::findChange(Input::get('id'));
            if ($change !== FALSE) {
                HubsPlugin::setHub($change->pilotId, $change->after, HubsPlugin::hubCaptain(Input::get('id')));
                HubsPlugin::setChangeStatus(Input::get('id'), 1);
                Session::flash('success', 'Hub Change Successful');
            }
        }

        $this->get_admin();
    }
}
