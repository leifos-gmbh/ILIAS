<script language="javascript" type="text/javascript">
	$(document).ready(function(){
	
	
	
	<!-- Bookmark Script - DO NOT CHANGE -->
	  $("a.jQueryBookmark").click(function(e){
			e.preventDefault(); // this will prevent the anchor tag from going the user off to the link
			var bookmarkUrl = 'http://lms.skyguide.corp';
			var bookmarkTitle = 'Education Portal (LMS)';
		
			if (window.sidebar) { // For Mozilla Firefox Bookmark
				window.sidebar.addPanel(bookmarkTitle, bookmarkUrl,"");
			} else if( window.external || document.all) { // For IE Favorite
				window.external.AddFavorite( bookmarkUrl, bookmarkTitle);
			} else if(window.opera) { // For Opera Browsers
				$("a.jQueryBookmark").attr("href",bookmarkUrl);
				$("a.jQueryBookmark").attr("title",bookmarkTitle);
				$("a.jQueryBookmark").attr("rel","sidebar");
			} else { // for other browsers which does not support
				 alert('Your browser does not support this bookmark action');
				 return false;
			}
	  });
	  

	  $(".fold").animate({
		height: 	"toggle",
		opacity: 	"toggle"
		
	  }, "slow" );
	  
	  
	  
	  
	});
</script>


<div id='block_news_0'>
	<!-- Header DO NOT ALTER -->
	<div class='il_Block'>
		<div class='ilBlockHeader'>
			<a href="https://lmstest.skyguide.corp/ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToSelectedItems">News</a>
		</div>
		<div class='ilBlockRow1'>
			<!--span style='color:#E85D00;'>STC News</span-->
			<div class='small'>
				<strong>LMS now on latest release!</strong><br>
				Read more about the new features 
				<a href='http://www.ilias.de/docu/goto_docu_wiki_1357_Release_4.4.html' target='_blanc'> here.</a>				
			</div>
		</div>
	
		<!-- Block Beginn -->
		<!--
		<div class='ilBlockRow1'>
			<div class='std'>
				<div class='small'>
					<span style='color:red'><strong>EDU System Maintenance</strong></span>
					<div>
						<p>Please note that some EDU systems are not available tonight due to server maintenance. <br>Affected systems: MINT, 19:00-24:00</p>
					</div>
				</div>
			</div>
		</div>
		-->
		<!-- Block End -->
	
		<!-- Block Beginn -->
		<!--
		<div class='ilBlockRow1'>
			<div class='small'>
				<strong><FONT COLOR=green>STC Values and Norms</FONT></strong><br><br>
				<div>
					<center><a href='https://lms.skyguide.corp/goto.php?target=grp_22877&client_id=skyguide'><img src="https://lms.skyguide.corp/repository.php?ref_id=228182&cmd=sendfile"></center><br>
					<a href='https://lms.skyguide.corp/goto.php?target=grp_22877&client_id=skyguide' target='_blanc'>Definition of our values</a>
				</div>
			</div>
		</div>
		-->
		<!-- Block End -->
	
		<!-- Block Beginn -->
		<!--
		<div class='ilBlockRow1'>
			<div class='small'>
				<strong>Missing the WebFolder?</strong><br>
				<a href='https://lms.skyguide.corp/repository.php?ref_id=216111&cmd=sendfile' target='_blanc'>With the upgrade to Windows 7, webfolders were broken. We have a solution ready!</a>
			</div>
		</div>
		-->
		<!-- Block End -->
	
		<!-- Block Beginn -->
		<!--
		<div class='ilBlockRow1'>
				<div class='small'>
					<div>
						<hr noshade width="100%" size="3" align="center">
					</div>
				</div>
		</div>
		-->
		<!-- Block End -->
	
	
	
	
	
	
		<!-- Block Beginn -->
		<!--<tr class='ilBlockRow1'>
			<td class='std'>
				<div class='small'>
					<strong>Where is the login gone?</strong><br>
					Right, you are already logged in and this fully automated. Nice, eh?
				</div>
			</td>
		</tr>
		-->
		<!-- Block End -->
	
		<!-- Block Beginn -->
		<div class='ilBlockRow1'>
			<div class='small'>
				<a href='#' class="jQueryBookmark">Bookmark this page!</a>
			</div>
		</div>
		<!-- Block End -->
	
		<!-- Block Beginn -->
		<div class='ilBlockRow1'>				
			<div class='small'>
				<a href='mailto:elearning.support@skyguide.ch'>Questions? Email us!</a>
			</div>
		</div>
		<!-- Block End -->
	
	</div>
			
	
</div>