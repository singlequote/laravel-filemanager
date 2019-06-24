
<div class="image">
    
    @if(in_array(strtolower($file->extension), ['jpg', 'jpeg', 'png']))

        <img src="{{ route(config("laravel-filemanager.media.prefix", "media"), [400,400, $file->basepath]) }}">

    @elseif($file->extension === 'svg')
        @php
            $file = file_get_contents(route(config("laravel-filemanager.media.prefix", "media"), $file->basepath));
        @endphp
        {!! $file !!}
    @else
    
        <img src="{{ route(config("laravel-filemanager.media.prefix", "media"), $file->basepath) }}">
        
    @endif

</div>