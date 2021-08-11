var rowCount=1;
var mutationReadOnly=false;

var account_options=[];
var disableAlert=false;

//------------------------------------------------------------------------
//these functions allow adding options to HTML select elements dynamically
//------------------------------------------------------------------------

function addOptionsPHP(all_options){
	for (o=0;o<all_options.length;o++){
		var select = document.getElementById(all_options[o][0]);	
		addOptions(select,all_options[o][1],"def");
	}
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

function setGlobalOptions(accounts){
	account_options=accounts;
}


//-----------------------------------------------------
//these functions create new input rows and remove them
//-----------------------------------------------------


function addOnClick(){
	var but = document.getElementById("addMutationRowButton");

	//note eventlistener wants a function, addMutationRow() actually gives a return value
	but.addEventListener("click",function(){
		addMutationRow();
	});
}

function addMutationRow(sel_options="") {

	var mutationTable = document.getElementById("mutationTable");
	var newMutationRow = document.createElement("tr");
	newMutationRow.setAttribute("id", "mutationRow"+rowCount.toString());
	newMutationRow.setAttribute("class", "mutationInputRow");
	mutationTable.appendChild(newMutationRow);

	//Create the rows
	var newMutationColA = document.createElement("td");
	newMutationColA.setAttribute("class", "mutationInputCol");
	newMutationRow.appendChild(newMutationColA);

	var newMutationColB = document.createElement("td");
	newMutationColB.setAttribute("class", "mutationInputCol");
	newMutationRow.appendChild(newMutationColB);

	var newMutationColC = document.createElement("td");
	newMutationColC.setAttribute("class", "mutationInputCol");
	newMutationRow.appendChild(newMutationColC);

	// If the function is called with pre-selected options
	// (in case of loading a booking, these are extracted here 

	if (sel_options.length>0){

		//from database
		sel_account_from=sel_options[0];
		sel_amount=sel_options[2];
	}
	else{

		//defaults
		sel_account_from="def";
		sel_amount=0;
	}

	//input fields

	//mutation Account From & hidden select field
	var newMutationAccountFrom = document.createElement("select");
	newMutationAccountFrom.setAttribute("id", "mutationAccountFrom"+rowCount.toString());
	newMutationAccountFrom.setAttribute("name", "mutationAccountFrom"+rowCount.toString());
        addOptions(newMutationAccountFrom,account_options,sel_account_from);
	newMutationColA.appendChild(newMutationAccountFrom);

	//this hidden field is used to keep the previous value 
	//of the select field in case a row is deleted

	var newMutationAccountFromHidden = document.createElement("select");
	newMutationAccountFromHidden.setAttribute("id", "mutationAccountFromHidden"+rowCount.toString());
	newMutationAccountFromHidden.setAttribute("name", "mutationAccountFromHidden"+rowCount.toString());
	newMutationAccountFromHidden.setAttribute("hidden","true");
        addOptions(newMutationAccountFromHidden,account_options,sel_account_from);
	newMutationColA.appendChild(newMutationAccountFromHidden);

	newMutationAccountFrom.addEventListener("change",function(){
		readOnlySelect(newMutationAccountFrom.id,newMutationAccountFromHidden.id);
	});

	//mutation Amount
	var newMutationAmount = document.createElement("input");
	newMutationAmount.setAttribute("id", "mutationAmount"+rowCount.toString());
	newMutationAmount.setAttribute("name", "mutationAmount"+rowCount.toString());
	newMutationAmount.setAttribute("type", "number");
	newMutationAmount.setAttribute("step", "0.01");
	newMutationAmount.setAttribute("value", sel_amount);
	newMutationAmount.setAttribute("class", "mutationInputField");
	newMutationColB.appendChild(newMutationAmount);

	//Remove row button
	var newMutationRem = document.createElement("input");
        newMutationRem.setAttribute("id", "mutationBut"+rowCount.toString());
	newMutationRem.setAttribute("name", "mutationBut"+rowCount.toString());
	newMutationRem.setAttribute("type", "button");
	newMutationRem.setAttribute("value", "-");
	newMutationRem.setAttribute("onclick", "removeMutationRow(this.id)");
	newMutationColC.appendChild(newMutationRem);

	//Read only settings
	if(mutationReadOnly){
		newMutationAccountFrom.setAttribute("disabled","disabled");
		newMutationAmount.setAttribute("readonly","readonly");
	}

	//increment rowCount
	rowCount+=1;

	//adjust total values --> needed when entering data from database
	adjustMutationTot();
}

function removeMutationRow(butval){
	var row = parseInt(butval.replace("mutationBut",""));
        var rowID = "mutationRow"+row.toString();
	var rmRow = document.getElementById(rowID);
	var inputs=["mutationAccountFrom","mutationAmount"];
	
	//get all values up to rowcount and move downward
	for(r=row;r<(rowCount-1);r++){
		for (i=0;i<inputs.length;i++){
			old_value=document.getElementById(inputs[i]+(r+1).toString()).value;
			document.getElementById(inputs[i]+r.toString()).value=old_value;
		}
	}

        //remove the last row and all its children
        var lastRow = "mutationRow"+(rowCount-1).toString();
	var lastRowEl = document.getElementById(lastRow);
	
	lastRowEl.remove();
	rowCount=rowCount-1;

	//adjust values
	adjustMutationTot();
}

//------------------------------------------------------------------------------------------------------
//functions that fill (read-only) or hidden field inputs when a change to the form fieldsets are applied
//------------------------------------------------------------------------------------------------------


function onChangeFieldSet(ids){

	for(var i=0;i<ids.length;i++){
		var id=ids[i];

		var form = document.getElementById(id);
	
		if(id=="mutationFieldSet"){
			//note eventlistener wants a function, addSalesRow() actually gives a return value
			form.addEventListener("change",function(){
				adjustMutationTot();
				saveGuard();
			
			});
		}

		else if(id=="metaFieldSet"){
			form.addEventListener("change",function(){
				saveGuard();
			});
		}

	} //for loop

}

function adjustMutationTot(){

	//get total fields
	var mutationTot=document.getElementById("mutationTot");
        var sumAmount=0;

	//sum the mutation rows
	for (i=1;i<rowCount;i++){
		if (document.getElementById('mutationAmount'+i.toString())){
			sumAmount+=+document.getElementById('mutationAmount'+i.toString()).value;
		}
	}

	//set totals
        mutationTot.value=sumAmount;
}

function readOnlySelect(id,id_hidden){
	var select = document.getElementById(id);
	var select_hidden = document.getElementById(id_hidden);

	//Load previous value in a data field
	select.setAttribute("data",select_hidden.value);
	select_hidden.value=select.value;
}

function switchPeriodPresets(yearID,periodID,fromID,toID){
	
	var year = document.getElementById(yearID);
	var period = document.getElementById(periodID);

	var from = document.getElementById(fromID);
	var to = document.getElementById(toID);		//up to value
	var from_label = document.getElementById(fromID+"Label");
	var to_label = document.getElementById(toID+"Label");	

	if(period.value!="Else"){

		if(period.value == "Y"){
			from.value=year.value+"-01-01";
			to.value=(parseInt(year.value)+1).toString()+"-01-01"; 	
		}
		else if(period.value == "Q1"){ 			//1st quarter
			from.value=year.value+"-01-01";
			to.value=year.value+"-04-01";
		}
		else if(period.value == "Q2"){ 			//2nd quarter
			from.value=year.value+"-04-01";
			to.value=year.value+"-07-01";
		}
		else if(period.value == "Q3"){ 			//3rd quarter
			from.value=year.value+"-07-01";
			to.value=year.value+"-10-01";
		}
		else if(period.value == "Q4"){ 			//4th quarter
			from.value=year.value+"-10-01";
			to.value=(parseInt(year.value)+1).toString()+"-01-01"; 	
		}

		else{ 						//period is a month
			if(period.value=="12"){
				console.log("period is december");
				from.value=year.value+"-12-01";
				to.value=(parseInt(year.value)+1).toString()+"-01-01"; 	
			}
			else if(parseInt(period.value)<10){
				from.value=year.value+"-0"+period.value+"-01";
				to.value=year.value+"-0"+(parseInt(period.value)+1).toString()+"-01";
			}
			else{
				from.value=year.value+"-"+period.value+"-01";
				to.value=year.value+"-"+(parseInt(period.value)+1).toString()+"-01";
			}
		}

		//show from an to fields
		from_label.setAttribute("hidden","true");
		to_label.setAttribute("hidden","true");	
	}
	else{							//user selection

		//show from and to fields
		from_label.removeAttribute("hidden");
		to_label.removeAttribute("hidden");

		//set values
		//from.value= year.value+"-01-01";
		//to.value= (parseInt(year.value)+1).toString()+"-01-01"; 

	}

}



//--------------------------------------------------------------------------
//Functions that check & limit the user input 
//--------------------------------------------------------------------------

function saveGuard(){
//this function prevents the user from saving if checks are not satisfied
	
	//get elements
	var invoice=document.getElementById("location").value;
	var update_span=document.getElementById("updateSpan");
	var update_but=document.getElementById("updateButton");
	var send=false;

	//check if there is an invoice location set	
	if(!invoice){
		update_but.setAttribute('disabled','disabled');
		update_span.setAttribute("title","Please provide an invoice location");
		
	}
	else{
		//other checks
		if(maxCheck()){
			update_but.removeAttribute('disabled');
			update_span.setAttribute("title","Save the purchase");
		}
		else{
			update_but.setAttribute('disabled','disabled');
			update_span.setAttribute("title","Sum of entries not zero");
		}

	}
	
}


function maxCheck(){
	var mutationTot=document.getElementById("mutationTot");
	if (mutationTot.value==0){
		return true;
	}
	else{
		return false;
	}
	
}

//----------------------------------------------
//Functions that allow a document to be uploaded
//----------------------------------------------

function upload(name, input) {
	var xhr = new XMLHttpRequest();
	xhr.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			filename = this.responseText;
			document.getElementById('location').value = filename;
			type  = filename.split('.').pop();
			if (type=='pdf') document.getElementById('invoiceView').innerHTML = '<embed src="files/' + filename + '" width="400px" height="600px" />';
			if (type=='jpg') document.getElementById('invoiceView').innerHTML = '<img src="files/' + filename + '" width="400px" />';
		}
	};
	var data = new FormData();
	data.append(name, input.files[0]);
	xhr.open('POST', '');
	xhr.send(data);
}


