var rowCount=1;
var salesReadOnly=false;
var inRowCount=1;

var sales_options=[];
var vat_options=[];
var invoice_options=[];

var change_time;
var make_time;

var disableAlert=false;

//-------------------------------------------------------------------
//these functions allow adding options to select elements dynamically
//-------------------------------------------------------------------

function addOptionsPHP(selectID,options){
	var select = document.getElementById(selectID);
	addOptions(select,options);
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

function setGlobalOptions(invoice,sales,vat){
	invoice_options=invoice;
	sales_options=sales;
	vat_options=vat;
}

//-----------------------------------------------------
//these functions create new input rows and remove them
//-----------------------------------------------------

function addOnClick(){
	
	//invoice 
	var but = document.getElementById("addInvoiceRowButton");

	//note eventlistener wants a function, addSalesRow() actually gives a return value
	but.addEventListener("click",function(){
		addInvoiceRow();
	});

	//Sales
	var but = document.getElementById("addSalesRowButton");

	//note eventlistener wants a function, addSalesRow() actually gives a return value
	but.addEventListener("click",function(){
		addSalesRow();
	});

}

function addInvoiceRow(sel_options=""){

	var invoiceTable = document.getElementById("invoiceTable");
	var newInvoiceRow = document.createElement("tr");
	newInvoiceRow.setAttribute("id", "invoiceRow"+inRowCount.toString());
	newInvoiceRow.setAttribute("class", "invoiceInputRow");
	invoiceTable.appendChild(newInvoiceRow);

	//Create the rows
	var newInvoiceColA = document.createElement("td");
	newInvoiceColA.setAttribute("class", "invoiceInputCol");
	newInvoiceRow.appendChild(newInvoiceColA);

	var newInvoiceColB = document.createElement("td");
	newInvoiceColB.setAttribute("class", "invoiceInputCol");
	newInvoiceRow.appendChild(newInvoiceColB);

	var newInvoiceColC = document.createElement("td");
	newInvoiceColC.setAttribute("class", "invoiceInputCol");
	newInvoiceRow.appendChild(newInvoiceColC);

	var newInvoiceColD = document.createElement("td");
	newInvoiceColD.setAttribute("class", "invoiceInputCol");
	newInvoiceRow.appendChild(newInvoiceColD);

	var newInvoiceColE = document.createElement("td");
	newInvoiceColE.setAttribute("class", "invoiceInputCol");
	newInvoiceRow.appendChild(newInvoiceColE);

        var newInvoiceColF = document.createElement("td");
        newInvoiceColE.setAttribute("class", "invoiceInputCol");
        newInvoiceRow.appendChild(newInvoiceColF);

        var newInvoiceColG = document.createElement("td");
        newInvoiceColE.setAttribute("class", "invoiceInputColLast");
        newInvoiceRow.appendChild(newInvoiceColG);

	// Extract selected options
	if (sel_options.length>0){
		//from database
		sel_invoice=sel_options[0];
		sel_desc=sel_options[1];
		sel_amount=sel_options[2];
		sel_price=sel_options[3];
		sel_nett=sel_options[4];
		sel_vat_type=sel_options[5];
	}
	else{
		//defaults
		sel_invoice="def";
		sel_desc="";
		sel_amount=0;
		sel_price=0;
		sel_nett=0;
		sel_vat_type="21";
	}

	//input fields

	//Invoice Type & Hidden select field
	var newInvoiceType = document.createElement("select");
	newInvoiceType.setAttribute("id", "invoiceType"+inRowCount.toString());
	newInvoiceType.setAttribute("name", "invoiceType"+inRowCount.toString());
        addOptions(newInvoiceType,invoice_options,sel_invoice);
	newInvoiceColA.appendChild(newInvoiceType);

	var newInvoiceTypeHidden = document.createElement("select");
	newInvoiceTypeHidden.setAttribute("id", "invoiceTypeHidden"+inRowCount.toString());
	newInvoiceTypeHidden.setAttribute("name", "invoiceTypeHidden"+inRowCount.toString());
	newInvoiceTypeHidden.setAttribute("hidden","true");
        addOptions(newInvoiceTypeHidden,invoice_options,sel_invoice);
	newInvoiceColA.appendChild(newInvoiceTypeHidden);
	
	newInvoiceType.addEventListener("change",function(){
		readOnlySelect(newInvoiceType.id,newInvoiceTypeHidden.id);
	});

	//Description
	var newInvoiceGross = document.createElement("input");
	newInvoiceGross.setAttribute("id", "invoiceDesc"+inRowCount.toString());
	newInvoiceGross.setAttribute("name", "invoiceDesc"+inRowCount.toString());
	newInvoiceGross.setAttribute("type", "text");
	newInvoiceGross.setAttribute("value", sel_desc);
	newInvoiceColB.appendChild(newInvoiceGross);

	//Amount
	var newInvoiceAmount = document.createElement("input");
	newInvoiceAmount.setAttribute("id", "invoiceAmount"+inRowCount.toString());
        newInvoiceAmount.setAttribute("name", "invoiceAmount"+inRowCount.toString());
	newInvoiceAmount.setAttribute("type", "number");
	newInvoiceAmount.setAttribute("step", "0.1");
	newInvoiceAmount.setAttribute("value", sel_amount);
	newInvoiceColC.appendChild(newInvoiceAmount);

	//Price
	var newInvoicePrice = document.createElement("input");
	newInvoicePrice.setAttribute("id", "invoicePrice"+inRowCount.toString());
	newInvoicePrice.setAttribute("name", "invoicePrice"+inRowCount.toString());
	newInvoicePrice.setAttribute("type", "number");
	newInvoicePrice.setAttribute("step", "0.01");
	newInvoicePrice.setAttribute("value", sel_price);
	newInvoiceColD.appendChild(newInvoicePrice);

	//Nett
	var newInvoiceNett = document.createElement("input");
	newInvoiceNett.setAttribute("id", "invoiceNett"+inRowCount.toString());
	newInvoiceNett.setAttribute("name", "invoiceNett"+inRowCount.toString());
	newInvoiceNett.setAttribute("type", "number");
	newInvoiceNett.setAttribute("step", "0.01");
	newInvoiceNett.setAttribute("value", sel_nett);
	newInvoiceColE.appendChild(newInvoiceNett);

	//VAT type & hidden select item
        var newVatType = document.createElement("select");
        newVatType.setAttribute("id", "invoiceVatType"+inRowCount.toString());
        newVatType.setAttribute("name", "invoiceVatType"+inRowCount.toString());
        addOptions(newVatType,vat_options,sel_vat_type);
        newInvoiceColF.appendChild(newVatType);

        var newVatTypeHidden = document.createElement("select");
        newVatTypeHidden.setAttribute("id", "invoiceVatTypeHidden"+inRowCount.toString());
        newVatTypeHidden.setAttribute("name", "invoiceVatTypeHidden"+inRowCount.toString());
	newVatTypeHidden.setAttribute("hidden","true");
        addOptions(newVatTypeHidden,vat_options,sel_vat_type);
        newInvoiceColF.appendChild(newVatTypeHidden);

	newVatType.addEventListener("change",function(){
		readOnlySelect(newVatType.id,newVatTypeHidden.id);
	});

	//Row remove button
	var newInvoiceRem = document.createElement("input");
        newInvoiceRem.setAttribute("id", "invoiceBut"+inRowCount.toString());
	newInvoiceRem.setAttribute("name", "invoiceBut"+inRowCount.toString());
	newInvoiceRem.setAttribute("type", "button");
	newInvoiceRem.setAttribute("value", "-");
	newInvoiceRem.setAttribute("onclick", "removeInvoiceRow(this.id)");
	newInvoiceColG.appendChild(newInvoiceRem);

	//if header, hide the fields
	if(sel_invoice=="head"){
		invoiceHeaderRow(inRowCount,"hide");
	}

	//increment inRowCount
	inRowCount+=1;

	//adjust total values
	invoiceToSales();
}

function removeInvoiceRow(butval){
	var row = parseInt(butval.replace("invoiceBut",""));
        var rowID = "invoiceRow"+row.toString();
	var rmRow = document.getElementById(rowID);
	var inputs=["invoiceType","invoiceTypeHidden","invoiceDesc","invoiceAmount","invoicePrice","invoiceNett","invoiceVatType","invoiceVatTypeHidden"];
	
	//get all values up to rowcount and move downward
	for(r=row;r<(inRowCount-1);r++){	
		for (i=0;i<inputs.length;i++){
			old_value=document.getElementById(inputs[i]+(r+1).toString()).value;
			prev_value=document.getElementById(inputs[i]+r.toString()).value
			new_value=document.getElementById(inputs[i]+r.toString()).value=old_value;

			//reveal fields in case of a header as previous value
			if(inputs[i]=="invoiceType" && prev_value==0){
				invoiceHeaderRow((r),"reveal");
			}

			//hide fields in case of a header as new value
			if(inputs[i]=="invoiceType" && new_value==0){
				invoiceHeaderRow(r,"hide");
			}

		}
	}

        //remove the last row and all its children
        var lastRow = "invoiceRow"+(inRowCount-1).toString();
	var lastRowEl = document.getElementById(lastRow);
	
	lastRowEl.remove();
	inRowCount=inRowCount-1;

	//adjust values
        invoiceToSales();
}


function addSalesRow(sel_options="") {

	var salesTable = document.getElementById("salesTable");
	var newSalesRow = document.createElement("tr");
	newSalesRow.setAttribute("id", "salesRow"+rowCount.toString());
	newSalesRow.setAttribute("class", "salesInputRow");
	salesTable.appendChild(newSalesRow);

	//Create the rows
	var newSalesColA = document.createElement("td");
	newSalesColA.setAttribute("class", "salesInputCol");
	newSalesRow.appendChild(newSalesColA);

	var newSalesColB = document.createElement("td");
	newSalesColB.setAttribute("class", "salesInputCol");
	newSalesRow.appendChild(newSalesColB);

	var newSalesColC = document.createElement("td");
	newSalesColC.setAttribute("class", "salesInputCol");
	newSalesRow.appendChild(newSalesColC);

	var newSalesColD = document.createElement("td");
	newSalesColD.setAttribute("class", "salesInputCol");
	newSalesRow.appendChild(newSalesColD);

	var newSalesColE = document.createElement("td");
	newSalesColE.setAttribute("class", "salesInputCol");
	newSalesRow.appendChild(newSalesColE);

        var newSalesColF = document.createElement("td");
        newSalesColE.setAttribute("class", "salesInputColLast");
        newSalesRow.appendChild(newSalesColF);

	// Extract selected options
	if (sel_options.length>0){
		//from database
		sel_sales=sel_options[0];
		sel_nett=sel_options[1];
		sel_vat_type=sel_options[2];
		sel_vat=sel_options[3];
		sel_gross=sel_options[4];
	}
	else{
		//defaults
		sel_sales="def";
		sel_nett=0;
		sel_vat_type="21";
		sel_vat=0;
		sel_gross=0;
	}

	//input fields

	//sales Type & hidden select field
	var newSalesType = document.createElement("select");
	newSalesType.setAttribute("id", "salesType"+rowCount.toString());
	newSalesType.setAttribute("name", "salesType"+rowCount.toString());
        addOptions(newSalesType,sales_options,sel_sales);
	newSalesColA.appendChild(newSalesType);

	var newSalesTypeHidden = document.createElement("select");
	newSalesTypeHidden.setAttribute("id", "salesTypeHidden"+rowCount.toString());
	newSalesTypeHidden.setAttribute("name", "salesTypeHidden"+rowCount.toString());
	newSalesTypeHidden.setAttribute("hidden","true");
        addOptions(newSalesTypeHidden,sales_options,sel_sales);
	newSalesColA.appendChild(newSalesTypeHidden);

	newSalesType.addEventListener("change",function(){
		readOnlySelect(newSalesType.id,newSalesTypeHidden.id);
	});

	//Nett
	var newSalesNett = document.createElement("input");
	newSalesNett.setAttribute("id", "salesNett"+rowCount.toString());
	newSalesNett.setAttribute("name", "salesNett"+rowCount.toString());
	newSalesNett.setAttribute("type", "number");
	newSalesNett.setAttribute("step", "0.01");
	newSalesNett.setAttribute("value", sel_nett);
	newSalesColB.appendChild(newSalesNett);

	//VAT type
        var newVatType = document.createElement("select");
        newVatType.setAttribute("id", "salesVatType"+rowCount.toString());
        newVatType.setAttribute("name", "salesVatType"+rowCount.toString());
        addOptions(newVatType,vat_options,sel_vat_type);
        newSalesColC.appendChild(newVatType);

        var newVatTypeHidden = document.createElement("select");
        newVatTypeHidden.setAttribute("id", "salesVatTypeHidden"+rowCount.toString());
        newVatTypeHidden.setAttribute("name", "salesVatTypeHidden"+rowCount.toString());
	newVatTypeHidden.setAttribute("hidden","true");
        addOptions(newVatTypeHidden,vat_options,sel_vat_type);
        newSalesColC.appendChild(newVatTypeHidden);

	newVatType.addEventListener("change",function(){
		readOnlySelect(newVatType.id,newVatTypeHidden.id);
	});

	//VAT
	var newSalesVat = document.createElement("input");
	newSalesVat.setAttribute("id", "salesVat"+rowCount.toString());
	newSalesVat.setAttribute("name", "salesVat"+rowCount.toString());
	newSalesVat.setAttribute("type", "number");
	newSalesVat.setAttribute("step", "0.01");
	newSalesVat.setAttribute("readonly", "readonly");
	newSalesVat.setAttribute("value", sel_vat);
	newSalesColD.appendChild(newSalesVat);

	//Gross
	var newSalesGross = document.createElement("input");
	newSalesGross.setAttribute("id", "salesGross"+rowCount.toString());
	newSalesGross.setAttribute("name", "salesGross"+rowCount.toString());
	newSalesGross.setAttribute("type", "number");
	newSalesGross.setAttribute("step", "0.01");
	newSalesGross.setAttribute("readonly", "readonly");
	newSalesGross.setAttribute("value", sel_gross);
	newSalesColE.appendChild(newSalesGross);

	//Remove row button
	var newSalesRem = document.createElement("input");
        newSalesRem.setAttribute("id", "salesBut"+rowCount.toString());
	newSalesRem.setAttribute("name", "salesBut"+rowCount.toString());
	newSalesRem.setAttribute("type", "button");
	newSalesRem.setAttribute("value", "-");
	newSalesRem.setAttribute("onclick", "removeSalesRow(this.id)");
	newSalesColF.appendChild(newSalesRem);

	//Read only settings
	if(salesReadOnly){
		newSalesType.setAttribute("disabled","disabled");
		newSalesNett.setAttribute("readonly","readonly");
		newVatType.setAttribute("disabled","disabled");
	}

	//increment rowCount
	rowCount+=1;

	//adjust total values --> needed when entering data from database
	adjustSalesTot();
}


function removeSalesRow(butval){
	var row = parseInt(butval.replace("salesBut",""));
        var rowID = "salesRow"+row.toString();
	var rmRow = document.getElementById(rowID);
	var inputs=["salesType","salesTypeHidden","salesNett","salesVatType","salesVatTypeHidden","salesVat","salesGross"];
	
	//get all values up to rowcount and move downward
	for(r=row;r<(rowCount-1);r++){
		for (i=0;i<inputs.length;i++){
			old_value=document.getElementById(inputs[i]+(r+1).toString()).value;
			document.getElementById(inputs[i]+r.toString()).value=old_value;
		}
	}

        //remove the last row and all its children
        var lastRow = "salesRow"+(rowCount-1).toString();
	var lastRowEl = document.getElementById(lastRow);
	
	lastRowEl.remove();
	rowCount=rowCount-1;

	//adjust values
	adjustSalesTot();
}

//-----------------------------------------------------------------------------------------------------
//function that allows to switch between adding data for an existing invoice and creating a new invoice
//-----------------------------------------------------------------------------------------------------

function onchangeInput(id){
	var select = document.getElementById(id);
	select.addEventListener("change",function(){
		var selected = document.getElementById(id).value;
		switchInputMode(selected);
	});
}

function switchInputMode(selected){
	var invoiceMode=document.getElementById("invoiceMode");
	var invoiceModeHidden=document.getElementById("invoiceModeHidden");
	var invoiceField=document.getElementById("invoiceFieldSet");
	var salesField=document.getElementById("salesFieldSet");
	var invoiceLocation=document.getElementById("location");
	var retSwitch=true;

	//if a location exists ask the user if he/she is sure
	if(invoiceLocation.value && disableAlert==false){
		retSwitch = confirm("Switching input mode deletes current invoice value, continue?");
		console.log("User answer = ", retSwitch);

		//if the user wants to switch delete the invoice in the field
		if(retSwitch==true){

			//TODO: check if the invoice is an actual file
			deleteInvoice(invoiceLocation.value);
			readOnlySelect(invoiceMode.id,invoiceModeHidden.id);
		}
		else{
			invoiceMode.value=invoiceModeHidden.value;
		}
	}
	else{
		//load hidden select field
		readOnlySelect(invoiceMode.id,invoiceModeHidden.id);
	}

	//make the actual switch to input mode
	if(retSwitch==true){

		if(selected=="generate"){

			//freeze location input field and remove browse button
			document.getElementById("location").setAttribute("readonly","readonly");
			document.getElementById("invoiceUpBut").setAttribute("hidden","hidden");

			invoiceField.removeAttribute("hidden");
			addSalesRowButton.setAttribute("disabled","disabled");
			salesReadOnly=true;

			//set relevant inputs to readonly
			for(i=1;i<rowCount;i++){
				var sales_type=document.getElementById("salesType"+i.toString());
				var nett=document.getElementById("salesNett"+i.toString());
				var vat_type=document.getElementById("salesVatType"+i.toString());
				var sales_but=document.getElementById("salesBut"+i.toString());

				nett.setAttribute("readonly","readonly");
				vat_type.setAttribute("disabled","disabled"); 
				sales_type.setAttribute("disabled","disabled");
				sales_but.setAttribute("disabled","disabled");
			}

		}
		else{
			//freeze /unfreeze location input field and remove browse button
			if(selected=="upload"){
				document.getElementById("location").setAttribute("readonly","readonly");
				document.getElementById("invoiceUpBut").removeAttribute("hidden");	
			}
			else if(selected=="paper"){
				document.getElementById("location").removeAttribute("readonly","readonly");
				document.getElementById("invoiceUpBut").setAttribute("hidden","hidden");
			}

			invoiceField.setAttribute("hidden","true");
			addSalesRowButton.removeAttribute("disabled");
			salesReadOnly=false;	
			//set relevant inputs to write
			for(i=1;i<rowCount;i++){
				var sales_type=document.getElementById("salesType"+i.toString());
				var nett=document.getElementById("salesNett"+i.toString());
				var vat_type=document.getElementById("salesVatType"+i.toString());
				var sales_but=document.getElementById("salesBut"+i.toString());

				nett.removeAttribute("readonly");
				sales_type.removeAttribute("disabled");
				vat_type.removeAttribute("disabled");
				sales_but.removeAttribute("disabled");
			
			}

		}
	}
}

function switchAlert(){
	var retVal= confirm("This will delete your invoice, do you want to continue?");
	return true;		
}


//----------------------------------------------------------------------------------------
//functions that fill (read-only) or hidden field inputs when a change to the form fieldset is applied
//----------------------------------------------------------------------------------------

function onChangeFieldSet(id){
	var form = document.getElementById(id);
	
	if(id=="salesFieldSet"){
		//note eventlistener wants a function, addSalesRow() actually gives a return value
		form.addEventListener("change",function(){
			for (i=1;i<rowCount;i++){
				adjustSalesRow(i); 
			}
			adjustSalesTot();
			saveGuard();
			
		});
	}
	else if(id=="invoiceFieldSet"){
		form.addEventListener("change",function(){
			for (i=1;i<inRowCount;i++){
				adjustInvoiceRow(i); 
			}
			invoiceToSales();
			saveGuard();
		});
	}

	else if(id=="metaFieldSet"){
		form.addEventListener("change",function(){
			saveGuard();
		});
	}

}

function invoiceToSales(){
	
	//create an array and loop through all the invoice lines
	var sales_lines=[];
	var found;

	for(i=1;i<inRowCount;i++){
		found=false;
		var check=document.getElementById("invoiceTypeHidden"+i.toString())
		console.log(check.value);
		if (typeof(check) !="undefined" && check != null){  //head moet hier in blijven anders reset de boel niet
			var invoiceType=document.getElementById("invoiceTypeHidden"+i.toString()).value;
			var invoiceNett=document.getElementById("invoiceNett"+i.toString()).value;
			var invoiceVatType=document.getElementById("invoiceVatTypeHidden"+i.toString()).value;
			var invoiceVat=+invoiceNett*(+invoiceVatType/100);
			var invoiceGross=+invoiceNett+(+invoiceVat);
		}
		
		for(l=0;l<sales_lines.length;l++){
			if(sales_lines[l][0]==invoiceType && sales_lines[l][2]==invoiceVatType){
				sales_lines[l][1]=(+sales_lines[l][1])+(+invoiceNett);
				sales_lines[l][3]=(+sales_lines[l][1])*(+sales_lines[l][2]/100);
				sales_lines[l][4]=(+sales_lines[l][1])+(+sales_lines[l][3]);
				found=true;
			}
		}

		if(!found && invoiceType!="head"){ //head niet toevoegen
			sales_lines.push([invoiceType,invoiceNett,invoiceVatType,invoiceVat,invoiceGross]);
		} 
	}
	
	//change existing rows and create new rows if needed
	for(s=0;s<sales_lines.length;s++){ 
		var salesType=document.getElementById("salesType"+(s+1).toString())

		if (typeof(salesType) !="undefined" && salesType != null){ 

			//Sales type
			document.getElementById("salesType"+(s+1).toString()).removeAttribute("disabled");
			document.getElementById("salesType"+(s+1).toString()).value=sales_lines[s][0];
			document.getElementById("salesType"+(s+1).toString()).setAttribute("disabled","disabled");

			//Vat type
			document.getElementById("salesVatType"+(s+1).toString()).removeAttribute("disabled");
			document.getElementById("salesVatType"+(s+1).toString()).value=sales_lines[s][2];
			document.getElementById("salesVatType"+(s+1).toString()).setAttribute("disabled","disabled");

			//Nett, Gross & VAT
			document.getElementById("salesNett"+(s+1).toString()).value=sales_lines[s][1];
			document.getElementById("salesVat"+(s+1).toString()).value=sales_lines[s][3];
			document.getElementById("salesGross"+(s+1).toString()).value=sales_lines[s][4];
		}
		else{
			if (salesType !="null"){ 	//een header geeft salesType=null in de sales row
				//2 cases de rij bestond of er zijn niet meer rijen
				addSalesRow(sel_options=sales_lines[s]);
			}
		}
	}

        //remove rows if no longer needed
	for(r=s+1;r<rowCount;r++){
		var salesType=document.getElementById("salesTypeHidden"+(s+1).toString())
		if (typeof(salesType) !="undefined" && salesType != null){ 
			removeSalesRow("salesBut"+r.toString());
		}
	}
	adjustSalesTot();
		
}

function adjustInvoiceRow(row){
	var check=document.getElementById('invoiceType'+row.toString());
	if (typeof(check) !="undefined" && check != null){ 
		if(check.value!="head"){
			var amount=+document.getElementById('invoiceAmount'+row.toString()).value;
			var price=+document.getElementById('invoicePrice'+row.toString()).value;
			var vat_type=+document.getElementById('invoiceVatType'+row.toString()).value;
			console.log("amount :",amount);
			console.log("price : ",price);
			console.log("vat_type : ",vat_type);

			var nett=document.getElementById('invoiceNett'+row.toString());
			nett.value=(amount*price).toFixed(2);
			invoiceHeaderRow(row,"reveal");
		}
		else{
			//set all fields to hiden and 0
			invoiceHeaderRow(row,"hide");
			document.getElementById('invoiceAmount'+row.toString()).value=0;
			document.getElementById('invoicePrice'+row.toString()).value=0;
			document.getElementById('invoiceNett'+row.toString()).value=0;
		}			
	}
}

function invoiceHeaderRow(r, what){

	if(what=="hide"){
		document.getElementById('invoiceAmount'+r.toString()).setAttribute("hidden","hidden");
		document.getElementById('invoicePrice'+r.toString()).setAttribute("hidden","hidden");
		document.getElementById('invoiceVatType'+r.toString()).setAttribute("hidden","hidden");
		document.getElementById('invoiceNett'+r.toString()).setAttribute("hidden","hidden");
	}

	if(what=="reveal"){
		document.getElementById('invoiceAmount'+r.toString()).removeAttribute("hidden");
		document.getElementById('invoicePrice'+r.toString()).removeAttribute("hidden");
		document.getElementById('invoiceVatType'+r.toString()).removeAttribute("hidden");
		document.getElementById('invoiceNett'+r.toString()).removeAttribute("hidden");
	}
}

function adjustSalesRow(row){
	var check=document.getElementById('salesTypeHidden'+row.toString());
	if (typeof(check) !="undefined" && check != null){ 
		var nett=document.getElementById('salesNett'+row.toString()).value;
		
		var vat_type=+document.getElementById('salesVatTypeHidden'+row.toString()).value;
		
		var vat=document.getElementById('salesVat'+row.toString());
		vat.value=(+nett*(vat_type/100)).toFixed(2);

		var gross=document.getElementById('salesGross'+row.toString());
		gross.value=(+nett+(+vat.value)).toFixed(2);
	}
}

function adjustSalesTot(){
	//get sum for nett
	var inputTot=document.getElementById("nettTot");
        var sumnett=0;
	for (i=1;i<rowCount;i++){
		if (document.getElementById('salesNett'+i.toString())){
			sumnett+=+document.getElementById('salesNett'+i.toString()).value;
		}
	}

        inputTot.value=sumnett;
	
	//get sum of vat
	var sumvat=adjustTotVat();
	
	//get sum for gross
	var sumgross=sumnett+sumvat;	
	var grossTot=document.getElementById("grossTot");
	
	// only if shift=no
	var shiftTot=document.getElementById("vatShift");
	if (shiftTot.value=="no"){
		grossTot.value=sumgross;
	}
	else{
		grossTot.value=sumnett;
	}
}

function adjustTotVat(options=new Array('9','21')){
	var totVat=0;

	for (i=0;i<options.length;i++){
		var id="vatTot_"+options[i];
		var inputTot=document.getElementById(id);
		var rowTot=document.getElementById("vatTotRow_"+options[i]);
		var sum=0;

		//for each row
		for (n=1;n<rowCount;n++){
			vat_query="salesVatTypeHidden"+n.toString()
			if(document.getElementById(vat_query)){
				var vattype=document.getElementById(vat_query).value;
				var vat=document.getElementById("salesVat"+n.toString()).value;

				if (vattype){
					if(vattype==options[i]){
						sum+=+vat;
					}
				}
			}
		}

		inputTot.value=sum;
		totVat+=sum;

	}

	return totVat;
}

function readOnlySelect(id,id_hidden){
	
	//get all child elements of the form
	var select = document.getElementById(id);
	var select_hidden = document.getElementById(id_hidden);

	//Load previous value in a data field
	select.setAttribute("data",select_hidden.value);
	select_hidden.value=select.value;
}

//Maybe you can move the presets to PHP entirely and call the onchange function from there
function periodPresets(id){
	var period = document.getElementById("periodSelect");
	var from = document.getElementById("periodFrom");
	var to = document.getElementById("periodTo");
	var year = Date.now().prototype.getFullYear();

	if(period == year){
		from.value=new Date(year,1,1);
		to.value= new Date(year,12,31);
	}
	else if(period == "Q1_"+year){
		from.value=new Date(year,1,1);
		to.value= new Date(year,3,31);
	}
	else if(period == "Q2_"+year){
		from.value=new Date(year,4,1);
		to.value= new Date(year,6,30);
	}

	else if(period == "Q2_"+year){
		from.value=new Date(year,7,1);
		to.value= new Date(year,9,30);
	}
	else if(period == "Q2_"+year){
		from.value=new Date(year,10,1);
		to.value= new Date(year,12,31);
	}
}


//--------------------------------------------------------------------------
//Functions that check & limit the user input 
//--------------------------------------------------------------------------

function saveGuard(){

	change_time= new Date();
	var invoice=document.getElementById("location").value;
	var span=document.getElementById("update_span");
	var save_but=document.getElementById("update");
	var invoice_mode=document.getElementById("invoiceMode").value;

	//check if there is an invoice location set	
	if(invoice){
		//if the invoice is generated before last change time it can be saved
		if(invoice_mode=='generate'){

			//get the date it was made
			var mt=new Date(make_time.getTime());
			mt.setSeconds(mt.getSeconds()+1);

			//if changed after disable, otherwise release
			if(change_time>mt){
				save_but.setAttribute('disabled','disabled');	
				span.setAttribute("title","Please save invoice first");		
			}
			else{
				save_but.removeAttribute('disabled');
				span.removeAttribute("title");
			}
		}
		//if an invoice is uploaded or inserted manually you can save
		else{
			save_but.removeAttribute('disabled');
		}
	}

	//if no invoice is given you cannot save
	else{
		save_but.setAttribute('disabled','disabled');
		span.setAttribute("title","Please provide an invoice location or generate an invoice first");	
	}
}


//--------------------------------------------------------------------------
//Functions that create an invoice from data entered in the invoice fieldset 
//--------------------------------------------------------------------------

function onchangeMake(id,name){
	var but = document.getElementById(id);	
	but.addEventListener("click",function(){
		makeInvoice(name);
	});
}

function deleteInvoice(location){

	var xhr = new XMLHttpRequest();
	xhr.onreadystatechange = function() {

		if (this.readyState == 4 && this.status == 200) {

			//set filename in the location field
			document.getElementById('location').value = this.responseText;;

			//stop displaying the invoice in the viewer

			//make sure the user cannot save
			saveGuard();
		}
	};

	var data = new FormData();
	data.append("invoice_del",location);
	xhr.open('POST', '');
	xhr.send(data);
}


function uploadInvoice(name, input) {
	var xhr = new XMLHttpRequest();
	xhr.onreadystatechange = function() {

		if (this.readyState == 4 && this.status == 200) {

			console.log("file uploaded and a response");

			//set filename in the location field
			filename = this.responseText;
			document.getElementById('location').value = filename;

			//display the invoice in the viewer
			type  = filename.split('.').pop();
			if (type=='pdf') document.getElementById('invoiceView').innerHTML = '<embed src="files/' + filename + '" width="400px" height="600px" />';
			if (type=='jpg') document.getElementById('invoiceView').innerHTML = '<img src="files/' + filename + '" width="400px" />';

			//set name of the location field
			filename = this.responseText;
			document.getElementById('location').value=filename;

			//TODO: Show the file in the viewer

			//allow user to save the entry
			make_time=new Date();
			saveGuard();
		}
	};
	var data = new FormData();
	data.append(name, input.files[0]);
	xhr.open('POST', '');
	xhr.send(data);
}

function makeInvoice(name, options=new Array('9','21')){

	var invoice_dict= {};
	var meta_dict={};
	num=0;

	//get info for each row
	//add meta data
        meta_dict['recipient']=document.getElementById('contactId').value;
	meta_dict['invoiceDate']=document.getElementById('transactionDate').value;
	meta_dict['reference']=document.getElementById('reference').value;
	meta_dict['project']=document.getElementById('projectId').value;
	invoice_dict["Meta"]=meta_dict;
	
	//walk through invoice lines
	for (n=1;n<inRowCount;n++){
		check=document.getElementById('invoiceTypeHidden'+n.toString())
		if (typeof(check) !="undefined" && check != null){ 	
			line_dict={};
			line_dict['invoiceType']=document.getElementById('invoiceTypeHidden'+n.toString()).value;
			line_dict['desc']=document.getElementById('invoiceDesc'+n.toString()).value;

			if(line_dict['invoiceType']=="head"){ //double check not really
				line_dict['amount']=0;
				line_dict['price']=0;				
				line_dict['nett']=0;
				line_dict['vat_type']=0;
			}
			
			else{
				line_dict['amount']=+document.getElementById('invoiceAmount'+n.toString()).value;
				line_dict['price']=+document.getElementById('invoicePrice'+n.toString()).value;
				line_dict['nett']=+document.getElementById('invoiceNett'+n.toString()).value;
				line_dict['vat_type']=+document.getElementById('invoiceVatTypeHidden'+n.toString()).value;
			}

			invoice_dict[("invoiceLine_"+n.toString())]=line_dict;
		}
		
	}

	//save sales totals
	sales_tot={};
	sales_tot['nett']=document.getElementById("nettTot").value;
	sales_tot['gross']=document.getElementById("grossTot").value;
	sales_tot['shift']=document.getElementById("vatShift").value;

	for (i=0;i<options.length;i++){
		sales_tot["vat_"+options[i]]=document.getElementById("vatTot_"+options[i]).value;
	}

	invoice_dict["salesTot"]=sales_tot;

	//save a temporary json file with the invoice data
	invoice_string=JSON.stringify(invoice_dict);
	console.log(invoice_string)

	// send the invoice to php
	var xhr = new XMLHttpRequest();

	xhr.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {

			//set name of the location field
			filename = this.responseText;
			document.getElementById('location').value=filename;

			//TODO: Show the file in the viewer

			//allow user to save the entry
			make_time=new Date();
			saveGuard();
		}
	};
	
	var data = new FormData();

	//checken of er een locatie is
	if(document.getElementById('location').value!=""){
		data.append("Location",document.getElementById('location').value)
	}
	data.append(name, invoice_string);
	xhr.open('POST', '');  //´ '=zichzelf
	xhr.send(data);
	

}

