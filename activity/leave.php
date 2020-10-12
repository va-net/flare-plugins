<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

require_once './core/init.php';

$user = new User();

Page::setTitle('Leave of Absense - '.Config::get('va/name'));

if (!$user->isLoggedIn()) {
    Redirect::to('index.php');
}
?>
<!DOCTYPE html>
<html>
<head>
    <?php include './includes/header.php'; ?>
</head>
<body>
    <nav class="navbar navbar-dark navbar-expand-lg bg-custom">
        <?php include './includes/navbar.php'; ?>
    </nav>
    <div class="container-fluid">
        <div class="container-fluid mt-4 text-center" style="overflow: auto;">
            <div class="row m-0 p-0">
                <?php include './includes/sidebar.php'; ?>
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

                                echo '<script>window.location.href="/leave.php";</script>';
                                die();
                            }
                        ?>
                        <h3>Leave of Absense</h3>
                        <p>
                            Here, you can see previous LOAs, apply for LOA and view the status of your requests.
                        </p>
                        <h4>Request LOA</h4>
                        <form method="post">
                            <div class="form-group">
                                <label>Start Date</label>
                                <input required class="form-control" name="fromdate" type="date" min="<?= date("Y-m-d") ?>" />
                            </div>
                            <div class="form-group">
                                <label>End Date</label>
                                <input required class="form-control" name="todate" type="date" min="<?= date("Y-m-d") ?>" />
                            </div>
                            <div class="form-group">
                                <label>Reason</label>
                                <input required class="form-control" name="reason" type="text" />
                            </div>
                            <input class="btn bg-custom" type="submit" value="Submit Request" />
                        </form>

                        <hr />

                        <h4>LOA Requests</h4>
                        <table class="table table-striped datatable">
                            <thead class="bg-custom">
                                <tr>
                                    <th>Dates</th>
                                    <th class="mobile-hidden">Reason</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $statuses = [
                                        [
                                            'label' => 'Pending',
                                            'badge' => 'warning'
                                        ],
                                        [
                                            'label' => 'Accepted',
                                            'badge' => 'success'
                                        ],
                                        [
                                            'label' => 'Denied',
                                            'badge' => 'danger'
                                        ]
                                    ];
                                    $leaves = ActivityPlugin::userReqs($user->data()->id);
                                    foreach ($leaves as $l) {
                                        echo '<tr><td>';
                                        echo $l->fromdate.' &rarr; '.$l->todate;
                                        echo '</td><td class="mobile-hidden">';
                                        echo $l->reason;
                                        echo '</td><td>';
                                        $status = $statuses[$l->status];
                                        echo '<span class="badge badge-'.$status['badge'].'">'.$status['label'].'</span>';
                                        echo '</td></tr>';
                                    }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <footer class="container-fluid text-center">
                <?php include './includes/footer.php'; ?>
            </footer>
        </div>
    </div>
</body>
</html>