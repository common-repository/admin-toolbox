<?php

if(!defined('ABSPATH')) exit;
$user_roles=atb_cur();
if(empty($user_roles)) $user_roles=array();
$get_config=atb_r("SELECT option_name,option_value FROM wp_options WHERE option_name LIKE 'atb_%';");

if($get_config){
  foreach($get_config as $row):
    if($row->option_name=='atb_hide_new_btn') $hide_new_btn=$row->option_value;
    if($row->option_name=='atb_hide_new_btn_role') $hide_new_btn_role=explode(",",$row->option_value);
    if($row->option_name=='atb_hide_new_btn_role_exclude') $hide_new_btn_role_exclude=$row->option_value;
    if($row->option_name=='atb_hide_new_btn_post') $hide_new_btn_post=explode(",",$row->option_value);
    if($row->option_name=='atb_hide_new_btn_post_exclude') $hide_new_btn_post_exclude=$row->option_value;
    
    if($row->option_name=='atb_search_post_meta') $search_post_meta=$row->option_value;
    if($row->option_name=='atb_search_post_meta_role') $search_post_meta_role=explode(",",$row->option_value);
    if($row->option_name=='atb_search_post_meta_role_exclude') $search_post_meta_role_exclude=$row->option_value;
    if($row->option_name=='atb_search_post_meta_post') $search_post_meta_post=explode(",",$row->option_value);
    if($row->option_name=='atb_search_post_meta_post_exclude') $search_post_meta_post_exclude=$row->option_value;
    
    if($row->option_name=='atb_hide_bulk_action') $hide_bulk_action=$row->option_value;
    if($row->option_name=='atb_hide_bulk_action_role') $hide_bulk_action_role=explode(",",$row->option_value);
    if($row->option_name=='atb_hide_bulk_action_role_exclude') $hide_bulk_action_role_exclude=$row->option_value;
    if($row->option_name=='atb_hide_bulk_action_post') $hide_bulk_action_post=explode(",",$row->option_value);
    if($row->option_name=='atb_hide_bulk_action_post_exclude') $hide_bulk_action_post_exclude=$row->option_value;

    if($row->option_name=='atb_limit_usereditor_role') $limit_usereditor_role=$row->option_value;
    if($row->option_name=='atb_limit_usereditor_role_role') $limit_usereditor_role_role=explode(",",$row->option_value);
    if($row->option_name=='atb_limit_usereditor_role_role_exclude') $limit_usereditor_role_role_exclude=$row->option_value;
    if($row->option_name=='atb_limit_usereditor_hiderole_role') {$limit_usereditor_hiderole_role=$row->option_value; set_transient('limit_usereditor_hiderole_role_'.atb_cui(),$limit_usereditor_hiderole_role,3600);}
    
    if($row->option_name=='atb_disable_notices') $disable_notices=$row->option_value;
    if($row->option_name=='atb_disable_notices_role') $disable_notices_role=explode(",",$row->option_value);
    if($row->option_name=='atb_disable_notices_role_exclude') $disable_notices_role_exclude=$row->option_value;
    
    if($row->option_name=='atb_page_hit') $page_hit=$row->option_value;
    if($row->option_name=='atb_page_hit_role') $page_hit_role=explode(",",$row->option_value);
    if($row->option_name=='atb_page_hit_role_exclude') $page_hit_role_exclude=$row->option_value;
    
    $hide_item=0;
    if($row->option_name=='atb_hide_item') $hide_item=$row->option_value;
    if($row->option_name=='atb_hide_item_role') $hide_item_role=explode(",",$row->option_value);
    if($row->option_name=='atb_hide_item_role_exclude') $hide_item_role_exclude=$row->option_value;
    if($row->option_name=='atb_hide_item_id') $hide_item_id=$row->option_value;
    if($row->option_name=='atb_hide_item_all') $hide_item_all=$row->option_value;
    
    if($row->option_name=='atb_dual_auth') $dual_auth=$row->option_value;
    if($row->option_name=='atb_dual_auth_role') $dual_auth_role=explode(",",$row->option_value);
    if($row->option_name=='atb_dual_auth_role_exclude') $dual_auth_role_exclude=$row->option_value;
    if($row->option_name=='atb_dual_auth_ip_exclude') $dual_auth_ip_exclude=$row->option_value;

  endforeach;


  // Get Post Type
  $post_type='';
  if(!empty($_GET['post'])) {
    $post=get_post(intval($_GET['post']));
    if(isset($post->post_type)) $post_type=$post->post_type;
  }
  if(empty($post_type) && !empty($_GET['post_type'])) $post_type=sanitize_text_field($_GET['post_type']);

  $admin_url=basename(get_admin_url());


  // Hide New Post Buttons
  if(isset($hide_new_btn)) if($hide_new_btn==1) {
    if(($hide_new_btn_role_exclude==0 && (count(array_intersect($hide_new_btn_role,$user_roles))>0 || in_array('*All',$hide_new_btn_role,true)))
     || ($hide_new_btn_role_exclude==1 && count(array_intersect($hide_new_btn_role,$user_roles))==0 && !in_array('*All',$hide_new_btn_role,true))) {

      function atb_custom_hide_js() { ?>
        <script type='text/javascript'>
          var admindash_interval=setInterval(function() {if(document.readyState==='complete'){ clearInterval(admindash_interval);hide_new_button();}},10);
          function hide_new_button() {
            if(document.getElementsByClassName('wp-first-item')) {
              var menu_item=document.getElementsByClassName('wp-first-item');
              var i;
              for(i=0; i<menu_item.length; i++) {
                if(menu_item[i].className.replace(' current','')=='wp-first-item') {
                  if(menu_item[i].nextSibling) if(menu_item[i].nextSibling.innerHTML.toLowerCase().indexOf('new')>0) menu_item[i].nextSibling.style.display='none';// Remove New Post Main Menu Items
                }
              }
            }
          }
        </script><?php
      }
      add_action('admin_head','atb_custom_hide_js');
        
      if(($hide_new_btn_post_exclude==0 && (in_array($post_type,$hide_new_btn_post,true) || in_array('*All',$hide_new_btn_post,true)))
       || ($hide_new_btn_post_exclude==1 && !in_array($post_type,$hide_new_btn_post,true) && !in_array('*All',$hide_new_btn_post,true))) {

        function atb_custom_hide_css() { ?>
          <style>.wrap .page-title-action,.wrap .wp-heading-inline+.page-title-action,.wrap .add-new-h2,.split-page-title-action{display:none!important}</style>
          <script type='text/javascript'>
            var admindash_interval=setInterval(function() {if(document.readyState==='complete'){ clearInterval(admindash_interval);hide_new_button();}},10);
            function hide_new_button() {
              if(document.getElementsByClassName('wp-first-item')) {
                var menu_item=document.getElementsByClassName('wp-first-item');
                var i;
                for(i=0; i<menu_item.length; i++) {
                  if(menu_item[i].className.replace(' current','')=='wp-first-item') {
                    if(menu_item[i].nextSibling) if(menu_item[i].nextSibling.innerHTML.toLowerCase().indexOf('new')>0) menu_item[i].nextSibling.style.display='none';// Remove New Post Main Menu Items
                  }
                }
              }
            }
          </script><?php
        }
        add_action('admin_head','atb_custom_hide_css');
      }
    }
  }


  // Admin Post Search Meta
  if(isset($search_post_meta))
  if((atb_is_path($admin_url) && atb_is_path('/edit.php,post_type=')) && $search_post_meta==1) {
    if(($search_post_meta_role_exclude==0 && (count(array_intersect($search_post_meta_role,$user_roles))>0 || in_array('*All',$search_post_meta_role,true)))
     || ($search_post_meta_role_exclude==1 && count(array_intersect($search_post_meta_role,$user_roles))==0 && !in_array('*All',$search_post_meta_role,true))) {

      if(strlen($post_type)>0 
       && (($search_post_meta_post_exclude==0 && (in_array($post_type,$search_post_meta_post,true) || in_array('*All',$search_post_meta_post,true)))
       || ($search_post_meta_post_exclude==1 && !in_array($post_type,$search_post_meta_post,true) && !in_array('*All',$search_post_meta_post,true)))) {
        
        add_filter('pre_get_posts','atb_admin_post_search_meta');
        
        function add_search_meta_checkbox() {
          $js="<script type='text/javascript'>";
            $post_type=sanitize_text_field($_GET['post_type']);
            if(isset($_GET['search_meta'])) $search_meta='checked'; else $search_meta='';
            $meta_keys=atb_r("
              SELECT DISTINCT REPLACE(meta_key,'_',' ')meta_key,MAX(LENGTH(meta_value))val_len
              FROM wp_postmeta pm 
              JOIN (SELECT * FROM wp_posts WHERE post_type='$post_type' AND post_status!='trash' ORDER BY ID DESC LIMIT 100)p ON p.ID=pm.post_id
              WHERE meta_value NOT LIKE 'field_%'
              AND meta_key NOT LIKE '%_edit_%'
              AND LENGTH(meta_value)<100
              GROUP BY meta_key
              HAVING val_len>2
              LIMIT 100;");
            $meta=array();
            if($meta_keys) foreach($meta_keys as $m) array_push($meta,ucwords($m->meta_key)); else return;
            if(count($meta)>0) {
              $meta=implode('\n',$meta);
              $js.="if(document.getElementsByClassName('search-box')) document.getElementsByClassName('search-box')[0].innerHTML+=\"<div style='margin: .3em 0' title='Search Meta Data:\\n$meta'><input type='checkbox' name='search_meta' $search_meta> Search Meta<span class='dashicons dashicons-editor-help'></span></div>\";";
            }

          if(isset($_GET['s'])) {
            $term=sanitize_text_field($_GET['s']);
            $js.="if(document.getElementsByClassName('subtitle')) document.getElementsByClassName('subtitle')[0].innerHTML='Search results for <b>\"$term\"</b>';";
          }
          echo "$js</script>";
        }
        add_action('admin_footer','add_search_meta_checkbox');
      }
    }
  }


  // Hide Bulk Actions
  if((atb_is_path($admin_url) && atb_is_path('/edit.php,post_type=')) && $hide_bulk_action==1) {
    if(($hide_bulk_action_role_exclude==0 && (count(array_intersect($hide_bulk_action_role,$user_roles))>0 || in_array('*All',$hide_bulk_action_role,true)))
     || ($hide_bulk_action_role_exclude==1 && count(array_intersect($hide_bulk_action_role,$user_roles))==0 && !in_array('*All',$hide_bulk_action_role,true))) {

      if(strlen($post_type)>0 
       && (($hide_bulk_action_post_exclude==0 && (in_array($post_type,$hide_bulk_action_post,true) || in_array('*All',$hide_bulk_action_post,true)))
       || ($hide_bulk_action_post_exclude==1 && !in_array($post_type,$hide_bulk_action_post,true) && !in_array('*All',$hide_bulk_action_post,true)))) {
      
        function atb_custom_remove_bulk() { ?>
          <style>
            .bulkactions,.check-column{display:none} /* Hide Bulk Delete */
            .row-actions .trash,.row-actions .duplicate,.row-actions .acf-duplicate-field-group,.row-actions .editinline,.row-actions .view,.row-actions .hide-if-no-js{display:none} /* Hide Individual Delete */
            .row-actions .edit:after{content:"\0270E";color:#0073b1}
          </style><?php
        }
        add_action('admin_footer','atb_custom_remove_bulk');
      }
    }
  }


  // User New/Edit Screen
  if((atb_is_path($admin_url) && atb_is_path('/user-new.php,/user-edit.php')) && $limit_usereditor_role==1) {
    if(($limit_usereditor_role_role_exclude==0 && (count(array_intersect($limit_usereditor_role_role,$user_roles))>0 || in_array('*All',$limit_usereditor_role_role,true)))
     || ($limit_usereditor_role_role_exclude==1 && count(array_intersect($limit_usereditor_role_role,$user_roles))==0 && !in_array('*All',$limit_usereditor_role_role,true))) {

      function atb_user() { ?>
      <style type='text/css'>.actions,#role{display:none}</style>
      
      <script type='text/javascript'>
        var removeUserRoles_interval=setInterval(function() {if(document.readyState==='complete') { clearInterval(removeUserRoles_interval); atb_removeUserRoles('role'); }},100);
        function atb_removeUserRoles(menu) {
          
          // Hide Specific Roles
          if(document.getElementById(menu)) {
            var roles=document.getElementById(menu);
            var role_html=roles.innerHTML;
            var hide_roles='<?php echo get_transient('limit_usereditor_hiderole_role_'.atb_cui()); ?>'.split(',');
            if(hide_roles) for(var i=0; i<hide_roles.length; i++) if(hide_roles[i].length>1) role_html=role_html.replace('<option value="'+hide_roles[i]+'">','<option value="'+hide_roles[i]+'" hidden>');
            roles.innerHTML=role_html;
            roles.style.display='block';
          }

          // Hide Multiple Role Selection
          if(document.getElementsByClassName('form-table')) {
            var user_multi_role=document.getElementsByClassName('form-table');
            for(i=0; i < user_multi_role.length; i++) if(user_multi_role[i].innerHTML.indexOf('Other Roles')>0) user_multi_role[i].style.visibility='hidden';
          }
        }
      </script><?php
      }
      add_action('admin_footer','atb_user');
    }
  }


  // User List Screen
  if(atb_is_path($admin_url.'/users.php') && $limit_usereditor_role==1) {
    if(($limit_usereditor_role_role_exclude==0 && (count(array_intersect($limit_usereditor_role_role,$user_roles))>0 || in_array('*All',$limit_usereditor_role_role,true)))
     || ($limit_usereditor_role_role_exclude==1 && count(array_intersect($limit_usereditor_role_role,$user_roles))==0 && !in_array('*All',$limit_usereditor_role_role,true))) {

      function atb_user_list() { ?>
        <style type='text/css'>#ure_grant_roles,#new_role,#new_role2{display:none}</style>
        
        <script type='text/javascript'>
          var removeUserRoles_interval=setInterval(function() {if(document.readyState==='complete') { clearInterval(removeUserRoles_interval); atb_removeUserRoles('new_role'); atb_removeUserRoles('new_role2');}},100);
          function atb_removeUserRoles(menu) {
          
            // Hide Specific Roles
            if(document.getElementById(menu)) {
              var roles=document.getElementById(menu);
              var role_html=roles.innerHTML;
              var hide_roles='<?php echo get_transient('limit_usereditor_hiderole_role_'.atb_cui());?>'.split(',');
              if(hide_roles) for(var i=0; i<hide_roles.length; i++) if(hide_roles[i].length>1) role_html=role_html.replace('<option value="'+hide_roles[i]+'">','<option value="'+hide_roles[i]+'" hidden>');
              roles.innerHTML=role_html;
              roles.style.display='block';
            }

          }
        </script><?php
      }
      add_action('admin_footer','atb_user_list');
    }
  }


  // Disable Update Notices
  if($disable_notices==1) {
    if(($disable_notices_role_exclude==0 && (count(array_intersect($disable_notices_role,$user_roles))>0 || in_array('*All',$disable_notices_role,true)))
     || ($disable_notices_role_exclude==1 && count(array_intersect($disable_notices_role,$user_roles))==0 && !in_array('*All',$disable_notices_role,true))) {

      function remove_core_updates(){global $wp_version;return(object) array('last_checked'=> time(),'version_checked'=> $wp_version,);}
      add_filter('pre_site_transient_update_core','remove_core_updates');
      add_filter('pre_site_transient_update_plugins','remove_core_updates');
      add_filter('pre_site_transient_update_themes','remove_core_updates');
      
      function atb_notices() { return "<style type='text/css'>.update_nag,.notice-warning,notice-error,.error.is-dismissible{display:none!important}</style>"; }
      add_action('admin_footer','atb_notices');
      //echo "<script type='text/javascript'>alert('no_update_notification');</script>";
    }
  }


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


  // Hide Items 
  if($hide_item==1) {
    if(!empty($hide_item_all)) add_action('admin_footer','atb_hide_item_all');
    if(!empty($hide_item_id)) {
      if(($hide_item_role_exclude==0 && (count(array_intersect($hide_item_role,$user_roles))>0 || in_array('*All',$hide_item_role,true)))
       || ($hide_item_role_exclude==1 && count(array_intersect($hide_item_role,$user_roles))==0 && !in_array('*All',$hide_item_role,true))) {
        add_action('admin_head','atb_hide_item');
      }
    }
  }

} else atb_checkConfig();
