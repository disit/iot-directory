//var dataTable = "";
var _serviceIP = "..";

function ajaxRequest() {
    var request = false;
    try {
        request = new XMLHttpRequest()
    } catch (e1) {
        try {
            request = new ActiveXObject("Msxml2.XMLHTTP")
        } catch (e2) {
            try {
                request = new ActiveXObject("Microsoft.XMLHTTP")
            } catch (e3) {
                request = false
            }
        }
    }
    return request
}

function activateStub(button, cb, ip, port, protocol)
{
    var data = "contextbroker=" + cb + "&ip=" + ip + "&port=" + port;
    var service = _serviceIP + "/stub/" + protocol;
    var xhr = ajaxRequest();

    xhr.addEventListener("readystatechange", function () {
        if (this.readyState === 4 && this.status == 200) {
            $(button).css('border', '2px solid green');
            //$(button).addClass("btn btn-success btn-circle");
            // $("#" + cb + " > i").removeClass();
            // $("#" + cb + " > i").addClass("fa fa-cogs");
            $(button).prop('onclick', null).off('click');
        }
    });

    xhr.open("POST", service);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send(data);
    return true;
}

function aggiornaStub()
{
    var service = _serviceIP + "/api/status";
    var xhr = ajaxRequest();
    xhr.addEventListener("readystatechange", function () {
        if (this.readyState === 4 && this.status == 200) {
            var activeStub = JSON.parse(JSON.parse(this.responseText).message);
            for (var i = 0; i < activeStub.length; i++)
            {
                var cb = activeStub[i];
                $("#" + cb).css('border', '2px solid green');
                $("#" + cb).prop('onclick', null).off('click');
            }
        }
    });
    xhr.open("GET", service);
    // xhr.setRequestHeader("Cache-Control", "no-cache");
    //xhr.send();
    return true;
}

function format(d) {
//     return '<div class="container-fluid">' +
//            '<div class="row">' +
//            '<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><b>Broker URI:</b>' + "  " + d.accesslink + '</div>' +
//            '<div class="clearfix visible-xs"></div>' +
//            '<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><b>Broker Port:</b>' + "  " + d.accessport + '</div>' +
//            '</div>' +
//            '<div class="row">' +
//            '<div class="col-xs-6 col-sm-6" style="background-color:#D6CADD;"><b>Latitude:</b>' + "  " + d.latitude + '</div>' +
//            '<div class="clearfix visible-xs"></div>' +
//            '<div class="col-xs-6 col-sm-6" style="background-color:#D6CADD;"><b>Longitude:</b>' + "  " + d.longitude + '</div>' +
//            '</div>' +
//            '<div class="row">' +
//            '<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><b>Login:</b>' + "  " + d.login + '</div>' +
//            '<div class="clearfix visible-xs"></div>' +
//            '<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><b>Password:</b>' + "  " + d.password + '</div>' +
//            '</div>' +            
//            '<div class="row">' +
//            '<div class="col-xs-6 col-sm-6" style="background-color:#D6CADD;"><b>SHA:</b>' + "  " + d.sha + '</div>' +
//            '<div class="clearfix visible-xs"></div>' +
//            '<div class="col-xs-6 col-sm-6" style="background-color:#D6CADD;"><b>Version:</b>' + "  " + d.version + '</div>' +     
//             '</div>' +            
//              
//            
//            '<div class="row">' +
//            '<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><b>Orion version:</b>' + "  " + '<span id=\"Version_api\" class=\"rsvp\" ></span>' +  '</div>' +
//            '<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><b>Orion status:</b>' + "  " + '<span id=\"Version_status\" class=\"rsvp\" ></span>' + 
//            '<button id="Update_version_CB" class="btn btn-info my-small-button pull-right" type="button" data-org="' + d.organization + '"  data-name="' + d.name + '" onclick="UpDateOrion()">TRY UPDATE</button></div>' +
//            '</div>' +  
//            '</div>' +
//            '</div>' +
//            '</div>' +
//            '</div>';

    if (d.dynamic == true) {
        return '<div class="container-fluid">' +
                '<div class="row">' +
                '<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><b>Broker URI:</b>' + "  " + d.accesslink + '</div>' +
                '<div class="clearfix visible-xs"></div>' +
                '<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><b>Broker Port:</b>' + "  " + d.accessport + '</div>' +
                '</div>' +
                '<div class="row">' +
                '<div class="col-xs-6 col-sm-6" style="background-color:#D6CADD;"><b>Latitude:</b>' + "  " + d.latitude + '</div>' +
                '<div class="clearfix visible-xs"></div>' +
                '<div class="col-xs-6 col-sm-6" style="background-color:#D6CADD;"><b>Longitude:</b>' + "  " + d.longitude + '</div>' +
                '</div>' +
                '<div class="row">' +
                '<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><b>Login:</b>' + "  " + d.login + '</div>' +
                '<div class="clearfix visible-xs"></div>' +
                '<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><b>Password:</b>' + "  " + d.password + '</div>' +
                '</div>' +
                '<div class="row">' +
                '<div class="col-xs-6 col-sm-6" style="background-color:#D6CADD;"><b>SHA:</b>' + "  " + d.sha + '</div>' +
                '<div class="clearfix visible-xs"></div>' +
                '<div class="col-xs-6 col-sm-6" style="background-color:#D6CADD;"><b>Version:</b>' + "  " + d.version + '</div>' +
                '<div class="row">' +
                '<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><b>Orion version:</b>' + "  " + '<span id=\"Version_api\" class=\"rsvp\" ></span>' + '</div>' +
                '<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><b>Orion status:</b>' + "  " + '<span id=\"Version_status\" class=\"rsvp\" ></span>' +
                '<button id="Update_version_CB" class="btn btn-info my-small-button pull-right" type="button" data-org="' + d.organization + '"  data-name="' + d.name + '" onclick="UpDateOrion()">UPDATE</button></div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>';
    } else if (d.dynamic == false) {
        return '<div class="container-fluid">' +
                '<div class="row">' +
                '<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><b>Broker URI:</b>' + "  " + d.accesslink + '</div>' +
                '<div class="clearfix visible-xs"></div>' +
                '<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><b>Broker Port:</b>' + "  " + d.accessport + '</div>' +
                '</div>' +
                '<div class="row">' +
                '<div class="col-xs-6 col-sm-6" style="background-color:#D6CADD;"><b>Latitude:</b>' + "  " + d.latitude + '</div>' +
                '<div class="clearfix visible-xs"></div>' +
                '<div class="col-xs-6 col-sm-6" style="background-color:#D6CADD;"><b>Longitude:</b>' + "  " + d.longitude + '</div>' +
                '</div>' +
                '<div class="row">' +
                '<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><b>Login:</b>' + "  " + d.login + '</div>' +
                '<div class="clearfix visible-xs"></div>' +
                '<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><b>Password:</b>' + "  " + d.password + '</div>' +
                '</div>' +
                '<div class="row">' +
                '<div class="col-xs-6 col-sm-6" style="background-color:#D6CADD;"><b>SHA:</b>' + "  " + d.sha + '</div>' +
                '<div class="clearfix visible-xs"></div>' +
                '<div class="col-xs-6 col-sm-6" style="background-color:#D6CADD;"><b>Version:</b>' + "  " + d.version + '</div>' +
                '<div class="row">' +
                '</div>' +
                '</div>' +
                '</div>';
    }

    // `d` is the original data object for the row

}

function UpDateOrion() {
    var name = $('#Update_version_CB').attr('data-name');
    var org = $('#Update_version_CB').attr('data-org');

    $("#UpdatingContextBrokerModal").modal('show');


    $.ajax({
        url: "../api/contextbroker.php",
        data: {
            action: "update_orion",
            token: sessionToken,
            name: name,
            org: org
        },
        type: "POST",
        async: true,
        success: function (result)
        {//console.log(result);
            //  $('#Version_status').val(result["Upstatus"]["status"]);
            if (result["status"] === 'ko')
            {

                console.log("Error in the connection with the orion context broker.");

            } else {

                console.log("Change status for the orion context broker.");

            }
        }
    });

    console.log(name + org);
}

function fetch_data(destroyOld, selected = null) {


    if (destroyOld) {
        $('#contextBrokerTable').DataTable().clear().destroy();
        tableFirstLoad = true;
    }

    if (selected == null) {
        mydata = {action: "get_all_contextbroker", token: sessionToken, no_columns: ["position", "owner", "edit", "delete", "goto", "check"]};
    } else {
        mydata = {action: "get_all_contextbroker", token: sessionToken, select: selected, no_columns: ["position", "owner", "edit", "delete", "goto", "check"]};
    }

    dataTable = $('#contextBrokerTable').DataTable({

        "processing": true,
        
        "serverSide": true,
        scrollX: true,
        "paging": true,
        "ajax": {
            url: "../api/contextbroker.php",
            data: mydata,
            datatype: 'json',
            type: "POST"
        },
        "columns": [{
                "class": "details-control",
                "name": "position",
                "orderable": false,
                "data": null,
                "defaultContent": "",
                "render": function () {
                    return '<i class="fa fa-plus-square" aria-hidden="true"></i>';
                }
            }, {
                "name": "name",
                "data": function (row, type, val, meta) {
                    return row.name;
                }
            }, {
                "name": "accesslink",
                "data": function (row, type, val, meta) {
                    return row.accesslink;
                }
            }, {
                "name": "accessport",
                "data": function (row, type, val, meta) {
                    return row.accessport;
                }
            }, {
                "name": "kind",
                "data": function (row, type, val, meta) {
                    return row.kind;
                }
            }, {
                "name": "protocol",
                "data": function (row, type, val, meta) {
                    return row.protocol;
                }
            }, {
                "name": "visibility",
                "data": function (row, type, val, meta) {
                    if (row.visibility == 'MyOwnPrivate') {
                        return '<button type="button"  class=\"myOwnPrivateBtn\" onclick="changeVisibility(\'' +
                                row.name + '\',\'' + row.visibility + '\',\'' + row.organization + '\',\'' + row.accesslink + '\')">' + row.visibility + '</button>';
                    } else if (row.visibility == 'MyOwnPublic') {
                        return '<button type="button"  class=\"myOwnPublicBtn\" onclick="changeVisibility(\'' +
                                row.name + '\',\'' + row.visibility + '\',\'' + row.organization + '\',\'' + row.accesslink + '\')">' + row.visibility + '</button>';
                    } else if (row.visibility == 'public')
                    {
                        return '<button type="button"  class=\"publicBtn\" >' + row.visibility + '</button>';
                    } else // value is private
                    {
                        return "<div class=\"delegatedBtn\">" + row.visibility + "</div>";
                    }
               
             return '';
                }
            }, {
                "name": "organization",
                "data": function (row, type, val, meta) {
                    return row.organization;
                }
            }, {
                "name": "owner",
                "data": function (row, type, val, meta) {
                    return row.owner;
                }
            }, {
                "name": "created",
                "data": function (row, type, val, meta) {
                    return row.created;
                }
            }, {
                data: null,
                "name": "edit",
                "orderable": false,
                className: "center",
                render: function (d) {
                    if (loggedRole == 'RootAdmin' || d.visibility == 'MyOwnPrivate' || d.visibility == 'MyOwnPublic') {
                        return '<button type="button" class="editDashBtn" ' +
                                'data-name="' + d.name + '" ' +
                                'data-organization="' + d.organization + '" ' +
                                'data-kind="' + d.kind + '" ' +
                                'data-ip="' + d.ip + '" ' +
                                'data-protocol="' + d.protocol + '" ' +
                                'data-version="' + d.version + '" ' +
                                'data-port="' + d.port + '" ' +
                                'data-uri="' + d.uri + '" ' +
                                'data-created="' + d.created + '" ' +
                                'data-visibility="' + d.visibility + '" ' +
                                'data-longitude="' + d.longitude + '" ' +
                                'data-latitude="' + d.latitude + '" ' +
                                'data-login="' + d.login + '" ' +
                                'data-password="' + d.password + '" ' +
                                'data-accesslink="' + d.accesslink + '" ' +
                                'data-accessport="' + d.accessport + '" ' +
                                'data-apikey="' + d.apikey + '" ' +
                                'data-path="' + d.path + '" ' +
                                'data-sha="' + d.sha + '" ' +
                                'data-dynam="' + d.dynamic + '" ' +
                                'data-enable_direct_access="' + d.enable_direct_access + '" ' +
                                'data-urlnificallback="' + d.urlnificallback + '" ' +
                                'data-subscription_id="' + d.subscription_id + '" ' + 'data-services="' + d.services + '">Edit</button>';
                    }else 
                        return '';
                }
            }, {
                data: null,
                "name": "delete",
                "orderable": false,
                className: "center",
                render: function (d) {
                    if (loggedRole == 'RootAdmin' || d.visibility == 'MyOwnPrivate' || d.visibility == 'MyOwnPublic') {
                        return '<button type="button" class="delDashBtn" ' +
                                'data-name="' + d.name + '">Delete</button>';
                    }else 
                        return '';
                }
            }, {
                "name": "goto",
                 "orderable": false,
                "data": function (row, type, val, meta) {
                    var goto="";
                    //&& (row.accesslink.substring(0,14)!= "http://192.168" && row.accesslink.substring(0,7)!= "192.168") 
                    if ((row.protocol == 'ngsi' || row.protocol.substring(0, 4) == 'ngsi')) {
                        if (row.accesslink.substring(0, 4) == 'http' ) {
                            
                            var goto = row.accesslink;
                        } else if (row.kind == "internal" && row.accessport ) {
                            var goto = "https://" + row.accesslink+":" + row.accessport;
                        } else if(row.accessport ){
                            var goto = "http://" + row.accesslink+":" + row.accessport;
                        }else {
                             var goto = "https://" + row.accesslink;
                        }

                        if ((row.version != null ||row.version != 'null' )&& row.accesslink.substring(-2) != 'v2' || row.accesslink.substring(-2) != 'v1' || row.accesslink.substring(-3) != 'v2/' || row.accesslink.substring(-3) != 'v1/') {
                            goto += "/" + row.version;
                        }

                        if (row.dynamic == true) {
                            return '<a href="' + goto + ' " target = "_blank" ><p id="GoNoDyn' + row.name + '">GoDyn</p></a>';
                        } else if (row.dynamic == false) {
                            return '<a href="' + goto + ' " target = "_blank" ><p id="GoNoDyn' + row.name + '">Go</p></a>';
                        }
                        return ' ';


                    }
                    return goto;

                }
            }, {
                data: null,
                "name": "check",
                 "orderable": false,
                render: function (d) {
                    if ((d.protocol == 'ngsi' || d.protocol.substring(0, 4) == 'ngsi')) {
                        //&& (d.accesslink.substring(0, 14) != "http://192.168" && d.accesslink.substring(0, 7) != "192.168")
                        return '<button id="testbuttonB" type="button"  class="testDashBtn" data-name="' + d.name + '" ' + 'data-version="' + d.version + '" ' + 'data-organization="' + d.organization + '" > Test </button><button type="button" class="viewButDashBtn" data-name="' + d.name + '" ' +
                                'data-organization="' + d.organization + '" ' +
                                'data-kind="' + d.kind + '" ' +
                                'data-ip="' + d.ip + '" ' +
                                'data-protocol="' + d.protocol + '" ' +
                                'data-version="' + d.version + '" ' +
                                'data-port="' + d.port + '" ' +
                                'data-uri="' + d.uri + '" ' +
                                'data-created="' + d.created + '" ' +
                                'data-visibility="' + d.visibility + '" ' +
                                'data-longitude="' + d.longitude + '" ' +
                                'data-latitude="' + d.latitude + '" ' +
                                'data-login="' + d.login + '" ' +
                                'data-password="' + d.password + '" ' +
                                'data-accesslink="' + d.accesslink + '" ' +
                                'data-accessport="' + d.accessport + '" ' +
                                'data-apikey="' + d.apikey + '" ' +
                                'data-path="' + d.path + '" ' +
                                'data-sha="' + d.sha + '" ' +
                                'data-enable_direct_access="' + d.enable_direct_access + '" ' +
                                'data-dynam="' + d.dynamic + '" ' +
                                'data-urlnificallback="' + d.urlnificallback + '" ' +
                                'data-subscription_id="' + d.subscription_id + '" ' + 'data-services="' + d.services + '">View</button>';
                    } else {
                        return '<button type="button" class="viewButDashBtn" data-name="' + d.name + '" ' +
                                'data-organization="' + d.organization + '" ' +
                                'data-kind="' + d.kind + '" ' +
                                'data-ip="' + d.ip + '" ' +
                                'data-protocol="' + d.protocol + '" ' +
                                'data-version="' + d.version + '" ' +
                                'data-port="' + d.port + '" ' +
                                'data-uri="' + d.uri + '" ' +
                                'data-created="' + d.created + '" ' +
                                'data-visibility="' + d.visibility + '" ' +
                                'data-longitude="' + d.longitude + '" ' +
                                'data-latitude="' + d.latitude + '" ' +
                                'data-login="' + d.login + '" ' +
                                'data-password="' + d.password + '" ' +
                                'data-accesslink="' + d.accesslink + '" ' +
                                'data-accessport="' + d.accessport + '" ' +
                                'data-apikey="' + d.apikey + '" ' +
                                'data-path="' + d.path + '" ' +
                                'data-sha="' + d.sha + '" ' +
                                'data-dynam="' + d.dynamic + '" ' +
                                'data-urlnificallback="' + d.urlnificallback + '" ' +
                                'data-subscription_id="' + d.subscription_id + '" ' + 'data-services="' + d.services + '">View</button>';
                    }
                    return '';
                }
            }],
        "order": []
    });

    $('#contextBrokerTable').DataTable().columns.adjust().draw();
    if (loggedRole != 'RootAdmin' && loggedRole != 'ToolAdmin') {
        dataTable.columns([8, 10, 11]).visible(false);	//hide Owner, Edit, Delete
    }
    if (loggedRole == 'ToolAdmin') {
        dataTable.columns([8]).visible(false);//hide Owner 		
}
}
//end of fetch function 

