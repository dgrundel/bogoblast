<?php global $post; ?>

<script type="text/javascript">
    var geocoder;
    var map;
    var marker;

    jQuery(document).ready(function($){
        geocoder = new google.maps.Geocoder();

        $("#geocode_address").click(function(){
            geocode_address();
        });

        $("#recenter_map").click(function(){
            recenter_map();
        });

        function recenter_map() {
            var lat = parseFloat($("#bogoblast_business_latitude").val());
            var lng = parseFloat($("#bogoblast_business_longitude").val());
            if(!isNaN(lat) && !isNaN(lng)) {
                var latlng = new google.maps.LatLng(lat, lng);
                var mapOptions = {
                    zoom: 16,
                    center: latlng,
                    mapTypeId: google.maps.MapTypeId.ROADMAP
                }
                map = new google.maps.Map(document.getElementById('preview_map'), mapOptions);

                marker = new google.maps.Marker({
                    map: map,
                    position: latlng
                });

                $("#preview_map").css("height", 300);

                return true;
            }
            return false;
        }

        function geocode_address() {
            var address = $("#bogoblast_business_address_1").val() + ' ' +
                $("#bogoblast_business_address_2").val() + ' ' +
                $("#bogoblast_business_city").val() + ', ' +
                $("#bogoblast_business_state").val() + ' ' +
                $("#bogoblast_business_zip").val();

            geocoder.geocode( { 'address': address }, function(results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    //alert(results[0].geometry.location);
                    if($("#bogoblast_business_latitude").val() == '') { $("#bogoblast_business_latitude").val(results[0].geometry.location.lat()); }
                    if($("#bogoblast_business_longitude").val() == '') { $("#bogoblast_business_longitude").val(results[0].geometry.location.lng()); }
                } else {
                    //alert('Geocode was not successful for the following reason: ' + status);
                }
            });
        }
    });
</script>

<!-- nonce for verification -->
<input type="hidden" name="bogoblast_business_meta_box_nonce" value="<?php echo wp_create_nonce('bogoblast_business_meta_box_nonce'); ?>">

