<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class File extends Model
{
    use HasFactory;

    protected $table = 'files';

    protected $casts = [
        'custom_properties' => 'json',
    ];

    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    public function showTrimFileName(int $string_length=50): string
    {
        return strlen($this->name) > $string_length ? substr($this->name,0,$string_length)."..." : $this->name;
    }

    public function showFormatSize(): string
    {
        if ($this->size >= 1073741824)
        {
            $size = number_format($this->size / 1073741824, 2) . ' GB';
        }
        elseif ($this->size >= 1048576)
        {
            $size = number_format($this->size / 1048576, 2) . ' MB';
        }
        elseif ($this->size >= 1024)
        {
            $size = number_format($this->size / 1024, 2) . ' KB';
        }
        elseif ($this->size > 1)
        {
            $size = $this->size . ' bytes';
        }
        elseif ($this->size == 1)
        {
            $size = $this->size . ' byte';
        }
        else
        {
            $size = '0 bytes';
        }

        return $size;
    }

    public function getFullUrl(string $conversionName = ''): string
    {
        if($this->disk == 'public'){
            return  \Illuminate\Support\Facades\Storage::disk('public')->url( $this->url );
        }elseif($this->disk == 'local'){
            return  \Illuminate\Support\Facades\Storage::disk('local')->url( $this->url );
        }elseif($this->disk == 's3'){
            return $this->url;
        }
        else{
            return '';
        }
    }

    public function hasCustomProperty(string $propertyName): bool
    {
        return Arr::has($this->custom_properties, $propertyName);
    }

    public function getCustomProperty(string $propertyName, $default = null)
    {
        return Arr::get($this->custom_properties, $propertyName, $default);
    }

    public function setCustomProperty(string $name, $value): self
    {
        $customProperties = $this->custom_properties;

        Arr::set($customProperties, $name, $value);

        $this->custom_properties = $customProperties;

        return $this;
    }

    public function forgetCustomProperty(string $name): self
    {
        $customProperties = $this->custom_properties;

        Arr::forget($customProperties, $name);

        $this->custom_properties = $customProperties;

        return $this;
    }

    public function move(File $file, $collectionName = 'default', string $diskName = '', string $fileName = ''): self
    {
        $newMedia = $this->copy($file, $collectionName, $diskName, $fileName);

        $this->delete();

        return $newMedia;
    }

    public function copy(File $file, $collectionName = 'default', string $diskName = '', string $fileName = ''): self
    {
        $temporaryDirectory = TemporaryDirectory::create();

        $temporaryFile = $temporaryDirectory->path('/') . DIRECTORY_SEPARATOR . $this->file_name;

        /** @var \Spatie\MediaLibrary\MediaCollections\Filesystem $filesystem */
        $filesystem = app(Filesystem::class);

        $filesystem->copyFromMediaLibrary($this, $temporaryFile);

        $fileAdder = $file
            ->addMedia($temporaryFile)
            ->usingName($this->name)
            ->setOrder($this->order_column)
            ->withCustomProperties($this->custom_properties);

        if ($fileName !== '') {
            $fileAdder->usingFileName($fileName);
        }

        $newMedia = $fileAdder
            ->toMediaCollection($collectionName, $diskName);

        $temporaryDirectory->delete();

        return $newMedia;
    }

    public function remove(){
        Storage::disk($this->disk)->delete($this->url);
        return $this->delete();
    }



}
