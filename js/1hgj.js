
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
			$(this).parent().parent().parent().fadeIn(200);
		}else{
			$(this).parent().parent().parent().fadeOut(200);
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
			$(this).parent().fadeIn(200);
		}else{
			var string = $(this).find(".entryAuthor").text().toLowerCase() +" "+ $(this).find(".entryTitle").text();
			string = string.toLowerCase();
			//alert(string);
			if(string.indexOf(text) > -1){
				$(this).parent().fadeIn(200);
			}else{
				$(this).parent().fadeOut(200);
			}
		}
	});
	return false;
}

function makeJam(jam){
	function getGetOrdinal(n) {
	   var s=["th","st","nd","rd"],
		   v = n%100;
	   return n+(s[(v-20)%10]||s[v]||s[0]);
	}
	
	var html = "";
	html += "<div class='panel panel-info' id='jam"+jam.jam_number+"'>";
	html += "	<div class='panel-heading'>";
	html += "		<a name='jam"+jam.jam_number+"'></a>";
	html += "		<h3 class='panel-title' style='font-size: 24px;'>";
	html += "			"+getGetOrdinal(jam.jam_number)+" hour game jam ("+jam.date+")";
	html += "		</h3>";
	html += "		Topic: "+jam.theme;
	html += "	</div>";
	html += "	<div class='panel-body' style='background: none; background-color: #F7FBFD;' id='entries"+jam.jam_number+"'>";
	html += "	</div>";
	html += "</div>";
	
	$(html).appendTo("#jamlist");
	
	linkhtml = "<a href='#' onclick='scr(\"#jam"+jam.jam_number+"\");'>"+getGetOrdinal(jam.jam_number)+" Jam</a><br />";
	
	$(linkhtml).appendTo("#jammenu");
}

function makeEntry(jam, entry){
	var html = "";
	html += "<a href='"+entry.url+"' target='_BLANK'>";
	html += "	<div class='panel panel-default col-md-3 entry' style='padding-left: 0px; padding-right: 0px; height: 289px;'>";
	html += "		<div class='panel-body' style='text-align: center; height: 226px;'>";
	if(entry.screenshot_url != ""){
		html += "			<img src='"+entry.screenshot_url+"' style='max-width:100%; max-height: 200px' alt='"+entry.title+" by "+entry.author+"' onerror='this.src=\"logo.png\"'>";
	}else{
		html += "			<img src='logo.png' style='max-width:100%; max-height: 200px' alt='"+entry.title+" by "+entry.author+"'>";
	}
	html += "		</div>";
	html += "		<div class='panel-footer'>";
	if(entry.title != ""){
		html += "			<b><span class='entryTitle'>"+entry.title+"</span></b><br />";
	}else{
		html += "			<b><span class='entryTitle'>Untitled</span></b><br />";
	}
	html += "			by <span class='entryAuthor'>"+entry.author+"</span>";
	html += "		</div>";
	html += "	</div>";
	html += "</a>";
	$(html).appendTo("#entries"+jam.jam_number);
	
	var authorSane = entry.author.trim();
	
	if(typeof(authors[authorSane]) === "undefined"){
		authors[authorSane] = 1;
		var authorHTML = "<li><a href='#' onclick='filterAuthor(\""+entry.author+"\");' id='author-"+authorSane+"'>"+entry.author+" (1)</a></li>";
		$(authorHTML).appendTo("#authorlist");
	}else{
		authors[authorSane]++;
		var authorHTML = entry.author+" ("+authors[authorSane]+")";
		$("#author-"+authorSane).html(authorHTML);
	}
}

$(document).ready(function(){	
	$("#nextevent").html(NEXT_EVENT);
	$("#search").keyup(search);
});