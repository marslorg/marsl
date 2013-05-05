<?php
include_once (dirname(__FILE__)."/../../includes/errorHandler.php");
?>
<table class="navtable">
	<tr class="standardfont">
		<td class="navhead">Kategorien</td>
		<td class="navhead">Position</td>
		<td class="navhead">Rollen</td>
		<td class="navhead"> </td>
	</tr>
	<?php foreach($categories as $category): ?>
	<tr class="standardfont">
		<form method="post" action="index.php?var=module&amp;module=navigation&amp;action=change&amp;type=0">
			<td><input type="text" name="name" value="<?php echo $category['name']; ?>" /></td>
			<td><input type="text" name="pos" value="<?php echo $category['pos']; ?>" /></td>
			<td><?php if ($category['role']): ?><a href="index.php?var=module&amp;module=navigation&amp;action=role&amp;id=<?php echo $category['id']; ?>">Rechteverwaltung</a><?php endif; ?></td>
			<td>
				<input type="hidden" value="<?php echo $authTime; ?>" name="authTime" />
				<input type="hidden" value="<?php echo $authToken; ?>" name="authToken" />
				<button type="submit" name="id" value="<?php echo $category['id']; ?>"> &Auml;ndern </button>
				<?php if ($category['role']): ?>
				<br />
				<a href="index.php?var=module&amp;module=navigation&amp;action=del&amp;id=<?php echo $category['id']; ?>&amp;time=<?php echo $authTime; ?>&amp;token=<?php echo $authToken; ?>">Kategorie l&ouml;schen</a>
				<?php endif; ?>
			</td>
		</form>
	</tr>
	<?php endforeach; ?>
</table>
<table class="navtable">
	<tr class="standardfont">
		<td class="navhead">Inhaltskategorien</td>
		<td class="navhead">Position</td>
		<td class="navhead">Rollen</td>
		<td class="navhead"> </td>
	</tr>
	<?php foreach($catcontents as $category): ?>
	<tr class="standardfont">
		<form method="post" action="index.php?var=module&amp;module=navigation&amp;action=change&amp;type=1">
			<td><input type="text" name="name" value="<?php echo $category['name']; ?>" /></td>
			<td><input type="text" name="pos" value="<?php echo $category['pos']; ?>" /></td>
			<td><?php if ($category['role']): ?><a href="index.php?var=module&amp;module=navigation&amp;action=role&amp;id=<?php echo $category['id']; ?>">Rechteverwaltung</a><?php endif; ?></td>
			<td>
				<input type="hidden" value="<?php echo $authTime; ?>" name="authTime" />
				<input type="hidden" value="<?php echo $authToken; ?>" name="authToken" />
				<button type="submit" name="id" value="<?php echo $category['id']; ?>"> &Auml;ndern </button>
				<?php if ($category['role']): ?>
				<br />
				<a href="index.php?var=module&amp;module=navigation&amp;action=del&amp;id=<?php echo $category['id']; ?>&amp;time=<?php echo $authTime; ?>&amp;token=<?php echo $authToken; ?>">Kategorie l&ouml;schen</a>
				<?php endif; ?>
			</td>
		</form>
	</tr>
	<?php endforeach; ?>
</table>
<table class="navtable">
	<tr class="standardfont">
		<td class="navhead">Links</td>
		<td class="navhead">Position</td>
		<td class="navhead">Kategoriezugeh&ouml;rigkeit</td>
		<td class="navhead">Rollen</td>
		<td class="navhead"> </td>
	</tr>
	<?php foreach($links as $link): ?>
	<tr class="standardfont">
		<form method="post" action="index.php?var=module&amp;module=navigation&amp;action=change&amp;type=2">
			<td><input type="text" name="name" value="<?php echo $link['name']; ?>" /></td>
			<td><input type="text" name="pos" value="<?php echo $link['pos']; ?>" /></td>
			<td>
				<select name="catbelong">
					<?php foreach($categories as $category): ?>
					<option value="<?php echo $category['id']; ?>" <?php if($category['id']==$link['category']): ?>selected<?php endif; ?>>
						<?php echo $category['name']; ?>
					</option>
					<?php endforeach; ?>
					<?php foreach($catcontents as $category): ?>
					<option value="<?php echo $category['id']; ?>" <?php if($category['id']==$link['category']): ?>selected<?php endif; ?>>
						<?php echo $category['name']; ?>
					</option>
					<?php endforeach; ?>
				</select>
			</td>
			<td><?php if ($link['role']): ?><a href="index.php?var=module&amp;module=navigation&amp;action=role&amp;id=<?php echo $link['id']; ?>">Rechteverwaltung</a><?php endif; ?></td>
			<td>
				<input type="hidden" value="<?php echo $authTime; ?>" name="authTime" />
				<input type="hidden" value="<?php echo $authToken; ?>" name="authToken" />
				<button type="submit" name="id" value="<?php echo $link['id']; ?>"> &Auml;ndern </button>
				<?php if ($link['role']): ?>
				<br />
				<a href="index.php?var=module&amp;module=navigation&amp;action=del&amp;id=<?php echo $link['id']; ?>&amp;time=<?php echo $authTime; ?>&amp;token=<?php echo $authToken; ?>">Link l&ouml;schen</a>
				<?php endif; ?>
			</td>
		</form>
	</tr>
	<?php endforeach; ?>
</table>
<table class="navtable">
	<tr class="standardfont">
		<td><a href="index.php?var=module&amp;module=navigation&amp;action=addcat&amp;time=<?php echo $authTime; ?>&amp;token=<?php echo $authToken; ?>">Kategorie hinzuf&uuml;gen</a></td>
		<td><a href="index.php?var=module&amp;module=navigation&amp;action=addcatcontent&amp;time=<?php echo $authTime; ?>&amp;token=<?php echo $authToken; ?>">Inhaltskategorie hinzuf&uuml;gen</a></td>
		<td><a href="index.php?var=module&amp;module=navigation&amp;action=addlink&amp;time=<?php echo $authTime; ?>&amp;token=<?php echo $authToken; ?>">Link hinzuf&uuml;gen</a></td>
	</tr>
</table>