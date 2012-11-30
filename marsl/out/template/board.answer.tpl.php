<?php
include_once (dirname(__FILE__)."/../includes/errorHandler.php");
?>
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
<h2>Antwort auf Thema: <a href="index.php?id=<?php echo $location; ?>&amp;action=posts&amp;thread=<?php echo $threadID;?>"><?php echo $title; ?></a></h2>
<form method="post" action="index.php?id=<?php echo $location; ?>&amp;action=answer&amp;thread=<?php echo $threadID; ?>">
	<textarea name="content" rows="15" style="width:100%"><?php echo $quote; ?></textarea>
	<input type="hidden" name="authTime" value="<?php echo $authTime; ?>" />
	<input type="hidden" name="authToken" value="<?php echo $authToken; ?>" />
	<div class="center">
		<button type="submit" name="do" value="answer"> Antworten </button>
		<button type="reset"> L&ouml;schen </button>
	</div>
</form>