function toggleInfoPopup(infoButton, msg) {
	var helpPopup = document.createElement('DIV');
	helpPopup.innerText = msg;
	helpPopup.className = 'helppopup';
	helpPopup.style.left = infoButton.getBoundingClientRect().left + 'px';
	helpPopup.style.top = infoButton.getBoundingClientRect().top + 'px';
	
	var closeButton = document.createElement('BUTTON');
	closeButton.innerText = 'X';
	closeButton.onclick = function() {
		helpPopup.remove();
	}
	closeButton.className = 'closebutton';
	helpPopup.appendChild(closeButton);
	
	document.body.appendChild(helpPopup);
}
/*
function positionTextBox(clickEvent, $textBox, text) {
	var x = clickEvent.clientX; // Get the horizontal coordinate
	var y = clickEvent.clientY; // Get the vertical coordinate
	// determine if window is scrolled
	if (window.pageXOffset !== undefined) {
		// All browsers, except IE9 and earlier
		x += window.pageXOffset;
		y += window.pageYOffset;
	} else { // IE9 and earlier
		x += document.documentElement.scrollLeft;
		y += document.documentElement.scrollTop;
	}
	// get viewport
	var sx = document.documentElement.clientWidth;
	var sy = document.documentElement.clientHeight;
	var windowCenter = sx / 2;

	$textBox.css('fontSize', '12px');
	$textBox.css('fontStyle', 'normal');
	$textBox.css('padding', '10px');
	$textBox.css('position', 'absolute');
	$textBox.html(text);
	var box = {
		width: $textBox.innerWidth(),
		height: $textBox.innerHeight(),
	};

	$textBox.css('width', box.width + 'px');
	$textBox.css('height', box.height + 'px');
	$textBox.addClass('notelo');

	if ((y < box.height) && (x < box.width)) {
		$textBox.css('left', x + box.width / 3 + 'px');
		$textBox.css('top', y + 15 + 'px');
	} else if ((y < box.height) && ((box.width < x) && (x < sx - box.width))) {
		$textBox.css('left', x + 'px');
		$textBox.css('top', y + 15 + 'px');
		$textBox.addClass('up');
	} else if ((y < box.height) && (x > sx - box.width)) {
		$textBox.css('left', x - box.width + 'px');
		$textBox.css('top', y + 15 + 'px');
	} else if (((y >= box.height) && (y < sy - box.height)) && (x < windowCenter)) {
		$textBox.css('left', x - 40 + box.width / 2 + 'px');
		$textBox.css('top', y - 10 - box.height / 2 + 'px');
		$textBox.addClass('left');
	} else if (((y >= box.height) && (y < sy - box.height)) && (x > windowCenter)) {
		$textBox.css('left', x - box.width + 'px');
		$textBox.css('top', y - 10 - box.height / 2 + 'px');
		$textBox.addClass('right');
	} else if ((y >= box.height) && (x < box.width)) {
		$textBox.css('left', x + box.width / 3 + 'px');
		$textBox.css('top', y - 10 - box.height + 'px');
	} else if ((y >= box.height) && ((box.width < x) && (x < sx - box.width))) {
		$textBox.css('left', x - box.width / 3 + 'px');
		$textBox.css('top', y - box.height - 40 + 'px');
		$textBox.addClass('down');
	} else if ((y >= box.height) && (x > sx - box.width)) {
		$textBox.css('left', x - box.width + 'px');
		$textBox.css('top', y - box.height - 15 + 'px');
	} else alert('Could not determine the area');
}
*/
