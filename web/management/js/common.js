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

