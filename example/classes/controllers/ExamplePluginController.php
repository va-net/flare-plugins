<?php

class ExamplePluginController extends Controller
{
    public function get()
    {
        $data = new stdClass;
        $data->user = new User;

        if (!$data->user->isLoggedIn()) {
            $this->redirect('/');
        } elseif (!$data->user->hasPermission('admin')) {
            $this->redirect('/home');
        }

        $this->render('plugin_example', $data);
    }
}
