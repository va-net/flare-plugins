<?php
require_once '../core/init.php';

$user = new User();

Page::setTitle('Hubs Admin - '.Config::get('va/name'));

if (!$user->isLoggedIn()) {
    Redirect::to('/index.php');
} elseif (!$user->hasPermission('opsmanage') || !$user->hasPermission('admin')) {
    Redirect::to('/home.php');
}

$ACTIVE_CATEGORY = 'plugins';
?>
<!DOCTYPE html>
<html>
<head>
    <?php include '../includes/header.php'; ?>
</head>
<body>
    <nav class="navbar navbar-dark navbar-expand-lg bg-custom">
        <?php include '../includes/navbar.php'; ?>
    </nav>
    <div class="container-fluid">
        <div class="container-fluid mt-4 text-center" style="overflow: auto;">
            <div class="row m-0 p-0">
                <?php include '../includes/sidebar.php'; ?>
                <div class="col-lg-9 main-content">
                    <div id="loader-wrapper"><div id="loader" class="spinner-border spinner-border-sm spinner-custom"></div></div>
                    <div class="loaded">
                        <?php
                            if (Session::exists('error')) {
                                echo '<div class="alert alert-danger text-center">Error: '.Session::flash('error').'</div>';
                            }
                            if (Session::exists('success')) {
                                echo '<div class="alert alert-success text-center">'.Session::flash('success').'</div>';
                            }

                            if (Input::get('action') === 'savesettings') {
                                Config::replace('DEFAULT_HUB', Input::get('defaulthub'));
                                Session::flash('success', 'Settings Saved');
                                echo '<script>window.location.href="/admin/hubs_plugin.php";</script>';
                                die();
                            } elseif (Input::get('action') === 'savehubs') {
                                $pilots = HubsPlugin::allPilots();
                                foreach ($pilots as $p) {
                                    $wasCap = $p->isHubCaptain == 1 ? 'on' : '';
                                    if (Input::get('hub'.$p->id) != $p->hub || Input::get('captain'.$p->id) != $wasCap) {
                                        HubsPlugin::setHub($p->id, Input::get('hub'.$p->id), Input::get('captain'.$p->id) == 'on');
                                    }
                                }

                                Session::flash('success', 'Pilot Hubs Saved');
                                echo '<script>window.location.href="/admin/hubs_plugin.php";</script>';
                                die();
                            } elseif (Input::get('action') === 'denychange') {
                                HubsPlugin::setChangeStatus(Input::get('id'), 2);
                                Session::flash('success', 'Hub Change Denied');
                                echo '<script>window.location.href="/admin/hubs_plugin.php";</script>';
                                die();
                            } elseif (Input::get('action') === 'acceptchange') {
                                $change = HubsPlugin::findChange(Input::get('id'));
                                if ($change !== FALSE) {
                                    HubsPlugin::setHub($change->pilotId, $change->after, HubsPlugin::hubCaptain(Input::get('id')));
                                    HubsPlugin::setChangeStatus(Input::get('id'), 1);
                                    Session::flash('success', 'Hub Change Successful');
                                    echo '<script>window.location.href="/admin/hubs_plugin.php";</script>';
                                    die();
                                }
                            }

                            $defaultHub = Config::get('DEFAULT_HUB');
                            $settingsAlert = empty($defaultHub) ? ' <span class="text-danger"><i class="fa fa-exclamation-circle"></i></span>' : '';
                        ?>
                        <h3>Hubs Admin</h3>
                        <ul class="nav nav-tabs justify-content-center mb-3">
                            <li class="nav-item">
                                <a class="nav-link active" data-toggle="tab" href="#hubs">Pilot Hubs</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#changes">Change Requests</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#settings">Settings<?= $settingsAlert ?></a>
                            </li>
                        </ul>

                        <div class="tab-content">
                            <div class="tab-pane container active" id="hubs">
                                <h4>Pilot Hubs</h4>
                                <p>Change pilots' hubs as you wish then hit save for the changes to take effect.</p>
                                <form method="post" class="mobile-hidden">
                                    <input hidden name="action" value="savehubs" />
                                    <input type="submit" class="btn bg-custom" value="Save" />
                                    <table class="table datatable">
                                        <thead class="bg-custom">
                                            <tr>
                                                <th>Pilot</th>
                                                <th>Hub</th>
                                                <th>Captain?</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                                $pilots = HubsPlugin::allPilots();
                                                foreach ($pilots as $p) {
                                                    echo '<tr><td class="align-middle">';
                                                    echo $p->name;
                                                    echo '</td><td class="align-middle">';
                                                    echo '<input type="text" name="hub'.$p->id.'" class="form-control" maxlength="4" value="'.$p->hub.'" />';
                                                    echo '</td><td class="align-middle">';
                                                    $checked = $p->isHubCaptain == 1 ? ' checked' : '';
                                                    echo '<input type="checkbox" name="captain'.$p->id.'"'.$checked.'>';
                                                    echo '</td></tr>';
                                                }
                                            ?>
                                        </tbody>
                                    </table>
                                </form>
                                <p class="desktop-hidden">Please use a wider screen to manage Pilot Hubs.</p>
                            </div>
                            <div class="tab-pane container" id="changes">
                                <h4>Hub Change Requests</h4>
                                <form method="post" id="acceptchange">
                                    <input hidden name="action" value="acceptchange" />
                                </form>
                                <form method="post" id="denychange">
                                    <input hidden name="action" value="denychange" />
                                </form>
                                <table class="table">
                                    <thead class="bg-custom">
                                        <tr>
                                            <th>Pilot</th>
                                            <th>Change</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                            $pending = HubsPlugin::pendingChanges();
                                            foreach ($pending as $p) {
                                                echo '<tr><td class="align-middle">';
                                                echo $p->pilot;
                                                echo '</td><td class="align-middle">';
                                                echo $p->before.' &rarr; '.$p->after;
                                                echo '</td><td class="align-middle">';
                                                echo '<button class="btn btn-success" form="acceptchange" name="id" type="submit" value="'.$p->id.'"><i class="fa fa-check"></i></button>';
                                                echo '&nbsp;<button class="btn btn-danger" form="denychange" name="id" type="submit" value="'.$p->id.'"><i class="fa fa-times"></i></button>';
                                                echo '</td></tr>';
                                            }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="tab-pane container fade" id="settings">
                                <h4>Plugin Settings</h4>
                                <form method="post">
                                    <input hidden name="action" value="savesettings" />
                                    <div class="form-group">
                                        <label for="">Default Hub ICAO</label>
                                        <input required type="text" class="form-control" name="defaulthub" placeholder="EGLL" maxlength="4" value="<?= $defaultHub ?>" />
                                        <small class="text-muted">All New Pilots will be assigned to this hub.</small>
                                    </div>
                                    <input type="submit" class="btn bg-custom" value="Save" />
                                </form>
                            </div>
                        </div>
                        <style>
                            .nav-tabs .nav-link {
                                color: #000!important;
                            }
                        </style>
                    </div>
                </div>
            </div>
            <footer class="container-fluid text-center">
                <?php include '../includes/footer.php'; ?>
            </footer>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            $(".<?= $ACTIVE_CATEGORY ?>").collapse('show');
        });
    </script>
</body>
</html>