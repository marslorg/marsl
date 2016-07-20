<?php
include_once (dirname(__FILE__)."/../../includes/errorHandler.php");
?>
<h2>Modulrechte einstellen</h2>
<?php foreach($modules as $module): ?>
<a href="index.php?var=modulerights&amp;module=<?php echo $module['file']; ?>&amp;action=role">
	<?php echo $module['name']; ?>
</a>
<br />
<?php endforeach; ?>