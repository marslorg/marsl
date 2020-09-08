<?php
include_once (dirname(__FILE__)."/../../includes/errorHandler.php");
?>
<!DOCTYPE HTML>
<html>
	<head>
		<title>Admin System - <?php echo $title; ?></title>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<link rel="stylesheet" href="styles/style.css" type="text/css" />
		<link rel="stylesheet" href="styles/menu.css" type="text/css" />
		<link rel="stylesheet" href="styles/jquery.css" type="text/css" />
		<link rel="stylesheet" href="styles/jquery.Jcrop.min.css" type="text/css" />
		<link rel="stylesheet" href="../includes/jscripts/plupload/js/jquery.plupload.queue/css/jquery.plupload.queue.css" type="text/css" />
		<link rel="stylesheet" href="../includes/jscripts/slimbox/css/slimbox2.css" type="text/css" media="screen" />
		<script type="text/javascript" src="../includes/jscripts/jquery/jquery.js"></script>
		<script type="text/javascript" src="../includes/jscripts/jquery/jquery-ui.js"></script>
		<script type="text/javascript" src="../includes/jscripts/slimbox/js/slimbox2.js"></script>
		<script type="text/javascript" src="http://bp.yahooapis.com/2.4.21/browserplus-min.js"></script>
		<script type="text/javascript" src="../includes/jscripts/plupload/js/plupload.full.min.js"></script>
		<script type="text/javascript" src="../includes/jscripts/plupload/js/jquery.plupload.queue/jquery.plupload.queue.js"></script>
		<script type="text/javascript" src="../includes/jscripts/plupload/js/i18n/de.js"></script>
		<script type="text/javascript" src="../includes/jscripts/jcrop/jquery.Jcrop.min.js"></script>
	</head>
	<body>
		<div class="menu">
			<ul>
				<li>
					<a class="hide" href="#">Navigation</a>
					<ul>
						<?php $urlloader->adminNavi(); ?>
					</ul>
				</li>
				<li>
					<a class="hide" href="#">Module</a>
					<ul>
						<?php foreach ($modules as $module): ?>
						<?php if ($auth->moduleAdminAllowed($module['file'], $roleID)): ?>
						<li>
							<a href="index.php?var=module&amp;module=<?php echo $module['file']; ?>">
							<?php echo $module['name']; ?>
							</a>
						</li>
						<?php endif; ?>
						<?php endforeach; ?>
					</ul>
				</li>
				<li>
					<a class="hide" href="#">Benutzer</a>
					<ul>
						<li>
							<a href="index.php?var=register">Registrierung</a>
						</li>
						<li>
							<a href="index.php?var=role">Rollen</a>
						</li>
						<?php if ($headAdmin): ?>
						<li>
							<a href="index.php?var=standards">Standardrollen</a>
						</li>
						<?php endif; ?>
						<li>
							<a href="index.php?var=modulerights">Modulrechte</a>
						</li>
					</ul>
				</li>
				<?php if ($headAdmin): ?>
				<li>
					<a class="hide" href="index.php?var=tags&amp;search=%">Tags</a>
				</li>
				<li>
					<a class="hide" href="#">Newsletter</a>
				</li>
				<?php endif; ?>
				<?php if ($userdata): ?>
				<li>
					<a class="hide" href="index.php?var=module&amp;module=userdata&amp;action=details&amp;user=<?php echo $userID; ?>">Daten &auml;ndern</a>
				</li>
				<?php endif; ?>
				<li>
					<a class="hide" href="index.php?var=logout">Logout</a>
				</li>
			</ul>
		</div>
		<br />
		<div class="content">
			<?php $content->admin(); ?>
			<br /><br />Verbunden mit <?php echo $clusterServer; ?>
		</div>
	</body>
</html>