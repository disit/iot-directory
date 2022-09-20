$.fn.modal.Constructor.prototype.enforceFocus = function () { };

var data;



$(document).ready(function () {


    // DATI
    data = {action: "get_rules_ext", token: sessionToken, should_be_registered: "no", no_columns: ["position", "edit", "delete"]};


    dataTable3 = $('#RulesContextExternalBrokerTable').DataTable({
        dom: 'Plfrtip',
        "processing": true,
        "paging": true,
        "ajax": {
            url: "../api/bulkDeviceLoad.php",
            data: data,
            //datatype: 'json',
            type: "POST"
        },

        "searchPanes": {
            "columns": [2],
            "initCollapsed": true,
            orderable: false
        },
        columnDefs: [{
                searchPanes: {
                    show: true
                },
                targets: [2]
            }],
        serverSide: true,
        select: true,

        "columns": [{
                "class": "details-control",
                "name": "position",
                "orderable": false,
                "data": null,
                "defaultContent": "",
                "render": function () {
                    return '<i class="fa fa-plus-square" aria-hidden="true" disabled ></i>';
                },
                width: "15px"
            }, {
                "name": "Name",
                "orderable": true,
                "data": function (row, type, val, meta) {
                    return  "<b>" + row.Name + "<b>";
                }
            },
            {
                "name": "mode",
                "data": "mode",                
                "render": function (row, type, val, meta) {
                    if (row == '0') {
                       
                        return 'not active';
                    } else {
                        
                        return 'active';
                    }
                }
            }, {
                "name": "Organization",
                "data": function (row, type, val, meta) {
                    return row.Organization;


                }
            },
            {
                "name": "edit",
                "orderable": false,
                className: "center",
                data: null,
                render: function (d) {
                    if(d.Organization==''){
                        Bottone='<button type="button" class="editDashBtn" ' +
                            'data-id="' + d.Name + '" ' +
                            'data-if=/"' + escape(d.If_statement) + '/" ' +
                            'data-then=/"' + escape(d.Then_statement) + '/" ' +
                            'data-mode="' + d.mode + '" ' +
                            '"">Edit</button>';
                        console.log(Bottone);
                        
                              return (Bottone);
                         }else if(d.Organization!=''){
                             Bottone= '<button type="button" class="editDashBtn" ' +
                            'data-id="' + d.Name + '" ' +
                            'data-organization="' + d.Organization + '" ' +
                            'data-if=/"' + d.If_statement + '/" ' +
                            'data-then=/"' + d.Then_statement + '/" ' +
                            'data-mode="' + d.mode + '" ' +
                            '"">Edit</button>';
                       console.log(Bottone);
                        
                              return (Bottone);
                    } else {
                        return '';
                    }

                    
                }



            }
            ,
            {
                "name": "Delete",
                "orderable": false,
                className: "center",
                data: null,

                render: function (d) {
                     if (loggedRole == 'RootAdmin' ) {
                        
                        return '<button type="button" class="delDashBtn" ' +
                            'data-id="' + d.Name + '" ' +
                            'data-organization="' + d.Organization + '" ' +
                            'data-if= /"' + d.If_statement + '/" ' +
                            'data-then= /"' + d.Then_statement + '/" ' +
                            'data-mode="' + d.mode + '" ' +
                            '"">Delete</button>';
                    } else {
                        return '';
                    }

                  
                }

            }
        ],
        "order": []
    });

    //function for edit and view rule
    function ReadOnly(block, flag) {
        //button
        $('#addifBlockBtnValue').css('display', block);
        $('#addDecisionBlockBtnValue').css('display', block);
        $('.minusDevice').css('display', block);
        $('.fa ').css('display', block);


        // field
        $(".fieldIf").prop('disabled', flag);
        $(".fieldSelectEqual").prop('disabled', flag);
        $("#Update_mode").prop('disabled', flag);
        $(".fieldThenValue").prop('disabled', flag);
        $(".fieldSelectIf").prop('disabled', flag);
        $(".fieldName").attr('readonly', flag);
        $(".fieldNameIfValue").attr('readonly', flag);





    }

    // Edit Rules
    $('#RulesContextExternalBrokerTable tbody').on('click', 'button.editDashBtn', function () {
        //show modal
        $('#updateMultipleDeviceModal').modal('show');
        $("#title_rule_form").html("<b>Edit -  </b> " + " " + $(this).attr('data-id'));
        //fit modal on rule
        RulesLoad($(this).attr('data-id'), $(this).attr('data-if'), $(this).attr('data-then'), $(this).attr('data-mode'), 'ifBlockTableValue', 'decisionBlockTableValue');

        //display block for updating
        ReadOnly('block', false);
        $('#Update_rules').show();
        //pass rule's name in the ok button
        $('#NAMERule').val($(this).attr('data-id'));


    });

//Delete rules
 $('#RulesContextExternalBrokerTable tbody').on('click', 'button.delDashBtn', function () {
      
        var id = $(this).attr('data-id');
        var dev_organization = $(this).attr('data-organization');
        var if_st = $(this).attr("data-if");
        var then_st = $(this).attr('data-then');
        var mode = $(this).attr('data-mode');
        
        $("#deleteRuleModal div.modal-body").html('<div class="modalBodyInnerDiv"><span data-id = "' + id + '" data-organization = "' + dev_organization + '"  data-if ="' + if_st + '" data-then ="' + then_st + '" data-mode="' + mode + '" >Do you want to confirm deletion of rule <b>' + id + '</b>?</span></div>');
        $("#deleteRuleModalInnerDiv1").html('<h5>Rule deletion in progress, please wait</h5>');
        $("#deleteRuleModalInnerDiv2").html('<i class="fa fa-circle-o-notch fa-spin" style="font-size:36px"></i>');
        $("#deleteRuleModalInnerDiv1").hide();
        $("#deleteRuleModalInnerDiv2").hide();
        $("#deleteRuleOkBtn").hide();
        $("#deleteRuleCancelBtn").show();
        $("#deleteRuleConfirmBtn").show();
        $("#deleteRuleModal").modal('show');
    });

  $('#deleteRuleConfirmBtn').off("click");
    $("#deleteRuleConfirmBtn").click(function () {

       var id = document.querySelector("#deleteRuleModal > div > div > div.modal-body > div > span").dataset.id ;//$(this).attr('data-id');
//        var dev_organization = $(this).attr('data-organization');
//        var if_st = $(this).attr("data-if");
//        var then_st = $(this).attr('data-then');
//        var mode = $(this).attr('data-mode');
        
        
        $("#deleteRuleModal div.modal-body").html("");
        $("#deleteRuleOkBtn").hide();
        $("#deleteRuleCancelBtn").hide();
        $("#deleteRuleConfirmBtn").hide();
        $("#deleteRuleModalInnerDiv1").show();
        $("#deleteRuleModalInnerDiv2").show();
        $.ajax({
            url: "../api/bulkDeviceLoad.php",
            data: {
                action: "delete_rules_ext",
                 token: sessionToken,
                id: id
                
            },
            type: "POST",
            datatype: "json",
            async: true,
            success: function (data)
            {
                //console.log(JSON.stringify(data));
                $("#deleteRuleOkBtn").show();
                if (data["status"] === 'ko')
                {
                    $("#deleteRuleModalInnerDiv1").html(data["error_msg"]);
                    $("#deleteRuleModalInnerDiv2").html('<i class="fa fa-frown-o" style="font-size:42px"></i>');
                } else if (data["status"] === 'ok')
                {
                    $("#deleteRuleModalInnerDiv1").html('Rule &nbsp; <b>' + id + '</b> &nbsp;deleted successfully');
                    $("#deleteRuleModalInnerDiv1").show();
                    $("#deleteRuleModalInnerDiv2").html('<i class="fa fa-check" style="font-size:42px"></i>');
                    $('#dashboardTotNumberCnt .pageSingleDataCnt').html(parseInt($('#dashboardTotNumberCnt .pageSingleDataCnt').html()) - 1);
                    
                    $('#RulesContextExternalBrokerTable').DataTable().destroy();
                    fetch_data(true);
                    
                }
            },
            error: function (data)
            {
                $("#deleteRuleOkBtn").show();
                console.log(JSON.stringify(data));
                $("#deleteRuleModalInnerDiv1").html(data["error_msg"]);
                $("#deleteRuleModalInnerDiv2").html('<i class="fa fa-frown-o" style="font-size:42px"></i>');
            }
        });
    });
    $("#deleteRuleOkBtn").off("click");
    $("#deleteRuleOkBtn").click(function () {
        $("#deleteRuleModal div.modal-body").html("Do you want to confirm deletion of the following Rule?");
        $("#deleteRuleOkBtn").hide();
        $("#deleteRuleCancelBtn").show();
        $("#deleteRuleConfirmBtn").show();
        $("#deleteRuleModalInnerDiv1").html('<h5>Device deletion in progress, please wait</h5>');
        $("#deleteRuleModalInnerDiv2").html('<i class="fa fa-circle-o-notch fa-spin" style="font-size:36px"></i>');
        $("#deleteRuleModalInnerDiv1").hide();
        $("#deleteRuleModalInnerDiv2").hide();
    });





// Update rules
    $("#Update_rules").on("click", function () {
        var num1 = document.getElementById('ifBlockTableValue').tBodies[0].childElementCount;
        var num2 = document.getElementById('decisionBlockTableValue').tBodies[0].childElementCount;

        if (num1 != 0 || num2 != 0) {

            var attributesIfValues = getIfRules2(num1);
            var attributesThenValues = getThenRules2(num2);

            if ($('#Update_mode option').filter(':selected').val() == '0') {
                var mode = '0';
            } else {
                var mode = '1';
            }

            $.ajax({
                url: "../api/bulkDeviceUpdate.php",
                data: {
                    action: "Save_device_rules",
                    attributesIf: JSON.stringify(attributesIfValues),
                    attributesThen: JSON.stringify(attributesThenValues),
                    name: $('#NAMERule').val(),
                    mode: mode,
                    update: true,
                    token: sessionToken
                },
                dataType: 'json',
                type: "POST",
                async: true,
                success: function (myData) {
                    if (myData['status'] == 'ok') {
                        $('#updateMultipleDeviceModal').modal('hide');
                        $('#updateMultipleDeviceModalConfirm').show();
                    } else if (myData['status'] == 'ko') {
                        $('#updateMultipleDeviceModal').modal('hide');
                        $('#updateMultipleDeviceModalProblem').show();

                    }
                },
                error: function (myData) {


                }
            });

        }
    });



//Titolo Default
    if (titolo_default != "") {
        $('#headerTitleCnt').text(titolo_default);
    }

    if (access_denied != "") {
        alert('You need to log in with the right credentials before to access to this page!');
    }

    ///// SHOW FRAME PARAMETER USE/////
    if (nascondi == 'hide') {
        $('#mainMenuCnt').hide();
        $('#title_row').hide();
        $('#mainCnt').removeClass('col-md-10');
        $('#mainCnt').addClass('col-md-12');
    }
    //// SHOW FRAME PARAMETER  ////

    $('#sessionExpiringPopup').css("top", parseInt($('body').height() - $('#sessionExpiringPopup').height()) + "px");
    $('#sessionExpiringPopup').css("left", parseInt($('body').width() - $('#sessionExpiringPopup').width()) + "px");

    setInterval(function () {
        var now = parseInt(new Date().getTime() / 1000);
        var difference = sessionEndTime - now;

        if (difference === 300) {
            $('#sessionExpiringPopupTime').html("5 minutes");
            $('#sessionExpiringPopup').show();
            $('#sessionExpiringPopup').css("opacity", "1");
            setTimeout(function () {
                $('#sessionExpiringPopup').css("opacity", "0");
                setTimeout(function () {
                    $('#sessionExpiringPopup').hide();
                }, 1000);
            }, 4000);
        }

        if (difference === 120) {
            $('#sessionExpiringPopupTime').html("2 minutes");
            $('#sessionExpiringPopup').show();
            $('#sessionExpiringPopup').css("opacity", "1");
            setTimeout(function () {
                $('#sessionExpiringPopup').css("opacity", "0");
                setTimeout(function () {
                    $('#sessionExpiringPopup').hide();
                }, 1000);
            }, 4000);
        }

        if ((difference > 0) && (difference <= 60)) {
            $('#sessionExpiringPopup').show();
            $('#sessionExpiringPopup').css("opacity", "1");
            $('#sessionExpiringPopupTime').html(difference + " seconds");
        }

        if (difference <= 0) {
            location.href = "logout.php?sessionExpired=true";
        }
    }, 1000);

    $('#mainContentCnt').height($('#mainMenuCnt').height() - $('#headerTitleCnt').height());

    $(window).resize(function () {
        $('#mainContentCnt').height($('#mainMenuCnt').height() - $('#headerTitleCnt').height());
    });

});