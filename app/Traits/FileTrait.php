<?php

namespace App\Traits;

use App\Models\Bill;
use App\Models\File;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait FileTrait {

    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'model');
    }

    public function defaultFiles(): MorphMany
    {
        return $this->morphMany(File::class, 'model')->where('collection_name','default');
    }

    public function addFile(UploadedFile $file, string $path, int $user_id, string $collection = 'default', array $custom_properties = null, string $disk = 'public'): int
    {
        // check if path ends with slash
        $path = (!(substr($path, -1)=='/' || substr($path, -1)=='\\')) ? ($path .= '/') : $path;
        $filename = date('YmdHis') . '_' .Str::uuid(). '.' . $file->getClientOriginalExtension();

        // store file
        $file->storeAs($path , $filename , $disk);
        $newFile = new File();
        $newFile->user_id = $user_id;
        $newFile->model_id = $this->id;
        $newFile->model_type = $this->getMorphClass();
        $newFile->collection_name = $collection;
        $newFile->name = $file->getClientOriginalName();
        $newFile->mime_type = $file->getMimeType();
        $newFile->size = $file->getSize();
        $newFile->disk = $disk;
        $newFile->url = $path . $filename;
        if($custom_properties!==null){
            $newFile->custom_properties = $custom_properties;
        }
        $newFile->save();

        return $newFile->id;
    }

    public function addFileWithName(UploadedFile $file, string $path, string $filename, int $user_id, string $collection = 'default', array $custom_properties = null, string $disk = 'public'): int
    {
        // check if path ends with slash
        $path = (!(substr($path, -1)=='/' || substr($path, -1)=='\\')) ? ($path .= '/') : $path;

        // store file
        $file->storeAs($path , $filename , $disk);
        $newFile = new File();
        $newFile->user_id = $user_id;
        $newFile->model_id = $this->id;
        $newFile->model_type = $this->getMorphClass();
        $newFile->collection_name = $collection;
        $newFile->name = $file->getClientOriginalName();
        $newFile->mime_type = $file->getMimeType();
        $newFile->size = $file->getSize();
        $newFile->disk = $disk;
        $newFile->url = $path . $filename;
        if($custom_properties!==null){
            $newFile->custom_properties = $custom_properties;
        }
        $newFile->save();

        return $newFile->id;
    }

    public function createFileWithoutUpload(string $path, string $filename , int $user_id, string $collection = 'default', array $custom_properties = null, string $disk = 'public'): int
    {
        $newFile = new File();
        $newFile->user_id = $user_id;
        $newFile->model_id = $this->id;
        $newFile->model_type = $this->getMorphClass();
        $newFile->collection_name = $collection;
        $newFile->name = $filename;
        $newFile->mime_type = Storage::disk('public')->getMimeType($path);
        $newFile->size = Storage::disk('public')->size($path);
        $newFile->disk = $disk;
        $newFile->url = $path;
        if($custom_properties!==null){
            $newFile->custom_properties = $custom_properties;
        }
        $newFile->save();

        return $newFile->id;
    }

    public function copyFile(string $oldPath, string $newPath, string $filename , int $user_id, string $collection = 'default', array $custom_properties = null, string $disk = 'public'):int
    {

        Storage::disk('public')->copy($oldPath, $newPath);

        // store file
        $newFile = new File();
        $newFile->user_id = $user_id;
        $newFile->model_id = $this->id;
        $newFile->model_type = $this->getMorphClass();
        $newFile->collection_name = $collection;
        $newFile->name = $filename;
        $newFile->mime_type = Storage::disk('public')->getMimeType($newPath);
        $newFile->size = Storage::disk('public')->size($newPath);
        $newFile->disk = $disk;
        $newFile->url = $newPath;
        if($custom_properties!==null){
            $newFile->custom_properties = $custom_properties;
        }
        $newFile->save();
        return $newFile->id;
    }

    public function moveFile(string $oldPath, string $newPath, string $filename , int $user_id, string $collection = 'default', array $custom_properties = null, string $disk = 'public'):int
    {
        $file_id = $this->copyFile($oldPath, $newPath, $filename, $user_id, $collection, $custom_properties, $disk);
        Storage::disk('public')->delete($oldPath);
        return $file_id;
    }

    public function deleteFile($id): bool
    {
        $file = File::find($id);
        Storage::disk($file->disk)->delete($file->url);
        return $file->delete();
    }

    public function deleteFileFromUrl($url): bool
    {
        $file = File::where('url',$url)->first();
        return $this->deleteFile($file->id);
    }

    public function deleteCollection(string $collection = 'default'):bool
    {
        $files = File::where([['model_id', $this->id],['model_type', $this->getMorphClass()],['collection_name', $collection]])->get();
        $bool = 1;
        foreach($files as $file){
            Storage::disk($file->disk)->delete($file->url);
            $file->delete() ?: $bool = 0;
        }
        return $bool;

    }

    public function deleteCollectionToRecycleBin(string $collection = 'default'):bool
    {
        $files = File::where([['model_id', $this->id],['model_type', $this->getMorphClass()],['collection_name', $collection]])->get();
        $bool = 1;
        foreach($files as $file){
            copyFileToRecycleBin($file->url);
            Storage::disk($file->disk)->delete($file->url);
            $file->delete() ?: $bool = 0;
        }
        return $bool;

    }



}
