<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/
Page::setTitle('Site Dashboard - ' . Page::$pageData->va_name);
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
                        <h3>Slack/Discord Message Settings</h3>
                        <form method="post">
                            <input hidden name="action" value="save" />
                            <div class="form-group">
                                <label for="platform">Platform</label>
                                <select required class="form-control" name="platform" id="platform">
                                    <option value="">Select</option>
                                    <option>Slack</option>
                                    <option>Discord</option>
                                </select>
                                <script>
                                    $("#platform").val('<?= Page::$pageData->settings[0] ?>');
                                </script>
                            </div>
                            <div class="form-group">
                                <label for="">Public Channel Webhook</label>
                                <input required type="url" class="form-control" name="public" value="<?= Page::$pageData->settings[1] ?>" />
                                <small class="text-muted">PIREP and News Notifications will be posted here.</small>
                            </div>
                            <div class="form-group">
                                <label for="">Private Channel Webhook</label>
                                <input required type="url" class="form-control" name="private" value="<?= Page::$pageData->settings[2] ?>" />
                                <small class="text-muted">Recruitment and Update Notifications will be posted here.</small>
                            </div>
                            <input type="submit" class="btn bg-custom" value="Save" />
                        </form>
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