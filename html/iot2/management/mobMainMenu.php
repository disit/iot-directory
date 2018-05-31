<?php
/* Snap4City: IoT-Directory
   Copyright (C) 2017 DISIT Lab https://www.disit.org - University of Florence

   This program is free software; you can redistribute it and/or
   modify it under the terms of the GNU General Public License
   as published by the Free Software Foundation; either version 2
   of the License, or (at your option) any later version.
   This program is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.
   You should have received a copy of the GNU General Public License
   along with this program; if not, write to the Free Software
   Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA. */


$link = mysqli_connect($host, $username, $password) or die("failed to connect to server !!");
mysqli_select_db($link, $dbname);

//Altrimenti restituisce in output le warning
error_reporting(E_ERROR | E_NOTICE);   
   
?>

<i id="mobMainMenuBtn" data-shown="false" class="fa fa-navicon"></i>

<div id="mobMainMenuCnt">
    <div id="mobMainMenuPortraitCnt">
        <div class="row">
            <div class="col-xs-12 centerWithFlex" id="mobMainMenuIconCnt">
                <img src="../img/mainMenuIcons/user.ico" />
            </div>
            <div class="col-xs-12 centerWithFlex" id="mobMainMenuUsrCnt">
                <?php echo $_SESSION['loggedUsername']; ?>
            </div>
            <div class="col-xs-12 centerWithFlex" id="mobMainMenuUsrDetCnt">
                <?php echo $_SESSION['loggedRole'] . " | " . $_SESSION['loggedType']; ?>
            </div>
            <div class="col-xs-12 centerWithFlex" id="mobMainMenuUsrLogoutCnt">
                <button type="button" id="mobMainMenuUsrLogoutBtn" class="editDashBtn">logout</button>
            </div>
        </div>
        <hr>
		
		
	<?php
		
		$q = "SELECT * FROM mainmenu  WHERE active=1 ORDER BY id;";
		
		$r = mysqli_query($link, $q);
		if($r) 
		{
           while($row = mysqli_fetch_assoc($r)) 
           {
		     $privileges=json_decode($row["privileges"]);
			 if (in_array($_SESSION['loggedRole'],$privileges))
			 {
			   echo "<a href=\"$row[linkUrl]\" id=\"$row[linkId]\" class=\"internalLink moduleLink\">\r
			\t <div class=\"col-xs-12 mobMainMenuItemCnt\">\r
			 \t\t    <i class=\"$row[icon]\" style=\"color:$row[iconColor]\"></i>\r
			 \t\t	&nbsp;&nbsp;&nbsp;$row[text]\r
			 \t </div>\r
			 </a>\r";		
	          } // close if on sessionRole
           } // close while		   
		} // close if
		else {echo  mysqli_error($link);}
		?>
		
	
	
      
    </div>
	
	
    
    <div id="mobMainMenuLandCnt">
        <div class="row">
            <div class="col-xs-4 centerWithFlex" id="mobMainMenuUsrCnt">
                <img src="../img/mainMenuIcons/user.ico" />&nbsp;&nbsp;<?php echo $_SESSION['loggedUsername']; ?>
            </div>
            <div class="col-xs-4 centerWithFlex" id="mobMainMenuUsrDetCnt">
                <?php echo $_SESSION['loggedRole'] . " | " . $_SESSION['loggedType']; ?>
            </div>
            <div class="col-xs-4 centerWithFlex" id="mobMainMenuUsrLogoutCnt">
                <button type="button" id="mobMainMenuUsrLogoutBtn" class="editDashBtn">logout</button>
            </div>
        </div>
	

<?php
		
		$q = "SELECT * FROM mainmenu WHERE active=1 ORDER BY id;";
		
		$r = mysqli_query($link, $q);
		if($r) 
		{
           while($row = mysqli_fetch_assoc($r)) 
           {
		    // echo $row["privileges"];
		     $privileges=json_decode($row["privileges"]);
			// print_r($privileges);
			 if (in_array($_SESSION['loggedRole'],$privileges))
			 {
			    echo "<a href=\"$row[linkUrl]\" id=\"$row[linkId]\" class=\"internalLink moduleLink\">\r
			\t <div class=\"col-xs-4 mobMainMenuItemCnt\">\r
			 \t\t    <i class=\"$row[icon]\" style=\"color:$row[iconColor]\"></i>\r
			 \t\t	&nbsp;&nbsp;&nbsp;$row[text]\r
			 \t </div>\r
			 </a>\r";		
	          } // close if on sessionRole
           } // close while		   
		} // close if
		else {echo  mysqli_error($link);}
		?>       
    </div>  
</div>

<script type='text/javascript'>
    $(document).ready(function () 
    {
        $('#mobMainMenuCnt').css("top", parseInt($('#mobHeaderClaimCnt').height() + $('#headerMenuCnt').height()) + "px");
        
        $( window ).on( "orientationchange", function( event ) {
            if($('#mobMainMenuCnt').is(':visible'))
            {
                if($(window).width() < $(window).height())
                {
                    $('#mobMainMenuPortraitCnt').hide();
                    $('#mobMainMenuLandCnt').show();
                }
                else
                {
                    $('#mobMainMenuLandCnt').hide();
                    $('#mobMainMenuPortraitCnt').show();
                }
            }
        });
        
        $('#mobMainMenuBtn').parent().click(function(){
            if($('#mobMainMenuBtn').attr("data-shown") === "false")
            {
                $('#mobMainMenuCnt').show();
                if($(window).width() < $(window).height())
                {
                    $('#mobMainMenuLandCnt').hide();
                    $('#mobMainMenuPortraitCnt').show();
                }
                else
                {
                    $('#mobMainMenuPortraitCnt').hide();
                    $('#mobMainMenuLandCnt').show();
                }
                
                
                $('#mobMainMenuBtn').attr("data-shown", "true");
                setTimeout(function(){
                    $('#mobMainMenuCnt').css("opacity", "1");
                }, 50);
            }
            else
            {
                
                $('#mobMainMenuCnt').css("opacity", "0");
                $('#mobMainMenuBtn').attr("data-shown", "false");
                setTimeout(function(){
                    $('#mobMainMenuCnt').hide();
                }, 350);
            }
        });
		
        $('#mobMainMenuPortraitCnt #mobMainMenuUsrLogoutBtn').click(function(){
            location.href = "logout.php";
        });
        
        $('#mobMainMenuLandCnt #mobMainMenuUsrLogoutBtn').click(function(){
            location.href = "logout.php";
        });
    });
</script>    

