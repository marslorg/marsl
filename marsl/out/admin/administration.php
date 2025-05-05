<?php

namespace marsl\admin;

include_once(dirname(__FILE__)."/../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../autoload.php");

use marsl\user\User;

class Administration
{
    private User $user;

    public function __construct(
        User $user
    ) {
        $this->user = $user;
    }

    /*
     * Loads the main admin page.
     */
    public function admin(): void
    {
        if ($this->user->isAdmin()) {
            require_once("template/main.tpl.php");
        }
    }

}
