
var authors = [];

function scr(id){
	$('html, body').animate({
		scrollTop: $(id).offset().top
	}, 500);
	return false;
};

function filterAuthor(text){
	
	$(".entryAuthor").each(function(){
		if($(this).text() == text || text == "showall"){
			$(this).parent().parent().parent().show();
		}else{
			$(this).parent().parent().parent().hide();
		}
	});
	
	$(".jamContent").each(function(){
		var visibleEntries = 0;
		$(this).children("a").each(function(){
			if($(this).css("display") == "inline"){
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
			$(this).parent().show();
			$(this).parent().parent().parent().show();
		}else{
			var string = $(this).find(".entryAuthor").text().toLowerCase() +" "+ $(this).find(".entryTitle").text();
			string = string.toLowerCase();
			
			if(string.indexOf(text) > -1){
				$(this).parent().show();
			}else{
				$(this).parent().hide();
			}
		}
	});
	
	$(".jamContent").each(function(){
		if(text != ""){
			var visibleEntries = 0;
			$(this).children("a").each(function(){
				if($(this).css("display") == "inline"){
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

$(document).ready(function(){	
	$("#search").keyup(search);
	$(".jamHeader").click(function(){
		$(this).parent().children(".jamContent").slideToggle();
	});
});