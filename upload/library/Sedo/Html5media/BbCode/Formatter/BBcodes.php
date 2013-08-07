<?php

class Sedo_Html5media_BbCode_Formatter_BBcodes
{
	public static function html5media(&$content, array &$options, &$templateName, &$fallBack, array $rendererStates, $parentClass)
	{
		/*XenOptions*/
		$xenoptions = XenForo_Application::get('options');
		$audio_extensions = array_filter(explode(';', $xenoptions->sedo_bbcode_av_audio_ext));
		$video_extensions = array_filter(explode(';', $xenoptions->sedo_bbcode_av_video_ext));
		$all_extensions = array_merge($audio_extensions, $video_extensions);

		$width_default = $xenoptions->sedo_bbcode_av_default_width;
		$width_max = $xenoptions->sedo_bbcode_av_max_width;
		$height_default = $xenoptions->sedo_bbcode_av_default_height;
		$height_max = $xenoptions->sedo_bbcode_av_max_height;
		
		/*Misc*/
		$regex_url = '#^(?:(?:https?|ftp|file)://|www\.|ftp\.)[-\p{L}0-9+&@\#/%=~_|$?!:,.]*[-\p{L}0-9+&@\#/%=~_|$]$#ui';

		/*Init variables*/
		$width = '';
		$height = '';
		$css = '';
		$css_caption = '';	
		$type = '';
		$hasFallback = false;
		$hasPoster = false;
		$hasCaption = false;
		$posterType = false;
		
		/*Options broswing*/
		$i = 0;
		foreach($options as $option)
		{
			if(in_array($option, array('video', 'audio')))
			{
				$type = $option;
			}
			elseif($option == 'fallback')
			{
				$hasFallback = true;
			}
			elseif (preg_match('#^(\d+?(px)?)x(\d+?)$#', $option, $matches))
			{
				$width = $matches[1];
				$height = $matches[3];				
			}
			elseif($option == 'left')
			{
				$css_caption = 'left';
			}
			elseif($option == 'center')
			{
				$css_caption = 'center';
			}
			elseif($option == 'right')
			{
				$css_caption = 'right';
			}
			elseif($option == 'bleft')
			{
				$css = 'bleft';
			}
			elseif($option == 'bcenter')
			{
				$css = 'bcenter';
			}
			elseif($option == 'bright')
			{
				$css = 'bright';
			}
			elseif($option == 'fleft')
			{
				$css = 'fleft';
			}
			elseif($option == 'fright')
			{
				$css = 'fright';
			}
			elseif(preg_match($regex_url, $option))
			{
				$hasPoster = $option;
				$posterType = 'url';
			}
			elseif(preg_match('#\d+#', $option))
			{
				$hasPoster = $option;
				$posterType = 'id';
			}
			else
			{
				$hasCaption = $option;
			}
			
			if($i > 50)
			{
				break;
			}
			$i++;
		}

		/*Safety width & height*/
		if(	(empty($width) || empty($height))
			OR 
			(!empty($width) && $width > $width_max)
			OR
			(!empty($height) && $height > $height_max)
		)
		{
			$width = $width_default;
			$height = $height_default;
		}

		/*Media fallback detection*/
		$media_fallback = false;
		
		if($hasFallback)
		{
			$wip = explode('|', $content);
			$media_fallback = ( isset($wip[1]) ) ? self::_miniUrlEncode($wip[1]) : false;
			$content = ($media_fallback) ? $wip[0] : $content;
			
			$fallback_ext = XenForo_Helper_File::getFileExtension($media_fallback);
			$media_fallback = (in_array($fallback_ext, $all_extensions)) ? $media_fallback : false;
		}

		/*Main detection*/
		$content = self::_miniUrlEncode($content);
		$extension = XenForo_Helper_File::getFileExtension($content);

		if(empty($type) && in_array($extension, $audio_extensions))
		{
			$type = 'audio';
		}
		elseif(empty($type) && in_array($extension, $video_extensions))
		{
			$type = 'video';		
		}


		/*Poster Detection*/
		if($hasPoster && $type == 'video')
		{
			if($posterType == 'url')
			{
				$posterExt = XenForo_Helper_File::getFileExtension($hasPoster);
				$hasPoster = ( !empty($posterExt) ) ?  $hasPoster : false;
			}
			else
			{
				$hasPoster = XenForo_Link::buildPublicLink('attachments', array('attachment_id' => $hasPoster));
			}
		}
		else
		{
			$hasPoster = false;
		}
		
		
		/*Final options*/
		$options['isValid'] = (in_array($extension, $all_extensions)) ? true : false;
		$options['mediaType'] = $type;
		$options['hasFallback'] = $media_fallback;
		$options['hasPoster'] = $hasPoster;	
		$options['width'] = $width;
		$options['height'] = $height;
		$options['css'] = $css;
		$options['hasCaption'] = $hasCaption;
		$options['cssCaption'] = 'cap_'.$css_caption;
	}
	
	protected static function _miniUrlEncode($url)
	{
		//rawurlencode => doesn't work with the script
		$search = array(
			0 => ' '
		);
		
		$replace = array(
			0 => '%20'
		);
		
		return str_replace($search, $replace, $url);
	}
}
//Zend_Debug::dump($contents);