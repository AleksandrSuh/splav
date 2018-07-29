// JavaScript Document
$(function() {
		   
	$('.Play').click(function() {
								$('#playlist').playlist('play');
								});
	$('.Pause').click(function() {
								$('#playlist').playlist('pause');
								});
	
	$('.pmenu').hover(function() {
							  //alert('df');
							  $(this).children($('.submen').fadeIn());
							  },
					 function() {
						 	  $(this).children($('.submen').fadeOut());
					 });	
	$(".uch_listing a, .naverh").click(function(){
		var selected = $(this).attr('href');	
		$.scrollTo(selected, 500);		
		return false;
	});$('a[name=regmodal]').click(function(e) {
    	e.preventDefault();
    	var id = $(this).attr('href');  
   		var maskHeight = $(document).height();
    	var maskWidth = $(window).width();  
    	$('#mask').css({'width':maskWidth,'height':maskHeight});
    	$('#mask').fadeIn(600); 
   		$('#mask').fadeTo(600,0.5); 
    	var winH = $(window).height();
   		var winW = $(window).width();  
  		$(id).css('top',  winH/2-$(id).height()/2);
    	$(id).css('left', winW/2-$(id).width()/2);  
    	$(id).fadeIn(600);		
   });
  
 
   $('#mask, .close').click(function () {
		$('#mask').hide();
    	$('.window').hide();
     });
   $('.otpr2').click(function() {
    //alert('xs');
        var n = 0;
        var li = $("#zayav input").length;
         $("#zayav input").each(function(){
             if ($.trim($(this).val()).length==false)  $('.n2').fadeIn(); 
             else n++;
         });           
         if (n==li) {
            $('.n2').fadeOut();
            var email = $('input[name=email]').val();
            email2 = email.replace(/^[\w\-\_\.]+@[\w\-\_\.]+\.[\w\-\_\.]+$/,'');
            if (email2 != '') $('.n4').fadeIn(); 
            else {
                $('.n4').fadeOut();
                $('.n0').fadeIn();
                    $('#zayav form').submit();                
            }     
         }
    });
   $('#zayav textarea').click(function() {
									   alert('asa');
		if($(this).val()=='Текст статьи') $(this).val('');
	});
});