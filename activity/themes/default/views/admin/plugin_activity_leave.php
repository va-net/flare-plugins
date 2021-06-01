<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/
Page::setTitle('LOA Admin - ' . Page::$pageData->va_name);
$ACTIVE_CATEGORY = 'user-management';
?>
<!DOCTYPE html>
<html>

<head>
    <?php require_once __DIR__ . '/../../includes/header.php'; ?>
</head>

<body>
    <nav class="navbar navbar-dark navbar-expand-lg bg-custom">
        <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>
    </nav>
    <div class="container-fluid">
        <div class="container-fluid mt-4 text-center" style="overflow: auto;">
            <div class="row m-0 p-0">
                <?php require_once __DIR__ . '/../../includes/sidebar.php'; ?>
                <div class="col-lg-9 main-content">
                    <div id="loader-wrapper">
                        <div id="loader" class="spinner-border spinner-border-sm spinner-custom"></div>
                    </div>
                    <div class="loaded">
                        <h3>LOA Admin</h3>
                        <?php
                        if (Session::exists('error')) {
                            echo '<div class="alert alert-danger text-center">Error: ' . Session::flash('error') . '</div>';
                        }
                        if (Session::exists('success')) {
                            echo '<div class="alert alert-success text-center">' . Session::flash('success') . '</div>';
                        }
                        ?>
                        <form id="acceptReq" method="post">
                            <input hidden name="action" value="accept" />
                        </form>
                        <form id="denyReq" method="post">
                            <input hidden name="action" value="deny" />
                        </form>
                        <div class="mobile-hidden">
                            <h4>Pending Requests</h4>
                            <table class="table">
                                <thead class="bg-custom">
                                    <tr>
                                        <th>Dates</th>
                                        <th>Pilot</th>
                                        <th>Reason</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach (Page::$pageData->pending_leave as $p) {
                                        echo '<tr><td class="align-middle">';
                                        echo $p->fromdate . ' &rarr; ' . $p->todate;
                                        echo '</td><td class="align-middle">';
                                        echo $p->pilot;
                                        echo '</td><td class="align-middle">';
                                        echo $p->reason;
                                        echo '</td><td class="align-middle">';
                                        echo '<button class="btn btn-success" type="submit" form="acceptReq" name="id" value="' . $p->id . '"><i class="fa fa-check"></i></button>';
                                        echo '&nbsp;<button class="btn btn-danger" type="submit" form="denyReq" name="id" value="' . $p->id . '"><i class="fa fa-times"></i></button>';
                                        echo '</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>

                            <hr />

                            <h4>Current & Upcoming Leave</h4>
                            <table class="table datatable">
                                <thead class="bg-custom">
                                    <tr>
                                        <th>Dates</th>
                                        <th>Pilot</th>
                                        <th>Reason</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach (Page::$pageData->approved_leave as $c) {
                                        echo '<tr><td class="align-middle">';
                                        echo $c->fromdate . ' &rarr; ' . $c->todate;
                                        echo '</td><td class="align-middle">';
                                        echo $c->pilot;
                                        echo '</td><td class="align-middle">';
                                        echo $c->reason;
                                        echo '</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                        <p class="desktop-hidden">Please use a larger screen for LOA Admin</p>
                    </div>
                </div>
            </div>
            <footer class="container-fluid text-center">
                <?php require_once __DIR__ . '/../../includes/footer.php'; ?>
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