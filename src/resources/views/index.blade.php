<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Laravel filemanager</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
        <!--REQUIRED CSS-->
        <link type='text/css' rel='stylesheet' href='{{asset('vendor/laravel-filemanager/css/filemanager.css')}}' />
        <!--END REQUIRED CSS-->
    </head>
    <body style="background-image:url('https://picsum.photos/2000?blur');">
        <div class='container' style='padding-top:100px;'>

        <div class='row'>
            <div class='col'>


<!--        =======================================================
            =======================================================
            =======================================================-->

                <div id='app'>
                    The package will load it in here
                    Update your template files located int he templates folder
                    To change the layouts
                </div>
    <!--        =======================================================
                =======================================================
                =======================================================-->


    
                </div>
            </div>
        </div>


        <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
        <!--REQUIRED SCRIPTS-->
        <script type='text/javascript' src='{{asset('vendor/laravel-filemanager/js/filemanager.min.js')}}'></script>
        <script type='text/javascript'>
            //first boot the package where the configs are loaded
            //then build the package for the default view
            filemanager.boot().build();
            //Boot up the filemanager
            filemanager.token = "{{csrf_token()}}"; //set the token

            filemanager.livereload = false; //auto reload the content when set to true

            /**
             * The callback for most actions
             *
             * @returns {undefined}
             */
            filemanager.callback = () => {
                feather.replace(); //this is called evertime you perform an action
            };
        </script>
        <!--END REQUIRED SCRIPTS-->
    </body>
</html>