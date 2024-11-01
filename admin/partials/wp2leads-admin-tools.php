<?php
/**
 * Created by PhpStorm.
 * User: oleksii.khodakivskyi
 * Date: 26.08.18
 * Time: 21:43
 */

$connector = new Wp2leads_KlicktippConnector();
$logged_in = $connector->login();

if ($logged_in) {
    $tags = (array) $connector->tag_index();
    asort($tags, SORT_STRING | SORT_FLAG_CASE | SORT_NATURAL);
?>

<h2><?php _e('Tools', 'wp2leads') ?></h2>
<hr>

<div class="remove-tags-wrapper">
    <h3><?php _e( 'Select Tags for deletion', 'wp2leads' ) ?></h3>
    <input type="text" value="" placeholder="<?php _e( 'Filter tags...', 'wp2leads' ) ?>" class="tag-text">
    <button id="select-all-for-remove" class="button button-primary"><?php echo _e('Select all', 'wp2leads'); ?></button>
    <button id="deselect-all-for-remove" class="button button-primary"><?php echo _e('Deselect all', 'wp2leads'); ?></button>
    <button id="remove-tag" class="button button-primary"><?php echo _e('Remove tag', 'wp2leads'); ?></button>
    <div class="remove-tags-cloud-wrapper">
        <div class="remove-tags-cloud">
            <?php foreach ($tags as $tag_code => $tag_name) {?>
                <fieldset>
                    <input id="remove_<?php echo $tag_code; ?>" type="checkbox" value="<?php echo $tag_code; ?>" data-name="<?php echo $tag_name; ?>">
                    <label for="remove_<?php echo $tag_code; ?>"><?php echo $tag_name; ?></label>
                </fieldset>
            <?php }?>
        </div>
    </div>
</div>
<?php }?>