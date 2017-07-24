<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Photo.
 *
 * @property int id
 * @property int created_by_user_id
 * @property string description
 * @property string path
 * @property string avg_color
 * @property Carbon published_at
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property User $createdByUser
 * @property Exif $exif
 * @property Collection $tags
 * @property Collection $thumbnails
 * @package App\Models
 */
class Photo extends Model
{
    /**
     * @inheritdoc
     */
    protected $attributes = [
        'description' => '',
    ];

    /**
     * @inheritdoc
     */
    protected $dates = [
        'published_at',
        'created_at',
        'updated_at',
    ];

    /**
     * @inheritdoc
     */
    protected $fillable = [
        'description',
    ];

    /**
     * Setter for the 'description' attribute.
     *
     * @param string $description
     * @return $this
     */
    public function setDescriptionAttribute(string $description)
    {
        $this->attributes['description'] = trim($description);

        return $this;
    }

    /**
     * Check if photo is published.
     *
     * @return bool
     */
    public function isPublished(): bool
    {
        return !is_null($this->published_at);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function exif()
    {
        return $this->hasOne(Exif::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'photo_tags');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function thumbnails()
    {
        return $this->belongsToMany(Thumbnail::class, 'photo_thumbnails')->orderBy('width')->orderBy('height');
    }
}
