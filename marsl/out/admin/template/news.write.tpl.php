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
<script type="text/javascript">
	function handleTeaser(files) {
		teaserPhoto = files[0];
	}

	function uploadTeaser() {
		var reader = new FileReader();
		reader.onload = function () {
			var photograph = $('#teaser_photo_photograph').val();
			var fileName = $('#teaser_photo').val();
			document.getElementById('teaser_photo_form').innerHTML = '<tr><td class="center"><br />Je nach Gr&ouml;&szlig;e des Fotos kann dieser Vorgang etwas dauern.<br /><img src="../includes/graphics/big_loader.gif" /></td></tr>';
			$.post('ajax/newsphoto.php', {fileName: teaserPhoto.name, data: reader.result, authTime: '<?php echo $authTime; ?>', authToken: '<?php echo $authToken; ?>', type: 'teaser', photograph: photograph}, function(data) {
				var result = eval('(' + data + ')');
				if (result.type=="error") {
					if (result.code=="1") {
						document.getElementById('teaser_photo_form').innerHTML = '<tr><td colspan="2" class="center"><br /><b>Die hochgeladene Datei ist kein Bild.</b></td></tr>';	
					}
					if (result.code=="3") {
						document.getElementById('teaser_photo_form').innerHTML = '<tr><td colspan="2" class="center"><br /><b>Leider ist ein interner Fehler aufgetreten. Versuche es nochmal. Sollte der Fehler &ouml;fter auftreten, kontaktiere einen Administrator.</b></td></tr>';			
					}
					document.getElementById('teaser_photo_form').innerHTML += '<tr><td colspan="2" class="center"><h3>Teaser Foto</h3></td></tr>';
					document.getElementById('teaser_photo_form').innerHTML += '<tr><td>Foto: </td><td><input id="teaser_photo" type="file" onchange="JavaScript:handleTeaser(this.files)" /></td></tr>';
					document.getElementById('teaser_photo_form').innerHTML += '<tr><td>Fotograf: </td><td><input type="text" id="teaser_photo_photograph" value="'+photograph+'" /></td></tr>';
					document.getElementById('teaser_photo_form').innerHTML += '<tr><td colspan="2" class="center"><a onmouseover="this.style.cursor = \'pointer\'" onclick="JavaScript:uploadTeaser()"><b>Hochladen</b></a></td></tr>';
				}
				if (result.type=="success") {
					document.getElementById('teaser_photo_form').innerHTML = '<tr><td class="center"><br /><input type="hidden" name="picture1" value="'+result.id+'" /><img src="../news/'+result.file+'" /><br />Foto: '+photograph+'</td></tr>';
				}
			});
		}
		reader.readAsDataURL(teaserPhoto);
	}

	function handleText(files) {
		textPhoto = files[0];
	}

	function uploadText() {
		var reader = new FileReader();
		reader.onload = function () {
			var photograph = $('#text_photo_photograph').val();
			var fileName = $('#text_photo').val();
			var subtitle = $('#text_photo_text').val();
			document.getElementById('text_photo_form').innerHTML = '<tr><td class="center"><br />Je nach Gr&ouml;&szlig;e des Fotos kann dieser Vorgang etwas dauern.<br /><img src="../includes/graphics/big_loader.gif" /></td></tr>';
			$.post('ajax/newsphoto.php', {fileName: textPhoto.name, data: reader.result, authTime: '<?php echo $authTime; ?>', authToken: '<?php echo $authToken; ?>', type: 'text', photograph: photograph, subtitle: subtitle}, function(data) {
				var result = eval('(' + data + ')');
				if (result.type=="error") {
					if (result.code=="1") {
						document.getElementById('text_photo_form').innerHTML = '<tr><td colspan="2" class="center"><br /><b>Die hochgeladene Datei ist kein Bild.</b></td></tr>';	
					}
					if (result.code=="2") {
						document.getElementById('text_photo_form').innerHTML = '<tr><td colspan="2" class="center"><br /><b>Die hochgeladene Datei muss die Mindestma&szlig;e 640 Pixel Breite und 320 Pixel H&ouml;he haben. Au&szlig;erdem muss die Breite mindestens halb so lang und h&ouml;chstens vier Mal so lang, wie die H&ouml;he sein.</b></td></tr>';
					}
					if (result.code=="3") {
						document.getElementById('text_photo_form').innerHTML = '<tr><td colspan="2" class="center"><br /><b>Leider ist ein interner Fehler aufgetreten. Versuche es nochmal. Sollte der Fehler &ouml;fter auftreten, kontaktiere einen Administrator.</b></td></tr>';			
					}
					document.getElementById('text_photo_form').innerHTML += '<tr><td colspan="2" class="center"><h3>Text Foto</h3></td></tr>';
					document.getElementById('text_photo_form').innerHTML += '<tr><td>Foto: </td><td><input id="text_photo" type="file" onchange="JavaScript:handleText(this.files)" /></td></tr>';
					document.getElementById('text_photo_form').innerHTML += '<tr><td>Fotograf: </td><td><input type="text" id="text_photo_photograph" value="'+photograph+'" /></td></tr>';
					document.getElementById('text_photo_form').innerHTML += '<tr><td>Untertitel: </td><td><input type="text" class="subtitle" id="text_photo_text" value="'+subtitle+'" /></td></tr>';
					document.getElementById('text_photo_form').innerHTML += '<tr><td colspan="2" class="center"><a onmouseover="this.style.cursor = \'pointer\'" onclick="JavaScript:uploadText()"><b>Hochladen</b></a></td></tr>';
				}
				if (result.type=="success") {
					document.getElementById('text_photo_form').innerHTML = '<tr><td class="center"><br />';
					document.getElementById('text_photo_form').innerHTML += '<input type="hidden" id="pic2X" name="pic2X" /><input type="hidden" id="pic2Y" name="pic2Y" />';
					document.getElementById('text_photo_form').innerHTML += '<input type="hidden" id="pic2W" name="pic2W" /><input type="hidden" id="pic2H" name="pic2H" />';
					document.getElementById('text_photo_form').innerHTML += '<input type="hidden" name="picture2" value="'+result.id+'" />';
					document.getElementById('text_photo_form').innerHTML += '<img src="../news/'+result.file+'" id="crop" /><br />Foto: '+photograph+'<br />Untertitel: '+subtitle+'</td></tr>';
					$('#crop').Jcrop({
						minSize: [640, 320],
						aspectRatio: 2,
						onSelect: updateCoords,
						setSelect: [0, 0, 640, 320],
						allowSelect: false
					});
				}
			});
		}
		reader.readAsDataURL(textPhoto);
	}

	function updateCoords(c) {

		$('#pic2X').val(c.x);
		$('#pic2Y').val(c.y);
		$('#pic2W').val(c.w);
		$('#pic2H').val(c.h);

	}
