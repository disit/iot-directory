<?php 

include_once "./utility/connect.php";

$errore= NULL;
if (isset($_POST["reg"]))
{
  $reg=1;
  $name = trim($_POST["name"]);
  $email = trim($_POST["email"]);
  $ist = trim($_POST["organization"]);
  $pass1 = trim($_POST["pwd1"]);
  $pass2 = trim($_POST["pwd2"]);
    
  if (empty($name))
        $errore= $errore . "The name field is not specified";
		
  if (empty($ist))
        $errore= $errore . "The organization field is not specified";

  if (empty($pass1))
        $errore= $errore . "The password field is not specified";

  
  if (!filter_var($email, FILTER_VALIDATE_EMAIL))
   $errore= $errore . "The email address does not follow the usual pattern";

  if ($pass1 != $pass2)
     $errore= $errore . "The two passords are different";

   if (!$errore)
   {
     $cid= connect();
	 
	 if ($cid->errno)	 $errore= $errore . "<p>Impossible to execute the query $sql.</p>"
						. "<p>Error code " . $cid->errno  . ": " . $cid->error . "</p>";
	
	 else
	 {
		$sql= "INSERT INTO `users`(`name`,`email`,`institution`,`password`) VALUES ('$name','$email','$ist','$pass1')";
	 
		$res = $cid->query($sql);

		if (!$res)	 $errore= $errore . "<p>Impossible to execute the query $sql.</p>"
						. "<p>Error code " . $cid->errno  . ": " . $cid->error . "</p>";
			
		disconnect($cid);
	}	
   }     
}
else 
{
  $name = "";
  $email = "";
  $ist = "";
  $pass1 = "";
  $pass2 = "";
}

?>

<!DOCTYPE html>
<html>

<?php include_once "utility/head.php";
$connected=0;?>

<body>
    <!--  wrapper -->
    <div id="wrapper">
        <!-- navbar top -->
        
		<?php include_once "utility/top-menu.php";?>
		
		<!-- end navbar top -->

        <!-- navbar side -->
        <?php include_once "utility/side-menu.php";?>
		
		<!-- end navbar side -->
        <!--  page-wrapper -->
        <div id="page-wrapper">
			<div class="row">
                 <!-- page header -->
                <div class="col-lg-12">
                    <h1 class="page-header">Registration</h1>
                </div>
                <!--end page header -->
            </div>

			<?php 
               if (isset($_POST["reg"]) && !$errore)
               {

			?>
			    <div class="row">
                <div class="col-lg-12">
                    <!-- Form Elements -->
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            The registration is ok.  Please log in!
                        </div>
					</div>
				</div>
				</div>	
            <?php 
               }else // 1
               {
			?>
			<div class="row">
                <div class="col-lg-12">
                    <!-- Form Elements -->
                    <div class="panel panel-default">
                      <!--  <div class="panel-heading">
                            Load User-defined Network
                        </div> -->
                        <div class="panel-body">

				
				
					<form role="form" method="POST" action="register.php">
					
					<div class="form-group">
                        <label>First and Last Name</label>
                        <input class="form-control" type = "text"  name = "name" value="<?php echo $name;?>">
                    </div>
					
					<div class="form-group">
                        <label>Email</label>
                        <input class="form-control" type = "text"  name = "email" value="<?php echo $email;?>">
                    </div>
					
					<div class="form-group">
                        <label>Organization</label>
                        <input class="form-control" type = "text"  name = "organization" value="<?php echo $ist;?>">
                    </div>
					
					<div class="form-group">
                        <label>Password</label>
                        <input class="form-control" type = "password"  name = "pwd1" >
                    </div>
					
					<div class="form-group">
                        <label>Password (again)</label>
                        <input class="form-control" type = "password"  name = "pwd2">
                    </div>
					
					<?php if ($errore)  {?>
					
					<div class="form-group has-error">
                        <label class="control-label" for="inputError">Input with error</label>
						<label class="control-label" for="inputError"><?php echo $errore;?></label>
						</div>
					
					<?php } // fine if ?>
					
					
					<input type = "hidden"  name = "reg" value="1">
					<button type="submit" class="btn btn-primary">Submit </button>
                    <button type="reset" class="btn btn-success">Reset </button>
						
					</form>		
						
						</div>
					</div>
				</div>
			</div>
											
        </div>
		
		<?php 
              } // end else 1
			?>
        <!-- end page-wrapper -->

    </div>
    <!-- end wrapper -->

    <!-- Core Scripts - Include with every page -->
    <script src="assets/plugins/jquery-1.10.2.js"></script>
    <script src="assets/plugins/bootstrap/bootstrap.min.js"></script>
    <script src="assets/plugins/metisMenu/jquery.metisMenu.js"></script>
    <script src="assets/plugins/pace/pace.js"></script>
    <script src="assets/scripts/siminta.js"></script>
    <!-- Page-Level Plugin Scripts-->
    <script src="assets/plugins/morris/raphael-2.1.0.min.js"></script>
    <script src="assets/plugins/morris/morris.js"></script>
    <script src="assets/scripts/dashboard-demo.js"></script>

</body>

</html>
