<?php

namespace marsl\admin\ajax;

include_once(dirname(__FILE__)."/../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../autoload.php");

use marsl\ComponentBuilder;
use marsl\includes\Configuration;
use marsl\Infrastructure\RequestParameters\Adapters\Drivers\Service\IRequestParametersService;
use marsl\user\Authentication;
use marsl\user\Role;

class SharedImagesUploader
{
    private Authentication $authentication;
    private Configuration $configuration;
    private IRequestParametersService $requestParametersService;
    private Role $role;

    public function __construct(
        Authentication $authentication,
        Configuration $configuration,
        IRequestParametersService $requestParametersService,
        Role $role
    ) {
        $this->authentication = $authentication;
        $this->configuration = $configuration;
        $this->requestParametersService = $requestParametersService;
        $this->role = $role;
    }

    public function upload(): void
    {
        if ($this->authentication->moduleAdminAllowed("news", $this->role->getRole())) {
            if ($this->authentication->checkToken(
                $this->requestParametersService->fromGet()->getIntegerParameter("authTime"),
                $this->requestParametersService->fromGet()->getStringParameter("authToken")
            )) {
                $imageFolder = "../../shared/image/";
                $temp = $this->requestParametersService->fromFiles()->getArrayParameter("file", array());
                if (is_string($temp['tmp_name']) && is_uploaded_file($temp['tmp_name']) && is_string($temp['name'])) {
                    if (preg_match("/([^\w\s\d\-_~,;:\[\]\(\).])|([\.]{2,})/", $temp['name'])) {
                        header("HTTP/1.1 400 Invalid file name.");
                        return;
                    }
                    if (!in_array(strtolower(pathinfo($temp['name'], PATHINFO_EXTENSION)), array("gif", "jpg", "png"))) {
                        header("HTTP/1.1 400 Invalid extension.");
                        return;
                    }
                    $random = mt_rand();
                    $fileName = $random.$temp['name'];
                    $fileToWrite = $imageFolder.$fileName;
                    $fileAbsolutePath = $this->configuration->getDomain().$this->configuration->getBasePath()."/shared/image/".$fileName;
                    move_uploaded_file($temp['tmp_name'], $fileToWrite);
                    echo json_encode(array('location' => $fileAbsolutePath));
                } else {
                    header("HTTP/1.1 500 Server Error");
                }
            } else {
                header("HTTP/1.1 403 Wrong authentication");
            }
        } else {
            header("HTTP/1.1 403 Forbidden");
        }
    }
}


$sharedImagesUploader = ComponentBuilder::buildDependencies()->make('marsl\admin\ajax\SharedImagesUploader');

if ($sharedImagesUploader instanceof SharedImagesUploader) {
    $sharedImagesUploader->upload();
}
