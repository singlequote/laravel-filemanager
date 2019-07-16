<link rel="stylesheet" href="{{ $css }}" media="screen">
<div id="package-filemanager" data-start="{{ $loadOnStartUp }}" data-modal="{{ $modal ? "true" : "false" }}" data-url="{{ route(config('laravel-filemanager.prefix')) }}">


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
        
        <hr>
        @if(Auth::check() && $myDrive)
        <div class="diskSize">
            <div class="sidebar-button" id="diskSize">
                <div class="sidebar-icon"><i data-feather="database"></i></div>
                <div class="sidebar-label">{{ __('filemanager::laravel-filemanager.storage') }}</div>
                <div class=""></div>
                <div class="progress-bars">
                    <span class="bars">
                        <span class="bar-progress" style="width:{{ ($driversSize / 1000) / $maxUpload * 100 }}%"></span>
                    </span>
                    <small class="{{ $driversSize ? "size" : "" }}">{{ $driversSize ?  $driversSize : "0.1 KB"}}</small>
                </div>
            </div>
        </div>
        @endif
        
    </div>

    <div id='package-content'></div>

    <div id='package-details'>
        <h3 id="title"></h3>
        <div id="content">

        </div>
    </div>

</div>
<!--<script src="{{ $script }}"></script>-->

<script type="text/javascript" defer>   
    if(typeof $ !== 'undefined' && typeof filemanager === 'undefined'){
        $.getScript("{{ $script }}");
    }
</script>