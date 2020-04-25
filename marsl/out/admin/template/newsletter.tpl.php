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