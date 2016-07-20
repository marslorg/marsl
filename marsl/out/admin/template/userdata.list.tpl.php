<?php
include_once (dirname(__FILE__)."/../../includes/errorHandler.php");
?>
<table class="userlist">
	<tr>
		<th>ID</th>
		<th>Nickname</th>
		<th>Rolle</th>
		<th>E-Mail</th>
		<th>Vorname</th>
		<th>Nachname</th>
		<th>K&uuml;rzel</th>
		<th>Anzahl Beitr&auml;ge</th>
		<th>Registrierungsdatum</th>
	</tr>
	<?php foreach($userdata as $su): ?>
	<tr>
		<td><?php echo $su['user']; ?></td>
		<td>
			<?php if($su['isMaster']): ?><a href="index.php?var=module&amp;module=userdata&amp;action=details&amp;user=<?php echo $su['user']; ?>"><?php endif; ?>
			<?php echo $su['nickname']; ?>
			<?php if($su['isMaster']): ?></a><?php endif; ?>
		</td>
		<td><?php echo $su['rolename']; ?></td>
		<td>
			<a href="mailto:<?php echo $su['email']; ?>"><?php echo $su['email']; ?></a>
		</td>
		<td><?php echo $su['prename']; ?></td>
		<td><?php echo $su['name']; ?></td>
		<td><?php echo $su['acronym']; ?></td>
		<td><?php echo $su['postcount']; ?></td>
		<td><?php echo $su['regdate']; ?></td>
	</tr>
	<?php endforeach; ?>
</table>