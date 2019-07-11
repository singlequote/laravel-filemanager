@foreach($folders->items as $folder)
<div class="folder" data-slug="{{ $folder->path }}" data-id="{{ $folder->id }}">
    <div class="icon"><i data-feather="folder"></i></div>
    <div class="label">
        {{ $folder->name }}
        @if(isset($folder->shared))
        <div class='shared'>
            <i data-feather='share-2'></i>
        </div>
        @endif
    </div>
</div>
@endforeach

@if($folders->showMore)
<div class="load-more" data-type="folders">
    <div class="icon"><i data-feather="chevrons-down"></i> </div>
    <div class="label">{{ __('filemanager::laravel-filemanager.load more') }}</div>
</div>
@endif
