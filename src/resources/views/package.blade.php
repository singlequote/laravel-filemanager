<div id="package-filemanager">


    <div id='package-sidebar'>
        
        <div class="sidebar-header">
            <h3>{{ __("filemanager::laravel-filemanager.filemanager") }}</h3> 
        </div>
        
        <hr>
        
        @if(Auth::check() && $myDrive)
        <div class="sidebar-button drive {{ $activeDrive && $activeDrive === 'drive' ? 'active' : "" }}" data-slug="drive">
            <div class="sidebar-icon"><i data-feather="hard-drive"></i></div>
            <div class="sidebar-label">{{ __('filemanager::laravel-filemanager.my drive') }}</div>
        </div>
        @endif
        
        @if(Auth::check() && $sharedDrive && $myDrive)
        <div class="sidebar-button drive {{ $activeDrive && $activeDrive === 'shared' ? 'active' : "" }}" data-slug="shared">
            <div class="sidebar-icon"><i data-feather="share-2"></i></div>
            <div class="sidebar-label">{{ __('filemanager::laravel-filemanager.shared with me') }}</div>
        </div>
        @endif
        
        @if($publicDrive)
        <div class="sidebar-button drive {{ $activeDrive && $activeDrive === 'public' ? 'active' : "" }}" data-slug="public">
            <div class="sidebar-icon"><i data-feather="users"></i></div>
            <div class="sidebar-label">{{ __('filemanager::laravel-filemanager.public') }}</div>
        </div>
        @endif
    </div>

    <div id='package-content'></div>

</div>





