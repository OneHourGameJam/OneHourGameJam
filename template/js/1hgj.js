
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

$(document).ready(function(){	
	$("#search").keyup(search);
	$(".jamHeader").click(function(){
		$(this).parent().children(".jamContent").slideToggle();
		$(this).parent().children(".jamContent").find("img").each(function(){
			if($(this).css("display") == "inline"){
				var imgElement = $(this);
				if($(imgElement).attr("src") != $(imgElement).attr("hidden_src")){
					$(imgElement).attr("src", $(imgElement).attr("hidden_src"));
				}
			}
		});
	});
});