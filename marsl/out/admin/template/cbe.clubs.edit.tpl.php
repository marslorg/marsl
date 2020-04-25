<?php
include_once (dirname(__FILE__)."/../../includes/errorHandler.php");
?>
<!-- TinyMCE -->
<script type="text/javascript" src="../includes/jscripts/tinymce/tinymce.min.js"></script>
<script type="text/javascript">
	tinymce.init({
		// General options
		selector : "textarea",
		language : "de",
		plugins : "advlist,autolink,autosave,charmap,code,codesample,colorpicker,contextmenu,directionality,emoticons,fullscreen,help,hr,image,imagetools,link,lists,nonbreaking,paste,preview,quickbars,searchreplace,tabfocus,table,template,textcolor,textpattern,toc,visualblocks,visualchars,wordcount",
		forced_root_block : false,
		remove_script_host : false,
		convert_urls : false,
		default_link_target: "_blank",
		images_upload_url: 'ajax/sharedimagesuploader.php?authTime=<?php echo $authTime; ?>&authToken=<?php echo $authToken; ?>',
		image_upload_credentials: false,

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
		],

		templates: [
			{title: 'Linkbox', description: 'Fügt eine Linkbox rechts im Text mit einem soliden Rand ein.', content: '<table style="float: right; margin: 10px; border-width: 1px; border-style: solid; width: 250px;" border="0"><tbody><tr><td></a></td></tr></tbody></table>'}
		],

		image_title: true,
		automatic_uploads: true,

		file_picker_types: 'image',

		file_picker_callback: function (cb, value, meta) {
    	var input = document.createElement('input');
    	input.setAttribute('type', 'file');
    	input.setAttribute('accept', 'image/*');

    	input.onchange = function () {
     			var file = this.files[0];

      			var reader = new FileReader();
      			reader.onload = function () {
        			var id = 'blobid' + (new Date()).getTime();
        			var blobCache =  tinymce.activeEditor.editorUpload.blobCache;
        			var base64 = reader.result.split(',')[1];
        			var blobInfo = blobCache.create(id, file, base64);
        			blobCache.add(blobInfo);

        			cb(blobInfo.blobUri(), { title: file.name });
      			};
      			reader.readAsDataURL(file);
    		};

    		input.click();
  		}	
	});
</script>
<!-- /TinyMCE -->
<br />
<h2>Location: <?php echo $tag; ?></h2>
<form method="post" action="index.php?var=module&amp;module=cbe&amp;action=editclub&amp;club=<?php echo $id; ?>">
	<table class="newstable">
		<tr>
			<td>Stra&szlig;e - Hausnummer: </td>
			<td>
				<input type="text" name="street" value="<?php echo $street; ?>" /> - 
				<input type="text" name="number" value="<?php echo $number; ?>" size="3" />
			</td>
		</tr>
		<tr>
			<td>PLZ - Stadt: </td>
			<td>
				<input type="text" name="zip" value="<?php echo $zip; ?>" size="5" /> - 
				<input type="text" name="city" value="<?php echo $city; ?>" />
			</td>
		</tr>
		<tr><td> <br /> </td><td></td></tr>
		<tr>
			<td>Land: </td>
			<td><input type="text" name="country" value="<?php echo $country; ?>" /></td>
		</tr>
		<tr><td> <br /> </td><td></td></tr>
		<tr>
			<td>max. Kapazit&auml;t: </td>
			<td><input type="text" name="capacity" value="<?php echo $capacity; ?>" size="6" /></td>
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
<form method="post" action="index.php?var=module&amp;module=cbe&amp;action=editclub&amp;club=<?php echo $id; ?>">
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