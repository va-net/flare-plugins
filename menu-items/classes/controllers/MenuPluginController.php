<?php

class MenuPluginController extends Controller
{
    public function get()
    {
        $data = new stdClass;
        $data->user = new User;
        $this->authenticate($data->user, true);

        $data->active_items = MenuPlugin::getItems();
        $this->render('admin/menu_plugin', $data);
    }

    public function post()
    {
        $user = new User;
        $this->authenticate($user, true);

        if (Input::get('action') === 'deleteitem') {
            $res = MenuPlugin::deleteItem(Input::get('id'));
            if ($res) {
                Session::flash('success', 'Item Deleted - it make take a moment to take effect');
            } else {
                Session::flash('error', 'Failed to Delete Item');
            }
        } elseif (Input::get('action') === 'additem') {
            $res = MenuPlugin::addItem([
                "type" => Input::get('type'),
                "label" => Input::get('label'),
                "link" => Input::get('link'),
                "icon" => Input::get('icon'),
            ]);

            if ($res) {
                Session::flash('success', 'Item Added - it make take a moment to take effect');
            } else {
                Session::flash('error', 'Failed to Add Item');
            }
        }

        $this->get();
    }
}
