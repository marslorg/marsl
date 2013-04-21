<?php
include_once (dirname(__FILE__)."/../../includes/errorHandler.php");
?>
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
		forced_root_block : false,
		force_br_newlines : true,
		force_p_newlines : false,
		remove_script_host : false,
		convert_urls : false,

		// Theme options
		theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
		theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
		theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
		theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak,restoredraft",
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
<br />
<h2>Location: <?php echo $tag; ?></h2>
<form method="post" action="index.php?var=module&amp;module=cbe&amp;action=editband&amp;band=<?php echo $id; ?>">
	<table class="newstable">
		<tr>
			<td>Gr&uuml;ndung: </td>
			<td>
				<input type="text" name="founded" value="<?php echo $founded; ?>" size="4" />
			</td>
		</tr>
		<tr>
			<td>Aufl&ouml;sung: </td>
			<td>
				<input type="text" name="ended" value="<?php echo $ended; ?>" size="4" />
			</td>
		</tr>
		<tr><td> <br /> </td><td></td></tr>
		<tr>
			<td class="top">Info: </td>
			<td><textarea name="info"><?php echo $info; ?></textarea></td>
		</tr>
		<tr>
			<td colspan="2">
				<input type="hidden" name="authTime" value="<?php echo $authTime; ?>" />
				<input type="hidden" name="authToken" value="<?php echo $authToken; ?>" />
				<button type="submit" name="action" value="send"> Absenden </button>
				<button type="reset"> L&ouml;schen </button>
			</td>
		</tr>
	</table>
</form>
<h3>Tag umbenennen:</h3>
<form method="post" action="index.php?var=module&amp;module=cbe&amp;action=editband&amp;band=<?php echo $id; ?>">
	<input type="text" name="tag" value="<?php echo $tag; ?>" />
	<input type="hidden" name="authTime" value="<?php echo $authTime; ?>" />
	<input type="hidden" name="authToken" value="<?php echo $authToken; ?>" />
	<button type="submit" name="action" value="name"> &Auml;ndern </button>
</form>
<h3>Artikel:</h3>
<ul>
	<?php foreach($news as $article): ?>
	<li class="cbenews">
		<a href="index.php?var=module&amp;module=news&amp;action=details&amp;id=<?php echo $article['news']; ?>">
			<?php echo $article['headline']; ?>: <?php echo $article['title']; ?>
		</li>
	</li>
	<?php endforeach; ?>
</ul>