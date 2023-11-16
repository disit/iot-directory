
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
   include('../config.php');
?>

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
 		 	
	<?php
		
		$q = "SELECT * FROM mainmenu  WHERE active=1 ORDER BY id;";
		
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
						\t <div class=\"col-md-12 mainMenuItemCnt\">\r
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

<script type='text/javascript'>
    $(document).ready(function () 
    {	
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
		
        
        $('div.mainMenuUsrCnt').click(function(){
            location.href = "logout.php";
        });
    });
</script>    


