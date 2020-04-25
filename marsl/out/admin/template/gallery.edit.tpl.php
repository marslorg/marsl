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
<form method="post" action="index.php?var=module&amp;module=gallery&amp;action=edit&amp;id=<?php echo $album; ?>">
	<table class="galtable">
		<tr>
			<td>Fotograf: </td>
			<td><input type="text" name="photograph" value="<?php echo $photograph; ?>" /></td>
		</tr>
		<tr>
			<td>Kategorie: </td>
			<td>
				<select name="category">
					<?php foreach ($locations as $location): ?>
					<option value="<?php echo $location['location']; ?>" <?php if ($location['location']==$category): ?>selected<?php endif; ?>>
						<?php echo $location['name']; ?>
					</option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<tr>
			<td>Datum des Geschehens: </td>
			<td>
				<input type="text" name="day" value="<?php echo $day; ?>" size="2" />
				<input type="text" name="month" value="<?php echo $month; ?>" size="2" />
				<input type="text" name="year" value="<?php echo $year; ?>" size="4" />
			</td>
		</tr>
		<tr>
			<td class="top">Beschreibung: </td>
			<td><textarea name="description" class="midtext" rows="10"><?php echo $description; ?></textarea></td>
		</tr>
		<tr>
			<td colspan="2" class="center">
				<input type="hidden" name="authTime" value="<?php echo $authTime; ?>" />
				<input type="hidden" name="authToken" value="<?php echo $authToken; ?>" />
				<button type="submit" name="action" value="send"> Absenden </button>
				<button type="reset"> L�schen </button>
			</td>
		</tr>
	</table>
</form>