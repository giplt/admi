rowCount=1;

function addOptionsPHP_onclick(selectID,options,reductionID){
	
	var select = document.getElementById(selectID);
	var reduct = document.getElementById(reductionID);
	console.log("options",options);
	
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

function addOptionsPHP(selectID,options){
	var select = document.getElementById(selectID);
	addOptions(select,options);
}

function addOnClick(sales_options,vat_options){
	var but = document.getElementById("addRowButton");

	//note eventlistener wants a function, addSalesRow() actually gives a return value
	but.addEventListener("click",function(){
		addSalesRow(sales_options,vat_options);
	});
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
	console.log("Sel options: ",sel_options);
	if (sel_options.length>0){
		//from database
		sel_sales=sel_options[0];
		sel_desc=sel_options[1];
		sel_amount=sel_options[2];
		sel_price=sel_options[3];
		sel_nett=sel_options[4];
		sel_vat_type=sel_options[5];
	}
	else{
		//defaults
		sel_sales="def";
		sel_desc="";
		sel_amount=0;
		sel_price=0;
		sel_nett=0;
		sel_vat_type="21";
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

	var newSalesNett = document.createElement("input");
	newSalesNett.setAttribute("id", "amount"+rowCount.toString());
        newSalesNett.setAttribute("name", "amount"+rowCount.toString());
	newSalesNett.setAttribute("type", "number");
	newSalesNett.setAttribute("step", "0.1");
	newSalesNett.setAttribute("value", sel_amount);
	newSalesColC.appendChild(newSalesNett);

	var newSalesVat = document.createElement("input");
	newSalesVat.setAttribute("id", "price"+rowCount.toString());
	newSalesVat.setAttribute("name", "price"+rowCount.toString());
	newSalesVat.setAttribute("type", "number");
	newSalesVat.setAttribute("step", "0.01");
	newSalesVat.setAttribute("value", sel_price);
	newSalesColD.appendChild(newSalesVat);

	var newSalesVat = document.createElement("input");
	newSalesVat.setAttribute("id", "nett"+rowCount.toString());
	newSalesVat.setAttribute("name", "nett"+rowCount.toString());
	newSalesVat.setAttribute("type", "number");
	newSalesVat.setAttribute("step", "0.01");
	newSalesVat.setAttribute("value", sel_nett);
        newSalesVat.setAttribute("onchange","adjustTot('nett',rowCount)");
	newSalesColE.appendChild(newSalesVat);

        var newVatType = document.createElement("select");
        newVatType.setAttribute("name", "vatType"+rowCount.toString());
        addOptions(newVatType,vat_options,sel_vat_type);
        newSalesColF.appendChild(newVatType);

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
        adjustTot("gross", rowCount);
	adjustTot("nett", rowCount);
	adjustTot("vat", rowCount);
}

function removeSalesRow(butval){
        var rowID = "salesRow"+butval.replace("salesBut","");
	var rmRow = document.getElementById(rowID);
        rmRow.innerHTML="x";

	//adjust values
        adjustTot("nett", rowCount);
	adjustTot("vat", rowCount);
}

function adjustTot(type,rowCount){
	var inputTot=document.getElementById(type+"Tot");
        var sum=0;
	for (i=1;i<rowCount;i++){
		if (document.getElementById(type+i.toString())){
			sum+=+document.getElementById(type+i.toString()).value;
		}
	}
        inputTot.value=sum;
}


//misschien de totalen een input veld maken, waar de inhoud van veranderd
// zo kan er ook een mogelijkheid zijn om btw maar 1x in te vullen, bij factuur met materialen+uren
// de rij blijft bestaan maar heeft geen zichtbare inhoud meer,
// misschien aanpassen dat ook echt alle children worden verwijderd
// nu volstaat selecteren op .innerHTML='x'
//iets van $phpding=function() die ook teruggeeft het aantal rijen
