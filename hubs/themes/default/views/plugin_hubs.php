<?php Page::setTitle('My Hub - ' . Page::$pageData->va_name); ?>
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
        <div class="container-fluid mt-4 text-center" style="overflow: auto;">
            <div class="row m-0 p-0">
                <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
                <div class="col-lg-9 main-content">
                    <div id="loader-wrapper">
                        <div id="loader" class="spinner-border spinner-border-sm spinner-custom"></div>
                    </div>
                    <div class="loaded">
                        <h3>Hub</h3>
                        <?php
                        if (Session::exists('error')) {
                            echo '<div class="alert alert-danger text-center">Error: ' . Session::flash('error') . '</div>';
                        }
                        if (Session::exists('success')) {
                            echo '<div class="alert alert-success text-center">' . Session::flash('success') . '</div>';
                        }
                        ?>

                        <ul class="nav nav-tabs justify-content-center mb-3">
                            <li class="nav-item">
                                <a class="nav-link active" data-toggle="tab" href="#home">My Hub</a>
                            </li>
                            <?php if (Page::$pageData->isHubCaptain) : ?>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#pireps">Hub PIREPs</a>
                                </li>
                            <?php else : ?>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#change">Request Change</a>
                                </li>
                            <?php endif; ?>
                        </ul>

                        <div class="tab-content">
                            <div class="tab-pane container active" id="home">
                                <h4>My Hub</h4>
                                <?php if (!empty(Page::$pageData->myhub)) : ?>
                                    <p>Your Hub is <b><?= Page::$pageData->myhub ?></b></p>
                                    <p>
                                        <?php if (!Page::$pageData->hub_info) : ?>
                                            Hub Information not Available
                                        <?php else : ?>
                                            <?= Page::$pageData->hub_info['name'] ?><br />
                                            <?= count(Page::$pageData->hub_info['runways']) ?> Runways<br />
                                            <?= count(Page::$pageData->hub_info['frequencies']) ?> ATC Frequencies<br />
                                            <?= Page::$pageData->hub_info['elevation'] ?>ft Above Sea Level<br />
                                            Located in <?= Page::$pageData->hub_info['city'] . ', ' . Page::$pageData->hub_info['country'] ?><br />
                                        <?php endif; ?>
                                    </p>
                                <?php else : ?>
                                    <p class="font-weight-bold">Your hub has not been set! You can request a hub in the <i>Request Change</i> tab.</p>
                                <?php endif; ?>
                            </div>
                            <?php if (Page::$pageData->isHubCaptain) : ?>
                                <div class="tab-pane container" id="pireps">
                                    <h4>Pending Hub PIREPs</h4>
                                    <form id="acceptpirep" method="post">
                                        <input hidden name="action" value="acceptpirep">
                                    </form>
                                    <form id="declinepirep" method="post">
                                        <input hidden name="action" value="declinepirep">
                                    </form>
                                    <table class="table table-striped">
                                        <thead class="bg-custom">
                                            <tr>
                                                <th class="mobile-hidden">Flight Number</th>
                                                <th>Dep<span class="mobile-hidden">arture</span></th>
                                                <th>Arr<span class="mobile-hidden">ival</span></th>
                                                <th>Flight Time</th>
                                                <th class="mobile-hidden">Multiplier</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            foreach (Page::$pageData->hub_pireps as $pirep) {
                                                echo '<tr><td class="align-middle mobile-hidden">';
                                                echo $pirep->flightnum;
                                                echo '</td><td class="align-middle">';
                                                echo $pirep->departure;
                                                echo '</td><td class="align-middle">';
                                                echo $pirep->arrival;
                                                echo '</td><td class="align-middle">';
                                                echo Time::secsToString($pirep->flighttime);
                                                echo '</td><td class="align-middle mobile-hidden">';
                                                echo $pirep->multi;
                                                echo '</td><td class="align-middle">';
                                                echo '<button class="btn btn-success text-light" value="' . $pirep->id . '" form="acceptpirep" type="submit" name="accept"><i class="fa fa-check"></i></button>';
                                                echo '&nbsp;<button value="' . $pirep->id . '" form="declinepirep" type="submit" class="btn btn-danger text-light" name="decline"><i class="fa fa-times"></i></button>';
                                                echo '</td>';
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else : ?>
                                <div class="tab-pane container" id="change">
                                    <h4>Request Hub Change</h4>
                                    <p>Your request will be sent to the staff team for review before your hub will be changed.</p>
                                    <hr />
                                    <form method="post">
                                        <input hidden name="action" value="reqchange" />
                                        <div class="form-group">
                                            <label for="">New Hub</label>
                                            <input required type="text" class="form-control" maxlength="4" name="new" />
                                        </div>
                                        <input type="submit" class="btn bg-custom" value="Submit" />
                                    </form>
                                    <hr />
                                </div>
                            <?php endif; ?>
                        </div>

                        <style>
                            .nav-tabs .nav-link {
                                color: #000 !important;
                            }
                        </style>
                    </div>
                </div>
            </div>
            <footer class="container-fluid text-center">
                <?php require_once __DIR__ . '/../includes/footer.php'; ?>
            </footer>
        </div>
    </div>
</body>

</html>