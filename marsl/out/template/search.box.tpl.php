<?php
include_once (dirname(__FILE__)."/../includes/errorHandler.php");
?>
<form method="get" action="index.php">
	<div class="search">
		<label for="mod-search-searchword">Suche...</label>
		<input type="text" name="search" id="mod-search-searchword" class="inputbox" size="20" value="Suche..." onblur="if (this.value=='') this.value='Suche...';" onfocus="if (this.value=='Suche...') this.value='';" />
		<input type="hidden" name="scope" value="news_all" />
	</div>
</form>