<?php
/**
 * @var $changelog_link
 */

defined( 'ABSPATH' ) || exit;

if (!function_exists('plugins_api')) {
    include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
}

$api = plugins_api(
    'plugin_information',
    array(
        'slug' => wp_unslash( 'wp2leads' ),
    )
);

if ( !is_wp_error( $api ) && !empty( (array) $api->sections )) {

    $changelog_version = get_option('wp2leads_changelog_version');
    $current_version = WP2LEADS_VERSION;

    update_option('wp2leads_changelog_version', $current_version);

    $plugins_allowedtags = array(
        'a'          => array(
            'href'   => array(),
            'title'  => array(),
            'target' => array(),
        ),
        'abbr'       => array( 'title' => array() ),
        'acronym'    => array( 'title' => array() ),
        'code'       => array(),
        'pre'        => array(),
        'em'         => array(),
        'strong'     => array(),
        'div'        => array( 'class' => array() ),
        'span'       => array( 'class' => array() ),
        'p'          => array(),
        'br'         => array(),
        'ul'         => array(),
        'ol'         => array(),
        'li'         => array(),
        'h1'         => array(),
        'h2'         => array(),
        'h3'         => array(),
        'h4'         => array(),
        'h5'         => array(),
        'h6'         => array(),
        'img'        => array(
            'src'   => array(),
            'class' => array(),
            'alt'   => array(),
        ),
        'blockquote' => array( 'cite' => true ),
    );
    $changelog = $api->sections['changelog'];
    // $changelog = wp_kses($changelog, $plugins_allowedtags);

    ob_start();
    ?>
    <style>
        #wi-plugin-information .section ul, #wi-plugin-information .section ol {
            list-style-type: disc;
            margin-left: 24px;
        }

        #wi-plugin-information iframe {
            max-width: 100%;
        }



        @media (max-width: 640px) {

            #wi-plugin-information .embed-youtube {
                position: relative;
                padding-bottom: 56.25%; /* 16:9 */
                height: 0;
            }
            #wi-plugin-information .embed-youtube iframe {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
            }
        }
    </style>
    <?php
    echo $changelog;
    $changelog_html = ob_get_clean();
} else {
    ob_start();
    ?>
    <iframe scrolling="no" width="100%" height="188200px" src="<?php echo $changelog_link; ?>" title="<?php _e( "WP2LEADS Support", "wp2leads" ); ?>" style="border:none;"></iframe>

    <style>
        iframe{
            overflow:hidden;
        }
    </style>
    <?php
    $changelog_html = ob_get_clean();
}
?>

<div id="wi-plugin-information" class="wrap">
    <div class="row">
        <div class="col-xs-12 col-md-8">
            <h2><?php echo __('Changelog', 'wp2leads'); ?></h2>
        </div>
    </div>

    <div class="row">

        <div class="col-xs-12 col-md-8 section">
            <?php echo $changelog_html; ?>
        </div>
    </div>
</div>