//--------------------------------------------------------------------------
//Function that reads the JSON file and puts it into the input field
//---------------------------------------------------------------------


function readJson(json){

	//ensure that automatic reading does not call an alert box	
	disableAlert=true;

	// set invoice mode to new invoice and disable the selection field
	var im="";
	
	//Iterate through the json file
	for(var line in json){
		if(json.hasOwnProperty(line)){
			var l=json[line];
			console.log("Line:",line);

			//check file type
			if(line=="Meta"){
				im=l.filetype;
			}

			//load invoice lines
			else if(line.substring(0,11)=="invoiceLine" && im=="generate") {
				addInvoiceRow([l.invoiceType,l.invoiceDesc,l.invoiceAmount,l.invoicePrice,l.invoiceNett,l.invoiceVatType])	
			}

			//load sales_lines
			else if(line.substring(0,9)=="salesLine" && im=="upload"){
				addSalesRow([l.salesType,l.salesNett,l.salesVatType,l.salesVat,l.salesGross])
			}

			//load sales_lines
			else if(line.substring(0,9)=="salesLine" && im=="paper"){
				addSalesRow([l.salesType,l.salesNett,l.salesVatType,l.salesVat,l.salesGross])
			}
		}
	}

	//set the invoice mode correctly, based on the json
	var invoice_mode=document.getElementById("invoiceMode");
	invoice_mode.value=im;

	if(im=="generate" || im=="upload"){
		switchInputMode(im);
		make_time= new Date();
	}

	disableAlert=false;
}


// de rij blijft bestaan maar heeft geen zichtbare inhoud meer,
// misschien aanpassen dat ook echt alle children worden verwijderd
// nu volstaat selecteren op .innerHTML='x'
//iets van $phpding=function() die ook teruggeeft het aantal rijen
