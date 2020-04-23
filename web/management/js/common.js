//---------------------------------------------------------------------------------------------------------------------------------------------managing valuetype-valueunit
function valueTypeChanged(indice){

       //get new value type that has been selected
       valueTypeNew=$("#value_type"+indice).val();

       //remove old value units
       valueUnitNew=$("#value_unit"+indice).find("option").remove().end();

       //retrieve valid value units basing on new value type selected (selected value unit is discarged)
       validValueUnit=getValidValueUnit(valueTypeNew, "");

       //if there are any valid value units, present to the users
        if (validValueUnit!==""){
               if (!validValueUnit.includes('selected')){
                       valueUnitNew.append("<option hidden disabled selected value=\"NOT VALID OPTION\"> -- select an option -- </option>");
                       //update msg_value_unit
                       $("#value_unit"+indice).parent().siblings().last().css("color", "red");
                       $("#value_unit"+indice).parent().siblings().last().html("Value unit is mandatory");
               }
                valueUnitNew.append(validValueUnit);
        }

       //update msg_value_type
       $("#value_type"+indice).parent().siblings().last().css("color", "#337ab7");
       $("#value_type"+indice).parent().siblings().last().html("Ok");
 }

function valueUnitChanged(indice){

        //update msg_value_unit
        $("#value_unit"+indice).parent().siblings().last().css("color", "#337ab7");
        $("#value_unit"+indice).parent().siblings().last().html("Ok");
}

function getValidValueUnit(valueType, selectedValueUnit){

       valueTypeObj="";
       toReturn="";

       //get value type STRUCTURE for passed valueType
       for (var n=0; n < gb_value_types.length; n++)
               if (gb_value_types[n].value===valueType)
                       valueTypeObj = gb_value_types[n];

        //console.log("Get validValueUnit for "+valueType);
        //console.log("Accepte valueUnit are"+JSON.stringify(valueTypeObj));

       //add new value unit OPTIONS according to new value type STRUCTURE
       if (valueTypeObj!=="")
       {
               if (valueTypeObj.children_value.length===0)
               {
                                toReturn+="<option selected value=\"-\">-</option>";//by default insert -
                }
               else
               for (var n=0; n < gb_value_units.length; n++)
               {
                       for (var j=0;j<valueTypeObj.children_value.length;j++)
                       {
                               if (valueTypeObj.children_value[j]===gb_value_units[n].value)
                               {
                                       if ((gb_value_units[n].value===selectedValueUnit)||(valueTypeObj.children_value.length==1))
                                               toReturn+="<option selected value=\""+gb_value_units[n].value+"\">"+ gb_value_units[n].label+ " (" +gb_value_units[n].value+")</option>";
                                       else
                                               toReturn+="<option value=\""+gb_value_units[n].value+"\">"+ gb_value_units[n].label+" (" +gb_value_units[n].value+")</option>";
                               }
                       }
               }
       }

       return toReturn;
};


//--------------------------------------------------------------------------------------------------------------------------subnature

var select2option={
                placeholder: 'Select an option',
                width:400,
                allowClear: true,
		closeOnSelect: false
                };

function checkSubnatureChanged(element, old, nuovo, info, edit){
	if ((old!="")&&(!confirm("Are you sure you want to change the subnature? You will lose all the attributes inserted so far!"))) {
		info.preventDefault();
		element.select2("close");
		return;                  //abort!
	}
	element.val(nuovo);
	element.select2(select2option).select2("close");
	subnatureChanged(edit);
}

//called on edit + add
function subnatureChanged(edit, staticAttributes){

	console.log("SUBNATURE CHANGED:"+edit);

	removeStaticAttributes(edit);

	//get new subnature that has been selected
	if (edit){
		subnatureNew=$("#selectSubnatureM").val();
		if (subnatureNew===""){
                	$("#addNewStaticBtnM").hide();
        	}
		else {
			$("#addNewStaticBtnM").show();
		}
	}
	else{
		subnatureNew=$("#selectSubnature").val();
		if (subnatureNew===""){
         	       $("#addNewStaticBtn").hide();
		}
		else{
			$("#addNewStaticBtn").show();
		}
	}

	if (subnatureNew!==""){
		//retrieve new avaialbaility
		$.ajax({
			url: "../api/device.php",
			data: {
				action: 'get_available_static',
				token: sessionToken, 
				subnature: subnatureNew
	      		},
		        type: "POST",
	        	async: true,
		        dataType: 'json',
		        success: function (mydata)
	        	{
				if (mydata["status"] === 'ok')
				{
					//if called in edit, populate static attributes
					if (edit){
						currentDictionaryStaticAttribEdit=JSON.parse(mydata["availibility"]);
						if (staticAttributes)
						{
						        for(let i = 0; i < staticAttributes.length; i++){
						                createRowElem(staticAttributes[i][0], staticAttributes[i][1], currentDictionaryStaticAttribEdit, "editlistStaticAttributes");
							}
						}
				        }
					else {
						currentDictionaryStaticAttribAdd=JSON.parse(mydata["availibility"]);
						if (staticAttributes)
                                                {
                                                        for(let i = 0; i < staticAttributes.length; i++){
                                                                createRowElem(staticAttributes[i][0], staticAttributes[i][1], currentDictionaryStaticAttribAdd, "addlistStaticAttributes");
                                                        }
                                                }	
        	                                $("#addNewStaticBtn").show();
					}
				}
				else
				{
					console.log(JSON.stringify(mydata));
					alert("Unknown error. <br/> Get in touch with the Snap4City Administrator<br/>"+ JSON.stringify(mydata));
					$("#addNewStaticBtn").hide();
				}
		        },
			error: function (mydata)
		        {
				console.log(JSON.stringify(mydata));
                		alert("Network errors. <br/> Get in touch with the Snap4City Administrator<br/>"+ JSON.stringify(mydata));
				$("#addNewStaticBtn").hide();
			}
		});
	}
}

