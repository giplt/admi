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

function addOnClick(exp_options,vat_options){
	var but = document.getElementById("addRowButton");

	//note eventlistener wants a function, addExpenseRow() actually gives a return value
	but.addEventListener("click",function(){
		addExpenseRow(exp_options,vat_options);
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


function addExpenseRow(exp_options,vat_options, sel_options="") {

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
        newExpenseColE.setAttribute("class", "expenseInputCol");
        newExpenseRow.appendChild(newExpenseColF);

        var newExpenseColG = document.createElement("td");
        newExpenseColE.setAttribute("class", "expenseInputColLast");
        newExpenseRow.appendChild(newExpenseColG);

	// Extract selected options
	if (sel_options.length>0){
		sel_expense=sel_options[0];
		sel_gross=sel_options[1];
		sel_nett=sel_options[2];
		sel_vat=sel_options[3];
		sel_vat_type=sel_options[4];

		console.log("Expense:", sel_expense);
		console.log("Gross:", sel_gross);
		console.log("Nett:", sel_nett);
		console.log("Vat:", sel_vat);
		console.log("Vat type:", sel_vat_type);
	}
	else{
		sel_expense="def";
		sel_gross=0;
		sel_nett=0;
		sel_vat=0;
		sel_vat_type="def";
	}

	//input fields
	var newExpenseType = document.createElement("select");
	newExpenseType.setAttribute("id", "ExpenseType"+rowCount.toString());
	newExpenseType.setAttribute("name", "ExpenseType"+rowCount.toString());
        addOptions(newExpenseType,exp_options,sel_expense);
	newExpenseColA.appendChild(newExpenseType);

	var newExpenseGross = document.createElement("input");
	newExpenseGross.setAttribute("id", "gross"+rowCount.toString());
	newExpenseGross.setAttribute("name", "gross"+rowCount.toString());
	newExpenseGross.setAttribute("type", "number");
	newExpenseGross.setAttribute("step", "0.01");
	newExpenseGross.setAttribute("value", sel_gross);
	newExpenseGross.setAttribute("class", "expenseInputField");
	newExpenseGross.setAttribute("onchange","adjustTot('gross',rowCount)");
	newExpenseColB.appendChild(newExpenseGross);

	var newExpenseNett = document.createElement("input");
	newExpenseNett.setAttribute("id", "nett"+rowCount.toString());
        newExpenseNett.setAttribute("name", "nett"+rowCount.toString());
	newExpenseNett.setAttribute("class", "expenseInputField");
	newExpenseNett.setAttribute("type", "number");
	newExpenseNett.setAttribute("step", "0.01");
	newExpenseNett.setAttribute("value", sel_nett);
        newExpenseNett.setAttribute("onchange","adjustTot('nett',rowCount)");
	newExpenseColC.appendChild(newExpenseNett);

	var newExpenseVat = document.createElement("input");
	newExpenseVat.setAttribute("id", "vat"+rowCount.toString());
	newExpenseVat.setAttribute("name", "vat"+rowCount.toString());
	newExpenseVat.setAttribute("type", "number");
	newExpenseVat.setAttribute("step", "0.01");
	newExpenseVat.setAttribute("value", sel_vat);
	newExpenseVat.setAttribute("class", "expenseInputField");
        newExpenseVat.setAttribute("onchange","adjustTot('vat',rowCount)");
	newExpenseColD.appendChild(newExpenseVat);

        var newVatType = document.createElement("select");
        newVatType.setAttribute("name", "vatType"+rowCount.toString());
        addOptions(newVatType,vat_options,sel_vat_type);
        newExpenseColE.appendChild(newVatType);

	var newExpenseRem = document.createElement("input");
        newExpenseRem.setAttribute("id", "expenseBut"+rowCount.toString());
	newExpenseRem.setAttribute("name", "expenseBut"+rowCount.toString());
	newExpenseRem.setAttribute("type", "button");
	newExpenseRem.setAttribute("value", "\uD83D\uDDD1");
	newExpenseRem.setAttribute("onclick", "removeExpenseRow(this.id)");
	newExpenseColG.appendChild(newExpenseRem);

	//increment rowCount
	rowCount+=1;

	//adjust total values
        adjustTot("gross", rowCount);
	adjustTot("nett", rowCount);
	adjustTot("vat", rowCount);
}

function removeExpenseRow(butval){
        var rowID = "expenseRow"+butval.replace("expenseBut","");
	var rmRow = document.getElementById(rowID);
        rmRow.innerHTML="x";

	//adjust values
        adjustTot("gross", rowCount);
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

function upload(name, input) {
	var xhr = new XMLHttpRequest();
	xhr.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			filename = this.responseText;
			document.getElementById('url').value = filename;
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

//misschien de totalen een input veld maken, waar de inhoud van veranderd
// zo kan er ook een mogelijkheid zijn om btw maar 1x in te vullen, bij factuur met materialen+uren
// de rij blijft bestaan maar heeft geen zichtbare inhoud meer,
// misschien aanpassen dat ook echt alle children worden verwijderd
// nu volstaat selecteren op .innerHTML='x'
//iets van $phpding=function() die ook teruggeeft het aantal rijen
