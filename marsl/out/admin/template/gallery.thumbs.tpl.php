<?php
include_once (dirname(__FILE__)."/../../includes/errorHandler.php");
?>
<div class="gallery">
	<form method="post" action="index.php?var=module&amp;module=gallery&amp;action=details&amp;id=<?php echo $album; ?>">
		<table class="albumtable">
			<tr>
				<td colspan="3"><center><a href="index.php?var=module&amp;module=gallery&amp;action=add&amp;id=<?php echo $album; ?>">Fotos hinzuf&uuml;gen</a></center></td>
			</tr>
			<tr>
				<td colspan="3">
					<center>
						<input type="hidden" name="authTime" value="<?php echo $authTime; ?>" />
						<input type="hidden" name="authToken" value="<?php echo $authToken; ?>" />
						<button type="submit" name="action" value="send"> &Auml;ndern </button>
						<button type="reset"> L&ouml;schen </button>
					</center>
				</td>
			</tr>
			<tr>
				<?php 
				$i = 0;
				foreach($pictures as $picture): $i++; ?>
				<td class="center">
					<a href="<?php echo $picture['picPath']; ?>" rel="lightboxgallery" title="<?php echo $picture['subtitle']; ?>"><img border="0" src="<?php echo $picture['thumbPath']; ?>"></a>
					<?php if ($picture['administrator']): ?>
					<br />L&ouml;schen: <input type="checkbox" name="<?php echo $picture['picture']; ?>_delete" value="1" />
					<?php if (!$picture['visible']): ?><br />Freischalten: <input type="checkbox" name="<?php echo $picture['picture']; ?>_submit" value="1" /><?php endif; ?>
					<br /><a onmouseover="this.style.cursor = 'pointer'" onclick="JavaScript:window.open('editpicture.php?id=<?php echo $picture['picture']; ?>','PopUp','width=800,height=600,menubar=no,toolbar=no,scrollbars=yes,status=no,resizable=no,location=no,hotkeys=yes')">Editieren</a>
					<?php endif; ?>
				</td>
				<?php if ($i==3): $i=0; ?>
				</tr>
				<tr>
				<?php endif; ?>
				<?php endforeach; ?>
			</tr>
			<tr>
				<td colspan="3">
					<center>
						<input type="hidden" name="authTime" value="<?php echo $authTime; ?>" />
						<input type="hidden" name="authToken" value="<?php echo $authToken; ?>" />
						<button type="submit" name="action" value="send"> &Auml;ndern </button>
						<button type="reset"> L&ouml;schen </button>
					</center>
				</td>
			</tr>
		</table>
	</form>
</div>