/*JQuery Started...*/

$(document).ready(function ()
{



//fetch_data function will load the contextbroker table 	
    fetch_data(false);
    //aggiornaStub();
//detail control for contextbroker dataTable
    var detailRows = [];





    $('#contextBrokerTable tbody').on('click', 'td.details-control', function () {
        var tr = $(this).closest('tr');
        var tdi = tr.find("i.fa");
        var row = dataTable.row(tr);


        if (row.child.isShown()) {
            // This row is already open - close it
            row.child.hide();
            tr.removeClass('shown');
            tdi.first().removeClass('fa-minus-square');
            tdi.first().addClass('fa-plus-square');
        } else {
            // Open this row
            row.child(format(row.data())).show();
            if (row.data()["dynamic"] == true) {
                keep_version(row.data());
            }
            tr.addClass('shown');
            tdi.first().removeClass('fa-plus-square');
            tdi.first().addClass('fa-minus-square');
        }
    });

    function keep_version(data) {

        var status = "";
        var time_status = "";
        $.ajax({
            url: "../api/contextbroker.php",
            data: {
                action: "orion_version",
                token: sessionToken,
                name: data["name"],
                org: data["organization"]
            },
            type: "POST",
            async: true,
            success: function (result)
            {//console.log(result);
                console.log(result);
                status = result["Upstatus"]["status"];
                time_status = result["Upstatus"]["status_timestamp"];

                if (result["status"] === 'ko')
                {
                    status = result["Upstatus"]["status"];
                    time_status = result["Upstatus"]["status_timestamp"];

                    console.log("Error in the connection with the ngsi context broker.");

                } else {
                    status = result["Upstatus"]["status"];
                    time_status = result["Upstatus"]["status_timestamp"];
                    document.getElementById('Version_api').innerHTML = result["data"]["orion"]["version"];
                    if (time_status != null) {
                        document.getElementById('Version_status').innerHTML = status + " </b> " + time_status;
                    } else {
                        document.getElementById('Version_status').innerHTML = status;
                    }

                }
            }, error: function (result) {
                console.log(result);

            }
        });



    }

//end of detail control for contextbroker dataTable 


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

        if (difference === 300)
        {
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

        if (difference === 120)
        {
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

        if ((difference > 0) && (difference <= 60))
        {
            $('#sessionExpiringPopup').show();
            $('#sessionExpiringPopup').css("opacity", "1");
            $('#sessionExpiringPopupTime').html(difference + " seconds");
        }

        if (difference <= 0)
        {
            location.href = "logout.php?sessionExpired=true";
        }
    }, 1000);

    $('#mainContentCnt').height($('#mainMenuCnt').height() - $('#headerTitleCnt').height());

    $(window).resize(function () {
        $('#mainContentCnt').height($('#mainMenuCnt').height() - $('#headerTitleCnt').height());
        //$('#sessionExpiringPopup').css("top", parseInt($('body').height() - $('#sessionExpiringPopup').height()) + "px");
        //$('#sessionExpiringPopup').css("left", parseInt($('body').width() - $('#sessionExpiringPopup').width()) + "px");

        if ($(window).width() < 992)
        {
            //$('#contextBrokerTable').bootstrapTable('hideColumn', 'name');
            //$('#contextBrokerTable').bootstrapTable('hideColumn', 'uri');
            //$('#contextBrokerTable').bootstrapTable('hideColumn', 'accesslink');
            //$('#contextBrokerTable').bootstrapTable('hideColumn', 'accessport');
            //$('#contextBrokerTable').bootstrapTable('hideColumn', 'protocol');
            //$('#contextBrokerTable').bootstrapTable('hideColumn', 'created');
        } else
        {
            //$('#contextBrokerTable').bootstrapTable('showColumn', 'name');
            //$('#contextBrokerTable').bootstrapTable('showColumn', 'uri');
            //$('#contextBrokerTable').bootstrapTable('showColumn', 'accesslink');
            //$('#contextBrokerTable').bootstrapTable('showColumn', 'accessport');
            //$('#contextBrokerTable').bootstrapTable('showColumn', 'protocol');
            //$('#contextBrokerTable').bootstrapTable('showColumn', 'created');
        }
    });

    for (var func = 0; func < functionality.length; func++)
    {
        var element = functionality[func];
        if (element.view == "view")
        {
            if (element[loggedRole] == 1)
            {
                $(element["class"]).show();
            } else
            {
                $(element["class"]).hide();
            }
        }
    }

    $('#contextbrokerLink .mainMenuItemCnt').addClass("mainMenuItemCntActive");
    $('#mobMainMenuPortraitCnt #contextbrokerLink .mobMainMenuItemCnt').addClass("mainMenuItemCntActive");
    $('#mobMainMenuLandCnt #contextbrokerLink .mobMainMenuItemCnt').addClass("mainMenuItemCntActive");



    //buildMainTable(false);

    /*  ADD CONTEXT BROKER (INSERT INTO DB) */

    $('#addContextBrokerConfirmBtn').off("click");
    $("#addContextBrokerConfirmBtn").click(function () {

        $('#addContextBrokerModalTabs').hide();
        $('#addContextBrokerModalBody').hide();
        $('#addContextBrokerModal div.modalCell').hide();
        //$('#addContextBrokerModalFooter').hide();
        $('#addContextBrokerCancelBtn').hide();
        $('#addContextBrokerConfirmBtn').hide();
        $('#addContextBrokerOkBtn').hide();
        $('#addCBOkMsg').hide();
        $('#addCBOkIcon').hide();
        $('#addCBKoMsg').hide();
        $('#addCBKoIcon').hide();
        $('#addCBLoadingMsg').show();
        $('#addCBLoadingIcon').show();

        var accesslink = $('#inputAccessLinkCB').val();
        if (accesslink == "") {
            accesslink = $('#inputIpCB').val();
        }

        var servicesArr = $('input[name="inputServiceCB"]');
        var serviceValues = [];
        for (let i = 0; i < servicesArr.length; i++) {
            if (servicesArr[i].value && !(serviceValues.includes(servicesArr[i].value)))
                serviceValues.push(servicesArr[i].value.trim());
        }

        var placehold_accesslink;
        var placehold_accesslink_port;
        if (flag_CB && deployOrionFilterAccess != "") {
            placehold_accesslink = deployOrionFilterAccess + $('#inputNameCB').val();
            if (deployOrionFilterAccess.substr(0, 5) == "https") {
                placehold_accesslink_port = '443';
            } else {
                placehold_accesslink_port = '80';
            }
        } else {
            placehold_accesslink = accesslink;
            placehold_accesslink_port = $('#inputAccessPortCB').val();
        }

        $.ajax({
            url: "../api/contextbroker.php",
            data: {
                action: "insert",
                token: sessionToken,
                name: $('#inputNameCB').val(),
                kind: $('#selectKindCB').val(),
                ip: $('#inputIpCB').val(),
                port: $('#inputPortCB').val(),
                protocol: $('#selectProtocolCB').val(),
                version: $('#inputVersionCB').val(),
                login: $('#inputLoginCB').val(),
                path: $('#inputPathCB').val(),
                visibility: $('#selectVisibilityCB').val(),
                password: $('#inputPasswordCB').val(),
                latitude: $('#inputLatitudeCB').val(),
                longitude: $('#inputLongitudeCB').val(),
                accesslink: placehold_accesslink,
                accessport: placehold_accesslink_port,
                sha: $('#inputSHACB').val(),
                urlnificallback: $('#inputUrlOrionCallback').val(),
                services: JSON.stringify(serviceValues),
                input_log: flag_log,
                id: ID_ORION_CB,
                flag_CB: flag_CB
            },
            type: "POST",
            async: true,
            success: function (data)
            {
                //   console.log(id_CB);
                if (data["status"] === 'ko')
                {

                    $('#addContextBrokerModalTabs').hide();
                    $('#addContextBrokerModalBody').hide();
                    $('#addContextBrokerModal div.modalCell').hide();
                    $('#addContextBrokerCancelBtn').hide();
                    $('#addContextBrokerConfirmBtn').hide();
                    $('#addContextBrokerOkBtn').show();
                    $('#addCBOkMsg').hide();
                    $('#addCBOkIcon').hide();
                    $('#addCBLoadingMsg').hide();
                    $('#addCBLoadingIcon').hide();
                    $('#addCBKoMsg').show();
                    $('#addCBKoMsg div:first-child').html(data["error_msg"]);
                    $('#addCBKoIcon').show();

                } else if (data["status"] === 'ok')
                {
                    //    var NN='#GoDynamic'+ $('#inputNameCB');
                    //    document.getElementById("GoDynamic"+$('#inputNameCB').val() ).innerHTML = "GoDyn";
                    $('#addCBLoadingMsg').hide();
                    $('#addCBLoadingIcon').hide();
                    $('#addCBKoMsg').hide();
                    $('#addCBKoIcon').hide();
                    $('#addContextBrokerModalTabs').hide();
                    $('#addContextBrokerModalBody').hide();
                    $('#addContextBrokerModal div.modalCell').hide();
                    $('#addContextBrokerCancelBtn').hide();
                    $('#addContextBrokerConfirmBtn').hide();
                    $('#addContextBrokerOkBtn').show();
                    $('#addCBOkMsg').show();
                    $('#addCBOkIcon').show();
                    $('#addContextBrokerOkBtn').show();
                    $('#dashboardTotNumberCnt .pageSingleDataCnt').html(parseInt($('#dashboardTotNumberCnt .pageSingleDataCnt').html()) + 1);
                    $('#dashboardTotActiveCnt .pageSingleDataCnt').html(parseInt($('#dashboardTotActiveCnt .pageSingleDataCnt').html()) + 1);
                    $('#contextBrokerTable').DataTable().clear().destroy();

                    //   $('.GoDynamic').style.display = "inline"; 
                    fetch_data(true);

                    $('#inputNameCB').val("");
                    $('#selectKindCB').val("");
                    $('#inputPathCB').val("");
                    $('#selectVisibilityCB').val("");
                    $('#inputVersionCB').val("");
                    $('#inputIpCB').val("");
                    $('#inputPortCB').val("");
                    $('#selectProtocolCB').val("NULL");
                    $('#inputUriCB').val("");
                    $('#inputLoginCB').val("");
                    $('#inputPasswordCB').val("");
                    $('#inputLatitudeCB').val("");
                    $('#inputLongitudeCB').val("");
                    $('#createdDateCB').val("");
                    $('#inputAccessLinkCB').val("");
                    $('#inputAccessPortCB').val("");
                    $('#inputSHACB').val("");

                    $('input[name="inputServiceCB"]').val("");
                    window.location.reload();

                    // $(NN).innerText = "GoDyn";


                }
            },
            error: function (data)
            {
                $("#addContextBrokerModal").modal('hide');
                $('#addContextBrokerModalTabs').hide();
                $('#addContextBrokerModalBody').hide();
                $('#addContextBrokerModal div.modalCell').hide();
                $('#addContextBrokerModalFooter').hide();
                $('#addCBOkMsg').hide();
                $('#addCBOkIcon').hide();
                $('#addCBKoMsg').show();
                $('#addCBKoIcon').show();
            }
        });
    });


// DELETE CONTEXT BROKER 

    //To be modified 
    $('#contextBrokerTable button.delDashBtn').off('hover');
    $('#contextBrokerTable button.delDashBtn').hover(function () {
        $(this).css('background', '#ffcc00');
        $(this).parents('tr').find('td').eq(1).css('background', '#ffcc00');
    },
            function () {
                $(this).css('background', '#e37777');
                $(this).parents('tr').find('td').eq(1).css('background', $(this).parents('td').css('background'));
            });


    $('#contextBrokerTable tbody').on('click', 'button.delDashBtn', function ()
    {
        var name = $(this).attr('data-name');
        $("#deleteContextBrokerModal div.modal-body").html('<div class="modalBodyInnerDiv"><span data-name = "' + name + '">Do you want to confirm deletion of context broker <b>' + name + '</b>?</span></div>');


        $("#deleteCBModalInnerDiv1").html('<h5>Context broker deletion in progress, please wait</h5>');
        $("#deleteCBModalInnerDiv2").html('<i class="fa fa-circle-o-notch fa-spin" style="font-size:36px"></i>');
        $("#deleteCBModalInnerDiv1").hide();
        $("#deleteCBModalInnerDiv2").hide();
        $("#deleteContextBrokerOkBtn").hide();
        $("#deleteContextBrokerCancelBtn").show();
        $("#deleteContextBrokerConfirmBtn").show();
        $("#deleteContextBrokerModal").modal('show');


    });


    $('#deleteContextBrokerConfirmBtn').off("click");
    $("#deleteContextBrokerConfirmBtn").click(function () {

        var name = $("#deleteContextBrokerModal span").attr("data-name");

        $("#deleteContextBrokerModal div.modal-body").html("");
        $("#deleteContextBrokerCancelBtn").hide();
        $("#deleteContextBrokerConfirmBtn").hide();
        $("#deleteContextBrokerOkBtn").hide();
        $("#deleteCBModalInnerDiv1").show();
        $("#deleteCBModalInnerDiv2").show();


        $.ajax({
            url: "../api/contextbroker.php",
            data: {
                action: "delete",
                token: sessionToken,
                name: name
            },
            type: "POST",
            datatype: "json",
            async: true,

            success: function (data)
            {
                $("#deleteContextBrokerOkBtn").show();
                if (data["status"] === 'ko')
                {
                    $("#deleteCBModalInnerDiv1").html(data["error_msg"]);
                    $("#deleteCBModalInnerDiv2").html('<i class="fa fa-frown-o" style="font-size:42px"></i>');
                } else if (data["status"] === 'ok')
                {
                    $("#deleteCBModalInnerDiv1").html('Contextbroker &nbsp; deleted successfully');
                    $("#deleteCBModalInnerDiv1").show();
                    $("#deleteCBModalInnerDiv2").html('<i class="fa fa-check" style="font-size:42px"></i>');

                    $('#dashboardTotNumberCnt .pageSingleDataCnt').html(parseInt($('#dashboardTotNumberCnt .pageSingleDataCnt').html()) - 1);
                    $('#dashboardTotActiveCnt .pageSingleDataCnt').html(parseInt($('#dashboardTotActiveCnt .pageSingleDataCnt').html()) - 1);

                    fetch_data(true);

                    /*setTimeout(function()
                     {
                     //buildMainTable(true);
                     $('#contextBrokerTable').DataTable().destroy();
                     fetch_data(true);
                     $("#deleteContextBrokerModal").modal('hide');
                     setTimeout(function(){
                     $("#deleteContextBrokerCancelBtn").show();
                     $("#deleteContextBrokerConfirmBtn").show();
                     }, 500);
                     }, 2000);*/
                } else
                {
                    console.log("delete context broker error:" + data);
                }

            },
            error: function (data)
            {
                $("#deleteContextBrokerOkBtn").show();
                console.log(JSON.stringify(data));
                $("#deleteCBModalInnerDiv1").html(data["error_msg"]);
                $("#deleteContextBrokerModalInnerDiv2").html('<i class="fa fa-frown-o" style="font-size:42px"></i>');

            }
        });
    });

    $("#deleteContextBrokerOkBtn").off("click");
    $("#deleteContextBrokerOkBtn").click(function () {
        $("#deleteContextBrokerModal div.modal-body").html("Do you want to confirm deletion of the following Context broker?");
        $("#deleteContextBrokerOkBtn").hide();
        $("#deleteContextBrokerCancelBtn").show();
        $("#deleteContextBrokerConfirmBtn").show();
        $("#deleteCBModalInnerDiv1").html('<h5>Context broker deletion in progress, please wait</h5>');
        $("#deleteCBModalInnerDiv2").html('<i class="fa fa-circle-o-notch fa-spin" style="font-size:36px"></i>');
        $("#deleteCBModalInnerDiv1").hide();
        $("#deleteCBModalInnerDiv2").hide();
    });



// TESTING CONTEXT BROKER
    $('#contextBrokerTable button.testDashBtn').off('hover');
    $('#contextBrokerTable button.testDashBtn').hover(function () {
        $(this).css('background', '#ffcc00');
        $(this).parents('tr').find('td').eq(1).css('background', '#ffcc00');
    },
            function () {
                $(this).css('background', 'rgba(0, 162, 211, 1)');
                $(this).parents('tr').find('td').eq(1).css('background', $(this).parents('td').css('background'));
            });


    $('#contextBrokerTable tbody').on('click', 'button.testDashBtn', function () {
        $("#testContextBrokerModal").modal('show');
        $("#TestModalStatus").html("Waiting for ..." + " <br>");
        $.ajax({
            url: "../api/contextbroker.php",
            data: {
                action: 'is_broker_up',
                token: sessionToken,
                contextbroker: $(this).attr("data-name"),
                version: $(this).attr("data-version")
            },
            type: "POST",
            async: true,
            dataType: 'json',
            success: function (rr) {


                if (rr["status"] === 'ok') {
                    //console.log('ok');
                    //console.log($("#TestModalStatus"));
                    $("#TestModalStatus").html("The broker answers at the link: " + " <br>" + rr["content"]);
                } else if (rr["status"] === 'ko') {
                    //console.log('ko');
                    //console.log((rr));
                    $("#TestModalStatus").html("There is a problem:" + " <br>" + rr["error_msg"]);

                }
            }, error: function (data)
            {


                console.log("Error in link test");
                console.log((data));


                $("#TestModalStatus").html("There is a problem:" + " <br>" + data.error_msg);


            }

        });
    });


////////

// EDIT CONTEXT BROKER  

    //To be modified 
    $('#contextBrokerTable button.editDashBtn').off('hover');
    $('#contextBrokerTable button.editDashBtn').hover(function () {
        $(this).css('background', '#ffcc00');
        $(this).parents('tr').find('td').eq(1).css('background', '#ffcc00');
    },
            function () {
                $(this).css('background', 'rgb(69, 183, 175)');
                $(this).parents('tr').find('td').eq(1).css('background', $(this).parents('td').css('background'));
            });
    var flag_CB = false;
    var flag_log = false;
    function startEditModal() {
        $("#editContextBrokerModal").modal('show');
        $('.nav-tabs a[href="#editInfoTabCB"]').tab('show');
        if ($(this).attr("data-dynam") == 'true') {
            var items = ["ngsi", "ngsi w/MultiService"];
            var IDitems = ["ngsi", "ngsi_w/MultiService"];
            var str = ""
            for (var i = 0; i < items.length; i++) {

                str += "<option  id= " + IDitems[i] + ">" + items[i] + "</option>"
            }
            document.getElementById("selectProtocolCBM").innerHTML = str;
        } else {
            items = ["amqp", "coap", "mqtt", "ngsi", "ngsi w/MultiService", "sigfox"];
            var IDitems = ["amqp", "coap", "mqtt", "ngsi", "ngsi_w/MultiService", "sigfox"];
            var str = ""
            for (var i = 0; i < items.length; i++) {
                str += "<option  id= " + IDitems[i] + ">" + items[i] + "</option>"
            }

            document.getElementById("selectProtocolCBM").innerHTML = str;
        }

        $("#editContextBrokerModalUpdating").hide();
        $("#editCBModalLoading").hide();
        $("#editContextBrokerModalBody").show();
        $("#editContextBrokerModalFooter").show();
        $("#editContextBrokerCancelBtn").show();
        $("#editContextBrokerConfirmBtn").show();

        $('#editContextBrokerLoadingMsg').hide();
        $('#editContextBrokerLoadingIcon').hide();
        $('#editContextBrokerOkMsg').hide();
        $('#editContextBrokerOkIcon').hide();
        $('#editContextBrokerKoMsg').hide();
        $('#editContextBrokerKoIcon').hide();
        $('#editContextBrokerOkBtn').hide();
        $("#inputUrlOrionCallbackM").show();
        $("#urlOrionCallbackLabelM").show();
        $("#selectUrlOrionCallbackMsgM").show();
        $("#inputNameCBM").val($(this).attr("data-name"));
        $("#inputOrganizationCBM").val($(this).attr("data-organization"));
        $("#selectKindCBM").val($(this).attr("data-kind"));
        $("#inputPathCBM").val($(this).attr("data-path"));
        $("#inputVersionCBM").val($(this).attr("data-version"));
        $("#selectVisibilityCBM").val($(this).attr("data-visibility"));
        $("#inputIpCBM").val($(this).attr("data-ip"));
        $("#inputPortCBM").val($(this).attr("data-port"));
        $("#selectProtocolCBM").val($(this).attr("data-protocol"));
        $("#inputUriCBM").val($(this).attr("data-uri"));
        $("#createdDateCBM").val($(this).attr("data-created"));
        $("#inputLatitudeCBM").val($(this).attr("data-latitude"));
        $("#inputLongitudeCBM").val($(this).attr("data-longitude"));
        $("#inputLoginCBM").val($(this).attr("data-login"));
        $("#inputPasswordCBM").val($(this).attr("data-password"));
        $("#inputAccessLinkCBM").val($(this).attr("data-accesslink"));
        $("#inputAccessPortCBM").val($(this).attr("data-accessport"));
        $("#inputApiKeyCBM").val($(this).attr("data-apikey"));
        $("#inputSHACBM").val($(this).attr("data-sha"));
        if ($(this).attr("data-dynam") == 'true') {
            $(".ShowIfDyn").show();
            $(".NotShowIfDyn").hide();

            if ($(this).attr("data-enable_direct_access") == 'true') {
                document.getElementById("Login_CB_view").checked = true;
                var a = document.getElementById('Login_CB_view_ref');
                a.href = deployOrionDirectAccess+$(this).attr("data-name")+"/v2";
                $("#Login_CB_view_url").text(a.href);
            } else {
                document.getElementById("Login_CB_view").checked = false;
                $("#Login_CB_view_url").text("");
            }
        } else {
            $(".ShowIfDyn").hide();
            $(".NotShowIfDyn").show();
        }

        //subscription tab
        if (($(this).attr("data-subscription_id") == "undefined") || ($(this).attr("data-subscription_id") == "null"))
        {
            $("#substatusCBMMsg").text("The automatic subscription is disabled");
            //disable the uri
            $("#inputUrlOrionCallbackM").hide();
            $("#urlOrionCallbackLabelM").hide();
            $("#selectUrlOrionCallbackMsgM").hide();
        } else if ($(this).attr("data-subscription_id") == "FAILED")
        {
            $("#substatusCBMMsg").text("The automatic subscription failed");
        } else {
            $("#substatusCBMMsg").text("Subscription id:\n" + $(this).attr("data-subscription_id").replace(/,/g, "\n"));
        }
        $("#inputUrlOrionCallbackM").val($(this).attr("data-urlnificallback"));
        if ($(this).attr("data-protocol"))
        {
            $("#tab-editCB-4").show();
        } else
        {
            $("#tab-editCB-4").hide();
        }

        if ($(this).attr("data-kind") === "internal") {
            $('#selectUrlOrionCallbackMsgHint').hide();
        }

        editCBSManagementButtonPressed($(this).attr("data-services"));
        showEditCbModal();
    }

// Edit Context broker			
    $('#contextBrokerTable tbody').on('click', 'button.editDashBtn', function () {
        startEditModal.call(this);
        $("#editCBModalLabel").html("Edit Context Broker - " + $(this).attr("data-name"));



//        if ($(this).attr("data-dynam") == 'true') {
//            document.getElementById("editInfoTabCB").style.display = "none";
//           // document.getElementById("#editSubscriptionTabCB").style.display = "none";
//            $('#tab-editCB-1').removeClass('active');
//             $('#tab-editCB-1').addClass('hidden');
//            $('#tab-editCB-4').addClass('hidden');
//            $('#tab-editCB-2').addClass('active');
//            $('#editGeoPositionTabCB').addClass('active in ');
//            var latitude = $("#inputLatitudeCBM").val();
//            var longitude = $("#inputLongitudeCBM").val();
//            var flag = 1;
//            drawMap1(latitude, longitude, flag);
//        } else {
//            
//            document.getElementById("editInfoTabCB").style.display ;
//            document.getElementById("tab-editCB-1").style.display;
//            $('#tab-editCB-1').removeClass('hidden');
//            $('#tab-editCB-1').addClass('active');
//            $('#tab-editCB-4').removeClass('hidden');
//            // $('#tab-editCB-1').removeClass('hidden');
//            $('#tab-editCB-2').removeClass('active');
//             $('#editInfoTabCB').addClass('active in ');
//            $('#editGeoPositionTabCB').removeClass('active in ');
        //        }

        ///dynimc
        $('a[data-toggle="tab"]').off('shown.bs.tab').on('shown.bs.tab', function (e) {

            var target = $(e.target).attr("href");
            if ((target == '#editGeoPositionTabCB')) {
                var latitude = $("#inputLatitudeCBM").val();
                var longitude = $("#inputLongitudeCBM").val();
                var flag = 1;
                drawMap1(latitude, longitude, flag);

            }
        }
        );

    })


    $('#contextBrokerTable button.viewDashBtn').off('hover');
    $('#contextBrokerTable button.viewDashBtn').hover(function () {
        $(this).css('background', '#ffcc00');
        $(this).parents('tr').find('td').eq(1).css('background', '#ffcc00');
    },
            function () {
                $(this).css('background', 'rgba(0, 162, 211, 1)');
                $(this).parents('tr').find('td').eq(1).css('background', $(this).parents('td').css('background'));
            });

// view dettails CONTEXT BROKER
    $('#contextBrokerTable button.viewButDashBtn').off('hover');
    $('#contextBrokerTable button.viewButDashBtn').hover(function () {
        $(this).css('background', '#ffcc00');
        $(this).parents('tr').find('td').eq(1).css('background', '#ffcc00');
    },
            function () {
                $(this).css('background', 'rgba(0, 162, 211, 1)');
                $(this).parents('tr').find('td').eq(1).css('background', $(this).parents('td').css('background'));
            });

    function fillCBTableLabel(mode) {
        $("#inputIpCBM").attr('readonly', mode);
        $("#selectVisibilityCBM").prop('disabled', mode);
        $("#inputNameCBM").attr('readonly', mode);
        $("#inputOrganizationCBM").attr('readonly', mode);
        $("#selectKindCBM").prop('disabled', mode);
        $("#inputPathCBM").attr('readonly', mode);
        $("#inputVersionCBM").attr('readonly', mode);
        $("#selectVisibilityCBM").prop('disabled', mode);
        $("#inputPortCBM").attr('readonly', mode);
        $("#selectProtocolCBM").prop('disabled', mode);
        $("#inputUriCBM").attr('readonly', mode);
        $("#createdDateCBM").prop('disabled', mode);
        $("#inputLatitudeCBM").attr('readonly', mode);
        $("#inputLongitudeCBM").attr('readonly', mode);
        $("#inputLoginCBM").attr('readonly', mode);
        $("#inputPasswordCBM").attr('readonly', mode);
        $("#inputAccessLinkCBM").attr('readonly', mode);
        $("#inputAccessPortCBM").attr('readonly', mode);
        $("#inputApiKeyCBM").attr('readonly', mode);
        $("#inputSHACBM").attr('readonly', mode);
        $(".editInputServiceCB").attr('readonly', mode);
        $("#inputUrlOrionCallbackM").attr('readonly', mode);
        //$("#inputUrlOrionCallbackM").attr('readonly', mode);
        $("#selectUrlOrionCallbackMsgM").prop('disabled', mode);
        document.getElementById("Login_CB_view").disabled = mode;
        document.getElementById("editAddNewCBServiceBtn").disabled = mode;




    }


    $('#contextBrokerTable tbody').on('click', 'button.viewButDashBtn', function () {
        startEditModal.call(this);
        $("#editCBModalLabel").html("View Context Broker - " + $(this).attr("data-name"));

        fillCBTableLabel(true);


        $("#editContextBrokerConfirmBtn").hide();
        var latitude = $(this).attr("data-latitude");
        var longitude = $(this).attr("data-longitude");
        $('a[data-toggle="tab"]').off('shown.bs.tab').on('shown.bs.tab', function (e) {
            var target = $(e.target).attr("href");
            if ((target == '#editGeoPositionTabCB')) {

                var flag = 2;
                drawMap1(latitude, longitude, flag);

            }
        });




    });


// make input editable
    $("#editContextBrokerModal").on("hidden.bs.modal", function () {
        fillCBTableLabel(false);
        $('#addLatLongEdit').show();
    });


    $('#contextBrokerTable tbody').on('click', 'button.viewDashBtn', function () {

        var name = $(this).attr('data-name');
        //var ip = $(this).parents('tr').find('td').eq(3).text();
        var ip = $(this).attr('data-ip');
        // var port = $(this).parents('tr').attr("data-port");
        var port = $(this).attr("data-port");
        var protocol = $(this).attr("data-protocol");
        //You can call the stub function here								
        activateStub(this, name, ip, port, protocol);

    });




    $('#editContextBrokerConfirmBtn').off("click");
    $("#editContextBrokerConfirmBtn").click(function () {
        $("#editContextBrokerCancelBtn").hide();
        $("#editContextBrokerConfirmBtn").hide();
        $("#editContextBrokerModalBody").hide();
        $('#editContextBrokerLoadingMsg').show();
        $('#editContextBrokerLoadingIcon').show();

        var servicesArr = $('input[name="editInputServiceCB"]');
        var serviceValues = [];
        for (let i = 0; i < servicesArr.length; i++) {
            if (servicesArr[i].value && !(serviceValues.includes(servicesArr[i].value)))
                serviceValues.push(servicesArr[i].value.trim());
        }
        var flag_directAcess = null;
        if (document.getElementById("Login_CB_view").checked == true) {
            flag_directAcess = 1;
        } else {
            flag_directAcess = 0;
        }

        $.ajax({
            url: "../api/contextbroker.php",
            data: {
                token: sessionToken,
                action: "update",
                name: $('#inputNameCBM').val(),
                kind: $('#selectKindCBM').val(),
                path: $('#inputPathCBM').val(),
                version: $('#inputVersionCBM').val(),
                visibility: $('#selectVisibilityCBM').val(),
                ip: $('#inputIpCBM').val(),
                port: $('#inputPortCBM').val(),
                protocol: $('#selectProtocolCBM').val(),
                uri: $('#inputUriCBM').val(),
                login: $('#inputLoginCBM').val(),
                password: $('#inputPasswordCBM').val(),
                latitude: $('#inputLatitudeCBM').val(),
                longitude: $('#inputLongitudeCBM').val(),
                createdDate: $('#createdDateCBM').val(),
                accesslink: $('#inputAccessLinkCBM').val(),
                accessport: $('#inputAccessPortCBM').val(),
                apikey: $('#inputApiKeyCBM').val(),
                sha: $('#inputSHACBM').val(),
                urlnificallback: $('#inputUrlOrionCallbackM').val(),
                services: JSON.stringify(serviceValues),
                log_orion: flag_directAcess
            },
            type: "POST",
            async: true,
            success: function (data)
            {
                if (data["status"] === 'ko')
                {
                    $('#editContextBrokerLoadingMsg').hide();
                    $('#editContextBrokerLoadingIcon').hide();
                    $('#editContextBrokerOkMsg').hide();
                    $('#editContextBrokerOkIcon').hide();
                    $("#editContextBrokerKoMsg").html(data["error_msg"]);
                    $('#editContextBrokerKoMsg').show();
                    $('#editContextBrokerKoIcon').show();
                    $('#editContextBrokerOkBtn').show();
                    console.log("finito ko");

                    setTimeout(function () {		//force reload to avoid servicepath mismatch
                        location.reload();
                    }, 500);
                } else if (data["status"] === 'ok')
                {
                    console.log("starting");
                    $('#inputNameCBM').val("");
                    $('#inputIpCBM').val("");
                    $('#inputPortCBM').val("");
                    $('#inputVersionCBM').val("");
                    $('#inputAccessLinkCBM').val("");
                    $('#inputAccessPortCBM').val("");
                    $('#inputApiKeyCBM').val("");
                    $('#inputPathCBM').val("");
                    $('#inputLatitudeCBM').val("");
                    $('#inputLongitudeCBM').val("");
                    $('#inputLoginCBM').val("");
                    $('#inputPasswordCBM').val("");
                    $('#inputSHACBM').val("");

                    //if a subscription is failed, it return OK, but the data[content] is FAILED -> show a message
                    if (data["content"] === "FAILED")
                        $('#additionInfoOKedit').html('&nbsp;Warning: the automatic subscription for NIFI support FAILED. Please contact the Administrator');
                    else
                        $('#additionInfoOKedit').html('&nbsp;');

                    $('#editServiceCBRow1').remove();
                    $('div[name="additionalRow"]').remove();

                    $('#editContextBrokerLoadingMsg').hide();
                    $('#editContextBrokerLoadingIcon').hide();
                    $('#editContextBrokerOkMsg').show();
                    $('#editContextBrokerOkIcon').show();
                    $('#editContextBrokerKoMsg').hide();
                    $('#editContextBrokerKoIcon').hide();
                    $('#editContextBrokerOkBtn').show();

                    $('#contextBrokerTable').DataTable().clear().destroy();
                    fetch_data(true);
                }
            },
            error: function (data)
            {
                $('#editContextBrokerLoadingMsg').hide();
                $('#editContextBrokerLoadingIcon').hide();
                $('#editContextBrokerOkMsg').hide();
                $('#editContextBrokerOkIcon').hide();
                $('#editContextBrokerKoMsg').show();
                $('#editContextBrokerKoIcon').show();
                $('#editContextBrokerOkBtn').show();
            }
        });
        window.local.reload();
    });




    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        var target = $(e.target).attr("href");
        if ((target == '#geoPositionTabCB')) {
            var latitude = 43.7800;
            var longitude = 11.2300;
            var flag = 0;
            drawMap1(latitude, longitude, flag);
        }
    });

    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        var target = $(e.target).attr("href");
        if ((target == '#editGeoPositionTabCB')) {
            var latitude = $("#inputLatitudeCBM").val();
            var longitude = $("#inputLongitudeCBM").val();
            var flag = 1;
            drawMap1(latitude, longitude, flag);
        }
    });


    function fattoring_add(mode) {

        $("#tab-addCB-4").hide();
        $('#addContextBrokerModalTabs').show();
        $('.nav-tabs a[href="#infoTabCB"]').tab('show');

        $('#addContextBrokerModalBody').show();
        $('#addContextBrokerModal div.modalCell').show();
        $('#addContextBrokerModalFooter').show();
        $('#addContextBrokerCancelBtn').show();
        $('#addContextBrokerConfirmBtn').show();
        $('#addContextBrokerOkBtn').hide();
        $('#addCBOkMsg').hide();
        $('#addCBOkIcon').hide();
        $('#addCBKoMsg').hide();
        $('#addCBKoIcon').hide();
        $('#addCBLoadingMsg').hide();
        $('#addCBLoadingIcon').hide();

        $("#loginExternal").hide();
        $("#loginInternal").hide();

        $('#inputNameCB').attr('readonly', mode);
        $('#inputIpCB').attr('readonly', mode);
        $('#inputPortCB').attr('readonly', mode);
        $('#inputAccessPortCB').attr('readonly', mode);
        $('#inputAccessLinkCB').attr('readonly', mode);
        $('#selectKindCB').prop('disabled', mode);
        $('#selectProtocolCB').prop('disabled', mode);
        $('#inputVersionCB').attr('readonly', mode);
        $('#inputUrlOrionCallback').attr('readonly', mode);

        $('#inputNameCB').val("");
        $('#inputIpCB').val("");
        $('#inputPortCB').val("");
        $('#inputAccessPortCB').val("");
        $('#selectKindCB option[value=""]').attr("selected", "selected");
        $('#selectProtocolCB option[value="amqp"]').attr("selected", "selected");
        $('#inputVersionCB').val("");
        $('#inputUrlOrionCallback').val("");



//document.getElementById('addContextBrokerModal').innerHTML = "";
        //$("#addContextBrokerModal").show();
        $("#addContextBrokerModal").modal('hide');

        if (mode == false) {
            $('#selectKindCB option[value=""]').attr("selected", "selected");
            $('#selectProtocolCB option[value=""]').attr("selected", "selected");

            showAddCbModal();
        } else {
            $('#selectProtocolCB option[value="ngsi"]').attr("selected", "selected");
            $('#selectKindCB option[value="internal"]').attr("selected", "selected");
            $('#selectProtocolCB option[value="ngsi"]').attr("selected", "selected");
            $('#inputVersionCB').val("v2");


        }


    }
