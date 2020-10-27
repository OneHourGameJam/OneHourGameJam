
var authors = [];

function scr(id){
	$('html, body').animate({
		scrollTop: $(id).offset().top
	}, 500);
	return false;
};

function filterAuthor(text){
	text = text.trim().toLowerCase();
	$(".entryAuthor").each(function(){
		if($(this).text().trim().toLowerCase() == text || text == "showall"){
			$(this).parent().parent().show();
		}else{
			$(this).parent().parent().hide();
		}
	});
	
	$(".jamContent").each(function(){
		if(text != ""){
			var visibleEntries = 0;
			$(this).children("div").each(function(){
				if($(this).css("display") != "none"){
					var imgElement = $(this).children("div").children("div").children("img");
					if($(imgElement).attr("src") != $(imgElement).attr("hidden_src")){
						$(imgElement).attr("src", $(imgElement).attr("hidden_src"));
					}
					visibleEntries++;
				}
			});
			
			if(visibleEntries > 0){
				$(this).parent().fadeIn(200);
				$(this).slideDown(200);
			}else{
				$(this).parent().fadeOut(200);
				$(this).slideUp(200);
			}
		}
	});
	
	return false;
}

function search(){
	var text = $("#search").val();
	filterText(text);
}

function filterText(text){
	text = text.trim().toLowerCase();
	$(".entry").each(function(){
		if(text == ""){
			$(this).show();
		}else{
			var string = $(this).find(".entryAuthor").text().toLowerCase() +" "+ $(this).find(".entryTitle").text();
			string = string.toLowerCase();
			
			if(string.indexOf(text) > -1){
				$(this).show();
			}else{
				$(this).hide();
			}
		}
	});
	
	$(".jamContent").each(function(){
		if(text != ""){
			var visibleEntries = 0;
			$(this).children("div").each(function(){
				if($(this).css("display") != "none"){
					var imgElement = $(this).children("div").children("div").children("img");
					if($(imgElement).attr("src") != $(imgElement).attr("hidden_src")){
						$(imgElement).attr("src", $(imgElement).attr("hidden_src"));
					}
					visibleEntries++;
				}
			});
			
			if(visibleEntries > 0){
				$(this).parent().fadeIn(200);
				$(this).slideDown(200);
			}else{
				$(this).parent().fadeOut(200);
				$(this).slideUp(200);
			}
		}
	});
	
	return false;
}

Array.prototype.remove = function() {
	var what, a = arguments, L = a.length, ax;
	while (L && this.length) {
		what = a[--L];
		while ((ax = this.indexOf(what)) !== -1) {
			this.splice(ax, 1);
		}
	}
	return this;
};

function getCookie(cname) {
	var name = cname + "=";
	var decodedCookie = decodeURIComponent(document.cookie);
	var ca = decodedCookie.split(';');
	for(var i = 0; i <ca.length; i++) {
		var c = ca[i];
		while (c.charAt(0) == ' ') {
			c = c.substring(1);
		}
		if (c.indexOf(name) == 0) {
			return c.substring(name.length, c.length);
		}
	}
	return "";
}

function setCookie(name, value, days) {
	var d = new Date;
	d.setTime(d.getTime() + 24*60*60*1000*days);
	document.cookie = name + "=" + value + ";path=/;expires=" + d.toGMTString();
}

function ToggleNotification(id){
	$("#notificaion_"+id).toggle();
	$("#notification_icon_"+id).toggle();
	$("#notification_close_"+id).toggle();
	$("#notification_show_"+id).toggle();

	var cookieNoticeCookie = getCookie("cookienotice");
	if(cookieNoticeCookie != 1){
		$("#cookieNotice").show();
	}

	var closedNotificationsCookie = getCookie("closednotifications");
	if(closedNotificationsCookie != ""){
		var closedNotifications = JSON.parse(closedNotificationsCookie);
		if(closedNotifications.includes(id)){
			closedNotifications.remove(id);
		}else{
			closedNotifications.push(id);
		}
		setCookie("closednotifications", JSON.stringify(closedNotifications), 3650);
	}else{
		setCookie("closednotifications", JSON.stringify([id]), 3650);
	}
}

$(document).ready(function(){	
	$("#search").keyup(search);
	$(".jamContener").each(function(){
		var jamContainer = $(this);
		var jamHeader = jamContainer.find(".jamHeader");
		var jamContent = jamContainer.find(".jamContent");
		jamHeader.click(function(){
			jamContent.slideToggle();
			jamContent.find("img").each(function(){
				if($(this).css("display") == "inline"){
					var imgElement = $(this);
					if($(imgElement).attr("src") != $(imgElement).attr("hidden_src")){
						$(imgElement).attr("src", $(imgElement).attr("hidden_src"));
					}
				}
			});
		});
	})
});