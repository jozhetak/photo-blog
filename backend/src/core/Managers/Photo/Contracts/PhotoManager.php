<?php

namespace Core\Managers\Photo\Contracts;

use Closure;
use Core\Models\Photo;
use Illuminate\Http\UploadedFile;

/**
 * Interface PhotoManager.
 *
 * @package Core\Managers\Photo\Contracts
 */
interface PhotoManager
{
    /**
     * Get the photo by its ID.
     *
     * @param int $id
     * @return Photo
     */
    public function getById(int $id);

    /**
     * Get published photo by its ID.
     *
     * @param int $id
     * @return Photo
     */
    public function getPublishedById(int $id);

    /**
     * Get not published photo by its ID.
     *
     * @param int $id
     * @return Photo
     */
    public function getNotPublishedById(int $id);

    /**
     * Paginate over published photos.
     *
     * @param int $page
     * @param int $perPage
     * @param array $query
     * @return mixed
     */
    public function paginateOverPublished(int $page, int $perPage, array $query = []);

    /**
     * Apply the callback function on each photo.
     *
     * @param Closure $callback
     * @return void
     */
    public function each(Closure $callback);

    /**
     * Apply the callback function on each not published photo older than week.
     *
     * @param Closure $callback
     * @return void
     */
    public function eachNotPublishedOlderThanWeek(Closure $callback);

    /**
     * Determine if exists published photos older than week.
     *
     * @return bool
     */
    public function existsPublishedOlderThanWeek();

    /**
     * Save the photo filled with the attributes array.
     *
     * @param Photo $photo
     * @param array $attributes
     * @param array $options
     * @return void
     */
    public function save(Photo $photo, array $attributes = [], array $options = []);

    /**
     * Save the photo associated with the file.
     *
     * @param Photo $photo
     * @param UploadedFile $file
     * @return void
     */
    public function saveWithFile(Photo $photo, UploadedFile $file);

    /**
     * Delete the photo.
     *
     * @param Photo $photo
     * @return void
     */
    public function delete(Photo $photo);
}