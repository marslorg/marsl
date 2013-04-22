<?php
include_once (dirname(__FILE__)."/../../includes/errorHandler.php");
?>
<h2><?php echo $name; ?></h2>
<h3><a href="index.php?var=module&amp;module=board">zur&uuml;ck</a></h3>
<form method="post" action="index.php?var=module&amp;module=board&amp;page=operator&amp;board=<?php echo $board; ?>">
	<select name="operator">
		<?php foreach($operators as $operator): ?>
		<option value="<?php echo $operator['user']; ?>">
			<?php echo $operator['nickname']; ?>
		</option>
		<?php endforeach; ?>
	</select>
	<input type="hidden" value="<?php echo $authTime; ?>" name="authTime" />
	<input type="hidden" value="<?php echo $authToken; ?>" name="authToken" />
	<button type="submit" name="add" value="<?php echo $board['board']; ?>"> Moderator hinzuf&uuml;gen </button>
</form>
<br />
<table>
<?php foreach($boardOperators as $boardOperator): ?>
	<tr>
		<td><?php echo $boardOperator['nickname']; ?></td>
		<td><a href="index.php?var=module&amp;module=board&amp;page=operator&amp;action=delete&amp;board=<?php echo $board; ?>&amp;operator=<?php echo $boardOperator['user']; ?>&amp;time=<?php echo $authTime; ?>&amp;token=<?php echo $authToken; ?>" onclick="return confirm('M&ouml;chtest du den Eintrag wirklich l&ouml;schen?')">L&ouml;schen</a></td>
	</tr>
<?php endforeach; ?>
</table>