<?php
Page::setTitle('Pilot Activity - ' . Page::$pageData->va_name);
require_once __DIR__ . '/../../includes/header.php';
?>
<script type="application/json" id="allEntries">
    <?php
    $inactive = array_map(function ($p) {
        $p->status = 'Inactive';
        return $p;
    }, Page::$pageData->pilots->inactive);
    $leave = array_map(function ($p) {
        $p->status = 'On Leave';
        return $p;
    }, Page::$pageData->pilots->leave);
    echo Json::encode(array_merge($inactive, $leave));
    ?>
</script>
<script>
    var allEntries = JSON.parse(document.getElementById('allEntries').innerHTML);
</script>
<div id="content" class="text-black dark:text-white" x-data="{ table: { current: [], orderBy: (x) => x.name, orderByName: 'Name', order: 'desc', search: '', filters: [] }, refresh() { return updateDataTable(allEntries, this.table) }, }">
    <div class="flex w-full p-5 dark:bg-gray-600 bg-gray-100 py-7 mb-4 items-center gap-2">
        <h2 class="flex-1 text-2xl font-bold lg:text-4xl">
            Pilot Activity
        </h2>
    </div>

    <div class="md:px-5 px-2 max-w-full">
        <div class="flex gap-2 items-center mb-2">
            <input type="text" :value="table.search" class="form-control flex-1" placeholder="Search" @input="table.search = $event.target.value; updateDataTable(allEntries, table);" />
            <div class="text-sm">
                <p x-text="`Ordering by ${table.orderByName}`"></p>
                <p x-text="`${table.current.length} result${table.current.length == 1 ? '' : 's'}`"></p>
            </div>
        </div>
        <div class="table-wrapper mb-1">
            <table class="table" x-init="refresh()">
                <thead>
                    <tr>
                        <th class="cursor-pointer" @click="dataTableOrder((x) => x.callsign, $el.textContent, table)">Callsign</th>
                        <th class="cursor-pointer" @click="dataTableOrder((x) => x.name, $el.textContent, table)">Name</th>
                        <th>Status</th>
                        <th><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    <form method="post" x-ref="retirePilot">
                        <input type="hidden" name="action" value="retire" />
                        <input type="hidden" name="pilot" x-ref="retirePilot-id" value="" />
                    </form>
                    <template x-for="user in table.current" :key="user.id">
                        <tr class="hover:bg-black/20 cursor-pointer" @click="window.location.href = `/admin/users/${user.id}`">
                            <td x-text="user.callsign"></td>
                            <td x-text="user.name"></td>
                            <td x-text="user.status"></td>
                            <td class="flex justify-end items-center gap-2" x-show="user.status === 'Inactive'">
                                <button @click.stop="confirm('Are you sure you want to retire this pilot?') && (() => { $refs['retirePilot-id'].value = user.id; $refs.retirePilot.submit(); })()" class="px-2 py-1 text-lg rounded-md shadow-md hover:shadow-lg bg-red-600 text-white focus:outline-none focus:ring-2 focus:ring-transparent focus:ring-offset-1 focus:ring-offset-black dark:focus:ring-offset-white">
                                    Retire
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>