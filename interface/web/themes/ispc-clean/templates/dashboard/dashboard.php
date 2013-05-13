<?php
/*
Copyright (c) 2010 Till Brehm, projektfarm Gmbh and Oliver Vogel www.muv.com
All rights reserved.

Redistribution and use in source and binary forms, with or without modification,
are permitted provided that the following conditions are met:

    * Redistributions of source code must retain the above copyright notice,
      this list of conditions and the following disclaimer.
    * Redistributions in binary form must reproduce the above copyright notice,
      this list of conditions and the following disclaimer in the documentation
      and/or other materials provided with the distribution.
    * Neither the name of ISPConfig nor the names of its contributors
      may be used to endorse or promote products derived from this software without
      specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY
OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE,
EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/

require_once('../../lib/config.inc.php');
require_once('../../lib/app.inc.php');

//* Check permissions for module
$app->auth->check_module_permissions('dashboard');

//* Loading Template
$app->uses('tpl');
$app->tpl->newTemplate("templates/dashboard.htm");

//* load language file
$lng_file = 'lib/lang/'.$_SESSION['s']['language'].'.lng';
include($lng_file);
$app->tpl->setVar($wb);

//* set Default - Values
$app->tpl_defaults();

/*
 * Let the user welcome
*/
if($_SESSION['s']['user']['typ'] == 'admin') {
	$name = $_SESSION['s']['user']['username'];
} else {
	$tmp = $app->db->queryOneRecord("SELECT contact_name FROM client WHERE username = '".$app->db->quote($_SESSION['s']['user']['username'])."'");
	$name = $tmp['contact_name'];
}

$welcome = sprintf($wb['welcome_user_txt'], htmlentities($name, ENT_QUOTES, 'UTF-8'));
$app->tpl->setVar('welcome_user', $welcome);


/*
 * ToDo: Display errors, warnings and hints
*/
///*
// * If there is any error to display, do it...
//*/
//$error = array();
//
//$error[] = array('error_msg' => 'EClaus1');
//$error[] = array('error_msg' => 'EEClaus2');
//$error[] = array('error_msg' => 'EClaus3');
//$error[] = array('error_msg' => 'EClaus4');
//
//$app->tpl->setloop('error', $error);
//
///*
// * If there is any warning to display, do it...
//*/
//$warning = array();
//
//$warning[] = array('warning_msg' => 'WClaus1');
//$warning[] = array('warning_msg' => 'WWClaus2');
//$warning[] = array('warning_msg' => 'WClaus3');
//$warning[] = array('warning_msg' => 'WClaus4');
//
//$app->tpl->setloop('warning', $warning);
//


/*
 * If there is any information to display, do it...
*/
$info = array();

if(isset($_SESSION['show_info_msg'])) {
    $info[] = array('info_msg' => '<p>'.$_SESSION['show_info_msg'].'</p>');
    unset($_SESSION['show_info_msg']);
}
if(isset($_SESSION['show_error_msg'])) {
    $app->tpl->setloop('error', array(array('error_msg' => '<p>'.$_SESSION['show_error_msg'].'</p>')));
    unset($_SESSION['show_error_msg']);
}


/*
 * Check the ISPConfig-Version (only for the admin)
*/
if($_SESSION["s"]["user"]["typ"] == 'admin') {
	if(!isset($_SESSION['s']['new_ispconfig_version'])) {
		$new_version = @file_get_contents('http://www.ispconfig.org/downloads/ispconfig3_version.txt');
		$_SESSION['s']['new_ispconfig_version'] = trim($new_version);
	}
	$v1 = ISPC_APP_VERSION;
	$v2 = $_SESSION['s']['new_ispconfig_version'];
	$this_version = explode(".",$v1);
	/*
	$this_fullversion = (($this_version[0] < 10) ? '0'.$this_version[0] : $this_version[0]) .
			    ((isset($this_version[1]) && $this_version[1] < 10) ? '0'.$this_version[1] : $this_version[1]) .
			    ((isset($this_version[2]) && $this_version[2] < 10) ? '0'.$this_version[2] : $this_version[2]) .
			    ((isset($this_version[3]) && $this_version[3] < 10) ? (($this_version[3] < 1) ? '00' : '0'.$this_version[3]) : @$this_version[3]);

	*/
	
	$new_version = explode(".",$v2);
	/*
	$new_fullversion =  (($new_version[0] < 10) ? '0'.$new_version[0] : $new_version[0]) .
			    ((isset($new_version[1]) && $new_version[1] < 10) ? '0'.$new_version[1] : $new_version[1]) .
			    ((isset($new_version[2]) && $new_version[2] < 10) ? '0'.$new_version[2] : $new_version[2]) .
			    ((isset($new_version[3]) && $new_version[3] < 10) ? (($new_version[3] < 1) ? '00' : '0'.$new_version[3]) : @$new_version[3]);
	*/
	
	$this_fullversion = str_pad($this_version[0], 2,'0',STR_PAD_LEFT).str_pad($this_version[1], 2,'0',STR_PAD_LEFT).@str_pad($this_version[2], 2,'0',STR_PAD_LEFT).@str_pad($this_version[3], 2,'0',STR_PAD_LEFT);
	$new_fullversion = str_pad($new_version[0], 2,'0',STR_PAD_LEFT).str_pad($new_version[1], 2,'0',STR_PAD_LEFT).@str_pad($new_version[2], 2,'0',STR_PAD_LEFT).@str_pad($new_version[3], 2,'0',STR_PAD_LEFT);
	if($new_fullversion > $this_fullversion) {
		$info[] = array('info_msg' => '<p>There is a new Version of ISPConfig 3 available!</p>' . 
			'<p>This Version: <b>' . $v1 . '</b></p>' . 
			'<p>New Version : <b>' . $v2 .  '</b></p>' .
			'<p><a href="http://www.ispconfig.org/ispconfig-3/download" target="ISPC">See more...</a></p>');
	}
}

$app->tpl->setloop('info', $info);

/* Load the dashlets*/
$dashlet_list = array();
$handle = @opendir(ISPC_WEB_PATH.'/dashboard/dashlets'); 
while ($file = @readdir ($handle)) { 
    if ($file != '.' && $file != '..' && !is_dir($file)) {
        $dashlet_name = substr($file,0,-4);
		$dashlet_class = 'dashlet_'.$dashlet_name;
		include_once(ISPC_WEB_PATH.'/dashboard/dashlets/'.$file);
		$dashlet_list[$dashlet_name] = new $dashlet_class;
	}
}


/* Which dashlets in which column */
/******************************************************************************/
$leftcol_dashlets = array('modules','invoices');
$rightcol_dashlets = array('limits');
/******************************************************************************/


/* Fill the left column */
$leftcol = array();
foreach($leftcol_dashlets as $name) {
	if(isset($dashlet_list[$name])) {
		$leftcol[]['content'] = $dashlet_list[$name]->show();
	}
}
$app->tpl->setloop('leftcol', $leftcol);

/* Fill the right columnn */
$rightcol = array();
foreach($rightcol_dashlets as $name) {
	if(isset($dashlet_list[$name])) {
		$rightcol[]['content'] = $dashlet_list[$name]->show();
	}
}
$app->tpl->setloop('rightcol', $rightcol);


//* Do Output
$app->tpl->pparse();

?>