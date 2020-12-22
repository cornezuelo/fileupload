<?php
$pwd = 'ca8db27f5bd4dc18da6776e4b5c52569'; //It must be in md5. Abc1234. by default. Set to false, null or empty string for disallowing this feature.
$limit = '1000000'; //Number of bytes maximum. Default 1000000 bytes
$files_allowed = ['jpg','png']; //Array of allowed file extensions
$target_dir = __DIR__.'/files/'; //Where are the files going to be stored
//*********************************************************************************
function creasinoexiste($dir,$modo=02775) {
  if(!$dir) {
    return; 
  }   
    $salida=false;
  if (!file_exists($dir)) {
    if (isset($_SERVER["WINDIR"])) $salida=@mkdir($dir);//windows
    else
    {
      $salida=@mkdir($dir, $modo, true);//no windows y recursivo
      exec("chown -R www-data:www-data $dir");
    }
  }
  return $salida;
}     

function formatBytes($bytes, $precision = 2) { 
    $units = array('B', 'KB', 'MB', 'GB', 'TB'); 

    $bytes = max($bytes, 0); 
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
    $pow = min($pow, count($units) - 1); 

    // Uncomment one of the following alternatives
    $bytes /= pow(1024, $pow);
    // $bytes /= (1 << (10 * $pow)); 

    return round($bytes, $precision) . ' ' . $units[$pow]; 
} 
//*********************************************************************************
creasinoexiste($target_dir); //Creates the dir if it doesn't exist
$msg = '';
if (isset($_FILES['file'])) {  
  $fileType = strtolower(pathinfo(basename($_FILES["file"]["name"]),PATHINFO_EXTENSION));
  $target_file = $target_dir . uniqid() .'.'.$fileType;
  //Pwd check
  if (!empty($pwd) && md5($_REQUEST['pwd']) !== $pwd) {
    $msg = '<div class="alert alert-danger" role="alert"><b>¡Error!</b> Incorrect password.</div>';
  } 
  //Limit check
  elseif ($_FILES["file"]["size"] > $limit) {
    $msg = '<div class="alert alert-danger" role="alert"><b>¡Error!</b> Maximum allowed size is '.formatBytes($limit).'.</div>';
  }
  //Extension check
  elseif (!in_array($fileType, $files_allowed)) {
    $msg = '<div class="alert alert-danger" role="alert"><b>¡Error!</b> Only allowed '.implode(',',$files_allowed).' extensions.</div>';
  }
  //Try to move
  elseif (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {            
      $path = 'http://' . str_replace( $_SERVER['DOCUMENT_ROOT'], $_SERVER['SERVER_NAME'], $target_file ); // this is where image is uploaded
      $msg = "<div class='alert alert-success' role='alert'><b>¡Success!</b> The file ". basename( $_FILES["file"]["name"]). " has been uploaded to:<br><input type='text' class='form-control' onClick='this.select();' value='".$path."'></div>";
  } else {
      $msg = '<div class="alert alert-danger" role="alert"><b>¡Error!</b> There was a problem uploading the file.</div>';
  }

}
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">    

    <title>FileUpload</title>

    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css" integrity="sha384-9gVQ4dYFwwWSjIDZnLEWnxCjeSWFphJiwGPXr1jddIhOegiu1FwO5qRGvFXOdJZ4" crossorigin="anonymous">

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.13/css/all.css" integrity="sha384-DNOHZ68U8hZfKXOrtjWvjxusGo9WQnrNx2sqG0tfsghAvtVlRW3tvkXWZh58N9jp" crossorigin="anonymous">

    <!-- Custom styles for this template -->
    <style>
      /*
       * Globals
       */

      /* Links */
      a,
      a:focus,
      a:hover {
        color: #fff;
      }

      /* Custom default button */
      .btn-secondary,
      .btn-secondary:hover,
      .btn-secondary:focus {
        color: #333;
        text-shadow: none; /* Prevent inheritance from `body` */
        background-color: #fff;
        border: .05rem solid #fff;
      }


      /*
       * Base structure
       */

      html,
      body {
        height: 100%;
        background: linear-gradient(
          rgba(0, 0, 0, 0.8), 
          rgba(0, 0, 0, 0.8)
        ),url('https://source.unsplash.com/random/1920x1080') no-repeat center center fixed;
        -webkit-background-size: cover;
        -moz-background-size: cover;
        -o-background-size: cover;
        background-size: cover;        
      }

      body {
        align-items: center;
        display: flex;
        color: #fff;                
      }      

      .shadow {
        text-shadow: 0 .05rem .1rem rgba(0, 0, 0, .5);
        box-shadow: inset 0 0 5rem rgba(0, 0, 0, .5);
      }
    </style>
  </head>

  <body>    
    <div class="container h-100">      
      <div class="row align-items-center h-100">        
        <div class="col-6 mx-auto" align="center">      
          <h1 class="cover-heading shadow">FileUpload</h1>
          <p class="shadow">Upload and share your files directly.</p>
          <p class="shadow">          
            <form id="form" method="POST" enctype="multipart/form-data">
              <?php if (!empty($pwd)) { ?>
                <div class="form-group row">
                  <label for="inputPassword" class="col-sm-2 col-form-label">Password</label>
                  <div class="col-sm-10">
                    <input type="password" class="form-control" id="inputPassword" placeholder="Password" name="pwd">
                  </div>                          
                </div>            
              <?php } ?>
              <div class="form-group row">
                <label for="inputFile" class="col-sm-2 col-form-label">File</label>
                <div class="col-sm-10">
                  <input type="file" class="form-control-file" id="inputFile" name="file">
                  <small class="form-text" style="color:grey">Only <?php echo implode(',',$files_allowed);?> max <?php echo formatBytes($limit);?></small>
                </div>                          
              </div>                            
              <button id="btn-upload" type="submit" class="btn btn-lg btn-secondary">UPLOAD</button>          
            </form>            
          </p>     
          <?php if (!empty($msg)) { echo '<hr>';echo $msg; } ?>
        </div>        
      </div>          
    </div>    


    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js" integrity="sha384-cs/chFZiN24E4KMATLdqdvsezGxaGsi4hLGOzlXwp5UZB1LY//20VyM2taTB4QvJ" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/js/bootstrap.min.js" integrity="sha384-uefMccjFJAIv6A+rW+L4AHf99KvxDjWSu1z9VI8SKNVmz4sk7buKt/6v9KI65qnm" crossorigin="anonymous"></script>

    <script>
      $('#form').submit(function (e) {         
        $('#btn-upload').html('<i class="fas fa-spinner fa-pulse"></i>');
        $('#btn-upload').prop('disabled',true);
      });
    </script>
  </body>
</html>
