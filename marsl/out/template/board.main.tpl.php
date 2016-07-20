<?php
include_once (dirname(__FILE__)."/../includes/errorHandler.php");
?>
<table class="boardtable">
	<tr>
		<td class="boardhead">Forum</td>
		<td class="boardhead">Beitr&auml;ge</td>
		<td class="boardhead">Themen</td>
		<td class="boardhead">Letzter Beitrag</td>
		<td class="boardhead">Moderatoren</td>
	</tr>
	<?php foreach ($categories as $category): ?>
	<tr class="standardfont">
		<td class="category" colspan="5"><?php echo $category['title']; ?></td>
	</tr>
	<?php foreach ($category['boards'] as $board):?>
	<tr>
		<td>
			<a href="index.php?id=<?php echo $location; ?>&amp;action=threads&amp;board=<?php echo $board['board']; ?>"><?php echo $board['title']; ?></a>
			<br />
			<span class="smallfont"><?php echo $board['description']; ?></span>
		</td>
		<td class="center"><?php echo $board['postcount']; ?></td>
		<td class="center"><?php echo $board['threadcount']; ?></td>
		<td>
			<span class="smallfont">
				<a href="index.php?id=<?php echo $location; ?>&amp;action=posts&amp;thread=<?php echo $board['thread']; ?>&amp;page=<?php echo $board['page']; ?>#<?php echo $board['post']; ?>"><?php echo $board['threadTitle']; ?></a>,
				<?php echo $board['date']; ?>,
				<?php echo $board['nickname']; ?>
			</span>
		</td>
		<td>
			<?php $i=0; ?>
			<?php foreach ($board['operators'] as $operator): ?><?php if ($i>0): ?>, <?php endif; ?><?php echo $operator['nickname']; ?><?php $i++; ?><?php endforeach; ?>
		</td>
	</tr>
	<?php endforeach; ?>
	<?php endforeach; ?>
</table>