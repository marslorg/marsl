<?php

namespace marsl\modules;

include_once(dirname(__FILE__)."/../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../autoload.php");

use marsl\includes\Basic;
use marsl\Infrastructure\RequestParameters\Adapters\Drivers\Service\IRequestParametersService;
use marsl\user\Authentication;
use marsl\user\Role;

class Newsletter implements Module
{
    private Authentication $authentication;
    private Basic $basic;
    private IRequestParametersService $requestParametersService;
    private Role $role;

    public function __construct(
        Authentication $authentication,
        Basic $basic,
        IRequestParametersService $requestParametersService,
        Role $role
    ) {
        $this->authentication = $authentication;
        $this->basic = $basic;
        $this->requestParametersService = $requestParametersService;
        $this->role = $role;
    }

    /*
     * Initiate the module's frontend view.
     */
    public function display(): void
    {
    }

    /*
     * Initiate the module's admin view.
     */
    public function admin(): void
    {
        $authTime = time();
        $authToken = $this->authentication->getToken($authTime);

        if ($this->authentication->moduleAdminAllowed("newsletter", $this->role->getRole()) && $this->authentication->moduleExtendedAllowed("newsletter", $this->role->getRole())) {

            if ($this->requestParametersService->fromPost()->getStringParameter("action", "") == "") {

                $temporaryKey = $this->basic->tempFileKey();

                $allRoles = $this->role->getRoles();

                $roles = array();

                foreach ($allRoles as $curRole) {
                    array_push($roles, array('role' => $curRole['role'],'name' => $curRole['name']));
                }

                require_once(dirname(__FILE__)."/../admin/template/newsletter.tpl.php");
            }
        }
    }

    /*
     * Returns whether the module has a search function.
     */
    public function isSearchable(): bool
    {
        return false;
    }

    /*
     * Interface method stub.
     */
    public function getSearchList(): array
    {
        return array();
    }

    /*
     * Performs a fulltext search on the searchable attributes.
     */
    public function search(string $query, string $type): void
    {

    }

    /*
     * Returns whether the module has a tag function.
     */
    public function isTaggable(): bool
    {
        return false;
    }

    /*
     * Interface method stub.
    */
    public function getTagList(): array
    {
        return array();
    }

    /*
     * Pushs a tag string to the module.
     */
    public function addTags(string $tagString, string $type, int $news): void
    {

    }

    /*
     * Returns the tag string of the module.
     */
    public function getTagString(string $type, int $news): string|null
    {
        return null;
    }

    /*
     * Returns an array of the tags.
     */
    public function getTags(string $type, int $news): array
    {
        return array();
    }

    /*
     * Displays the information of a tag.
     */
    public function displayTag(): void
    {

    }

    /*
     * Returns a page specific image.
     */
    public function getImage(): string|null
    {
        return null;
    }

    /*
     *
     */
    public function getTitle(): string|null
    {
        return null;
    }

    public function getRestfulURIPartFromOldURL(): string|null
    {
        return null;
    }

    public function getOldURIPartFromRestfulURL(): string|null
    {
        return null;
    }
}
