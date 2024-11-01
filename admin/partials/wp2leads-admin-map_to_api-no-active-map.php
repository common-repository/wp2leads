<?php
/**
 * No Active Map Template
 *
 * @package Wp2Leads/Partials/MapToAPI
 * @version 1.0.1.7
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div id="map-to-api__container" class="no-active-map">
    <div id="map-to-api__body">
        <div id="map-to-api__results" class="results-holder">
            <?php require_once dirname( __FILE__ ) . '/wp2leads-admin-select-map.php'; ?>
        </div>

        <div id="map-to-api__map-list" class="active">
            <?php require_once dirname( __FILE__ ) . '/wp2leads-admin-runner-map-list.php'; ?>
        </div>
    </div>
</div>