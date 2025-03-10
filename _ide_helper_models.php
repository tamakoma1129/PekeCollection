<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $duration
 * @property string $raw_image_path
 * @property string $preview_audio_path
 * @property \DateTime|null $created_at
 * @property \DateTime|null $updated_at
 * @property-read \App\Models\MediaFile|null $mediaFile
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Audio newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Audio newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Audio query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Audio whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Audio whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Audio whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Audio wherePreviewAudioPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Audio whereRawImagePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Audio whereUpdatedAt($value)
 */
	class Audio extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $dimensions
 * @property \DateTime|null $created_at
 * @property \DateTime|null $updated_at
 * @property int $width
 * @property int $height
 * @property-read \App\Models\MediaFile|null $mediaFile
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image whereHeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image whereWidth($value)
 */
	class Image extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $file_name
 * @property string $file_path
 * @property string $file_extension
 * @property int $file_size
 * @property int $mediable_id
 * @property string $mediable_type
 * @property string $preview_image_path
 * @property \DateTime|null $created_at
 * @property \DateTime|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $mediable
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MediaFile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MediaFile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MediaFile query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MediaFile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MediaFile whereFileExtension($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MediaFile whereFileName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MediaFile whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MediaFile whereFileSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MediaFile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MediaFile whereMediableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MediaFile whereMediableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MediaFile wherePreviewImagePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MediaFile whereUpdatedAt($value)
 */
	class MediaFile extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \DateTime|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \DateTime|null $created_at
 * @property \DateTime|null $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 */
	class User extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $duration
 * @property int $resolution_width
 * @property int $resolution_height
 * @property string $raw_image_path
 * @property string $preview_video_path
 * @property \DateTime|null $created_at
 * @property \DateTime|null $updated_at
 * @property-read \App\Models\MediaFile|null $mediaFile
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Video newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Video newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Video query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Video whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Video whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Video whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Video wherePreviewVideoPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Video whereRawImagePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Video whereResolutionHeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Video whereResolutionWidth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Video whereUpdatedAt($value)
 */
	class Video extends \Eloquent {}
}

