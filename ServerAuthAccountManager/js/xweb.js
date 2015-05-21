/*
 * xWeb (v1.0) by EvolSoft
 * Developer: EvolSoft (Flavius12)
 * Website: http://www.evolsoft.tk
 * Date: 11/05/2015 06:15 PM (UTC)
 * Copyright & License: (C) 2015 EvolSoft
 * Licensed under MIT (https://github.com/EvolSoft/xWeb/blob/master/LICENSE)
 */

//**** xWeb Core ****//

/** @var string XWEB_VERSION **/
var XWEB_VERSION = "1.0";

/**
 * Changes the Webpage title
 * 
 * @param title The new title
 */
function changeTitle(title){
	if(checkElement("title")){
		$("title").text(title);
	}else{
		$("head").append("<title>" + title + "</title>");
	}
}

/**
 * Checks if a element exists
 * 
 * @param element The element tag, class, name or id to check
 * 
 * @return int
 */
function checkElement(element){
	return $(element).length;
}

/**
 * Get xWeb version
 * 
 * @return string xWebVersion
 */
function getVersion(){
	return XWEB_VERSION;
}

/**
 * px to int
 * 
 * @param px px
 * 
 * @return int
 */
function pxtoint(px){
	return parseInt(px.replace("px", ""));
}

//**** Alerts ****//

$(document).on("click", ".close", function() {
	if($(this).parent().hasClass("alert")){
		$(this).parent().trigger("alert.close");
		$(this).parent().remove();
	}
});

$(document).on("click", "[alert-close]", function() {
	if($(this).parent().hasClass("alert")){
		$(this).parent().trigger("alert.close");
		$(this).parent().remove();
	}
});

//**** Image Sliders ****//


//Auto initialize Image Slider
$(document).ready(function(){
	$(".image-slider").each(function(){
		initializeImageSlider(this);
	});
	setInterval(function(){
		$("[auto-scroll]").each(function(){
			if($(this).hasClass("image-slider")){
				slideRight(this);
			}
		});
	}, 4000);
});

$(document).on("click", ".image-slider-control-left", function(){
	slideLeft($(event.target).parent());
});

$(document).on("click", ".image-slider-control-right", function(){
	slideRight($(event.target).parent());
});

$(document).on("click", ".image-slider > .image-slider-navigation > li", function(){
	if(!$(event.target).hasClass("active")){
		if($(event.target).nextAll().hasClass("active")){
			slideRightPos($(event.target).parent().parent(), $(this).attr("img-id"));
		}else{
			slideLeftPos($(event.target).parent().parent(), $(this).attr("img-id"));
		}
	}

});

/**
 * Initialize an Image Slider
 * 
 * @param image_slider The image-slider
 */
function initializeImageSlider(image_slider){
	if($(image_slider).hasClass("image-slider")){
		ImageItem = $(image_slider).find(".image-slider-images").children(".image-slider-item");
		activeImageItem = $(image_slider).find(".image-slider-images").children(".image-slider-item.active");
		$(image_slider).trigger("image-slider.initialize");
		if(activeImageItem.length > 0){
			activeImageItem.css("margin-left", "0%");
			activeImageItem.prevAll().css("margin-left", "-100%");
			activeImageItem.nextAll().css("margin-left", "100%");
			$(image_slider).find(".image-slider-navigation").find("[img-id='" + activeImageItem.attr("img-item") + "']").addClass("active");
		}else{
			ImageItem.first().addClass("active");
			ImageItem.nextAll().css("margin-left", "100%");
			$(image_slider).find(".image-slider-navigation").find("[img-id='" + ImageItem.attr("img-item") + "']").addClass("active");
		}
	}
}

/**
 * Slide to left image on Image Slider
 * 
 * @param image_slider The image-slider
 */
