<div class="cta mailing-list social" id="alm-mailing-list">
	<div class="head-wrap">
	<h3><?php _e('Stay Informed', ALM_NAME); ?></h3>
		<h4><?php _e('Follow Ajax Load More', ALM_NAME); ?></h4>
		<a class="follow-btn facebook" href="http://facebook.com/ajaxloadmore" target="_blank"><i class="fa fa-facebook"></i></a>
		<a class="follow-btn twitter" href="http://twitter.com/ajaxloadmore" target="_blank"><i class="fa fa-twitter"></i></a>

		<h4 style="padding-top: 15px; border-top: 1px solid #efefef;"><?php _e('Join the Mailing List', ALM_NAME); ?></h4>
		<p><?php _e('Receive product updates delivered (infrequently) directly to your inbox.', ALM_NAME); ?></p>
	</div>
	<form action="" method="post" id="alm-mc-embedded" name="mc-embedded-subscribe-form" class="validate" data-path="<?php echo ALM_ADMIN_URL; ?>includes/mailchimp/mailchimp-info.php" novalidate>   	
      <div class="form-wrap">
         <div class="inner-wrap">
            <i class="fa fa-envelope"></i>
            <label for="mc_email" class="offscreen"><?php _e('Email Address', ALM_NAME); ?> <span class="asterisk">*</span> </label>
            <input type="email" value="" name="email" placeholder="<?php _e('Enter email address', ALM_NAME); ?>" class="required email" id="mc_email">
            <button type="submit" class="submit" id="mc_signup_submit" name="mc_signup_submit" title="Subscribe"><span class="offscreen"><?php _e('Subscribe', ALM_NAME); ?></span><i class="fa fa-arrow-circle-right"></i></button>
            <div id="response"><div class="p-wrap"><p></p></div></div>
         </div>
      </div>      
   </form>
</div>