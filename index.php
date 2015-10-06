<!doctype html>
<html lang="en">
	<head>
		<meta charset='utf-8'>
		<script src="js/jquery.js"></script>
		<script type='text/javascript'>
			//SETUP!
			
			var NEXT_EVENT = "10-10-2015 20:00 UTC";

			
			$(document).ready(function(){
				//List of jams and entries. 
				//Newer jams should be BEFORE older ones so they appear higher in the list.
				//JAM_NUMBERs have to be unique (IE there can't be two jams with JAM_NUMBER set to 4.)
				//A jam has to be created BEFORE entries with the same JAM_NUMBER
			
				//makeJam(JAM_NUMBER, DATE, THEME);
				//makeEntry(JAM_NUMBER, AUTHOR, TITLE, IMAGE_URL, PALY_URL);

				makeJam(1,"6 Oct 2015", "First theme")
				makeEntry(1, "User", "Example entry", "logo.svg", "http://example.com");
			});
			
		</script>
		<title>1HGJ - One hour game jam</title>
		<meta name="viewport" content="width=device-width, initial-scale=1">

		<link href="bs/css/bootstrap.min.css" rel="stylesheet">
		<style type='text/css'>
			body{
				background-color: #EFEFEF;
			}
			
			.panel-body{
				background-color: #FFFFFF;
			}
		</style>
		<script type='text/javascript'>
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
			
			function makeJam(jamNumber, date, topic){
				var num = jamNumber;
				
				function getGetOrdinal(n) {
				   var s=["th","st","nd","rd"],
					   v = n%100;
				   return n+(s[(v-20)%10]||s[v]||s[0]);
				}
				
				var html = "";
				html += "<div class='panel panel-info' id='jam"+num+"'>";
				html += "	<div class='panel-heading'>";
				html += "		<a name='jam"+num+"'></a>";
				html += "		<h3 class='panel-title' style='font-size: 24px;'>";
				html += "			"+getGetOrdinal(num)+" hour game jam ("+date+")";
				html += "		</h3>";
				html += "		Topic: "+topic;
				html += "	</div>";
				html += "	<div class='panel-body' style='background: none; background-color: #F7FBFD;' id='entries"+num+"'>";
				html += "	</div>";
				html += "</div>";
				
				$(html).appendTo("#jamlist");
				
				linkhtml = "<a href='#' onclick='scr(\"#jam"+num+"\");'>"+getGetOrdinal(num)+" Jam</a><br />";
				
				$(linkhtml).appendTo("#jammenu");
			}
			
			function makeEntry(jamNumber, author, title, image, link){
				var html = "";
				html += "<a href='"+link+"' target='_BLANK'>";
				html += "	<div class='panel panel-default col-md-3 entry' style='padding-left: 0px; padding-right: 0px; height: 289px;'>";
				html += "		<div class='panel-body' style='text-align: center; height: 226px;'>";
				if(image != ""){
					html += "			<img src='"+image+"' style='max-width:100%; max-height: 200px' alt='"+title+" by "+author+"' onerror='this.src=\"logo.svg\"'>";
				}
				html += "		</div>";
				html += "		<div class='panel-footer'>";
				if(title != ""){
					html += "			<b><span class='entryTitle'>"+title+"</span></b><br />";
				}else{
					html += "			<b><span class='entryTitle'>Untitled</span></b><br />";
				}
				html += "			by <span class='entryAuthor'>"+author+"</span>";
				html += "		</div>";
				html += "	</div>";
				html += "</a>";
				$(html).appendTo("#entries"+jamNumber);
				
				var authorSane = author.trim();
				
				if(typeof(authors[authorSane]) === "undefined"){
					authors[authorSane] = 1;
					var authorHTML = "<li><a href='#' onclick='filterAuthor(\""+author+"\");' id='author-"+authorSane+"'>"+author+" (1)</a></li>";
					$(authorHTML).appendTo("#authorlist");
				}else{
					authors[authorSane]++;
					var authorHTML = author+" ("+authors[authorSane]+")";
					$("#author-"+authorSane).html(authorHTML);
				}
			}
		</script>
	</head>
	<body>
		<div class="container">
			<div class="row">
				<div class="col-md-12" style='text-align: center; margin-top: 10px;'>
					<div class="panel panel-info">
						<img src='logo.svg' alt='Logo' style='float: left; max-height: 100px; margin-top: 17px; margin-left: 17px;' />
						<div class="panel-body">
							<h1 style='margin-top: 0px;'>One hour game jam</h1>
							A weekly one hour game jam.<br />
							Next event: <span id='nextevent'>--</span>.<br />
							Join us on IRC: <a href='irc:afternet.org/ludumdare'>#ludumdare</a> channel on <a href='http://afternet.org'>afternet.org</a>
						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-2">
					<div class="panel panel-info">
						<div class="panel-heading">
							<h3 class="panel-title">Previous jams</h3>
						</div>
						<div class="panel-body">
							<ul id='jammenu' style='padding-left: 12px;'>
							
							</ul>
						</div>
					</div>
					<div class="panel panel-info">
						<div class="panel-heading">
							<h3 class="panel-title">Authors</h3>
						</div>
						<div class="panel-body">
							<ul id='authorlist' style='padding-left: 12px;'>
								<li><a href='#' onclick='filterAuthor("showall");'>Show all</a></li>
							</ul>
						</div>
					</div>
				</div>
				<div class="col-md-10" id='jamlist'>
					<input type="text" class="form-control" id='search' placeholder="Search for..." style='margin-bottom: 10px;'>
				</div>
			</div>
		</div>
		<script type='text/javascript'>
			$("#nextevent").html(NEXT_EVENT);
			$("#search").keyup(search);
		</script>
	
		<script src="bs/js/bootstrap.min.js"></script>
	</body>
</html>
