jQuery(document).ready(function($) {
   "use strict"; 
   
   $(".row select, .cnkt-main select").select2();    
   
   var _alm = {},
       output_div = $('#shortcode_output'),
       output = '[ajax_load_more]';    
       
   output_div.text(output); //Init the shortcode output        
   
   
   /*
   *  _alm.buildShortcode
   *  Loop sections and build the shortcode
   *
   *  @since 1.0
   */     

   _alm.buildShortcode = function(){
      output = '[ajax_load_more';
      
      // ---------------------------
      // - Repeater
      // ---------------------------
      
      var repeater = $('.repeater select').val(); 
      if(repeater != '' && repeater != undefined && repeater != 'default') 
         output += ' repeater="'+repeater+'"';
      
      
      // ---------------------------
      // - Post Types
      // ---------------------------
      
      var post_type_count = 0;
      $('.post_types input[type=checkbox]').each(function(e){         
         if($(this).is(":checked")) {
            post_type_count++;
            if(post_type_count>1){
               output += ', ' + $(this).data('type');
            }else{
               if($(this).hasClass('changed')){
                  output += ' post_type="'+$(this).data('type')+''; 
               }              
            }
         }
      }); 
      if(post_type_count>0) 
         output += '"';
        
      // ---------------------------
      // - Post Format
      // ---------------------------
      
      var post_format = $('.post_format select').val(); 
      if(post_format != '' && post_format != undefined) 
         output += ' post_format="'+post_format+'"';
      
 
        
      // ---------------------------
      // - Taxonomy Query     
      // ---------------------------
      
      var tax = $('select#taxonomy-select').val(),
      	  tax_operator = $('#tax-operator-select input[name=tax-operator]:checked').val();      	  
      	          
      if(tax !== '' && tax !== undefined){
         output += ' taxonomy="'+tax+'"';
         if($('select#taxonomy-select').hasClass('changed')){         	
         	$('#taxonomy-extended').slideDown(200, 'alm_easeInOutQuad');
         	get_tax_terms(tax);
         	$('select#taxonomy-select').removeClass('changed');
         }
         
		var tax_term_count = 0;
		$('#tax-terms-container input[type=checkbox]').each(function(e){         
			if($(this).is(":checked")) {
				tax_term_count++;
				if(tax_term_count>1){
					output += ', ' + $(this).data('type');
				}else{
				if($('#tax-terms-container input').hasClass('changed'))
					output += ' taxonomy_terms="'+$(this).data('type')+'';               
				}
			}
		}); 
		if(tax_term_count>0) 
		 output += '"';
         
         //Get Tax Operator
         if(tax_operator !== '' && tax_operator !== 'IN' && tax_operator !== undefined && tax_term_count !== 0){
	        output += ' taxonomy_operator="'+tax_operator+'"';
         }         
      }else{
	      $('#taxonomy-extended').slideUp(200, 'alm_easeInOutQuad');
      }
         
      // ---------------------------
      // - Categories      
      // ---------------------------
      
      var cat = $('.categories select').val();              
      if(cat !== '' && cat !== undefined) 
         output += ' category="'+cat+'"';
      
      // ---------------------------
      // - Tags      
      // ---------------------------
      
      var tag = $('.tags select').val();              
      if(tag !== '' && tag !== undefined) 
         output += ' tag="'+tag+'"';   
      
      // ---------------------------
      // - Authors      
      // ---------------------------
      
      var author = $('.authors select').val();              
      if(author !== '' && author !== undefined) 
         output += ' author="'+author+'"';   
      
      
      // ---------------------------
      // - Search      
      // ---------------------------
      
      var search = $('.search-term input').val();    
      search = $.trim(search);       
      if(search !== '') 
         output += ' search="'+search+'"'; 
      
      
      // ---------------------------
      // - Meta Key
      // ---------------------------
      var meta_key = $.trim($('input#meta-key').val()),
          meta_value = $.trim($('input#meta-value').val()),
          meta_compare = $('select#meta-compare').val();
     
      // Set meta_compare default value
      if(meta_compare === '' || meta_compare == undefined)       
          meta_compare = '=';
          
      if(meta_key !== '' && meta_key !== undefined){
         if($('input#meta-key').hasClass('changed')){         	
         	$('#meta-query-extended').slideDown(200, 'alm_easeInOutQuad');
         	         	
            output += ' meta_key="'+meta_key+'"';
            output += ' meta_value="'+meta_value+'"';
            
            if(meta_compare !== '=')
               output += ' meta_compare="'+meta_compare+'"';
         }
      }else{
	      $('#meta-query-extended').slideUp(200, 'alm_easeInOutQuad');
	      $('input#meta-key').removeClass('changed');
      } 
         
          
      // ---------------------------
      // - Ordering      
      // ---------------------------
      var order = $('select#post-order').val(),
         orderby = $('select#post-orderby').val();    
      if(order !== 'DESC') 
         output += ' order="'+order+'"'; 
      if(orderby !== 'date') 
         output += ' orderby="'+orderby+'"'; 
         
         
      // ---------------------------
      // - Exclude posts      
      // ---------------------------
      
      var exclude = $('.exclude input').val();    
      exclude = $.trim(exclude);       
      if(exclude !== ''){
         //Remove trailing comma, if present
         if(exclude.charAt( exclude.length-1 ) == ",") {
            exclude = exclude.slice(0, -1)
         }
         output += ' exclude="'+exclude+'"';  
      } 
      
      
      // ---------------------------
      // - Post Offset      
      // ---------------------------
      
      var offset = $('.offset select').val();   
      if(offset > 0) 
      	output += ' offset="'+offset+'"';  
      
      
      // ---------------------------
      // - Posts Per Page       
      // ---------------------------
      
      var posts_per_page = $('.posts_per_page select').val();  
      if(posts_per_page > 0 && posts_per_page !== 5 && $('.posts_per_page select').hasClass('changed'))
         output += ' posts_per_page="'+posts_per_page+'"';            
      
      
      // ---------------------------
      // - Scrolling      
      // ---------------------------
      
      var scroll_load = $('.scroll_load input[name=scroll]:checked').val();     
      if(scroll_load === 'f'){
         $('.row.max_pages').slideUp(100, 'alm_easeInOutQuad');
         if($('.scroll_load input').hasClass('changed'))          
            output += ' scroll="false"';         
      }else{
         $('.row.max_pages').slideDown(100, 'alm_easeInOutQuad');
         if($('.max_pages select').hasClass('changed'))           
            output += ' max_pages="'+$('.max_pages select').val()+'"';
         
      }   
      
      
      // ---------------------------
      // - Pause Loading      
      // ---------------------------
      
      var pause_load = $('.pause_load input[name=pause]:checked').val();     
      if(pause_load === 't')          
            output += ' pause="true"';         

      
      // ---------------------------
      // - transition       
      // ---------------------------
      
      var transition = $('.transition input[name=transition]:checked').val(); 
      if(transition !== 'slide')
         output += ' transition="'+transition+'"';
      
      
      // ---------------------------
      // - Button Label      
      // ---------------------------
      
      var btn_lbl = $('.btn-label input').val();    
      btn_lbl = $.trim(btn_lbl);       
      if(btn_lbl !== '' && $('.btn-label input').hasClass('changed')) 
         output += ' button_label="'+btn_lbl+'"';         
      
      
      output += ']';  //Close shortcode          
      output_div.text(output);      
   }  
   
    
   /*
   *  On change events
   *
   *  @since 2.0.0
   */ 
   
   //Select 'post' by default
   $('.post_types input[type=checkbox]#chk-post').prop('checked', true).addClass('changed'); 
   
   
   $(document).on('change keyup', '.alm_element', function() {      
      $(this).addClass('changed');      

      // If post type is not selected, select 'post'.
      if(!$('.post_types input[type=checkbox]:checked').length > 0){
         $('.post_types input[type=checkbox]#chk-post').prop('checked', true);
      } 
      // If Tax Term Operator is not selected, select 'IN'.
      if(!$('#tax-operator-select input[type=radio]:checked').length > 0){
         $('#tax-operator-select input[type=radio]#tax-in-radio').prop('checked', true);
      }      
      
      _alm.buildShortcode();
   });
   
   
   $('.search-term input, .exclude input, .btn-label input').keyup(function() {  
      $(this).addClass('changed'); 
      _alm.buildShortcode();
   });
   
   
   
   /*
   *  Jump to section
   *
   *  @since 2.0.0
   */ 
   
   var jumpOptions = '';
	$('.row').each(function(){
		var id = $(this).attr('id');
		var title = $(this).find('h3.heading').text();
		jumpOptions += '<option value="'+id+'">'+title+'</option>';
	});
	
	$('select.jump-menu').append(jumpOptions);
	
	$('select.jump-menu').change(function() {
		var pos = $(this).val();
		if(pos !== 'null'){
			$('html,body').animate({
			   scrollTop: $('#'+pos).offset().top - ($('.intro').height() - 20)
			}, 200, 'alm_easeInOutQuad');
		}
   });
    
    
   /*
   *  Expand/Collapse shortcode headings
   *
   *  @since 2.0.0
   */ 
   
	$(document).on('click', 'h3.heading', function(){
		var el = $(this);
		if($(el).hasClass('open')){
			$(el).next('.expand-wrap').slideDown(100, 'alm_easeInOutQuad', function(){
				$(el).removeClass('open');
			});
		}else{
			$(el).next('.expand-wrap').slideUp(100, 'alm_easeInOutQuad', function(){
				$(el).addClass('open');
			});
		}
	});
	
	$(document).on('click', '.toggle-all', function(){
        var el = $(this);
		if($(el).hasClass('closed')){
		    $(el).removeClass('closed');
            $('h3.heading').removeClass('open');
			$('.expand-wrap').slideDown(100, 'alm_easeInOutQuad');
		}else{
		    $(el).addClass('closed');
            $('h3.heading').addClass('open');
			$('.expand-wrap').slideUp(100, 'alm_easeInOutQuad');
		}
    });
	
	
   /*
   *  get_tax_terms
   *  Get taxonomy terms via ajax
   *
   *  @since 2.1.0
   */
   function get_tax_terms(tax){
   		var placement = $('#tax-terms-container');
   		placement.html("<p class='loading'>Fetching Terms...</p>");
		$.ajax({
			type: 'GET',
			url: window.parent.alm_admin_localize.ajax_admin_url,
			data: {
				action: 'alm_get_tax_terms',
				taxonomy: tax,
				nonce: window.parent.alm_admin_localize.alm_admin_nonce,
			},
			dataType: "html",
			success: function(data) {			
				//console.log(data);
				placement.html(data);
			},
			error: function(xhr, status, error) {
				responseText.html('<p>Error - Something went wrong and the terms could not be retrieved.');
			}
		});
	}
	
	
	
	/*
   *  _alm.alm_easeInOutQuad
   *  Ajax Load More easing
   *
   *  @since 2.0.0
   */  
   
	$.easing.alm_easeInOutQuad = function (x, t, b, c, d) {
      if ((t/=d/2) < 1) return c/2*t*t + b;
      return -c/2 * ((--t)*(t-2) - 1) + b;
   }
   
   
   
   /*
   *  _alm.SelectText
   *  Click to select text
   *
   *  @since 2.0.0
   */  
   
   _alm.SelectText = function(element) {
       var doc = document, 
         text = doc.getElementById(element), 
         range, 
         selection;    
       if (doc.body.createTextRange) {
           range = document.body.createTextRange();
           range.moveToElementText(text);
           range.select();
       } else if (window.getSelection) {
           selection = window.getSelection();        
           range = document.createRange();
           range.selectNodeContents(text);
           selection.removeAllRanges();
           selection.addRange(range);
       }
   }
   $('#shortcode_output').click(function() {
     _alm.SelectText('shortcode_output');
   });
   
   
   
   /*
   *  _alm.copyToClipboard
   *  Copy shortcode to clipboard
   *
   *  @since 2.0.0
   */     
	
	_alm.copyToClipboard = function(text) {
		window.prompt ("Copy link to your clipboard: Press Ctrl + C then hit Enter to copy.", text);
	}
	
	$('.output-wrap .copy').click(function(){
		var c = $('#shortcode_output').html();
		_alm.copyToClipboard(c);
	});
   
   

  
});