//Start Related to Register new Device 

    var ID_ORION_CB = "";
//var id_CB;
    $("#RegisterCBeBtn").off("click");
    $("#RegisterCBBtn").click(function () {

        flag_CB = true;
        flag_log = false;
        fattoring_add(true);



        $.ajax({
            url: "../api/contextbroker.php",
            data: {
                token: sessionToken,
                action: "take_default_CB_take"


            },
            type: "POST",
            async: true,
            success: function (data)
            {
                if (data["content"] != "null") {
                    var source = jQuery.parseJSON(data["content"]);
                    ID_ORION_CB = source['id_orionbroker'];



                    $('#inputNameCB').val(source['name']);
                    $('#inputIpCB').val(source['ipaddr']);
                    $('#inputAccessLinkCB').val(source['ipaddr']);
                    $('#inputPortCB').val(source['external_port']);
                    $('#inputAccessPortCB').val(source['access_port']);
                    $('#inputUrlOrionCallback').val(source['urlnificallback']);
                    // id_CB=source['id_orionbroker'];


                    showAddCbModal();
                    $('#tab-addCB-4').show();
                    $('#selectProtocolCB').prop('disabled', false);
                    var items = ["ngsi", "ngsi w/MultiService"];
                    var IDitems = ["ngsi", "ngsi_w/MultiService"];
                    var str = ""
                    for (var i = 0; i < items.length; i++) {
                        str += "<option  id= " + IDitems[i] + ">" + items[i] + "</option>"
                    }
                    document.getElementById("selectProtocolCB").innerHTML = str;
                    $("#addContextBrokerModalLabel").html("Deploy new orion broker");
                    $('#inputNameCB').attr('readonly', false);

                    $('#inputSHACB').hide();
                    $('#inputSHACB_lab').hide();
                    $('#Login_CB').show();
                    $('#Login_CB_lab').show();

                    $('#Login_CB').on('change', function show_USername_PWD() {
                        var checkBox = document.getElementById("Login_CB");
                        if (checkBox.checked == true) {

                            flag_log = true;
                        } else {

                            flag_log = false;
                        }
                    });

                } else {
                    $("#addContextBrokerModal").modal('hide');
                    $("#addContextBrokerModalNULL").modal('show');
                }



            },
            error: function (data)
            {

            }
        });

    });

