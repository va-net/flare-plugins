<?php
require_once './core/init.php';

$user = new User();

Page::setTitle('Example Plugin Admin - '.Config::get('va/name'));

if (!$user->isLoggedIn()) {
    Redirect::to('index.php');
} elseif (!$user->hasPermission('admin')) {
    Redirect::to('home.php');
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
                        <h3>Example Plugin</h3>
                        <p>
                            This is just a simple example plugin to show how plugins work in Flare. 
                            Be sure to check out the source code!
                        </p>
                        <p>
                            <?php Example::sayhello(); ?>
                        </p>
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