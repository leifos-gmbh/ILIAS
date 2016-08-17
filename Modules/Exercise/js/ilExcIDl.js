/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

il.ExcIDl = {
	ajax_url: '',
	
	init: function (url) {
		this.ajax_url = url;
		il.ExcIDl.initModal();
	},
	
	trigger: function(id) {
		il.Util.sendAjaxGetRequestToUrl(
			il.ExcIDl.ajax_url,
			{idlid: id},
			{},
			il.ExcIDl.showModal
		);
		return false;
	},
	
	initModal: function() {						
		// add form action
		$('form[name="ilExcIDlForm"]').submit(function() {			
			var submit_btn = $(document.activeElement).attr("name");
			if(submit_btn)
			{
				var values = {};
				var cmd = null;
				var ids = [];
				$.each($(this).serializeArray(), function(i, field) {
					if(submit_btn == "select_cmd2" && field.name == "selected_cmd2")
					{
						cmd = field.value;
					}
					else if(submit_btn == "select_cmd" && field.name == "selected_cmd")
					{
						cmd = field.value;
					}					
					// extract user/team ids
					if(field.name.substr(0, 6) == "member")
					{
						ids.push(field.name.substr(7, field.name.length-8));
					}
				});	
				if(cmd == "setIndividualDeadline" && ids.length)
				{
					// :TODO: handle preventDoubleSubmission?
					
					il.Util.sendAjaxGetRequestToUrl(
						il.ExcIDl.ajax_url,
						{idlid: ids.join()},
						{},
						il.ExcIDl.showModal
					);
					return false;
				}
			}
		});		
		// modal clean-up on close
		$('#ilExcIDl').on('hidden.bs.modal', function(e) {
			$("#ilExcIDlBody").html("");			
		});				
	},		
	
	showModal: function(o) {	
		if(o.responseText !== undefined)
		{			
			$("#ilExcIDlBody").html(o.responseText);
			
			il.ExcIDl.parseForm();
			
			$("#ilExcIDl").modal('show');			
		}
	},
	
	parseForm: function() {			
		$('form[name="ilExcIDlForm"]').submit(function() {		
			$.ajax({
				type: "POST",
				url: il.ExcIDl.ajax_url,
				data: $(this).serializeArray(),
				success: il.ExcIDl.handleForm
			  });
			return false;
		});		
	},
	
	handleForm: function(responseText) {		
		if(responseText !== undefined)
		{
			if(responseText != "ok")
			{
				$("#ilExcIDlBody").html(responseText);				
				il.ExcIDl.parseForm();
			}
			else
			{
				window.location.replace(il.ExcIDl.ajax_url + "&dn=1");
			}
		}
	}	
};