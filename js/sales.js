rowCount=1;

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
			}
			//add options for contact only
			if (options[i][2]==reduct_val){
				red_options.push(options[i]);
			}
		}
		addOptions(select,red_options);
		});

}

function addOptionsPHP(selectID,options){
	var select = document.getElementById(selectID);
	addOptions(select,options);
}

// --- EVENT listneres

function addOnClick(sales_options,vat_options){
	var but = document.getElementById("addRowButton");

	//note eventlistener wants a function, addSalesRow() actually gives a return value
	but.addEventListener("click",function(){
		addSalesRow(sales_options,vat_options);
	});
}

function onchangeMake(id,name){
	var but = document.getElementById(id);	
	but.addEventListener("click",function(){
		makeInvoice(name);
	});
}

function makeInvoice(name){

	var invoice_dict= {}

	num=0;

	//get info for each row
	for (n=1;n<rowCount;n++){

		if (document.getElementById('amount'+n.toString())){	
			amount=+document.getElementById('amount'+n.toString()).value;
			price=+document.getElementById('price'+n.toString()).value;
			vat_type=+document.getElementById('vatType'+n.toString()).value;
			nett=+document.getElementById('nett'+n.toString()).value;
			gross=+document.getElementById('gross'+n.toString()).value;
			vat=+document.getElementById('vat'+n.toString()).value;
			vatShift=+document.getElementById('vatShift').value;
			
			line_dict={}
			line_dict["SalesType"]=amount;
			line_dict["desc"]=amount;
			line_dict["amount"]=amount;
			line_dict["price"]=price;
			line_dict["vat_type"]=vat_type;
			line_dict["nett"]=nett;
			line_dict["gross"]=gross;
			line_dict["vat"]=vat;
			line_dict["vatShift"]=vatShift;
			invoice_dict[("Line_"+n.toString())]=line_dict;
		}

	}
	
	//save a json file with the data
	invoice_string=JSON.stringify(invoice_dict);

	// send the invoice to php
	var xhr = new XMLHttpRequest();

	xhr.onreadystatechange = function() {
		console.log("Function called");
		if (this.readyState == 4 && this.status == 200) {
			console.log("Response text: ", this.responseText);
			filename = this.responseText;
			document.getElementById('location').setAttribute("value",filename);
			document.getElementById('location').setAttribute("readonly","readonly");
			//TODO: PDF laten zien als dat klaar is
		}
	};
	
	var data = new FormData();
	data.append(name, invoice_string);
	xhr.open('POST', '');  //´ '=zichzelf
	xhr.send(data);
	

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


function addSalesRow(sales_options,vat_options, sel_options="") {

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
        newSalesColE.setAttribute("class", "salesInputCol");
        newSalesRow.appendChild(newSalesColF);

        var newSalesColG = document.createElement("td");
        newSalesColE.setAttribute("class", "salesInputColLast");
        newSalesRow.appendChild(newSalesColG);

	// Extract selected options
	if (sel_options.length>0){
		//from database
		sel_sales=sel_options[0];
		sel_gross=sel_options[1];
		sel_nett=sel_options[2];
		sel_vat=sel_options[4];
		sel_vat_type=sel_options[5];

		sel_price=sel_options[6];
		sel_desc=sel_options[7];
		sel_amount=sel_options[8];
	}
	else{
		//defaults
		sel_sales="def";
		sel_desc="";
		sel_amount=0;
		sel_price=0;
		sel_nett=0;
		sel_vat_type="21";
		sel_vat=0;
		sel_gross=0;
	}

	//input fields
	var newSalesType = document.createElement("select");
	newSalesType.setAttribute("id", "SalesType"+rowCount.toString());
	newSalesType.setAttribute("name", "SalesType"+rowCount.toString());
        addOptions(newSalesType,sales_options,sel_sales);
	newSalesColA.appendChild(newSalesType);

	var newSalesGross = document.createElement("input");
	newSalesGross.setAttribute("id", "desc"+rowCount.toString());
	newSalesGross.setAttribute("name", "description"+rowCount.toString());
	newSalesGross.setAttribute("type", "text");
	newSalesGross.setAttribute("value", sel_desc);
	newSalesColB.appendChild(newSalesGross);

	var newSalesAmount = document.createElement("input");
	newSalesAmount.setAttribute("id", "amount"+rowCount.toString());
        newSalesAmount.setAttribute("name", "amount"+rowCount.toString());
	newSalesAmount.setAttribute("type", "number");
	newSalesAmount.setAttribute("step", "0.1");
        newSalesAmount.setAttribute("onchange","adjustRow("+rowCount+")");
	newSalesAmount.setAttribute("value", sel_amount);
	newSalesColC.appendChild(newSalesAmount);

	var newSalesPrice = document.createElement("input");
	newSalesPrice.setAttribute("id", "price"+rowCount.toString());
	newSalesPrice.setAttribute("name", "price"+rowCount.toString());
	newSalesPrice.setAttribute("type", "number");
	newSalesPrice.setAttribute("step", "0.01");
        newSalesPrice.setAttribute("onchange","adjustRow("+rowCount+",rowCount)");
	newSalesPrice.setAttribute("value", sel_price);
	newSalesColD.appendChild(newSalesPrice);

	var newSalesNett = document.createElement("input");
	newSalesNett.setAttribute("id", "nett"+rowCount.toString());
	newSalesNett.setAttribute("name", "nett"+rowCount.toString());
	newSalesNett.setAttribute("type", "number");
	newSalesNett.setAttribute("step", "0.01");
	newSalesNett.setAttribute("value", sel_nett);
	newSalesColE.appendChild(newSalesNett);

        var newVatType = document.createElement("select");
        newVatType.setAttribute("id", "vatType"+rowCount.toString());
        newVatType.setAttribute("name", "vatType"+rowCount.toString());
        newVatType.setAttribute("onchange","adjustRow("+rowCount+")");
        addOptions(newVatType,vat_options,sel_vat_type);
        newSalesColF.appendChild(newVatType);

	var newSalesVat = document.createElement("input");
	newSalesVat.setAttribute("id", "vat"+rowCount.toString());
	newSalesVat.setAttribute("name", "vat"+rowCount.toString());
	newSalesVat.setAttribute("type", "number");
	newSalesVat.setAttribute("step", "0.01");
	newSalesVat.setAttribute("hidden", "true");
	newSalesVat.setAttribute("value", sel_vat);
	newSalesColF.appendChild(newSalesVat);

	var newSalesGross = document.createElement("input");
	newSalesGross.setAttribute("id", "gross"+rowCount.toString());
	newSalesGross.setAttribute("name", "gross"+rowCount.toString());
	newSalesGross.setAttribute("type", "number");
	newSalesGross.setAttribute("step", "0.01");
	newSalesGross.setAttribute("hidden", "true");
	newSalesGross.setAttribute("value", sel_gross);
	newSalesColF.appendChild(newSalesGross);

	var newSalesRem = document.createElement("input");
        newSalesRem.setAttribute("id", "salesBut"+rowCount.toString());
	newSalesRem.setAttribute("name", "salesBut"+rowCount.toString());
	newSalesRem.setAttribute("type", "button");
	newSalesRem.setAttribute("value", "-");
	newSalesRem.setAttribute("onclick", "removeSalesRow(this.id)");
	newSalesColG.appendChild(newSalesRem);

	//increment rowCount
	rowCount+=1;

	//adjust total values
	adjustTot(rowCount);
}

function removeSalesRow(butval){
        var rowID = "salesRow"+butval.replace("salesBut","");
	var rmRow = document.getElementById(rowID);
        rmRow.innerHTML="x";

	//adjust values
        adjustTot(rowCount);
}

//------FUNCTIONS for dynamically summing fields and totals

function onchangeForm(id){
	var form = document.getElementById(id);

	//note eventlistener wants a function, addSalesRow() actually gives a return value
	form.addEventListener("change",function(){
		adjustTot(rowCount);
	});
}

function adjustRow(row,rowCount){
	var amount=+document.getElementById('amount'+row.toString()).value;
	var price=+document.getElementById('price'+row.toString()).value;
	var vat_type=+document.getElementById('vatType'+row.toString()).value;
	var nett=document.getElementById('nett'+row.toString());
	var gross=document.getElementById('gross'+row.toString());
	var vat=document.getElementById('vat'+row.toString());

	nett.setAttribute("value",amount*price);
	vat.setAttribute("value",+nett.value*(vat_type/100));
	gross.setAttribute("value",+nett.value+(+vat.value));

	adjustTot(rowCount);
}

function adjustTot(rowCount){
	//get sum for nett
	var inputTot=document.getElementById("nettTot");
        var sumnett=0;
	for (i=1;i<rowCount;i++){
		if (document.getElementById('nett'+i.toString())){
			sumnett+=+document.getElementById('nett'+i.toString()).value;
		}
	}

        inputTot.value=sumnett;
	
	//get sum of vat
	var sumvat=adjustTotVat(rowCount);
	
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

function adjustTotVat(rowCount, vat_options=new Array('9','21')){

	var totVat=0
	for (i=0;i<vat_options.length;i++){
		var id="vatTot_"+vat_options[i]
		var inputTot=document.getElementById(id);
		var rowTot=document.getElementById("vatTotRow_"+vat_options[i]);
		var sum=0;
			
		//for each row
		for (n=1;n<rowCount;n++){

			vat_query="vatType"+n.toString()
			if(document.getElementById(vat_query)){
				var vattype=document.getElementById("vatType"+n.toString()).value;
				var vat=document.getElementById("vat"+n.toString()).value;

				if (vattype){
					if(vattype==vat_options[i]){
						sum+=+vat;
					}
				}
			}

		inputTot.value=sum;
		totVat+=sum;
		}
	}
	return totVat;
}

// de rij blijft bestaan maar heeft geen zichtbare inhoud meer,
// misschien aanpassen dat ook echt alle children worden verwijderd
// nu volstaat selecteren op .innerHTML='x'
//iets van $phpding=function() die ook teruggeeft het aantal rijen
