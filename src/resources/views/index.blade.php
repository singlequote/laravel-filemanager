
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Laravel Filemanager</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        
        <style>
            html, body { width: 100%; height: 100%; margin: 0px; padding: 0px; }
        </style>
    </head>
    <body>

        @includeIf('laravel-filemanager::package')
        <script src="{{ $script }}"></script>
    </body>
</html>