<h4>Business Contact Info</h4>
<table class="form-table">
    <tr>
        <th><label for="bogoblast_business_status">Business Status</label></th>
        <td>
            <select name="bogoblast_business[status]" id="bogoblast_business_status">
                <?php
                    $statuses = array(
                        '' => '',
                        'active_public' => 'Active, Public',
                        'active_private' => 'Active, Private',
                        'listing_only' => 'Listing Only',
                        'inactive' => 'Inactive',
                    );

                    $current_status = get_post_meta($post->ID, '_bogoblast_business_status', true);
                    foreach($statuses as $value => $label) {
                        ?><option value="<?php echo $value; ?>"<?php if($current_status == $value) echo ' selected="selected"'; ?>><?php echo $label; ?></option><?php
                    }
                ?>
            </select>
        </td>
    </tr>
    <tr>
        <th>Subscriber Count</th>
        <td>
            <?php
            $transient_key = "{$post->ID}_subscriber_count_meta_box";
            $count = get_transient($transient_key);
            if($count === false || intval($_REQUEST['refresh_subscriber_count']) > 0) {
                $count = Bogoblast::count_subscribers_for($post->ID);
                set_transient($transient_key, $count, 60 * 60 * 15); //Expire in 15 minutes
            }
            echo $count;
            ?>
            <p class="description">Refreshed every 15 minutes. <a href="<?php echo BogoblastUtil::set_url_query_value(BogoblastUtil::current_url(), 'refresh_subscriber_count', 1); ?>">Refresh Now</a>.</p>
        </td>
    </tr>
    <tr>
        <th><label for="bogoblast_business_owner_name">Owner Name</label></th>
        <td><input type="text" name="bogoblast_business[owner_name]" id="bogoblast_business_owner_name" value="<?php echo get_post_meta($post->ID, '_bogoblast_business_owner_name', true); ?>" class="regular-text" /></td>
    </tr>
    <tr>
        <th><label for="bogoblast_business_owner_cell_phone">Owner Cell Phone</label></th>
        <td><input type="text" name="bogoblast_business[owner_cell_phone]" id="bogoblast_business_owner_cell_phone" value="<?php echo get_post_meta($post->ID, '_bogoblast_business_owner_cell_phone', true); ?>" class="regular-text" /></td>
    </tr>
    <tr>
        <th><label for="bogoblast_business_owner_email">Owner E-Mail</label></th>
        <td><input type="text" name="bogoblast_business[owner_email]" id="bogoblast_business_owner_email" value="<?php echo get_post_meta($post->ID, '_bogoblast_business_owner_email', true); ?>" class="regular-text" /></td>
    </tr>
    <tr>
        <th><label for="bogoblast_business_address_1">Address</label></th>
        <td><input type="text" name="bogoblast_business[address_1]" id="bogoblast_business_address_1" value="<?php echo get_post_meta($post->ID, '_bogoblast_business_address_1', true); ?>" class="regular-text" /></td>
    </tr>
    <tr>
        <th></th>
        <td><input type="text" name="bogoblast_business[address_2]" id="bogoblast_business_address_2" value="<?php echo get_post_meta($post->ID, '_bogoblast_business_address_2', true); ?>" class="regular-text" /></td>
    </tr>
    <tr>
        <th><label for="bogoblast_business_city">City</label></th>
        <td><input type="text" name="bogoblast_business[city]" id="bogoblast_business_city" value="<?php echo get_post_meta($post->ID, '_bogoblast_business_city', true); ?>" class="regular-text" /></td>
    </tr>
    <tr>
        <th><label for="bogoblast_business_state">State/Provice</label></th>
        <td>
            <select name="bogoblast_business[state]" id="bogoblast_business_state">
            <?php
            $selected_state = get_post_meta($post->ID, '_bogoblast_business_state', true);
            foreach(BogoblastUtil::us_states() as $value => $label):
                ?><option value="<?php echo $value; ?>"<?php if($value == $selected_state) echo ' selected="selected"'; ?>><?php echo $label; ?></option><?php
            endforeach; ?>
            </select>
        </td>
    </tr>
    <tr>
        <th><label for="bogoblast_business_zip">Postal Code</label></th>
        <td><input type="text" name="bogoblast_business[zip]" id="bogoblast_business_zip" value="<?php echo get_post_meta($post->ID, '_bogoblast_business_zip', true); ?>" class="regular-text" /></td>
    </tr>
    <tr>
        <th><label for="bogoblast_business_directions">Directions</label></th>
        <td>
            <textarea name="bogoblast_business[directions]" id="bogoblast_business_directions" class="widefat"><?php echo get_post_meta($post->ID, '_bogoblast_business_directions', true); ?></textarea>
            <p class="description">Additional directions to the business address.</p>
        </td>
    </tr>
    <tr>
        <th>
            Mapping
        </th>
        <td>
            <div id="preview_map"></div>
            <p>
                <a class="button" id="geocode_address">Get Lat/Long from Address</a>
                <a class="button" id="recenter_map">Show on Map</a>
            </p>
            <p>
                <label for="bogoblast_business_latitude">Latitude</label><br />
                <input type="text" name="bogoblast_business[latitude]" id="bogoblast_business_latitude" value="<?php echo get_post_meta($post->ID, '_bogoblast_business_latitude', true); ?>" class="regular-text" />
            </p>
            <p>
            <label for="bogoblast_business_longitude">Longitude</label><br />
            <input type="text" name="bogoblast_business[longitude]" id="bogoblast_business_longitude" value="<?php echo get_post_meta($post->ID, '_bogoblast_business_longitude', true); ?>" class="regular-text" />
            </p>
            <p class="description">Used for mapping the business location.</p>
        </td>
    </tr>
    <tr>
        <th><label for="bogoblast_business_phone">Phone Number</label></th>
        <td><input type="text" name="bogoblast_business[phone]" id="bogoblast_business_phone" value="<?php echo get_post_meta($post->ID, '_bogoblast_business_phone', true); ?>" class="regular-text" /></td>
    </tr>
    <tr>
        <th><label for="bogoblast_business_fax">Fax</label></th>
        <td><input type="text" name="bogoblast_business[fax]" id="bogoblast_business_fax" value="<?php echo get_post_meta($post->ID, '_bogoblast_business_fax', true); ?>" class="regular-text" /></td>
    </tr>
    <tr>
        <th><label for="bogoblast_business_email">E-Mail Address</label></th>
        <td><input type="text" name="bogoblast_business[email]" id="bogoblast_business_email" value="<?php echo get_post_meta($post->ID, '_bogoblast_business_email', true); ?>" class="regular-text" /></td>
    </tr>
    <tr>
        <th><label for="bogoblast_business_url">Website</label></th>
        <td><input type="text" name="bogoblast_business[url]" id="bogoblast_business_url" value="<?php echo get_post_meta($post->ID, '_bogoblast_business_url', true); ?>" class="regular-text" /></td>
    </tr>
    <tr>
        <th><label>Social Media</label></th>
        <td>
            <p>
                <label for="bogoblast_business_facebook">Facebook</label><br />
                <input type="text" name="bogoblast_business[facebook]" id="bogoblast_business_facebook" value="<?php echo get_post_meta($post->ID, '_bogoblast_business_facebook', true); ?>" class="regular-text" />
            </p>
            <p>
                <label for="bogoblast_business_twitter">Twitter</label><br />
                <input type="text" name="bogoblast_business[twitter]" id="bogoblast_business_twitter" value="<?php echo get_post_meta($post->ID, '_bogoblast_business_twitter', true); ?>" class="regular-text" />
            </p>
            <p>
                <label for="bogoblast_business_google_plus">Google+</label><br />
                <input type="text" name="bogoblast_business[google_plus]" id="bogoblast_business_google_plus" value="<?php echo get_post_meta($post->ID, '_bogoblast_business_google_plus', true); ?>" class="regular-text" />
            </p>
            <p>
                <label for="bogoblast_business_linkedin">LinkedIn</label><br />
                <input type="text" name="bogoblast_business[linkedin]" id="bogoblast_business_linkedin" value="<?php echo get_post_meta($post->ID, '_bogoblast_business_linkedin', true); ?>" class="regular-text" />
            </p>
            <p>
                <label for="bogoblast_business_youtube">YouTube</label><br />
                <input type="text" name="bogoblast_business[youtube]" id="bogoblast_business_youtube" value="<?php echo get_post_meta($post->ID, '_bogoblast_business_youtube', true); ?>" class="regular-text" />
            </p>
            <p>
                <label for="bogoblast_business_pinterest">Pinterest</label><br />
                <input type="text" name="bogoblast_business[pinterest]" id="bogoblast_business_pinterest" value="<?php echo get_post_meta($post->ID, '_bogoblast_business_pinterest', true); ?>" class="regular-text" />
            </p>
        </td>
    </tr>

</table>