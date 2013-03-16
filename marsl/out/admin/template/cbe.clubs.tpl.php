<?php
include_once (dirname(__FILE__)."/../../includes/errorHandler.php");
?>
<br />
<?php foreach ($clubs as $club): ?>
<br />
<a href="index.php?var=module&amp;module=cbe&amp;action=editclub&amp;club=<?php echo $club['id']; ?>">
	<?php echo $club['tag']; ?>
</a>
<?php endforeach; ?>