<?php
include_once (dirname(__FILE__)."/../../includes/errorHandler.php");
?>
<table class="boardtable">
	<tr class="standardfont">
		<td class="boardhead">Kategorien</td>
		<td class="boardhead">Position</td>
		<td class="boardhead">Zugeh&ouml;rigkeit</td>
		<td class="boardhead">Rollen</td>
		<td class="boardhead"> </td>
	</tr>
	<?php foreach ($categories as $category): ?>
	<tr class="standardfont">
		<form method="post" action="index.php?var=module&amp;module=board&amp;action=change&amp;type=0">
			<td><input type="text" name="title" value="<?php echo $category['title']; ?>" /></td>
			<td><input type="text" name="pos" value="<?php echo $category['pos']; ?>" /></td>
			<td>
				<select name="location">
					<?php foreach ($locations as $location): ?>
					<option value="<?php echo $location['id']; ?>" <?php if ($location['id']==$category['location']): ?>selected<?php endif; ?>>
						<?php echo $location['name']; ?>
					</option>
					<?php endforeach; ?>
				</select>
			</td>
			<td>
				<?php if ($category['boardAdmin']): ?>
				<a href="index.php?var=module&amp;module=board&amp;page=role&amp;board=<?php echo $category['board']; ?>">Rechteverwaltung</a>
				<?php endif; ?>
			</td>
			<td>
				<input type="hidden" value="<?php echo $authTime; ?>" name="authTime" />
				<input type="hidden" value="<?php echo $authToken; ?>" name="authToken" />
				<button type="submit" name="board" value="<?php echo $category['board']; ?>"> &Auml;ndern </button>
				<?php if ($category['boardAdmin']): ?>
				<br />
				<a href="index.php?var=module&amp;module=board&amp;action=del&amp;board=<?php echo $category['board']; ?>&amp;time=<?php echo $authTime; ?>&amp;token=<?php echo $authToken; ?>">Kategorie l&ouml;schen</a>
				<?php endif; ?>
			</td>
		</form>
	</tr>
	<?php endforeach; ?>
</table>

<table class="boardtable">
	<tr class="standardfont">
		<td class="boardhead">Foren</td>
		<td class="boardhead">Position</td>
		<td class="boardhead">Zugeh&ouml;rigkeit</td>
		<td class="boardhead">Beschreibung</td>
		<td class="boardhead">Rollen</td>
		<td class="boardhead">Moderatoren</td>
		<td class="boardhead"> </td>
	</tr>
	<?php foreach ($boards as $board): ?>
	<tr class="standardfont">
		<form method="post" action="index.php?var=module&amp;module=board&amp;action=change&amp;type=0">
			<td><input type="text" name="title" value="<?php echo $board['title']; ?>" /></td>
			<td><input type="text" name="pos" value="<?php echo $board['pos']; ?>" /></td>
			<td>
				<select name="location">
					<?php foreach ($categories as $category): ?>
					<option value="<?php echo $category['board']; ?>" <?php if ($category['board']==$board['location']): ?>selected<?php endif; ?>>
						<?php echo $category['title']; ?>
					</option>
					<?php endforeach; ?>
				</select>
			</td>
			<td>
				<a href="index.php?var=module&amp;module=board&amp;page=description&amp;board=<?php echo $board['board']; ?>">Beschreibung</a>
			</td>
			<td>
				<?php if ($board['boardAdmin']): ?>
				<a href="index.php?var=module&amp;module=board&amp;page=role&amp;board=<?php echo $board['board']; ?>">Rechteverwaltung</a>
				<?php endif; ?>
			</td>
			<td>
				<a href="index.php?var=module&amp;module=board&amp;page=operator&amp;board=<?php echo $board['board']; ?>">Moderatoren</a>
			</td>
			<td>
				<input type="hidden" value="<?php echo $authTime; ?>" name="authTime" />
				<input type="hidden" value="<?php echo $authToken; ?>" name="authToken" />
				<button type="submit" name="board" value="<?php echo $board['board']; ?>"> &Auml;ndern </button>
				<?php if ($board['boardAdmin']): ?>
				<br />
				<a href="index.php?var=module&amp;module=board&amp;action=del&amp;board=<?php echo $board['board']; ?>&amp;time=<?php echo $authTime; ?>&amp;token=<?php echo $authToken; ?>">Forum l&ouml;schen</a>
				<?php endif; ?>
			</td>
		</form>
	</tr>
	<?php endforeach; ?>
</table>
<table class="boardtable">
	<tr class="standardfont">
		<td><a href="index.php?var=module&amp;module=board&amp;action=addcat&amp;time=<?php echo $authTime; ?>&amp;token=<?php echo $authToken; ?>">Kategorie hinzuf&uuml;gen</a></td>
		<td><a href="index.php?var=module&amp;module=board&amp;action=addboard&amp;time=<?php echo $authTime; ?>&amp;token=<?php echo $authToken; ?>">Forum hinzuf&uuml;gen</a></td>
	</tr>
</table>