cdUI = {
	max_na_sel: 3,
	na_text: "max %s elements",
	
	setNaText: function (str) {
console.log("done");
		cdUI.na_text = str;
	},
	
	init: function () {
console.log("init");
		$('input[id^="talk_understand"]').click(function (e) {
			cdUI.checkBoxes(e, 'talk_understand');
			});
		$('input[id^="write_read"]').click(function (e) {
			cdUI.checkBoxes(e, 'write_read');
			});
	},
	
	checkBoxes: function (e, type) {
		if ($("#" + e.target.id).prop('checked') == true) {
			var cb = $('input[id^="' + type + '"]:checked');
			if (cb.length > cdUI.max_na_sel) {
				$("#" + e.target.id).attr("checked", false);
				alert(cdUI.na_text.replace("%s", "" + cdUI.max_na_sel));
			}
		}
	}
}
il.Util.addOnLoad(function () {
	cdUI.init();
});