//end 

    $('#addContextBrokerBtn').off('click');
    $('#addContextBrokerBtn').click(function () {
        flag_CB = false;
        fattoring_add(false);
        var items = ["amqp", "coap", "mqtt", "ngsi", "ngsi w/MultiService", "sigfox"];
        var IDitems = ["amqp", "coap", "mqtt", "ngsi", "ngsi_w/MultiService", "sigfox"];
        var str = ""
        for (var i = 0; i < items.length; i++) {
            str += "<option  id= " + IDitems[i] + ">" + items[i] + "</option>"
        }

        document.getElementById("selectProtocolCB").innerHTML = str;
        $("#addContextBrokerModalLabel").html("Register new IOT Broker");
        // document.getElementById('selectProtocolCB').innerHTML = "";
        $('#inputSHACB').show();
        $('#inputSHACB_lab').show();
        $('#Login_CB').hide();
        $('#Login_CB_lab').hide();




    });

    // BEGIN ADD BROKER MULTISERVICE SECTION
    // Author: Antonino Mauro Liuzzo

    function createServiceRowElem(initalValue, name) {
        // creation of the components of a row element
        var row = document.createElement("div");
        $(row).attr('class', 'row');
        $(row).attr('name', 'additionalRow');

        var modalCell1 = document.createElement("div");
        $(modalCell1).attr('class', 'col-xs-12 col-md-8 modalCell');

        var modalFieldCnt1 = document.createElement("div");
        $(modalFieldCnt1).attr('class', 'modalFieldCnt');

        var modalInputTxt = document.createElement("input");
        $(modalInputTxt).attr('type', 'text');
        $(modalInputTxt).attr('class', 'modalInputTxt ' + name);
        $(modalInputTxt).attr('name', name);
        $(modalInputTxt).attr('onkeyup', 'checkStrangeCharacters(this)');
        $(modalInputTxt).val(initalValue);

        var modalFieldLabelCnt = document.createElement("div");
        $(modalFieldLabelCnt).attr('class', 'modalFieldLabelCnt');
        $(modalFieldLabelCnt).text("Service/Tenant");

        var modalCell2 = document.createElement("div");
        $(modalCell2).attr('class', 'col-xs-12 col-md-4 modalCell');

        var modalFieldCnt2 = document.createElement("div");
        $(modalFieldCnt2).attr('class', 'modalFieldCnt');

        var rmButton = document.createElement("button");
        $(rmButton).attr('type', 'text');
        $(rmButton).attr('name', 'removeCBServiceBtn');
        $(rmButton).attr('class', 'btn btn-danger');
        $(rmButton).text("Remove");

        rmButton.addEventListener('click', function () {
            row.remove()
        });

        // row element composition
        $(row).append(modalCell1);
        $(modalCell1).append(modalFieldCnt1);
        $(modalFieldCnt1).append(modalInputTxt);
        $(modalCell1).append(modalFieldLabelCnt);
        $(row).append(modalCell2)
        $(modalCell2).append(modalFieldCnt2);
        $(modalFieldCnt2).append(rmButton);

        return row;
    }

    /**
     * Add new Service/Tenant Text Field when the "Add Service/Tenant" is pressed
     */
    $('#addNewCBServiceBtn').click(function () {
        // get the service/tenant tab
        var stTab = $('#serviceTenantTabCB').last();
        // create a new row element
        var row = createServiceRowElem('', 'inputServiceCB');
        // append of the row element
        stTab.append(row);
    });

    /**
     * Trigger the MultiService tab visibility
     * The MultiService tab is visible only if the ngsi w/MultiService protocol is selected
     * When the user choose another protocol, inserted values are saved, except for empty strings
     * If the user choose back ngsi w/MultiService, saved values are restored
     */
    var oldServicesValues = [];

    function addServicesVisibilityCheck() {
        if ($('#selectProtocolCB').val() !== "ngsi w/MultiService") {
            // hide the MultiService selector
            $('#multiServiceTabSelector').addClass("hidden");

            // save the first Service row and clear the row
            var rowValue = $('#serviceCBRow1').find('.modalInputTxt').val().trim();
            if (rowValue !== "")
                oldServicesValues.push(rowValue);
            $('#serviceCBRow1').find('.modalInputTxt').val('');

            // save every additional row and remove them
            var additionalRows = $('#serviceTenantTabCB div[name="additionalRow"]');
            for (let i = 0; i < additionalRows.length; i++) {
                var rowValue = $(additionalRows[i]).find('.modalInputTxt').val().trim();
                if (rowValue !== "")
                    oldServicesValues.push(rowValue);
                additionalRows[i].remove();
            }
        } else {
            // show the MultiService selector
            $('#multiServiceTabSelector').removeClass("hidden");
            restoreServicesValuesAdd(oldServicesValues);
            oldServicesValues = [];
        }
    }
    $('#selectProtocolCB').change(addServicesVisibilityCheck);

    function restoreServicesValuesAdd(servicesArray) {
        // restore the first row
        $('#serviceCBRow1').find('.modalInputTxt').val(servicesArray[0]);

        // restore additional rows
        var stTab = $('#serviceTenantTabCB').last();
        for (let i = 1; i < servicesArray.length; i++) {
            row = createServiceRowElem(servicesArray[i], 'inputServiceCB');
            stTab.append(row);
        }
    }

    // END ADD BROKER MULTISERVICE SECTION

    // BEGIN EDIT BROKER MULTISERVICE SECTION
    // Author: Antonino Mauro Liuzzo

    function createFirstServiceRowElem(initalValue, name) {
        if ($('#editServiceTenantTabCB').find('#editServiceCBRow1').length !== 0) {
            return null;
        }

        // creation of the components of a row element
        var row = document.createElement("div");
        $(row).attr('class', 'row');
        $(row).attr('id', 'editServiceCBRow1');

        var modalCell1 = document.createElement("div");
        $(modalCell1).attr('class', 'col-xs-12 col-md-8 modalCell');

        var modalFieldCnt1 = document.createElement("div");
        $(modalFieldCnt1).attr('class', 'modalFieldCnt');

        var modalInputTxt = document.createElement("input");
        $(modalInputTxt).attr('type', 'text');
        $(modalInputTxt).attr('class', 'modalInputTxt ' + name);
        $(modalInputTxt).attr('name', name);
        $(modalInputTxt).attr('name', name);
        $(modalInputTxt).attr('onkeyup', 'checkStrangeCharacters(this)');
        $(modalInputTxt).val(initalValue);

        var modalFieldLabelCnt = document.createElement("div");
        $(modalFieldLabelCnt).attr('class', 'modalFieldLabelCnt');
        $(modalFieldLabelCnt).text("Service/Tenant");

        var modalCell2 = document.createElement("div");
        $(modalCell2).attr('class', 'col-xs-12 col-md-4 modalCell');

        var modalFieldCnt2 = document.createElement("div");
        $(modalFieldCnt2).attr('class', 'modalFieldCnt');

        var rmButton = document.createElement("button");
        $(rmButton).attr('type', 'text');
        $(rmButton).attr('id', 'editAddNewCBServiceBtn');
        $(rmButton).attr('class', 'btn confirmBtn');
        $(rmButton).text("Add Service/Tenant");

        rmButton.addEventListener('click', function () {
            // get the service/tenant tab
            var stTab = $('#editServiceTenantTabCB').last();
            // create and append of the row element
            stTab.append(createServiceRowElem('', 'editInputServiceCB'));
        });

        // row element composition
        $(row).append(modalCell1);
        $(modalCell1).append(modalFieldCnt1);
        $(modalFieldCnt1).append(modalInputTxt);
        $(modalCell1).append(modalFieldLabelCnt);
        $(row).append(modalCell2)
        $(modalCell2).append(modalFieldCnt2);
        $(modalFieldCnt2).append(rmButton);

        return row;

    }

    // Handle the Edit Button click event
    function editCBSManagementButtonPressed(services) {
        /* services is initially a string with one of these values:
         *	- undefined -> if protocol !== "ngsi w/MultiService"
         * 	- a non empty of comma-separated values -> if protocol === "ngsi w/MultiService"
         */

        // set the Info section as visible
        $('#editMultiServiceTabSelector').removeClass('active');
        $('#editGeoPositionTabSelector').removeClass('active');
        $('#editSecurityTabSelector').removeClass('active');
        $('#editInfoTabSelector').addClass('active');
        $('#editServiceTenantTabCB').removeClass("active in");
        $('#editGeoPositionTabCB').removeClass("active in");
        $('#editSecurityTabCB').removeClass("active in");
        $('#editInfoTabCB').addClass("active in");

        var protocol = $("#selectProtocolCBM").val();
        if (protocol !== "ngsi w/MultiService") {

            // hide the MultiServices tab
            $('#editMultiServiceTabSelector').addClass("hidden");

            // remove form elements
            $('#editServiceCBRow1').remove();
            $('div[name="additionalRow"]').remove();

            // clear the message section
            $('#editInputServiceCBMsg').removeClass('alert-info alert');
            $('#editInputServiceCBMsg').html('');

            return;
        } else {

            // create an array from 
            services = services.split(",");

            // show the MultiServices tab
            $('#editMultiServiceTabSelector').removeClass("hidden");

            // check if the first element has already been created
            let firstRow = createFirstServiceRowElem(services[0], 'editInputServiceCB');

            if (firstRow) {
                // add first Services row element
                $('#editServiceTenantTabCB').append(firstRow);

                // add additional Services row elements
                if (services.length > 1) {
                    for (let i = 1; i < services.length; i++) {
                        // get the service/tenant tab
                        var stTab = $('#editServiceTenantTabCB').last();
                        // create and append of the row element
                        stTab.append(createServiceRowElem(services[i], 'editInputServiceCB'));
                    }
                }
            }
        }
    }

    /**
     * Trigger the MultiService tab visibility
     * The MultiService tab is visible only if the ngsi w/MultiService protocol is selected
     * When the user choose another protocol, inserted values are saved, except for empty strings
     * If the user choose back ngsi w/MultiService, saved values are restored
     */
    var editOldServicesValues = [];

    function editServicesVisibilityCheck() {

        if ($('#selectProtocolCBM').val() !== "ngsi w/MultiService") {
            // hide the MultiService selector
            $('#editMultiServiceTabSelector').addClass("hidden");

            // save the first Service row and clear the row
            firstRow = $('#editServiceCBRow1').find('.modalInputTxt');
            if (firstRow.length > 0) {
                var rowValue = firstRow.val().trim();
                if (rowValue !== "")
                    editOldServicesValues.push(rowValue);
                $('#editServiceCBRow1').remove();
            }

            // save every additional row and remove them
            var additionalRows = $('#editServiceTenantTabCB div[name="additionalRow"]');
            for (let i = 0; i < additionalRows.length; i++) {
                var rowValue = $(additionalRows[i]).find('.modalInputTxt').val().trim();
                if (rowValue !== "")
                    editOldServicesValues.push(rowValue);
                additionalRows[i].remove();
            }
        } else {
            // show the MultiService selector
            $('#editMultiServiceTabSelector').removeClass("hidden");
            // add first Services row element
            $('#editServiceTenantTabCB').append(createFirstServiceRowElem('', 'editInputServiceCB'));
            // restore elements
            restoreServicesValuesEdit(editOldServicesValues);
            editOldServicesValues = [];
        }
    }
    $('#selectProtocolCBM').change(editServicesVisibilityCheck);

    function restoreServicesValuesEdit(servicesArray) {
        // restore the first row
        $('#editServiceCBRow1').find('.modalInputTxt').val(servicesArray[0]);

        // restore additional rows
        var stTab = $('#editServiceTenantTabCB').last();
        for (let i = 1; i < servicesArray.length; i++) {
            row = createServiceRowElem(servicesArray[i], 'editInputServiceCB');
            stTab.append(row);
        }
    }


    // END EDIT BROKER MULTISERVICE SECTION

    $('#contextBrokerTable thead').css("background", "rgba(0, 162, 211, 1)");
    $('#contextBrokerTable thead').css("color", "white");
    $('#contextBrokerTable thead').css("font-size", "1.1em");

    /*$('#contextBrokerTable tbody tr').each(function(i){
     if(i%2 !== 0)
     {
     $(this).find('td').eq(0).css("background-color", "rgb(230, 249, 255)");
     $(this).find('td').eq(0).css("border-top", "none");
     }
     else
     {
     $(this).find('td').eq(0).css("background-color", "white");
     $(this).find('td').eq(0).css("border-top", "none");
     }
     }); */


    $('#displayDevicesMapCB').off('click');
    $('#displayDevicesMapCB').click(function () {

        $.ajax({
            url: "../api/contextbroker.php",
            data: {
                token: sessionToken,
                action: "get_all_contextbroker_latlong"
            },
            type: "POST",
            async: true,
            datatype: 'json',
            success: function (data)
            {

                if (data["status"] === 'ko')
                {
                    alert("An error occured when reading the data. <br/> Get in touch with the Snap4City Administrator<br/>" + data["msg"]);
                } else
                    (data["status"] === 'ok')
                {
                    var data = data["content"];


                    $("#addMap1CB").modal('show');

                    drawMapAll(data, 'searchDeviceMapModalBodyCB');

                }
            },
            error: function (data)
            {
                console.log("Ko result: " + data);
                alert("Network errors. <br/> Get in touch with the Snap4City Administrator<br/>" + JSON.stringify(data));
            }

        });

    });

    //CONTEXTBROKER AND PROTOCOL RELATION FOR EDIT DEVICE -SELECTOR 
    $("#selectProtocolCB").change(function () {
        var value = document.getElementById("selectProtocolCB").value;

        //related to subscription tab
        var kind_value = document.getElementById("selectKindCB").value;


        if (value.indexOf('ngsi') != -1)
        {
            $("#tab-addCB-4").show();
        } else
        {
            $("#tab-addCB-4").hide();
        }

    });


    $("#selectKindCB").change(function () {

        var value = document.getElementById("selectKindCB").value;

        if (value === 'internal')
        {
            $("#selectUrlOrionCallbackMsgHint").hide();
            $("#loginExternal").hide();
            $("#loginInternal").show();
            document.getElementById("inputApiKeyCB").value = '';
            document.getElementById("inputPathCB").value = '';

        } else if (value === 'external')
        {
            $("#selectUrlOrionCallbackMsgHint").show();
            $("#loginInternal").hide();
            $("#loginExternal").show();
            document.getElementById("inputLoginCB").value = '';
            document.getElementById("inputPasswordCB").value = '';
        } else if (value === '')
        {
            $("#loginInternal").hide();
            $("#loginExternal").hide();
            document.getElementById("inputLoginCB").value = '';
            document.getElementById("inputPasswordCB").value = '';
            document.getElementById("inputApiKeyCB").value = '';
            document.getElementById("inputPathCB").value = '';
        } else
        {
            document.getElementById("inputApiKeyCB").value = '';
            document.getElementById("inputPathCB").value = '';
            document.getElementById("inputLoginCB").value = '';
            document.getElementById("inputPasswordCB").value = '';

        }

        //related to subscription tab
        var protocol_value = document.getElementById("selectProtocolCB").value;



        if (protocol_value.indexOf('ngsi') != -1)
        {
            $("#tab-addCB-4").show();
        } else
        {
            $("#tab-addCB-4").hide();
        }

    });

    $("#selectProtocolCBM").change(function () {
        var value = document.getElementById("selectProtocolCBM").value;

        //related to subscription tab
        var kind_value = document.getElementById("selectKindCBM").value;


        if (value.indexOf('ngsi') != -1)
        {
            $("#tab-editCB-4").show();
        } else
        {
            $("#tab-editCB-4").hide();
        }

    });

    $("#selectKindCBM").change(function () {

        var value = document.getElementById("selectKindCBM").value;

        //TO CHECK: do you need here the same code as depicted above? dinamically update something on base of this edit???

        //related to subscription tab
        var protocol_value = document.getElementById("selectProtocolCBM").value;


        if (protocol_value.indexOf('ngsi') != -1)
        {
            $("#tab-editCB-4").show();
        } else
        {
            $("#tab-editCB-4").hide();
        }

    });



    //Validation of the name of the new owner during typing
    $('#newOwner').on('input', function (e)
    {

        if ($(this).val().trim() === '')
        {
            $('#newOwnerMsg').css('color', '#f3cf58');
            $('#newOwnerMsg').html('New owner username can\'t be empty');
            $('#newOwnershipConfirmBtn').addClass('disabled');
        } else
        {
            //if(($(this).val().trim() === "<?= $_SESSION['loggedUsername'] ?>")&&("<?= $_SESSION['loggedRole'] ?>" !== "RootAdmin"))
            if (($(this).val().trim() === loggedUser) && (loggedRole !== "RootAdmin") && (loggedRole !== "ToolAdmin"))

            {
                $('#newOwnerMsg').css('color', '#f3cf58');
                $('#newOwnerMsg').html('New owner can\'t be you');
                $('#newOwnershipConfirmBtn').addClass('disabled');
            } else
            {
                $('#newOwnerMsg').css('color', 'white');
                $('#newOwnerMsg').html('User can be new owner');
                $('#newOwnershipConfirmBtn').removeClass('disabled');
            }
        }
    });

    // DELEGATIONS
    function updateGroupList(ouname) {
        $.ajax({
            url: "../api/ldap.php",
            data: {
                action: "get_group_for_ou",
                ou: ouname,
                token: sessionToken
            },
            type: "POST",
            async: true,
            success: function (data)
            {
                if (data["status"] === 'ko')
                {
                    $('#newDelegatedMsgGroup').css('color', '#f3cf58');
                    $('#newDelegatedMsgGroup').html(data["msg"]);
                } else if (data["status"] === 'ok')
                {
                    var $dropdown = $("#newDelegationGroup");
                    //remove old ones
                    $dropdown.empty();
                    //adding empty to rootadmin
                    if ((loggedRole == 'RootAdmin') || (loggedRole == 'ToolAdmin')) {
                        $dropdown.append($("<option />").val("All groups").text("All groups"));
                    }
                    //add new ones
                    $.each(data['content'], function () {
                        $dropdown.append($("<option />").val(this).text(this));
                    });

                }
            },
            error: function (data)
            {
                $('#newDelegatedMsgGroup').css('color', '#f3cf58');
                $('#newDelegatedMsgGroup').html('Error calling internal API');
            }
        });
    }

    //populate organization list with any possibile value (if rootAdmin)
    if ((loggedRole == 'RootAdmin') || (loggedRole == 'ToolAdmin')) {
        $.ajax({
            url: "../api/ldap.php",
            data: {
                action: "get_all_ou",
                token: sessionToken
            },
            type: "POST",
            async: false,
            success: function (data)
            {
                if (data["status"] === 'ko')
                {
                    $('#newDelegatedMsgGroup').css('color', '#f3cf58');
                    $('#newDelegatedMsgGroup').html(data["msg"]);
                } else if (data["status"] === 'ok')
                {
                    var $dropdown = $("#newDelegationOrganization");
                    $.each(data['content'], function () {
                        $dropdown.append($("<option />").val(this).text(this));
                    });
                }
            },
            error: function (data)
            {
                $('#newDelegatedMsgGroup').css('color', '#f3cf58');
                $('#newDelegatedMsgGroup').html('Error calling internal API');
            }
        });
    }
    //populate organization list with myorganization (otherwise)
    else {
        $.ajax({
            url: "../api/ldap.php",
            data: {
                action: "get_logged_ou",
                token: sessionToken
            },
            type: "POST",
            async: false,
            success: function (data)
            {
                if (data["status"] === 'ko')
                {
                    console.log("Error: " + data);
                    //TODO: manage error
                } else if (data["status"] === 'ok')
                {
                    var $dropdown = $("#newDelegationOrganization");
                    $dropdown.append($("<option/>").val(data['content']).text(data['content']));
                }
            },
            error: function (data)
            {
                console.log("Error: " + data);
                //TODO: manage error
            }
        });
    }

    //populate group list with selected organization
    updateGroupList($("#newDelegationOrganization").val());

    //eventually update the group list
    $('#newDelegationOrganization').change(function () {
        $(this).find(":selected").each(function () {
            updateGroupList($(this).val());
        });
    });

    $('#newDelegation').val('');

    $('#newDelegation').off('input');

    $('#newDelegation').on('input', function (e)
    {
        if ($(this).val().trim() === '')
        {
            $('#newDelegatedMsg').css('color', '#f3cf58');
            $('#newDelegatedMsg').html('Delegated username can\'t be empty');
            $('#newDelegationConfirmBtn').addClass('disabled');
        } else
        {
            $('#newDelegatedMsg').css('color', 'white');
            $('#newDelegatedMsg').html('User can be delegated');
            $('#newDelegationConfirmBtn').removeClass('disabled');

            $('#delegationsTable tbody tr').each(function (i)
            {
                if ($(this).attr('data-delegated').trim() === $('#newDelegation').val())
                {
                    $('#newDelegatedMsg').css('color', '#f3cf58');
                    $('#newDelegatedMsg').html('User already delegated');
                    $('#newDelegationConfirmBtn').addClass('disabled');
                }
            });
        }
    });

    $('#valuesTable thead').css("background", "rgba(0, 162, 211, 1)");
    $('#valuesTable thead').css("color", "white");
    $('#valuesTable thead').css("font-size", "1em");

    $('#valuesTable tbody tr').each(function (i) {
        if (i % 2 !== 0)
        {
            $(this).find('td').eq(0).css("background-color", "rgb(230, 249, 255)");
            $(this).find('td').eq(0).css("border-top", "none");
        } else
        {
            $(this).find('td').eq(0).css("background-color", "white");
            $(this).find('td').eq(0).css("border-top", "none");
        }
    });

    $('#delegationsModal').on('hidden.bs.modal', function (e)
    {
        $(this).removeData();
    });

}); // end of ready function			

