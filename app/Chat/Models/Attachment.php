<?php

namespace App\Chat\Models;

use App\Chat\Contracts\IAttachment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Jenssegers\Mongodb\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\SoftDeletes;

class Attachment extends Model implements IAttachment
{
    use SoftDeletes;

    public $primaryKey = '_id';
    public $connection = "mongodb";
    public $table = "chat_attachments";
    public $collection = "chat_attachments";
    public $timestamps = true;
    public $guarded = [];

    public $appends = [ 'url' ];

    protected $content;

    public static function boot()
    {
        parent::boot();
        static::saving(function($model){
            /**
             * @var $model Attachment
             */
            $model->setFilePath(Str::random(32) .".". $model->getFileExtension());
            Storage::disk($model->getStorage())->put($model->getFilePath(), $model->getContent());
        });
    }

    /**
     * @return BelongsTo|Builder|Conversation
     */
    public function message()
    {
        return $this->belongsTo('\App\Chat\Models\Message', 'message_id', '_id');
    }

    /**
     * @param $query
     * @param $filePath
     * @return mixed
     */
    public function scopeFilePath($query, $filePath){
        return $query->where('file_path', '=', $filePath);
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getUrlAttribute()
    {
        return Storage::disk($this->getStorage())->url($this->file_path);
    }

    /**
     * @param $storage
     * @return $this
     */
    public function setStorage($storage)
    {
        $this->storage = $storage;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * @param $contentType
     * @return $this
     */
    public function setContentType($contentType)
    {
        $this->content_type = $contentType;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getContentType()
    {
        return $this->content_type;
    }

    /**
     * @param $content
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function getContent()
    {
        if (!$this->content) {
            $this->content = Storage::disk($this->storage)->get($this->file_path);
        }
        return $this->content;
    }

    /**
     * @param $fileName
     * @return $this
     */
    public function setFileName($fileName)
    {
        $this->file_name = $fileName;
        $this->file_extension = pathinfo($fileName, PATHINFO_EXTENSION);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFileName()
    {
        return $this->file_name;
    }

    /**
     * @return mixed
     */
    public function getFileExtension()
    {
        return $this->file_extension;
    }

    /**
     * @param $path
     * @return $this
     */
    public function setFilePath($path)
    {
        $this->file_path = $path;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFilePath()
    {
        return $this->file_path;
    }

    /**
     * @return string
     */
    public function getStoragePath(){
        return config('filesystems.disks.' . $this->getStorage() . '.root') . DIRECTORY_SEPARATOR . $this->getFilePath();
    }
}
