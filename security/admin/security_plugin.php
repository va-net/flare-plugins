<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

require_once '../core/init.php';

$user = new User();

Page::setTitle('Security - '.Config::get('va/name'));

if (!$user->isLoggedIn()) {
    Redirect::to('/index.php');
} elseif (!$user->hasPermission('admin')) { 
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
                        ?>
                        <h3>Manage Plugins</h3>
                        <?php
                            $tab = "overview";
                            if (!empty(Input::get('tab'))) {
                                $tab = Input::get('tab');
                            }
                            $ACTIVE_CATEGORY = 'plugins';

                            // Process Everything
                            $threatcolors = [
                                "success",
                                "warning",
                                "warning",
                                "warning",
                                "danger",
                                "danger"
                            ];
                            $users = SecurityPlugin::checkUsers();
                            $dbPass = SecurityPlugin::getThreat(Config::get('mysql/password'));
                            $badusers = count(array_filter(array_map(function($u) {
                                if ($u->threat !== 0) return $u;
                            }, $users), function($u) {
                                if ($u != null) return $u;
                            }));
                            $userthreats = [];
                            $staffthreats = [];
                            $admins = array_map(function($s) {
                                return $s->id;
                            }, Permissions::usersWith('admin'));
                            foreach ($users as $u) {
                                array_push($userthreats, $u->threat);
                                if (in_array($u->id, $admins)) array_push($staffthreats, $u->threat);
                            }
                            $userthreatav = round(array_sum($userthreats) / count($userthreats), 2);
                            $staffthreatav = round(array_sum($staffthreats) / count($staffthreats), 2);

                            if (Input::get('action') == 'reset') {
                                $pass = SecurityPlugin::randomPass();
                                SecurityPlugin::issueTemp(Input::get('user'), $pass);
                                Session::flash('success', "Passsword reset to <b>{$pass}</b>. Copy this now, you will only see it once!");
                                echo '<script>window.location.href="/admin/security_plugin.php";</script>';
                                die();
                            }
                        ?>
                        <script>
                            $(document).ready(function() {
                                $("#<?= $tab; ?>link").click();
                            });
                        </script>
                        <ul class="nav nav-tabs nav-dark justify-content-center">
                            <li class="nav-item">
                                <a class="nav-link" id="overviewlink" data-toggle="tab" href="#overview">Overview</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="pilotslink" data-toggle="tab" href="#pilots">Pilots</a>
                            </li>
                        </ul>

                        <div class="tab-content">
                            <div id="overview" class="tab-pane container-fluid p-3 fade">
                                <h4>Security Overview</h4>
                                <p>
                                    Welcome to the Security Overview. Here, you can get a quick glance at your site's security. 
                                    Security is measured in Threat Levels on a scale of 0 to 5, 0 being the lowest threat and 5 
                                    being the highest. Our wordlist is relatively short, so even a 1 is considered very vulnerable.
                                </p>
                                <table class="table">
                                    <tr>
                                        <th>DB Password Threat</th>
                                        <td class="text-<?= $threatcolors[$dbPass[0]] ?>" data-toggle="tooltip" title="<?= $dbPass[1] ?>"><?= $dbPass[0] ?></td>
                                    </tr>
                                    <tr>
                                        <th>Average User Threat</th>
                                        <td class="text-<?= $threatcolors[ceil($userthreatav)] ?>"><?= $userthreatav ?></td>
                                    </tr>
                                    <tr>
                                        <th>Average Staff Threat</th>
                                        <td class="text-<?= $threatcolors[ceil($staffthreatav)] ?>"><?= $staffthreatav ?></td>
                                    </tr>
                                    <tr>
                                        <th># Vulnerable Users</th>
                                        <td><?= $badusers ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div id="pilots" class="tab-pane container-fluid p-3 fade">
                                <h4>Pilot Security</h4>
                                <form method="post" id="forcereset">
                                    <input hidden name="action" value="reset" />
                                </form>
                                <table class="table table-striped datatable">
                                    <thead class="bg-custom">
                                        <tr>
                                            <th>Pilot</th>
                                            <th>Threat</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                            foreach ($users as $u) {
                                                echo '<tr><td class="align-middle">';
                                                echo $u->name;
                                                echo '</td><td class="align-middle">';
                                                echo '<span class="text-'.$threatcolors[$u->threat].'">'.$u->threat.'</span>';
                                                echo '</td><td class="align-middle">';
                                                echo '<button class="btn btn-danger" form="forcereset" type="submit" name="user" value="'.$u->id.'" data-toggle="tooltip" title="Force Password Reset"><i class="fa fa-sync"></i></button>';
                                            }
                                        ?>
                                    </tbody>
                                </table>
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