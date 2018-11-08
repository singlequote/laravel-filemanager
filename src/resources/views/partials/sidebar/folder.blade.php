
@if($folder->children->isEmpty())
<li>
    <span>
        <i class="fa fa-folder"></i>
        <a class="folder" data-route="{{config('laravel-filemanager.encrypted') ? encrypt($folder->route) : $folder->route}}" href="#"> {{$folder->name}}</a>
    </span>
</li>
@else

<li>
    <span>
        <a class="folder" data-route="{{config('laravel-filemanager.encrypted') ? encrypt($folder->route) : $folder->route}}" data-toggle="collapse" href="#{{str_slug($folder->name)}}" aria-expanded="false" aria-controls="{{str_slug($folder->name)}}">
            <i class="collapsed"><i class="fa fa-folder"></i></i>
            <i class="expanded"><i class="fa fa-folder-open"></i></i> {{$folder->name}}
        </a>
    </span>
    <ul>
        <div id="{{str_slug($folder->name)}}" class="collapse">
            @foreach($folder->children as $child)
                @include($directory.'.sidebar.folder', ['folder' => $child])
            @endforeach
        </div>
    </ul>
</li>
@endif