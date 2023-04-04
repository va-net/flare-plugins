<?php
Page::setTitle('Pilot LOA - ' . Page::$pageData->va_name);
require_once __DIR__ . '/../../includes/header.php';
?>
<script type="application/json" id="pendingLeave">
    <?= Json::encode(Page::$pageData->pending_leave) ?>
</script>
<script type="application/json" id="approvedLeave">
    <?= Json::encode(Page::$pageData->approved_leave) ?>
</script>
<script>
    var pendingLeave = JSON.parse(document.getElementById('pendingLeave').innerHTML);
    var approvedLeave = JSON.parse(document.getElementById('approvedLeave').innerHTML);
</script>
<div id="content" class="text-black dark:text-white">
    <div class="flex w-full p-5 dark:bg-gray-600 bg-gray-100 py-7 mb-4 items-center gap-2">
        <h2 class="flex-1 text-2xl font-bold lg:text-4xl">
            Pilot LOA
        </h2>
    </div>

    <div class="md:px-5 px-2 max-w-full">
        <!-- Pending LOA -->
        <div class="bg-black/10 dark:bg-white/10 p-3 rounded shadow mb-4" x-data="{ table: { current: [], orderBy: (x) => x.fromdate, orderByName: 'Start Date', order: 'asc', search: '', filters: [] }, refresh() { return updateDataTable(pendingLeave, this.table) }, }">
            <h3 class="text-2xl font-bold mb-2">Pending LOA</h3>
            <div class="flex gap-2 items-center mb-2">
                <input type="text" :value="table.search" class="form-control flex-1" placeholder="Search" @input="table.search = $event.target.value; updateDataTable(pendingLeave, table);" />
                <div class="text-sm">
                    <p x-text="`Ordering by ${table.orderByName}`"></p>
                    <p x-text="`${table.current.length} result${table.current.length == 1 ? '' : 's'}`"></p>
                </div>
            </div>
            <div class="table-wrapper mb-1">
                <table class="table" x-init="refresh()">
                    <thead>
                        <tr>
                            <th class="cursor-pointer" @click="dataTableOrder((x) => x.fromdate, $el.textContent, table)">Dates</th>
                            <th>Pilot</th>
                            <th>Reason</th>
                            <th><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <form method="post" x-ref="acceptLeave">
                            <input type="hidden" name="action" value="accept" />
                            <input type="hidden" name="id" x-ref="acceptLeave-id" value="" />
                        </form>
                        <form method="post" x-ref="denyLeave">
                            <input type="hidden" name="action" value="deny" />
                            <input type="hidden" name="id" x-ref="denyLeave-id" value="" />
                        </form>
                        <template x-for="leave in table.current" :key="leave.id">
                            <tr class="hover:bg-black/20 cursor-pointer" @click="window.location.href = `/admin/users/${user.id}`">
                                <td x-text="`${new Date(leave.fromdate).toLocaleDateString()} → ${new Date(leave.todate).toLocaleDateString()}`"></td>
                                <td x-text="leave.pilot"></td>
                                <td x-text="leave.reason"></td>
                                <td class="flex justify-end items-center gap-2">
                                    <button @click.stop="$refs['acceptLeave-id'].value = leave.id; $refs.acceptLeave.submit();" class="px-2 py-1 text-lg font-semibold rounded-md shadow-md hover:shadow-lg bg-green-600 text-white focus:outline-none focus:ring-2 focus:ring-transparent focus:ring-offset-1 focus:ring-offset-black dark:focus:ring-offset-white">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </button>
                                    <button @click.stop="$refs['denyLeave-id'].value = leave.id; $refs.denyLeave.submit();" class="px-2 py-1 text-lg font-semibold rounded-md shadow-md hover:shadow-lg bg-red-600 text-white focus:outline-none focus:ring-2 focus:ring-transparent focus:ring-offset-1 focus:ring-offset-black dark:focus:ring-offset-white">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Current LOA -->
        <div class="bg-black/10 dark:bg-white/10 p-3 rounded shadow" x-data="{ table: { current: [], orderBy: (x) => x.fromdate, orderByName: 'Start Date', order: 'asc', search: '', filters: [] }, refresh() { return updateDataTable(approvedLeave, this.table) }, }">
            <h3 class="text-2xl font-bold mb-2">Accepted LOA</h3>
            <div class="flex gap-2 items-center mb-2">
                <input type="text" :value="table.search" class="form-control flex-1" placeholder="Search" @input="table.search = $event.target.value; updateDataTable(approvedLeave, table);" />
                <div class="text-sm">
                    <p x-text="`Ordering by ${table.orderByName}`"></p>
                    <p x-text="`${table.current.length} result${table.current.length == 1 ? '' : 's'}`"></p>
                </div>
            </div>
            <div class="table-wrapper mb-1">
                <table class="table" x-init="refresh()">
                    <thead>
                        <tr>
                            <th class="cursor-pointer" @click="dataTableOrder((x) => x.fromdate, $el.textContent, table)">Dates</th>
                            <th>Pilot</th>
                            <th>Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="leave in table.current" :key="leave.id">
                            <tr>
                                <td x-text="`${new Date(leave.fromdate).toLocaleDateString()} → ${new Date(leave.todate).toLocaleDateString()}`"></td>
                                <td x-text="leave.pilot"></td>
                                <td x-text="leave.reason"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>