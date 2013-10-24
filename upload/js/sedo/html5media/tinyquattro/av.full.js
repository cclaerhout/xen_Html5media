xenMCE.Templates.Bbm_multimedia = {
	onafterload: function($ovl, data, ed, parentClass)
	{
		/*Attach*/
		var $attachFiles = $ovl.find('.quattro_h5l_attach_file'), 
			$attachImg = $ovl.find('.quattro_h5l_attach_img'),
			$tabs = $ovl.find('.mceTabs > div');
			
		function toggleAttach(src)
		{
			var $src = $(src), id = $src.attr('id');

			if(id == 'xentabs_h5l_layout'){
				$attachFiles.hide();
				$attachImg.hide();
			}else if(id == 'xentabs_h5l_background'){
				$attachFiles.hide();
				$attachImg.show();			
			}else{
				$attachImg.hide();
				$attachFiles.show();
			}
		}

		function insertId(src)
		{
			var id = $(src).data('attachid');
	
			if(!id){
				return false;
			}
			
			var $currentPane = $ovl.find('.mce-pane:visible'),
				$idEl = $currentPane.find('.targetId');
			$idEl.val(id);
		}

		$tabs.click(function(){
			toggleAttach(this);
		});
		
		$attachFiles.find('span').click(function(){
			if($(this).hasClass('selected')){
				$(this).removeClass('selected');
				return false;
			}else{
				$(this).addClass('selected');			
				insertId(this);
			}
		});
		
		$attachImg.find('img').click(function(){
			insertId(this);
		});
	},
	submit: function(e, $ovl, ed, parentClass)
	{
		var tag = parentClass.bbm_tag,
			separator = parentClass.bbm_separator,
			data = e.data;

		var src = parentClass.escapeHtml(data.src),
			fallback = (!data.fallback) ? false : parentClass.escapeHtml(data.fallback),
			background = (!data.background) ? false : parentClass.escapeHtml(data.background),
			width = (!data.width) ? false : data.width,
			height = (!data.height) ? false : data.height,
			caption = (!caption) ? false : parentClass.escapeHtml(data.caption),
			captionAlign = (data.captionAlign == 'left') ? false : data.captionAlign,
			blockAlign = (data.blockAlign == 'bleft') ? false : data.blockAlign;


		var 	content = (fallback) ? src +'|'+ fallback : src,
			size = (!width || !height) ? false : width +'x'+height,
			hasfallback = (!fallback) ? false : 'fallback';
			
			

		//Bake options
		var options = '';
			
		if(hasfallback !== false){ bakeOptions(hasfallback); }
		if(blockAlign !== false){ bakeOptions(blockAlign); }
		if(size !== false){ bakeOptions(size); }
		if(caption !== false){ bakeOptions(width); }
		if(captionAlign !== false){ bakeOptions(captionAlign); }
		if(background !== false){ bakeOptions(background); }

		if(caption !== false){ bakeOptions(caption); }
				
		function bakeOptions(option){
			if (!options)
				options = option;
			else
				options = options + separator + option;			
		}

		//Bake ouput & insert it in editor !
		parentClass.insertBbCode(tag, options, content);
	}
}