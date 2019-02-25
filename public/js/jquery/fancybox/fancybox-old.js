/*
 * FancyBox - simple jQuery plugin for fancy image zooming
 * Examples and documentation at: http://fancy.klade.lv/
 * Version: 0.1b (22/03/2008)
 * Copyright (c) 2008 Janis Skarnelis
 * Licensed under the MIT License: http://www.opensource.org/licenses/mit-license.php
 * Requires: jQuery v1.2.1 or later
*/
$.fn.fancybox = function(settings) {
	settings = $.extend({}, $.fn.fancybox.defaults, settings);

	var clickedElem;
	var currentElem;
	var imgThumb;

	function getPageSize() {
		var d = document.documentElement;
		var w = window.innerWidth	|| self.innerWidth	|| (d && d.clientWidth)		|| document.body.clientWidth;
		var h = window.innerHeight	|| self.innerHeight	|| (d && d.clientHeight)	|| document.body.clientHeight;

		return [w,h];
	}

	function getPosition(el) {
		var pos = el.offset();

		pos.top		+= parseFloat(el.css('paddingTop'));
		pos.left	+= parseFloat(el.css('paddingLeft'));

		pos.top		+= parseFloat(el.css('borderTopWidth'));
		pos.left	+= parseFloat(el.css('borderLeftWidth'));

		return pos;
	}

	function getImageSize(maxWidth, maxHeight, imageWidth, imageHeight) {
		if (imageWidth > maxWidth) {
			imageHeight	= imageHeight * (maxWidth / imageWidth);
			imageWidth	= maxWidth;

			if (imageHeight > maxHeight) {
				imageWidth = imageWidth * (maxHeight / imageHeight);
				imageHeight = maxHeight;
			}

		} else if (imageHeight > maxHeight) {
			imageWidth	= imageWidth * (maxHeight / imageHeight);
			imageHeight	= maxHeight;

			if (imageWidth > maxWidth) {
				imageHeight = imageHeight * (maxWidth / imageWidth);
				imageWidth = maxWidth;
			}
		}

		return [Math.round(imageWidth), Math.round(imageHeight)];
	}

	function createTransparentDiv(attr, file, w, h, pos) {
		var z = arguments[5] !== undefined ? arguments[5]  : 90;
		var s = arguments[6] !== undefined ? arguments[6]  : '';
		var t = arguments[7] !== undefined ? arguments[7]  : '';

		var el = '';

		el += '<div ' + attr + ' style="z-index:9000;position:absolute;' + pos + ';' + (h ? 'height:' + h  + 'px;' : '') + 'width:' + w + 'px;' + s + ';';

		el += $.browser.msie ? 'FILTER:progid:DXImageTransform.Microsoft.AlphaImageLoader(sizingMethod=scale, src=\'' + file + '\');' : 'background:transparent url(\'' + file + '\') ' + (w && h ? 'repeat-' + (w > h ? 'x' : 'y') : '') + ';';

		el += '">' + t + '</div>';

		return el;
	}

	var removeFancy = function() {
		$(document).unbind("keydown");

		$("#fancy_close,#fancy_img").unbind("click");
        $("#fancy_block").stop();

		if (arguments[0] !== undefined && arguments[0] == true) {
			$("#fancy_wrap").remove();

		} else {
			$('#fancy_close,#btnLeft,#btnRight,div.fancy_shadow,#fancy_title').remove();

			if (settings.fancy) {
				imgThumb = currentElem.children("img:first");

				var pos = getPosition(imgThumb);
				var w	= imgThumb.width();
				var h	= imgThumb.height();

				var params = {
					left:		pos.left	+ "px",
					top:		pos.top		+ "px",
					height:		h,
					width:		w
				}

				if (settings.opacity) {
					params.opacity = 'hide';
				}

				$('#fancy_block').animate(params, settings.speed, "swing", function() {
					$("#fancy_wrap").remove();
				});

			} else {
				$('#fancy_block').fadeOut(settings.speed, function() {
					$("#fancy_wrap").remove();
				});
			}
		}
	}

	var showFancy = function() {
	    currentElem = clickedElem;

		var pageSize	= getPageSize();
		var imageSize	= getImageSize(pageSize[0] - 70, pageSize[1] - 70, imgPreloader.width,  imgPreloader.height);

		var m_left	= Math.round(pageSize[0] / 2)  - Math.round(imageSize[0] / 2);
		var m_top	= Math.round(pageSize[1] / 2)  - Math.round(imageSize[1] / 2);

		m_top	+= typeof window.pageYOffset != 'undefined' ? window.pageYOffset : document.documentElement.scrollTop;
		m_left	+= typeof window.pageXOffset != 'undefined' ? window.pageXOffset : document.documentElement.scrollLeft;

        $("#fancy_loading").remove();

		if ($("#fancy_wrap").is('*')) {
			removeFancy(true);
		}

		$('<div id="fancy_wrap"	style="z-index:9000;position:absolute;top:0px;left:0px;"></div>').prependTo("body");

		$('<div id="fancy_block" style="position:absolute;top:' + m_top + 'px;left:' + m_left + 'px;width:' + imageSize[0] + 'px;height:' + imageSize[1] + 'px;display:none;"></div>').appendTo("#fancy_wrap");

		$('<img id="fancy_img" style="width:100%;height:100%;position:absolute;z-index:93;" src="' + imgPreloader.src + '" />').appendTo("#fancy_block");

		var currentElemId		= currentElem.attr('id');
		var currentElemRel		= currentElem.attr('rel');
		var currentElemTitle	= currentElem.attr('title');

		var nextElem = false, prevElem = false, foundElem = false;

		if (currentElemRel !== undefined) {
			var arr_rel	= $("a[@rel=" + currentElemRel + "]").get();

			for (var i = 0; ((i < arr_rel.length) && (nextElem === false)); i++) {
				if (!(arr_rel[i].id == currentElemId)) {
					foundElem ? nextElem = arr_rel[i].id : prevElem = arr_rel[i].id;

				} else {
					foundElem = true;
				}
			}
		}

        $(document).keydown(function(event) {
            if (event.keyCode == 27) {
                removeFancy();

            } else if(event.keyCode == 37 && prevElem) {
				$("#" + prevElem).click();

			} else if(event.keyCode == 39 && nextElem) {
				$("#" + nextElem).click();
			}
        });

        $('#fancy_block').fadeIn("normal", function() {
			$( createTransparentDiv('id="fancy_close"', settings.path + 'fancy_closebox.png', 30, 30, 'top:-10px;left:-15px', 94) ).appendTo("#fancy_block");

			if (currentElemRel !== undefined || currentElemTitle !== undefined) {
				var titlePadding = nextElem || prevElem ? 50 : 15;
				currentElemTitle = currentElemTitle === undefined ? '&nbsp;' : currentElemTitle;

				$("#fancy_block").append(createTransparentDiv('id="fancy_title"', settings.path + 'fancy_title.png', (imageSize[0] - titlePadding), false, 'bottom:0px;left:0px', 94, 'color:#FFF;padding:12px 0 7px ' + titlePadding + 'px;font:' + settings.font,  currentElemTitle));
			}

			$("#fancy_block").append( createTransparentDiv('class="fancy_shadow"', settings.path + 'fancy_shadow1.png', (imageSize[0] - 28), 25, 'top:-7px;left:14px') );
			$("#fancy_block").append( createTransparentDiv('class="fancy_shadow"', settings.path + 'fancy_shadow2.png', 27, 25, 'top:-7px;right:-13px') );
			$("#fancy_block").append( createTransparentDiv('class="fancy_shadow"', settings.path + 'fancy_shadow3.png', 27, (imageSize[1] - 26), 'top:18px;right:-13px') );
			$("#fancy_block").append( createTransparentDiv('class="fancy_shadow"', settings.path + 'fancy_shadow4.png', 27, 26, 'bottom:-18px;right:-13px;') );
			$("#fancy_block").append( createTransparentDiv('class="fancy_shadow"', settings.path + 'fancy_shadow5.png', (imageSize[0] - 28), 26, 'bottom:-18px;left:14px;') );
			$("#fancy_block").append( createTransparentDiv('class="fancy_shadow"', settings.path + 'fancy_shadow6.png', 27, 26, 'bottom:-18px;left:-13px;') );
			$("#fancy_block").append( createTransparentDiv('class="fancy_shadow"', settings.path + 'fancy_shadow7.png', 27, (imageSize[1] - 26), 'top:18px;left:-13px;') );
			$("#fancy_block").append( createTransparentDiv('class="fancy_shadow"', settings.path + 'fancy_shadow8.png', 27, 25, 'top:-7px;left:-13px;') );

			if (prevElem) {
				$("#fancy_block").append( createTransparentDiv('id="btnLeft"', settings.path + 'fancy_left.png',24,24,'bottom:6px;left:14px',94, 'cursor:pointer;') );
				$("#btnLeft").click(function() {
					$("#" + prevElem).click();
				});

			} else if (nextElem) {
				$("#fancy_block").append( createTransparentDiv('id="btnLeft"', settings.path + 'fancy_left_off.png',24,24,'bottom:6px;left:14px',94) );
			}

			if (nextElem) {
				$("#fancy_block").append( createTransparentDiv('id="btnRight"', settings.path + 'fancy_right.png',24,24,'bottom:6px;right:14px',94,'cursor:pointer;') );
				$("#btnRight").click(function() {
					$("#" + nextElem).click();
				});

			} else if (prevElem) {
				$("#fancy_block").append( createTransparentDiv('id="btnRight"', settings.path + 'fancy_right_off.png',24,24,'bottom:6px;right:14px',94) );
			}

			$("#fancy_close,#fancy_img").click( function() {
				removeFancy();
			});
		});
	}

	var loadFancy = function(el) {
	    clickedElem = el;

		imgThumb = clickedElem.children("img:first");
		imgThumb.css("z-index", "9080");

		var pos = getPosition(imgThumb);
		var w	= imgThumb.width();
		var h	= imgThumb.height();

		$("#fancy_loading").remove();

		if ($("#fancy_wrap").is('*')) {
			removeFancy(true);
		}

        imgPreloader = new Image();
		imgPreloader.src = clickedElem.attr('href');

		if (imgPreloader.complete) {
			showFancy();
			return;
		}

		$('<div id="fancy_loading" style="position:absolute;z-index: 9000;top:' + pos.top + 'px;left:' +  pos.left + 'px; width:' + w + 'px;height:' + h + 'px;background: #FFF url(\'' + settings.path + 'fancy_loader.gif\') no-repeat center center;"></div>').prependTo("body");

		$("#fancy_loading").css("opacity", 0.5);

		$("#fancy_loading").click(function() {
			imgPreloader.onload = null;
			$(this).remove();
		});

		imgPreloader.onload = function() {
			showFancy();
		}
	}

	return this.each(function() {
		var $this = $(this);

		$this.click(function(e) {
			loadFancy($this);
			return false;
		});
	});
};

$.fn.fancybox.defaults = {
	fancy:		true,
	opacity:	true,
	speed:		500,
	font:		'normal 12px/18px Verdana,Helvetica;letter-spacing:1px;',
	path:		'/'
}