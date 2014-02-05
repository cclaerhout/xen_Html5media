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
		$preload =  $xenoptions->sedo_bbcode_av_video_preload;
		$preloadBb =  $xenoptions->sedo_bbcode_av_video_preloadbb;
		
		/*Misc*/
		$regex_url = BBM_Helper_BbCodes::$regexUrl;

		/*Init variables*/
		$width = '';
		$height = '';
		$css = '';
		$css_caption = 'left';	
		$type = '';
		$extension = '';
		$hasFallback = false;
		$hasPoster = false;
		$hasCaption = false;
		$posterType = false;
		$displayNoViewPerms = false;
		
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
			elseif(in_array($option, array('nocache', 'metadata', 'cache')) )
			{
				if($preloadBb)
				{
					if($option == 'nocache')
					{
						$preload = 'none';
					}
					elseif($option == 'cache')
					{
						$preload = 'auto';		
					}
					else
					{
						$preload = 'metadata';
					}
				}
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

			$media_fallback = false;
			
			if(isset($wip[1]))
			{
				//Fallback exists
				if(preg_match($regex_url, $wip[1]))
				{
					//The fallback is an url
					$media_fallback = self::_miniUrlEncode($wip[1]);
					$fallback_ext = XenForo_Helper_File::getFileExtension($media_fallback);
					$media_fallback = (in_array($fallback_ext, $all_extensions)) ? $media_fallback : false;
					
				}
				elseif(preg_match('#\d+#', $wip[1]))
				{
					//The fallback is an attachment id
					$attachmentParams = $parentClass->getAttachmentParams($wip[1], $all_extensions, null);

					if($attachmentParams['canView'] || $attachmentParams['validAttachment'])
					{
						$media_fallback = $attachmentParams['url'];
					}
					else
					{
						$media_fallback = false;
					}
				}
			}
			
			$content = ($media_fallback) ? $wip[0] : $content;
		}

		/*Main detection*/
		if(preg_match($regex_url, $content))
		{
			//The main content is an url
			$content = self::_miniUrlEncode($content);
			$extension = XenForo_Helper_File::getFileExtension($content);
		}
		elseif(preg_match('#\d+#', $content))
		{
			//The main content is an attachment id
			$attachmentParams = $parentClass->getAttachmentParams($content, $all_extensions, null);

			if($attachmentParams['canView'] || $attachmentParams['validAttachment'])
			{
				$content = self::_miniUrlEncode($attachmentParams['url']);
				$extension = $attachmentParams['attachment']['extension'];
			}

			if(!$attachmentParams['canView'])
			{
				$displayNoViewPerms = true;
			}
		}

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
				$attachmentParams = $parentClass->getAttachmentParams($hasPoster);

				if($attachmentParams['canView'] || $attachmentParams['validAttachment'])
				{
					$hasPoster = $attachmentParams['url'];
				}
				else
				{
					$hasPoster = false;
				}
			}
		}
		else
		{
			$hasPoster = false;
		}

		$useResponsiveMode = BBM_Helper_BbCodes::useResponsiveMode();

		if($useResponsiveMode)
		{
			$css = 'responsive';
		}

		/*Final options*/
		$options['isValid'] = (in_array($extension, $all_extensions)) ? true : false;
		$options['mediaType'] = $type;
		$options['fallback'] = $media_fallback;
		$options['poster'] = $hasPoster;	
		$options['width'] = $width;
		$options['height'] = $height;
		$options['css'] = $css;
		$options['caption'] = $hasCaption;
		$options['cssCaption'] = 'cap_'.$css_caption;
		$options['displayNoViewPerms'] = $displayNoViewPerms;
		$options['responsiveMode'] = $useResponsiveMode;
		$options['preload'] = $preload;
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