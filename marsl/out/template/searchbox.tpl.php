<?php
include_once (dirname(__FILE__)."/../includes/errorHandler.php");
?>
<form method="get" action="index.php" class="searchbox">
	<input type="text" name="search" />
	in <select name="scope">
		<?php foreach ($searchList as $scope): ?>
		<option value="<?php echo $scope['class']; ?>_<?php echo $scope['type']; ?>"><?php echo $scope['text']; ?></option>
		<?php endforeach; ?>
	</select>
	<button type="submit"> Suchen </button>
</form>