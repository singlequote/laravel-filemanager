<div class="font-icon-list col-lg-2 col-md-3 col-sm-4 col-6 folder" data-route="{{config('laravel-filemanager.encrypted') ? encrypt($folder->route) : $folder->route}}">
    <div class="font-icon-detail text-primary">
        <i class="fa fa-folder fa-4x"></i>
        <p class="folder-name">{{$folder->name}}</p>
    </div>
</div>