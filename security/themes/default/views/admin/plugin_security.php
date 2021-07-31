<?php
Page::setTitle('Security - ' . Page::$pageData->va_name);
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
                        <h3>Temporary Passowrds</h3>
                        <p>Click the cycle button to issue a user with a temporary password. They will need to set a permanent one when they log in next. Temporary passwords expire 24 hours after they are created.</p>
                        <form method="post" id="forcereset">
                            <input hidden name="action" value="reset" />
                        </form>
                        <table class="table table-striped datatable">
                            <thead class="bg-custom">
                                <tr>
                                    <th>Pilot</th>
                                    <th><span class="mobile-hidden">Reset Password</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach (Page::$pageData->users as $u) {
                                    echo '<tr><td class="align-middle">';
                                    echo $u['name'];
                                    echo '</td><td class="align-middle">';
                                    echo '<button class="btn btn-danger" form="forcereset" type="submit" name="user" value="' . $u['id'] . '" data-toggle="tooltip" title="Force Password Reset"><i class="fa fa-sync"></i></button>';
                                }
                                ?>
                            </tbody>
                        </table>

                        <style>
                            .nav-tabs .nav-link {
                                color: #000 !important;
                            }
                        </style>
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