<?php
/*
Plugin Name: Factchecker's Schema.org ClaimReview 
Plugin URI: http://www.africacheck.org/
Description: Add schema.org ClaimReview metadata to your posts as JSON-LD objects. See https://pending.schema.org/ClaimReview
Author: Adam Davis
Version: 1.0 
Author URI: http://admataz.com/
*/


require_once('includes/class-factchecker-claim-review.php');
require_once('includes/class-factchecker-claim-review-admin.php');


$public = new Factchecker_Claim_Review();
$admin = new Factchecker_Claim_Review_Admin();
