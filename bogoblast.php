<?php /*
    Plugin Name: Bogoblast
    Plugin URI: http://bogoblast.co
    Description: Bogoblast.co
    Version: 1
    Author: Daniel Grundel, Web Presence Partners
    Author URI: http://www.webpresencepartners.com
*/

    define('BOGOBLAST_DIR', plugin_dir_path(__FILE__));
    define('BOGOBLAST_URL', plugin_dir_url(__FILE__));

    require_once(BOGOBLAST_DIR.'bogoblast-util.php');
    require_once(BOGOBLAST_DIR.'bogoblast-email-message.php');
    require_once(BOGOBLAST_DIR.'bogoblast-wp_mail.php');

    class Bogoblast {
        public function __construct() {
            //Custom Post Types, Taxonomies, Statuses
            add_action('init', array(&$this, 'register_custom_post_types'));
            add_action('init', array(&$this, 'register_taxonomies'));
            add_action('init', array(&$this, 'register_custom_post_statuses'));

            //Admin Bar
            add_action('wp_before_admin_bar_render', array(&$this, 'admin_bar'));

            //Business Meta Box
            add_action('admin_menu', array(&$this, 'business_add_meta_box'));
            add_action('save_post', array(&$this, 'business_save_meta_box'));
            add_action('delete_post', array(&$this, 'business_delete_post'));
            if(is_admin()) add_action('admin_enqueue_scripts', array(&$this, 'business_admin_enqueue_scripts'));

            //Add and populate columns in Business list
            add_filter('manage_bogoblast_business_posts_columns', array(&$this, 'business_column_heads'));
            add_action('manage_bogoblast_business_posts_custom_column', array(&$this, 'business_column_contents'), 10, 2);

            //Business Comment/Review Fields
            add_action('comment_form_logged_in_after', array(&$this, 'business_comment_fields'));
            add_action('comment_form_after_fields', array(&$this, 'business_comment_fields'));
            add_action('comment_post', array(&$this, 'business_save_comment_meta_data'));
            add_filter('preprocess_comment', array(&$this, 'business_verify_comment_meta_data'));

            //Business Subscribe Shortcode
            add_shortcode('bogoblast_business_subscribe', array(&$this, 'business_subscribe_shortcode'));

            //Business Category Widget
            wp_register_sidebar_widget('bogoblast_business_category_widget', 'Bogoblast Business Categories', array(&$this, 'business_category_widget'));

            //City Widget
            wp_register_sidebar_widget('bogoblast_city_widget', 'Bogoblast Cities', array(&$this, 'city_widget'));

            //Business Pre Get Posts
            add_action('pre_get_posts', array(&$this, 'business_pre_get_posts'));

            //Promotion Meta Box
            add_action('admin_menu', array(&$this, 'promotion_add_meta_box'));
            add_action('save_post', array(&$this, 'promotion_save_meta_box'));

            //Add Custom Filtering Drop Down to Promotion List
            add_action('restrict_manage_posts', array(&$this, 'promotion_filter_drop_down'));
            add_filter('parse_query', array(&$this, 'promotion_posts_filter'));

            //Add and populate columns in Promotion list
            add_filter('manage_bogoblast_promotion_posts_columns', array(&$this, 'promotion_column_heads'));
            add_action('manage_bogoblast_promotion_posts_custom_column', array(&$this, 'promotion_column_contents'), 10, 2);

            //Promotion AJAX Action
            add_action('wp_ajax_bogoblast_promotion_queue_ajax', array(&$this, 'promotion_queue_ajax'));

            //Subscriber Meta Box
            add_action('admin_menu', array(&$this, 'subscriber_add_meta_box'));
            add_action('save_post', array(&$this, 'subscriber_save_meta_box'));

            //Add and populate columns in Subscriber list
            add_filter('manage_bogoblast_subscriber_posts_columns', array(&$this, 'subscriber_column_heads'));
            add_action('manage_bogoblast_subscriber_posts_custom_column', array(&$this, 'subscriber_column_contents'), 10, 2);

            //Subscriber Import
            add_action('admin_menu', array(&$this, 'bogoblast_subscriber_import_admin_menu'));
            add_action('wp_ajax_bogoblast-subscriber-import-ajax', array(&$this, 'bogoblast_subscriber_import_render_ajax_action'));

            //Subscriber Export
            add_action('admin_menu', array(&$this, 'bogoblast_subscriber_export_admin_menu'));

            //Subscriber Preferences Shortcode
            add_shortcode('bogoblast_subscriber_preferences', array(&$this, 'bogoblast_subscriber_preferences_shortcode'));

            //Bogoblast Mail Meta Box
            add_action('admin_menu', array(&$this, 'bogoblast_mail_add_meta_box'));

            //Add a Warning Message to the Mail List if Sending Paused
            add_action('restrict_manage_posts', array(&$this, 'mail_sending_paused_warning'));

            //Add and populate columns in Bogoblast Mail list
            add_filter('manage_bogoblast_mail_posts_columns', array(&$this, 'bogoblast_mail_column_heads'));
            add_action('manage_bogoblast_mail_posts_custom_column', array(&$this, 'bogoblast_mail_column_contents'), 10, 2);

            //Hide Bogoblast Mail 'Add New' buttons
            add_action('admin_menu', array(&$this, 'bogoblast_mail_hide_add_new_menu_item'));
            add_action('admin_head', array(&$this, 'bogoblast_mail_hide_add_new_header_button'));

            //Bogoblast Mail Tracking
            add_action('init', array(&$this, 'bogoblast_mail_stat'));

            //Hide Event Log 'Add New' buttons
            add_action('admin_menu', array(&$this, 'bogoblast_event_log_hide_add_new_menu_item'));
            add_action('admin_head', array(&$this, 'bogoblast_event_log_hide_add_new_header_button'));

            //Bogoblast Options
            add_action('admin_menu', array(&$this, 'bogoblast_options_admin_menu'));
            add_filter('admin_init', array(&$this , 'bogoblast_options_register_settings_fields'));

            //Twitter AJAX Action
            add_action('wp_ajax_bogoblast_twitter_ajax', array(&$this, 'twitter_ajax'));

            //Facebook AJAX Action
            add_action('wp_ajax_bogoblast_facebook_ajax', array(&$this, 'facebook_ajax'));

            //Flash Messages
            add_action('admin_notices', array(&$this, 'show_flash_messages'));
        }

        //Custom Post Types, Taxonomies, Statuses
        public function register_custom_post_types() {
            require(BOGOBLAST_DIR.'bogoblast-custom-post-types.php');
        }
        public function register_taxonomies() {
            require(BOGOBLAST_DIR.'bogoblast-taxonomies.php');
        }
        public function register_custom_post_statuses() {
            require(BOGOBLAST_DIR.'bogoblast-custom-post-statuses.php');
        }

        //Admin Bar
        public function admin_bar() {
            global $wp_admin_bar;

            $wp_admin_bar->add_menu( array(
                'parent' => 'site-name',
                'id' => 'bogoblast_business-index',
                'title' => __('Businesses'),
                'href' => admin_url( 'edit.php?post_type=bogoblast_business')
            ) );
            $wp_admin_bar->add_menu( array(
                'parent' => 'site-name',
                'id' => 'bogoblast_promotion-index',
                'title' => __('Promotions'),
                'href' => admin_url( 'edit.php?post_type=bogoblast_promotion')
            ) );
            $wp_admin_bar->add_menu( array(
                'parent' => 'site-name',
                'id' => 'bogoblast_subscriber-index',
                'title' => __('Subscribers'),
                'href' => admin_url( 'edit.php?post_type=bogoblast_subscriber')
            ) );
            $wp_admin_bar->add_menu( array(
                'parent' => 'site-name',
                'id' => 'bogoblast_mail-index',
                'title' => __('Mail'),
                'href' => admin_url( 'edit.php?post_type=bogoblast_mail')
            ) );
            $wp_admin_bar->add_menu( array(
                'parent' => 'site-name',
                'id' => 'bogoblast_event_log-index',
                'title' => __('Event Log'),
                'href' => admin_url( 'edit.php?post_type=bogoblast_event_log')
            ) );
        }

        //Business Meta Box
        public function business_add_meta_box() {
            add_meta_box('business_meta_box', 'Business Settings', array(&$this, 'business_show_meta_box'), 'bogoblast_business', 'normal', 'high');
        }
        public function business_show_meta_box() {
            require(BOGOBLAST_DIR.'bogoblast-business-show-meta-box.php');
        }
        public function business_save_meta_box($post_id) {
            require(BOGOBLAST_DIR.'bogoblast-business-save-meta-box.php');
        }
        public function business_delete_post($post_id) {
            require(BOGOBLAST_DIR.'bogoblast-business-delete-post.php');
        }
        public function business_admin_enqueue_scripts() {
            wp_register_script('google_maps',
                'https://maps.googleapis.com/maps/api/js?sensor=false');
            wp_enqueue_script('google_maps');
        }

        //Add and populate columns in Business list
        public function business_column_heads($defaults) {
            $defaults['title'] = 'Business Name';
            $defaults['bogoblast_business_owner_name'] = 'Owner Name';
            $defaults['bogoblast_business_phone'] = 'Phone';
            $defaults['bogoblast_business_email'] = 'E-Mail Address';
            $defaults['bogoblast_business_status'] = 'Status';
            //$defaults['subscriber_count'] = 'Subscriber Count';
            $defaults['post_thumbnail'] = 'Logo';

            unset($defaults['date']); //remove date col
            return $defaults;
        }
        public function business_column_contents($column_name, $post_id) {
            switch($column_name) {
                case 'bogoblast_business_owner_name':
                    echo get_post_meta($post_id, '_bogoblast_business_owner_name', true);
                    break;
                case 'bogoblast_business_phone':
                    echo get_post_meta($post_id, '_bogoblast_business_phone', true);
                    break;
                case 'bogoblast_business_email':
                    echo get_post_meta($post_id, '_bogoblast_business_email', true);
                    break;
                case 'bogoblast_business_status':
                    echo get_post_meta($post_id, '_bogoblast_business_status', true);
                    break;
                case 'subscriber_count':
                    $transient_key = "{$post_id}_subscriber_count";
                    $count = get_transient($transient_key);
                    if($count === false) {
                        $count = Bogoblast::count_subscribers_for($post_id);
                        $expiration = 60 * mt_rand(1,60) * 5; //random time between 5 minutes and 5 hours
                        set_transient($transient_key, $count, $expiration);
                    }
                    echo "{$count} (approx.)";
                    break;
                case 'post_thumbnail':
                    if (has_post_thumbnail($post_id)) {
                        echo Bogoblast::post_thumbnail($post_id, array(80, 80), 2);
                    }
                    break;
            }
        }

        //Business Comment Fields
        public function business_comment_fields() {
            ?><p class="comment-form-rating">
                <label>Rating<span class="required">*</span></label>
                <span class="commentratingbox">
                <?php for( $i=1; $i <= 5; $i++ ): ?>
                    <span class="commentrating">
                        <input type="radio" name="rating" id="rating_<?php echo $i; ?>" value="<?php echo $i; ?>"/>
                        <label for="rating_<?php echo $i; ?>"><?php echo $i; ?></label>
                    </span>
                <?php endfor; ?>
                </span>
            </p><?php
        }
        public function business_save_comment_meta_data( $comment_id ) {
            if ( ( isset( $_POST['rating'] ) ) && ( $_POST['rating'] != '') )
                $rating = wp_filter_nohtml_kses($_POST['rating']);
            add_comment_meta( $comment_id, 'rating', $rating );
        }
        public function business_verify_comment_meta_data( $commentdata ) {
            if ( ! isset( $_POST['rating'] ) )
            wp_die( __( 'Error: You did not add a rating. Hit the Back button on your Web browser and resubmit your comment with a rating.' ) );
            return $commentdata;
        }

        //Business Subscribe Shortcode
        public function business_subscribe_shortcode() {
            require(BOGOBLAST_DIR.'bogoblast-business-subscribe-shortcode.php');
        }

        //Business Category Widget
        public function business_category_widget($args) {
            require(BOGOBLAST_DIR.'bogoblast-business-category-widget.php');
        }

        //City Widget
        public function city_widget($args) {
            require(BOGOBLAST_DIR.'bogoblast-city-widget.php');
        }

        //Business Pre Get Posts
        public function business_pre_get_posts($query) {
            if(is_admin()) return;
            if(!$query->is_main_query()) return;

            if($query->get('post_type') == 'bogoblast_business' ||
                strlen($query->get('bogoblast_business_category')) > 0 ||
                strlen($query->get('bogoblast_city')) > 0 ) {

                if(strlen($query->get('orderby')) == 0) {
                    $query->set('orderby', 'title');
                }

                if(strlen($query->get('order')) == 0) {
                    $query->set('order', 'asc');
                }
            }

            //bogoblast_search_category
            if(isset($_REQUEST['bogoblast_search_category']) && strlen($_REQUEST['bogoblast_search_category']) > 0) {
                $bogoblast_search_category = stripslashes($_REQUEST['bogoblast_search_category']);

                if($bogoblast_business = get_page_by_title($bogoblast_search_category, 'OBJECT', 'bogoblast_business')) {
                    //$query->set('p', $bogoblast_business->ID);
                    wp_redirect(get_permalink($bogoblast_business->ID));
                    exit();
                    //echo '<!-- bogoblast_business -->';
                } elseif($bogoblast_business_category = get_term_by('name', $bogoblast_search_category, 'bogoblast_business_category')) {
                    $query->set('bogoblast_business_category', $bogoblast_business_category->slug);
                    //echo '<!-- bogoblast_business_category -->';
                } else {
                    $query->set('post_type', 'bogoblast_business');
                    $query->set('s', $bogoblast_search_category);
                    //echo '<!-- search -->';
                }

                $query->parse_query($query->query_vars);
            }

            //bogoblast_search_location
            if(isset($_REQUEST['bogoblast_search_location']) && strlen($_REQUEST['bogoblast_search_location']) > 0) {
                $bogoblast_search_location = stripslashes($_REQUEST['bogoblast_search_location']);
                $geolocated_str = isset($_REQUEST['geolocated_str']) ? stripslashes($_REQUEST['geolocated_str']) : '';
                $geolocated_lat = isset($_REQUEST['geolocated_lat']) ? floatval($_REQUEST['geolocated_lat']) : 0;
                $geolocated_lng = isset($_REQUEST['geolocated_lng']) ? floatval($_REQUEST['geolocated_lng']) : 0;

                if($bogoblast_city = get_term_by('name', $bogoblast_search_location, 'bogoblast_city')) {
                    $query->set('bogoblast_city', $bogoblast_city->slug);
                    //echo '<!-- bogoblast_city -->';
                } else {
                    //geocode!

                    if( $geolocated_str == $bogoblast_search_location &&
                        $geolocated_lat != 0 && $geolocated_lng != 0 ) {

                        $business_ids = Bogoblast::get_business_ids_near($geolocated_lat, $geolocated_lng);
                    } else {
                        $geocode_result = BogoblastUtil::geocode_address($bogoblast_search_location);
                        if($geocode_result !== null) {
                            $business_ids = Bogoblast::get_business_ids_near($geocode_result['lat'], $geocode_result['lng']);
                        }
                    }

                    if(is_array($business_ids) && sizeof($business_ids) > 0) {
                        $query->set('post__in', $business_ids);
                        $query->set('orderby', 'post__in');
                        $query->set('order', '');
                    }
                    //echo '<!-- geocode -->';
                }

                $query->parse_query($query->query_vars);
            }
        }

        //Promotion Meta Box
        public function promotion_add_meta_box() {
            add_meta_box('promotion_meta_box', 'Promotion Settings', array(&$this, 'promotion_show_meta_box'), 'bogoblast_promotion', 'normal', 'high');
        }
        public function promotion_show_meta_box() {
            require(BOGOBLAST_DIR.'bogoblast-promotion-show-meta-box.php');
        }
        public function promotion_save_meta_box($post_id) {
            require(BOGOBLAST_DIR.'bogoblast-promotion-save-meta-box.php');
        }

        //Add Custom Filtering Drop Down to Promotion List
        public function promotion_filter_drop_down() {
            if ($_REQUEST['post_type'] == 'bogoblast_promotion'):
                $selected_business_id = intval($_REQUEST['bogoblast_business_id']);
                $all_bogoblast_businesses = Bogoblast::get_businesses();
            ?>
                <select name="bogoblast_business_id">
                    <option value="">Filter By Business</option>
                    <?php foreach ($all_bogoblast_businesses as $bogoblast_business): ?>
                        <option value="<?php echo $bogoblast_business->ID; ?>"
                        <?php if($selected_business_id == $bogoblast_business->ID) echo 'selected="selected"'; ?>
                        ><?php echo $bogoblast_business->post_title; ?></option>
                    <?php endforeach; ?>
                </select>
            <?php endif;
        }
        public function promotion_posts_filter($query){
            global $pagenow;

            if( $_REQUEST['post_type'] == 'bogoblast_promotion' &&
                is_admin() &&
                $pagenow=='edit.php' &&
                isset($_REQUEST['bogoblast_business_id']) &&
                $_REQUEST['bogoblast_business_id'] != '') {

                $query->query_vars['meta_key'] = '_bogoblast_business_id';
                $query->query_vars['meta_value'] = $_REQUEST['bogoblast_business_id'];
            }
        }

        //Add and populate columns in Promotion list
        public function promotion_column_heads($defaults) {
            $defaults['bogoblast_business'] = 'Business';
            //remove and add date col to change col order
            unset($defaults['date']);
            $defaults['date'] = 'Date';
            $defaults['promotion_expiration'] = 'Expiration Date';

            return $defaults;
        }
        public function promotion_column_contents($column_name, $post_id) {
            switch($column_name) {
                case 'bogoblast_business':
                    $bogoblast_business_id = intval(get_post_meta($post_id, '_bogoblast_business_id', true));
                    if($bogoblast_business_id) {
                        echo '<a href="'.get_admin_url().'post.php?post='.
                            $bogoblast_business_id.'&action=edit">'.
                            get_the_title($bogoblast_business_id).'</a>';
                    }
                    break;
                case 'promotion_expiration':
                    $promotion_expiration = intval(get_post_meta($post_id, '_promotion_expiration', true));
                    echo date_i18n(get_option('date_format'), $promotion_expiration);
                    break;
            }
        }

        //Promotion AJAX Action
        public function promotion_queue_ajax() {
            require(BOGOBLAST_DIR.'bogoblast-promotion-queue-ajax.php');
            die(); // this is required to return a proper result
        }

        //Subscriber Meta Box
        public function subscriber_add_meta_box() {
            add_meta_box('subscriber_meta_box', 'Subscriber Settings', array(&$this, 'subscriber_show_meta_box'), 'bogoblast_subscriber', 'normal', 'high');
        }
        public function subscriber_show_meta_box() {
            require(BOGOBLAST_DIR.'bogoblast-subscriber-show-meta-box.php');
        }
        public function subscriber_save_meta_box($post_id) {
            require(BOGOBLAST_DIR.'bogoblast-subscriber-save-meta-box.php');
        }

        //Add and populate columns in Subscriber list
        function subscriber_column_heads($defaults) {
            $defaults['subscribed_to'] = 'Subscribed To';
            $defaults['title'] = 'E-Mail Address';
            return $defaults;
        }
        function subscriber_column_contents($column_name, $post_id) {
            if ($column_name == 'subscribed_to') {
                $bogoblast_business_ids = get_post_meta($post_id, '_bogoblast_business_id');
                $links = array();
                foreach($bogoblast_business_ids as $bogoblast_business_id) {
                    $links[] = '<a href="http://beta.endl.org/wp-admin/post.php?post='.$bogoblast_business_id.'&action=edit">'.get_the_title($bogoblast_business_id).'</a>';
                }
                echo implode(', ', $links);
            }
        }

        //Subscriber Import
        public function bogoblast_subscriber_import_admin_menu() {
            add_submenu_page('edit.php?post_type=bogoblast_subscriber', 'Subscriber Import', 'Subscriber Import', 'manage_options', 'bogoblast-subscriber-import', array(&$this, 'bogoblast_subscriber_import_render_action'));
        }
        public function bogoblast_subscriber_import_render_action() {
            $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'upload';
            require(BOGOBLAST_DIR.'bogoblast-subscriber-import-common.php');
            require(BOGOBLAST_DIR."bogoblast-subscriber-import-{$action}.php");
        }
        public function bogoblast_subscriber_import_render_ajax_action() {
            require(BOGOBLAST_DIR.'bogoblast-subscriber-import-ajax.php');
            die(); // this is required to return a proper result
        }

        //Subscriber Export
        //bogoblast-subscriber-export-options.php
        public function bogoblast_subscriber_export_admin_menu() {
            add_submenu_page('edit.php?post_type=bogoblast_subscriber', 'Subscriber Export', 'Subscriber Export', 'manage_options', 'bogoblast-subscriber-export', array(&$this, 'bogoblast_subscriber_export_render_action'));
        }
        public function bogoblast_subscriber_export_render_action() {
            $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'options';
            require(BOGOBLAST_DIR.'bogoblast-subscriber-export-common.php');
            require(BOGOBLAST_DIR."bogoblast-subscriber-export-{$action}.php");
        }

        //Subscriber Preferences Shortcode
        public function bogoblast_subscriber_preferences_shortcode() {
            require(BOGOBLAST_DIR.'bogoblast-subscriber-preferences-shortcode.php');
        }

        //Bogoblast Mail Meta Box
        function bogoblast_mail_add_meta_box() {
            add_meta_box('sent_mail_details', 'Mail Details', array(&$this, 'bogoblast_mail_show_meta_box'), 'bogoblast_mail', 'normal', 'high');
        }
        function bogoblast_mail_show_meta_box() {
            require_once(plugin_dir_path(__FILE__).'bogoblast-mail-meta-box.php');
        }

        //Hide Bogoblast Mail 'Add New' buttons
        function bogoblast_mail_hide_add_new_menu_item() {
            global $submenu;
            unset($submenu['edit.php?post_type=bogoblast_mail'][10]);
        }
        function bogoblast_mail_hide_add_new_header_button() {
            global $pagenow;
            $post_id = intval($_GET['post']);
            $post_type = $post_id > 0 ? get_post_type($post_id) : $_GET['post_type'];

            if(is_admin() && $post_type == 'bogoblast_mail' && ( $pagenow == 'edit.php' || $pagenow == 'post.php' )): ?>
                <style type="text/css">
                    .add-new-h2 { display: none; }
                </style>
            <?php endif;
        }

        //Add a Warning Message to the Mail List if Sending Paused
        public function mail_sending_paused_warning() {
            if($_REQUEST['post_type'] == 'bogoblast_mail'):
                $pause_sending = intval(get_option('bogoblast__pause_sending', 0));
                if($pause_sending): ?>
                    <strong style="background-color: #f00; color: #fff; font-weight: bold;">Sending Paused</strong>
                <?php endif;
            endif;
        }

        //Add and populate columns in Bogoblast Mail list
        function bogoblast_mail_column_heads($defaults) {
            $defaults['sent_mail_to'] = 'To';
            $defaults['title'] = 'Subject';
            $defaults['scheduled_send_date'] = 'Scheduled Send Date';
            $defaults['sent_date'] = 'Sent Date';
            $defaults['trackables'] = 'Trackables';
            return $defaults;
        }
        function bogoblast_mail_column_contents($column_name, $post_id) {
            switch($column_name) {
                case 'sent_mail_to':
                    echo get_post_meta($post_id, '_to', true);
                    break;
                case 'scheduled_send_date':
                    $date = intval(get_post_meta($post_id, '_scheduled_send_date', true));
                    if($date == 0) $date = intval(unserialize(get_post_meta($post_id, '__scheduled_send_date', true)));
                    if($date > 0) echo BogoblastUtil::format_date($date);
                    break;
                case 'sent_date':
                    $date = intval(get_post_meta($post_id, '_sent_date', true));
                    if($date == 0) $date = intval(unserialize(get_post_meta($post_id, '__sent_date', true)));
                    if($date > 0) echo BogoblastUtil::format_date($date);
                    break;
                case 'trackables':
                    $trackables = unserialize(get_post_meta($post_id, '__trackables', true));
                    if(is_array($trackables)) {
                        foreach($trackables as $key => $value) {
                            echo "<div>{$key}: {$value}</div>";
                        }
                    }
                    break;
            }
        }

        //Bogoblast Mail Tracking
        function bogoblast_mail_stat() {
            if(isset($_REQUEST['bogoblast_mail_set_trackable']) && intval($_REQUEST['bogoblast_mail_id']) > 0) {

                //don't track if referrer is wp-admin
                if(stristr($_SERVER['HTTP_REFERER'], get_admin_url()) === false) {

                    $bogoblast_mail = new BogoblastEMailMessage(intval($_REQUEST['bogoblast_mail_id']));
                    $bogoblast_mail->set_trackable($_REQUEST['bogoblast_mail_set_trackable']);
                    $bogoblast_mail->save();

                }

                //echo out the transparent gif
                $image_path = BOGOBLAST_DIR.'img/void.gif';
                $fp = fopen($image_path, 'rb');
                header("Content-Type: image/gif");
                header("Content-Length: " . filesize($image_path));
                fpassthru($fp);
                exit();
            }
        }

        //Hide Event Log 'Add New' buttons
        function bogoblast_event_log_hide_add_new_menu_item() {
            global $submenu;
            unset($submenu['edit.php?post_type=bogoblast_event_log'][10]);
        }
        function bogoblast_event_log_hide_add_new_header_button() {
            global $pagenow;
            $post_id = intval($_GET['post']);
            $post_type = $post_id > 0 ? get_post_type($post_id) : $_GET['post_type'];

            if(is_admin() && $post_type == 'bogoblast_event_log' && ( $pagenow == 'edit.php' || $pagenow == 'post.php' )): ?>
                <style type="text/css">
                    .add-new-h2 { display: none; }
                </style>
            <?php endif;
        }

        //Bogoblast Options
        public function bogoblast_options_admin_menu() {
            //add a link to options page to all of the tabs
            add_options_page('Bogoblast', 'Bogoblast', 'manage_options', 'bogoblast_options', array(&$this, 'bogoblast_options_html'));
            //add_submenu_page('edit.php?post_type=bogoblast_business', 'Bogoblast Options', 'Bogoblast Options', 'manage_options', 'bogoblast_options', array(&$this, 'bogoblast_options_html'));
            //add_submenu_page('edit.php?post_type=bogoblast_promotion', 'Bogoblast Options', 'Bogoblast Options', 'manage_options', 'bogoblast_options', array(&$this, 'bogoblast_options_html'));
            //add_submenu_page('edit.php?post_type=bogoblast_subscriber', 'Bogoblast Options', 'Bogoblast Options', 'manage_options', 'bogoblast_options', array(&$this, 'bogoblast_options_html'));
            //add_submenu_page('edit.php?post_type=bogoblast_event_log', 'Bogoblast Options', 'Bogoblast Options', 'manage_options', 'bogoblast_options', array(&$this, 'bogoblast_options_html'));
            //add_submenu_page('edit.php?post_type=bogoblast_mail', 'Bogoblast Options', 'Bogoblast Options', 'manage_options', 'bogoblast_options', array(&$this, 'bogoblast_options_html'));
        }
        public function bogoblast_options_html() {
            require(BOGOBLAST_DIR.'bogoblast-options-html.php');
        }
        public function bogoblast_options_register_settings_fields() {
            require(BOGOBLAST_DIR.'bogoblast-options-register-settings-fields.php');
        }

        //echo out html for settings fields
        function bogoblast_options_general_section_description_html() {
            return;
        }
        function bogoblast_options_send_mail_from_name_html() {
            $field_value = get_option('bogoblast__send_mail_from_name', '');
            ?><input type="text" id="bogoblast__send_mail_from_name" name="bogoblast__send_mail_from_name" value="<?php echo htmlspecialchars($field_value); ?>" class="regular-text ltr"><?php
        }
        function bogoblast_options_send_mail_from_address_html() {
            $field_value = get_option('bogoblast__send_mail_from_address', '');
            ?><input type="text" id="bogoblast__send_mail_from_address" name="bogoblast__send_mail_from_address" value="<?php echo htmlspecialchars($field_value); ?>" class="regular-text ltr"><?php
        }
        function bogoblast_options_pause_sending_html() {
            $field_value = intval(get_option('bogoblast__pause_sending', 0));
            ?><input type="hidden" name="bogoblast__pause_sending" value="0">
            <input type="checkbox" id="bogoblast__pause_sending" name="bogoblast__pause_sending" value="1"<?php if($field_value) echo ' checked'; ?>>
            <?php if($field_value): ?><strong style="background-color: #f00; color: #fff; font-weight: bold;">Sending Paused</strong><?php endif ;?>
            <p class="description">When checked, no mail will be sent!</p><?php
        }
        function bogoblast_options_ses_section_description_html() {
            ?><p>Use Amazon's Simple E-Mail Service to send messages.</p><?php
        }
        function bogoblast_options_enable_ses_html() {
            $field_value = intval(get_option('bogoblast__enable_ses', 0));
            ?><input type="hidden" name="bogoblast__enable_ses" value="0">
            <input type="checkbox" id="bogoblast__enable_ses" name="bogoblast__enable_ses" value="1"<?php if($field_value) echo ' checked'; ?>><?php
        }
        function bogoblast_options_ses_access_key_html() {
            $field_value = get_option('bogoblast__ses_access_key', '');
            ?><input type="text" id="bogoblast__ses_access_key" name="bogoblast__ses_access_key" value="<?php echo htmlspecialchars($field_value); ?>" class="regular-text code"><?php
        }
        function bogoblast_options_ses_secret_key_html() {
            $field_value = get_option('bogoblast__ses_secret_key', '');
            ?><input type="text" id="bogoblast__ses_secret_key" name="bogoblast__ses_secret_key" value="<?php echo htmlspecialchars($field_value); ?>" class="regular-text code"><?php
        }

        function bogoblast_options_twitter_section_description_html() {
            ?><p>Tweet New Businesses and Promotions from the post editor.</p><?php
        }
        function bogoblast_options_twitter_consumer_key_html() {
            $field_value = get_option('bogoblast__twitter_consumer_key', '');
            ?><input type="text" id="bogoblast__twitter_consumer_key" name="bogoblast__twitter_consumer_key" value="<?php echo htmlspecialchars($field_value); ?>" class="regular-text code"><?php
        }
        function bogoblast_options_twitter_consumer_secret_html() {
            $field_value = get_option('bogoblast__twitter_consumer_secret', '');
            ?><input type="text" id="bogoblast__twitter_consumer_secret" name="bogoblast__twitter_consumer_secret" value="<?php echo htmlspecialchars($field_value); ?>" class="regular-text code"><?php
        }
        function bogoblast_options_twitter_user_token_html() {
            $field_value = get_option('bogoblast__twitter_user_token', '');
            ?><input type="text" id="bogoblast__twitter_user_token" name="bogoblast__twitter_user_token" value="<?php echo htmlspecialchars($field_value); ?>" class="regular-text code"><?php
        }
        function bogoblast_options_twitter_user_secret_html() {
            $field_value = get_option('bogoblast__twitter_user_secret', '');
            ?><input type="text" id="bogoblast__twitter_user_secret" name="bogoblast__twitter_user_secret" value="<?php echo htmlspecialchars($field_value); ?>" class="regular-text code"><?php
        }

        //Twitter AJAX Action
        public function twitter_ajax() {
            require(BOGOBLAST_DIR.'bogoblast-twitter-ajax.php');
            die(); // this is required to return a proper result
        }

        //Facebook AJAX Action
        public function facebook_ajax() {
            require(BOGOBLAST_DIR.'bogoblast-facebook-ajax.php');
            die(); // this is required to return a proper result
        }

        public static function count_sent_mail_for($promotion_id) {
            global $wpdb;

            $query = "SELECT COUNT(*)
                FROM {$wpdb->posts}
                INNER JOIN {$wpdb->postmeta}
                ON {$wpdb->posts}.`id` = {$wpdb->postmeta}.`post_id`
                WHERE {$wpdb->posts}.`post_type` = 'bogoblast_mail'
                AND {$wpdb->posts}.`post_status` = 'publish'
                AND {$wpdb->postmeta}.`meta_key` = '_trackable_bogoblast_promotion_id'
                AND {$wpdb->postmeta}.`meta_value` = '{$promotion_id}'";

            return $wpdb->get_var($query);
        }

        public static function count_opened_mail_for($promotion_id) {
            global $wpdb;

            $query = "SELECT COUNT(*)
                FROM {$wpdb->posts}
                INNER JOIN {$wpdb->postmeta}
                ON {$wpdb->posts}.`id` = {$wpdb->postmeta}.`post_id`
                INNER JOIN {$wpdb->postmeta} AS trackable_opened
                ON {$wpdb->posts}.`id` = trackable_opened.`post_id`
                WHERE {$wpdb->posts}.`post_type` = 'bogoblast_mail'
                AND {$wpdb->posts}.`post_status` = 'publish'
                AND {$wpdb->postmeta}.`meta_key` = '_trackable_bogoblast_promotion_id'
                AND {$wpdb->postmeta}.`meta_value` = '{$promotion_id}'
                AND trackable_opened.`meta_key` = '_trackable_opened'
                AND trackable_opened.`meta_value` = '1'";

            return $wpdb->get_var($query);
        }

        public static function count_subscribers_for($business_id) {
            global $wpdb;

            $query = "SELECT COUNT(*)
                FROM {$wpdb->posts}
                INNER JOIN {$wpdb->postmeta}
                ON {$wpdb->posts}.`id` = {$wpdb->postmeta}.`post_id`
                WHERE {$wpdb->posts}.`post_type` = 'bogoblast_subscriber'
                AND {$wpdb->postmeta}.`meta_key` = '_bogoblast_business_id'
                AND {$wpdb->postmeta}.`meta_value` = '{$business_id}'";

            return $wpdb->get_var($query);
        }

        public static function get_business_ids_near($lat, $lng, $limit = 0, $distance = 0, $distance_unit = 'mi') {
            global $wpdb;

            switch($distance_unit) {
                case 'mi':
                    $distance_multiplier = 3959;
                    break;
                case 'km':
                    $distance_multiplier = 6371;
                    break;
            }

            $limit_sql = (intval($limit) > 0) ? 'LIMIT 0, '.intval($limit) : '';
            $distance_sql = (intval($distance) > 0) ? 'HAVING `distance` < '.intval($distance) : '';

            $query = "SELECT {$wpdb->posts}.`id` AS `business_id`,
                    (
                        {$distance_multiplier} * ACOS(
                            COS( RADIANS({$lat}) ) *
                            COS( RADIANS( `latitude`.`meta_value` ) ) *
                            COS( RADIANS( `longitude`.`meta_value` ) - RADIANS({$lng}) ) +
                            SIN( RADIANS({$lat}) ) *
                            SIN( RADIANS( `latitude`.`meta_value` ) )
                        )
                    ) AS `distance`
                FROM {$wpdb->posts}
                INNER JOIN {$wpdb->postmeta} AS `latitude`
                    ON {$wpdb->posts}.`id` = `latitude`.`post_id`
                INNER JOIN {$wpdb->postmeta} AS `longitude`
                    ON {$wpdb->posts}.`id` = `longitude`.`post_id`
                WHERE {$wpdb->posts}.`post_type` = 'bogoblast_business'
                    AND `latitude`.`meta_key` = '_bogoblast_business_latitude'
                    AND `latitude`.`meta_value` != ''
                    AND `longitude`.`meta_key` = '_bogoblast_business_longitude'
                    AND `longitude`.`meta_value` != ''
                {$distance_sql}
                ORDER BY `distance` ASC
                {$limit_sql}";

            return $wpdb->get_col($query);
        }

        public static function post_thumbnail( $post_id = null, $size = null, $zoomcrop = null, $align = null, $class = null) {

            $post_id = ( null === $post_id ) ? get_the_ID() : $post_id;
            $post_thumbnail_id = get_post_thumbnail_id( $post_id );

            if ( $post_thumbnail_id ) {
                $image_src = wp_get_attachment_image_src($post_thumbnail_id, 'full', false);
                if(is_array($image_src)) {

                    $image_url = $image_src[0];

                    @list($image_w, $image_h) = getimagesize($image_url);
                    $ratio = floatval($image_w) / floatval($image_h);

                    $style_html = '';
                    $class_html = ($class !== null) ? " class=\"{$class}\"" : '';

                    if(is_array($size) || intval($size) > 0) {

                        $fit_w = is_array($size) ? $size[0] : $size;
                        $fit_h = is_array($size) ? $size[1] : $size;

                        $output_w = round(($image_w > $image_h) ? $fit_h : (float)$fit_h * $ratio);
                        $output_h = round(($image_w < $image_h) ? $fit_w : (float)$fit_w / $ratio);

                        //echo "<!-- ";
                        //print_r($image_src);
                        //echo " $image_w $image_h $fit_w $fit_h $output_w $output_h $ratio -->";

                        if( class_exists( 'Jetpack' ) &&
                            method_exists( 'Jetpack', 'get_active_modules' ) &&
                            in_array( 'photon', Jetpack::get_active_modules() ) &&
                            function_exists( 'jetpack_photon_url' ) ) {

                            $pad_w = ($output_w < $fit_w) ? floor(($fit_w - $output_w) / 2) : 0;
                            $pad_h = ($output_h < $fit_h) ? floor(($fit_h - $output_h) / 2) : 0;

                            $style_html = " style=\"padding: {$pad_h}px {$pad_w}px;\"";

                            $args = array();
                            $args['fit'] = is_array($size) ? implode(',', $size) : "{$size},{$size}";

                            $src = jetpack_photon_url($image_url, $args);

                        } else {

                            $w = is_array($size) ? "&w={$size[0]}" : '';
                            $h = is_array($size) ? "&h={$size[1]}" : '';

                            if($zoomcrop !== null) {
                                $zc = "&zc={$zoomcrop}";
                            } else {
                                $zc_postmeta = get_post_meta($post_id, 'zc', true);
                                $zc = strlen($zc_postmeta) > 0 ? "&zc={$zc_postmeta}" : '';
                            }

                            if($align !== null) {
                                $a = "&a={$align}";
                            } else {
                                $a_postmeta = get_post_meta($post_id, 'a', true);
                                $a = strlen($a_postmeta) > 0 ? "&a={$a_postmeta}" : '';
                            }

                            $src = BOGOBLAST_URL."lib/timthumb.php?src={$image_url}{$w}{$h}{$zc}{$a}";
                        }

                    } else {

                        $src = $image_url;
                        $style_html = '';
                        $output_w = $image_w;
                        $output_h = $image_h;
                    }

                    $alt = trim(strip_tags(get_post_meta($post_thumbnail_id, '_wp_attachment_image_alt', true)));
                    ?><img src="<?php echo $src; ?>" alt="<?php echo $alt; ?>" width="<?php echo $output_w; ?>" height="<?php echo $output_h; ?>"<?php echo $class_html; ?><?php echo $style_html; ?>><?php
                }
            }
        }

        public static function log_event($title, $content = null) {
            if($content === null) $content = $title;

            $new_post = array();
            $new_post['post_type'] = 'bogoblast_event_log';
            $new_post['post_title'] = $title;
            $new_post['post_content'] = $content;
            $new_post['post_status'] = 'publish';

            return wp_insert_post($new_post, true);
        }

        //Flash Messages
        public static function queue_flash_message($message, $class = 'updated') {
            $flash_messages = maybe_unserialize(get_option('bogoblast__flash_messages', ''));

            if(!is_array($flash_messages)) $flash_messages = array();
            if(!is_array($flash_messages[$class])) $flash_messages[$class] = array();

            $flash_messages[$class][] = $message;

            update_option('bogoblast__flash_messages', serialize($flash_messages));
        }
        public static function show_flash_messages() {
            $flash_messages = unserialize(get_option('bogoblast__flash_messages', serialize('')));

            if(is_array($flash_messages)) {
                foreach($flash_messages as $class => $messages) {
                    foreach($messages as $message) {
                        ?><div class="<?php echo $class; ?>"><p><?php echo $message; ?></p></div><?php
                    }
                }
            }

            //empty out flash messages
            update_option('bogoblast__flash_messages', serialize(''));
        }

        public static function get_businesses($args = null) {

            $bogoblast_business_query_vars = array(
                'numberposts' => -1,
                'post_type' => 'bogoblast_business',
                'orderby' => 'title',
                'order' => 'ASC',
                'post_status' => 'any');

            if(is_array($args)) {
                $cache_key = md5(serialize($args));
                $cached_businesses = wp_cache_get($cache_key, 'bogoblast_businesses');

                if($cached_businesses === false) {
                    foreach($args as $arg => $arg_value) {
                        $bogoblast_business_query_vars[$arg] = $arg_value;
                    }
                    $bogoblast_businesses = get_posts($bogoblast_business_query_vars);

                    wp_cache_add($cache_key, $bogoblast_businesses, 'bogoblast_businesses', 30);

                    return $bogoblast_businesses;
                } else {
                    return $cached_businesses;
                }

            } else {
                $cached_businesses = get_transient('all_bogoblast_businesses');

                if($cached_businesses === false) {
                    $bogoblast_businesses = get_posts($bogoblast_business_query_vars);

                    set_transient('all_bogoblast_businesses', $bogoblast_businesses);

                    return $bogoblast_businesses;
                } else {
                    return $cached_businesses;
                }
            }
        }

        public static function clear_business_cache() {
            delete_transient('all_bogoblast_businesses');
        }

        public static function add_subscriber_to($business_id, $subscriber_id_or_email) {
            $bogoblast_subscriber = Bogoblast::get_or_create_subscriber($subscriber_id_or_email);

            if($bogoblast_subscriber !== null) {
                delete_post_meta($bogoblast_subscriber->ID, '_bogoblast_business_id', $business_id);
                add_post_meta($bogoblast_subscriber->ID, '_bogoblast_business_id', $business_id, false);
                return true;
            } else {
                return false;
            }
        }

        public static function create_subscriber($subscriber_email) {

            $new_post = array();
            $new_post['post_type'] = 'bogoblast_subscriber';
            $new_post['post_title'] = $subscriber_email;
            $new_post['post_content'] = '';
            $new_post['post_status'] = 'publish';

            $subscriber_id = wp_insert_post($new_post, true);

            $subscriber_id = intval($subscriber_id);

            if($subscriber_id > 0) {
                return get_post($subscriber_id);
            } else {
                return null;
            }
        }

        public static function get_or_create_subscriber($subscriber_id_or_email) {
            if(is_numeric($subscriber_id_or_email)) {
                //it's an id
                return get_post(intval($subscriber_id_or_email));

            } else {
                //it's an e-mail address

                //get subscriber post by title
                $bogoblast_subscriber = get_page_by_title($subscriber_id_or_email, 'OBJECT', 'bogoblast_subscriber');

                //if couldn't get subscriber post, make one
                if($bogoblast_subscriber === null) {
                    $bogoblast_subscriber = Bogoblast::create_subscriber($subscriber_id_or_email);
                }

                $subscriber_id = $bogoblast_subscriber->ID;
            }

            $subscriber_id = intval($subscriber_id);

            if($subscriber_id > 0) {
                return $bogoblast_subscriber;
            } else {
                return null;
            }
        }

        public static function get_and_validate_promotion($post_id) {
            $error_messages = array();
            $fatal_error = false;

            //get promotion
            $bogoblast_promotion = get_post($post_id);
            if($bogoblast_promotion->post_type != 'bogoblast_promotion') {
                //error

                //only log an event if it's not a revision. We don't care about those.
                if($bogoblast_promotion->post_type != 'revision') {
                    Bogoblast::log_event("Post '{$bogoblast_promotion->post_title}' (ID: $post_id) is not a bogoblast_promotion.");
                    $error_messages[] = "Post '{$bogoblast_promotion->post_title}' (ID: $post_id) is not a bogoblast_promotion.";
                    $fatal_error = true;
                }
            }

            //get promotion email subject.
            if(!$fatal_error) {
                $promotion_email_subject = get_post_meta($post_id, '_promotion_email_subject', true);
                if(strlen($promotion_email_subject) <= 0) {
                    //error
                    Bogoblast::log_event("bogoblast_promotion '{$bogoblast_promotion->post_title}' (ID: $post_id) has no email subject.");
                    $error_messages[] = "bogoblast_promotion '{$bogoblast_promotion->post_title}' (ID: $post_id) has no email subject.";
                    $fatal_error = true;
                }
            }

            //check expiration.
            if(!$fatal_error) {
                $promotion_expiration = intval(get_post_meta($post_id, '_promotion_expiration', true));
                if($promotion_expiration <= 0) {
                    //error
                    Bogoblast::log_event("bogoblast_promotion '{$bogoblast_promotion->post_title}' (ID: $post_id) has no expiration date.");
                    $error_messages[] = "bogoblast_promotion '{$bogoblast_promotion->post_title}' (ID: $post_id) has no expiration date.";
                    $fatal_error = true;
                } elseif($promotion_expiration <= time()) {
                    //error
                    Bogoblast::log_event("bogoblast_promotion '{$bogoblast_promotion->post_title}' (ID: $post_id) has an expiration date in the past.");
                    $error_messages[] = "bogoblast_promotion '{$bogoblast_promotion->post_title}' (ID: $post_id) has an expiration date in the past.";
                    $fatal_error = true;
                }
            }

            //get business
            if(!$fatal_error) {
                $bogoblast_business_id = intval(get_post_meta($post_id, '_bogoblast_business_id', true));
                if($bogoblast_business_id <= 0) {
                    //error
                    Bogoblast::log_event("bogoblast_promotion '{$bogoblast_promotion->post_title}' (ID: $post_id) has no associated bogoblast_business.");
                    $error_messages[] = "bogoblast_promotion '{$bogoblast_promotion->post_title}' (ID: $post_id) has no associated bogoblast_business.";
                    $fatal_error = true;
                }
            }

            if(!$fatal_error) {
                $bogoblast_business = get_post($bogoblast_business_id);
                if($bogoblast_business === null) {
                    //error
                    Bogoblast::log_event("bogoblast_business $bogoblast_business_id doesn't exist. Attempted to use it for bogoblast_promotion '{$bogoblast_promotion->post_title}' (ID: $post_id).");
                    $error_messages[] = "bogoblast_business $bogoblast_business_id doesn't exist. Attempted to use it for bogoblast_promotion '{$bogoblast_promotion->post_title}' (ID: $post_id).";
                    $fatal_error = true;
                }
            }

            if(!$fatal_error) {
                $subscriber_count = Bogoblast::count_subscribers_for($bogoblast_business_id);

                $business_status = get_post_meta($bogoblast_business->ID, '_bogoblast_business_status', true);
                switch($business_status) {
                    case 'active_public':
                    case 'active_private':
                        break;
                    default:
                        Bogoblast::log_event("bogoblast_business '{$bogoblast_business->post_title}' (ID: $bogoblast_business_id) is not an active business. Attempted to use it for bogoblast_promotion '{$bogoblast_promotion->post_title}' (ID: $post_id).");
                        $error_messages[] = "bogoblast_business '{$bogoblast_business->post_title}' (ID: $bogoblast_business_id) is not an active business. Attempted to use it for bogoblast_promotion '{$bogoblast_promotion->post_title}' (ID: $post_id).";
                        $fatal_error = true;
                }
            }

            if(!$fatal_error) {
                $business_phone = get_post_meta($bogoblast_business->ID, '_bogoblast_business_phone', true);
                $business_address_1 = get_post_meta($bogoblast_business->ID, '_bogoblast_business_address_1', true);
                $business_address_2 = get_post_meta($bogoblast_business->ID, '_bogoblast_business_address_2', true);
                $business_city = get_post_meta($bogoblast_business->ID, '_bogoblast_business_city', true);
                $business_state = get_post_meta($bogoblast_business->ID, '_bogoblast_business_state', true);
                $business_zip = get_post_meta($bogoblast_business->ID, '_bogoblast_business_zip', true);
                $business_url = get_post_meta($bogoblast_business->ID, '_bogoblast_business_url', true);
                $business_directions = get_post_meta($bogoblast_business->ID, '_bogoblast_business_directions', true);

                if(strlen(trim("$business_phone $business_address_1 $business_address_2 $business_city $business_state $business_zip $business_url $business_directions")) <= 0) {
                    //error
                    //Bogoblast::queue_flash_message("<a href=\"{$admin_url}post.php?post={$bogoblast_business_id}&action=edit\">{$bogoblast_business->post_title}</a> has no contact information.", 'error');
                    Bogoblast::log_event("bogoblast_business '{$bogoblast_business->post_title}' (ID: $bogoblast_business_id) has no contact information. Attempted to use it for bogoblast_promotion '{$bogoblast_promotion->post_title}' (ID: $post_id).");
                    $error_messages[] = "bogoblast_business '{$bogoblast_business->post_title}' (ID: $bogoblast_business_id) has no contact information. Attempted to use it for bogoblast_promotion '{$bogoblast_promotion->post_title}' (ID: $post_id).";
                    $fatal_error = true;
                }
            }

            //get message content
            if(!$fatal_error) {
                $path_to_message_template = get_post_meta($post_id, '_promotion_email_template', true);
                //$path_to_message_template = BOGOBLAST_DIR.'mail-templates/bogoblast-default/mail.php';
                if(!file_exists($path_to_message_template)) {
                    //error
                    //Bogoblast::queue_flash_message("Could not find message template '{$path_to_message_template}' for
                        //<a href=\"{$admin_url}post.php?post={$post_id}&action=edit\">{$bogoblast_promotion->post_title}</a>", 'error');
                    Bogoblast::log_event("Could not find message template '$path_to_message_template' for bogoblast_promotion '{$bogoblast_promotion->post_title}' (ID: $post_id).");
                    $error_messages[] = "Could not find message template '$path_to_message_template' for bogoblast_promotion '{$bogoblast_promotion->post_title}' (ID: $post_id).";
                    $fatal_error = true;
                }
            }

            if(!$fatal_error) {
                ob_start();
                include ($path_to_message_template);
                $message_content = ob_get_contents();
                ob_end_clean();
            }

            if(!$fatal_error) {
                return array(
                    'post_id' => $post_id,
                    'fatal_error' => false,
                    'error_messages' => $error_messages,
                    'post_title' => $bogoblast_promotion->post_title,
                    'bogoblast_business_id' => $bogoblast_business_id,
                    'subscriber_count' => $subscriber_count,
                    'message_from' => "{$bogoblast_business->post_title} <mailer@bogoblast.co>",
                    'message_subject' => $promotion_email_subject,
                    'message_content' => $message_content
                );

            } else {
                return array(
                    'post_id' => $post_id,
                    'fatal_error' => true,
                    'error_messages' => $error_messages
                );
            }
        }
    }
    $bogoblast = new Bogoblast();

?>