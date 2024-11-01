<?php
/**
 * Created by PhpStorm.
 * User: snth
 * Date: 20.01.19
 * Time: 11:40
 */
?>

<div id="tags-cloud-options" class="tags-cloud" data-tags-cloud='<?php echo json_encode($tags, JSON_FORCE_OBJECT) ?>'>
    <?php
    $manually_selected_tags = isset($decodedSendApiSettings['manually_selected_tags']['tag_ids']) ? $decodedSendApiSettings['manually_selected_tags']['tag_ids'] : array();

    foreach ($tags as $tag_code => $tag_name) {
        ?>
        <fieldset>
            <input
                <?php echo in_array($tag_code, $manually_selected_tags) ? 'checked' : ''; ?>
                    id="<?php echo $tag_code; ?>"
                    type="checkbox"
                    value="<?php echo $tag_name; ?>"
                    data-name="<?php echo $tag_name; ?>"
            >
            <label for="<?php echo $tag_code; ?>"><?php echo $tag_name; ?></label>
        </fieldset>
        <?php
    }
    ?>
</div>