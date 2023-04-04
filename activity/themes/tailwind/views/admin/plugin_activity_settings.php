<?php
Page::setTitle('Activity Settings - ' . Page::$pageData->va_name);
require_once __DIR__ . '/../../includes/header.php';
?>
<div id="content" class="text-black dark:text-white m-5">
    <h1 class="mb-3 text-3xl font-bold">Activity Settings</h1>
    <form method="post" class="space-y-2" id="activity-settings">
        <input hidden name="action" value="save" />
        <div class="space-y-1">
            <label for="active">PIREP Requirement</label>
            <input id="active" name="active" type="number" required class="form-control" value="<?= Page::$pageData->active_days ?>" />
            <small class="text-gray-500">Pilots must file 1 PIREP per this many days</small>
        </div>
        <div class="space-y-1">
            <label for="new">New Pilot Days</label>
            <input id="new" name="new" type="number" required class="form-control" value="<?= Page::$pageData->new_days ?>" />
            <small class="text-gray-500">Pilots must file a PIREP within this many days after joining and are otherwise marked as inactive</small>
        </div>
    </form>
    <button type="submit" form="activity-settings" class="px-3 py-2 mt-3 rounded-md shadow-md bg-primary text-primary-text focus:outline-none focus:ring-2 focus:ring-transparent focus:ring-offset-1 focus:ring-offset-black dark:focus:ring-offset-white">
        Save
    </button>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>