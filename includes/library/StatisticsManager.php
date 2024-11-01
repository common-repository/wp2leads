<?php
/**
 * Created by PhpStorm.
 * User: oleksii.khodakivskyi
 * Date: 06.09.18
 * Time: 23:24
 */

class StatisticsManager {

    /**
     * Filtering and saving statistics data using StatisticsModel
     *
     * @param $data
     */
    public static function saveStatistics($data) {
        if (!empty($data)) {
            $data['time'] = date('Y-m-d H:i:s', $data['time']);
            $data['statistics'] = json_encode($data['statistics']);
            StatisticsModel::insert($data);
        }
    }

    public static function getTotallyTransferedData($map_id) {
        $result = StatisticsModel::get( array( 'map_id' => $map_id ), array( 'statistics' ) );

        $times = StatisticsModel::get( array( 'map_id' => $map_id, 'transfer_type' => 'manually' ), array( 'time' ) );
        $crontimes = StatisticsModel::get( array( 'map_id' => $map_id, 'transfer_type' => 'cron' ), array( 'time' ) );
        $last_time = '';
        $last_crontime = '';

        foreach ($times as $time) {
            $last_time = $time['time'];

            break;
        }

        foreach ($crontimes as $time) {
            $last_crontime = $time['time'];

            break;
        }

        $transfered = array(
            'unique' => 0,
            'all'  => 0,
            'failed'  => 0,
            'time'  => $last_time,
            'crontime'  => $last_crontime,
        );

        foreach ( $result as $statistic ) {
            $decoded_statistic = json_decode( $statistic['statistics'], true );

            if (!empty($decoded_statistic[__( 'New subscribers', 'wp2leads' )])) {
                $transfered['unique'] += (int) $decoded_statistic[__( 'New subscribers', 'wp2leads' )];
            }

            if (!empty($decoded_statistic[__( 'Total transferred', 'wp2leads' )])) {
                $transfered['all'] += (int) $decoded_statistic[__( 'Total transferred', 'wp2leads' )];
            }

            if (!empty($decoded_statistic[__( 'Failed subscribers', 'wp2leads' )])) {
                $transfered['failed'] += (int) count($decoded_statistic[__( 'Failed subscribers', 'wp2leads' )]);
            }
        }

        return $transfered;
    }

    public static function getLastTotallyTransferedData($map_id) {
        $result = StatisticsModel::get( array( 'map_id' => $map_id ), array( 'statistics' ) );

        $transfered = array(
            'unique' => 0,
            'all'  => 0,
        );

        foreach ( $result as $statistic ) {
            $decoded_statistic = json_decode( $statistic['statistics'], true );

            $transfered['unique'] += (int) $decoded_statistic[__( 'New subscribers', 'wp2leads' )];
            $transfered['all'] += (int) $decoded_statistic[__( 'Total transferred', 'wp2leads' )];
        }

        return $transfered;
    }

    /**
     * Returnign the list of all available maps
     *
     * @return array
     */
    public static function getMaps() {
        global $wpdb;
        $maps = array();
        $table_name = $wpdb->prefix . 'wp2l_maps';
        $result = $wpdb->get_results("SELECT id, name FROM {$table_name} ORDER BY id DESC", ARRAY_A);

        foreach ($result as $row) {
            $maps[$row['id']] = array(
                'name' => $row['name']
            );
        }

        return $maps;
    }

    public static function getTimeZone() {
        $time_zone = get_option('timezone_string');

        if (!empty($time_zone)) {
            return $time_zone;
        }

        $current_offset = get_option('gmt_offset', 0);

        if (0 === $current_offset) {
            return 'UTC';
        }

        return 'UTC';
    }

    /**
     * Converting UNIX time to the Local time using time zone configured in the WP Settings
     *
     * @param $time
     * @return string
     */
    public static function convertTimeToLocal($time) {
        $format = 'Y-m-d H:i:s';
        $time_zone = StatisticsManager::getTimeZone();

        $dt = new DateTime();
        $dt->setTimestamp(strtotime($time));
        $dt->setTimezone(new DateTimeZone($time_zone));
        return $dt->format($format);
    }

    public static function get_pagination($params = array()) {
        $limit = StatisticsModel::$limit;
        $page = 1;

        if (!empty($params['limit'])) {
            $limit = (int)$params['limit'];
            unset($params['limit']);
        }

        if ($limit < StatisticsModel::$limit) {
            $limit = StatisticsModel::$limit;
        }

        if (!empty($params['page'])) {
            $page = (int)$params['page'];
            unset($params['page']);
        }

        $count = count(StatisticsModel::getList($params));
        $pages = ceil($count / $limit);
        $url = '?page=wp2l-admin&tab=statistics';

        if (!empty($params['map_id'])) {
            $url .= '&active_mapping=' . $params['map_id'];
        } elseif (!empty($params['no_map'])) {
            $url .= '&no_map=1';
        }

        ob_start();
        if (1 !== (int)$pages) {
            ?>
            <span class="pagination">
                <?php
                if (1 !== (int)$page) {
                    $prev_page = $page - 1;
                    $prev_url = $url . '&wp2l_page=' . $prev_page;
                    ?>
                    <a href="<?php echo $prev_url ?>" class="button button-primary" title="<?php echo __('Previous page', 'wp2leads'); ?>"><?php echo __('<', 'wp2leads'); ?></a>
                    <?php
                }
                ?>
                <span style="display:inline-block;margin-right:5px;margin-left:5px;">
                    <?php echo __('Page', 'wp2leads'); ?> <strong><?php echo $page ?></strong>
                    <?php echo __('out of', 'wp2leads'); ?> <strong><?php echo $pages ?></strong>
                </span>
                <?php

                if ((int)$pages !== (int)$page) {
                    $next_page = $page + 1;
                    $next_url = $url . '&wp2l_page=' . $next_page;
                    ?>
                    <a href="<?php echo $next_url ?>" class="button button-primary" title="<?php echo __('Next page', 'wp2leads'); ?>"><?php echo __('>', 'wp2leads'); ?></a>
                    <?php
                }
                ?>
            </span>
            <?php
        }
        $pagination = ob_get_clean();

        return $pagination;
    }

    public static function get_clickable_link($link) {
        if (0 !== strpos($link, 'http://') || 0 !== strpos($link, 'https://')) {
            $link = preg_replace('/^http:\/\//i', "", $link );
            $link = preg_replace('/^https:\/\//i', "", $link );
            $link = preg_replace('/^www./i', "", $link );

            $link = 'http://' . $link;
        }

        return $link;
    }
}