var internalDest = false;
var tableFirstLoad = true;

$('.internalLink').on('mousedown', function () {
    internalDest = true;
});

setGlobals(loggedRole, usr, loggedType, userVisibilitySet);

$("#logoutBtn").off("click");
$("#logoutBtn").click(function (event)
{
    event.preventDefault();
    location.href = "logout.php";

});

function updateCBTimeout()
{
    $("#editCBOkModal").modal('hide');
    setTimeout(function () {
        location.reload();
    }, 500);
}




//   START TO CHANGE THE VISIBILITY  & OWNERSHIP 

function changeVisibility(name, visibility, obj_organization, accesslink) {
    $("#delegationsModal").modal('show');
    $("#delegationHeadModalLabel").html("Device - " + name);

    if (visibility == 'MyOwnPrivate') {
        newVisibility = 'public';
        $('#visID').css('color', '#f3cf58');
        $("#visID").html("Visibility - Private");
        document.getElementById('newVisibilityPrivateBtn').style.visibility = 'hidden';
        document.getElementById('newVisibilityPublicBtn').style.visibility = 'show';

    } else

    {
        newVisibility = 'private';
        $('#visID').css('color', '#f3cf58');
        $("#visID").html("Visibility - Public");
        document.getElementById('newVisibilityPrivateBtn').style.visibility = 'show';
        document.getElementById('newVisibilityPublicBtn').style.visibility = 'hidden';
    }

    $(document).on("click", "#newVisibilityPublicBtn", function (event) {
        $.ajax({
            url: "../api/contextbroker.php",
            data:
                    {
                        action: "change_visibility",
                        username: loggedUser,
                        organization: organization,
                        obj_organization: obj_organization,
                        name: name,
                        object: "BrokerID",
                        table: "contextbroker",
                        accesslink: accesslink,
                        visibility: newVisibility,
                        token: sessionToken
                    },
            type: "POST",
            async: true,
            dataType: 'json',
            success: function (data)
            {
                if (data["status"] === 'ok')
                {
                    $('#newVisibilityResultMsg').show();
                    $("#visID").html("");
                    $('#visID').css('color', '#f3cf58');
                    $("#visID").html("Visibility - Private");
                    $('#newVisibilityResultMsg').html('New visibility set to Public');

                    $('#newVisibilityPublicBtn').addClass('disabled');

                    setTimeout(function ()
                    {
                        $('#devicesTable').DataTable().destroy();
                        fetch_data(true);
                        location.reload();
                    }, 3000);
                } else if (data["status"] === 'ko')
                {
                    $('#newVisibilityResultMsg').show();
                    $('#newVisibilityResultMsg').html('Error setting new visibility');
                    $('#newVisibilityPublicBtn').addClass('disabled');

                    setTimeout(function ()
                    {
                        $('#newVisibilityPublicBtn').removeClass('disabled');
                        $('#newVisibilityResultMsg').html('');
                        $('#newVisibilityResultMsg').hide();
                    }, 3000);
                } else {
                    console.log(data);
                }
            },
            error: function (errorData)
            {
                $('#newVisibilityResultMsg').show();
                $('#newVisibilityResultMsg').html('Error setting new visibility');
                $('#newVisibilityPublicBtn').addClass('disabled');

                setTimeout(function ()
                {
                    $('#newVisibilityPublicBtn').removeClass('disabled');
                    $('#newVisibilityResultMsg').html('');
                    $('#newVisibilityResultMsg').hide();
                }, 3000);
            }
        });
    });


    $(document).on("click", "#newVisibilityPrivateBtn", function (event) {
        $.ajax({
            url: "../api/contextbroker.php",
            data:
                    {
                        action: "change_visibility",
                        name: name,
                        object: "BrokerID",
                        table: "contextbroker",
                        accesslink: accesslink,
                        visibility: newVisibility,
                        token: sessionToken
                    },
            type: "POST",
            async: true,
            dataType: 'json',
            success: function (data)
            {
                if (data["status"] === 'ok')
                {
                    $('#newVisibilityResultMsg').show();
                    $('#newVisibilityResultMsg').html('New visibility set Private');
                    $('#newVisibilityPrivateBtn').addClass('disabled');
                    setTimeout(function ()
                    {
                        $('#devicesTable').DataTable().destroy();
                        fetch_data(true);
                        location.reload();
                    }, 3000);
                } else if (data["status"] === 'ko')
                {
                    $('#newVisibilityResultMsg').show();
                    $('#newVisibilityResultMsg').html('Error setting new visibility');
                    $('#newVisibilityPrivateBtn').addClass('disabled');

                    setTimeout(function ()
                    {
                        $('#newVisibilityPrivateBtn').removeClass('disabled');
                        $('#newVisibilityResultMsg').html('');
                        $('#newVisibilityResultMsg').hide();
                    }, 3000);
                } else {
                    console.log(data);
                }
            },
            error: function (errorData)
            {
                $('#newVisibilityResultMsg').show();
                $('#newVisibilityResultMsg').html('Error setting new visibility');
                $('#newVisibilityPrivateBtn').addClass('disabled');

                setTimeout(function ()
                {
                    $('#newVisibilityPrivateBtn').removeClass('disabled');
                    $('#newVisibilityResultMsg').html('');
                    $('#newVisibilityResultMsg').hide();
                }, 3000);
            }
        });
    });

    $(document).on("click", "#newOwnershipConfirmBtn", function (event) {
        $.ajax({
            url: "../api/contextbroker.php",
            data: {
                action: "change_owner",
                table: "contextbroker",
                object: "BrokerID",
                name: name,
                accesslink: accesslink,
                newOwner: $('#newOwner').val(),
                token: sessionToken
            },
            type: "POST",
            async: true,
            dataType: 'json',
            success: function (data)
            {
                if (data["status"] === 'ok')
                {
                    $('#newOwner').val('');
                    $('#newOwner').addClass('disabled');
                    $('#newOwnershipResultMsg').show();
                    $('#newOwnershipResultMsg').html('New ownership set correctly');
                    $('#newOwnershipConfirmBtn').addClass('disabled');


                    setTimeout(function ()
                    {
                        $('#devicesTable').DataTable().destroy();
                        fetch_data(true);
                        location.reload();
                    }, 3000);
                } else if (data["status"] === 'ko')
                {
                    $('#newOwner').addClass('disabled');
                    $('#newOwnershipResultMsg').html('Error setting new ownership: please try again');
                    $('#newOwnershipConfirmBtn').addClass('disabled');

                    setTimeout(function ()
                    {
                        $('#newOwner').removeClass('disabled');
                        $('#newOwnershipResultMsg').html('');
                        $('#newOwnershipResultMsg').hide();
                    }, 3000);
                } else {
                    console.log(data);
                }
            },
            error: function (errorData)
            {
                $('#newOwner').addClass('disabled');
                $('#newOwnershipResultMsg').html('Error setting new ownership: please try again');
                $('#newOwnershipConfirmBtn').addClass('disabled');

                setTimeout(function ()
                {
                    $('#newOwner').removeClass('disabled');
                    $('#newOwnershipResultMsg').html('');
                    $('#newOwnershipResultMsg').hide();
                }, 3000);
            }
        });
    });



    $("#delegationsCancelBtn").off("click");
    $("#delegationsCancelBtn").on('click', function () {
        $('#newDelegation').val("");
        $('#newDelegationGroup').val("");
        $('#newDelegationOrganization').val("");
        $('#newOwner').val("");
        $("#newVisibilityResultMsg").html("");
        $("#newOwnershipResultMsg").html("");
        location.reload();
        $('#delegationsModal').modal('hide');
    });


    $.ajax({
        url: "../api/contextbroker.php", //Checking the delegation table
        data:
                {

                    action: "get_delegations", // check the action and to be specified
                    accesslink: accesslink,
                    name: name,
                    object: "BrokerID",
                    token: sessionToken,
                },
        type: "POST",
        async: true,
        dataType: 'json',
        success: function (data)
        {

            if (data["status"] == 'ok')
            {

                console.log(JSON.stringify(data));
                delegations = data["delegation"];
                $('#delegationsTable tbody').html("");
                $('#delegationsTableGroup tbody').html("");

                for (var i = 0; i < delegations.length; i++)
                {

                    if ((delegations[i].userDelegated != "ANONYMOUS") && (delegations[i].userDelegated != null)) {


                        $('#delegationsTable tbody').append('<tr class="delegationTableRow" data-delegationId="' + delegations[i].delegationId + '" data-delegated="' + delegations[i].userDelegated + '"><td class="delegatedName">' + delegations[i].userDelegated + '</td><td><i class="fa fa-remove removeDelegationBtn"></i></td></tr>');


                    } else if (delegations[i].groupDelegated != null) {

                        //extract cn and ou
                        var startindex = delegations[i].groupDelegated.indexOf("cn=");
                        var endindex_gr = delegations[i].groupDelegated.indexOf(",");
                        var gr = delegations[i].groupDelegated.substring(3, endindex_gr);
                        var endindex_ou = delegations[i].groupDelegated.indexOf(",", endindex_gr + 1);
                        var ou = delegations[i].groupDelegated.substring(endindex_gr + 4, endindex_ou);


                        var DN = "";
                        if (startindex != -1) {
                            DN = ou + "," + gr;
                        } else {
                            DN = gr;
                        }


                        $('#delegationsTableGroup tbody').append('<tr class="delegationTableRowGroup" data-delegationId="' + delegations[i].delegationId + '" data-delegated="' + ou + "," + gr + '"><td class="delegatedName">' + DN + '</td><td><i class="fa fa-remove removeDelegationBtnGroup"></i></td></tr>');

                    }

                }
                $('#delegationsTable tbody').on("click", "i.removeDelegationBtn", function () {
                    var rowToRemove = $(this).parents('tr');
                    $.ajax({
                        url: "../api/contextbroker.php", //check the url
                        data:
                                {
                                    action: "remove_delegation", // to be specified
                                    token: sessionToken,
                                    delegationId: $(this).parents('tr').attr('data-delegationId')
                                },
                        type: "POST",
                        async: true,
                        dataType: 'json',
                        success: function (data)
                        {
                            if (data["status"] === 'ok')
                            {
                                rowToRemove.remove();
                            } else
                            {
                                console.log("error removing delegation");
                            }
                        },
                        error: function (errorData)
                        {
                            console.log("error in call for removing delegation");
                        }
                    });
                });
                $('#delegationsTableGroup tbody').on("click", "i.removeDelegationBtnGroup", function () {
                    var rowToRemove = $(this).parents('tr');
                    $.ajax({
                        url: "../api/contextbroker.php", //check the url
                        data:
                                {
                                    action: "remove_delegation",
                                    token: sessionToken,
                                    delegationId: $(this).parents('tr').attr('data-delegationId')
                                },
                        type: "POST",
                        async: true,
                        dataType: 'json',
                        success: function (data)
                        {
                            if (data["status"] === 'ok')
                            {
                                rowToRemove.remove();
                            } else
                            {
                                console.log("error removing delegation");
                            }
                        },
                        error: function (errorData)
                        {
                            console.log("error in the call ro remove delegation");
                        }
                    });
                });

            } else
            {
                console.log(json_encode(data));
            }
        },
        error: function (errorData)
        {
            console.log(errorData);//TBD  insert a message of error
        }
    });


    //listen about the confimation
    $(document).on("click", "#newDelegationConfirmBtn", function (event) {
        var newDelegation = document.getElementById('newDelegation').value;
        $.ajax({
            url: "../api/contextbroker.php", //which api to use
            data:
                    {
                        action: "add_delegation",
                        accesslink: accesslink,
                        obj_name: name,
                        object: "BrokerID",
                        token: sessionToken,
                        delegated_user: newDelegation,
                    },
            type: "POST",
            async: true,
            dataType: 'json',
            success: function (data)
            {
                if (data["status"] === 'ok')
                {
                    $('#delegationsTable tbody').append('<tr class="delegationTableRow" data-delegationId="' + data["delegationId"] + '" data-delegated="' + $('#newDelegation').val() + '"><td class="delegatedName">' + $('#newDelegation').val() + '</td><td><i class="fa fa-remove removeDelegationBtn"></i></td></tr>');


                    $('#newDelegation').val('');
                    $('#newDelegation').addClass('disabled');
                    $('#newDelegatedMsg').css('color', 'white');
                    $('#newDelegatedMsg').html('New delegation added correctly');
                    $('#newDelegationConfirmBtn').addClass('disabled');

                    setTimeout(function ()
                    {
                        $('#newDelegation').removeClass('disabled');
                        $('#newDelegatedMsg').css('color', '#f3cf58');
                        $('#newDelegatedMsg').html('Delegated username can\'t be empty');
                    }, 1500);
                } else
                {
                    var errorMsg = null;


                    $('#newDelegation').val('');
                    $('#newDelegation').addClass('disabled');
                    $('#newDelegatedMsg').css('color', '#f3cf58');
                    $('#newDelegatedMsg').html(data["msg"]);
                    $('#newDelegationConfirmBtn').addClass('disabled');

                    setTimeout(function ()
                    {
                        $('#newDelegation').removeClass('disabled');
                        $('#newDelegatedMsg').css('color', '#f3cf58');
                        $('#newDelegatedMsg').html('Delegated username can\'t be empty');
                    }, 2000);
                }
            },
            error: function (errorData)
            {
                var errorMsg = "Error calling internal API";
                $('#newDelegation').val('');
                $('#newDelegation').addClass('disabled');
                $('#newDelegatedMsg').css('color', '#f3cf58');
                $('#newDelegatedMsg').html(errorMsg);
                $('#newDelegationConfirmBtn').addClass('disabled');

                setTimeout(function ()
                {
                    $('#newDelegation').removeClass('disabled');
                    $('#newDelegatedMsg').css('color', '#f3cf58');
                    $('#newDelegatedMsg').html('Delegated username can\'t be empty');
                }, 2000);
            }
        });

    });

    //group delegation -start------------------------------------------------------------------------------------------------------------
    $(document).on("click", "#newDelegationConfirmBtnGroup", function (event) {

        var delegatedDN = "";
        var e = document.getElementById("newDelegationGroup");
        if ((typeof e.options[e.selectedIndex] !== 'undefined') && (e.options[e.selectedIndex].text !== 'All groups')) {
            delegatedDN = "cn=" + e.options[e.selectedIndex].text + ",";
        }
        var e2 = document.getElementById("newDelegationOrganization");
        delegatedDN = delegatedDN + "ou=" + e2.options[e2.selectedIndex].text;

        $.ajax({
            url: "../api/contextbroker.php",
            data:
                    {
                        action: "add_delegation",
                        accesslink: accesslink,
                        obj_name: name,
                        object: "BrokerID",
                        token: sessionToken,
                        delegated_group: delegatedDN
                    },
            type: "POST",
            async: true,
            dataType: 'json',
            success: function (data)
            {
                if (data["status"] === 'ok')
                {
                    var toadd = $('#newDelegationOrganization').val();
                    if (document.getElementById("newDelegationGroup").options[e.selectedIndex].text != '') {
                        toadd = toadd + "," + $('#newDelegationGroup').val();
                    }

                    $('#delegationsTableGroup tbody').append('<tr class="delegationTableRowGroup" data-delegationId="' + data["delegationId"] + '" data-delegated="' + toadd + '"><td class="delegatedNameGroup">' + toadd + '</td><td><i class="fa fa-remove removeDelegationBtnGroup"></i></td></tr>');
                    $('#newDelegatedMsgGroup').css('color', 'white');
                    $('#newDelegatedMsgGroup').html('New delegation added correctly');

                    setTimeout(function ()
                    {
                        $('#newDelegatedMsgGroup').css('color', '#f3cf58');
                        $('#newDelegatedMsgGroup').html('Delegated groupname can\'t be empty');
                    }, 1500);
                } else
                {
                    var errorMsg = null;
                    $('#newDelegatedMsgGroup').css('color', '#f3cf58');
                    $('#newDelegatedMsgGroup').html(data["msg"]);

                    setTimeout(function ()
                    {
                        $('#newDelegationGroup').removeClass('disabled');
                        $('#newDelegationOrganization').removeClass('disabled');
                        $('#newDelegatedMsgGroup').css('color', '#f3cf58');
                        $('#newDelegatedMsgGroup').html('Delegated groupname can\'t be empty');
                    }, 2000);
                }
            },
            error: function (errorData)
            {
                var errorMsg = "Error calling internal API";
                $('#newDelegatedMsgGroup').css('color', '#f3cf58');
                $('#newDelegatedMsgGroup').html(errorMsg);

                setTimeout(function ()
                {
                    $('#newDelegatedMsgGroup').css('color', '#f3cf58');
                    $('#newDelegatedMsgGroup').html('Delegated groupname can\'t be empty');
                }, 2000);
            }
        });
    });     //group delegation -end

}

