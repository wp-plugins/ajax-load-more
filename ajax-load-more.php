<?php
/*
Plugin Name: Ajax Load More
Plugin URI: http://connekthq.com/plugins/ajax-load-more
Description: A simple solution for lazy loading WordPress posts and pages with Ajax.
Author: Darren Cooney
Twitter: @KaptonKaos
Author URI: http://connekthq.com
Version: 2.6.3.2
License: GPL
Copyright: Darren Cooney & Connekt Media
*/	
	
define('ALM_VERSION', '2.6.3.2');
define('ALM_RELEASE', 'May 14, 2015');

/*
*  alm_install
*  Create table for storing repeater
*
*  @since 2.0.0
*/

register_activation_hook( __FILE__, 'alm_install' );
function alm_install() {   	
	global $wpdb;	
	$table_name = $wpdb->prefix . "alm";
	$defaultRepeater = '<li <?php if (!has_post_thumbnail()) { ?> class="no-img"<?php } ?>><?php if ( has_post_thumbnail() ) { the_post_thumbnail(array(100,100));}?><h3><a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a></h3><p class="entry-meta"><?php the_time("F d, Y"); ?></p><?php the_excerpt(); ?></li>';	
		
	//Create table, if it doesn't already exist.	
	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {	
		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			name text NOT NULL,
			repeaterDefault longtext NOT NULL,
			pluginVersion text NOT NULL,
			UNIQUE KEY id (id)
		);";		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
		
		//Insert the default data in created table
		$wpdb->insert($table_name , array('name' => 'default', 'repeaterDefault' => $defaultRepeater, 'pluginVersion' => ALM_VERSION));
	}	
	
	if( !get_option( 'alm_version' ) )
      add_option( 'alm_version', ALM_VERSION ); // Add 'alm_version' to WP options table
   else  
      update_option( 'alm_version', ALM_VERSION ); // Update 'alm_version'
      		
}



