var rowCount=1;
var expenseReadOnly=false;

var expense_options=[];
var vat_options=[];

var disableAlert=false;

//-------------------------------------------------------------------
//these functions allow adding options to select elements dynamically
//-------------------------------------------------------------------

function addOptionsPHP_onclick(selectID,options,reductionID){
	
	var select = document.getElementById(selectID);
	var reduct = document.getElementById(reductionID);
	
	//Eventlistener
	reduct.addEventListener("change",function(){

		//delete all options, if any		
		if (select.options){
			var len = select.options.length;
			for (i = len-1; i >= 0; i--) {
  				select.options[i] = null;
			}
		}

		//add options for this contact	
		var reduct_val = reduct.value;	
		var red_options=[];

		for (i=0;i<options.length;i++){ 
			//add the default option
			if (options[i][0]=="def"){
				red_options.push(options[i]);
				console.log("default option");
			}
			//add options for contact only
			if (options[i][2]==reduct_val){
				red_options.push(options[i]);
				console.log("contact option");
			}
		}
		addOptions(select,red_options);
		});

}

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

function setGlobalOptions(expense,vat){
	expense_options=expense;
	vat_options=vat;
}


//-----------------------------------------------------
//these functions create new input rows and remove them
//-----------------------------------------------------


function addOnClick(){
	var but = document.getElementById("addExpenseRowButton");

	//note eventlistener wants a function, addExpenseRow() actually gives a return value
	but.addEventListener("click",function(){
		addExpenseRow(expense_options,vat_options);
	});
}

function addExpenseRow(sel_options="") {

	var expenseTable = document.getElementById("expenseTable");
	var newExpenseRow = document.createElement("tr");
	newExpenseRow.setAttribute("id", "expenseRow"+rowCount.toString());
	newExpenseRow.setAttribute("class", "expenseInputRow");
	expenseTable.appendChild(newExpenseRow);

	//Create the rows
	var newExpenseColA = document.createElement("td");
	newExpenseColA.setAttribute("class", "expenseInputCol");
	newExpenseRow.appendChild(newExpenseColA);

	var newExpenseColB = document.createElement("td");
	newExpenseColB.setAttribute("class", "expenseInputCol");
	newExpenseRow.appendChild(newExpenseColB);

	var newExpenseColC = document.createElement("td");
	newExpenseColC.setAttribute("class", "expenseInputCol");
	newExpenseRow.appendChild(newExpenseColC);

	var newExpenseColD = document.createElement("td");
	newExpenseColD.setAttribute("class", "expenseInputCol");
	newExpenseRow.appendChild(newExpenseColD);

	var newExpenseColE = document.createElement("td");
	newExpenseColE.setAttribute("class", "expenseInputCol");
	newExpenseRow.appendChild(newExpenseColE);

        var newExpenseColF = document.createElement("td");
        newExpenseColF.setAttribute("class", "expenseInputCol");
        newExpenseRow.appendChild(newExpenseColF);

        var newExpenseColG = document.createElement("td");
        newExpenseColG.setAttribute("class", "expenseInputColLast");
        newExpenseRow.appendChild(newExpenseColG);

	// Extract selected options
	if (sel_options.length>0){
		//from database
		sel_expense=sel_options[0];
		sel_nett=sel_options[1];
		sel_vat_type=sel_options[2];
		sel_vat=sel_options[3];
		sel_gross=sel_options[4];
	}
	else{
		//defaults
		sel_expense="def";
		sel_nett=0;
		sel_vat_type="21";
		sel_vat=0;
		sel_gross=0;
	}

	//input fields

	//expense Type & hidden select field
	var newExpenseType = document.createElement("select");
	newExpenseType.setAttribute("id", "expenseType"+rowCount.toString());
	newExpenseType.setAttribute("name", "expenseType"+rowCount.toString());
        addOptions(newExpenseType,expense_options,sel_expense);
	newExpenseColA.appendChild(newExpenseType);

	var newExpenseTypeHidden = document.createElement("select");
	newExpenseTypeHidden.setAttribute("id", "expenseTypeHidden"+rowCount.toString());
	newExpenseTypeHidden.setAttribute("name", "expenseTypeHidden"+rowCount.toString());
	newExpenseTypeHidden.setAttribute("hidden","true");
        addOptions(newExpenseTypeHidden,expense_options,sel_expense);
	newExpenseColA.appendChild(newExpenseTypeHidden);

	newExpenseType.addEventListener("change",function(){
		readOnlySelect(newExpenseType.id,newExpenseTypeHidden.id);
	});

	//Gross
	var newExpenseGross = document.createElement("input");
	newExpenseGross.setAttribute("id", "expenseGross"+rowCount.toString());
	newExpenseGross.setAttribute("name", "expenseGross"+rowCount.toString());
	newExpenseGross.setAttribute("type", "number");
	newExpenseGross.setAttribute("step", "0.01");
	newExpenseGross.setAttribute("value", sel_gross);
	newExpenseGross.setAttribute("class", "expenseInputField");
	newExpenseColB.appendChild(newExpenseGross);

	//Nett
	var newExpenseNett = document.createElement("input");
	newExpenseNett.setAttribute("id", "expenseNett"+rowCount.toString());
	newExpenseNett.setAttribute("name", "expenseNett"+rowCount.toString());
	newExpenseNett.setAttribute("type", "number");
	newExpenseNett.setAttribute("step", "0.01");
	newExpenseNett.setAttribute("value", sel_nett);
	newExpenseNett.setAttribute("class", "expenseInputField");
	newExpenseColC.appendChild(newExpenseNett);

	//VAT
	var newExpenseVat = document.createElement("input");
	newExpenseVat.setAttribute("id", "expenseVat"+rowCount.toString());
	newExpenseVat.setAttribute("name", "expenseVat"+rowCount.toString());
	newExpenseVat.setAttribute("type", "number");
	newExpenseVat.setAttribute("step", "0.01");
	newExpenseVat.setAttribute("value", sel_vat);
	newExpenseVat.setAttribute("class", "expenseInputField");
	newExpenseColD.appendChild(newExpenseVat);

	//VAT type
        var newVatType = document.createElement("select");
        newVatType.setAttribute("id", "expenseVatType"+rowCount.toString());
        newVatType.setAttribute("name", "expenseVatType"+rowCount.toString());
        addOptions(newVatType,vat_options,sel_vat_type);
        newExpenseColE.appendChild(newVatType);

        var newVatTypeHidden = document.createElement("select");
        newVatTypeHidden.setAttribute("id", "expenseVatTypeHidden"+rowCount.toString());
        newVatTypeHidden.setAttribute("name", "expenseVatTypeHidden"+rowCount.toString());
	newVatTypeHidden.setAttribute("hidden","true");
        addOptions(newVatTypeHidden,vat_options,sel_vat_type);
        newExpenseColE.appendChild(newVatTypeHidden);

	newVatType.addEventListener("change",function(){
		readOnlySelect(newVatType.id,newVatTypeHidden.id);
	});

	//Remove row button
	var newExpenseRem = document.createElement("input");
        newExpenseRem.setAttribute("id", "expenseBut"+rowCount.toString());
	newExpenseRem.setAttribute("name", "expenseBut"+rowCount.toString());
	newExpenseRem.setAttribute("type", "button");
	newExpenseRem.setAttribute("value", "-");
	newExpenseRem.setAttribute("onclick", "removeExpenseRow(this.id)");
	newExpenseColG.appendChild(newExpenseRem);

	//Read only settings
	if(expenseReadOnly){
		newExpenseType.setAttribute("disabled","disabled");
		newExpenseNett.setAttribute("readonly","readonly");
		newVatType.setAttribute("disabled","disabled");
	}

	//increment rowCount
	rowCount+=1;

	//adjust total values --> needed when entering data from database
	adjustExpenseTot();
}

