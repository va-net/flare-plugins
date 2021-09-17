<?php
Page::setTitle('Leave of Absence - ' . Page::$pageData->va_name);
require_once __DIR__ . '/../includes/header.php';
?>
<div id="content" class="m-5 text-black dark:text-white">
    <h1 class="text-3xl font-bold">Leave of Absence</h1>
    <p class="mb-3">Here, you can see previous LOAs, apply for LOA and view the status of your requests.</p>
    <section id="request-loa" class="mb-3">
        <h2 class="mb-2 text-2xl font-bold">Request LOA</h2>
        <form method="post" class="space-y-2" id="request-loa-form">
            <div class="space-y-1">
                <label for="fromdate">Start Date</label>
                <input id="fromdate" name="fromdate" type="date" min="<?= date('Y-m-d') ?>" required class="form-control" />
            </div>
            <div class="space-y-1">
                <label for="todate">Start Date</label>
                <input id="todate" name="todate" type="date" min="<?= date('Y-m-d') ?>" required class="form-control" />
            </div>
            <div class="space-y-1">
                <label for="reason">Reason for Leave</label>
                <input id="reason" name="reason" type="text" required class="form-control" />
            </div>
        </form>
        <button type="submit" form="request-loa-form" class="px-3 py-2 mt-3 rounded-md shadow-md bg-primary text-primary-text focus:outline-none focus:ring-2 focus:ring-transparent focus:ring-offset-1 focus:ring-offset-black dark:focus:ring-offset-white">
            Submit Request
        </button>
    </section>
    <section id="loa-requests" class="mb-3">
        <h2 class="mb-2 text-2xl font-bold">LOA Requests</h2>
        <div class="inline-block w-full align-middle">
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase">
                                Dates
                            </th>
                            <th scope="col" class="hidden px-6 py-3 text-xs font-medium tracking-wider text-left uppercase lg:table-cell">
                                Reason
                            </th>
                            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase">
                                Status
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (Page::$pageData->requests as $l) : ?>
                            <tr>
                                <td class="hidden lg:table-cell">
                                    <?= $l->fromdate ?> &rarr; <?= $l->todate ?>
                                </td>
                                <td class="hidden lg:table-cell">
                                    <?= $l->reason ?>
                                </td>
                                <?php $status = ActivityPlugin::$tailwind_statuses[$l->status]; ?>
                                <td>
                                    <span class="inline-flex px-2 text-xs font-semibold leading-5 <?= $status['badge'] ?> rounded-full">
                                        <?= $status['label'] ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>