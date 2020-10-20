function filter(checkbox) {
	var urlParams = new URLSearchParams(window.location.search);
	var selected = urlParams.has('filter') ? urlParams.get('filter').split(',') : [];
	var id = checkbox.name.substr(5);
	if (checkbox.checked) selected.push(id);
	else selected.splice(selected.indexOf(id), 1);
	for (i=0; i<selected.length;i++) if (selected[i]=='') selected.splice(i, 1);
	selected.sort();
	if (selected.length) urlParams.set('filter', selected.join(','));
	else urlParams.delete('filter');
	window.location = '?' + urlParams.toString();
}

function sort(sortorder) {
	var urlParams = new URLSearchParams(window.location.search);
	urlParams.set('sort', sortorder);
	window.location = '?' + urlParams.toString();
}

function validateBankImportButton() {
	var inputs = document.getElementsByTagName('input');
	var hasBank = false;
	var hasFile = false;
	for (var i=0; i<inputs.length; i++) if (inputs[i].type === 'radio' && inputs[i].name=='bankID' && inputs[i].checked && inputs[i].value!='14') hasBank = true;
	if (document.getElementById('importFile').value) hasFile = true;
	document.getElementById('importButton').disabled = !(hasBank && hasFile);
}

function upload() {
	var xhr = new XMLHttpRequest();
	xhr.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			document.getElementById('csvView').innerHTML = this.responseText;
		}
	};
	var data = new FormData();
	var inputs = document.getElementsByTagName('input');
	for (var i=0; i<inputs.length; i++) if (inputs[i].type === 'radio' && inputs[i].name=='bankID' && inputs[i].checked) data.append('bankID', inputs[i].value);
	data.append('bankCSV', document.getElementById('importFile').files[0]);
	xhr.open('POST', '');
	xhr.send(data);
}
