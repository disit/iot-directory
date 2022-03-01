$.fn.modal.Constructor.prototype.enforceFocus = function () { };

var data;



$(document).ready(function () {


    // DATI
    data = {action: "get_rules_ext", token: sessionToken, should_be_registered: "no", no_columns: ["position", "edit"]};


    dataTable3 = $('#RulesContextExternalBrokerTable').DataTable({
        dom: 'Plfrtip',
        "processing": true,

        "responsive": {
            details: false
        },
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
            orderable: false,
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

                    return '<button type="button" class="editDashBtn" ' +
                            'data-id="' + d.Name + '" ' +
                            'data-organization="' + d.Organization + '" ' +
                            'data-if=/"' + d.If_statement + '/" ' +
                            'data-then=/"' + d.Then_statement + '/" ' +
                            'data-mode="' + d.mode + '" ' +
                            '"">Edit</button>';
                }



            }
//            ,
//            {
//                "name": "ViewRule",
//                "orderable": false,
//                className: "center",
//                data: null,
//
//                render: function (d) {
//
//                    return '<button type="button" class="viewDashBtn" ' +
//                            'data-id="' + d.Name + '" ' +
//                            'data-organization="' + d.Organization + '" ' +
//                            'data-if= /"' + d.If_statement + '/" ' +
//                            'data-then= /"' + d.Then_statement + '/" ' +
//                            'data-mode="' + d.mode + '" ' +
//                            '"">View</button>';
//                }
//
//            }
        ],
        "order": []
    });

    //function for edit and view rule
    function ReadOnly(block, flag) {
        //button
        $('#addifBlockBtn').css('display', block);
        $('#addDecisionBlockBtn').css('display', block);
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
        $("#title_rule_form").html("<b>Edit -  </b>" + $(this).attr('data-id'));
        //fit modal on rule
        RulesLoad($(this).attr('data-id'), $(this).attr('data-if'), $(this).attr('data-then'), $(this).attr('data-mode'), 'ifBlockTable', 'decisionBlockTable');

        //display block for updating
        ReadOnly('block', false);
        $('#Update_rules').show();
        //pass rule's name in the ok button
        $('#NAMERule').val($(this).attr('data-id'));


    });

// View rules
//    $('#RulesContextExternalBrokerTable tbody').on('click', 'button.viewDashBtn', function () {
//        //show modal
//        $('#updateMultipleDeviceModal').modal('show');
//        $("#title_rule_form").html("<b>View - </b>" + $(this).attr('data-id'));
//
//        //fit modal on rule
//        RulesLoad($(this).attr('data-id'), $(this).attr('data-if'), $(this).attr('data-then'), $(this).attr('data-mode'));
//        
//         //only read mode
//        ReadOnly('none', true);
//        $('#Update_rules').hide();
//   
//    });

// Update rules
    $("#Update_rules").on("click", function () {
        var num1 = document.getElementById('ifBlockTable').tBodies[0].childElementCount;
        var num2 = document.getElementById('decisionBlockTable').tBodies[0].childElementCount;

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