function removeExpenseRow(butval){
	var row = parseInt(butval.replace("expenseBut",""));
        var rowID = "expenseRow"+row.toString();
	var rmRow = document.getElementById(rowID);
	var inputs=["expenseType","expenseTypeHidden","expenseNett","expenseVatType","expenseVatTypeHidden","expenseVat","expenseGross"];
	
	//get all values up to rowcount and move downward
	for(r=row;r<(rowCount-1);r++){
		for (i=0;i<inputs.length;i++){
			old_value=document.getElementById(inputs[i]+(r+1).toString()).value;
			document.getElementById(inputs[i]+r.toString()).value=old_value;
		}
	}

        //remove the last row and all its children
        var lastRow = "expenseRow"+(rowCount-1).toString();
	var lastRowEl = document.getElementById(lastRow);
	
	lastRowEl.remove();
	rowCount=rowCount-1;

	//adjust values
	adjustExpenseTot();
}

//------------------------------------------------------------------------------------------------------
//functions that fill (read-only) or hidden field inputs when a change to the form fieldsets are applied
//------------------------------------------------------------------------------------------------------


function onChangeFieldSet(id){
	var form = document.getElementById(id);
	
	if(id=="expenseFieldSet"){
		//note eventlistener wants a function, addSalesRow() actually gives a return value
		form.addEventListener("change",function(){
			for (i=1;i<rowCount;i++){
				adjustExpenseRow(i); 
			}
			adjustExpenseTot();
			saveGuard();
			
		});
	}

	else if(id=="metaFieldSet"){
		form.addEventListener("change",function(){
			saveGuard();
		});
	}

	else if(id=="contactFieldSet"){
		form.addEventListener("change",function(){
			saveGuard();
		});
	}

}


function adjustExpenseRow(){
	//TODO: choose to do this or not
}

function adjustExpenseTot(){

	//get total fields
	var inputTot=document.getElementById("nettTot");
	var vatTot=document.getElementById("vatTot");
	var grossTot=document.getElementById("grossTot");
	var shiftTot=document.getElementById("vatShift");

	console.log("Shift:",shiftTot.value);

        var sumnett=0;
        var sumvat=0;
	var sumgross=0;

	//sum the expense rows
	for (i=1;i<rowCount;i++){
		if (document.getElementById('expenseNett'+i.toString())){
			sumnett+=+document.getElementById('expenseNett'+i.toString()).value;
			sumvat+=+document.getElementById('expenseVat'+i.toString()).value;
			sumgross+=+document.getElementById('expenseGross'+i.toString()).value;
		}
	}

	//set totals
        inputTot.value=sumnett;
	vatTot.value=sumvat;	
	grossTot.value=sumgross
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

		//show from an to fields
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
//this function prevents the user from saving when there is no entry in the location field
	
	//get elements
	var invoice=document.getElementById("location").value;
	var span=document.getElementById("update_span");
	var save_but=document.getElementById("update");

	//check if there is an invoice location set	
	if(!invoice){
		save_but.setAttribute('disabled','disabled');
		span.setAttribute("title","Please provide an invoice location or generate an invoice first");	
	}
}

//----------------------------------------------
//Functions that allow an invoice to be uploaded
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
