    <nav class="navbar fixed-top navbar-toggleable-md navbar-inverse bg-inverse">
        <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarExample" aria-controls="navbarExample" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="container">
            <a class="navbar-brand" href="index.php">Snap4City IoT Directory</a>
            <div class="collapse navbar-collapse" id="navbarExample">
                <ul class="navbar-nav ml-auto">
                    <!-- <li class="nav-item">
                        <a class="nav-link" href="about.php">about</a>
                    </li>-->
                    
					
					<?php if ($connected && isset($_SESSION["cb"])) { ?>
					<li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarSedute" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            New devices
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarSedute">
							<a class="dropdown-item" href="index.php?op=listSensors&status=unmapped">Show devices</a>
                        </div>
                    </li>
					
					<li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarSedute" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Registered devices
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarSedute">
							<a class="dropdown-item" href="index.php?op=listSensors&status=mapped">Show devices</a>
							
							<a class="dropdown-item" href="index.php?op=createSensor">New device</a>
                        </div>
                    </li>		
					<?php } ?>
					
					
					
					<?php if ($connected && !isset($_SESSION["cb"])) { ?>
					<li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarPazienti" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Context Brokers
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarPazienti">
                            <a class="dropdown-item" href="index.php?op=cb">Show</a>
							<a class="dropdown-item" href="index.php?op=createCB">Create new one</a>
							
                        </div>
                    </li>
					<?php } ?>
					
                    <li class="nav-item dropdown">
					<a class="nav-link dropdown-toggle" href="#" id="navbarLogin" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            User
                        </a>
					<div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarLogin">
                            <?php if ($connected) { ?>
							<!-- <a class="dropdown-item" href="#">Profilo</a> -->
                            <a class="dropdown-item" href="logout.php">Logout</a>
							<?php } else { ?>
                            <a class="dropdown-item" href="login.php">Login</a>
                            <!-- <a class="dropdown-item" href="registration.php">Registration</a> -->
							<?php } // end if ?>
                        </div>

					</li>
                </ul>
            </div>
        </div>
    </nav>

