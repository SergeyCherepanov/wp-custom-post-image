<?php
/**
 * Plugin Name: Custom Post Image
 * Plugin URI: www.cherepanov.org.ua
 * Description: Custom post & page images
 * Version: 1.0
 * Author: Sergey Cherepanov
 * Author URI: mailto:s@cherepanov.org.ua
 */

// Future-friendly json_encode
if (!function_exists('json_encode')) {
    include_once(navigation::$abspath . '/lib/JSON.php');
    function json_encode($data)
    {
        $json = new Services_JSON();
        return ($json->encode($data));
    }
}

// Future-friendly json_decode
if (!function_exists('json_decode')) {
    include_once(navigation::$abspath . '/lib/JSON.php');
    function json_decode($data)
    {
        $json = new Services_JSON();
        return ($json->decode($data));
    }
}

class postImages
{
    static function init_meta_boxes()
    {
        add_meta_box('post_images', __('Images'), array('postImages', 'meta_box_control'), 'page', 'normal', 'high');
        add_meta_box('post_images', __('Images'), array('postImages', 'meta_box_control'), 'post', 'normal', 'high');
        add_meta_box('post_images', __('Images'), array('postImages', 'meta_box_control'), 'product', 'normal', 'high');
    }

    /**
     * @param $post
     * @return int
     */
    static function meta_box_control($post)
    {
        if (!function_exists('file_manager_insert_script') || !function_exists('thumbnail_js')) {

            return print('To work correctly, you must install the plugins: <strong>&laquo;file manager&raquo;</strong> and <strong>&laquo;thumbnail&raquo;</strong>.');

        }
        file_manager_insert_script();

        $upload_dir = wp_upload_dir();

        $images = json_decode(get_post_meta($post->ID, '_post_images', true));

        ?>
        <style type="text/css">
            .fadeimg {
                float: left;
                height: 110px;
                margin-right: 10px;
            }

            .fadeimg img {
                float: left;
                border: 1px solid #ccc;
                padding: 3px;
            }

            .fadeimg a {
                clear: both;
                float: left;
                text-align: center;
            }
        </style>
        <div id="postImages">
            <?php
            if (is_array($images) && !empty($images)) {
                foreach ($images as $image) {
                    echo '
                                <span class="fadeimg"><input type="hidden" name="post_images[]" value="' . $image . '" />
                                    <img width="74" height="74" src="' . get_thumbnail(array('src' => $upload_dir['basedir'] . $image, 'width' => 74, 'height' => 74)) . '" alt="" />
                                    <a href="#remove" onclick="postImages.remove(this);return false;">' . __('Delete') . '</a>
                                </span>';
                }
            } else {
                echo '<input class="notfound" type="hidden" value="" name="images" />';
            }
            ?>
        </div>
        <p class="clear">
            <?php thumbnail_js(); ?>
            <script type="text/javascript">
                var postImages = {
                    add: function (src) {
                        var basedir = "<?php echo str_replace(constant('ABSPATH'), '', $upload_dir['basedir']);?>";
                        var img = new Image(74, 74);
                        img.src = get_thumbnail({src: basedir + src, width: 74, height: 74, method: 'crop'});
                        img.onload = function () {
                            jQuery('#postImages .notfound').remove();
                            var preparehtml = jQuery('<span class="fadeimg"><input type="hidden" name="post_images[]" value="' + src + '" /><a href="#remove" onclick="postImages.remove(this);return false;">Remove</a></span>');
                            preparehtml.find('input').after(this);
                            jQuery('#postImages').append(preparehtml);
                        }
                    },
                    remove: function (e) {
                        jQuery(e).parent('.fadeimg').remove();
                        if (typeof(jQuery('#postImages').children('span.fadeimg').get(0)) == 'undefined') {
                            jQuery('#postImages').append('<input class="notfound" type="hidden" value="" name="post_images" />');
                        }
                    }
                };
            </script>
        </p>
        <p>
            <input class="button" type="button" value="<?php echo __('Add'); ?>"
                   onclick="return file_manager('postImages.add')"/>
        </p>
        <?php
    }

    /**
     * @param int $post_ID
     */
    static function save($post_ID)
    {
        if (isset($_POST['post_images'])) {
            if (is_array($_POST['post_images'])) {

                update_post_meta($post_ID, '_post_images', json_encode($_POST['post_images']));

            } else {

                delete_post_meta($post_ID, '_post_images');

            }
        }
    }
}

/**
 * @param null|int $post_id
 * @return mixed
 */
function get_post_images($post_id = null)
{
    if (!$post_id) {
        global $post;
        $post_id = $post->ID;
    }

    return json_decode(get_post_meta($post_id, '_post_images', true));
}

/**
 * @param bool $post_id
 * @param bool $width
 * @param bool $height
 * @param string $method
 * @param string $background
 * @param bool $html
 * @return bool|string
 */
function post_main_image($post_id = false, $width = false, $height = false, $method = 'crop', $background = 'FFFFFF', $html = true)
{
    $images = get_post_images($post_id);

    if (!empty($images)):
        $image = array_shift($images);
        $uploaddir = wp_upload_dir();

        if ($width || $height) {
            $image_url = get_thumbnail(array('src' => $uploaddir['basedir'] . $image, 'width' => $width, 'height' => $height, 'method' => $method, 'background' => $background));
        } else {
            $image_url = $uploaddir['baseurl'] . $image;
        }
        if ($html) {
            echo '<img src="' . $image_url . '" ' . ($width ? 'width="' . $width . '"' : '') . ' ' . ($height ? 'height="' . $height . '"' : '') . ' alt=""/>';
        } else {
            return $image_url;
        }
    endif;
}

/**
 * @param bool $post_id
 * @param bool $width
 * @param bool $height
 * @param string $method
 * @param string $background
 */
function list_post_images($post_id = false, $width = false, $height = false, $method = 'crop', $background = 'FFFFFF')
{
    $images = get_post_images($post_id);

    if (!empty($images)):
        echo '<div class="wp-post-images"><ul class="list-wp-post-images">';

        $i = 0;
        foreach ($images as $image) {

            $uploaddir = wp_upload_dir();

            if ($width || $height) {
                $image_url = get_thumbnail(array('src' => $uploaddir['basedir'] . $image, 'width' => $width, 'height' => $height, 'method' => $method, 'background' => $background));
            } else {
                $image_url = $uploaddir['baseurl'] . $image;
            }

            echo '<li class="post-image-item order-' . (++$i) . '"><span><img src="' . $image_url . '" ' . ($width ? 'width="' . $width . '"' : '') . ' ' . ($height ? 'height="' . $height . '"' : '') . ' alt=""/></span></li>';
        }

        echo '</ul></div>';

    endif;
}

if (is_admin()) {
    add_action('load-post.php', array('postImages', 'init_meta_boxes'));
    add_action('load-post-new.php', array('postImages', 'init_meta_boxes'));

    add_action('load-page.php', array('postImages', 'init_meta_boxes'));
    add_action('load-page-new.php', array('postImages', 'init_meta_boxes'));
    add_action('edit_post', array('postImages', 'save'));
}
