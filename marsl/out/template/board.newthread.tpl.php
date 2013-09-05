<?php
include_once (dirname(__FILE__)."/../includes/errorHandler.php");
?>
<script type="text/javascript">
// Convert divs to queue widgets when the DOM is ready
$(function() {
	$("#uploader").pluploadQueue({

		 
		// General settings
		runtimes : 'flash,silverlight,gears,browserplus,html5',
		url : 'ajax/general/fileuploader.php?temporary=<?php echo $temporaryKey; ?>&token=<?php echo $authToken; ?>&time=<?php echo $authTime; ?>',
		max_file_size : '30mb',
		chunk_size : '1mb',
		unique_names : true,

		// Flash settings
		flash_swf_url : 'includes/jscripts/plupload/js/plupload.flash.swf',

		// Silverlight settings
		silverlight_xap_url : 'includes/jscripts/plupload/js/plupload.silverlight.xap'
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
<script type="text/javascript" src="includes/jscripts/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript">
	tinyMCE.init({
		// General options
		language : "de",
		mode : "textareas",
		theme : "advanced",
		plugins : "autolink,lists,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,pasteAsPlainText,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,wordcount,advlist,autosave",
		extended_valid_elements : "a[href|target=_blank]",
		forced_root_block : false,
		force_br_newlines : true,
		force_p_newlines : false,
		remove_script_host : false,
		convert_urls : false,
		inline_styles : false,
		width : 500,
		height: 300,
		

		// Theme options
		theme_advanced_buttons1 : "bold,italic,underline,strikethrough,blockquote,link,unlink,image,emotions,charmap,|,cut,copy,pastetext,|,search,replace,|,undo,redo<?php if ($isAdmin): ?>,code<?php endif; ?>",
		theme_advanced_buttons2 : "",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_statusbar_location : "bottom",
		theme_advanced_resizing : true,

		// Example content CSS (should be your site CSS)
		content_css : "css/content.css",

		// Drop lists for link/image/media/template dialogs
		template_external_list_url : "lists/template_list.js",
		external_link_list_url : "lists/link_list.js",
		external_image_list_url : "lists/image_list.js",
		media_external_list_url : "lists/media_list.js",

		// Style formats
		style_formats : [
			{title : 'Bold text', inline : 'b'},
			{title : 'Red text', inline : 'span', styles : {color : '#ff0000'}},
			{title : 'Red header', block : 'h1', styles : {color : '#ff0000'}},
			{title : 'Example 1', inline : 'span', classes : 'example1'},
			{title : 'Example 2', inline : 'span', classes : 'example2'},
			{title : 'Table styles'},
			{title : 'Table row 1', selector : 'tr', classes : 'tablerow1'}
		],

		formats : {
			underline : {inline : 'u', exact : true},
			strikethrough : {inline : 's', exact : true}
		}

	});
</script>
<!-- /TinyMCE -->
<h2>Neues Thema</h2>
<form method="post" action="index.php?id=<?php echo $location; ?>&amp;action=newthread&amp;board=<?php echo $boardID; ?>">
	Titel: <input type="text" name="title" style="width:94%" /><br /><br />
	<div style="display:table">
		<textarea name="content" rows="15" style="display: table-cell; margin-top: 20px;"></textarea>
		<div id="uploaderCell">
			<div class="uploaderTitle">Anh&auml;nge hinzuf&uuml;gen:</div>
			<div id="uploader">
				Dein Browser unterst&uuml;tzt kein Flash, Silverlight, Gears, BrowserPlus oder HTML5.
			</div>
		</div>
	</div>
	<input type="hidden" name="temporary" value="<?php echo $temporaryKey; ?>" />
	<input type="hidden" name="authTime" value="<?php echo $authTime; ?>" />
	<input type="hidden" name="authToken" value="<?php echo $authToken; ?>" />
	<div class="center">
		<input type="hidden" name="do" value="newthread" />
		<button type="submit" name="do" value="newthread"> Thema erstellen </button>
		<button type="reset"> L&ouml;schen </button>
	</div>
</form>