function slideLeft(image_slider){
	if($(image_slider).hasClass("image-slider")){ //Check if the element is an image-slider
		parent = $(image_slider).find(".image-slider-images").children(".image-slider-item");
		activeParent = $(image_slider).find(".image-slider-images").children(".image-slider-item.active");
		$(image_slider).trigger("image-slider.slide");
		if(activeParent.prev().length > 0){ //Check if previous element exists
			parent.removeClass("active");
			activeParent.prev().addClass("active");
			activeParent.css("margin-left", "0%");
			activeParent.prev().css("margin-left", "-100%");
			activeParent.animate({"margin-left" : "100%"}, 200);
			activeParent.prev().animate({"margin-left" : "0%"}, 200);
			$(image_slider).find(".image-slider-navigation").children("li").removeClass("active");
			$(image_slider).find(".image-slider-navigation").find("[img-id='" + activeParent.prev().attr("img-item") + "']").addClass("active");
		}else{
			parent.removeClass("active");
			parent.last().addClass("active");
			activeParent.css("margin-left", "0%");
			parent.last().css("margin-left", "-100%");
			activeParent.animate({"margin-left" : "100%"}, 200);
			parent.last().animate({"margin-left" : "0%"}, 200);
			$(image_slider).find(".image-slider-navigation").children("li").removeClass("active");
			$(image_slider).find(".image-slider-navigation").find("[img-id='" + parent.last().attr("img-item") + "']").addClass("active");
		}
	}
}

/**
 * Slide to left image on the specified position on Image Slider
 * 
 * @param image_slider The image-slider
 * @param position The position
 */
function slideLeftPos(image_slider, position){
	if($(image_slider).hasClass("image-slider") && $(image_slider).find(".image-slider-images").children("[img-item='" + position + "']")){ //Check if the element is an image-slider
		parent = $(image_slider).find(".image-slider-images").children(".image-slider-item");
		parent.removeClass("active");
		$(image_slider).find(".image-slider-images").children("[img-item='" + position + "']").addClass("active");
		initializeImageSlider(image_slider);
		activeParent = $(image_slider).find(".image-slider-images").children(".image-slider-item.active");
		$(image_slider).trigger("image-slider.slide");
		if(activeParent.prev().length > 0){ //Check if element exists
			activeParent.prev().css("margin-left", "0%");
			activeParent.css("margin-left", "100%");
			activeParent.animate({"margin-left" : "0%"}, 200);
			activeParent.prev().animate({"margin-left" : "-100%"}, 200);
			$(image_slider).find(".image-slider-navigation").children("li").removeClass("active");
			$(image_slider).find(".image-slider-navigation").find("[img-id='" + activeParent.attr("img-item") + "']").addClass("active");
		}else{
			activeParent.css("margin-left", "100%");
			parent.last().css("margin-left", "0%");
			activeParent.animate({"margin-left" : "0%"}, 200);
			parent.last().animate({"margin-left" : "-100%"}, 200);
			$(image_slider).find(".image-slider-navigation").children("li").removeClass("active");
			$(image_slider).find(".image-slider-navigation").find("[img-id='" + activeParent.attr("img-item") + "']").addClass("active");
		}
	}
}

/**
 * Slide to right image on Image Slider
 * 
 * @param image_slider The image-slider
 */
function slideRight(image_slider){
	if($(image_slider).hasClass("image-slider")){ //Check if the element is an image-slider
		parent = $(image_slider).find(".image-slider-images").children(".image-slider-item");
		activeParent = $(image_slider).find(".image-slider-images").children(".image-slider-item.active");
		$(image_slider).trigger("image-slider.slide");
		if(activeParent.next().length > 0){ //Check if next element exists
			parent.removeClass("active");
			activeParent.next().addClass("active");
			activeParent.css("margin-left", "0%");
			activeParent.next().css("margin-left", "100%");
			activeParent.animate({"margin-left" : "-100%"}, 200);
			activeParent.next().animate({"margin-left" : "0%"}, 200);
			$(image_slider).find(".image-slider-navigation").children("li").removeClass("active");
			$(image_slider).find(".image-slider-navigation").find("[img-id='" + activeParent.next().attr("img-item") + "']").addClass("active");
		}else{
			parent.removeClass("active");
			parent.first().addClass("active");
			activeParent.css("margin-left", "0%");
			parent.first().css("margin-left", "100%");
			activeParent.animate({"margin-left" : "-100%"}, 200);
			parent.first().animate({"margin-left" : "0%"}, 200);
			$(image_slider).find(".image-slider-navigation").children("li").removeClass("active");
			$(image_slider).find(".image-slider-navigation").find("[img-id='" + parent.first().attr("img-item") + "']").addClass("active");
		}
	}
}

/**
 * Slide to right image on the specified position on Image Slider
 * 
 * @param image_slider The image-slider
 * @param position The position
 */
