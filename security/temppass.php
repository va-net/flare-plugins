<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

require_once './core/init.php';

$user = new User();

Page::setTitle('Temporary Password - '.Config::get('va/name'));

if (!$user->isLoggedIn()) {
    Redirect::to('index.php');
}

$GLOBALS['top-menu'] = array(
    "Apply" => [
        "link" => "/apply.php",
        "icon" => "fa-id-card",
        "loginOnly" => false,
        "mobileHidden" => false,
    ],
    "Log In" => [
        "link" => "/index.php",
        "icon" => "fa-sign-in-alt",
        "loginOnly" => false,
        "mobileHidden" => false,
    ],
    "Log Out" => [
        "link" => "/logout.php",
        "icon" => "fa-sign-out-alt",
        "loginOnly" => true,
        "mobileHidden" => true,
    ]
);
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
        <div class="container mt-4 text-center" style="overflow: auto;">
                <div class="main-content">
                    <div id="loader-wrapper"><div id="loader" class="spinner-border spinner-border-sm spinner-custom"></div></div>
                    <div class="loaded">
                        <?php
                        if (Session::exists('error')) {
                            echo '<div class="alert alert-danger text-center">Error: '.Session::flash('error').'</div>';
                        }
                        if (Session::exists('success')) {
                            echo '<div class="alert alert-success text-center">'.Session::flash('success').'</div>';
                        }
                        if (SecurityPlugin::tempForUser($user->data()->id) == null) {
                            echo '<script>window.location.href="/home.php";</script>';
                            die();
                        }
                        if (Input::exists() && Token::check(Input::get('token'))) {
                            if (Hash::check(Input::get('newpass'), $user->data()->password)) {
                                Session::flash('error', 'Your New Password must be different to your old one!');
                                echo '<script>window.location.href="/temppass.php";</script>';
                                die();
                            }

                            if (strlen(Input::get('newpass')) < 8) {
                                Session::flash('error', 'Your New Password must be at least 8 characters!');
                                echo '<script>window.location.href="/temppass.php";</script>';
                                die();
                            }

                            $weakpasswords = SecurityPlugin::$weakpasswords;
                            if (array_key_exists(strtolower(Input::get('newpass')), $weakpasswords) && !file_exists(__DIR__.'/.development')) {
                                Session::flash('error', 'Please use a stronger password!');
                                echo '<script>window.location.href="/temppass.php";</script>';
                                die();
                            }

                            $user->update([
                                'password' => Hash::make(Input::get('newpass')),
                            ]);
                            SecurityPlugin::revokeTemp(SecurityPlugin::tempForUser($user->data()->id)->id);
                            Session::flash('success', 'Password Set!');
                            echo '<script>window.location.href="/home.php";</script>';
                            die();
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
                <?php include './includes/footer.php'; ?>
            </footer>
        </div>
    </div>
</body>
</html>