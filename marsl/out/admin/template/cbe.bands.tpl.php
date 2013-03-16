<?php
include_once (dirname(__FILE__)."/../../includes/errorHandler.php");
?>
<br />
<?php foreach ($bands as $band): ?>
<br />
<a href="index.php?var=module&amp;module=cbe&amp;action=editband&amp;band=<?php echo $band['id']; ?>">
	<?php echo $band['tag']; ?>
</a>
<?php endforeach; ?>