// JavaScript Document
var BrowserDetect = {
	init: function() {
		this.browser = this.searchString(this.dataBrowser) || "An unknown browser";
		this.version = this.searchVersion(navigator.userAgent)
			|| this.searchVersion(navigator.appVersion)
			|| "an unknown version";
		this.OS = this.searchString(this.dataOS) || "an unknown OS";
		//alert(this.OS);
	},
	searchString: function (data) {
		for (var i=0;i<data.length;i++)	{
			var dataString = data[i].string;
			var dataProp = data[i].prop;
			this.versionSearchString = data[i].versionSearch || data[i].identity;
			if (dataString) {
				if (dataString.indexOf(data[i].subString) != -1)
					return data[i].identity;
			}
			else if (dataProp)
				return data[i].identity;
		}
	},
	searchVersion: function (dataString) {
		var index = dataString.indexOf(this.versionSearchString);
		if (index == -1) return;
		return parseFloat(dataString.substring(index+this.versionSearchString.length+1));
	},
	dataBrowser: [
		{
			string: navigator.userAgent,
			subString: "Chrome",
			identity: "Chrome"
		},
		{ 	string: navigator.userAgent,
			subString: "OmniWeb",
			versionSearch: "OmniWeb/",
			identity: "OmniWeb"
		},
		{
			string: navigator.vendor,
			subString: "Apple",
			identity: "Safari",
			versionSearch: "Version"
		},
		{
			prop: window.opera,
			identity: "Opera"
		},
		{
			string: navigator.vendor,
			subString: "iCab",
			identity: "iCab"
		},
		{
			string: navigator.vendor,
			subString: "KDE",
			identity: "Konqueror"
		},
		{
			string: navigator.userAgent,
			subString: "Firefox",
			identity: "Firefox"
		},
		{
			string: navigator.vendor,
			subString: "Camino",
			identity: "Camino"
		},
		{		// for newer Netscapes (6+)
			string: navigator.userAgent,
			subString: "Netscape",
			identity: "Netscape"
		},
		{
			string: navigator.userAgent,
			subString: "MSIE",
			identity: "Explorer",
			versionSearch: "MSIE"
		},
		{
			string: navigator.userAgent,
			subString: "Gecko",
			identity: "Mozilla",
			versionSearch: "rv"
		},
		{ 		// for older Netscapes (4-)
			string: navigator.userAgent,
			subString: "Mozilla",
			identity: "Netscape",
			versionSearch: "Mozilla"
		}
	],
	dataOS : [
		{
			string: navigator.platform,
			subString: "Win",
			identity: "Windows"
		},
		{
			string: navigator.platform,
			subString: "Mac",
			identity: "Mac"
		},
		{
			   string: navigator.userAgent,
			   subString: "iPhone",
			   identity: "iPhone/iPod"
	    },
		{
			string: navigator.platform,
			subString: "Linux",
			identity: "Linux"
		}
	]
 
};
BrowserDetect.init();
$(function() {
		   
	$('.add').click(function() {
						//alert ('d');
						var d = $(this).prev();
						//alert (d);
						var dd = d.clone();
						//alert (dd);
						dd.find('input').each(function() {
													   $(this).val('');
													   });
						dd.insertAfter($(this).prev());
/*		 var input = $('<input>', {
			name: $(this).prev().find(attr('name'),
			type: 'text'
		 });
		 $('<br />').insertAfter($(this).prev());
		 $(input).insertAfter($(this).prev()).focus().keypress(function(e){
			   if(e.keyCode==13){
				   $(this).parent().find('.add').click();
			   }
		 });*/
		 //alert('da');
	});
	$('.Play').click(function() {
								$('#playlist').playlist('play');
								});
	$('.Pause').click(function() {
								$('#playlist').playlist('pause');
								});
	
	$('.pmenu').hover(function() {
							   if ($(this).find('.submen').is(":hidden")) $('.submen', this).slideDown(300);
							  },
					 function() {
							  if ($(this).find('.submen').is(":visible")) $('.submen', this).slideUp(100);
					 });	
	$(".uch_listing a, .naverh").click(function(){
		var selected = $(this).attr('href');	
		$.scrollTo(selected, 500);		
		return false;
	});
	
	$('a[name=regmodal]').click(function(e) {
    	e.preventDefault();
    	var id = $(this).attr('href'); 
    	var rel = $(this).attr('rel'); 
		if (id=='#subcomm') {
			$.ajax({
				  type: "POST",
				  url: "/techn_pages/ajax/subcomm",
				  data: dataString = 'id='+rel,
				  cache: false,		
					success: function(html)
				  {
					  $(id).html(html);
				  } 
			});			
		};
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
        var li = $(this).parents(".window").find(".req").length;
         $(this).parents(".window").find(".req").each(function(){
             if ($.trim($(this).val()).length==false || $.trim($(this).val())=='текст')  $('.n2').fadeIn(); 
             else n++;
         });           
         if (n==li) {
            $('.n2').fadeOut();
/*            var email = $('input[name=email]').val();
            email2 = email.replace(/^[\w\-\_\.]+@[\w\-\_\.]+\.[\w\-\_\.]+$/,'');
            if (email2 != '') $('.n4').fadeIn(); 
            else {
                $('.n4').fadeOut();*/
                $('.n0').fadeIn();
                    $(this).parents('form').submit();                
/*            }   */  
         }
    });
   $('.window textarea').click(function() {
									   //alert($(this).val()+ '!=\'текст\'');
		if($(this).val()=='текст') $(this).val('');
	});
   $('#emailSubmit').click(function() {
		if ($.trim($('input[name=sign]').val())!='') {
			var email = $('input[name=sign]').val();
            email2 = email.replace(/^[\w\-\_\.]+@[\w\-\_\.]+\.[\w\-\_\.]+$/,'');
            if (email2 != '') $('.n2').fadeIn(); 
            else {
                $('.n2').fadeOut();
                $(this).parents('form').submit();                
            }
		}
	});
	
	$('.bxslider').bxSlider({
	  mode: 'fade',
	  auto: true,
	  pause:300,
	  speed:1500,
	  pager:false,
	  controls:false
	});
});