function slideRightPos(image_slider, position){
	if($(image_slider).hasClass("image-slider") && $(image_slider).find(".image-slider-images").children("[img-item='" + position + "']")){ //Check if the element is an image-slider
		parent = $(image_slider).find(".image-slider-images").children(".image-slider-item");
		parent.removeClass("active");
		$(image_slider).find(".image-slider-images").children("[img-item='" + position + "']").addClass("active");
		initializeImageSlider(image_slider);
		activeParent = $(image_slider).find(".image-slider-images").children(".image-slider-item.active");
		$(image_slider).trigger("image-slider.slide");
		if(activeParent.next().length > 0){ //Check if element exists
			activeParent.next().css("margin-left", "0%");
			activeParent.css("margin-left", "-100%");
			activeParent.animate({"margin-left" : "0%"}, 200);
			activeParent.next().animate({"margin-left" : "100%"}, 200);
			$(image_slider).find(".image-slider-navigation").children("li").removeClass("active");
			$(image_slider).find(".image-slider-navigation").find("[img-id='" + activeParent.attr("img-item") + "']").addClass("active");
		}else{
			activeParent.css("margin-left", "-100%");
			parent.first().css("margin-left", "0%");
			activeParent.animate({"margin-left" : "0%"}, 200);
			parent.first().animate({"margin-left" : "100%"}, 200);
			$(image_slider).find(".image-slider-navigation").children("li").removeClass("active");
			$(image_slider).find(".image-slider-navigation").find("[img-id='" + activeParent.attr("img-item") + "']").addClass("active");
		}
	}
}

//**** Menus ****//

$(document).on("click", function() {
	var target = event.target;
	if($(target).parent().hasClass("open") && $(target).parent().hasClass("menu-group")){
		$(".menu-group").removeClass("open"); //Closes all other menus
	    $(target).parent().trigger("menu.close"); //Trigger menu.close events
	}else if($(target).parent().hasClass("menu-group") && $(target).attr("openmenu") == ""){
		$(".menu-group").removeClass("open"); //Closes all other menus
		$(target).parent().toggleClass("open");
		$(target).parent().trigger("menu.open");
	}else{ //Close all opened menus
		$(".menu-group").each(function(){
			if($(this).hasClass("open")){
				$(this).trigger("menu.close");
			}
		})
		$(".menu-group").removeClass("open"); //Closes all other menus
	}
});

//**** Modals ****//

/**
 * Toggles a modal
 * 
 * @param target The target modal
 */
function toggleModal(target) {
	if($(target).hasClass("modal")){
	    $(target).toggleClass("modal-open");
	    if($(target).hasClass("modal-open")){ //Modal opened
	    	$(target).trigger("modal.open");
	    }else{ //Modal closed
	    	$(target).trigger("modal.close");
	    }
	}
}

/**
 * Closes a modal
 * 
 * @param target The target modal
 */
function closeModal(target){
	if($(target).hasClass("modal")){
		$(target).trigger("modal.close");
		$(target).removeClass("modal-open");
		$(target).addClass("closing");
		$(target).on('transitionend', function() {
			$(this).removeClass("closing");
		});
	}
}

$(document).on("click", ".close", function() {
	if($(this).parent().parent().hasClass("modal")){ //.modal > .modal-window > .close
    	closeModal($(this).parent().parent());
	}
});

$(document).on("click", ".modal-background", function() {
	if($(this).parent().hasClass("modal")){ //.modal > .modal-background
	    if($(this).parent().attr("static") == "false" || typeof $(this).parent().attr("static") === typeof undefined){
	    	closeModal($(this).parent());
		}
	}
});

//**** Navbar ****//

$(document).on("click", ".navbar-toggle", function(){
	if($(this).parent().hasClass("navbar")){ //.navbar > .navbar-toggle
		$(this).parent().find(".navbar-links").toggleClass("open");
	}
});

//**** Sliders ****//

/** @var bool click **/
var click = false;
/** @var current **/
var current = null;
/** @var int pos **/
var pos = 0;

$(document).on("mousedown", function() {
	click = true;
	current = event.target;
});

$(document).on("mouseup", function() {
	click = false;
	current = null;
});

