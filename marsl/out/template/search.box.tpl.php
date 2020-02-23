<div class="box">
	<div class="box-title">Suche</div>
	<div class="box-content">
		<form method="get" action="index.php">
			<input type="text" name="search" /><br />
			in <select name="scope">
				<?php foreach ($searchList as $scope): ?>
				<option value="<?php echo $scope['class']; ?>_<?php echo $scope['type']; ?>"><?php echo $scope['text']; ?></option>
				<?php endforeach; ?>
			</select>
			<br />
			<button type="submit"> Suchen </button>
		</form>
	</div>
</div>