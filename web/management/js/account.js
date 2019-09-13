    $(document).ready(function () 
    {
       var sessionEndTime = "<?php echo $_SESSION['sessionEndTime']; ?>";
        $('#sessionExpiringPopup').css("top", parseInt($('body').height() - $('#sessionExpiringPopup').height()) + "px");
        $('#sessionExpiringPopup').css("left", parseInt($('body').width() - $('#sessionExpiringPopup').width()) + "px");
        
        setInterval(function(){
            var now = parseInt(new Date().getTime() / 1000);
            var difference = sessionEndTime - now;
            
            if(difference === 300)
            {
                $('#sessionExpiringPopupTime').html("5 minutes");
                $('#sessionExpiringPopup').show();
                $('#sessionExpiringPopup').css("opacity", "1");
                setTimeout(function(){
                    $('#sessionExpiringPopup').css("opacity", "0");
                    setTimeout(function(){
                        $('#sessionExpiringPopup').hide();
                    }, 1000);
                }, 4000);
            }
            
            if(difference === 120)
            {
                $('#sessionExpiringPopupTime').html("2 minutes");
                $('#sessionExpiringPopup').show();
                $('#sessionExpiringPopup').css("opacity", "1");
                setTimeout(function(){
                    $('#sessionExpiringPopup').css("opacity", "0");
                    setTimeout(function(){
                        $('#sessionExpiringPopup').hide();
                    }, 1000);
                }, 4000);
            }
            
            if((difference > 0)&&(difference <= 60))
            {
                $('#sessionExpiringPopup').show();
                $('#sessionExpiringPopup').css("opacity", "1");
                $('#sessionExpiringPopupTime').html(difference + " seconds");
            }
            
            if(difference <= 0)
            {
                //console.log("Logout");
                location.href = "logout.php?sessionExpired=true";
            }
            /*else
            {
                console.log("Keep in");
            }*/
        }, 1000);
        
       $('#mainContentCnt').height($('#mainMenuCnt').height() - $('#headerTitleCnt').height());
        
        $(window).resize(function(){
            $('#mainContentCnt').height($('#mainMenuCnt').height() - $('#headerTitleCnt').height());
        });
        
        $('#accountManagementLink .mainMenuItemCnt').addClass("mainMenuItemCntActive");
        $('#mobMainMenuPortraitCnt #accountManagementLink .mobMainMenuItemCnt').addClass("mainMenuItemCntActive");
        $('#mobMainMenuLandCnt #accountManagementLink .mobMainMenuItemCnt').addClass("mainMenuItemCntActive");
        
        
       editAccountPageSetup();
       
       $('#editAccountCancelBtn').off("click");
       $("#editAccountCancelBtn").click(function(){
          $("#accountFirstName").val($("#accountFirstName").attr("data-originalvalue"));
          $("#accountLastName").val($("#accountLastName").attr("data-originalvalue"));
          $("#accountOrganization").val($("#accountOrganization").attr("data-originalvalue"));
          $("#accountEmail").val($("#accountEmail").attr("data-originalvalue"));
          $("#accountPassword").val($("#accountPassword").attr("data-originalvalue"));
          $("#accountPasswordConfirmation").val("");
          $("#editAccountConfirmBtn").attr("disabled", true);
          editAccountPageSetup();
       });
       
       $('#editAccountConfirmBtn').off("click");
       $("#editAccountConfirmBtn").click(function(){
          editAccount("<?php echo $_SESSION['loggedUsername'] ?>");
       });
       
    });//Fine document ready
