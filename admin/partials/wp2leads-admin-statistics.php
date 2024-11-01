<?php
/**
 * Statistic Page
 *
 * @package Wp2Leads/Partials
 * @version 1.1.0
 * @since 0.0.1
 * @var $activeMap
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$statistic_map_id = false;
$failed_item = false;
$show_failed_items = false;
$statistics_list = false;
$failed_items_list = false;

$maps = StatisticsManager::getMaps();

if (!empty($_GET['failed_items_list'])) {
    $show_failed_items = $_GET['failed_items_list'] === 'show' ? true : false;

    if (!empty($_GET['failed_item'])) {
        $failed_item = $_GET['failed_item'];
    }
}

$limit = !empty($_GET['wp2l_limit']) ? $_GET['wp2l_limit'] : StatisticsModel::$limit;
$page = !empty($_GET['wp2l_page']) ? (int)$_GET['wp2l_page'] : 1;

$args = array(
    'limit' => $limit,
    'page' => $page
);

if ($activeMap) {
    $statistic_map_id = $activeMap->id;
    $args['map_id'] = $statistic_map_id;

    if ($show_failed_items) {
        $failed_items_list = FailedTransferModel::getListByMapId($statistic_map_id);

        if (empty($failed_items_list)) {
            $statistics_list = StatisticsModel::getList($args);
        }
    } else {
        $statistics_list = StatisticsModel::getList($args);
    }

} else {
    $no_map = !empty($_GET['no_map']) ? 1 : false;

    if (!empty($no_map)) {
        $args['no_map'] = 1;
    }

    $statistics_list = StatisticsModel::getList($args);
}

if (!empty($statistics_list)) {
    $pagination_params = array(
        'limit' => $limit,
        'page' => $page
    );

    if ($activeMap) {
        $pagination_params['map_id'] = $statistic_map_id;
    } elseif (!empty($no_map)) {
        $pagination_params['no_map'] = $no_map;
    }
}

if ($statistic_map_id) {
    ?>
    <h2>
        <?php _e('Statistics for map:', 'wp2leads') ?> <?php echo $activeMap->name; ?> (id <?php echo $statistic_map_id; ?>)
    </h2>
    <?php
} else {
    ?>
    <h2><?php _e('Statistics', 'wp2leads') ?></h2>
    <?php
}
?>

<hr>

<?php
if (!empty($failed_items_list)) {
    $statistic_fields = false;
    $statistic_tags = false;
    $statistic_optins = false;
    $statistic_connector = new Wp2leads_KlicktippConnector();
    $statistic_logged_in = $statistic_connector->login();

    if (!$statistic_logged_in) {
        $statistic_connector_error = $statistic_connector->get_last_error(false);
    } else {
        $statistic_fields = $statistic_connector->field_index();
        $statistic_tags   = (array) $statistic_connector->tag_index();
        asort( $statistic_tags, SORT_STRING | SORT_FLAG_CASE | SORT_NATURAL );
        $statistic_optins = $statistic_connector->subscription_process_index();
    }

    include_once 'wp2leads-admin-statistics-failed-items.php';
} elseif (!empty($statistics_list)) {
    include_once 'wp2leads-admin-statistics-list.php';
} else {
    include_once 'wp2leads-admin-statistics-empty.php';
}
?>
