/* 
 *  Copyright © Ludvik Michalek, Bigfoot Interactive
 *  http://www.bigfoot.cz/
 *  
 *  Sharing utils
 */

var SharingUtils = function(){};


/**
 * Static functions
 */


/**
 * Init links with 'share' class as sharing actions
 */
SharingUtils.initShareLinks = function(){
	$('a.share.facebook').click(function(e){
		e.preventDefault();
		var link = $(this);
		var url = link.attr('href');
		if(url){
			var shareUrl = 'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(url);
			var sharePopup = window.open(shareUrl, 'sharePopup facebook', 'toolbar=0,status=0,width=626,height=436');
		}
		return false;
	});

	$('a.share.twitter').click(function(e){
		e.preventDefault();
		var link = $(this);
		var url = link.attr('href');
		var title = link.data('title');
		if(url){
			var shareUrl = 'https://twitter.com/intent/tweet?text=' + (title ? encodeURIComponent(title) : '') + '&url=' + encodeURIComponent(url);
			var sharePopup = window.open(shareUrl, 'sharePopup twitter', 'toolbar=0,status=0,width=626,height=436');
		}
		return false;
	});

	$('a.share.google').click(function(e){
		e.preventDefault();
		var link = $(this);
		var url = link.attr('href');
		if(url){
			var shareUrl = 'https://plus.google.com/share?url=' + encodeURIComponent(url);
			var sharePopup = window.open(shareUrl, 'sharePopup google', 'toolbar=0,status=0,width=626,height=436');
		}
		return false;
	});

	$('a.share.linkedin').click(function(e){
		e.preventDefault();
		var link = $(this);
		var url = link.attr('href');
		if(url){
			var shareUrl = 'https://www.linkedin.com/shareArticle?mini=true&url=' + encodeURIComponent(url);
			var sharePopup = window.open(shareUrl, 'sharePopup linkedin', 'toolbar=0,status=0,width=626,height=436');
		}
		return false;
	});

	$('a.share.email').click(function(e){
		e.preventDefault();
		var link = $(this);
		var url = link.attr('href');
		var title = link.data('title');
		if(url){
			var shareUrl = 'mailto:?to=&subject=' + encodeURIComponent(title) + '&body=' + encodeURIComponent(url);
			window.location.href = shareUrl;
		}
		return false;
	});
};
