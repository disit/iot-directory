<?php
session_start();
if (isset($_SESSION["user"]))
$connected = 1;
else
$connected = 0;

?>


<!DOCTYPE html>
<html lang="en">

<?php include_once "utility/head.php";?>
		

<body>


 <?php  include_once "utility/navigation.php";?> 

    <!-- Navigation -->

    <!-- Page Content -->
    <div class="container">

        

      
	 <?php 
	 if ($connected)
	 {
	 ?>
	  <div class="row">
            <!-- Sidebar Column -->
           <div class="col-md-2 sm-2">
                <div class="imgcontainer">
                <img src="img/user.png" alt="" class="avatar">
				<div><?php echo $_SESSION["name"];?> <strong><?php echo $_SESSION["lastname"];?></strong><br/>
				      <?php echo $_SESSION["role"]; ?><br/>
					 
				</div>
				</div>
				
				<?php  if (isset($_SESSION["cb"])) {?>
				<div class="imgcontainer">
                <img style="height: 70px;width:70px;" src="img/<?php echo $_SESSION["protocol"];?>.png" alt="" class="avatar">
				<?php echo $_SESSION["cb"];?><br/>
				<strong><?php echo $_SESSION["ip"];?></strong><br/>

					 <button class="btn btn-success btn-circle" onclick="return concludiCB();">close</a>
					 
				</div>	
				<?php } ?>
               
            </div>
            <!-- Content Column -->
          <div class="col-md-10 sm-4"> 
                       
			        <div class="panel panel-default">
                        
                     <?php if (isset($_GET['op'])) 					 
					      
						  include_once "php/" . $_GET["op"] . ".php";
					
					  ?>   
					</div>
			</div>
	 
	 
	 <?php
	 }
	 else
	 {
	 ?>
	 
    <header>
        <div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel">
            <ol class="carousel-indicators">
                <li data-target="#carouselExampleIndicators" data-slide-to="0" class="active"></li>
                <li data-target="#carouselExampleIndicators" data-slide-to="1"></li>
                <li data-target="#carouselExampleIndicators" data-slide-to="2"></li>
				<li data-target="#carouselExampleIndicators" data-slide-to="3"></li>
            </ol>
            <div class="carousel-inner" role="listbox">
                <!-- Slide One - Set the background image for this slide in the line below -->
                <div class="carousel-item active" style="background-size: contain;
background-image: url('./img/select4cities.png')">

                    <!--<div class="carousel-caption d-none d-md-block">
                        <h3>First Slide</h3>
                        <p>This is a description for the first slide.</p>
                    </div>-->
                </div>
                <!-- Slide Two - Set the background image for this slide in the line below -->
                <div class="carousel-item" style="background-size: contain;background-image: url('./img/disit.jpg')">
                    <!--<div class="carousel-caption d-none d-md-block">
                        <h3>Second Slide</h3>
                        <p>This is a description for the second slide.</p>
                    </div>-->
                </div>
                <!-- Slide Three - Set the background image for this slide in the line below -->
                <div class="carousel-item" style="background-size: contain;background-image: url('./img/unimi.png')">
                   <!-- <div class="carousel-caption d-none d-md-block">
                        <h3>Third Slide</h3>
                        <p>This is a description for the third slide.</p>
                    </div>-->
                </div>
				<div class="carousel-item" style="background-size: contain;background-image: url('./img/logoSnap4City.png')">
                   <!-- <div class="carousel-caption d-none d-md-block">
                        <h3>Fourth Slide</h3>
                        <p>This is a description for the fourth slide.</p>
                    </div>-->
                </div>
            </div>
            <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="sr-only">Previous</span>
            </a>
            <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="sr-only">Next</span>
            </a>
        </div>
    </header>
	 
	 
	 
    <?php	 
	 }
	 ?> 
	 
    </div> 	 
    </div>
    <!-- /.container -->

    <!-- Footer -->
  <?php include_once "utility/footer.php";?>


</body>

</html>