// END TO CHANGE THE VISIBILITY 		




/* Related to the Map */


function drawMap1(latitude, longitude, flag) {
    var marker;
    if (typeof map !== 'undefined') {
        map.remove();
    }
    if (flag == 0) { /*add position when create the device */
        var centerMapArr = gpsCentreLatLng.split(",", 2);
        var centerLat = parseFloat(centerMapArr[0].trim());
        var centerLng = parseFloat(centerMapArr[1].trim());
        map = L.map('addLatLong').setView([centerLat, centerLng], zoomLevel);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        window.node_input_map = map;
        //L.marker([latitude,longitude]).addTo(map).bindPopup("Hi DEVICE");
        setTimeout(function () {
            map.invalidateSize()
        }, 400);

        map.on("click", function (e) {

            var lat = e.latlng.lat;
            var lng = e.latlng.lng;
            lat = lat.toFixed(5);
            lng = lng.toFixed(5);


            document.getElementById('inputLatitudeCB').value = lat;
            document.getElementById('inputLongitudeCB').value = lng;
            //checkAddCbLatitude();
            //checkAddCbLongitude();
            addCbConditionsArray['inputLatitudeCB'] = true;
            checkCbLatitude();
            checkAddCbConditions();
            addCbConditionsArray['inputLongitudeDevice'] = true;
            checkCbLongitude();
            checkAddCbConditions();

            // addCbConditionsArray['inputLatitudeCB'] = true;
            // addCbConditionsArray['inputLongitudeCB'] = true;
            // checkCbLatitude();
            // checkCbLongitude();
            if (marker) {
                map.removeLayer(marker);
            }
            marker = new L.marker([lat, lng]).addTo(map).bindPopup(lat + ',' + lng);

        });


    } else if (flag == 1) { /*edit position when edit the device */
        map = L.map('addLatLongEdit').setView([latitude, longitude], 10);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        window.node_input_map = map;
        //L.marker([latitude,longitude]).addTo(map).bindPopup("Hi DEVICE");
        setTimeout(function () {
            map.invalidateSize()
        }, 400);

        marker = new L.marker([latitude, longitude]).addTo(map).bindPopup(longitude, longitude);
        map.on("click", function (e) {
            var lat = e.latlng.lat;
            var lng = e.latlng.lng;
            lat = lat.toFixed(5);
            lng = lng.toFixed(5);

            document.getElementById('inputLatitudeCBM').value = lat;
            document.getElementById('inputLongitudeCBM').value = lng;
            // checkEditCbLatitude();
            //  checkEditCbLongitude();
            editCbConditionsArray['inputLatitudeCBM'] = true;
            editCbConditionsArray['inputLongitudeCBM'] = true;

            if (marker) {
                map.removeLayer(marker);
            }
            marker = new L.marker([lat, lng]).addTo(map).bindPopup(lat + ',' + lng);

        });


    } else if (flag == 2) {
        map = L.map('addLatLongEdit').setView([latitude, longitude], 10);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        //window.node_input_map = map;
        //L.marker([latitude,longitude]).addTo(map).bindPopup("Hi DEVICE");
        setTimeout(function () {
            map.invalidateSize()
        }, 400);
        marker = L.marker([latitude, longitude]).addTo(map).bindPopup(latitude + ',' + longitude);

        map.off("click");
    }





}






