<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Laravel filemanager</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
        <link rel="stylesheet" href="https://bootswatch.com/4/flatly/bootstrap.min.css">
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
        <link type='text/css' rel='stylesheet' href='{{asset('vendor/laravel-filemanager/css/filemanager.css')}}' />
    </head>
    <body>
        <div class='container-fluid' style='padding-top:100px;'>

        <!--=======================================================
            =======================================================
            =======================================================-->
            <!--START OF THE PACKAGE-->
            <div class='row' id="filemanager">
                <!--Sidebar-->
                <div class='col-3'>
                    <div class="card bg-default">
                        <div class="card-header">Sidebar</div>
                        <div class="card-body">
                          <div id='filemanager-sidebar'></div>
                        </div>
                    </div>
                </div>

                <!--Files content-->
                <div class='col'>
                    <div class="card text-white bg-default">
                        <div class="card-header">Content</div>
                        <div class="card-body">
                          <div id='filemanager-content'></div>
                        </div>
                    </div>
                </div>
            </div>

            <!--The modal needed for the package-->
            <div class="modal fade modal-primary" id="filemanager-media-preview" tabindex="-1" role="dialog" aria-labelledby="filemanager-media-preview">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-body text-default"></div>
                        <div class="modal-footer justify-content-end">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
            <!--END OF THE PACKAGE-->
        <!--=======================================================
            =======================================================
            =======================================================-->

        </div>


        <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
        <!--REQUIRED SCRIPTS-->
        <script type='text/javascript' src='{{asset('vendor/laravel-filemanager/js/filemanager.min.js')}}'></script>
        <script type='text/javascript'>
            filemanager = new FileManager;
            filemanager.root = "{{Auth::user()->id}}";
            filemanager.token = "{{csrf_token()}}";
        </script>
        <!--END REQUIRED SCRIPTS-->
    </body>
</html>