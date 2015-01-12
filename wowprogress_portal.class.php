<?php
/*	Project:	EQdkp-Plus
 *	Package:	WoWprogress Portal Module
 *	Link:		http://eqdkp-plus.eu
 *
 *	Copyright (C) 2006-2015 EQdkp-Plus Developer Team
 *
 *	This program is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU Affero General Public License as published
 *	by the Free Software Foundation, either version 3 of the License, or
 *	(at your option) any later version.
 *
 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU Affero General Public License for more details.
 *
 *	You should have received a copy of the GNU Affero General Public License
 *	along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

if ( !defined('EQDKP_INC') ){
	header('HTTP/1.0 404 Not Found');exit;
}

class wowprogress_portal extends portal_generic {
	
	public static $shortcuts = array('puf'	=> 'urlfetcher');
	protected static $path		= 'wowprogress';
	protected static $data		= array(
		'name'			=> 'wowprogress',
		'version'		=> '0.3.0',
		'author'		=> 'GodMod',
		'contact'		=> EQDKP_PROJECT_URL,
		'description'	=> 'Shows the WoW Guildprogress',
		'lang_prefix'	=> 'wowprogress_',
		'icon'			=> 'fa-bar-chart-o',
	);
	
	private $tiers = array(
		'tier8', 'tier9_10', 'tier9_25', 'tier10_10', 'tier10_25', 'tier11', 'tier11_10', 'tier11_25','tier12','tier12_10','tier12_25', 'tier13', 'tier13_10','tier13_25','tier14','tier14_10','tier14_25','tier15', 'tier15_10', 'tier15_25','tier16', 'tier16_10', 'tier16_25',
	);
	
	protected static $apiLevel = 20;

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
				'type'		=> 'multiselect',
				'options'	=> $arrTiers,
			),
		);
		return $settings;
	}
	
	public function output() {
		if ($this->game->get_game() != "wow") return $this->user->lang('wp_wow_only');
		
		$strOut = $this->pdc->get('portal.modul.wowprogress',false,true);
		
		if($strOut === NULL){
			$arrEncounter = $this->config('encounter');
			$strBaseURL = $this->buildURL();

			$strOut = '<table class="table fullwidth colorswitch">';
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
		$server	= urlencode(strtolower(str_replace($search, '-',$this->config->get('servername'))));
		$guild	= str_replace($search, '+', urlencode(utf8_encode(strtolower($this->config->get('guildtag')))));
		$url	.= "guild/" . $this->config->get('uc_server_loc') . "/" . $server  . "/" . $guild . "/";
		return $url;
	}

}
?>
