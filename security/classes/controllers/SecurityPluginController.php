<?php

class SecurityPluginController extends Controller
{
    public function get_admin()
    {
        $data = new stdClass;
        $data->user = new User;
        $this->authenticate($data->user, true, 'usermanage');

        $data->users = $data->user->getAllUsers();
        $this->render('admin/plugin_security', $data);
    }

    public function post_admin()
    {
        $user = new User;
        $this->authenticate($user, true);

        if (Input::get('action') == 'reset') {
            $pass = SecurityPlugin::randomPass();
            SecurityPlugin::issueTemp(Input::get('user'), $pass);
            Session::flash('success', "Passsword reset to <b>{$pass}</b>. Copy this now, you will only see it once!");
        }

        $this->get_admin();
    }

    public function get()
    {
        $GLOBALS['top-menu'] = [
            "Log Out" => [
                "link" => "/logout",
                "icon" => "fa-sign-out-alt",
                "loginOnly" => true,
                "mobileHidden" => false,
            ]
        ];

        $data = new stdClass;
        $data->user = new User;
        $this->authenticate($data->user);
        if (SecurityPlugin::tempForUser($data->user->data()->id) == null) {
            $this->redirect('/home');
        }

        $this->render('plugin_security', $data);
    }

    public function post()
    {
        if (!Token::check(Input::get('token'))) $this->get();

        $user = new User;
        $this->authenticate($user);

        $temp = SecurityPlugin::tempForUser($user->data()->id);
        if ($temp == null) {
            $this->redirect('/home');
        }

        if (Hash::check(Input::get('newpass'), $user->data()->password)) {
            Session::flash('error', 'Your New Password must be different to your old one!');
            $this->get();
        }

        if (strlen(Input::get('newpass')) < 8) {
            Session::flash('error', 'Your New Password must be at least 8 characters!');
            $this->get();
        }

        $user->update([
            'password' => Hash::make(Input::get('newpass')),
        ]);
        SecurityPlugin::revokeTemp($temp->id, false);
        Session::flash('success', 'Password Set!');
        $this->redirect('/home');
    }
}
