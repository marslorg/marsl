<?php
include_once (dirname(__FILE__)."/../includes/errorHandler.php");
?>
<form class="center" method="post" action="<?php echo $titleURI; ?>">
	<input type="text" name="title" value="<?php echo $title; ?>" style="width:75%" />
	<input type="hidden" name="authTime" value="<?php echo $authTime; ?>" />
	<input type="hidden" name="authToken" value="<?php echo $authToken; ?>" />
	<button type="submit" name="do" value="change"> Titel &auml;ndern </button>
	<button type="reset"> L&ouml;schen </button>
</form>
