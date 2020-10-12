<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

require_once '../core/init.php';

$user = new User();

Page::setTitle('Activity Settings - '.Config::get('va/name'));

if (!$user->isLoggedIn()) {
    Redirect::to('/index.php');
} elseif (!$user->hasPermission('usermanage') || !$user->hasPermission('admin')) {
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

                        if (Input::exists()) {
                            ActivityPlugin::updateSettings(Input::get('active'), Input::get('new'));
                            Session::flash('success', 'Settings Updated');
                            echo '<script>window.location.href="/admin/activity_settings.php";</script>';
                            die();
                        }
                        ?>
                        <h3>Activity Settings</h3>
                        <form method="post">
                            <input hidden name="action" value="save" />
                            <div class="form-group">
                                <label for="active">PIREP Requirement</label>
                                <input required type="number" class="form-control" min="1" name="active" id="active" value="<?= Config::get('ACTIVE_DAYS') ?>" />
                                <small class="text-muted">Pilots must file 1 PIREP per this many days</small>
                            </div>
                            <div class="form-group">
                                <label for="new">New Pilot Days</label>
                                <input required type="number" class="form-control" min="1" name="new" id="new" value="<?= Config::get('NEW_DAYS') ?>" />
                                <small class="text-muted">Pilots must file a PIREP within this many days after joining and are otherwise marked as inactive</small>
                            </div>
                            <input type="submit" class="btn bg-custom" value="Save" />
                        </form>  
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