if( !class_exists('AjaxLoadMore') ):
	class AjaxLoadMore {	
		
   	function __construct(){	   
   	
   		define('ALM_PATH', plugin_dir_path(__FILE__));
   		define('ALM_URL', plugins_url('', __FILE__));
   		define('ALM_ADMIN_URL', plugins_url('admin/', __FILE__));
   		define('ALM_NAME', '_ajax_load_more');
   		define('ALM_TITLE', 'Ajax Load More');		
   		
   		add_action( 'wp_ajax_ajax_load_more_init', array(&$this, 'alm_query_posts') );
   		add_action( 'wp_ajax_nopriv_ajax_load_more_init', array(&$this, 'alm_query_posts') );
   		add_action( 'wp_enqueue_scripts', array(&$this, 'alm_enqueue_scripts') );			
   		add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array(&$this, 'alm_action_links') );
   
   		add_shortcode( 'ajax_load_more', array(&$this, 'alm_shortcode') );		
   		
   		// Allow shortcodes in widget areas
   		add_filter( 'widget_text', 'do_shortcode' );
   		
   		// load text domain
   		load_plugin_textdomain( 'ajax-load-more', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
   		
   		// Include ALM query functions
   		include_once( ALM_PATH . 'core/functions.php');
   		
   		// includes WP admin core
   		$this->alm_before_theme();	
   		
   	}	
   		
   	
   	/*
   	*  alm_before_theme
   	*  Load these files before the theme loads
   	*
   	*  @since 2.0.0
   	*/
   	
   	function alm_before_theme(){
   		if( is_admin()){
   			include_once('admin/editor/editor.php');
   			include_once('admin/admin.php');
   		}		
      }
      
   	/*
   	*  alm_action_links
   	*  Add plugin action links to WP plugin screen
   	*
   	*  @since 2.2.3
   	*/   
      
      function alm_action_links( $links ) {
         $links[] = '<a href="'. get_admin_url(null, 'admin.php?page=ajax-load-more') .'">'.__('Settings', ALM_NAME).'</a>';
         $links[] = '<a href="'. get_admin_url(null, 'admin.php?page=ajax-load-more-shortcode-builder') .'">'.__('Shortcode  Builder', ALM_NAME).'</a>';
         return $links;
      }
   
   
   
   	/*
   	*  alm_enqueue_scripts
   	*  Enqueue our scripts and create our localize variables
   	*
   	*  @since 2.0.0
   	*/
   
   	function alm_enqueue_scripts(){
   		
   		//wp_enqueue_script( 'ajax-load-more', plugins_url( '/core/js/ajax-load-more.js', __FILE__ ), array('jquery'),  '1.1', true );
   		wp_enqueue_script( 'ajax-load-more', plugins_url( '/core/js/ajax-load-more.min.js', __FILE__ ), array('jquery'),  '1.1', true );
   		
   		$options = get_option( 'alm_settings' );
   		
   		// Prevent loading of unnessasry posts - move user to top of page
   		$scrolltop = 'false';
   		if(!isset($options['_alm_scroll_top']) || $options['_alm_scroll_top'] != '1'){ // if unset or false
   			$scrolltop = 'false';
   		}else{ // if checked
      		$scrolltop = 'true';
   		}
   		
   		// 
   		if(!isset($options['_alm_disable_css']) || $options['_alm_disable_css'] != '1'){
   			wp_enqueue_style( 'ajax-load-more', plugins_url('/core/css/ajax-load-more.css', __FILE__ ));
   		}
   		
   		wp_localize_script(
   			'ajax-load-more',
   			'alm_localize',
   			array(
   				'ajaxurl'   => admin_url('admin-ajax.php'),
   				'alm_nonce' => wp_create_nonce( "ajax_load_more_nonce" ),
   				'pluginurl' => ALM_URL,
   				'scrolltop' => $scrolltop,
   			)
   		);
   		
   	}
   	
   
   
   	/*
   	*  alm_shortcode
   	*  The AjaxLoadMore shortcode
   	*
   	*  @since 2.0.0
   	*/
   
   	function alm_shortcode( $atts, $content = null ) {
   		$options = get_option( 'alm_settings' ); //Get plugin options
   		
   		extract(shortcode_atts(array(
				'cache' => 'false',		
				'cache_id' => '',	
				'preloaded' => 'false',
				'preloaded_amount' => '5',
				'seo' => 'false',
				'repeater' => 'default',
				'post_type' => 'post',
				'post_format' => '',
				'category' => '',	
				'category__not_in' => '',	
				'tag' => '',
				'tag__not_in' => '',
				'taxonomy' => '',
				'taxonomy_terms' => '',
				'taxonomy_operator' => '',	
				'meta_key' => '',
				'meta_value' => '',
				'meta_compare' => '',
				'year' => '',
				'month' => '',
				'day' => '',
				'author' => '',
				'search' => '',					
				'custom_args' => '',				
				'post_status' => '',					
				'order' => 'DESC',
				'orderby' => 'date',
				'post__in' => '',
				'exclude' => '',
				'offset' => '0',
				'posts_per_page' => '5',
				'scroll' => 'true',
				'scroll_distance' => '150',
				'max_pages' => '5',
				'pause' => 'false',
				'destroy_after' => '',
				'transition' => 'slide',
				'button_label' => __('Older Posts', ALM_NAME),		
			), $atts));
            
         // Get container elements (ul | div)
   		$container_element = 'ul';
   		if($options['_alm_container_type'] == '2')
   			$container_element = 'div';
   		
   		// Get extra classnames
   		$classname = '';
   		if(isset($options['_alm_classname']))
   			$classname = ' '.$options['_alm_classname'];
   		
   		// Get button color
   		$btn_color = '';
   		if(isset($options['_alm_btn_color']))
   			$btn_color = ' '.$options['_alm_btn_color'];
   		
   		// Get btn classnames
   		$button_classname = '';
   		if(isset($options['_alm_btn_classname']))
   			$button_classname = $options['_alm_btn_classname'];
   		
   		
   		// Language support   		
   		$lang = defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : ''; // WPML - http://wpml.org   		
   		if (function_exists('pll_current_language')) // Polylang - https://wordpress.org/plugins/polylang/
   		   $lang = pll_current_language();   		   
         if (function_exists('qtrans_getLanguage')) // qTranslate - https://wordpress.org/plugins/qtranslate/
   		   $lang = qtrans_getLanguage();  
               
         
   		$wp_posts_per_page = get_option( 'posts_per_page' ); // Posts per page	- settings -> reading
   		
   		/* If $wp_posts_per_page > than shortcode value then $posts_per_page to $wp_posts_per_page */
   		if(has_action('alm_seo_installed') && $wp_posts_per_page > $posts_per_page && $seo === 'true')
      		$posts_per_page = $wp_posts_per_page;  
      		      				   	
   		
   		// Start ALM object
   		$ajaxloadmore = '<div id="ajax-load-more" class="ajax-load-more-wrap '. $btn_color .'">';
   		
   		// Preload Posts
   		if(has_action('alm_preload_installed') && $preloaded === 'true'){   
   		   
   		   // If SEO then set $preloaded_amount to $posts_per_page
   		   if(has_action('alm_seo_installed') && $seo === 'true'){
   		      $preloaded_amount = $posts_per_page; 
            }
            
      		$preloaded_arr = array( // Create preload data array
         		'post_type'          => $post_type,
         		'post_format'        => $post_format,
         		'category'           => $category,
         		'category__not_in'   => $category__not_in,
         		'tag'                => $tag,
         		'tag__not_in'        => $tag__not_in,
         		'taxonomy'           => $taxonomy,
         		'taxonomy_terms'     => $taxonomy_terms,
         		'taxonomy_operator'  => $taxonomy_operator,
         		'meta_key'           => $meta_key,
         		'meta_value'         => $meta_value,
         		'meta_compare'       => $meta_compare,
         		'year'               => $year,
         		'month'              => $month,
         		'day'                => $day,
         		'author'             => $author,
         		'post__in'           => $post__in,
         		'search'             => $search,			
               'custom_args'        => $custom_args,
         		'post_status'        => $post_status,
         		'order'              => $order,
         		'orderby'            => $orderby,
         		'exclude'            => $exclude,
         		'offset'             => $offset,      		
         		'posts_per_page'     => $preloaded_amount,  
         		'lang'               => $lang,    		
            );   
                    		
      		$preloaded_type = preg_split('/(?=\d)/', $repeater, 2); // split $repeater at number to retrieve type
      		$preloaded_type = $preloaded_type[0]; // (default | repeater | template_)     		
      		
            // Create $args array and store it in $preloaded_arg_array
            $args = apply_filters('alm_preload_args', $preloaded_arr);
            
   			$alm_preload_query = new WP_Query($args);
   			$alm_total_posts = $alm_preload_query->found_posts - $offset;
            $output = '';
   			if ($alm_preload_query->have_posts()) :
   				$alm_loop_count = 0; // Count var
   				$alm_page = 0; // Set page to 0
   				$alm_found_posts = $alm_total_posts;
   			   while ($alm_preload_query->have_posts()) : $alm_preload_query->the_post();
   			   	$alm_loop_count++;
	   	         $alm_item = $alm_loop_count; // Get current item in loop 
   			   	$output .= apply_filters('alm_preload_inc', $repeater, $preloaded_type, $alm_found_posts, $alm_page, $alm_item);
               endwhile;
               wp_reset_query();
   			endif;
   			$preloaded_output = '<'.$container_element.' class="alm-listing alm-preloaded'. $classname .'" data-total-posts="'. $alm_total_posts .'">';
   			$preloaded_output .= $output;
   			$preloaded_output .= '</'.$container_element.'>';   			
   			
   			$ajaxloadmore .= $preloaded_output; // Add $preloadeded data to $ajaxloadmore
         }
         // End Preload Posts
   		
   		
   		
   		$ajaxloadmore .= '<'.$container_element.' class="alm-listing alm-ajax'. $classname . '"'; // Build ALM container 
   		
   		//Cache Add-on   		
   		if(has_action('alm_cache_installed') && $cache === 'true'){   		   
   		   $cache_return = apply_filters('alm_cache_shortcode', $cache, $cache_id, $options);   		   	
   			$ajaxloadmore .= $cache_return;		
         }
   		
   		// Preloaded Add-on
         if(has_action('alm_preload_installed') && $preloaded === 'true'){
   		   $ajaxloadmore .= ' data-preloaded="'.$preloaded.'"';	
            $ajaxloadmore .= ' data-preloaded-amount="'.$preloaded_amount.'"';
   		}
   		
   		
   		$ajaxloadmore .= ' data-repeater="'.$repeater.'"';
   		$ajaxloadmore .= ' data-post-type="'.$post_type.'"';
   		$ajaxloadmore .= ' data-post-format="'.$post_format.'"';
   		$ajaxloadmore .= ' data-category="'.$category.'"';
   		$ajaxloadmore .= ' data-category-not-in="'.$category__not_in.'"';
   		$ajaxloadmore .= ' data-tag="'.$tag.'"';
   		$ajaxloadmore .= ' data-tag-not-in="'.$tag__not_in.'"';
   		$ajaxloadmore .= ' data-taxonomy="'.$taxonomy.'"';
   		$ajaxloadmore .= ' data-taxonomy-terms="'.$taxonomy_terms.'"';
   		$ajaxloadmore .= ' data-taxonomy-operator="'.$taxonomy_operator.'"';
   		$ajaxloadmore .= ' data-meta-key="'.$meta_key.'"';
   		$ajaxloadmore .= ' data-meta-value="'.$meta_value.'"';
   		$ajaxloadmore .= ' data-meta-compare="'.$meta_compare.'"';
   		$ajaxloadmore .= ' data-year="'.$year.'"';
   		$ajaxloadmore .= ' data-month="'.$month.'"';
   		$ajaxloadmore .= ' data-day="'.$day.'"';
   		$ajaxloadmore .= ' data-author="'.$author.'"';
   		$ajaxloadmore .= ' data-post-in="'.$post__in.'"';
   		$ajaxloadmore .= ' data-exclude="'.$exclude.'"';
   		$ajaxloadmore .= ' data-search="'.$search.'"';
   		$ajaxloadmore .= ' data-custom-args="'.$custom_args.'"';
   		$ajaxloadmore .= ' data-post-status="'.$post_status.'"';
   		$ajaxloadmore .= ' data-order="'.$order.'"';
   		$ajaxloadmore .= ' data-orderby="'.$orderby.'"';
   		$ajaxloadmore .= ' data-offset="'.$offset.'"';	
   		$ajaxloadmore .= ' data-posts-per-page="'.$posts_per_page.'"';         
   		$ajaxloadmore .= ' data-lang="'.$lang.'"';
   		$ajaxloadmore .= ' data-scroll="'.$scroll.'"';
   		$ajaxloadmore .= ' data-scroll-distance="'.$scroll_distance.'"';
   		$ajaxloadmore .= ' data-max-pages="'.$max_pages.'"';
   		$ajaxloadmore .= ' data-pause="'.$pause.'"';
   		$ajaxloadmore .= ' data-button-label="'.$button_label.'"';
         $ajaxloadmore .= ' data-button-class="'.$button_classname.'"';
   		$ajaxloadmore .= ' data-destroy-after="'.$destroy_after.'"';
   		$ajaxloadmore .= ' data-transition="'.$transition.'"';   
   		   			
   		// SEO Add-on
   		if(has_action('alm_seo_installed') && $seo === 'true'){   		   
   		   $seo_return = apply_filters('alm_seo_shortcode', $seo, $preloaded, $options);   		   	
   			$ajaxloadmore .= $seo_return;		
         }      
   		
   		$ajaxloadmore .= '></'.$container_element.'>';
   		$ajaxloadmore .= '</div>';		
   		// End Build ALM container		
   		
   		return $ajaxloadmore;
   	}
   
   
   
   	/*
   	*  alm_query_posts
   	*  Ajax Load More Query
   	*
   	*  @since 2.0.0
   	*/
   
   	function alm_query_posts() {
   		
   		$nonce = $_GET['nonce'];
   		
   		$options = get_option( 'alm_settings' );
   		
   		if(!is_user_logged_in()){ // Skip nonce verification if user is logged in   		   
   		   
   		   $options = get_option( 'alm_settings' );
   		   
   		   // check alm_settings for _alm_nonce_security
   		   if(isset($options['_alm_nonce_security']) & $options['_alm_nonce_security'] == '1'){        		   		   
      		   if (! wp_verify_nonce( $nonce, 'ajax_load_more_nonce' )) // Check our nonce, if they don't match then bounce!
      		      die('Error, could not verify WP nonce.');      		      
            }
         }
         
   
   		$cache_id = (isset($_GET['cache_id'])) ? $_GET['cache_id'] : '';	
   		
   		$repeater = (isset($_GET['repeater'])) ? $_GET['repeater'] : 'default';		
   		$type = preg_split('/(?=\d)/', $repeater, 2); // split $repeater value at number to determine type
   		$type = $type[0]; // default | repeater | template_	
   		
   		$postType = (isset($_GET['postType'])) ? $_GET['postType'] : 'post';
   		$post_format = (isset($_GET['post_format'])) ? $_GET['post_format'] : '';
   		
   		$category = (isset($_GET['category'])) ? $_GET['category'] : '';
   		$category__not_in = (isset($_GET['category__not_in'])) ? $_GET['category__not_in'] : '';
   		$tag = (isset($_GET['tag'])) ? $_GET['tag'] : '';
   		$tag__not_in = (isset($_GET['tag__not_in'])) ? $_GET['tag__not_in'] : '';
   		
   		// Taxonomy
   		$taxonomy = (isset($_GET['taxonomy'])) ? $_GET['taxonomy'] : '';
   		$taxonomy_terms = (isset($_GET['taxonomy_terms'])) ? $_GET['taxonomy_terms'] : '';
   		$taxonomy_operator = $_GET['taxonomy_operator'];
   		if(empty($taxonomy_operator)) $taxonomy_operator = 'IN';
   		
   		// Date
   		$year = (isset($_GET['year'])) ? $_GET['year'] : '';
   		$month = (isset($_GET['month'])) ? $_GET['month'] : '';
   		$day = (isset($_GET['day'])) ? $_GET['day'] : '';
   		
   		// Custom Fields
   		$meta_key = (isset($_GET['meta_key'])) ? $_GET['meta_key'] : '';
   		$meta_value = (isset($_GET['meta_value'])) ? $_GET['meta_value'] : '';
   		$meta_compare = $_GET['meta_compare'];
   		if($meta_compare == '') $meta_compare = 'IN'; 
   		
   		$s = (isset($_GET['search'])) ? $_GET['search'] : '';   		
   		$custom_args = (isset($_GET['custom_args'])) ? $_GET['custom_args'] : '';
   		$author_id = (isset($_GET['author'])) ? $_GET['author'] : '';
   		
   		// Ordering
   		$order = (isset($_GET['order'])) ? $_GET['order'] : 'DESC';
   		$orderby = (isset($_GET['orderby'])) ? $_GET['orderby'] : 'date';
   		
   		// Include, Exclude, Offset, Status
   		$post__in = (isset($_GET['post__in'])) ? $_GET['post__in'] : '';	
   		$exclude = (isset($_GET['exclude'])) ? $_GET['exclude'] : '';		
   		$offset = (isset($_GET['offset'])) ? $_GET['offset'] : 0;
   		$post_status = $_GET['post_status'];
   		if($post_status == '') $post_status = 'publish'; 
   		
   		// Page
   		$numPosts = (isset($_GET['posts_per_page'])) ? $_GET['posts_per_page'] : 5;		
   		$page = (isset($_GET['pageNumber'])) ? $_GET['pageNumber'] : 0;
   		
   		// Preload
   		$preloaded = (isset($_GET['preloaded'])) ? $_GET['preloaded'] : 'false'; 
   		$preloaded_amount = (isset($_GET['preloaded_amount'])) ? $_GET['preloaded_amount'] : '5';  
   		if(has_action('alm_preload_installed') && $preloaded === 'true'){   		
   		   // If preload - offset the ajax posts by posts_per_page + preload_amount val	 
   		   $old_offset = $preloaded_amount;  	
   		   $offset = $offset + $preloaded_amount;	
         }
         
         //SEO
   		$seo_start_page = (isset($_GET['seo_start_page'])) ? $_GET['seo_start_page'] : 1;         
   		
   		// Language (Is this needed?)   			
   		$lang = (isset($_GET['lang'])) ? $_GET['lang'] : '';
   
   		// Set up initial args      
         $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
   		$args = array(
   			'post_type' => $postType,
   			'posts_per_page' => $numPosts,
   			'offset' => $offset + ($numPosts*$page),
   			'order' => $order,
   			'orderby' => $orderby,	
   			'post_status' => $post_status,
   			'ignore_sticky_posts' => false,
   			'paged' => $paged,
   		);
         
   	   // Post Format & taxonomy
   		if(!empty($post_format) || !empty($taxonomy)){	
   		   $args['tax_query'] = array(			
   				'relation' => 'AND',
   		      alm_get_tax_query($post_format, $taxonomy, $taxonomy_terms, $taxonomy_operator)
   		   );
   	   }
         
         // Category
   		if(!empty($category)){
   			$args['category_name'] = $category;
   		}
         
         // Category Not In
   		if(!empty($category__not_in)){
   		   $exclude_cats = explode(",",$category__not_in);
   			$args['category__not_in'] = $exclude_cats;
   		}
         
         // Tag
   		if(!empty($tag)){
   			$args['tag'] = $tag;
   		}
         
         // Tag Not In
   		if(!empty($tag__not_in)){
   		   $exclude_tags = explode(",",$tag__not_in);
   			$args['tag__not_in'] = $exclude_tags;
   		}
   	    
   	   // Date (not using date_query as there was issue with year/month archives)
   		if(!empty($year)){
      		$args['year'] = $year;
   	   } 
   	   if(!empty($month)){
      		$args['monthnum'] = $month;
   	   }  
   	   if(!empty($day)){
      		$args['day'] = $day;
   	   } 
   	    
   	   // Meta Query
   		if(!empty($meta_key) && !empty($meta_value)){
   			$args['meta_query'] = array(
   			   alm_get_meta_query($meta_key, $meta_value, $meta_compare)				
   			);
   	   }
         
         // Author
   		if(!empty($author_id)){
   			$args['author'] = $author_id;
   		}     
         
   		// Include posts
   		if(!empty($post__in)){
   			$post__in = explode(",",$post__in);
   			$args['post__in'] = $post__in;
   		}  
         
   		// Exclude posts
   		if(!empty($exclude)){
   			$exclude = explode(",",$exclude);
   			$args['post__not_in'] = $exclude;
   		}
         
         // Search Term
   		if(!empty($s)){
   			$args['s'] = $s;
   		}
         
         // Custom Args
         
   		if(!empty($custom_args)){
   			$custom_args_array = explode(",",$custom_args); // Split the $custom_args at ','
   			foreach($custom_args_array as $argument){ // Loop the $custom_args
   			   $argument = explode(":",$argument);  // Split the $custom_args at ':' 
   			   $args[$argument[0]] = $argument[1];
   			}
   		}
   	   
         // Meta_key, used for ordering by meta value
         if(!empty($meta_key)){
            $args['meta_key'] = $meta_key;
         }    
   		
         // Language
   		if(!empty($lang)){
   			$args['lang'] = $lang;
   		}
   		
   		// Set current page number for determining item number		
   		if($page == 0){
            $alm_page_count = 1;
   		}else{   		
   		   $alm_page_count = $page + 1;
   		}   		
   
   		// WP_Query()
   		$alm_query = new WP_Query( $args );	
   		
   		// If preload, set our loop count and total posts to
         if(has_action('alm_preload_installed') && $preloaded === 'true'){ 
            $alm_total_posts = $alm_query->found_posts - $offset + $preloaded_amount;
            if($old_offset > 0)
               $alm_loop_count = $old_offset;
            else
               $alm_loop_count = $offset;
         }else {
            $alm_total_posts = $alm_query->found_posts - $offset;
            $alm_loop_count = 0;
         }
         
         
         // Create cache directory 
         if(!empty($cache_id) && has_action('alm_cache_create_dir')){            
            $url = $_SERVER['HTTP_REFERER'];
            apply_filters('alm_cache_create_dir', $cache_id, $url);            
            $page_cache = ''; // set our page cache variable
         }
         
   		// Run the loop
   		if ($alm_query->have_posts()) : 
            $alm_found_posts = $alm_total_posts;    		     		   
   			while ($alm_query->have_posts()): $alm_query->the_post();	
   				$alm_loop_count++;         
   	         $alm_page = $alm_page_count; // Get page number      
   	         $alm_item = ($alm_page_count * $numPosts) - $numPosts + $alm_loop_count; // Get current item            
   				include( alm_get_current_repeater($repeater, $type) );//Include repeater template
   				
   				// If cache is enabled
   				// Build cache include and store in $page_cache variable
   				
   				if(!empty($cache_id) && has_action('alm_cache_inc')){
   				   $page_cache .= apply_filters('alm_cache_inc', $repeater, $type, $alm_page, $alm_found_posts, $alm_item);
      			}
   					   					
            endwhile; wp_reset_query();
         
         // If cache is enabled and seo_start_page is 1 (meaning, a user has not requested /page/12/)
         // - Only create cached files if the user visits pages in order 1, 2, 3 etc.
         
         if(!empty($cache_id) && has_action('alm_cache_installed') && $seo_start_page == 1){
            apply_filters('alm_cache_file', $cache_id, $page, $page_cache);
         }
         
   		endif; exit;
   	}
   	  	
   }
   
   
   /*
   *  AjaxLoadMore
   *  The main function responsible for returning the one true AjaxLoadMore Instance to functions everywhere.
   *
   *  @since 2.0.0
   */
   
   function AjaxLoadMore(){
   	global $ajax_load_more;
   
   	if( !isset($ajax_load_more) )
   	{
   		$ajax_load_more = new AjaxLoadMore();
   	}
   
   	return $ajax_load_more;
   }
   
   
   // initialize
   AjaxLoadMore();

endif; // class_exists check
