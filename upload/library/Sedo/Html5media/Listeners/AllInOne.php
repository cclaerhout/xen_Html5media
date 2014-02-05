<?php

class Sedo_Html5media_Listeners_AllInOne
{
	public static function mceConfig($mceConfigObj)
	{
		if($mceConfigObj->hasMenu('adv_insert'))
		{
			$mceConfigObj->addMenuItem('bbm_sedo_adv_av', 'adv_insert', '@adv_insert_1');
		}
		else
		{
			$mceConfigObj->addMenuItem('bbm_sedo_adv_av', 'insert', '@insert_2');
		}
	}
}