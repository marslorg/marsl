<?php
include_once(dirname(__FILE__)."/errorHandler.php");
include_once(dirname(__FILE__)."/config.inc.php");
include_once(dirname(__FILE__)."/recaptcha/recaptchalib.php");

class Recaptcha {
	
	public function getRecaptcha() {
		$config = new Configuration();
		return recaptcha_get_html($config->getPublicRecaptcha());
	}
	
	public function checkRecaptcha() {
		$config = new Configuration();
		$resp = recaptcha_check_answer($config->getPrivateRecaptcha(), $_SERVER['REMOTE_ADDR'], $_POST['recaptcha_challenge_field'], $_POST['recaptcha_response_field']);
		return $resp->is_valid;
	}
	
}
?>