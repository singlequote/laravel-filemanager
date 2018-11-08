

<div class="header">
    <div class="row">
        <div class="col">
            <button disabled='disabled' data-action='edit' class='btn btn-wd btn-warning btn-outline'>
                <i class='fa fa-edit'></i>
            </button>
        </div>
        <div class="col">
            <button disabled='disabled' data-action='delete' class='btn btn-wd btn-danger btn-outline'>
                <i class='fa fa-trash'></i>
            </button>
        </div>
        <div class="col">
            <button disabled='disabled' data-action='crop' class='btn btn-wd btn-info btn-outline' title='Not supported yet'>
                <i class='fa fa-crop'></i>
            </button>
        </div>
        <div class="col">
            <button data-action='upload' class='btn btn-wd btn-success btn-outline'>
                <i class='fa fa-cloud-upload'></i>
            </button>
        </div>
        <div class="col">
            <button data-action='new' class='btn btn-wd btn-success btn-outline'>
                <i class='fa fa-folder'></i>
            </button>
        </div>
    </div>
</div>

<div class="content all-icons">
    <div class="row">
        @if(!$root)
        <div class="font-icon-list col-lg-2 col-md-3 col-sm-4 col-6 folder" data-route="{{config('laravel-filemanager.encrypted') ? encrypt($previous) : $previous}}">
            <div class="font-icon-detail text-primary">
                <i class="fa fa-arrow-left fa-4x"></i>
                <p>&nbsp;</p>
            </div>
        </div>
        @endif
        @foreach($folders as $folder)
            @include('laravel-filemanager::partials.content.folders-'.$view, ['folder' => $folder])
        @endforeach
        @foreach($files as $file)
            @include('laravel-filemanager::partials.content.files-'.$view, ['file' => $file])
        @endforeach
    </div>
</div>

