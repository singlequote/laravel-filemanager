
<div class="font-icon-list col-lg-2 col-md-3 col-sm-4 col-6 file" data-route="{{config('laravel-filemanager.encrypted') ? encrypt($file->route) : $file->route}}">
    <div class="font-icon-detail">
        @if($file->type === 'image')
        <img class='img-fluid' src='{{route(config('laravel-filemanager.media.prefix'), $file->route)}}?h=100&w=100'>
        @else
        <i class="fa fa-image"></i>
        @endif
        <p class="file-name">{{$file->name}}</p>
    </div>
</div>