$(document).on("mousemove", function(e) {
	if(click && current != null && $(current).attr("class") == "slider-handle"){
		fixedpos = e.pageX - $(current).parent().offset().left;
		percent = Math.round(((fixedpos * 100) / pxtoint($(current).parent().css("width"))));
		if(e.pageX > pos){ //Check mouse direction
			if(fixedpos >= 0 && fixedpos <= $(current).parent().width()){
				$(current).css("left",  + percent + "%");
				$(current).parent().find(".slider-progress").css("width", percent + 1 + "%");
				$(current).parent().trigger("slider.change");
			}
		}else{
			if(fixedpos >= 0 && fixedpos <= $(current).parent().width()){
				$(current).css("left",  + percent + "%");
				$(current).parent().find(".slider-progress").css("width", percent + 1 + "%");
				$(current).parent().trigger("slider.change");
			}
		}
	}
	pos = e.pageX;
});

/**
 * Get range slider value
 * 
 * @param r_slider the range slider
 *
 * @return int|null range slider value in percentage or null if the element isn't a range slider
 */
function getRSliderVal(r_slider){
	if($(r_slider).hasClass("slider")){
		percentage = percent = Math.round(((pxtoint($(r_slider).find(".slider-handle").css("left")) * 100) / pxtoint($(r_slider).css("width"))));
		return percentage;
	}else{
		return null;
	}
}

//**** Tabs ****//

$(document).on("click", "a", function(){
	if($(this).parent().parent().hasClass("tabs")){ //ul.tabs > li > a
		$(this).parent().parent().find("li").removeClass("active"); //ul.tabs > li
		$(this).parent().addClass("active"); //ul.tabs > li
		$(this).parent().parent().parent().find(".tab-content").css("display", "none"); //Closes all tab-contents (div.tabs > ul.tabs > li > a)
		if($(this).parent().parent().parent().find("[tab-id=" + $(event.target).attr("open-tab") + "]").hasClass("tab-content")){
			$(this).parent().parent().parent().find("[tab-id=" + $(event.target).attr("open-tab") + "]").css("display", "block");
			$(this).parent().parent().parent().find("[tab-id=" + $(event.target).attr("open-tab") + "]").trigger("tab.open");
		}
	}
});

//**** Tooltips ****//

$(document).on("mouseover", function(){
	$("[tooltip]").hover(function(){
		tooltip = $("[tooltip-id='" + $(event.target).attr("tooltip") + "']");
		$(event.target).trigger("tooltip.show", [tooltip]);
		$(tooltip).css("visibility", "visible");
		$(tooltip).css("opacity", "0.8");
		if($(tooltip).hasClass("tooltip-top")){ //Top Tooltip
		    $(tooltip).css("top", $(event.target).position().top - ($(tooltip).outerHeight(true) - pxtoint($(event.target).css("marginTop"))));
			$(tooltip).css("left", $(event.target).position().left + (($(event.target).outerWidth(true) / 2) - ($(tooltip).outerWidth(true) / 2)));
		}else if($(tooltip).hasClass("tooltip-bottom")){ //Bottom Tooltip
		    $(tooltip).css("top", $(event.target).position().top + ($(event.target).outerHeight(true) - pxtoint($(event.target).css("marginBottom"))));
			$(tooltip).css("left", $(event.target).position().left + (($(event.target).outerWidth(true) / 2) - ($(tooltip).outerWidth(true) / 2)));
		}else if($(tooltip).hasClass("tooltip-left")){ //Left Tooltip
			$(tooltip).css("top", $(event.target).position().top + (($(event.target).outerHeight(true) / 2) - ($(tooltip).outerHeight(true) / 2)));
			$(tooltip).css("left", $(event.target).position().left + pxtoint($(event.target).css("marginLeft")) - ($(tooltip).outerWidth() + pxtoint($(tooltip).css("padding"))));
		}else if($(tooltip).hasClass("tooltip-right")){ //Right Tooltip
			$(tooltip).css("top", $(event.target).position().top + (($(event.target).outerHeight(true) / 2) - ($(tooltip).outerHeight(true) / 2)));
			$(tooltip).css("left", $(event.target).position().left + $(event.target).outerWidth(true) - pxtoint($(event.target).css("marginRight")) + pxtoint($(tooltip).css("padding")));
		}
	}, function(){
		tooltip = $("[tooltip-id='" + $(event.target).attr("tooltip") + "']");
		$(event.target).trigger("tooltip.hide", [tooltip]);
		$(tooltip).css("visibility", "hidden");
		$(tooltip).css("opacity", "0");
	});
});


