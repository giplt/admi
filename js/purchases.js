
function addExpenseRow(options) {
	var rowCount = 0;
	var expenseTable = document.getElementById("expenseTable");
	var newExpenseRow = document.createElement("tr");
	newExpenseRow.setAttribute("id", "ExpenseType"+rowCount.toString());
	expenseTable.appendChild(newExpenseRow);

	var newExpenseColA = document.createElement("td");
	newExpenseRow.appendChild(newExpenseColA);

	var newExpenseType = document.createElement("select");
	newExpenseType.setAttribute("name", "ExpenseID"+rowCount.toString());
	newExpenseType.innerHTML = options;
	newExpenseColA.appendChild(newExpenseType);

	var newExpenseColB = document.createElement("td");
	newExpenseRow.appendChild(newExpenseColB);

	var newExpenseGross = document.createElement("input");
	newExpenseType.setAttribute("name", "gross"+rowCount.toString());
	newExpenseColB.appendChild(newExpenseGross);

	var newExpenseColC = document.createElement("td");
	newExpenseRow.appendChild(newExpenseColC);

	var newExpenseNett = document.createElement("input");
	newExpenseType.setAttribute("name", "nett"+rowCount.toString());
	newExpenseColC.appendChild(newExpenseNett);

	var newExpenseColD = document.createElement("td");
	newExpenseRow.appendChild(newExpenseColD);

	var newExpenseVat = document.createElement("input");
	newExpenseType.setAttribute("name", "vat"+rowCount.toString());
	newExpenseColD.appendChild(newExpenseVat);

	var newExpenseColE = document.createElement("td");
	newExpenseRow.appendChild(newExpenseColE);

	var newExpenseVat = document.createElement("input");
	newExpenseType.setAttribute("type", "button");
	newExpenseType.setAttribute("value", "-");
	//newExpenseType.setAttribute("onclick", removeExpenseRow());
	newExpenseColE.appendChild(newExpenseVat);

}

//function removeExpenseRow

iets van $phpding=function() die ook teruggeeft het aantal rijen
