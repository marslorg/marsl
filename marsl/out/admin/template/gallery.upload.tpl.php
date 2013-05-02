<?php
include_once (dirname(__FILE__)."/../../includes/errorHandler.php");
?>
<script type="text/javascript">
// Convert divs to queue widgets when the DOM is ready
$(function() {
	$("#uploader").pluploadQueue({
		// General settings
		runtimes : 'silverlight,gears,flash,browserplus,html5',
		url : 'ajax/galleryuploader.php?dir=<?php echo $tmpDir; ?>',
		max_file_size : '30mb',
		chunk_size : '1mb',
		unique_names : true,

		// Resize images on clientside if we can
		resize : {width : 640, height : 640, quality : 100},

		// Specify what files to browse for
		filters : [
			{title : "Image files", extensions : "jpg,gif,png"},
		],

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
                    $('form')[0].submit();
                }
            });
                
            uploader.start();
        } else {
            alert('Du musst mindestens eine Datei hinzuf&uuml;gen.');
        }

        return false;
    });
});
</script>
<?php if($success): ?>
<div class="success">Die Galerie wurde erfolgreich eingestellt!</div>
<?php endif; ?>
<div id="uploader">
	Dein Browser unterst&uuml;tzt kein Flash, Silverlight, Gears, BrowserPlus oder HTML5.
</div>

<form method="post" action="index.php?var=module&amp;module=gallery&amp;step=2&amp;dir=<?php echo $tmpDir; ?>">
	<table class="galtable">
		<tr>
			<td>
				<button type="submit" name="action" value="new"> Weiter zu Schritt 2 </button>
			</td>
		</tr>
	</table>
</form>