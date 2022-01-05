<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/
Page::setTitle('Pilot Activity - ' . Page::$pageData->va_name);
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
                        <?php
                        if (Session::exists('error')) {
                            echo '<div class="alert alert-danger text-center">Error: ' . Session::flash('error') . '</div>';
                        }
                        if (Session::exists('success')) {
                            echo '<div class="alert alert-success text-center">' . Session::flash('success') . '</div>';
                        }
                        ?>
                        <h3>Pilot Activity</h3>
                        <form id="retirePilot" method="post">
                            <input hidden name="action" value="retire" />
                        </form>
                        <form id="unretirePilot" method="post">
                            <input hidden name="action" value="unretire" />
                        </form>
                        <table class="table table-striped datatable">
                            <thead>
                                <tr>
                                    <th>Status</th>
                                    <th>Name</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach (Page::$pageData->pilots->active as $a) {
                                    echo '<tr><td class="align-middle">';
                                    echo '<span class="badge badge-success">Active</span>';
                                    echo '</td><td class="align-middle">';
                                    echo $a->name;
                                    echo '</td><td class="align-middle">';
                                    echo '<button class="btn btn-danger" type="submit" form="retirePilot" name="pilot" value="' . $a->id . '">Retire</button>';
                                    echo '</td></tr>';
                                }
                                foreach (Page::$pageData->pilots->inactive as $i) {
                                    echo '<tr><td class="align-middle">';
                                    echo '<span class="badge badge-danger">Inactive</span>';
                                    echo '</td><td class="align-middle">';
                                    echo $i->name;
                                    echo '</td><td class="align-middle">';
                                    echo '<button class="btn btn-danger" type="submit" form="retirePilot" name="pilot" value="' . $i->id . '">Retire</button>';
                                    echo '</td></tr>';
                                }
                                foreach (Page::$pageData->pilots->new as $n) {
                                    echo '<tr><td class="align-middle">';
                                    echo '<span class="badge badge-success">New Pilot</span>';
                                    echo '</td><td class="align-middle">';
                                    echo $n->name;
                                    echo '</td><td class="align-middle">';
                                    echo '<button class="btn btn-danger" type="submit" form="retirePilot" name="pilot" value="' . $n->id . '">Retire</button>';
                                    echo '</td></tr>';
                                }
                                foreach (Page::$pageData->pilots->retired as $r) {
                                    echo '<tr><td class="align-middle">';
                                    echo '<span class="badge badge-secondary">Retired</span>';
                                    echo '</td><td class="align-middle">';
                                    echo $r->name;
                                    echo '</td><td class="align-middle">';
                                    echo '<button class="btn bg-custom" type="submit" form="unretirePilot" name="pilot" value="' . $r->id . '">Unretire</button>';
                                    echo '</td></tr>';
                                }
                                foreach (Page::$pageData->pilots->leave as $l) {
                                    echo '<tr><td class="align-middle">';
                                    echo '<span class="badge badge-info">On Leave</span>';
                                    echo '</td><td class="align-middle">';
                                    echo $l->name;
                                    echo '</td><td class="align-middle">';
                                    echo '<button class="btn btn-danger" type="submit" form="retirePilot" name="pilot" value="' . $l->id . '">Retire</button>';
                                    echo '</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
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