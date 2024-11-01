<?php
/**
 * Created by PhpStorm.
 * User: snth
 * Date: 25.01.19
 * Time: 16:50
 */

$preparing_array = array();
?>

<p><strong><?php _e('WP2Leads', 'wp2leads') ?></strong>: <?php _e('Background transfer running', 'wp2leads') ?></p>
<?php
if (!empty($maps_prepare_in_progress) && is_array($maps_prepare_in_progress)) {
    foreach ($maps_prepare_in_progress as $map_id => $process) {
        $map_object = MapsModel::get($map_id);
        ?>
        <p>
            <strong><?php _e('Map', 'wp2leads') ?>: <?php echo $map_object->name; ?> (<?php _e('id', 'wp2leads') ?> <?php echo $map_id ?>)</strong>:
            <?php _e('Preparing data to transfer', 'wp2leads') ?>
            <button class="terminate-bg-map-to-api button button-primary button-small" data-map="<?php echo $map_id ?>"><?php _e('Stop transfer', 'wp2leads'); ?></button>
        </p>
        <?php
        $preparing_array[] = $map_id;
    }
}

if (!empty($maps_load_in_progress) && is_array($maps_load_in_progress)) {
    foreach ($maps_load_in_progress as $map_id => $process) {
        $map_object = MapsModel::get($map_id);
        ?>
        <p>
            <strong><?php _e('Map', 'wp2leads') ?>: <?php echo $map_object->name; ?> (<?php _e('id', 'wp2leads') ?> <?php echo $map_id ?>)</strong>:
            <?php _e('Preparing data to transfer', 'wp2leads') ?>
            <button class="terminate-bg-map-to-api button button-primary button-small" data-map="<?php echo $map_id ?>"><?php _e('Stop transfer', 'wp2leads'); ?></button>
        </p>
        <?php
        $preparing_array[] = $map_id;
    }
}

if (!empty($wp2l_map_to_api_in_progress) && is_array($wp2l_map_to_api_in_progress)) {
    foreach ($wp2l_map_to_api_in_progress as $map_id => $process) {
        if (!in_array($map_id, $preparing_array)) {
            $map_object = MapsModel::get($map_id);

            if (true) {
                ob_start();
                $count = count($process);
                ?>
                <p><strong><?php _e('Map', 'wp2leads') ?>: <?php echo $map_object->name; ?> (<?php _e('id', 'wp2leads') ?> <?php echo $map_id ?>)</strong>:
                    <?php
                    $bg_total = 0;
                    $bg_done = 0;
                    $bg_new = 0;
                    $bg_updated = 0;
                    $bg_failed = 0;

                    $href = '?page=wp2l-admin&tab=statistics&failed_items_list=show';
                    $href .= '&active_mapping=' . $map_id;

                    foreach ($process as $key => $item) {
                        $bg_total += $item['total'];
                        $bg_done += $item['done'];
                        $bg_new += $item['new'];
                        $bg_updated += $item['updated'];
                        $bg_failed += count($item['failed']);
                    }

                    $bg_count = $bg_total - $bg_done;
                    ?>
                    <?php _e('Total:', 'wp2leads') ?> <strong><?php echo $bg_total ?></strong> -
                    <?php _e('Done:', 'wp2leads') ?> <strong><?php echo $bg_done ?></strong> -
                    <?php _e('Left:', 'wp2leads') ?> <strong><?php echo $bg_count ?></strong> -
                    <?php _e('New subscribers:', 'wp2leads') ?> <strong><?php echo $bg_new ?></strong> -
                    <?php _e('Updated subscribers:', 'wp2leads') ?> <strong><?php echo $bg_updated ?></strong> -
                    <?php _e('Failed subscribers:', 'wp2leads') ?> <strong><?php echo $bg_failed ?></strong> (<a href="<?php echo $href ?>" target="_blank"><?php _e('why failed?', 'wp2leads') ?></a>) -
                    <?php _e('Status: ', 'wp2leads'); ?>
                    <strong>
                        <?php
                        if ( $bg_done === $bg_total ) {
                            ?>
                            <span style="color:#1c3732"><?php _e('Finished', 'wp2leads'); ?></span>
                            <?php
                        } elseif ($bg_done !== 0 && $bg_done < $bg_total) {
                            ?>
                            <span style="color:#FC4C11"><?php _e('Running', 'wp2leads'); ?></span>
                            <?php
                        } elseif ($bg_count === $bg_total) {
                            _e('Waiting', 'wp2leads') ;
                        }
                        ?>
                    </strong>

                    <button class="terminate-bg-map-to-api button button-primary button-small" data-map="<?php echo $map_id ?>"><?php _e('Stop transfer', 'wp2leads'); ?></button>
                </p>
                <?php
                $bg_stat_html = ob_get_clean();

                if (0 < $bg_count) {
                    echo $bg_stat_html;
                }
            }
        }
    }
}
?>