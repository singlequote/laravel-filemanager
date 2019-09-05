@includeIf('laravel-filemanager::package')

<script type="text/javascript">   
    if(typeof $ !== 'undefined' && typeof filemanager === 'undefined'){
        $.getScript("{{ $script }}");
    }
</script>