//called on edit + add
function removeStaticAttributes(edit){
	
	if (edit)
		removetab="editStaticTabModel";
	else
		removetab="addStaticTabModel";

	//remove any old value for static attributes
        $('#'+removetab+' div[name="additionalRow"]').each(function(){
                this.remove();
        });
}

//called on edit + add
function createRowElem(initialValueDictiornary, initialValue, currentDictionaryStaticAttrib, element){
	// creation of the components of a row element
        var row = document.createElement("div");
        $(row).attr('class', 'row');
        $(row).attr('name', 'additionalRow');
	$(row).attr('style', 'border:2px solid blue');


	//selection
	var modalCell0 = document.createElement("div");
        $(modalCell0).attr('class', 'col-xs-12 col-md-4 modalCell');

        var modalFieldCnt0 = document.createElement("div");
        $(modalFieldCnt0).attr('class', 'modalFieldCnt');

	console.log("previously already addeed:"+retrieveStaticAttributes(element));
	var alreadyInserted=retrieveStaticAttributes(element, true);

	var atLeastOneEntry=false;
	var atLeastOneSelected=false;
        var modalInputTxt0 = document.createElement("select");
	for (var i = 0; i <currentDictionaryStaticAttrib.length; i++) {
		if ((currentDictionaryStaticAttrib[i].type==="http://www.w3.org/2001/XMLSchema#string")&&(checkNotInsert(alreadyInserted, currentDictionaryStaticAttrib[i].uri))){
			var option = document.createElement("option");
			option.value = currentDictionaryStaticAttrib[i].uri;
			option.text = currentDictionaryStaticAttrib[i].label;
			if (initialValueDictiornary===option.value){
				option.selected=true;
				atLeastOneSelected=true;
			}
			modalInputTxt0.appendChild(option);
			atLeastOneEntry=true;
		}
	}

	if ((initialValueDictiornary!="")&&(!atLeastOneSelected))
		atLeastOneEntry=false;

	//enter text
        var modalCell1 = document.createElement("div");
        $(modalCell1).attr('class', 'col-xs-12 col-md-4 modalCell');

        var modalFieldCnt1 = document.createElement("div");
        $(modalFieldCnt1).attr('class', 'modalFieldCnt');

        var modalInputTxt = document.createElement("input");
        $(modalInputTxt).attr('type', 'text');
        $(modalInputTxt).attr('class', 'modalInputTxt');
        $(modalInputTxt).attr('onkeyup', 'checkStrangeCharacters(this)');
        $(modalInputTxt).val(initialValue);

        var modalFieldLabelCnt = document.createElement("div");
        $(modalFieldLabelCnt).attr('class', 'modalFieldLabelCnt');
        $(modalFieldLabelCnt).text("Value");

	//remove button
        var modalCell2 = document.createElement("div");
        $(modalCell2).attr('class', 'col-xs-12 col-md-4 modalCell');

        var modalFieldCnt2 = document.createElement("div");
        $(modalFieldCnt2).attr('class', 'modalFieldCnt');

        var rmButton = document.createElement("button");
        $(rmButton).attr('type', 'text');
        $(rmButton).attr('name', 'removeCBServiceBtn');
        $(rmButton).attr('class', 'btn btn-danger');
        $(rmButton).text("Remove");

        rmButton.addEventListener('click', function(){row.remove()});

        // row element composition
	$(row).append(modalCell0);	
	$(modalCell0).append(modalFieldCnt0);
	$(modalFieldCnt0).append(modalInputTxt0);
        $(row).append(modalCell1);
        $(modalCell1).append(modalFieldCnt1);
        $(modalFieldCnt1).append(modalInputTxt);
        $(modalCell1).append(modalFieldLabelCnt);
        $(row).append(modalCell2)
        $(modalCell2).append(modalFieldCnt2);
        $(modalFieldCnt2).append(rmButton);

	// get static tab
        if (atLeastOneEntry){
		var stTab = $("#"+element).last();
		stTab.append(row);
		//stTab.parent().parent().scrollTop(100000);
	}
}

