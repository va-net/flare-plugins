<?php

class ActivityPluginController extends Controller
{
    public function get_admin()
    {
        $data = new stdClass;
        $data->user = new User;
        $this->authenticate($data->user, true, 'usermanage');

        $data->pilots = new stdClass;
        $data->pilots->active = ActivityPlugin::activePilots();
        $data->pilots->inactive = ActivityPlugin::inactivePilots();
        $data->pilots->new = ActivityPlugin::newPilots();
        $data->pilots->retired = ActivityPlugin::retiredPilots();
        $data->pilots->leave = ActivityPlugin::pilotsOnLeave();

        $data->active_dropdown = 'user-management';
        $this->render('admin/plugin_activity', $data);
    }

    public function post_admin()
    {
        $user = new User;
        $this->authenticate($user, true, 'usermanage');

        if (Input::get('action') == 'retire') {
            $user->update([
                'status' => 2
            ], Input::get('pilot'));
            Session::flash('success', 'Pilot Retired Successfully');
        } elseif (Input::get('action') == 'unretire') {
            $user->update([
                'status' => 1
            ], Input::get('pilot'));
            Session::flash('success', 'Pilot Unretired Successfully');
        }

        $this->get_admin();
    }

    public function get_leave_admin()
    {
        $data = new stdClass;
        $data->user = new User;
        $this->authenticate($data->user, true, 'usermanage');

        $data->pending_leave = ActivityPlugin::pendingReqs();
        $data->approved_leave = ActivityPlugin::currentFutureLeave();

        $data->active_dropdown = 'user-management';
        $this->render('admin/plugin_activity_leave', $data);
    }

    public function post_leave_admin()
    {
        $user = new User;
        $this->authenticate($user, true, 'usermanage');

        if (Input::get('action') == 'accept') {
            $res = ActivityPlugin::acceptLeave(Input::get('id'));
            if (!$res) {
                Session::flash('error', 'Failed to Accept Leave');
            } else {
                Session::flash('success', 'Leave Accepted');
            }
        } elseif (Input::get('action') == 'deny') {
            $res = ActivityPlugin::denyLeave(Input::get('id'));
            if (!$res) {
                Session::flash('error', 'Failed to Deny Leave');
            } else {
                Session::flash('success', 'Leave Denied');
            }
        }

        $this->get_leave_admin();
    }

    public function get_settings()
    {
        $data = new stdClass;
        $data->user = new User;
        $this->authenticate($data->user, true, 'usermanage');

        $data->active_days = Config::get('ACTIVE_DAYS');
        $data->new_days = Config::get('NEW_DAYS');

        $data->active_dropdown = 'plugins';
        $this->render('admin/plugin_activity_settings', $data);
    }

    public function post_settings()
    {
        $user = new User;
        $this->authenticate($user, true, 'usermanage');

        ActivityPlugin::updateSettings(Input::get('active'), Input::get('new'));
        Session::flash('success', 'Settings Updated');
        $this->get_settings();
    }

    public function get_leave()
    {
        $data = new stdClass;
        $data->user = new User;
        $this->authenticate($data->user);

        $data->requests = ActivityPlugin::userReqs($data->user->data()->id);

        $this->render('plugin_activity_loa', $data);
    }

    public function post_leave()
    {
        $user = new User;
        $this->authenticate($user);
        $res = ActivityPlugin::fileLeave([
            "pilotid" => $user->data()->id,
            "fromdate" => Input::get('fromdate'),
            "todate" => Input::get('todate'),
            "reason" => Input::get('reason')
        ]);

        if (!$res) {
            Session::flash('error', 'Leave Request Failed');
        } else {
            Session::flash('success', 'Leave Requested Successfully');
        }

        $this->get_leave();
    }
}
