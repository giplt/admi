var selected = JSON.parse('[]');

function select(element, amount) {
	var elementID = element.getAttribute('id');
	if (selected[elementID]) {
		document.getElementById(elementID).style.background = '#efefef';
		delete selected[elementID];
	}
	else {
		document.getElementById(elementID).style.background = '#8bf';
		selected[elementID] = amount;
	}
	var sum = 0;
	var count = 0;
	for (var key in selected) {
		if(key[0] == 'l') sum+= selected[key];
		else sum-= selected[key];
		count++;
	}
	document.getElementById('balance').innerHTML = '€' + sum.toFixed(2);
	document.getElementById('btnMerge').disabled = (sum==0 && count>0) ? false : true;
}

function merge() {
	var IDs = '';
	for (var key in selected) {
		if (IDs!='') IDs+= ',';
		IDs+= key.substring(1);
	}
	const form = document.createElement('form');
	form.method = 'post';
	form.action = window.location.href;
	
	const hiddenField = document.createElement('input');
	hiddenField.type = 'hidden';
	hiddenField.name = 'TransactionIDs';
	hiddenField.value = IDs;
	form.appendChild(hiddenField);

	document.body.appendChild(form);
	form.submit();
}
