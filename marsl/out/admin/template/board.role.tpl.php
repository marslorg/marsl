<?php
include_once (dirname(__FILE__)."/../../includes/errorHandler.php");
?>
<h2><?php echo $name; ?></h2>
<h3><a href="index.php?var=module&amp;module=board">zur&uuml;ck</a></h3>
<table class="boardtable">
	<tr class="standardfont">
		<td class="boardhead">Rolle</td>
		<td class="boardhead">read</td>
		<td class="boardhead">write</td>
		<td class="boardhead">extended</td>
		<td class="boardhead">admin</td>
	</tr>
	<form method="post" action="index.php?var=module&amp;module=board&amp;page=role&amp;board=<?php echo $board; ?>">
		<?php foreach($rights as $right): ?>
		<tr>
			<td class="boardhead"><?php echo $right['name']; ?></td>
			<td><input type="checkbox" name="<?php echo $right['role']; ?>_read" value="1" <?php if ($right['read']): ?>checked<?php endif; ?> /></td>
			<td><input type="checkbox" name="<?php echo $right['role']; ?>_write" value="1" <?php if ($right['write']): ?>checked<?php endif; ?> /></td>
			<td><input type="checkbox" name="<?php echo $right['role']; ?>_extended" value="1" <?php if ($right['extended']): ?>checked<?php endif; ?> /></td>
			<td><input type="checkbox" name="<?php echo $right['role']; ?>_admin" value="1" <?php if ($right['admin']): ?>checked<?php endif; ?> /></td>
		</tr>
		<?php endforeach; ?>
		<tr>
			<td colspan="5" class="boardhead">
				<input type="hidden" value="<?php echo $authTime; ?>" name="authTime" />
				<input type="hidden" value="<?php echo $authToken; ?>" name="authToken" />
				<button type="submit" name="change"> &Auml;ndern </button>
				<button type="reset"> L&ouml;schen </button>
			</td>
		</tr>
	</form>
</table>