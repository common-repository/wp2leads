<?php
/**
 * Template for displaying button group on Map Builder tab
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @since      1.0.0
 *
 * @package    Wp2leads
 * @subpackage Wp2leads/admin/partials
 */

$disabled_class = '';
if ( !$activeMap && !$mapForDuplicate && !$is_create_own_map_allowed) {
    $disabled_class = ' disabled';
}
?>
<span class="alignright">
    <a href="?page=wp2l-admin&tab=map_builder" class="button button-primary">
        <?php _e( 'Exit Map', 'wp2leads' ) ?>
    </a>
</span>