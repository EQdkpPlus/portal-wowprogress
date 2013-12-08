<?php
 /*
 * Project:		EQdkp-Plus
 * License:		Creative Commons - Attribution-Noncommercial-Share Alike 3.0 Unported
 * Link:		http://creativecommons.org/licenses/by-nc-sa/3.0/
 * -----------------------------------------------------------------------
 * Began:		2008
 * Date:		$Date: 2012-12-17 13:13:57 +0100 (Mo, 17. Dez 2012) $
 * -----------------------------------------------------------------------
 * @author		$Author: godmod $
 * @copyright	2006-2011 EQdkp-Plus Developer Team
 * @link		http://eqdkp-plus.com
 * @package		eqdkp-plus
 * @version		$Rev: 12604 $
 * 
 * $Id: wowprogress_portal.class.php 12604 2012-12-17 12:13:57Z godmod $
 */

if ( !defined('EQDKP_INC') ){
	header('HTTP/1.0 404 Not Found');exit;
}

class wowprogress_portal extends portal_generic {
	
	public static $shortcuts = array('puf'	=> 'urlfetcher', 'in');
	protected static $path		= 'wowprogress';
	protected static $data		= array(
		'name'			=> 'wowprogress',
		'version'		=> '0.2.0',
		'author'		=> 'GodMod',
		'contact'		=> EQDKP_PROJECT_URL,
		'description'	=> 'Shows the WoW Guildprogress',
		'lang_prefix'	=> 'wowprogress_'
	);
	
	private $tiers = array(
		'tier8', 'tier9_10', 'tier9_25', 'tier10_10', 'tier10_25', 'tier11', 'tier11_10', 'tier11_25','tier12','tier12_10','tier12_25', 'tier13', 'tier13_10','tier13_25','tier14','tier14_10','tier14_25','tier15', 'tier15_10', 'tier15_25','tier16', 'tier16_10', 'tier16_25',
	);

	public function get_settings($state){
		$arrTiers;
		//Build Tear Multiselect
		foreach($this->tiers as $strTier){
			$strNumbers = str_replace("tier", "", $strTier);
			$arrNumbers = explode("_", $strNumbers);

			$arrTiers[$strTier] = $this->user->lang('wp_tier').' '.$arrNumbers[0].((isset($arrNumbers[1])) ? ' ('.$arrNumbers[1].')' : '');
		}
		
		$settings	= array(
			'encounter' => array(
				'type'		=> 'jq_multiselect',
				'options'	=> $arrTiers,
			),
		);
		return $settings;
	}
	
	public function output() {
		if ($this->game->get_game() != "wow") return $this->user->lang('wp_wow_only');
		
		$strOut = $this->pdc->get('portal.modul.wowprogress',false,true);
		
		if($strOut === NULL){
			$arrEncounter = unserialize($this->config('encounter'));
			$strBaseURL = $this->buildURL();

			$strOut = '<table width="100%" class="colorswitch">';
			foreach($arrEncounter as $strKey){
				$strURL = $strBaseURL .'rating.'.$strKey.'/json_rank';
				$strResult = $this->puf->fetch($strURL);
				$arrResult = json_decode($strResult, true);
				if ($arrResult != NULL){
					$strNumbers = str_replace("tier", "", $strKey);
					$arrNumbers = explode("_", $strNumbers);

					$strOut.='<tr>';
					$strOut.='<th colspan="2">'.$this->user->lang('wp_ranking').' '.$this->user->lang('wp_tier').' '.$arrNumbers[0];
					if(isset($arrNumbers[1])) $strOut .= ' - '.$arrNumbers[1].' '.$this->user->lang('wp_man');
					$strOut.='</th>';
					$strOut.='<tr><td>'.$this->user->lang('wp_world').'</td><td>'.$arrResult["world_rank"].'</td></tr>';
					$strOut.='<tr><td>'.strtoupper($this->config->get('uc_server_loc')).'-'.$this->user->lang('wp_rank').'</td><td>'.$arrResult["area_rank"].'</td></tr>';
					$strOut.='<tr><td>'.$this->user->lang('wp_realm').'</td><td>'.$arrResult["realm_rank"].'</td></tr>';
					$strOut.='</tr>';
				}
			}
			$strOut .= '</table>';
			$this->pdc->put('portal.modul.wowprogress',$strOut,3600,false,true);
		}
		return $strOut;
	}
	
	private function buildURL(){
		$url	= "http://www.wowprogress.com/";
		$search	= array('+',"'"," ");
		$server	= urlencode(strtolower(str_replace($search, '-',$this->config->get('uc_servername'))));
		$guild	= str_replace($search, '+', urlencode(utf8_encode(strtolower($this->config->get('guildtag')))));
		$url	.= "guild/" . $this->config->get('uc_server_loc') . "/" . $server  . "/" . $guild . "/";
		return $url;
	}

}
?>