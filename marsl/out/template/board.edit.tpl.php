<?php
include_once (dirname(__FILE__)."/../includes/errorHandler.php");
?>
<script type="text/javascript">
// Convert divs to queue widgets when the DOM is ready
$(function() {
	$("#uploader").pluploadQueue({		 
		// General settings
		runtimes : 'html5',
		url : 'ajax/general/fileuploader.php?temporary=<?php echo $temporaryKey; ?>&token=<?php echo $authToken; ?>&time=<?php echo $authTime; ?>',
		max_file_size : '30mb',
		chunk_size : '1mb',
		unique_names : true,
	});

	// Client side form validation
	$('form').submit(function(e) {
        var uploader = $('#uploader').pluploadQueue();

        // Files in queue upload them first
        if (uploader.files.length > 0) {
            // When all files are uploaded submit form
            uploader.bind('StateChanged', function() {
                if (uploader.files.length === (uploader.total.uploaded + uploader.total.failed)) {
                    $('form')[1].submit();
                }
            });
                
            uploader.start();
        }
        else {
            $('form')[1].submit();
        }

        return false;
    });
});
</script>
<!-- TinyMCE -->
<script type="text/javascript" src="includes/jscripts/tinymce/tinymce.min.js"></script>
<script type="text/javascript">
	tinymce.init({
		// General options
		selector : "textarea",
		language : "de",
		plugins : "advlist,autolink,autosave,charmap,codesample,colorpicker,contextmenu,directionality,emoticons,fullscreen,help,hr,image,imagetools,link,lists,nonbreaking,paste,preview,quickbars,searchreplace,tabfocus,table,textcolor,textpattern,toc,visualblocks,visualchars,wordcount",
		forced_root_block : false,
		remove_script_host : false,
		convert_urls : false,
		default_link_target: "_blank",

		// Example content CSS (should be your site CSS)
		content_css : "css/content.css",

		// Style formats
		style_formats : [
			{title : 'Bold text', inline : 'b'},
			{title : 'Red text', inline : 'span', styles : {color : '#ff0000'}},
			{title : 'Red header', block : 'h1', styles : {color : '#ff0000'}},
			{title : 'Example 1', inline : 'span', classes : 'example1'},
			{title : 'Example 2', inline : 'span', classes : 'example2'},
			{title : 'Table styles'},
			{title : 'Table row 1', selector : 'tr', classes : 'tablerow1'}
		]	
	});
</script>
<!-- /TinyMCE -->
<h2>Beitrag &auml;ndern</h2>
<form method="post" action="index.php?id=<?php echo $location; ?>&amp;action=edit&amp;post=<?php echo $postID; ?>&amp;page=<?php echo $page; ?>">
	<div style="display:table">
		<table>
			<tr><td><textarea name="content" rows="30"><?php echo $content; ?></textarea></td></tr>
			<tr>
				<td>
					<div id="uploaderCell">
						<div class="uploaderTitle">Anh&auml;nge hinzuf&uuml;gen:</div>
						<div id="uploader">
							Dein Browser unterst&uuml;tzt kein Flash, Silverlight, Gears, BrowserPlus oder HTML5.
						</div>
					</div>
				</td>
			</tr>
		</table>
	</div>
	<input type="hidden" name="temporary" value="<?php echo $temporaryKey; ?>" />
	<input type="hidden" name="authTime" value="<?php echo $authTime; ?>" />
	<input type="hidden" name="authToken" value="<?php echo $authToken; ?>" />
	<div class="center">
		<input type="hidden" name="do" value="edit" />
		<button type="submit" name="do" value="edit"> &Auml;ndern </button>
		<button type="reset"> L&ouml;schen </button>
	</div>
</form>