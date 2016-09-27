var PortfolioPdfExportHelper  = (function () {
	'use strict';
	var pub = {},
	    pro = {
		    fake_player         : '<div class="ilFakePlayer"></div>',
		    fake_player_text    : '<div class="ilFakePlayerText"></div>',
		    fake_player_play    : '<div class="ilFakePlayerPlay"></div>'
	    };

	pub.replaceVideoWithText = function()
	{
		$( '.ilPageVideo' ).each(function() {
			var org_heigth      = $(this).attr('height');
			var org_width       = $(this).attr('width');
			var filename        = $(this).find('source').attr('src');
			var inject_text     = $(pro.fake_player_text).html(filename);
			var element         = $(pro.fake_player).css({'height' : org_heigth, 'width': org_width})
			var inject          = element.html(inject_text).append(pro.fake_player_play);
			$(this).closest('div').html(inject);
		});
	};

	pub.protect = pro;
	return pub;
}());

$( window ).load(function() {
	PortfolioPdfExportHelper.replaceVideoWithText();
});
