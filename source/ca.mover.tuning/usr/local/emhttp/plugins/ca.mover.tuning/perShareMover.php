#!/usr/bin/php
<?PHP
/* Copyright 2005-2023, Lime Technology
 * Copyright 2012-2023, Bergware International.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 */
?>
<?
#---------------------------------------------------------------------------------------------------------------------
#This section was adapted from "Wrapper.php" and includes an adapted "parse_plugin_cfg()" function.
$docroot = $docroot ?? $_SERVER['DOCUMENT_ROOT'] ?: '/usr/local/emhttp';
function parse_share_cfg($plugin, $shareName, $sections = false, $scanner = INI_SCANNER_NORMAL)
{
    global $docroot;
    $ram = "$docroot/plugins/$plugin/default.cfg";
    $rom = "/boot/config/plugins/$plugin/shareOverrideConfig/$shareName.cfg";
    $cfg = file_exists($ram) ? parse_ini_file($ram, $sections, $scanner) : [];
    return file_exists($rom) ? array_replace_recursive($cfg, parse_ini_file($rom, $sections, $scanner)) : $cfg;
}
#---------------------------------------------------------------------------------------------------------------------


#---------------------------------------------------------------------------------------------------------------------
#This section was adapted from "mover.php"
function getShareSettings($shareName)
{
    $cfg = parse_share_cfg("ca.mover.tuning", $shareName);

    $delimitter = "?|+?"; #To replace spaces, chosen so it is very unlikely to be an issue
    $mover_opt_str = "override"; #Will be used for "initialise()" in age_mover to log that the settings are being changed for the share
    $ageLevel = $cfg['daysold'];
    $sizeLevel = $cfg['sizeinM'];
    $sparsnessLevel = $cfg['sparsnessv'];
    $filelistLevel = str_replace(' ', $delimitter, trim($cfg['filelistv']));
    $filetypesLevel = str_replace(' ', '', trim($cfg['filetypesv']));
    $ctime = $cfg['ctime'];
    $ihidden = $cfg['ignoreHidden'];

    #build age_mover command for all options.
    if ($cfg['age'] == "yes") {
        $mover_opt_str = "$mover_opt_str $ageLevel";
    } else {
        $mover_opt_str = "$mover_opt_str 0";
    }
    if ($cfg['sizef'] == "yes") {
        $mover_opt_str = "$mover_opt_str $sizeLevel";
    } else {
        $mover_opt_str = "$mover_opt_str 0";
    }
    if ($cfg['sparsnessf'] == "yes") {
        $mover_opt_str = "$mover_opt_str $sparsnessLevel";
    } else {
        $mover_opt_str = "$mover_opt_str 0";
    }
    if ($cfg['filelistf'] == "yes") {
        $mover_opt_str = "$mover_opt_str $filelistLevel";
    } else {
        $mover_opt_str = "$mover_opt_str ''";
    }
    if ($cfg['filetypesf'] == "yes") {
        $mover_opt_str = "$mover_opt_str $filetypesLevel";
    } else {
        $mover_opt_str = "$mover_opt_str ''";
    }

    $mover_opt_str = "$mover_opt_str '' ''"; #Required for spacing, defaults before and after scripts to empty

    if (empty($ctime)) {
        $mover_opt_str = "$mover_opt_str ''";
    } else {
        $mover_opt_str = "$mover_opt_str $ctime";
    }

    $mover_opt_str = "$mover_opt_str '' ''"; #Required for spacing, defaults mover threshold and testmode to empty.

    if ($cfg['ignoreHidden'] == "yes") {
        $mover_opt_str = "$mover_opt_str 'yes'";
    } else {
        $mover_opt_str = "$mover_opt_str ''";
    }

    $age_mover_str = "$age_mover_str $delimitter"; 	#Add delimitter for age_mover to use
    $mover_opt_str = "$mover_opt_str $shareName";   #Will be used to log the name of the share with the updated settings



    //exec("echo 'about to hit mover string here: $mover_opt_str' >> /var/log/syslog");

    return $mover_opt_str;
}
#---------------------------------------------------------------------------------------------------------------------
getShareSettings($argv[1]);
echo $mover_opt_str;
?>