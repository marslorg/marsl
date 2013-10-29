<?php
include_once (dirname(__FILE__)."/../../includes/errorHandler.php");
?>
<div class="newsmenu">
	<ul>
		<li>
			<a class="hide" href="index.php?var=module&amp;module=gallery">Hochladen</a>
		</li>
		<?php if ($moduleExtended): ?>
		<li>
			<a class="hide" href="index.php?var=module&amp;module=gallery&amp;action=ftp">FTP-Galerie</a>
		</li>
		<li>
			<a class="hide" href="index.php?var=module&amp;module=gallery&amp;action=queue">Freischalten</a>
		</li>
		<?php endif; ?>
		<li>
			<a class="hide" href="index.php?var=module&amp;module=gallery&amp;action=albums">Galerien</a>
		</li>
	</ul>
</div>
<br /><br />