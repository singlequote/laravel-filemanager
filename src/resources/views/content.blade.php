<ul class="breadcrumb">
  <li><a href="">{{ $title }}</a></li>
  @foreach($breadcrumb as $crumb)
  <li><a href="{{ $crumb['path'] }}">{{ $crumb['name'] }}</a></li>
  @endforeach
</ul>

@if(!$folders && !$files)
<div class="emptyContent">
    @includeIf("laravel-filemanager::svg.svgEmpty")
    <h4>{{ __('filemanager::laravel-filemanager.this looks empty') }}</h4>
</div>
@endif

@if($folders > 0)
<h4><small>{{ $folders }}</small> {{ __('filemanager::laravel-filemanager.folders') }}</h4>
<div class="folders"></div>
@endif

@if($files > 0)
<h4><small>{{ $files }}</small> {{ __('filemanager::laravel-filemanager.files') }}</h4>
<div class="files"></div>
@endif

<script type="text/javascript">
    function imgError(image)
    {
        image.src = `data:image/png;base64,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`
    }
</script>