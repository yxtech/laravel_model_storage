<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\UploadedFile;

interface HasFile
{
    public function files(): MorphMany;

    public function defaultFiles(): MorphMany;

    public function addFile(UploadedFile $file, string $path, int $user_id, string $collection = 'default', array $custom_properties = null, string $disk = 'public'): int;

    public function addFileWithName(UploadedFile $file, string $path, string $filename, int $user_id, string $collection = 'default', array $custom_properties = null, string $disk = 'public'): int;

    public function createFileWithoutUpload(string $path, string $filename , int $user_id, string $collection = 'default', array $custom_properties = null, string $disk = 'public'): int;

    public function copyFile(string $oldPath, string $newPath, string $filename , int $user_id, string $collection = 'default', array $custom_properties = null, string $disk = 'public'):int ;

    public function moveFile(string $oldPath, string $newPath, string $filename , int $user_id, string $collection = 'default', array $custom_properties = null, string $disk = 'public'):int ;

    public function deleteFile($id): bool;

    public function deleteFileFromUrl($url): bool;

    public function deleteCollection(string $collection = 'default'):bool;

}
