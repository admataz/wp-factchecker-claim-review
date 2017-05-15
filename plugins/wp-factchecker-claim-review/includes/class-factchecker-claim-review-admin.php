<?php

class Factchecker_Claim_Review_Admin{
    
    
    const ENDPOINT = 'factchecker/v1';
    
    
    public function __construct(){
        // add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'save_post', array($this, 'save_custom_fields'), 10, 2 );
        add_action( 'add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('rest_api_init', array( $this,'setup_endpoints'));
        add_action( 'admin_menu', array($this, 'add_plugin_page')  );
        add_action( 'admin_init', array($this, 'plugin_register_settings')  );
        // add_action( 'admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
    }
    
    
    function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Settings Admin',
            'Factchecker ClaimReview Settings',
            'manage_options',
            'claimreview_settings_admin',
            array($this, 'create_admin_page')
        );
    }
    public function admin_enqueue_scripts(){
      // this jquery ui if overridden by some plugins - not reliable
      //  wp_enqueue_script( 'jquery-ui-datepicker' );
    }

    public function create_admin_page(){
        ?>
  <div class="wrap">
    <h2>Factchecker schema.org ClaimReview creator Settings</h2>
    <form method="post" action="options.php">
      <?php
        // This prints out all hidden setting fields
        settings_fields( 'claimreview_schema_options' );
        do_settings_sections( 'claimreview_settings_admin' );
        // settings_fields( 'claimreview_content_options_group' );
        submit_button();
        ?>
    </form>
  </div>
  <?php
    }
    
    public function plugin_register_settings(){
        register_setting( 'claimreview_schema_options', 'claimreview_schema_options_org', array($this, 'claimreview_schema_options_org_sanitize') );
        register_setting( 'claimreview_schema_options', 'claimreview_schema_options_posts', array($this, 'claimreview_schema_options_posts_sanitize') );
        
        add_settings_section(
        'claimreview_schema_options_org_group', // ID
        'ClaimReview Schema Options', // Title
        array($this, 'claimreview_schema_options_section_info'), // Callback
        'claimreview_settings_admin' // Page
        );

        add_settings_section(
          'claimreview_schema_options_posts_group', // ID
          'ClaimReview Content Options', // Title
          array($this, 'claimreview_content_options_section_info'), // Callback
          'claimreview_settings_admin' // Page
        );
        
        add_settings_field(
        'org_name', // ID
        'Organisation Name', // Title
        array($this, 'settingsfield_org_name'), // Callback
        'claimreview_settings_admin', // Page
        'claimreview_schema_options_org_group' // Section
        );

        add_settings_field(
          'org_url', // ID
          'Organisation URL', // Title
          array($this, 'settingsfield_org_url'), // Callback
          'claimreview_settings_admin', // Page
          'claimreview_schema_options_org_group' // Section
        );

        add_settings_field(
          'org_other_url', // ID
          'Organisation alternative URL (e.g Twitter)', // Title
          array($this, 'settingsfield_org_other_url'), // Callback
          'claimreview_settings_admin', // Page
          'claimreview_schema_options_org_group' // Section
        );

        add_settings_field(
          'supported_post_types', // ID
          'Post types to attach reviews to', // Title
          array($this, 'settingsfield_post_types_supported'), // Callback
          'claimreview_settings_admin', // Page
          'claimreview_schema_options_posts_group' // Section
        );
        add_settings_field(
          'ratings_range', // ID
          'Ratings range labels - one per line', // Title
          array($this, 'settingsfield_ratings_range'), // Callback
          'claimreview_settings_admin', // Page
          'claimreview_schema_options_posts_group' // Section
        );

    }
    
    
    
    public function claimreview_schema_options_org_sanitize($input){
         $new_input = array();
        foreach ($input as $k => $v) {
            $new_input[$k] = trim($v);
        }

         if (!strlen($new_input['org_name'])) {
            add_settings_error('org_name', 'Organisation Name', 'Organisation Name is required', 'error');
            $new_input['org_name'] = $input['org_name'];
        }


        if(strlen($new_input['org_url'])){
          $new_input['org_url'] = esc_url_raw( $new_input['org_url'] );
        }
        if(strlen($new_input['org_other_url'])){
          $new_input['org_other_url'] = esc_url_raw( $new_input['org_other_url'] );
        }

        return $new_input;
    }
    
    
    public function claimreview_schema_options_posts_sanitize($input){
        if(isset($input['supported_post_types']) && is_array($input['supported_post_types'])){
          $input['supported_post_types'] =  array_keys($input['supported_post_types']);
        }
        return $input;
    }

    
    
    public function claimreview_schema_options_section_info(){
        print __("Information about your organisation");
    }

    public function claimreview_content_options_section_info(){
        print __("Content for ClaimReview schema.org metadata ");
    }
    
    
    public function settingsfield_org_name(){
        $options  = get_option('claimreview_schema_options_org', array());
        printf(
            '<input type="text" id="org_name" name="claimreview_schema_options_org[org_name]" value="%s" />',
            isset( $options['org_name'] ) ? esc_attr( $options['org_name']) : ''
        );
    }

    public function settingsfield_org_other_url(){
          $options  = get_option('claimreview_schema_options_org', array());
        printf(
            '<input type="text" id="org_other_url" name="claimreview_schema_options_org[org_other_url]" value="%s" />',
            isset( $options['org_other_url'] ) ? esc_attr( $options['org_other_url']) : ''
        );
    }

    public function settingsfield_org_url(){
        $options  = get_option('claimreview_schema_options_org', array());
        printf(
            '<input type="text" id="org_url" name="claimreview_schema_options_org[org_url]" value="%s" />',
            isset( $options['org_url'] ) ? esc_attr( $options['org_url']) : ''
        );
    }
    

    public function settingsfield_post_types_supported(){
      
      $posttypes = get_post_types(array(), 'objects');
    	$options  = get_option('claimreview_schema_options_posts', array('supported_post_types'=>array()));

		if(empty($options['supported_post_types'])){
			$options['supported_post_types'] = array();
		}

      echo '<ul>';
      foreach($posttypes as $postslug=>$posttype){
        printf(
            ' <li><label><input type="checkbox" id="posttype-%s" name="claimreview_schema_options_posts[supported_post_types][%s]" value="1" %s />%s</label> </li>',
           $postslug,
           $postslug,
          in_array($postslug, $options['supported_post_types'])? 'checked="checked"': '',
           $posttype->label
        );

      }
      echo '</ul>';

    }
    

       public function settingsfield_ratings_range(){
          $options  = get_option('claimreview_schema_options_posts', array());
        printf(
            '<textarea id="ratings_range" name="claimreview_schema_options_posts[ratings_range]" cols="20" rows="10">%s</textarea>',
            isset( $options['ratings_range'] ) ? esc_attr( $options['ratings_range']) : ''
        );
    }
    
    
    public function setup_endpoints(){
        
        register_rest_route(self::ENDPOINT, '/claim_review', array(
        'methods' => 'POST',
        'callback' => array( $this, 'save_new_claim_review'),
        'permission_callback' => function () {
            return  current_user_can('edit_posts');
        }
        ));
    }
    
    
    public function add_meta_boxes(){

      $options = get_option('claimreview_schema_options_posts', array('supported_post_types'=>array()));

        add_meta_box(
        'new-related-claim-review',
        'Add a new claim review',
        array( $this, 'select_claim_review_box' ),
        $options['supported_post_types'],
        'side',
        'high',
        array('related_review'=>true)
        );
        
        
        add_meta_box(
        'claim-review-details',
        'Claim Review',
        array($this, 'claim_review_metabox'),
        'claim_review',
        'advanced',
        'default',
        array('related_review'=>false) );
    }
    
    
    
    
    
    
    
    public function get_all_claims(){
        $claims = array();
        $query = new WP_Query(array('post_status'=>'publish', 'post_type'=>'claim_review', 'nopaging'=>true, 'order' => 'ASC', 'orderby'=> 'title'));
        if($query->have_posts()){
            $posts = $query->get_posts();
            foreach($posts as $k=>$v){
                // BASE WP FIELDS
                $p = array(
                'id' => $v->ID,
                'title' => $v->post_title,
                );
                // CUSTOM FIELDS
                $postmeta = get_post_meta($v->ID);
                $p['author'] = isset($postmeta['_claim_review_author_name']) ? $postmeta['_claim_review_author_name'][0] : '';
                $p['quote'] = isset($postmeta['_claim_review_claim_quote']) ? $postmeta['_claim_review_claim_quote'][0] : '';
                $claims[] = $p;
            }
        }
        return $claims;
    }
    
    
    
    
    
    public function select_claim_review_box( $post , $args) {
        $pluginpath = realpath(dirname(__FILE__));
        $existing = get_metadata( 'post', $post->ID, '_claim_reviews_selected', true );
        if(!$existing){
          $existing = array();
        }

        $local_vars = array(
        'plugin_path'=>plugins_url( '', $pluginpath ),
        'post_id' => $post->ID,
        'noncense' => wp_create_nonce('wp_rest'),
        'ajax_url' => admin_url('admin-ajax.php'),
        'endpoint' => rest_url(self::ENDPOINT),
        'selected' => $existing
        );
        wp_enqueue_script( 'claim-review-admin', plugins_url( 'js', $pluginpath ).'/main_admin.js', array('jquery', 'underscore', 'backbone'), '1.0.0', false );
        wp_enqueue_script( 'jquery-ui-autocomplete');
        wp_enqueue_script( 'jquery-ui-button');
        wp_localize_script('claim-review-admin', 'claimReviewLocaldata', $local_vars);
        
        $claims =  json_encode( $this->get_all_claims() );
        
        add_thickbox();
        ?>

    <div id="claim-review-select-metabox">
      <?php wp_nonce_field( basename( __FILE__ ), 'claim_review_selected_nonce' ); ?>
        <div id="claim-review-results-list"></div>
        <div id="claim-review-search-box">
          <div class="ui-widget">
            <label>search existing claims</label>
            <input type="text" id="claim_review_search_q">
          </div>
          <button id="btn-add-selected" class="ui-button ui-widget ui-corner-all">Add</button>
        </div>
    </div>

    <div id="new-claim-review-box" style="display:none;">
      <div class="newitem-box-content">
        <h3>Add Claim</h3>

        <table class="form-table">
          <?php wp_nonce_field( 'wp_rest', 'new_claim_review_nonce' ); ?>
            <tr>
              <th scope="row">
                <label for="new-claim-title">Admin title:</label>
              </th>
              <td>
                <input type="text" class="regular-text" id="new-claim-title" name="new-claim-title" />
              </td>
            </tr>

        </table>
        <?php
        $this->claim_review_form()
        ?>
          <button id="btn-save-new-claim-review" class="ui-button ui-widget ui-corner-all">Save</button>
      </div>
    </div>
    <a href="#TB_inline?width=600&height=600&inlineId=new-claim-review-box" id="btn-claim-review-add" class="thickbox">add a new claim review</a>

    <script type="x-template/text" id="tmpl-claim-review-item">
      <li>
        <label>
          <input type="checkbox" checked="checked" id="claim-review-item-<%= id %>" name="claim-reviews-selected[<%= id %>]">
          <a href="<?php echo admin_url('/post.php?action=edit&post=')?><%= id %>" target="_blank">
            <%= title %>
          </a>
        </label>
      </li>
    </script>
    <script>
      var claimsJSON = <?php echo $claims; ?>;
    </script>

    <?php
        
    }
    
    
    
    
    public function claim_review_metabox($post){
        $this->claim_review_form($post);
        
    }
    
    public function claim_review_form($post = null){
        
        if($post){
            $postmeta = get_metadata( 'post', $post->ID);
        } else {
            $postmeta = array();
        }
        
        $claim_quote_meta_value = isset($postmeta['_claim_review_claim_quote']) ? $postmeta['_claim_review_claim_quote'][0] : '';
        $claim_summary_meta_value = isset($postmeta['_claim_review_claim_summary']) ? $postmeta['_claim_review_claim_summary'][0] : '';
        $author_name_meta_value = isset($postmeta['_claim_review_author_name']) ? $postmeta['_claim_review_author_name'][0] : '';
        $author_url_meta_value = isset($postmeta['_claim_review_author_url']) ? $postmeta['_claim_review_author_url'][0] : '';
        $author_type_meta_value = isset($postmeta['_claim_review_author_type']) ? $postmeta['_claim_review_author_type'][0] : '';
        $publication_name_meta_value = isset($postmeta['_claim_review_publication_name']) ? $postmeta['_claim_review_publication_name'][0] : '';
        $publication_url_meta_value = isset($postmeta['_claim_review_publication_url']) ? $postmeta['_claim_review_publication_url'][0] : '';
        $publication_date_meta_value = isset($postmeta['_claim_review_publication_date']) ? $postmeta['_claim_review_publication_date'][0] : '';
        // $review_summary_meta_value = isset($postmeta['_claim_review_review_summary']) ? $postmeta['_claim_review_review_summary'][0] : '';
        $review_rating_meta_value = isset($postmeta['_claim_review_review_rating']) ? $postmeta['_claim_review_review_rating'][0] : '';
        
        $inputoptions  = get_option('claimreview_schema_options_posts', array('ratings_range'=>''));
        
        if(empty(trim($inputoptions['ratings_range']))){
          $ratingsoptions = ['1','2','3','4','5'];
        } else {
          $ratingsoptions = explode("\n", $inputoptions['ratings_range']);
        }
       
        // wp_enqueue_style( 'jquery-ui-datepicker' );
        ?>
      <?php wp_nonce_field( basename( __FILE__ ), 'claim_review_form_nonce' ); ?>
      <div  style="max-width:640px">
        <fieldset>
          <legend>
            <h2>What was said?</h2></legend>
          <table class="form-table">

            <tr>
              <th scope="row">
                <label for="claim-quote">Quote:</label>
              </th>
              <td>
                <textarea class="large-text" id="claim-quote" name="claim-quote" rows="6" style="max-width: 400px"><?php echo $claim_quote_meta_value ?></textarea>
              </td>
            </tr>

            <tr>
              <th scope="row">
                <label for="claim-summary">Summary:</label>
              </th>
              <td>
                <textarea class="large-text" id="claim-summary" name="claim-summary" rows="6" style="max-width: 400px"><?php echo $claim_summary_meta_value ?></textarea>
              </td>
            </tr>

          </table>

        </fieldset>

        <hr style="max-width:400px" />

        <fieldset>
          <legend>
            <h2>Who made the claim?</h2></legend>
          <table class="form-table">

            <tr>
              <th scope="row">
                <label for="author-name">Name:</label>
              </th>
              <td>
                <input type="text" name="author-name" id="author-name" class="regular-text" value="<?php echo $author_name_meta_value?>">
              </td>
            </tr>

            <tr>
              <th scope="row">
                <label for="author-url">Author  URL (wikipedia or organisation website):</label>
              </th>
              <td>
                <input type="text" name="author-url" id="author-url" class="regular-text" value="<?php echo $author_url_meta_value?>">
              </td>
            </tr>

            <tr>
              <th scope="row">
                <label>Author type</label>
              </th>
              <td>
                <label for="author-type-person">
                  <input type="radio" name="author-type" id="author-type-person" value="Person" <?php if($author_type_meta_value=='Person' ):?> checked="checked"
                  <?php endif; ?>>
                    <span><?php _e('Person', 'wp-factchecker-claim-review'); ?></span>
                </label>

                <label for="author-type-organisation">
                  <input type="radio" name="author-type" id="author-type-organisation" value="Organization" <?php if($author_type_meta_value=='Organization' ):?> checked="checked"
                  <?php endif; ?>>
                    <span><?php _e('Organization', 'wp-factchecker-claim-review'); ?></span>
                </label>
              </td>
            </tr>

          </table>
        </fieldset>

        <hr style="max-width:400px" />

        <fieldset>
          <legend>
            <h2>Where was the claim made?</h2></legend>
          <table class="form-table">

            <tr>
              <th scope="row">
                <label for="publication-name">Publication Name:</label>
              </th>
              <td>
                <input type="text" name="publication-name" id="publication-name" class="regular-text" value="<?php echo $publication_name_meta_value?>">
              </td>
            </tr>

            <tr>
              <th scope="row">
                <label for="publication-url">Publication URL:</label>
              </th>
              <td>
                <input type="text" name="publication-url" id="publication-url" class="regular-text" value="<?php echo $publication_url_meta_value?>">
              </td>
            </tr>


            <tr>
              <th scope="row">
                <label>Publication Date:</label>
              </th>
              <td>
                <input type="text" name="publication-date" id="publication-date" class="regular-text" value="<?php echo $publication_date_meta_value?date('Y-m-d', strtotime($publication_date_meta_value)):'' ?>">
                <p>Type the date as text - it will be converted to the ISO 8601 format (YYYY-mm-dd). Relative dates such as "today", "yesterday", etc. will also be converted.</p>
              </td>
            </tr>



          </table>
        </fieldset>


        <hr style="max-width:400px"/>

        <fieldset>
          <legend>
            <h2>Your Rating</h2></legend>
          <table class="form-table">

  

            <tr>
              <th scope="row">
                <label for="review-rating">Rating:</label>
              </th>
              <td>
                <?php
        
        
        foreach ($ratingsoptions as $p=>$label):?>
                  <input type="radio" name="review-rating" value="<?php echo $p?>" <?php if($review_rating_meta_value==$p):?> checked="checked"<?php endif ?>>
                    <?php echo $label;?>
              <?php endforeach; ?>
              </td>
            </tr>
          </table>
        </fieldset>
        </div>
        <?php
            
        }
        
        private function save_custom_field_value($post_id, $meta_key, $postmeta, $new_value){
            
            if(isset($postmeta[$meta_key]) && is_array($postmeta[$meta_key]) && isset($postmeta[$meta_key][0])){
              $meta_value = $postmeta[$meta_key][0];
            } else {
              $meta_value = '';
            }


            if(($new_value || $new_value=='0') && '' == $meta_key){
                add_post_meta( $post_id, $meta_key, $new_value, true);
            } elseif ( ($new_value || $new_value=='0') && $new_value != $meta_value) {
                update_post_meta( $post_id, $meta_key, $new_value);
            } elseif ( '' == $new_value ){
                delete_post_meta( $post_id, $meta_key, $meta_value);
            }
        }
        
        
        public function save_new_claim_review($request){
            $data = $request->get_json_params();
            // return $response = new \WP_REST_Response($data);
            
            
            if(!wp_verify_nonce( $data['new_claim_review_nonce'], 'wp_rest' )){
                return new \WP_Error( 'invalid_request', 'Not verifiedddd', array( 'status' => 403 ) );
            }
            if(!isset($data['post_title'])){
                return new \WP_Error( 'invalid_request', 'Not valid  payload', array( 'status' => 400 ) );
            }
            
            try {
                $new_post_id = wp_insert_post( array(
                'post_title' => $data['post_title'],
                'post_content' => '',
                'post_type' => 'claim_review',
                'post_status' => 'publish'
                ) );
                $post = get_post( $new_post_id );
                $this->save_custom_fields_data( $new_post_id, $data );
                $response = new \WP_REST_Response(array('id'=>$new_post_id, 'title' => $post->post_title));
                $response->set_status(201);
            } catch (Exception $e) {
                $response = $e;
            }
            return $response;
        }
        
        
        
        function save_custom_fields( $post_id, $post ) {
            
            $post_type = get_post_type_object( $post->post_type );
            
            if ( !current_user_can( $post_type->cap->edit_post, $post_id ) ){
                return $post_id;
            }
            
            if(defined("DOING_AUTOSAVE") && DOING_AUTOSAVE){
                return $post_id;
            }
            
            
            if($post->post_type == 'claim_review'){
                if ( !isset( $_POST['claim_review_form_nonce'] ) || !wp_verify_nonce( $_POST['claim_review_form_nonce'], basename( __FILE__ ) ) ){
                    return $post_id;
                }
                $this->save_custom_fields_data($post_id, $_POST);
            }
            $options = get_option('claimreview_schema_options_posts', array('supported_post_types'=>array()));
            if(in_array($post->post_type, $options['supported_post_types'])) {
                if ( !isset( $_POST['claim_review_selected_nonce'] ) || !wp_verify_nonce( $_POST['claim_review_selected_nonce'], basename( __FILE__ ) ) ){
                    return $post_id;
                }
                $this->save_selected_claimreviews($post_id, $_POST);
                
            }
            
        }
        
        private function save_selected_claimreviews($post_id, $data){
            // $data = array_keys($data['claim-reviews-selected']);
            $existing = get_metadata( 'post', $post_id, '_claim_reviews_selected', true );
            $new_value = ( isset( $data['claim-reviews-selected']) && is_array($data['claim-reviews-selected']) ? array_keys( $data['claim-reviews-selected'] ) : '');
            $this->save_custom_field_value($post_id, '_claim_reviews_selected', array('_claim_reviews_selected'=>$existing), $new_value);
        }
        
        
        private function save_custom_fields_data( $post_id, $data){
            
            $postmeta = get_metadata( 'post', $post_id);
            
            
            
            $new_claim_quote_value = ( isset( $data['claim-quote']) ? sanitize_textarea_field( $data['claim-quote'] ) : '');
            $claim_quote_meta_key = '_claim_review_claim_quote';
            $this->save_custom_field_value($post_id, $claim_quote_meta_key, $postmeta, $new_claim_quote_value);
            
            
            $new_claim_summary_value = ( isset( $data['claim-summary']) ? sanitize_textarea_field( $data['claim-summary'] ) : '');
            $claim_summary_meta_key = '_claim_review_claim_summary';
            $this->save_custom_field_value($post_id, $claim_summary_meta_key, $postmeta, $new_claim_summary_value);
            
            
            $new_author_name_value = ( isset( $data['author-name']) ? sanitize_text_field( $data['author-name'] ) : '');
            $author_name_meta_key = '_claim_review_author_name';
            $this->save_custom_field_value($post_id, $author_name_meta_key, $postmeta, $new_author_name_value);

            $new_author_url_value = ( isset( $data['author-url']) ? sanitize_text_field( $data['author-url'] ) : '');
            $author_url_meta_key = '_claim_review_author_url';
            $this->save_custom_field_value($post_id, $author_url_meta_key, $postmeta, $new_author_url_value);
            
            
            $new_author_type_value = ( isset( $data['author-type']) ? sanitize_text_field( $data['author-type'] ) : '');
            $author_type_meta_key = '_claim_review_author_type';
            $this->save_custom_field_value($post_id, $author_type_meta_key, $postmeta, $new_author_type_value);
            
            
            $new_publication_name_value = ( isset( $data['publication-name']) ? sanitize_text_field( $data['publication-name'] ) : '');
            $publication_name_meta_key = '_claim_review_publication_name';
            $this->save_custom_field_value($post_id, $publication_name_meta_key, $postmeta, $new_publication_name_value);
            
            
            $new_publication_url_value = ( isset( $data['publication-url']) ? esc_url_raw( $data['publication-url'] ) : '');
            $publication_url_meta_key = '_claim_review_publication_url';
            $this->save_custom_field_value($post_id, $publication_url_meta_key, $postmeta, $new_publication_url_value);
            
            
            $new_publication_date_value = ( isset( $data['publication-date']) ? sanitize_text_field( $data['publication-date'] ) : '');
            $new_publication_date_value = date('c', strtotime($new_publication_date_value));

            
            $publication_date_meta_key = '_claim_review_publication_date';
            $this->save_custom_field_value($post_id, $publication_date_meta_key, $postmeta, $new_publication_date_value);
            
            
            // $new_review_summary_value = ( isset( $data['review-summary']) ? sanitize_textarea_field( $data['review-summary'] ) : '');
            // $review_summary_meta_key = '_claim_review_review_summary';
            // $this->save_custom_field_value($post_id, $review_summary_meta_key, $postmeta, $new_review_summary_value);
            
            
            $new_review_rating_value = ( isset( $data['review-rating']) ? sanitize_text_field( $data['review-rating'] ) : '');
            $review_rating_meta_key = '_claim_review_review_rating';
            $this->save_custom_field_value($post_id, $review_rating_meta_key, $postmeta, $new_review_rating_value);
        }

        
        
    }