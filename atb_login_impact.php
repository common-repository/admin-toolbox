<?php

if(!defined('ABSPATH')) exit;
$user_roles=atb_cur();
if(empty($user_roles)) $user_roles=array();
$get_config=atb_r("SELECT option_name,option_value FROM wp_options WHERE option_name LIKE 'atb_%';");

if($get_config):
  foreach($get_config as $row):
    if($row->option_name=='atb_page_hit') $page_hit=$row->option_value;
    if($row->option_name=='atb_page_hit_role') $page_hit_role=explode(",",$row->option_value);
    if($row->option_name=='atb_page_hit_role_exclude') $page_hit_role_exclude=$row->option_value;
    
    if($row->option_name=='atb_dual_auth') $dual_auth=$row->option_value;
    if($row->option_name=='atb_dual_auth_role') $dual_auth_role=explode(",",$row->option_value);
    if($row->option_name=='atb_dual_auth_role_exclude') $dual_auth_role_exclude=$row->option_value;
    if($row->option_name=='atb_dual_auth_ip_exclude') $dual_auth_ip_exclude=$row->option_value;
  endforeach;


  // Dual Authentication 
  $ip=sanitize_text_field($_SERVER['REMOTE_ADDR']);
  if($dual_auth==1) {
    if(($dual_auth_role_exclude==0 && (count(array_intersect($dual_auth_role,$user_roles))>0 || in_array('*All',$dual_auth_role,true)))
     || ($dual_auth_role_exclude==1 && count(array_intersect($dual_auth_role,$user_roles))==0 && !in_array('*All',$dual_auth_role,true))) {
      atb_dual_auth($dual_auth_ip_exclude);
    } else if(get_transient("atb_flag_$ip:".atb_cui())!==false) delete_transient("atb_flag_$ip:".atb_cui());
  } else if(get_transient("atb_flag_$ip:".atb_cui())!==false) delete_transient("atb_flag_$ip:".atb_cui());


  // Capture Page Hits
  if($page_hit==1) {
    if(($page_hit_role_exclude==0 && (count(array_intersect($page_hit_role,$user_roles))>0 || in_array('*All',$page_hit_role,true)))
     || ($page_hit_role_exclude==1 && count(array_intersect($page_hit_role,$user_roles))==0 && !in_array('*All',$page_hit_role,true))) {
      atb_hit_page();
    }
  }
endif; ?>