<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\File\File;

    function copyFileToRecycleBin($url){
        if(!Storage::disk('public')->exists('recycle_bin')){
            Storage::disk('public')->makeDirectory('recycle_bin');
        }
        Storage::disk('public')->copy($url,'recycle_bin/'.basename($url));
    }

?>
