<?php

class Factchecker_Claim_Review{
    
    
    public function __construct(){
        add_action('init', array( $this, 'register_post_types'));
        add_action('wp_head', array($this, 'hook_schema_jsonld'));
    }
    
    
    
    private function get_schema_author(){
        
        $options  = get_option('claimreview_schema_options_options', array());

        if(!isset($options['org_name'])){
            return false;
        }

        return array(
        "@type" =>  "Organization",
        "url" =>  isset($options['org_url']) ? $options['org_url'] : '',
        "sameAs" =>  isset($options['org_other_url']) ? $options['org_other_url'] : '',
        "name" =>  isset($options['org_name']) ? $options['org_name'] : ''
        );
    }
    
    private function get_claimreview_schema($post_id){

        $review_author = $this->get_schema_author();

        if(!$review_author){
            return '';
        }

      $claimreviews = get_metadata( 'post',$post_id, '_claim_reviews_selected', true );
        if(!is_array($claimreviews)){
            return '';
        }

      $reviews = array();
        foreach($claimreviews as $id){
            $postmeta = get_post_meta($id);
            $claim_quote_meta_key = '_claim_review_claim_quote';
            if(is_array($postmeta[$claim_quote_meta_key]) && isset($postmeta[$claim_quote_meta_key][0])){
                $claim_quote_meta_value = $postmeta[$claim_quote_meta_key][0];
            } else {
                $claim_quote_meta_value = '';
            }
            $claim_summary_meta_key = '_claim_review_claim_summary';
            if(is_array($postmeta[$claim_summary_meta_key]) && isset($postmeta[$claim_summary_meta_key][0])){
                $claim_summary_meta_value = $postmeta[$claim_summary_meta_key][0];
            } else {
                $claim_summary_meta_value = '';
            }
            $author_name_meta_key = '_claim_review_author_name';
            if(is_array($postmeta[$author_name_meta_key]) && isset($postmeta[$author_name_meta_key][0])){
                $author_name_meta_value = $postmeta[$author_name_meta_key][0];
            } else {
                $author_name_meta_value = '';
            }
            $author_type_meta_key = '_claim_review_author_type';
            if(is_array($postmeta[$author_type_meta_key]) && isset($postmeta[$author_type_meta_key][0])){
                $author_type_meta_value = $postmeta[$author_type_meta_key][0];
            } else {
                $author_type_meta_value = '';
            }
            $publication_name_meta_key = '_claim_review_publication_name';
            if(is_array($postmeta[$publication_name_meta_key]) && isset($postmeta[$publication_name_meta_key][0])){
                $publication_name_meta_value = $postmeta[$publication_name_meta_key][0];
            } else {
                $publication_name_meta_value = '';
            }
            $publication_url_meta_key = '_claim_review_publication_url';
            if(is_array($postmeta[$publication_url_meta_key]) && isset($postmeta[$publication_url_meta_key][0])){
                $publication_url_meta_value = $postmeta[$publication_url_meta_key][0];
            } else {
                $publication_url_meta_value = '';
            }
            $publication_date_meta_key = '_claim_review_publication_date';
            if(is_array($postmeta[$publication_date_meta_key]) && isset($postmeta[$publication_date_meta_key][0])){
                $publication_date_meta_value = $postmeta[$publication_date_meta_key][0];
            } else {
                $publication_date_meta_value = '';
            }
            $review_summary_meta_key = '_claim_review_review_summary';
            if(is_array($postmeta[$review_summary_meta_key]) && isset($postmeta[$review_summary_meta_key][0])){
                $review_summary_meta_value = $postmeta[$review_summary_meta_key][0];
            } else {
                $review_summary_meta_value = '';
            }
            $review_rating_meta_key = '_claim_review_review_rating';
            if(is_array($postmeta[$review_rating_meta_key]) && isset($postmeta[$review_rating_meta_key][0])){
                $review_rating_meta_value = $postmeta[$review_rating_meta_key][0];
            } else {
                $review_rating_meta_value = '';
            }
            
            $obj = array(
            '@context' => 'http://schema.org',
            '@type' => array('Review', 'ClaimReview'),
            'author' => review_author,
            'datePublished' => get_the_date( 'c', $id ),
            'dateModified' => get_the_modified_date( 'c', $id ),
            'url' => get_post_permalink(),
            'description' => $claim_summary_meta_value,
            'claimReviewed' => $claim_quote_meta_value,
            'itemReviewed' => array(
              '@type' => 'creativeWork',
              'author' => array(
                '@type' =>  $author_type_meta_value,
                'name' => $author_name_meta_value
              ),
              'url' => $publication_url_meta_value,
              'datePublished' => date('c', strtotime($publication_date_meta_value)
            ),
            'reviewBody' => $review_summary_meta_value,
            'reviewRating' =>array(
              '@type' => 'Rating',
              'alternateName' => '',
              'ratingValue' => $review_rating_meta_value
            )

            )
            );
          $reviews[] = $obj;
        }

        if(count($reviews == 1)){
          $reviews = $reviews[0];
        }

        if(count($reviews)){
          return '<script type="application/ld+json">'.wp_json_encode($reviews, JSON_UNESCAPED_SLASHES).'</script>';
        }

        return '';
    }


    public function hook_schema_jsonld(){
        global $post;
        
        echo $this->get_claimreview_schema($post->ID);
        
        
        
        
        
        
        
    }
    
    
    public function register_post_types(){
        register_post_type( 'claim_review', array(
        'label' => 'Claim Reviews',
        'public' => true,
        'show_in_rest' => true,
        'supports' => array(
        'title'
        )
        ) );
    }
    
    
    
    
    
}