function drawMapAll(data, divName) {
    var latitude = 43.7800;
    var longitude = 11.2300;

    if (typeof map_all === 'undefined' || !map_all) {
        // map_all = L.map(divName).setView([latitude,longitude], 10);
        var centerMapArr = gpsCentreLatLng.split(",", 2);
        var centerLat = parseFloat(centerMapArr[0].trim());
        var centerLng = parseFloat(centerMapArr[1].trim());
        map_all = L.map(divName).setView([centerLat, centerLng], zoomLevel);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map_all);
        window.node_input_map = map_all;

        /**************************Fatima-start******************************/
        /*var blueIcon = L.icon({
         iconUrl: 'data:image/svg+xml;utf-8, \
         <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Capa_1" x="0px" y="0px" width="512px" height="512px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve"><g><path d="M256,0C167.641,0,96,71.625,96,160c0,24.75,5.625,48.219,15.672,69.125C112.234,230.313,256,512,256,512l142.594-279.375   C409.719,210.844,416,186.156,416,160C416,71.625,344.375,0,256,0z M256,256c-53.016,0-96-43-96-96s42.984-96,96-96   c53,0,96,43,96,96S309,256,256,256z" fill="#006DF0"/></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g></svg>',
         iconSize:     [38, 95], // size of the icon
         popupAnchor:  [-3, -76] // point from which the popup should open relative to the iconAnchor
         });
         //from https://www.flaticon.com/free-icon/map-marker_33622#
         
         var redIcon = L.icon({
         iconUrl: 'data:image/svg+xml;utf-8, \
         <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Capa_1" x="0px" y="0px" width="512px" height="512px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve"><g><path d="M256,0C167.641,0,96,71.625,96,160c0,24.75,5.625,48.219,15.672,69.125C112.234,230.313,256,512,256,512l142.594-279.375   C409.719,210.844,416,186.156,416,160C416,71.625,344.375,0,256,0z M256,256c-53.016,0-96-43-96-96s42.984-96,96-96   c53,0,96,43,96,96S309,256,256,256z" fill="#D80027"/></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g></svg>',
         iconSize:     [38, 95], // size of the icon
         popupAnchor:  [-3, -76] // point from which the popup should open relative to the iconAnchor
         });*/

        /**************************Fatima-end******************************/

        /*************Fatima2-start*************/
        green_markersGroup = undefined;
        marker_selection = [];

        redIcon = new L.Icon({

            iconUrl: '../img/markerPrivate.png',
            iconSize: new L.Point(32, 32),
            iconAnchor: new L.Point(16, 16),
            popupAnchor: new L.Point(0, -18)

        });

        blueIcon = new L.Icon({

            iconUrl: '../img/markerPublic.png',
            iconSize: new L.Point(32, 32),
            iconAnchor: new L.Point(16, 16),
            popupAnchor: new L.Point(0, -18)

        });
        greenIcon = new L.Icon({

            iconUrl: '../img/markerGreen.png',
            iconSize: new L.Point(32, 32),
            iconAnchor: new L.Point(16, 16),
            popupAnchor: new L.Point(0, -18)

        });
        /*************Fatima2-end**************/

        var mapLayers = {};
        drawnItems = new L.FeatureGroup();
        map_all.addLayer(drawnItems);

        var editControl = new L.Control.Draw({
            draw: false,
            edit: {
                //Fatima2-add-line
                remove: false,
                featureGroup: drawnItems,
                poly: {
                    allowIntersection: false
                }
            }
        });
        map_all.addControl(editControl);

        drawControl = new L.Control.Draw({
            //Fatima2-add-line
            remove: false,
            draw: {
                position: 'topleft',
                //polyline: false,
                //marker: false,
                circlemarker: false,
                //polygon: false,
                rectangle: false,
                polygon: {
                    allowIntersection: false,
                    showArea: true
                }
            }
        });
        map_all.addControl(drawControl);

        L.control.layers(mapLayers, {
            'drawlayer': drawnItems
        }, {
            collapsed: true
        }).addTo(map_all);

        map_all.on(L.Draw.Event.CREATED, function (e) {
            var fence = e.layer;
            if (drawnItems.hasLayer(fence) == false) {
                drawnItems.addLayer(fence);
            }

            drawControl.remove();
            TYPE = e.layerType;
            layer = e.layer;

            var resultsOut = drawSelection(layer, TYPE, data);
            $('#addMap1CB').modal('hide');
            //Fatima2-moveAndupdate-1-line
            colorSelectedMarkers(resultsOut, greenIcon);
            fetch_data(true, JSON.stringify(resultsOut));


        });

        map_all.on('draw:edited', function (e) {
            var fences = e.layers;
            fences.eachLayer(function (fence) {
                fence.shape = "geofence";
                if (drawnItems.hasLayer(fence) == false) {
                    drawnItems.addLayer(fence);
                }
            });
            drawnItems.eachLayer(function (layer) {
                var resultsOut = drawSelection(layer, TYPE, data);
                $('#addMap1CB').modal('hide');
                //Fatima2-moveAndupdate-1-line
                colorSelectedMarkers(resultsOut, greenIcon);
                fetch_data(true, JSON.stringify(resultsOut));
            });


        });


        /******************Fatima-start*************************/
        /* map_all.on('draw:deleted', function(e) {
         drawControl.addTo(map_all);
         });*/
        L.Control.RemoveAll = L.Control.extend(
                {
                    options:
                            {
                                position: 'topleft',
                            },
                    onAdd: function (map_all) {
                        var controlDiv = L.DomUtil.create('div', 'leaflet-draw-toolbar leaflet-bar');
                        L.DomEvent
                                .addListener(controlDiv, 'click', L.DomEvent.stopPropagation)
                                .addListener(controlDiv, 'click', L.DomEvent.preventDefault)
                                .addListener(controlDiv, 'click', function () {
                                    drawnItems.clearLayers();

                                    if (typeof green_markersGroup != 'undefined') {
                                        map_all.removeLayer(green_markersGroup);
                                        green_markersGroup = undefined;
                                        green_marker_array = [];
                                        marker_selection = [];
                                        //Fatima2-moveLine 
                                        fetch_data(true);
                                    }

                                    drawControl.addTo(map_all);

                                });

                        var controlUI = L.DomUtil.create('a', 'leaflet-draw-edit-remove', controlDiv);
                        controlUI.title = 'Delete';
                        controlUI.href = '#';
                        return controlDiv;
                    }


                });
        var removeAllControl = new L.Control.RemoveAll();
        map_all.addControl(removeAllControl);

        /******************Fatima-end***************************/


        for (var i = 0; i < data.length; i++) {


            var mylat = data[i].latitude;
            var mylong = data[i].longitude;
            var myname = data[i].name;
            if (mylat != null && mylong != null) {



                if (data[i].visibility == "public") {
                    m = L.marker([mylat, mylong], {icon: blueIcon}).addTo(map_all).bindPopup(myname);
                } else {
                    m = L.marker([mylat, mylong], {icon: redIcon}).addTo(map_all).bindPopup(myname);
                }

            }
        }
        setTimeout(function () {
            map_all.invalidateSize()
        }, 400);
    }
}




