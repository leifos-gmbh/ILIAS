var rolCourseTime = {
	min_onlinetime: 0,
	counter: 0,

	init: function (min_onlinetime, counter) {
		var t = rolCourseTime;
		t.min_onlinetime = min_onlinetime * 60;
		t.counter = counter;
		setInterval("rolCourseTime.count();", 1000);
	},

	count: function () {
		var t = rolCourseTime;
		if (t.counter === 0) {
			return;
		};
		t.counter++;
		if(t.counter < t.min_onlinetime) {
			document.getElementById("onlinetime").innerHTML="<font color='red'>" + t.secondsToTime(t.counter) + "</font>";
		}
		else{
			document.getElementById("onlinetime").innerHTML="<font color='green'>" + t.secondsToTime(t.counter) + "</font>";
		}
	},

	secondsToTime: function (secs) {
		var hours = Math.floor(secs / (60 * 60));
		if(hours < 10) {
			hours = "0" + hours;
		}
		var divisor_for_minutes = secs % (60 * 60);
		var minutes = Math.floor(divisor_for_minutes / 60);
		if (minutes < 10) {
			minutes = "0" + minutes;
		}
		var divisor_for_seconds = divisor_for_minutes % 60;
		var seconds = Math.ceil(divisor_for_seconds);
		if (seconds < 10) {
			seconds = "0" + seconds;
		}

		return hours + ":" + minutes + ":" + seconds;
	}
};