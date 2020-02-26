rowCount=1;

function addOptionsPHP(selectID,options){
	var select = document.getElementById(selectID);
	addOptions(select,options);
}

function addOnClick(exp_options,vat_options){
	var but = document.getElementById("addRowButton");
	console.log("Button:",but);
        console.log("expenses:",exp_options);
        console.log("vat:",vat_options);
	//note eventlistener wants a function, addExpenseRow() actually gives a return value
	but.addEventListener("click",function(){
		addExpenseRow(exp_options,vat_options);
	});
}

function addOptions(select_obj,options){
        for (i=0;i<options.length;i++){
                newOption = document.createElement("option");
                newOption.setAttribute("value",options[i][0]);
                newOption.innerHTML=options[i][1];
                if (options[i][0]=="placeholder"){
                        newOption.setAttribute("disabled","disabled")
                };
                select_obj.appendChild(newOption);
        };
}


function addExpenseRow(exp_options,vat_options) {
	var expenseTable = document.getElementById("expenseTable");
	var newExpenseRow = document.createElement("tr");
	newExpenseRow.setAttribute("id", "expenseRow"+rowCount.toString());
	newExpenseRow.setAttribute("class", "expenseInputRow");
	expenseTable.appendChild(newExpenseRow);

	var newExpenseColA = document.createElement("td");
	newExpenseColA.setAttribute("class", "expenseInputCol");
	newExpenseRow.appendChild(newExpenseColA);

	var newExpenseType = document.createElement("select");
	newExpenseType.setAttribute("id", "ExpenseType"+rowCount.toString());
	newExpenseType.setAttribute("name", "ExpenseType"+rowCount.toString());
        addOptions(newExpenseType,exp_options);
	newExpenseColA.appendChild(newExpenseType);

	var newExpenseColB = document.createElement("td");
	newExpenseColB.setAttribute("class", "expenseInputCol");
	newExpenseRow.appendChild(newExpenseColB);

	var newExpenseGross = document.createElement("input");
	newExpenseGross.setAttribute("id", "gross"+rowCount.toString());
	newExpenseGross.setAttribute("name", "gross"+rowCount.toString());
	newExpenseGross.setAttribute("type", "number");
	newExpenseGross.setAttribute("step", "0.01");
	newExpenseGross.setAttribute("class", "expenseInputField");
	newExpenseGross.setAttribute("onchange","adjustTot('gross',rowCount)");
	newExpenseColB.appendChild(newExpenseGross);

	var newExpenseColC = document.createElement("td");
	newExpenseColC.setAttribute("class", "expenseInputCol");
	newExpenseRow.appendChild(newExpenseColC);

	var newExpenseNett = document.createElement("input");
	newExpenseNett.setAttribute("id", "nett"+rowCount.toString());
        newExpenseNett.setAttribute("name", "nett"+rowCount.toString());
	newExpenseNett.setAttribute("class", "expenseInputField");
	newExpenseNett.setAttribute("type", "number");
	newExpenseNett.setAttribute("step", "0.01");
        newExpenseNett.setAttribute("onchange","adjustTot('nett',rowCount)");
	newExpenseColC.appendChild(newExpenseNett);

	var newExpenseColD = document.createElement("td");
	newExpenseColD.setAttribute("class", "expenseInputCol");
	newExpenseRow.appendChild(newExpenseColD);

	var newExpenseVat = document.createElement("input");
	newExpenseVat.setAttribute("id", "vat"+rowCount.toString());
	newExpenseVat.setAttribute("name", "vat"+rowCount.toString());
	newExpenseVat.setAttribute("type", "number");
	newExpenseVat.setAttribute("step", "0.01");
	newExpenseVat.setAttribute("class", "expenseInputField");
        newExpenseVat.setAttribute("onchange","adjustTot('vat',rowCount)");
	newExpenseColD.appendChild(newExpenseVat);

	var newExpenseColE = document.createElement("td");
	newExpenseColE.setAttribute("class", "expenseInputCol");
	newExpenseRow.appendChild(newExpenseColE);

        var newVatType = document.createElement("select");
        newVatType.setAttribute("name", "vatType"+rowCount.toString());
        addOptions(newVatType,vat_options);
        newExpenseColE.appendChild(newVatType);

        var newExpenseColF = document.createElement("td");
        newExpenseColE.setAttribute("class", "expenseInputCol");
        newExpenseRow.appendChild(newExpenseColF);

        var newExpenseColG = document.createElement("td");
        newExpenseColE.setAttribute("class", "expenseInputColLast");
        newExpenseRow.appendChild(newExpenseColG);

	var newExpenseRem = document.createElement("input");
        newExpenseRem.setAttribute("id", "expenseBut"+rowCount.toString());
	newExpenseRem.setAttribute("name", "expenseBut"+rowCount.toString());
	newExpenseRem.setAttribute("type", "button");
	newExpenseRem.setAttribute("value", "-");
	newExpenseRem.setAttribute("onclick", "removeExpenseRow(this.id)");
	newExpenseColG.appendChild(newExpenseRem);

	//increment rowCount
	rowCount+=1;
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

//misschien de totalen een input veld maken, waar de inhoud van veranderd
// zo kan er ook een mogelijkheid zijn om btw maar 1x in te vullen, bij factuur met materialen+uren
// de rij blijft bestaan maar heeft geen zichtbare inhoud meer,
// misschien aanpassen dat ook echt alle children worden verwijderd
// nu volstaat selecteren op .innerHTML='x'
//iets van $phpding=function() die ook teruggeeft het aantal rijen
