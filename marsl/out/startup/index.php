<?php
class Main {

    public function display() {
        http_response_code(200);
        echo "HEALTHY";
	}

}

$display = new Main();
$display->display();
?>