</script>
<?php if (!$new): ?>
<?php if ($failed): ?>
<div class="caution">Achtung, mindestens ein hochgeladenes Foto hat nicht die erforderlichen Ma&szlig;e. Der Text wurde nicht hochgeladen!</div>
<?php endif; ?>
<?php if (!$failed): ?>
<div class="success">Der Text wurde erfolgreich eingestellt!</div>
<?php endif; ?>
<?php endif; ?>
<form method="post" action="index.php?var=module&amp;module=news" enctype="multipart/form-data">
	<table class="newstable">
		<tr>
			<td>Dachzeile: </td>
			<td><input type="text" name="headline" class="newstitle" value="<?php echo $headline; ?>" /></td>
		</tr>
		<tr>
			<td>Titel: </td>
			<td><input type="text" name="title" class="newstitle" value="<?php echo $title; ?>" /></td>
		</tr>
		<tr>
			<td>Kategorie: </td>
			<td>
				<select name="category">
					<?php foreach($locations as $location): ?>
					<option value="<?php echo $location['location']; ?>" <?php if ($location['location']==$category): ?>selected<?php endif; ?>>
						<?php echo $location['name']; ?>
					</option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<tr>
			<td>Stadt: </td>
			<td><input type="text" name="city" value="<?php echo $city; ?>" /></td>
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
			<td class="top">Teaser: </td>
			<td><textarea name="teaser"><?php echo $teaser; ?></textarea></td>
		</tr>
		<tr>
			<td class="top">Text: </td>
			<td><textarea name="text" rows="30"><?php echo $text; ?></textarea></td>
		</tr>
		<tr>
			<td colspan="2">
				<table class="news_photo" id="teaser_photo_form">
					<tr><td colspan="2" class="center"><h3>Teaser Foto</h3></td></tr>
					<tr><td>Foto: </td><td><input id="teaser_photo" type="file" onchange="JavaScript:handleTeaser(this.files)" /></td></tr>
					<tr><td>Fotograf: </td><td><input type="text" id="teaser_photo_photograph" /></td></tr>
					<tr><td colspan="2" class="center"><a onmouseover="this.style.cursor = 'pointer'" onclick="JavaScript:uploadTeaser()"><b>Hochladen</b></a></td></tr>
				</table>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<table class="news_photo" id="text_photo_form">
					<tr><td colspan="2" class="center"><h3>Text Foto</h3></td></tr>
					<tr><td>Foto: </td><td><input id="text_photo" type="file" onchange="JavaScript:handleText(this.files)" /></td></tr>
					<tr><td>Fotograf: </td><td><input type="text" id="text_photo_photograph" /></td></tr>
					<tr><td>Untertitel: </td><td><input type="text" class="subtitle" id="text_photo_text" /></td></tr>
					<tr><td colspan="2" class="center"><a onmouseover="this.style.cursor = 'pointer'" onclick="JavaScript:uploadText()"><b>Hochladen</b></a><br /><br /></td></tr>
				</table>
			</td>
		</tr>
		<tr>
			<td><b>Tags:</b></td>
			<td>Einzelne Tags k&ouml;nnen voneinander mit einem Semikolon getrennt werden. Nach dem letzten Tag darf kein Semikolon angegeben werden.</td>
		</tr>
		<?php foreach($moduleTags as $moduleTag): ?>
		<tr>
			<td><?php echo $moduleTag['name']; ?>: </td>
			<td><input type="text" name="<?php echo $moduleTag['type']; ?>" class="newstitle" value="<?php echo $moduleTag['tags']; ?>" /></td>
		</tr>
		<?php endforeach; ?>
		<tr>
			<td colspan="2">
				<input type="hidden" value="<?php echo $authTime; ?>" name="authTime" />
				<input type="hidden" value="<?php echo $authToken; ?>" name="authToken" />
				<button type="submit" name="action" value="send"> Absenden </button>
				<button type="reset"> L&ouml;schen </button>
			</td>
		</tr>
	</table>
</form>