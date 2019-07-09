@foreach($files->items as $file)
<div class="file" data-id="{{ $file->id }}">
    @if($file->image)
    @includeIf("laravel-filemanager::types.image")
    @else
    @includeIf("laravel-filemanager::types.icon")
    @endif
    <div class="label">
        {{ $file->filename }}

        @if(isset($file->shared))
        <div class='shared'>
            <i data-feather='share-2'></i>
        </div>
        @endif
    </div>


</div>

@endforeach


@if($files->showMore)
<div class="load-more" data-type="files">
    <div class="icon"><i data-feather="chevrons-down"></i> </div>
    <div class="label">{{ __('filemanager::laravel-filemanager.load more') }}</div>
</div>
@endif