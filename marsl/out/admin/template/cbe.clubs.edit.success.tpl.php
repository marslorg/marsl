<?php
include_once (dirname(__FILE__)."/../../includes/errorHandler.php");
?>
<br /><br />
<div class="success">Der Eintrag wurde erfolgreich ge&auml;ndert! Du wirst gleich zur&uuml;ck geleitet. Wenn es nicht automatisch weiter geht, klicke <a href="index.php?var=module&amp;module=cbe&amp;action=editclub&amp;club=<?php echo $id; ?>">hier</a>.</div><script>top.location.href='index.php?var=module&module=cbe&action=editclub&club=<?php echo $id; ?>'</script></div>