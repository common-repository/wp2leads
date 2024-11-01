<?php
$current_map = empty($activeMap) ? false : $activeMap;

if ($current_map) {
    $mapping = unserialize($current_map->mapping);
    $api = unserialize($current_map->api);
    $info = unserialize($current_map->info);

    ?>
    <div id="wp2leads_test_performance">

    </div>
    <?php
}