<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/
Page::setTitle('Temporary Password - ' . Page::$pageData->va_name);
?>
<!DOCTYPE html>
<html>

<head>
    <?php require_once __DIR__ . '/../includes/header.php'; ?>
</head>

<body>
    <nav class="navbar navbar-dark navbar-expand-lg bg-custom">
        <?php require_once __DIR__ . '/../includes/navbar.php'; ?>
    </nav>
    <div class="container-fluid">
        <div class="container mt-4 text-center" style="overflow: auto;">
            <div class="main-content">
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
                    <h3>Temporary Password</h3>
                    <p>
                        Your password has been set as a temporary password. This means either a staff member would like you to change
                        your password, or your password has been changed recently and needs to be set to something permanent. To help
                        keep your account secure, you are required to choose a new, permanent password. This will take effect immediately.
                    </p>
                    <form method="post" action="">
                        <input type="hidden" name="token" value="<?= Token::generate() ?>" />
                        <div class="form-group">
                            <label for="">New Password</label>
                            <input required type="password" class="form-control" name="newpass" minlength="8" />
                        </div>
                        <input type="submit" class="btn bg-custom" value="Save" />
                    </form>
                </div>
            </div>
            <footer class="container-fluid text-center">
                <?php require_once __DIR__ . '/../includes/footer.php'; ?>
            </footer>
        </div>
    </div>
</body>

</html>