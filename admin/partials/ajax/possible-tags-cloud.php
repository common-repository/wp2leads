<?php
/**
 * Possible tags cloud list Template
 */

if (!empty($kt_tags)) {
    foreach ($kt_tags as $kt_tag) {

        $class = ' selected-tag-kt';

        if (!empty($manually_tags)) {
            foreach ($manually_tags as $key => $manually_tag) {
                if (!empty($manually_tag) && $kt_tag === $manually_tag) {
                    $class .= ' selected-tag-manual';

                    unset($manually_tags[$key]);
                }
            }
        }

        if (!empty($tags)) {
            foreach ($tags as $key => $tag) {
                if (!empty($tag)) {
                    $kt_tag_to_lower = strtolower($tags_cloud[$kt_tag]);
                    $tag_to_lower = strtolower($tag);

                    if ($kt_tag_to_lower === $tag_to_lower) {
                        $class .= ' selected-tag-manual';

                        unset($tags[$key]);
                    }
                }
            }
        }

        if (!empty($detach_tags)) {
            foreach ($detach_tags as $detach_tag) {
                if (!empty($detach_tag) && $kt_tag === $detach_tag) {
                    $class .= ' selected-tag-detach';
                }
            }
        }

        ?>
        <div class="selected-tag<?php echo $class ?>" data-tag-id="<?php echo $kt_tag ?>"><?php echo $tags_cloud[$kt_tag] ?></div><br>
        <?php
    }

}

$manually_tags_array = array();
$auto_tags_array = array();

if (!empty($manually_tags)) {
    foreach ($manually_tags as $key => $manually_tag) {
        if (!empty($detach_tags)) {
            foreach ($detach_tags as $detach_tag) {
                if (!empty($detach_tag) && $manually_tag === $detach_tag) {
                    unset($manually_tags[$key]);
                }
            }
        }
    }

    foreach ($manually_tags as $manually_tag) {
        if (!empty($manually_tag)) {
            $manually_tags_array[$manually_tag] = $tags_cloud[$manually_tag];
        }
    }
}

if (!empty($tags)) {
    foreach ($tags as $key => $tag) {
        foreach ($manually_tags as $manually_tag) {
            if (empty($tag) || (!empty($manually_tag) && strtolower($tags_cloud[$manually_tag]) === strtolower($tag))) {
                unset($tags[$key]);
            }
        }
    }

    foreach ($tags as $key => $tag) {
        foreach ($detach_tags as $detach_tag) {
            if (empty($tag) || (!empty($detach_tag) && strtolower($tags_cloud[$detach_tag]) === strtolower($tag))) {
                unset($tags[$key]);
            }
        }
    }

    foreach ($tags as $tag) {
        $data_tag_id = '';

        foreach ($tags_cloud as $tag_id => $tag_label) {
            if (trim(strtolower($tag_label)) === trim(strtolower($tag))) {
                $data_tag_id = $tag_id;

                continue;
            }
        }

        if (!empty($data_tag_id)) {
            $manually_tags_array[$data_tag_id] = $tag;
        } else {
            $auto_tags_array[] = $tag;
        }
    }
}

foreach ($manually_tags_array as $tag_id => $tag) {
    ?>
    <div class="selected-tag selected-tag-added" data-tag-id="<?php echo $tag_id ?>"><?php echo $tag ?></div><br>
    <?php
}

foreach ($auto_tags_array as $tag) {
    ?>
    <div class="selected-tag selected-tag-new" data-tag-id=""><?php echo $tag ?></div><br>
    <?php
}
?>
