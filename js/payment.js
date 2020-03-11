rowCount=1;

function addOptionsPHP(selectID,options, sel_opt="def"){
	var select = document.getElementById(selectID);
	addOptions(select,options, sel_opt);
}

function addOptions(select_obj,options,sel_opt){
        for (i=0;i<options.length;i++){
                newOption = document.createElement("option");
                newOption.setAttribute("value",options[i][0]);
                newOption.innerHTML=options[i][1];
                if (options[i][0]=="def"){
                        newOption.setAttribute("disabled","disabled")
                };
		if (options[i][0]==sel_opt){
			newOption.setAttribute("selected","");
		};
                select_obj.appendChild(newOption);
        };
}

function on_prov_change(flds){
	var prov_select=document.getElementById("ProvID");;
	prov_call="enable_select('"+flds+"','ProvID','new')"
	prov_select.setAttribute("onchange",prov_call);
}

//general function to enable an input field (fld) when option (opt) is selected in select element sel
function enable_select(flds_str, selID, opt){
	flds=flds_str.split(",");
	var select=document.getElementById(selID);
	if (select.value==opt){
		for (i=0;i<flds.length;i++){
			var fld=document.getElementById(flds[i]);
			fld.disabled=false;
		}
	}
	else{
		for (i=0;i<flds.length;i++){
			var fld=document.getElementById(flds[i]);
			fld.disabled=true;
		}
	}
}

