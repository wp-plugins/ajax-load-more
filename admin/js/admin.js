var _alm = _alm || {};

jQuery(document).ready(function($) {
	"use strict"; 	
	
	
	
	$('.alm-template-listing li a').click(function(e){
   	e.preventDefault();
   	var el = $(this),
   	    val = el.data('path');
   	el.parent().parent().next('.template-selection').val(val);
	});
	
	$('.alm-template-section-nav li a').click(function(e){
   	e.preventDefault();
   	var el = $(this),
   	    index = el.parent().index(),
   	    parent = el.parent().parent().parent('.repeater-wrap');
   	    
   	if(!el.hasClass('active')){
      	el.parent().addClass('active').siblings().removeClass('active');
      	$('.alm-template-toggle', parent).hide()
      	$('.alm-template-toggle', parent).eq(index).show();
   	}
   });
	
	
	
	/*
	*  Mailchimp Signup
	*  From the setting screen
	*
	*  @since 2.7.2
	*/
	$('form#alm-mc-embedded').submit(function() {
      var el = $('#alm-mailing-list'),
          email = $('input#mc_email', el).val(),
          data_path = $('form', el).data('path'); 
   	
   	// update user interface
   	$('#response', el).fadeIn(250).addClass('loading');
   	$('#response p', el).html('Adding email address...');   	
   	
   	// Verify email address
   	if(!IsEmail(email)){
   		$('#response p', el).html('<i class="fa fa-exclamation-circle"></i> Please enter a valid email address.');
   		$('#response', el).removeClass('loading');
   		$('#response', el).delay(2000).fadeOut(250);
   		return false;
   	}
   	// Prepare query string and send AJAX request
   	$.ajax({
   		url: data_path,
   		data: 'ajax=true&email=' + escape(email),
   		success: function(msg) {
   		   $('#response', el).removeClass('loading');
   			$('#response p', el).html(msg);			
   		},
   		error: function() {
            $('#response', el).removeClass('loading').delay(2000).fadeOut(250);	
   			$('#response p', el).html('There was an error submitting your email address.');
   		}
   	});
   	
   	return false;
   });
   function IsEmail(email) {
	  var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
	  return regex.test(email);
	}		
	
	
	
	/*
	*  _alm.copyToClipboard
	*  Copy shortcode to clipboard
	*
	*  @since 2.0.0
	*/     
	
	_alm.copyToClipboard = function(text) {
		window.prompt ("Copy link to your clipboard: Press Ctrl + C then hit Enter to copy.", text);
	}
	
	// Copy link on shortcode builder
	$('.output-wrap .copy').click(function(){
		var c = $('#shortcode_output').html();
		_alm.copyToClipboard(c);
	});
	
	// Copy link on repeater templates
	$('.alm-dropdown .copy a').click(function(){
		var container = $(this).closest('.repeater-wrap'), // find closet wrap
			 el = container.data('name'); // get template name
		
		if(el === 'default') el = 'template-default';
		var c = $('#'+el).val(); // Get textarea val()
		_alm.copyToClipboard(c);
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
   *  Scroll to setting section
   *
   *  @since 2.7.3
   */ 
   
	$(document).on('click', '.alm-settings-nav li a', function(e){
		e.preventDefault();
		var el = $(this).parent(),
			 index = el.index();
			 
		
		$('html, body').animate({
        scrollTop: $("#alm_OptionsForm h3").eq(index).offset().top - 40
    	}, 500);
		
		
	});
   
   
   
   /*
   *  equalheight()
   *
   *  @since 2.7.3
   */ 
   
   function equalheight(container){

      var currentTallest = 0,
           currentRowStart = 0,
           rowDivs = new Array(),
           $el,
           topPosition = 0;
       $(container).each(function() {
      
         $el = $(this);
         $($el).height('auto')
         topPosition = $el.position().top;
      
         if (currentRowStart != topPosition) {
           for (var currentDiv = 0 ; currentDiv < rowDivs.length ; currentDiv++) {
             rowDivs[currentDiv].height(currentTallest);
           }
           rowDivs.length = 0; // empty the array
           currentRowStart = topPosition;
           currentTallest = $el.height();
           rowDivs.push($el);
         } else {
           rowDivs.push($el);
           currentTallest = (currentTallest < $el.height()) ? ($el.height()) : (currentTallest);
        }
         for (currentDiv = 0 ; currentDiv < rowDivs.length ; currentDiv++) {
           rowDivs[currentDiv].height(currentTallest);
         }
       });
   }
   if($('#alm-add-ons').length){
      var addOnColumns = $('#alm-add-ons .group .expand-wrap');
      $(window).load(function() {
         equalheight(addOnColumns);
      });      
      $(window).resize(function() {
         setTimeout(function(){ 
            equalheight(addOnColumns); 
         }, 500);        
      });
   }

	
	
});