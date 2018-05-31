<div class="hidden-xs hidden-sm col-md-2" id="mainMenuCnt">
    <div id="headerClaimCnt" class="col-md-12 centerWithFlex">Snap4City</div>
    <div class="col-md-12 mainMenuUsrCnt">
        <div class="row">
            <div class="col-md-12 centerWithFlex" id="mainMenuIconCnt">
                <img src="../img/mainMenuIcons/user.ico" />
            </div>
            <div class="col-md-12 centerWithFlex" id="mainMenuUsrCnt">
                <?php echo $_SESSION['loggedUsername']; ?>
            </div>
            <div class="col-md-12 centerWithFlex" id="mainMenuUsrDetCnt">
                <?php echo $_SESSION['loggedRole'] . " | " . $_SESSION['loggedType']; ?>
            </div>
            <div class="col-md-12 centerWithFlex" id="mainMenuUsrLogoutCnt">
                Logout
            </div>
        </div>
    </div>
    

	
	<a href="../management/value.php" id="valueLink" class="internalLink moduleLink">
        <div class="col-md-12 mainMenuItemCnt">
            <i class="fa fa-plus"></i>&nbsp;&nbsp;&nbsp; Sensors&Actuators
        </div>
    </a>
	
	 <a href="../management/devices.php" id="devicesLink" class="internalLink moduleLink">
        <div class="col-md-12 mainMenuItemCnt">
            <i class="fa fa-dashboard"></i>&nbsp;&nbsp;&nbsp; Devices
        </div>
    </a>
	
	<a href="../management/contextBroker.php" id="contextBrokerLink" class="internalLink moduleLink">
        <div class="col-md-12 mainMenuItemCnt">
            <i class="fa fa-dashboard"></i>&nbsp;&nbsp;&nbsp; Context Brokers
        </div>
    </a>
	
  
	 <a href="#" id="operationLink" class="dropmenu">
        <div class="col-md-12 mainMenuItemCnt">
            <i class="fa fa-wrench"></i>&nbsp;&nbsp;&nbsp; IoT Management  
        </div>
    </a>
	
	<a href="../management/bulkUpdate.php" id="bulkDUpdateLink" class="internalLink moduleLink">
		<div id="bulkDUpdateLink" class="col-md-12 mainMenuItemCnt">
			&nbsp;&nbsp;&nbsp;<i class="fa fa-dashboard"></i>&nbsp;&nbsp;&nbsp; Update Devices  
		</div>
	</a>
	
	<a href="../management/bulkUpdate.php" id="bulkUpdateLink" class="internalLink moduleLink">
		<div id="bulkCBUpdateLink" class="col-md-12 mainMenuItemCnt">
			&nbsp;&nbsp;&nbsp;<i class="fa fa-dashboard"></i>&nbsp;&nbsp;&nbsp; Update CBs 
		</div>
	</a>
	
	
	    
    <?php
        if(isset($_SESSION['loggedRole'])&&isset($_SESSION['loggedType']))
        {
            if($_SESSION['loggedRole'] == "ToolAdmin")
            {
    ?>
                <a href="../management/setup.php" id="setupLink" class="internalLink moduleLink">
                    <div class="col-md-12 mainMenuItemCnt">
                        <i class="fa fa-cogs"></i>&nbsp;&nbsp;&nbsp;Settings
                    </div>
                </a>
    
    <?php        
            }
        }
    ?> 


	
	
	<a id="usersLink" href="#" class="dropmenu">
		<div  class="col-md-12 mainMenuItemCnt">
			<i class="fa fa-group"></i>&nbsp;&nbsp;&nbsp; Local Users
		</div>
	</a> 
	
    <?php
        if(isset($_SESSION['loggedRole'])&&isset($_SESSION['loggedType']))
        {
            if($_SESSION['loggedType'] == "local")
            {
    ?>
                <a class="internalLink moduleLink" href="../management/account.php" id="accountManagementLink">
                    <div  id="accountLink" class="col-md-12 mainMenuItemCnt">
                        &nbsp;&nbsp;&nbsp;<i class="fa fa-user"></i>&nbsp;&nbsp;&nbsp;&nbsp; Account
                    </div>
                </a>
    <?php        
            }
    ?>  
    
    <?php
            if($_SESSION['loggedRole'] == "ToolAdmin")
            {
    ?>

                <a class="internalLink moduleLink" href="../management/users.php" id="link_user_register">
                    <div id="userLink" id="userLink"class="col-md-12 mainMenuItemCnt">
                        &nbsp;&nbsp;&nbsp;<i class="fa fa-group"></i>&nbsp;&nbsp;&nbsp;&nbsp;List of Users
                    </div>
                </a> 
    <?php        
            }
    ?>
    

     
    <?php        
        }
    ?>    
       
</div>

<script type='text/javascript'>
    $(document).ready(function () 
    {
		
		$('#bulkDUpdateLink').hide();
		$('#bulkCBUpdateLink').hide();
		$('#accountLink').hide();
		$('#userLink').hide();
		
        $('div.mainMenuUsrCnt').hover(function(){
            $(this).css("background", "rgba(0, 162, 211, 1)");
            $(this).css("cursor", "pointer");
            $('#mainMenuUsrDetCnt').hide();
            $('#mainMenuUsrLogoutCnt').show();
        }, function(){
            $(this).css("background", "transparent");
            $(this).css("cursor", "normal");
            $('#mainMenuUsrLogoutCnt').hide();
            $('#mainMenuUsrDetCnt').show();
        });
		
		
		$('#operationLink').click(function(){        
         $('#bulkDUpdateLink').toggle();
		 $('#bulkCBUpdateLink').toggle();
			});
		
		$('#usersLink').click(function(){        
         $('#accountLink').toggle();
		 $('#userLink').toggle();
			});
		
        
        $('div.mainMenuUsrCnt').click(function(){
            location.href = "logout.php";
            /*$.ajax({
                url: "iframeProxy.php",
                action: "notificatorRemoteLogout",
                async: true,
                success: function()
                {

                },
                error: function(errorData)
                {
                    console.log("Remote logout from Notificator failed");
                    console.log(JSON.stringify(errorData));
                },
                complete: function()
                {
                    location.href = "logout.php";
                }
            });*/
        });
    });
</script>    


