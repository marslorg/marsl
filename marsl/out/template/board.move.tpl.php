<?php
include_once (dirname(__FILE__)."/../includes/errorHandler.php");
?>
<h2 class="center">Thema verschieben: <?php echo $title; ?></h2>
<form class="center" method="post" action="index.php?id=<?php echo $location; ?>&amp;action=move&amp;thread=<?php echo $threadID; ?>">
	<select name="destination">
		<?php foreach($boards as $destination): ?>
		<option value="<?php echo $destination['board']; ?>" <?php if ($destination['board']==$boardID): ?>selected<?php endif; ?>>
			<?php echo $destination['title']; ?>
		</option>
		<?php endforeach; ?>
	</select>
	<input type="hidden" name="authTime" value="<?php echo $authTime; ?>" />
	<input type="hidden" name="authToken" value="<?php echo $authToken; ?>" />
	<button type="submit" name="do" value="move"> Thema verschieben </button>
	<button type="reset"> L&ouml;schen </button>
</form>