/**********************Fatima2-start*****************************/

function colorSelectedMarkers(selections, greenIcon) {
    green_marker_array = [];
    for (var k in selections) {

        lat = Number(selections[k].latitude);
        lng = Number(selections[k].longitude);
        popup = selections[k].name;
        var m = L.marker([lat, lng], {icon: greenIcon}).bindPopup(popup);
        green_marker_array.push(m);
    }


    if (typeof green_markersGroup != 'undefined') {
        //Fatima2-adjust
        map_all.removeLayer(green_markersGroup);
    }
    green_markersGroup = L.layerGroup(green_marker_array);
    green_markersGroup.addTo(map_all);
    marker_selection = selections;

}

/**********************Fatima-end******************************/

function drawSelection(layer, type, data) {
    var resultsOut = [];
    switch (type) {

        case 'circle':
            circles = {};

            drawnItems.eachLayer(function (layer) {
                circles[layer.nodeID] = layer.toGeoJSON();
                circles[layer.nodeID].properties.radius = Math.round(layer.getRadius()) / 1000;
            });


            var lat_map = (circles[layer.nodeID].geometry.coordinates[1]);
            var long_map = (circles[layer.nodeID].geometry.coordinates[0]);
            var center_latlong = new L.LatLng(lat_map, long_map);
            var rad_map = (circles[layer.nodeID].properties.radius);


            for (var deviceTocheck in data) {


                var deviceLatLng = new L.LatLng(Number(data[deviceTocheck]["latitude"]), Number(data[deviceTocheck]["longitude"]));


                if (Math.abs(center_latlong.distanceTo(deviceLatLng) / 1000) <= rad_map) {

                    resultsOut.push(data[deviceTocheck]);

                }
            }

            break;
        case 'polygon':

            var polyPoints = layer._latlngs[0];
            for (var deviceTocheck in data) {

                //Ray Casting algorithm for checking if a point lies inside of a polygon
                var x = Number(data[deviceTocheck]["latitude"]), y = Number(data[deviceTocheck]["longitude"]);

                var inside = false;
                for (var i = 0, j = polyPoints.length - 1; i < polyPoints.length; j = i++) {
                    var xi = polyPoints[i].lat, yi = polyPoints[i].lng;
                    var xj = polyPoints[j].lat, yj = polyPoints[j].lng;

                    var intersect = ((yi > y) != (yj > y)) && (x < (xj - xi) * (y - yi) / (yj - yi) + xi);
                    if (intersect) {
                        inside = !inside;
                    }
                }

                if (inside) {

                    resultsOut.push(data[deviceTocheck]);

                }
            }
            break;
        case 'marker':

            var markerPoint = layer.getLatLng();

            for (var deviceTocheck in data) {

                var deviceLatLng = new L.LatLng(Number(data[deviceTocheck]["latitude"]), Number(data[deviceTocheck]["longitude"]));


                if (Math.abs(markerPoint.distanceTo(deviceLatLng) / 1000) <= 1) { //1 km 

                    resultsOut.push(data[deviceTocheck]);

                }
            }
            break;
        case 'polyline':

            var polyVerts = layer._latlngs;

            for (var deviceTocheck in data) {

                isclose = false;

                var deviceLatLng = new L.LatLng(Number(data[deviceTocheck]["latitude"]), Number(data[deviceTocheck]["longitude"]));

                for (var vi = 0, vl = polyVerts.length; vi < vl; vi++) {
                    var d = polyVerts[vi].distanceTo(deviceLatLng);
                    if (d / 1000 <= 1) {
                        isclose = true;
                        break;
                    }
                }

                if (isclose) {
                    resultsOut.push(data[deviceTocheck]);
                }
            }
            break;


    }

    return resultsOut;
}

