<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/
Page::setTitle('Menu Items - ' . Page::$pageData->va_name);
$ACTIVE_CATEGORY = 'plugins';
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
                        <h3>Custom Menu Items</h3>
                        <hr />
                        <h4>Add Item</h4>
                        <form method="post">
                            <input hidden name="action" value="additem" />
                            <div class="form-group">
                                <label for="">Item Type</label>
                                <select required class="form-control" name="type">
                                    <option value>Select</option>
                                    <?php
                                    foreach (MenuPlugin::$itemTypes as $val => $name) {
                                        echo '<option value="' . $val . '">' . $name . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="">Icon</label>
                                <input required type="text" maxlength="50" class="form-control" name="icon" placeholder="fa-square" />
                                <small class="text-muted">
                                    An icon list is available <a href="https://fontawesome.com/icons?d=gallery" target="_blank">here</a>.
                                    Pro icons and brand icons are not available. Enter <b>fa-</b> followed by the name, so for
                                    <b>question-circle</b> enter <b>fa-question-circle</b>.
                                </small>
                            </div>
                            <div class="form-group">
                                <label for="">Label</label>
                                <input required type="text" class="form-control" name="label" placeholder="Pilot Handbook" />
                            </div>
                            <div class="form-group">
                                <label for="">Link</label>
                                <input required type="url" class="form-control" name="link" placeholder="https://google.com" />
                            </div>
                            <input type="submit" class="btn bg-custom" value="Save" />
                        </form>
                        <hr />
                        <h4>Active Items</h4>
                        <form method="post" id="deleteitem">
                            <input hidden name="action" value="deleteitem" />
                        </form>
                        <table class="table datatable">
                            <thead class="bg-custom">
                                <tr>
                                    <th>Type</th>
                                    <th>Label</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach (Page::$pageData->active_items as $a) {
                                    echo '<tr><td class="align-middle">';
                                    echo MenuPlugin::$itemTypes[$a->type];
                                    echo '</td><td class="align-middle"><a href="' . $a->link . '" target="_blank">';
                                    echo $a->label;
                                    echo '</a></td><td class="align-middle">';
                                    echo '<button class="btn btn-danger" form="deleteitem" name="id" value="' . $a->id . '"><i class="fa fa-trash"></i></button>';
                                    echo '</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                        <hr />
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