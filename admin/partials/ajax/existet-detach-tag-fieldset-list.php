<?php
/**
 * Created by PhpStorm.
 * User: snth
 * Date: 20.01.19
 * Time: 11:48
 */
?>

<?php
$detach_tags = isset($decodedSendApiSettings['detach_tags']['tag_ids']) ? $decodedSendApiSettings['detach_tags']['tag_ids'] : array();

foreach ($tags as $tag_code => $tag_name) {?>
    <fieldset>
        <input <?php echo in_array($tag_code, $detach_tags) ? 'checked' : ''; ?>
                id="detach_<?php echo $tag_code; ?>"
                type="checkbox"
                value="<?php echo $tag_code; ?>"
                data-name="<?php echo $tag_name; ?>"
        >
        <label for="detach_<?php echo $tag_code; ?>"><?php echo $tag_name; ?></label>
    </fieldset>
<?php }?>