//called on edit + add
function retrieveStaticAttributes(source, all){
	var staticArr = $('#'+source+' div[name="additionalRow"]').find("select");
	var staticArr2=  $('#'+source+' div[name="additionalRow"]').find("input");
	var staticValues = [];
	for(let i = 0; i < staticArr.length; i++){
		if ((staticArr2[i].value)||(all!== undefined)){
			var array = [];
			array.push(staticArr[i].value);
			array.push(staticArr2[i].value);
			staticValues.push(array);
		}
	}
	return staticValues;
}

function checkNotInsert(inserted, check){
	for(let i = 0; i < inserted.length; i++){
		console.log("chcek against"+(inserted[0]));
		if (inserted[i][0]===check)
			return false;
	}
	return true;
}

function addSubnature(element, data){
        $.each(data, function(){
                element.append("<option value='"+this.value+"'>"+this.label+"&#160;&#160;&#160;<font size=\"2\">("+this.parent_value[0]+")</font>"+"</option>");
        });
	element.select2(select2option);
}

function verifySubnature(subnature, static_attributes){

		var tocheck=JSON.parse(static_attributes);
		var toreturn=true;

		//retrieve avaialbaility
                $.ajax({
                        url: "../api/device.php",
                        data: {
                                action: 'get_available_static',
                                token: sessionToken,
                                subnature: subnature
                        },
                        type: "POST",
                        async: false,
                        dataType: 'json',
                        success: function (mydata)
                        {
                                if (mydata["status"] === 'ok')
                                {
                                        currentDictionary=JSON.parse(mydata["availibility"]);
					for (let  i=0;i<tocheck.length;i++){
						var found=false;
						for(let j=0; j<currentDictionary.length;j++){
							if (tocheck[i][0]==currentDictionary[j].uri){
								found=true;
								break;
							}
						}
						if (!found){
							toreturn=false;
							return;
						}
					}
                                }
                                else
                                {
                                        console.log(JSON.stringify(mydata));
                                        alert("Unknown error. <br/> Get in touch with the Snap4City Administrator<br/>"+ JSON.stringify(mydata));
                                        $("#addNewStaticBtn").hide();
                                }
                        },
                        error: function (mydata)
                        {
                                console.log(JSON.stringify(mydata));
                                alert("Network errors. <br/> Get in touch with the Snap4City Administrator<br/>"+ JSON.stringify(mydata));
                                $("#addNewStaticBtn").hide();
                        }
                });

	return toreturn;

}

//-------------------------------------------------------------------------------------------------------------------CB
function addCB(element, data){
        $.each(data['content'], function() {
                element.append("<option my_data= "+this.protocol+" data_kind="+this.kind+" value='"+this.name+"'>"+this.name+"</option>");
        });
}


//-----------------------------------------------------------------------------------------------------------------------
function copyClipboard(elementName) {
        //copy text from elementName
        var copyText = document.getElementById(elementName);
        var value= "<input value=\""+copyText.value+"\" id=\"selVal\" />";
        $(value).insertAfter(copyText);
        $("#selVal").select();
        document.execCommand("copy");
        $('body').find("#selVal").remove();

        //give feedback for a second
        var feedback="<div class=\"modalFieldMsgCnt\" id=\"feedback\">Copied</div>"
        oldText=copyText.parentElement.nextElementSibling.nextElementSibling.textContent;
        oldColor=copyText.parentElement.nextElementSibling.nextElementSibling.style.color;
        setTimeout(function(){
                copyText.parentElement.nextElementSibling.nextElementSibling.textContent=oldText;
                copyText.parentElement.nextElementSibling.nextElementSibling.style.color=oldColor;
        },1000);
        copyText.parentElement.nextElementSibling.nextElementSibling.textContent="Copied";
        copyText.parentElement.nextElementSibling.nextElementSibling.style.color="black";
}

function CSVToArray2( text ){
let ret = [''], i = 0, p = '', s = true;
    for (let l in text) {
        l = text[l];
        if ('"' === l) {
            s = !s;
            if ('"' === p) {
                ret[i] += '"';
                l = '-';
            } else if ('' === p)
                l = '-';
        } else if (s && ',' === l)
            l = ret[++i] = '';
        else
            ret[i] += l;
        p = l;
    }
    return ret;
}
