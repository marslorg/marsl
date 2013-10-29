<?php
include_once (dirname(__FILE__)."/../includes/errorHandler.php");
?>
<h4 class="center">
	<?php for ($i = 0; $i<$pages; $i++): $j = $i+1; ?>
	<?php if ($j!=$page): ?>
	<a href="index.php?id=<?php echo $location; ?>&amp;action=posts&amp;thread=<?php echo $threadID; ?>&amp;page=<?php echo $j; ?>"><?php endif; ?><?php echo $j; ?><?php if ($j!=$page): ?></a>
	<?php endif; ?>
	<?php endfor; ?>
	<br />
	<br />
	<?php if($isAuthor): ?>
	<a href="index.php?id=<?php echo $location; ?>&amp;action=answer&amp;thread=<?php echo $threadID; ?>">
		Neue Antwort
	</a>
	<?php endif; ?>
</h4>
<?php if($isOperator||$isGlobalAdmin||$isAdmin): ?>
<form class="right" method="get" action="index.php">
	<input type="hidden" name="id" value="<?php echo $location; ?>" />
	<input type="hidden" name="thread" value="<?php echo $threadID; ?>">
	<select name="action">
		<option value="posts" selected> </option>
		<?php if($isGlobalAdmin): ?>
		<option value="globalfix">Als globale Ank&uuml;ndigung</option>
		<option value="defix">Ank&uuml;ndigung aufheben</option>
		<?php endif; ?>
		<option value="localfix">Als Ank&uuml;ndigung</option>
		<option value="posts">-----------------------</option>
		<option value="title">Titel &auml;ndern</option>
		<option value="move">Thema verschieben</option>
		<option value="close">Thema schlie&szlig;en</option>
		<option value="open">Thema &ouml;ffnen</option>
		<option value="posts">-----------------------</option>
		<option value="delete">Thema l&ouml;schen</option>
	</select>
	<input type="hidden" name="time" value="<?php echo $authTime; ?>" />
	<input type="hidden" name="token" value="<?php echo $authToken; ?>" />
	<button type="submit"> Los </button>
</form>
<?php endif; ?>
<br />
<table class="boardtable">
	<tr>
		<td class="boardhead">Autor</td>
		<td class="boardhead">Beitrag</td>
	</tr>
	<?php foreach($posts as $post): ?>
	<tr>
		<td class="boardauthor"><a name="<?php echo $post['post']; ?>"></a><b><?php echo $post['authorNickname']; ?></b></td>
		<td class="boardcontent">
			<?php echo $post['content']; ?>
			<?php $i = 0; ?>
			<?php foreach($post['files'] as $file): ?>
			<?php if ($i==0): ?>
			<br /><br /><span class="smallfont">Anh&auml;nge:</span>
			<?php $i++; ?>
			<?php endif; ?>
			<br />
			<span class="smallfont">
				<a href="file.php?file=<?php echo $file['file']; ?>&amp;scope=board">
					<?php echo $file['filename']; ?>
				</a>
			</span>
			<?php endforeach; ?>
			<?php if ($post['operator']>0): ?>
			<br /><br /><span class="smallfont">zuletzt ge&auml;ndert von <?php echo $post['operatorNickname']; ?> <?php echo $post['lastedit']; ?></span>
			<?php endif; ?>
		</td>
	</tr>
	<tr>
		<td class="boardauthor">
			<span class="smallfont"><?php echo $post['date']; ?></span>
		</td>
		<td class="boardcontent">
			<?php if($isAuthor): ?><a href="index.php?id=<?php echo $location; ?>&amp;action=answer&amp;thread=<?php echo $threadID; ?>&amp;quote=<?php echo $post['post']; ?>&amp;page=<?php echo $page; ?>">Zitieren</a><?php endif; ?>
			<?php if($post['editable']): ?><a href="index.php?id=<?php echo $location; ?>&amp;action=edit&amp;post=<?php echo $post['post']; ?>&amp;page=<?php echo $page; ?>">Editieren</a><?php endif; ?>
			<?php if ($isOperator): ?><a href="index.php?id=<?php echo $location; ?>&amp;action=posts&amp;do=del&amp;post=<?php echo $post['post']; ?>&amp;thread=<?php echo $threadID; ?>&amp;time=<?php echo $authTime; ?>&amp;token=<?php echo $authToken; ?>" onclick="return confirm('M&ouml;chtest du den Eintrag wirklich l&ouml;schen?')">L&ouml;schen</a><?php endif; ?>
		</td>
	</tr>
	<?php endforeach; ?>
</table>
<br />
<?php if($isOperator||$isGlobalAdmin||$isAdmin): ?>
<form class="right" method="get" action="index.php">
	<input type="hidden" name="id" value="<?php echo $location; ?>" />
	<input type="hidden" name="thread" value="<?php echo $threadID; ?> ">
	<select name="action">
		<option value="posts" selected> </option>
		<?php if($isGlobalAdmin): ?>
		<option value="globalfix">Als globale Ank&uuml;ndigung</option>
		<option value="defix">Ank&uuml;ndigung aufheben</option>
		<?php endif; ?>
		<option value="localfix">Als Ank&uuml;ndigung</option>
		<option value="posts">-----------------------</option>
		<option value="title">Titel &auml;ndern</option>
		<option value="move">Thema verschieben</option>
		<option value="close">Thema schlie&szlig;en</option>
		<option value="open">Thema &ouml;ffnen</option>
		<option value="posts">-----------------------</option>
		<option value="delete">Thema l&ouml;schen</option>
	</select>
	<input type="hidden" name="time" value="<?php echo $authTime; ?>" />
	<input type="hidden" name="token" value="<?php echo $authToken; ?>" />
	<button type="submit"> Los </button>
</form>
<?php endif; ?>
<h4 class="center">
	<?php if($isAuthor): ?>
	<a href="index.php?id=<?php echo $location; ?>&amp;action=answer&amp;thread=<?php echo $threadID; ?>">
		Neue Antwort
	</a>
	<?php endif; ?>
	<br />
	<br />
	<?php for ($i = 0; $i<$pages; $i++): $j = $i+1; ?>
	<?php if ($j!=$page): ?>
	<a href="index.php?id=<?php echo $location; ?>&amp;action=posts&amp;thread=<?php echo $threadID; ?>&amp;page=<?php echo $j; ?>"><?php endif; ?><?php echo $j; ?><?php if ($j!=$page): ?></a>
	<?php endif; ?>
	<?php endfor; ?>
</h4>