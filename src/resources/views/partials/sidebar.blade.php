<div class="tree ">
    <ul>
        @if(config('laravel-filemanager.auth.private_folder'))
        <li>
            <span>
                <a class="folder active" data-route="{{config('laravel-filemanager.encrypted') ? encrypt(Auth::user()->id) : Auth::user()->id}}" data-toggle="collapse" href="#private" aria-expanded="true" aria-controls="private"><i class="collapsed"><i class="fa fa-folder"></i></i>
                    <i class="expanded">
                        <i class="fa fa-folder-open"></i>
                    </i>
                    Private files
                </a>
            </span>
            <div id="private" class="collapse show">
                <ul>
                    @foreach($privateFolders as $folder)
                        @include('laravel-filemanager::partials.sidebar.folder', ['folder' => $folder])
                    @endforeach
                </ul>
            </div>
        </li>
        @endif
        @if(config('laravel-filemanager.auth.shared_folder'))
        <li>
            <span>
                <a class="folder" data-route="{{config('laravel-filemanager.encrypted') ? encrypt(config('laravel-filemanager.auth.shared_prefix')) : config('laravel-filemanager.auth.shared_prefix')}}" data-toggle="collapse" href="#public" aria-expanded="false" aria-controls="public">
                    <i class="collapsed"><i class="fa fa-folder"></i></i>
                    <i class="expanded">
                        <i class="fa fa-folder-open"></i>
                    </i>
                    Shared files
                </a>
            </span>
            <div id="public" class="collapse">
                <ul>
                    @foreach($publicFolders as $folder)
                        @include('laravel-filemanager::partials.sidebar.folder', ['folder' => $folder])
                    @endforeach
                </ul>
            </div>
        </li>
        @endif
    </ul>
</div>
