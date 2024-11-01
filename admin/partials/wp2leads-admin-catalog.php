<?php
/**
 * Template for displaying Import/Export tab content
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @since      1.0.0
 *
 * @package    Wp2leads
 * @subpackage Wp2leads/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}
$wp2l_no_map_server_response = get_transient('wp2l_no_map_server_response');
?>
<div>
	<h2 class="catalog-header"><?php _e('Catalog', 'wp2leads') ?></h2>
	<?php
		if (empty($_GET['welcome'])){
			echo '<a href="' . esc_url(get_admin_url(null, 'admin.php?page=wp2l-admin&tab=catalog&welcome=1')) . '">(' . __('Show more Instructions', 'wp2leads') . ')</a>';
		}
	?>
</div>
<hr>

<?php $w = Wp2leads_Catalog::show_welcome_text_1(); ?>

<?php
if (!$wp2l_no_map_server_response) {
    ?>
    <div class="catalog_list api-processing-holder">
        <div class="catalog_tags">
            <button class="catalog-tag all active" data-tag=""><?php _e('All', 'wp2leads'); ?></button>
            <?php
            if($all_tags = Wp2leads_Catalog::get_all_tags()) {
                foreach ($all_tags as $tag) {
                    echo '<button class="catalog-tag tag-'.$tag['id'].'" data-tag="'.$tag['id'].'">'.$tag['name'].'</button> ';
                }
            } ?>
        </div>
        <div class="catalog_wrap"></div>
        <div class="load_more">
            <button type="submit" class="button button-primary"><?php _e('Load More', 'wp2leads') ?></button>
        </div>
        <div class="api-spinner-holder">
            <div class="api-spinner"></div>
        </div>
    </div>
    <?php
} else {
    ?>
    <div class="notice notice-error inline">
        <h3><?php _e('No response from map server', 'wp2leads') ?></h3>

        <p>
            <strong><?php _e('Possible reason', 'wp2leads') ?>:</strong> <?php _e('Your web hosting is blocking the connection to our server so we can not load catalog items. Please write your web hosting and let them add the full URL https://maps.wp2leads.com/server/maps.php to the server whitelist. Sorry for the trouble.', 'wp2leads') ?>
        </p>
    </div>
    <?php
}
?>

<?php Wp2leads_Catalog::show_welcome_text_2($w); ?>