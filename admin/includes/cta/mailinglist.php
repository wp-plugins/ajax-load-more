<div class="cta mailing-list" id="alm-mailing-list">
	<div class="head-wrap">
	<h3><?php _e('Product Updates', ALM_NAME); ?></h3>
	<p><?php _e('Recieve product updates and release notifications delivered (infrequently) directly to your inbox.', ALM_NAME); ?></p>
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