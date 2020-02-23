<?php 
include_once (dirname(__FILE__)."/../../includes/errorHandler.php");
?>
<script type="text/javascript">
// Convert divs to queue widgets when the DOM is ready
$(function() {
	$("#uploader").pluploadQueue({

		 
		// General settings
		runtimes : 'flash,silverlight,gears,browserplus,html5',
		url : '../ajax/general/fileuploader.php?temporary=<?php echo $temporaryKey; ?>&token=<?php echo $authToken; ?>&time=<?php echo $authTime; ?>',
		max_file_size : '30mb',
		chunk_size : '1mb',
		unique_names : true,

		// Flash settings
		flash_swf_url : '../includes/jscripts/plupload/js/plupload.flash.swf',

		// Silverlight settings
		silverlight_xap_url : '../includes/jscripts/plupload/js/plupload.silverlight.xap'
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
<script type="text/javascript" src="../includes/jscripts/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript">
	function openKCFinder(field_name, url, type, win) {
	    tinyMCE.activeEditor.windowManager.open({
	        file: '../includes/kcfinder/browse.php?opener=tinymce&type=' + type,
	        title: 'KCFinder',
	        width: 700,
	        height: 500,
	        resizable: "yes",
	        inline: true,
	        close_previous: "no",
	        popup_css: false
	    }, {
	        window: win,
	        input: field_name
	    });
	    return false;
	}
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
		width : "100%",
		height: 300,

		// Theme options
		theme_advanced_buttons1 : "bold,italic,underline,strikethrough,blockquote,link,unlink,image,emotions,charmap,|,cut,copy,pastetext,|,search,replace,|,undo,redo,code",
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

		// Replace values for the template plugin
		template_replace_values : {
			username : "Some User",
			staffid : "991234"
		},
		file_browser_callback : 'openKCFinder'
	});
</script>
<!-- /TinyMCE -->

<form method="post" action="index.php?var=module&amp;module=newsletter" enctype="multipart/form-data">
	<table class="newslettertable">
		<tr>
			<td>Betreff: </td>
			<td><input type="text" name="subject" class="newslettersubject" /></td>
		</tr>
		<tr>
			<td class="top">Text: </td>
			<td class="top"><textarea name="mailtext" rows="30"></textarea></td>
		</tr>
		<tr>
			<td class="top">Empf&auml;nger: </td>
			<td class="top">
				<table class="newslettertable">
					<tr>
						<td class="top">
							<?php foreach($roles as $curRole): ?>
							<input type="checkbox" value="1" name="<?php echo $curRole['role']; ?>" /> <?php echo $curRole['name']; ?> <br />
							<?php endforeach; ?>
						</td>
						<td class="top">
							Anh&auml;nge:
							<div id="uploader">
								Dein Browser unterst&uuml;tzt kein Flash, Silverlight, Gears, BrowserPlus oder HTML5.
							</div>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<input type="hidden" name="temporary" value="<?php echo $temporaryKey; ?>" />
				<input type="hidden" value="<?php echo $authTime; ?>" name="authTime" />
				<input type="hidden" value="<?php echo $authToken; ?>" name="authToken" />
				<button type="submit" name="action" value="send"> Absenden </button>
				<button type="reset"> L&ouml;schen </button>
			</td>
		</tr>
	</table>
</form>