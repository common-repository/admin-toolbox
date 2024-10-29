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
    
    if($row->option_name=='atb_hide_item') $hide_item=$row->option_value;
    if($row->option_name=='atb_hide_item_role') $hide_item_role=explode(",",$row->option_value);
    if($row->option_name=='atb_hide_item_role_exclude') $hide_item_role_exclude=$row->option_value;
    if($row->option_name=='atb_hide_item_id') $hide_item_id=$row->option_value;
    if($row->option_name=='atb_hide_item_all') $hide_item_all=$row->option_value;

    if($row->option_name=='atb_disable_xmlrpc') $disable_xmlrpc=$row->option_value;
  endforeach;


  // Disable XML-RPC
  if($disable_xmlrpc==1) {
    add_filter('xmlrpc_enabled','__return_false');
    if(atb_is_path('xmlrpc.php')) exit;
  }

  // Capture Page Hits
  if($page_hit==1) {
    if(($page_hit_role_exclude==0 && (count(array_intersect($page_hit_role,$user_roles))>0 || in_array('*All',$page_hit_role,true)))
     || ($page_hit_role_exclude==1 && count(array_intersect($page_hit_role,$user_roles))==0 && !in_array('*All',$page_hit_role,true))) {

      atb_hit_page();
    }
  }

  // Hide Items 
  if($hide_item==1) {
    if(!empty($hide_item_all)) {add_action('admin_footer','atb_hide_item_all'); add_action('wp_footer','atb_hide_item_all');}
    if(!empty($hide_item_id)) {
    if(($hide_item_role_exclude==0 && (count(array_intersect($hide_item_role,$user_roles))>0 || in_array('*All',$hide_item_role,true)))
     || ($hide_item_role_exclude==1 && count(array_intersect($hide_item_role,$user_roles))==0 && !in_array('*All',$hide_item_role,true))) {
      add_action('wp_footer','atb_hide_item');
      }
    }
  }

endif; ?>