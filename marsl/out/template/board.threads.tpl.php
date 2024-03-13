<?php
include_once (dirname(__FILE__)."/../includes/errorHandler.php");
?>
<?php if ($writeAllowed): ?>
<h4 class="center">
	<a href="<?php echo $newThreadURI; ?>">Neues Thema</a>
</h4>
<?php endif; ?>
<h4 class="center">
	<?php for ($i = 0; $i<$pages; $i++): $j = $i+1; ?>
	<?php if ($j!=$page): ?>
	<a href="<?php echo $threadsURI; ?><?php echo $this->getPageURIFormatted($j); ?>"><?php endif; ?><?php echo $j; ?><?php if ($j!=$page): ?></a>
	<?php endif; ?>
	<?php endfor; ?>
</h4>
<table class="boardtable">
	<tr>
		<td class="boardhead">Thema</td>
		<td class="boardhead">Antworten</td>
		<td class="boardhead">Aufrufe</td>
		<td class="boardhead">Autor</td>
		<td class="boardhead">Letzter Beitrag</td>
	</tr>
	<?php foreach ($globals as $thread): ?>
	<tr>
		<td>
			<b>Ank&uuml;ndigung:</b> </b><a href="<?php echo $thread['postsURI']; ?>"><?php echo $thread['title']; ?></a>
			<br />
			<span class="smallfont">Seiten:<?php for ($i = 0; $i<$thread['page']; $i++): $j = $i+1; ?> <a href="<?php echo $thread['postsURI']; ?><?php echo $this->getPageURIFormatted($j); ?>"><?php echo $j; ?></a><?php endfor; ?></span>
		</td>
		<td class="center"><?php echo $thread['postcount']; ?></td>
		<td class="center"><?php echo $thread['viewcount']; ?></td>
		<td class="center"><?php echo $thread['threadNickname']; ?></td>
		<td><span class="smallfont"><a href="<?php echo $thread['postsURI']; ?><?php echo $this->getPageURIFormatted($thread['page']); ?>#<?php echo $thread['post']; ?>"><?php echo $thread['date']; ?></a>, <?php echo $thread['postNickname']; ?></span></td>
	</tr>
	<?php endforeach; ?>
	<?php foreach ($fixeds as $thread): ?>
	<tr>
		<td>
			<b>Ank&uuml;ndigung:</b> </b><a href="<?php echo $thread['postsURI']; ?>"><?php echo $thread['title']; ?></a>
			<br />
			<span class="smallfont">Seiten:<?php for ($i = 0; $i<$thread['page']; $i++): $j = $i+1; ?> <a href="<?php echo $thread['postsURI']; ?><?php echo $this->getPageURIFormatted($j); ?>"><?php echo $j; ?></a><?php endfor; ?></span>
		</td>
		<td class="center"><?php echo $thread['postcount']; ?></td>
		<td class="center"><?php echo $thread['viewcount']; ?></td>
		<td class="center"><?php echo $thread['threadNickname']; ?></td>
		<td><span class="smallfont"><a href="<?php echo $thread['postsURI']; ?><?php echo $this->getPageURIFormatted($thread['page']); ?>#<?php echo $thread['post']; ?>"><?php echo $thread['date']; ?></a>, <?php echo $thread['postNickname']; ?></span></td>
	</tr>
	<?php endforeach; ?>
	<?php foreach ($threads as $thread): ?>
	<tr>
		<td>
			<?php if ($thread['type']=="closed"): ?><b>Geschlossen:</b> <?php endif; ?><a href="<?php echo $thread['postsURI']; ?>"><?php echo $thread['title']; ?></a>
			<br />
			<span class="smallfont">Seiten:<?php for ($i = 0; $i<$thread['page']; $i++): $j = $i+1; ?> <a href="<?php echo $thread['postsURI']; ?><?php echo $this->getPageURIFormatted($j); ?>"><?php echo $j; ?></a><?php endfor; ?></span>
		</td>
		<td class="center"><?php echo $thread['postcount']; ?></td>
		<td class="center"><?php echo $thread['viewcount']; ?></td>
		<td class="center"><?php echo $thread['threadNickname']; ?></td>
		<td><span class="smallfont"><a href="<?php echo $thread['postsURI']; ?><?php echo $this->getPageURIFormatted($thread['page']); ?>#<?php echo $thread['post']; ?>"><?php echo $thread['date']; ?></a>, <?php echo $thread['postNickname']; ?></span></td>
	</tr>
	<?php endforeach; ?>
</table>
<h4 class="center">
	<?php for ($i = 0; $i<$pages; $i++): $j = $i+1; ?>
	<?php if ($j!=$page): ?>
	<a href="<?php echo $threadsURI; ?><?php echo $this->getPageURIFormatted($j); ?>"><?php endif; ?><?php echo $j; ?><?php if ($j!=$page): ?></a>
	<?php endif; ?>
	<?php endfor; ?>
</h4>
<?php if ($writeAllowed): ?>
<h4 class="center">
	<a href="<?php echo $newThreadURI; ?>">Neues Thema</a>
</h4>
<?php endif; ?>