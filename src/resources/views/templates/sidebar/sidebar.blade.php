<div>
    <ul class="tree">
        @if(config('laravel-filemanager.auth.private_folder') &&  Auth::check())
        <li class="folder" lf-attribute='data-route:private'> <span>Private</span>
            <ul id="filemanager-private">
                
            </ul>
        </li>
        @endif

        @if(config('laravel-filemanager.auth.shared_folder'))
        <li class="folder" lf-attribute='data-route:shared'> <span>Shared</span>
            <ul id="filemanager-shared">
                
            </ul>
        </li>
        @endif
    </ul>
</div>
