<?php

namespace marsl\modules;

include_once(dirname(__FILE__)."/../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../autoload.php");

/*
 * General interface for the module classes.
 */
interface Module
{
    /*
     * Initiate the module's frontend view.
     */
    public function display(): void;

    /*
     * Initiate the module's admin view.
     */
    public function admin(): void;

    /*
     * Returns whether the module has a search function.
     */
    public function isSearchable(): bool;

    /**
     * Returns an array of the module's fulltext searchable types.
     * @return array<int, array<string, string>>
     */
    public function getSearchList(): array;

    /*
     * Performs a fulltext search on the searchable attributes.
     */
    public function search(string $query, string $type): void;

    /*
     * Returns whether the module has a tag function.
    */
    public function isTaggable(): bool;

    /**
     * Returns an array of the module's  taggable types.
     * @return array<int, array<string, string>>
    */
    public function getTagList(): array;

    /*
     * Pushs a tag string to the module.
    */
    public function addTags(string $tagString, string $type, int $news): void;

    /*
     * Returns the tag string of the module.
    */
    public function getTagString(string $type, int $news): string|null;

    /**
     * Returns an array of the tags.
     * @return array<int, array<string, string>>
     */
    public function getTags(string $type, int $news): array;

    /**
     * Displays the information of a tag.
     */
    public function displayTag(): void;

    /*
     * Returns a page specific image.
     */
    public function getImage(): string|null;

    /*
     *
     */
    public function getTitle(): string|null;

    public function getRestfulURIPartFromOldURL(): string|null;

    public function getOldURIPartFromRestfulURL(): string|null;
}
