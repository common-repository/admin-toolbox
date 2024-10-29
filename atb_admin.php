<?php

if(!defined('ABSPATH')) exit;
if(!current_user_can('manage_options')) exit;
if(!empty($_POST['option_name'])) $option_name=array_map('sanitize_text_field',$_POST['option_name']);
if(!empty($_POST['option_value'])) $option_value=array_map('sanitize_textarea_field',$_POST['option_value']);
$input_saved=0;
$admin_ip=$_SERVER['REMOTE_ADDR'];

// Get Versions
  global $wp_version;
  global $atb_version;
  global $atb_pro_version;
  global $atb_version_type;
  $atb_db_version=get_option('atb_db_version');
  $atb_pro_version=0;
  $atb_version_type='GPL';
  atb_pro_path('admin-toolbox','PRO'); atb_pro_path('admin-toolbox','pro');
  $get_version=atb_r("SELECT @@version as version;");
  if($get_version) foreach($get_version as $row):$mysql_version=$row->version;endforeach;
  $db_config_mode=$wpdb->get_results("SELECT @@sql_safe_updates as mode;",OBJECT);
  if($db_config_mode) foreach($db_config_mode as $row):$config_mode=$row->mode;endforeach;

  function atb_pro_path($slug,$type) {
    global $atb_pro_version;
    global $atb_version_type;
    $pro_path=str_replace($slug,"$slug-$type",plugin_dir_path( __FILE__ )).'pro_functions.php';
    if(is_plugin_active("$slug-$type/pro_functions.php"))
      if(file_exists($pro_path)) {include_once $pro_path; $atb_pro_version=get_option('atb_pro_version'); if($atb_pro_version>0) $atb_version_type='PRO';}
  }

  function atb_roles() {
    if(is_multisite()) echo "<option value='super_admin'>super admin";
    foreach(get_editable_roles() as $role_name=>$role_info):?>
      <option value='<?php echo $role_name;?>'><?php echo $role_name;
    endforeach;
  }

// Sync Configuration
  if($atb_version!=$atb_db_version) atb_activate(1); // Run dbDelta upgrade

// Update Configuration
  if(!empty($_POST['atb_config']) && check_admin_referer('atb_config','atb_config')) {
    //print_r($option_name); print_r($option_value);
    foreach($option_name as $index=>$option) delete_option('atb_'.$option_name[$index]); reset($option_name);
    foreach($option_name as $index=>$option) {
      if(isset($option_value[$index])) update_option('atb_'.$option,$option_value[$index]);
    }
    $input_saved++;
    update_option('atb_setup',$atb_version);
  }

// Clear Abuse Cache
  if(isset($_GET['clear_abu_cache'])) {
    $clean_rpt=atb_r("DELETE FROM wp_aiowps_permanent_block;");
    $clean_abu=atb_r("DELETE FROM wp_options WHERE option_name LIKE '_transient_%abu_%' AND option_name LIKE '%.%';");
  }


// Get Configuration
  $get_config=atb_r("SELECT option_name,option_value FROM wp_options WHERE option_name LIKE 'atb_%';");
  $not_set='';
  if($get_config): 
  foreach ($get_config as $row): 
    if($row->option_name=='atb_page_hit_keep') $settings_keep=$row->option_value;
    if($row->option_name=='atb_settings_keep') $settings_keep=$row->option_value;
    if($row->option_name=='atb_https_redirect') $https_redirect=$row->option_value;

    if($row->option_name=='atb_page_hit') $page_hit=$row->option_value;
    if($row->option_name=='atb_page_hit_role') $page_hit_role=$row->option_value;
    if($row->option_name=='atb_page_hit_role_exclude') $page_hit_role_exclude=$row->option_value;
    if($row->option_name=='atb_page_hit_page_exclude') $page_hit_page_exclude=$row->option_value;
    if($row->option_name=='atb_page_hit_page_frag') $page_hit_page_frag=$row->option_value;

    if($row->option_name=='atb_dual_auth') $dual_auth=$row->option_value;
    if($row->option_name=='atb_dual_auth_role') $dual_auth_role=$row->option_value;
    if($row->option_name=='atb_dual_auth_role_exclude') $dual_auth_role_exclude=$row->option_value;
    if($row->option_name=='atb_dual_auth_ip_exclude') $dual_auth_ip_exclude=$row->option_value;

    if($row->option_name=='atb_disable_locs') $disable_locs=$row->option_value;
    if($row->option_name=='atb_disable_locs_ctry') $disable_locs_ctry=$row->option_value;
    if($row->option_name=='atb_disable_locs_exclude') $disable_locs_exclude=$row->option_value;
    if($row->option_name=='atb_disable_locs_ip_exclude') $disable_locs_ip_exclude=$row->option_value;

    if($row->option_name=='atb_sec_actions') $sec_actions=$row->option_value;
    if($row->option_name=='atb_sec_actions_mute') $sec_actions_mute=$row->option_value;
    if($row->option_name=='atb_sec_actions_ip_exclude') $sec_actions_ip_exclude=$row->option_value;

    if($row->option_name=='atb_err_rpt') $err_rpt=$row->option_value;
    if($row->option_name=='atb_err_rpt_path') $err_rpt_path=$row->option_value;
    if($row->option_name=='atb_err_rpt_exclude') $err_rpt_exclude=$row->option_value;

    if($row->option_name=='atb_hide_item') $hide_item=$row->option_value;
    if($row->option_name=='atb_hide_item_role') $hide_item_role=$row->option_value;
    if($row->option_name=='atb_hide_item_role_exclude') $hide_item_role_exclude=$row->option_value;
    if($row->option_name=='atb_hide_item_id') $hide_item_id=$row->option_value;
    if($row->option_name=='atb_hide_item_all') $hide_item_all=$row->option_value;

    if($row->option_name=='atb_redirect_hp') $redirect_hp=$row->option_value;
    if($row->option_name=='atb_redirect_hp_role') $redirect_hp_role=$row->option_value;
    if($row->option_name=='atb_redirect_hp_role_exclude') $redirect_hp_role_exclude=$row->option_value;
    if($row->option_name=='atb_redirect_hp_page') $redirect_hp_page=$row->option_value;

    if($row->option_name=='atb_redirect_db') $redirect_db=$row->option_value;
    if($row->option_name=='atb_redirect_db_role') $redirect_db_role=$row->option_value;
    if($row->option_name=='atb_redirect_db_role_exclude') $redirect_db_role_exclude=$row->option_value;
    if($row->option_name=='atb_redirect_db_page') $redirect_db_page=$row->option_value;

    if($row->option_name=='atb_disable_notices') $disable_notices=$row->option_value;
    if($row->option_name=='atb_disable_notices_role') $disable_notices_role=$row->option_value;
    if($row->option_name=='atb_disable_notices_role_exclude') $disable_notices_role_exclude=$row->option_value;

    if($row->option_name=='atb_search_post_meta') $search_post_meta=$row->option_value;
    if($row->option_name=='atb_search_post_meta_role') $search_post_meta_role=$row->option_value;
    if($row->option_name=='atb_search_post_meta_role_exclude') $search_post_meta_role_exclude=$row->option_value;
    if($row->option_name=='atb_search_post_meta_post') $search_post_meta_post=$row->option_value;
    if($row->option_name=='atb_search_post_meta_post_exclude') $search_post_meta_post_exclude=$row->option_value;

    if($row->option_name=='atb_hide_bulk_action') $hide_bulk_action=$row->option_value;
    if($row->option_name=='atb_hide_bulk_action_role') $hide_bulk_action_role=$row->option_value;
    if($row->option_name=='atb_hide_bulk_action_role_exclude') $hide_bulk_action_role_exclude=$row->option_value;
    if($row->option_name=='atb_hide_bulk_action_post') $hide_bulk_action_post=$row->option_value;
    if($row->option_name=='atb_hide_bulk_action_post_exclude') $hide_bulk_action_post_exclude=$row->option_value;

    if($row->option_name=='atb_hide_new_btn') $hide_new_btn=$row->option_value;
    if($row->option_name=='atb_hide_new_btn_role') $hide_new_btn_role=$row->option_value;
    if($row->option_name=='atb_hide_new_btn_role_exclude') $hide_new_btn_role_exclude=$row->option_value;
    if($row->option_name=='atb_hide_new_btn_post') $hide_new_btn_post=$row->option_value;
    if($row->option_name=='atb_hide_new_btn_post_exclude') $hide_new_btn_post_exclude=$row->option_value;

    if($row->option_name=='atb_limit_usereditor_role') $limit_usereditor_role=$row->option_value;
    if($row->option_name=='atb_limit_usereditor_role_role') $limit_usereditor_role_role=$row->option_value;
    if($row->option_name=='atb_limit_usereditor_role_role_exclude') $limit_usereditor_role_role_exclude=$row->option_value;
    if($row->option_name=='atb_limit_usereditor_hiderole_role') $limit_usereditor_hiderole_role=$row->option_value;

    if($row->option_name=='atb_limit_img_size') $limit_img_size=$row->option_value;
    if($row->option_name=='atb_limit_img_size_role') $limit_img_size_role=$row->option_value;
    if($row->option_name=='atb_limit_img_size_role_exclude') $limit_img_size_role_exclude=$row->option_value;
    if($row->option_name=='atb_limit_img_size_kb') $limit_img_size_kb=$row->option_value;

    if($row->option_name=='atb_admin_email_check') $admin_email_check=$row->option_value;
    if($row->option_name=='atb_disable_xmlrpc') $disable_xmlrpc=$row->option_value;
  endforeach;
  
  if(!empty($_POST['atb_config'])) if($page_hit>0)
    atb_r("CREATE TABLE IF NOT EXISTS wp_atb_pagehits
      (hit_id INT NOT NULL AUTO_INCREMENT
      ,date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
      ,ip VARCHAR(191) NULL
      ,userid INT
      ,url VARCHAR(500)	 
      ,referrer VARCHAR(500)
      ,PRIMARY KEY (hit_id));
    ");
  $atb_setup_mode=0;
else:
  $atb_setup_mode=1;
  $not_set="<div style='color:orange'>&#9888; Choose an option to proceed</div>";
endif;

if(!isset($settings_keep)) $settings_keep='';
if(!isset($https_redirect)) $https_redirect='';

if(!isset($page_hit)) $page_hit=0;
if(!isset($page_hit_role)) $page_hit_role='*All';
if(!isset($page_hit_role_exclude)) $page_hit_role_exclude=0;
if(!isset($page_hit_page_exclude)) $page_hit_page_exclude=1;
if(!isset($page_hit_page_frag)) $page_hit_page_frag='admin-ajax
wc-ajax
wp-cron.php
wc-ajax
robots.txt
favicon.ico';

if(!isset($dual_auth)) $dual_auth=0;
if(!isset($dual_auth_role)) $dual_auth_role='administrator';
if(!isset($dual_auth_role_exclude)) $dual_auth_role_exclude=1;
if(!isset($dual_auth_ip_exclude)) $dual_auth_ip_exclude=$admin_ip;

if(!isset($disable_locs)) $disable_locs=0;
if(!isset($disable_locs_ctry)) $disable_locs_ctry='US';
if(!isset($disable_locs_exclude)) $disable_locs_exclude=1;
if(!isset($disable_locs_ip_exclude)) $disable_locs_ip_exclude=$admin_ip;

if(!isset($sec_actions)) $sec_actions=0;
if(!isset($sec_actions_mute)) $sec_actions_mute=0;
if(!isset($sec_actions_ip_exclude)) $sec_actions_ip_exclude=$admin_ip;

if(!isset($err_rpt)) $err_rpt=0;
if(!isset($err_rpt_path)) $err_rpt_path='';
if(!isset($err_rpt_exclude)) $err_rpt_exclude='__()
client denied
log buffer is full
Cannot serve directory
ModSecurity: Warning';

if(!isset($hide_item)) $hide_item=0;
if(!isset($hide_item_role)) $hide_item_role='administrator';
if(!isset($hide_item_role_exclude)) $hide_item_role_exclude=1;
if(!isset($hide_item_id)) $hide_item_id='';
if(!isset($hide_item_all)) $hide_item_all='';

if(!isset($redirect_hp)) $redirect_hp=0;
if(!isset($redirect_hp_role)) $redirect_hp_role='*ALL';
if(!isset($redirect_hp_role_exclude)) $redirect_hp_role_exclude=0;
if(!isset($redirect_hp_page)) $redirect_hp_page='/author
/?author
/feed/';

if(!isset($redirect_db)) $redirect_db=0;
if(!isset($redirect_db_role)) $redirect_db_role='administrator';
if(!isset($redirect_db_role_exclude)) $redirect_db_role_exclude=1;
if(!isset($redirect_db_page)) $redirect_db_page='';

if(!isset($disable_notices)) $disable_notices='';
if(!isset($disable_notices_role)) $disable_notices_role='administrator';
if(!isset($disable_notices_role_exclude)) $disable_notices_role_exclude=1;

if(!isset($search_post_meta)) $search_post_meta='';
if(!isset($search_post_meta_role)) $search_post_meta_role='*All';
if(!isset($search_post_meta_role_exclude)) $search_post_meta_role_exclude=0; //new
if(!isset($search_post_meta_post)) $search_post_meta_post='*All';
if(!isset($search_post_meta_post_exclude)) $search_post_meta_post_exclude=0; //new

if(!isset($hide_bulk_action)) $hide_bulk_action='';
if(!isset($hide_bulk_action_role)) $hide_bulk_action_role='administrator';
if(!isset($hide_bulk_action_role_exclude)) $hide_bulk_action_role_exclude=1; //new
if(!isset($hide_bulk_action_post)) $hide_bulk_action_post='*All';
if(!isset($hide_bulk_action_post_exclude)) $hide_bulk_action_post_exclude=0; //new

if(!isset($hide_new_btn)) $hide_new_btn='';
if(!isset($hide_new_btn_role)) $hide_new_btn_role='administrator';
if(!isset($hide_new_btn_role_exclude)) $hide_new_btn_role_exclude=1; //new
if(!isset($hide_new_btn_post)) $hide_new_btn_post='*All';
if(!isset($hide_new_btn_post_exclude)) $hide_new_btn_post_exclude=0; //new

if(!isset($limit_usereditor_role)) $limit_usereditor_role='';
if(!isset($limit_usereditor_hiderole_role)) $limit_usereditor_hiderole_role='administrator';
if(!isset($limit_usereditor_role_role_exclude)) $limit_usereditor_role_role_exclude=1;
if(!isset($limit_usereditor_role_role)) $limit_usereditor_role_role='administrator';

if(!isset($limit_img_size)) $limit_img_size=0;
if(!isset($limit_img_size_role)) $limit_img_size_role='*All';
if(!isset($limit_img_size_role_exclude)) $limit_img_size_role_exclude=0;
if(!isset($limit_img_size_kb)) $limit_img_size_kb='512';

if(!isset($admin_email_check)) $admin_email_check=6;
if(!isset($disable_xmlrpc)) $disable_xmlrpc=0;
$phv=0;
if(atb_is_path('phv=')) if($_GET['phv']>0) $phv=1;
?>
<table id='atb_head' class='atb_control' style='width:100%;border:none;'>
  <tr>
    <td align='left'>
      <img style='height:20px;' src='<?php echo plugins_url('/assets/AdminToolbox.png',__FILE__);?>'>
      <div class='atb_links'>
        <a href="#!" onclick="atb_expand('atb_pro');"><span class='caps'>Admin Toolbox <span class='pro'>Pro</span><span class="dashicons dashicons-chart-line" style='color:#b71b8a'></span><span style='color:#1177aa;font-weight:bold'></a><br>
        <a href="#!" onclick="atb_expand('atb_diag');">Support & Diagnostics <span class="dashicons dashicons-admin-tools"></span></a>
      </div>
      <br><hr style='width:50%;float:left'><br>
      <?php if(isset($phv)) if($phv>0) { ?>
        <a id='atb_return' href='#!' onclick="atb_switchScreen('atb_admin');"><div id='atb_return_icon'></div> Return to Admin</a>
      <?php } ?>
    </td>
  </tr>
</table>

  <div id='atb_diag' class='atb_control atb_expand'style='float:right'>
    <span style='color:#1177aa;font-weight:bold'><span class="dashicons dashicons-admin-tools"></span> Support & Diagnostics</span>
    <a onclick="atb_expand('atb_diag');" style='text-decoration:none;float:right'><span class="dashicons dashicons-dismiss"></span></a><hr>
    <br>
    <div id='atb_diag_data'>
      <b>Configuration</b><br>
      Host <?php echo $_SERVER['HTTP_HOST'].'@'.$_SERVER['SERVER_ADDR']; ?><br>
      Path <?php echo substr(plugin_dir_path( __FILE__ ),-34);?><br>
      WP <?php echo $wp_version; if(is_multisite()) echo 'multi'; ?><br>
      PHP <?php echo phpversion();?><br>
      MYSQL <?php echo $mysql_version; if(!empty($config_mode)) echo $config_mode; ?><br>
      Theme <?php $pt=wp_get_theme(get_template()); echo $pt->Name.' '.$pt->Version; $ct=wp_get_theme(); if($pt->Name!==$ct->Name) echo ', '.$ct->Name.' '.$ct->Version;?><br>
      Admin Toolbox <?php echo $atb_version.' '.$atb_version_type.' '.$atb_pro_version; ?><br>
      Admin Toolbox db <?php echo $atb_db_version;?><br>
      <hr>
      <b>Settings</b><br>
      <?php if($atb_setup_mode==0) { ?>
      Keep Settings: <?php echo $settings_keep;?><br>
      Force HTTPS: <?php echo $https_redirect;?><br>
      Capture Visits: <?php echo $page_hit;?>, role <?php echo $page_hit_role;?>, role_exclude <?php echo $page_hit_role_exclude;?><br>
      Dual Auth: <?php echo $dual_auth;?>, role <?php echo $dual_auth_role;?>, role_exclude <?php echo $dual_auth_role_exclude;?>, ip_exclude <?php echo $dual_auth_ip_exclude;?><br>
      Geo Location: <?php echo $disable_locs;?>, ctry <?php echo $disable_locs_ctry;?>, exclude <?php echo $disable_locs_exclude;?><br>
      Security Actions: <?php echo $sec_actions;?>, mute <?php echo $sec_actions_mute;?>, ip_exclude <?php echo $sec_actions_ip_exclude;?><br>
      Error Reports: <?php echo $err_rpt;?>, path <?php echo $err_rpt_path;?>, exclude <?php echo $err_rpt_exclude;?><br>
      Hide Items: <?php echo $hide_item;?>, role <?php echo $hide_item_role;?>, role_exclude <?php echo $hide_item_role_exclude;?>, items <?php echo $hide_item_id;?>, all roles <?php echo $hide_item_all;?><br>
      Redirect to HP: <?php echo $redirect_hp;?>, role <?php echo $redirect_hp_role;?>, role_exclude <?php echo $redirect_hp_role_exclude;?>, pages <?php echo $redirect_hp_page;?><br>
      Redirect to WP Admin: <?php echo $redirect_db;?>, role <?php echo $redirect_db_role;?>, role_exclude <?php echo $redirect_db_role_exclude;?>, pages <?php echo $redirect_db_page;?><br>
      Disable Notices: <?php echo $disable_notices;?>, role <?php echo $disable_notices_role;?>, role_exclude <?php echo $disable_notices_role_exclude;?><br>
      Search Post Meta: <?php echo $search_post_meta;?>, role <?php echo $search_post_meta_role;?>, role_exclude <?php echo $search_post_meta_role_exclude;?>, post <?php echo $search_post_meta_post;?>, post_exclude <?php echo $search_post_meta_post_exclude;?><br>
      Hide Bulk Action: <?php echo $hide_bulk_action;?>, role <?php echo $hide_bulk_action_role;?>, role_exclude <?php echo $hide_bulk_action_role_exclude;?>, post <?php echo $hide_bulk_action_post;?>, post_exclude <?php echo $hide_bulk_action_post_exclude;?><br>
      Hide New Button: <?php echo $hide_new_btn;?>, role <?php echo $hide_new_btn_role;?>, role_exclude <?php echo $hide_new_btn_role_exclude;?>, post <?php echo $hide_new_btn_post;?>, post_exclude <?php echo $hide_new_btn_post_exclude;?><br>
      Hide Roles: <?php echo $limit_usereditor_role;?>, target role <?php echo $limit_usereditor_role_role;?>, role <?php echo $limit_usereditor_hiderole_role;?>, role_exclude <?php echo $limit_usereditor_role_role_exclude;?><br>
      Limit Image Size: <?php echo $limit_img_size;?>, role <?php echo $limit_img_size_role;?>, role_exclude <?php echo $limit_img_size_role_exclude;?>, size_kb <?php echo $limit_img_size_kb;?><br>
      Admin Email Check: <?php echo $admin_email_check;?><br>
      Disable XML-RPC: <?php echo $disable_xmlrpc;?><br><?php 
    } else echo 'Incomplete Setup'; ?>
    </div>
    <br>
    <a class='atb_support' href="https://richardlerma.com/contact/?imsg=" target='_blank' onclick="this.href+=append_diag('atb_diag_data');">Contact Support</a>
  </div>

  <div id='atb_pro' class='atb_control atb_expand' style='float:right'>
    Get <span class='caps'>Admin Toolbox <span class='pro'>Pro</span></span><span class="dashicons dashicons-chart-line" style='color:#b71b8a;vertical-align:text-bottom'></span>
    <a onclick="atb_expand('atb_pro');" style='text-decoration:none;float:right'><span class="dashicons dashicons-dismiss"></span></a><hr>
    <br>
      <strong>Subscription Features</strong>
      <ul style='padding:unset'>
        <li>View & Filter itemized page visits by date, user & URL
        <li>Geo-Location Lockout capabilities
        <li>Auto-blacklist by IP reputation
        <li>Crowd-report brute force hits and malicious traffic
        <li>Simple SQL Query Interface
        <li>Two-Factor Authentication via Text
        <li>Log Errors and Email new errors
        <li>Dedicated email support
        <li>PRO add-on installs quickly next to existing plugin
      </ul>
      </ul><br>

    <?php if(function_exists('atb_pro_activate')) {
      if($atb_version_type=='PRO') { ?>
        <div class='attn caps' style='font-weight:normal;cursor:pointer;text-align:center;padding-right:2.5em' onclick="atb_expand('atb_pro');"><span class='dashicons dashicons-yes'></span> Active</div>
        <input type='button' class='button' value='Manage Subscription' onclick="window.open('https://www.paypal.com/myaccount/autopay/','_blank');">
      <?php } ?>
      <input type='button' class='button' value='Check for Updates' style='margin-top:1em' onclick="window.location.href='<?php echo get_admin_url(null,'admin.php?page=admin-toolbox&pro_update=1');?>';">
    <?php 
    }

    if($atb_version_type!='PRO') { ?><a class='atb_link caps' style='margin-top:1em' href="https://richardlerma.com/atb-terms/" target='_blank'>Learn More</a><br><?php } ?>
  </div><?php

if($page_hit>0) atb_view_summary($atb_version_type);
if($phv>0 && function_exists('atb_view_hits')) atb_view_hits();?>

<div id='atb_admin'>
  <?php if(function_exists('atb_qint')) atb_qint();?>
  <form class='atb_form' name='atb_config' id='atb_config' method='post' accept-charset='UTF-8 ISO-8859-1''><?php
    echo wp_nonce_field('atb_config','atb_config');?>
    <table id='atb_settings' class='atb_control' cellspacing='0' cellpadding='0'>
      <tr><th align='left' colspan='4'><span class="dashicons dashicons-admin-settings" style='font-size:1.8em;vertical-align:bottom'></span> <span style='color:#1177aa;font-weight:bold'>Settings</span><hr><br></th></tr>
      <tr>
        <th style='min-width:150px'>Feature</th>
        <th>Status</th>
        <th>Condition One</th>
        <th>Condition Two</th>
      </tr>

      <tr style='background:#F5F5F5'>
        <td><span class='atb_title'><span class="dashicons dashicons-forms"></span>Data</span> <br>On uninstall...</td>
        <td nowrap colspan='3'>
          <input type='hidden' name='option_name[]' value='settings_keep'>
          <select name='option_value[]' id='settings_keep' onchange="atb_change('atb_config',this.id,this.selectedIndex);">
            <option value='' disabled  <?php if($atb_setup_mode>0) echo 'selected';?>>Uninstall Option
            <option value='1' <?php if($settings_keep==1) echo 'selected';?>>Keep Data Safe (Recommended)
            <option value='0' <?php if($settings_keep==0 && $atb_setup_mode==0) echo 'selected';?>>Delete all Plugin Data
          </select>
          <span id='settings_keep-sel' class='sel_status'><?php echo $not_set;?></span>
        </td>
      </tr>

      <tr style='background:#FFF'>
        <td><span class='atb_title'><span class="dashicons dashicons-shield"></span>Force HTTPS</span> <br>Always Redirect to HTTPS (Use when a valid SSL certificate is installed on <?php echo site_url();?>).</td>
        <td nowrap colspan='3'>
          <input type='hidden' name='option_name[]' value='https_redirect'>
          <select name='option_value[]' id='https_redirect' onchange="atb_change('atb_config',this.id,this.selectedIndex);">
            <option value='' disabled  <?php if($atb_setup_mode>0) echo 'selected';?>>HTTPS Redirect
            <option value='1' <?php if($https_redirect==1) echo 'selected';?>>Enabled (Recommended)
            <option value='0' <?php if($https_redirect==0 && $atb_setup_mode==0) echo 'selected';?>>Disabled (WP Default)
          </select>
          <span id='https_redirect-sel' class='sel_status'><?php echo $not_set;?></span>
        </td>
      </tr>

      <tr style='background:#F5F5F5'>
        <td>
          <span class='atb_title'><span class="dashicons dashicons-chart-line"></span>Capture Site Visits</span><br>Capture site visit statistics, chart traffic performance.<br><br>
          <div class='prof'>
            <?php if($atb_version_type=='PRO') { ?>
              <span class="dashicons dashicons-unlock" style='color:#b71b8a'></span> <span class='caps'>Pro</span> active<div style='margin-top:1em;font-size:.9em'>Now capturing visitors.<br><a href='<?php echo get_admin_url(null,'admin.php?page=admin-toolbox');?>&phv=1'>View recent hits</a></div><?php 
              } else { ?>
              <span class="dashicons dashicons-lock" style='color:#CCC'></span><a href="#atb_head" onclick="atb_expand('atb_pro');">Unlock <span class='caps'>Pro</span></a> to view itemized records by date, user & URL.<?php 
            } ?>
          </div>
        </td>
        <td nowrap>
          <input type='hidden' name='option_name[]' value='page_hit'>
          <select name='option_value[]' id='page_hit' onchange="if(this.options[this.selectedIndex].value<1) if(!confirm('Disabling this option will not permit \'2FA\' or \'Security Action\' features to work as designed.')) {this.value=1; return;} atb_change('atb_config',this.id,this.selectedIndex);">
            <option value='' disabled  <?php if($atb_setup_mode>0) echo 'selected';?>>Site Visit Tracking
            <option value='1' <?php if($page_hit==1) echo 'selected';?>>Enabled (Recommended)
            <option value='0' <?php if($page_hit==0 && $atb_setup_mode==0) echo 'selected';?>>Disabled (WP Default)
          </select>
          <span id='page_hit-sel' class='sel_status'><?php echo $not_set;?></span>
        </td>

        <td nowrap id='page_hit_role_td' <?php if($page_hit==0 && $atb_setup_mode==0) { ?>style='pointer-events:none;opacity:.3'<?php } ?>>
          <input type='hidden' name='option_name[]' value='page_hit_role'>
          <input type='hidden' name='option_value[]' id='page_hit_role'>
          <input type='hidden' name='option_name[]' value='page_hit_role_exclude'>

          <select name='option_value[]'>
            <option value='0'>Target these
            <option value='1' <?php if($page_hit_role_exclude==1) echo 'selected';?>>Exclude these
          </select>

          <select id='page_hit_role_select' onchange="atb_multiselect('page_hit','role',this.options[this.selectedIndex].value);">
            <option value='' selected disabled>Users
            <option value='*All'>All Users<?php echo atb_roles();?>
          </select><br>

          <div id='page_hit_role_items' class='atb_select_items'></div>
        </td>
        <td nowrap id='page_hit_page_td' <?php if($page_hit==0 && $atb_setup_mode==0) { ?>style='pointer-events:none;opacity:.3'<?php } ?>>
          <input type='hidden' name='option_name[]' value='page_hit_page_exclude'>
          <select name='option_value[]'>
            <option value='0'>Target these URL fragments
            <option value='1' <?php if($page_hit_page_exclude==1) echo 'selected';?>>Exclude these URLs fragments
          </select>

          <input type='hidden' name='option_name[]' value='page_hit_page_frag'>
          <br>URL fragments e.g. -ajax.php<br>
          <textarea name='option_value[]' style='width:100%;min-height:70px' title='Separate fragments by new line' placeholder='Separate fragments by new line'><?php echo str_replace(' ','&#10;',$page_hit_page_frag); ?></textarea>
        </td>
      </tr>

      <tr style='background:#FFF'>
        <td><span class='atb_title'><span class="dashicons dashicons-admin-network"></span>Two-Factor Authentication (2FA)</span> <br>2FA requires a physical token upon login.<br><br>
          <div class='prof'>
            <?php if($atb_version_type=='PRO') { ?><span class="dashicons dashicons-unlock" style='color:#b71b8a'></span> <span class='caps'>Pro</span> active<div style='margin-top:1em;font-size:.9em'>Upon next 2FA login, users will be asked to provide a mobile number.</div><?php 
            } else { ?>
            <span class="dashicons dashicons-lock" style='color:#CCC'></span><a href="#atb_head" onclick="atb_expand('atb_pro');">Unlock <span class='caps'>Pro</span></a> for 2FA via text.<?php 
          } ?>
          </div>
        </td>

        <td nowrap>
          <input type='hidden' name='option_name[]' value='dual_auth'>
          <select name='option_value[]' id='dual_auth' onchange="if(this.options[this.selectedIndex].value>0 && page_hit.value<1) {alert('Enable \'Capture Site Visits\' to make this feature available.'); this.value=0; return;} atb_change('atb_config',this.id,this.selectedIndex);">
            <option value='' disabled  <?php if($atb_setup_mode>0) echo 'selected';?>>Dual Authentication
            <option value='1' <?php if($dual_auth==1) echo 'selected';?>>Enabled (Recommended)
            <option value='0' <?php if($dual_auth==0 && $atb_setup_mode==0) echo 'selected';?>>Disabled (WP Default)
          </select>
          <span id='dual_auth-sel' class='sel_status'><?php echo $not_set;?></span>
        </td>

        <td nowrap id='dual_auth_role_td' <?php if($dual_auth==0 && $atb_setup_mode==0) { ?>style='pointer-events:none;opacity:.3'<?php } ?>>
          <input type='hidden' name='option_name[]' value='dual_auth_role'>
          <input type='hidden' name='option_value[]' id='dual_auth_role'>
          <input type='hidden' name='option_name[]' value='dual_auth_role_exclude'>

          <select name='option_value[]'>
            <option value='0'>Target these
            <option value='1' <?php if($dual_auth_role_exclude==1) echo 'selected';?>>Exclude these
          </select>

          <select id='dual_auth_role_select' onchange="atb_multiselect('dual_auth','role',this.options[this.selectedIndex].value);">
            <option value='' selected disabled>Users
            <option value='*All'>All Users<?php echo atb_roles();?>
          </select><br>

          <div id='dual_auth_role_items' class='atb_select_items'></div>
        </td>

        <td nowrap id='dual_auth_ip_exclude_post_td'>
          <input type='hidden' name='option_name[]' value='dual_auth_ip_exclude'>
          Allow these IPs to Bypass 2FA<br>e.g. <?php echo $admin_ip;?><br>
          <textarea name='option_value[]' style='width:100%;min-height:70px' title='Separate IPs by new line' placeholder='Separate IPs by new line'><?php echo str_replace(' ','&#10;',$dual_auth_ip_exclude); ?></textarea>
        </td>
      </tr>

      <tr style='background:#F5F5F5'>
        <td><span class='atb_title'><span class="dashicons dashicons-post-status"></span> Geo-Location Targeting</span><br>Limit features in target countries.<br><br>
          <div class='prof'>
            <?php if($atb_version_type=='PRO') { ?>
            <span class="dashicons dashicons-unlock" style='color:#b71b8a'></span> <span class='caps'>Pro</span> active<div style='margin-top:1em;font-size:.9em'>Log-ins and post-requests from target countries are now blocked.</div><?php 
            } else { ?>
            <span class="dashicons dashicons-lock" style='color:#CCC'></span><a href="#atb_head" onclick="atb_expand('atb_pro');">Unlock <span class='caps'>Pro</span></a> for geo-location targeting.<?php 
            } ?>
          </div>
        </td>

        <td nowrap <?php if($atb_version_type!='PRO') { ?>style='pointer-events:none;opacity:.3'<?php } ?>>
          <input type='hidden' name='option_name[]' value='disable_locs'>
          <select name='option_value[]' id='disable_locs' onchange="atb_change('atb_config',this.id,this.selectedIndex);">
            <option value='' disabled>Limit Locations
            <option value='1' <?php if($disable_locs==1) echo 'selected';?>>Enable for Login Only (Recommended)
            <option value='2' <?php if($disable_locs==2) echo 'selected';?>>Enable for ALL Requests
            <option value='0' <?php if($disable_locs==0 || $atb_setup_mode>0) echo 'selected';?>>Disabled (WP Default)
          </select>
        </td>

        <td nowrap id='disable_locs_ctry_td' <?php if(($disable_locs==0 && $atb_setup_mode==0) || $atb_version_type!='PRO') { ?>style='pointer-events:none;opacity:.3'<?php } ?>>
          <input type='hidden' name='option_name[]' value='disable_locs_ctry'>
          <input type='hidden' name='option_value[]' id='disable_locs_ctry'>
          <input type='hidden' name='option_name[]' value='disable_locs_exclude'>

          <select name='option_value[]'>
            <option value='0'>Target these countries
            <option value='1' <?php if($disable_locs_exclude==1) echo 'selected';?>>Exclude these
          </select>

          <select id='disable_locs_ctry_select' onchange="atb_multiselect('disable_locs','ctry',this.options[this.selectedIndex].value);">
            <option value='' selected disabled>Countries<?php
            if($atb_version_type=='PRO') {
              $cys=country_array();
              foreach($cys as $cd=>$cy){ ?><option value='<?php echo $cd;?>'><?php echo $cy;} 
            } ?>
          </select><br>

          <div id='disable_locs_ctry_items' class='atb_select_items'></div></td>
        </td>

        <td nowrap id='disable_locs_post_td' <?php if(($disable_locs==0 && $atb_setup_mode==0) || $atb_version_type!='PRO') { ?>style='pointer-events:none;opacity:.3'<?php } ?>>
          <input type='hidden' name='option_name[]' value='disable_locs_ip_exclude'>
          Allow these IPs to Bypass Geo-Location<br>e.g. <?php echo $admin_ip;?><br>
          <textarea name='option_value[]' style='width:100%;min-height:70px' title='Separate IPs by new line' placeholder='Separate IPs by new line'><?php echo str_replace(' ','&#10;',$disable_locs_ip_exclude); ?></textarea>
      </tr>

      <tr style='background:#FFF'>
        <td><span class='atb_title'><span class="dashicons dashicons-privacy"></span>Security Actions</span> <br>Auto-blacklist by reputation, brute force hits and malicious login/injection attempts.<br><br>
          <div class='prof'>
          <?php if($atb_version_type=='PRO') { ?>
            <span class="dashicons dashicons-unlock" style='color:#b71b8a'></span> <span class='caps'>Pro</span> active<div style='margin-top:1em;font-size:.9em'>
              <a href='<?php echo get_admin_url(null,'admin.php?page=admin-toolbox&clear_abu_cache');?>' onclick="if(!confirm('Are you sure you want to clear all cached IP reputations and reports?')) return false;">Clear all</a> cached reputations and reports. <?php 
              if(!class_exists('AIO_WP_Security')) { ?><br>For full feature logging, install <a href='https://wordpress.org/plugins/all-in-one-wp-security-and-firewall/' target='_blank'>WP Security</a>. <?php 
              } else { ?><a href='<?php echo get_admin_url(null,'admin.php?page=aiowpsec&tab=tab3');?>' target='_blank'>Security reports</a>.<?php }
            } else { ?>
            <span class="dashicons dashicons-lock" style='color:#CCC'></span><a href="#atb_head" onclick="atb_expand('atb_pro');">Unlock <span class='caps'>Pro</span></a> to enable automatic security actions.<?php 
          } ?>
          </div>
        </td>

        <td nowrap <?php if($atb_version_type!='PRO') { ?>style='pointer-events:none;opacity:.3'<?php } ?>>
          <input type='hidden' name='option_name[]' value='sec_actions'>
          <select name='option_value[]' id='sec_actions' onchange="if(this.options[this.selectedIndex].value>0 && page_hit.value<1) {alert('Enable \'Capture Site Visits\' to make this feature available.'); this.value=0; return;} atb_change('atb_config',this.id,this.selectedIndex);">
            <option value='' disabled>Security Monitoring
            <option value='1' <?php if($sec_actions==1) echo 'selected';?>>Enabled (Recommended)
            <option value='0' <?php if($sec_actions==0 || $atb_setup_mode>0) echo 'selected';?>>Disabled (WP Default)
          </select>
        </td>

        <td nowrap <?php if(($sec_actions==0 && $atb_setup_mode==0) || $atb_version_type!='PRO') { ?>style='pointer-events:none;opacity:.3'<?php } ?>>
          <input type='hidden' name='option_name[]' value='sec_actions_mute'>
          <select name='option_value[]' id='sec_actions_mute' onchange="atb_change('atb_config',this.id,this.selectedIndex);">
            <option value='' disabled<?php if($atb_setup_mode>0) echo 'selected';?>>Email Reports
            <option value='0' <?php if($sec_actions_mute==0 || $atb_setup_mode==0) echo 'selected';?>>Email Report to Admin
            <option value='1' <?php if($sec_actions_mute==1) echo 'selected';?>>Mute
          </select>
        </td>

        <td nowrap id='sec_actions_post_td' <?php if(($sec_actions==0 && $atb_setup_mode==0) || $atb_version_type!='PRO') { ?>style='pointer-events:none;opacity:.3'<?php } ?>>
          <input type='hidden' name='option_name[]' value='sec_actions_ip_exclude'>
          Allow these IPs to Bypass Security Monitoring<br>e.g. <?php echo $admin_ip;?><br>
          <textarea name='option_value[]' style='width:100%;min-height:70px' title='Separate IPs by new line' placeholder='Separate IPs by new line'><?php echo str_replace(' ','&#10;',$sec_actions_ip_exclude); ?></textarea>
      </tr>

      <tr style='background:#F5F5F5'>
        <td><span class='atb_title'><span class="dashicons dashicons-flag"></span>Error Reporting</span> <br>Log & email new errors.<br><br>
          <div class='prof'>
          <?php if($atb_version_type=='PRO') { ?>
            <span class="dashicons dashicons-unlock" style='color:#b71b8a'></span> <span class='caps'>Pro</span> active<div style='margin-top:1em;font-size:.9em'>
            <?php if($err_rpt>0 && !empty($err_rpt_path)) {?><a href='<?php echo get_admin_url(null,'/tools.php?page=atb_err_rpt');?>' target='_blank'>View recent errors</a><?php }
          } else { ?>
            <span class="dashicons dashicons-lock" style='color:#CCC'></span><a href="#atb_head" onclick="atb_expand('atb_pro');">Unlock <span class='caps'>Pro</span></a> to enable error reporting.<?php 
          } ?>
          </div>
        </td>

        <td nowrap <?php if($atb_version_type!='PRO') { ?>style='pointer-events:none;opacity:.3'<?php } ?>>
          <input type='hidden' name='option_name[]' value='err_rpt'>
          <select name='option_value[]' id='err_rpt' onchange="if(this.options[this.selectedIndex].value>0 && page_hit.value<1) {alert('Enable \'Capture Site Visits\' to make this feature available.'); this.value=0; return;} atb_change('atb_config',this.id,this.selectedIndex);">
            <option value='' disabled>Security Monitoring
            <option value='2' <?php if($err_rpt==2) echo 'selected';?>>Log & Email Errors
            <option value='1' <?php if($err_rpt==1) echo 'selected';?>>Log Errors Only
            <option value='0' <?php if($err_rpt==0 || $atb_setup_mode>0) echo 'selected';?>>Disabled (WP Default)
          </select>
        </td>

        <td nowrap <?php if(($err_rpt==0 && $atb_setup_mode==0) || $atb_version_type!='PRO') { ?>style='pointer-events:none;opacity:.3'<?php } ?>>
          <input type='hidden' name='option_name[]' value='err_rpt_path'>
          Look for new errors in this file:<?php if(empty($err_rpt_path)) { $abs=str_replace('/public_html/','',ABSPATH); echo "<div style='font-size:.8em;white-space:break-spaces;'>e.g. $abs/logs/apache_wordpress-677085-1234567.cloudwaysapps.com.error.log</div>";}?><br>
          <textarea name='option_value[]' style='width:100%;min-height:70px' title='Separate IPs by new line' placeholder='File path'><?php echo $err_rpt_path; ?></textarea>
        </td>

        <td nowrap id='err_rpt_post_td' <?php if(($err_rpt==0 && $atb_setup_mode==0) || $atb_version_type!='PRO') { ?>style='pointer-events:none;opacity:.3'<?php } ?>>
          <input type='hidden' name='option_name[]' value='err_rpt_exclude'>
          Exclude phrases from email alerts<br>e.g. Cannot serve, log buffer, client denied<br>
          <textarea name='option_value[]' style='width:100%;min-height:70px' title='Separate phrases by new line' placeholder='Separate phrases by new line'><?php echo $err_rpt_exclude; ?></textarea>
        </td>
      </tr>

      <tr style='background:#FFF'>
        <td><span class='atb_title'><span class="dashicons dashicons-list-view"></span>Hide Items</span> <br>Hide items using HTML ID or Class.<br>
        <td nowrap>
          <input type='hidden' name='option_name[]' value='hide_item'>
          <select name='option_value[]' id='hide_item' onchange="atb_change('atb_config',this.id,this.selectedIndex);">
            <option value='' disabled  <?php if($atb_setup_mode>0) echo 'selected';?>>Hide Items
            <option value='1' <?php if($hide_item==1) echo 'selected';?>>Enabled (Recommended)
            <option value='0' <?php if($hide_item==0 && $atb_setup_mode==0) echo 'selected';?>>Disabled (WP Default)
          </select>
          <span id='hide_item-sel' class='sel_status'><?php echo $not_set;?></span>
        </td>

        <td nowrap id='hide_item_role_td' <?php if($hide_item==0 && $atb_setup_mode==0) { ?>style='pointer-events:none;opacity:.3'<?php } ?>>
          <input type='hidden' name='option_name[]' value='hide_item_role'>
          <input type='hidden' name='option_value[]' id='hide_item_role'>
          <input type='hidden' name='option_name[]' value='hide_item_role_exclude'>

          <select name='option_value[]'>
            <option value='0'>Target these
            <option value='1' <?php if($hide_item_role_exclude==1) echo 'selected';?>>Exclude these
          </select>

          <select id='hide_item_role_select' onchange="atb_multiselect('hide_item','role',this.options[this.selectedIndex].value);">
            <option value='' selected disabled>Users
            <option value='*All'>All Users<?php echo atb_roles();?>
          </select><br>

          <div id='hide_item_role_items' class='atb_select_items'></div>
        </td>

        <td nowrap id='hide_item_id_td'>
          <input type='hidden' name='option_name[]' value='hide_item_id'>
          Restrict these HTML IDs or Classes<br><br>
          <b style='color:#000'>Based on Role</b><br>
          <textarea name='option_value[]' style='width:100%;min-height:70px' title='IDs or Classes' placeholder='IDs or Classes'><?php echo str_replace(' ','&#10;',$hide_item_id); ?></textarea>
          <select style='display:block' onchange="previousElementSibling.innerHTML=previousElementSibling.innerHTML+','+this.value;this.value='';">
            <option selected value=''>Select ID/Class
            <option value='#wpadminbar'>Admin Bar 
            <option value='#wp-admin-bar-my-sites'>Admin Bar Multisite 
            <option value='#wp-admin-bar-customize'>Admin Bar Customize
            <option value='#wp-admin-bar-comments'>Admin Bar Comments
            <option value='#wp-admin-bar-updates'>Admin Bar Updates
            <option value='#wp-admin-bar-new-content'>Admin Bar New Item
            <option value='#wp-admin-bar-edit'>Admin Bar Edit
            <option value='#menu-posts'>Admin Menu Posts
            <option value='#menu-media'>Admin Menu Media
            <option value='#menu-pages'>Admin Menu Pages
            <option value='#menu-comments'>Admin Menu Comments
            <option value='#menu-appearance'>Admin Menu Appearance
            <option value='#menu-tools'>Admin Menu Tools
            <option value='#menu-settings'>Admin Menu Settings
          </select>
          <br><br>

          <input type='hidden' name='option_name[]' value='hide_item_all'>
          <b style='color:#000'>For Everyone</b><br>
          <textarea name='option_value[]' style='width:100%;min-height:70px' title='IDs or Classes' placeholder='IDs or Classes'><?php echo str_replace(' ','&#10;',$hide_item_all); ?></textarea>
          <select style='display:block' onchange="previousElementSibling.innerHTML=previousElementSibling.innerHTML+','+this.value;this.value='';">
            <option selected value=''>Select ID/Class
            <option value='#wpadminbar'>Admin Bar 
            <option value='#wp-admin-bar-my-sites'>Admin Bar Multisite 
            <option value='#wp-admin-bar-customize'>Admin Bar Customize
            <option value='#wp-admin-bar-comments'>Admin Bar Comments
            <option value='#wp-admin-bar-updates'>Admin Bar Updates
            <option value='#wp-admin-bar-new-content'>Admin Bar New Item
            <option value='#wp-admin-bar-edit'>Admin Bar Edit
            <option value='#menu-posts'>Admin Menu Posts
            <option value='#menu-media'>Admin Menu Media
            <option value='#menu-pages'>Admin Menu Pages
            <option value='#menu-comments'>Admin Menu Comments
            <option value='#menu-appearance'>Admin Menu Appearance
            <option value='#menu-tools'>Admin Menu Tools
            <option value='#menu-settings'>Admin Menu Settings
          </select>
        </td>
      </tr>

      <tr style='background:#F5F5F5'>
        <td><span class='atb_title'><span class="dashicons dashicons-redo"></span>Redirect to Homepage</span> <br>Redirect home based on URL.<br>
        <td nowrap>
          <input type='hidden' name='option_name[]' value='redirect_hp'>
          <select name='option_value[]' id='redirect_hp' onchange="atb_change('atb_config',this.id,this.selectedIndex);">
            <option value='' disabled  <?php if($atb_setup_mode>0) echo 'selected';?>>Redirect to Dashboard
            <option value='1' <?php if($redirect_hp==1) echo 'selected';?>>Enabled (Recommended)
            <option value='0' <?php if($redirect_hp==0 && $atb_setup_mode==0) echo 'selected';?>>Disabled (WP Default)
          </select>
          <span id='redirect_hp-sel' class='sel_status'><?php echo $not_set;?></span>
        </td>

        <td nowrap id='redirect_hp_role_td' <?php if($redirect_hp==0 && $atb_setup_mode==0) { ?>style='pointer-events:none;opacity:.3'<?php } ?>>
          <input type='hidden' name='option_name[]' value='redirect_hp_role'>
          <input type='hidden' name='option_value[]' id='redirect_hp_role'>
          <input type='hidden' name='option_name[]' value='redirect_hp_role_exclude'>

          <select name='option_value[]'>
            <option value='0'>Target these
            <option value='1' <?php if($redirect_hp_role_exclude==1) echo 'selected';?>>Exclude these
          </select>

          <select id='redirect_hp_role_select' onchange="atb_multiselect('redirect_hp','role',this.options[this.selectedIndex].value);">
            <option value='' selected disabled>Users
            <option value='*All'>All Users<?php echo atb_roles();?>
          </select><br>

          <div id='redirect_hp_role_items' class='atb_select_items'></div>
        </td>
        
        <td nowrap id='redirect_hp_page_td'>
          <input type='hidden' name='option_name[]' value='redirect_hp_page'>
          Redirect home from these URL fragments<br>
          <textarea name='option_value[]' style='width:100%;min-height:70px' title='Separate URLs by new line' placeholder="/author
/?author
/feed/"><?php echo str_replace(' ','&#10;',$redirect_hp_page); ?></textarea>
        </td>
      </tr>

      <tr style='background:#FFF'>
        <td><span class='atb_title'><span class="dashicons dashicons-redo"></span>Redirect to Dashboard</span> <br>Redirect to /wp-admin based on role.<br>
        <td nowrap>
          <input type='hidden' name='option_name[]' value='redirect_db'>
          <select name='option_value[]' id='redirect_db' onchange="atb_change('atb_config',this.id,this.selectedIndex);">
            <option value='' disabled  <?php if($atb_setup_mode>0) echo 'selected';?>>Redirect to Dashboard
            <option value='1' <?php if($redirect_db==1) echo 'selected';?>>Enabled (Recommended)
            <option value='0' <?php if($redirect_db==0 && $atb_setup_mode==0) echo 'selected';?>>Disabled (WP Default)
          </select>
          <span id='redirect_db-sel' class='sel_status'><?php echo $not_set;?></span>
        </td>

        <td nowrap id='redirect_db_role_td' <?php if($redirect_db==0 && $atb_setup_mode==0) { ?>style='pointer-events:none;opacity:.3'<?php } ?>>
          <input type='hidden' name='option_name[]' value='redirect_db_role'>
          <input type='hidden' name='option_value[]' id='redirect_db_role'>
          <input type='hidden' name='option_name[]' value='redirect_db_role_exclude'>

          <select name='option_value[]'>
            <option value='0'>Target these
            <option value='1' <?php if($redirect_db_role_exclude==1) echo 'selected';?>>Exclude these
          </select>

          <select id='redirect_db_role_select' onchange="atb_multiselect('redirect_db','role',this.options[this.selectedIndex].value);">
            <option value='' selected disabled>Users
            <option value='*All'>All Users<?php echo atb_roles();?>
          </select><br>
          
          <div id='redirect_db_role_items' class='atb_select_items'></div>
        </td>

        <td nowrap id='redirect_db_page_td'>
          <input type='hidden' name='option_name[]' value='redirect_db_page'>
          Redirect to /wp-admin from these pages<br>
          <textarea name='option_value[]' style='width:100%;min-height:70px' title='Separate Pages by new line' placeholder='Separate Pages by new line'><?php echo str_replace(' ','&#10;',$redirect_db_page); ?></textarea>
          <select style='display:block' onchange="previousElementSibling.innerHTML=previousElementSibling.innerHTML+','+this.value;this.value='';">
            <option selected value=''>Select Page
            <option value='edit.php'>Edit Post/Page
            <option value='post-new.php'>New Post/Page
            <option value='edit-comments.php'>Edit Comments
            <option value='themes.php'>Themes
            <option value='theme-editor.php'>Theme Editor
            <option value='plugins.php'>Plugins
            <option value='plugin-editor.php'>Plugin Editor
            <option value='plugin-install.php'>Plugin Install
            <option value='users.php'>Users
            <option value='user-new.php'>Add User
            <option value='tools.php'>Tools
            <option value='options-general.php'>Settings
            <option value='media-new.php'>Add Media
            <option value='upload.php'>Media
            <option value='widgets.php'>Widgets
          </select>
        </td>
      </tr>

      <tr style='background:#F5F5F5'>
        <td><span class='atb_title'><span class="dashicons dashicons-format-chat"></span> Disable Notices</span><br>Disable all update-related notifications in WordPress.</td>
        <td nowrap>
          <input type='hidden' name='option_name[]' value='disable_notices'>
          <select name='option_value[]' id='disable_notices' onchange="atb_change('atb_config',this.id,this.selectedIndex);">
            <option value='' disabled  <?php if($atb_setup_mode>0) echo 'selected';?>>Disable Notices
            <option value='1' <?php if($disable_notices==1) echo 'selected';?>>Enabled (Recommended)
            <option value='0' <?php if($disable_notices==0 && $atb_setup_mode==0) echo 'selected';?>>Disabled (WP Default)
          </select>
          <span id='disable_notices-sel' class='sel_status'><?php echo $not_set;?></span>
        </td>
        
        <td nowrap id='disable_notices_role_td' <?php if($disable_notices==0 && $atb_setup_mode==0) { ?>style='pointer-events:none;opacity:.3'<?php } ?>>
          <input type='hidden' name='option_name[]' value='disable_notices_role'>
          <input type='hidden' name='option_value[]' id='disable_notices_role'>
          <input type='hidden' name='option_name[]' value='disable_notices_role_exclude'>
          
          <select name='option_value[]'>
            <option value='0'>Target these
            <option value='1' <?php if($disable_notices_role_exclude==1) echo 'selected';?>>Exclude these
          </select>
          
          <select id='disable_notices_role_select' onchange="atb_multiselect('disable_notices','role',this.options[this.selectedIndex].value);">
            <option value='' selected disabled>Users
            <option value='*All'>All Users<?php echo atb_roles();?>
          </select><br>
          
          <div id='disable_notices_role_items' class='atb_select_items'></div>
        </td>
        <td></td>
      </tr>

      <tr style='background:#FFF'>
        <td><span class='atb_title'><span class="dashicons dashicons-list-view"></span> Search Post Meta</span><br>Search posts by post meta in wp-admin.</td>
        <td nowrap>
          <input type='hidden' name='option_name[]' value='search_post_meta'>
          <select name='option_value[]' id='search_post_meta' onchange="atb_change('atb_config',this.id,this.selectedIndex);">
            <option value='' disabled  <?php if($atb_setup_mode>0) echo 'selected';?>>Search Post Meta
            <option value='1' <?php if($search_post_meta==1) echo 'selected';?>>Enabled (Recommended)
            <option value='0' <?php if($search_post_meta==0 && $atb_setup_mode==0) echo 'selected';?>>Disabled (WP Default)
          </select>
          <span id='search_post_meta-sel' class='sel_status'><?php echo $not_set;?></span>
        </td>

        <td nowrap id='search_post_meta_role_td' <?php if($search_post_meta==0 && $atb_setup_mode==0) { ?>style='pointer-events:none;opacity:.3'<?php } ?>>
          <input type='hidden' name='option_name[]' value='search_post_meta_role'>
          <input type='hidden' name='option_value[]' id='search_post_meta_role'>
          <input type='hidden' name='option_name[]' value='search_post_meta_role_exclude'>

          <select name='option_value[]'>
            <option value='0'>Target these
            <option value='1' <?php if($search_post_meta_role_exclude==1) echo 'selected';?>>Exclude these
          </select>

          <select id='search_post_meta_role_select' onchange="atb_multiselect('search_post_meta','role',this.options[this.selectedIndex].value);">
            <option value='' selected disabled>Users
            <option value='*All'>All Users<?php echo atb_roles();?>
          </select><br>

          <div id='search_post_meta_role_items' class='atb_select_items'></div>
        </td>

        <td nowrap id='search_post_meta_post_td' <?php if($search_post_meta==0 && $atb_setup_mode==0) { ?>style='pointer-events:none;opacity:.3'<?php } ?>>
          <input type='hidden' name='option_name[]' value='search_post_meta_post'>
          <input type='hidden' name='option_value[]' id='search_post_meta_post'>
          <input type='hidden' name='option_name[]' value='search_post_meta_post_exclude'>

          <select name='option_value[]'>
            <option value='0'>Target these
            <option value='1' <?php if($search_post_meta_post_exclude==1) echo 'selected';?>>Exclude these
          </select>

          <select id='search_post_meta_post_select' onchange="atb_multiselect('search_post_meta','post',this.options[this.selectedIndex].value);">
            <option value='' selected disabled>Posts
            <option value='*All'>All Posts<?php
            foreach(get_post_types('','names') as $post_type):?>
              <option value='<?php echo $post_type;?>'><?php echo $post_type;
            endforeach;?>
          </select><br>

          <div id='search_post_meta_post_items' class='atb_select_items'></div>
        </td>
      </tr>

      <tr style='background:#F5F5F5'>
        <td><span class='atb_title'><span class="dashicons dashicons-list-view"></span> Hide Bulk Actions</span><br>Hide list bulk actions, quick edit, and delete options. This features hides these actions only in lists, it does not remove functionality.</td>
        <td nowrap>
          <input type='hidden' name='option_name[]' value='hide_bulk_action'>
          <select name='option_value[]' id='hide_bulk_action' onchange="atb_change('atb_config',this.id,this.selectedIndex);">
            <option value='' disabled  <?php if($atb_setup_mode>0) echo 'selected';?>>Hide Bulk Actions
            <option value='1' <?php if($hide_bulk_action==1) echo 'selected';?>>Enabled (Recommended)
            <option value='0' <?php if($hide_bulk_action==0 && $atb_setup_mode==0) echo 'selected';?>>Disabled (WP Default)
          </select>
          <span id='hide_bulk_action-sel' class='sel_status'><?php echo $not_set;?></span>
        </td>

        <td nowrap id='hide_bulk_action_role_td' <?php if($hide_bulk_action==0 && $atb_setup_mode==0) { ?>style='pointer-events:none;opacity:.3'<?php } ?>>
          <input type='hidden' name='option_name[]' value='hide_bulk_action_role'>
          <input type='hidden' name='option_value[]' id='hide_bulk_action_role'>
          <input type='hidden' name='option_name[]' value='hide_bulk_action_role_exclude'>

          <select name='option_value[]'>
            <option value='0'>Target these
            <option value='1' <?php if($hide_bulk_action_role_exclude==1) echo 'selected';?>>Exclude these
          </select>

          <select id='hide_bulk_action_role_select' onchange="atb_multiselect('hide_bulk_action','role',this.options[this.selectedIndex].value);">
            <option value='' selected disabled>Users
            <option value='*All'>All Users<?php echo atb_roles();?>
          </select><br>

          <div id='hide_bulk_action_role_items' class='atb_select_items'></div>
        </td>

        <td nowrap id='hide_bulk_action_post_td' <?php if($hide_bulk_action==0 && $atb_setup_mode==0) { ?>style='pointer-events:none;opacity:.3'<?php } ?>>
          <input type='hidden' name='option_name[]' value='hide_bulk_action_post'>
          <input type='hidden' name='option_value[]' id='hide_bulk_action_post'>
          <input type='hidden' name='option_name[]' value='hide_bulk_action_post_exclude'>

          <select name='option_value[]'>
            <option value='0'>Target these
            <option value='1' <?php if($hide_bulk_action_post_exclude==1) echo 'selected';?>>Exclude these
          </select>

          <select id='hide_bulk_action_post_select' onchange="atb_multiselect('hide_bulk_action','post',this.options[this.selectedIndex].value);">
            <option value='' selected disabled>Posts
            <option value='*All'>All Posts<?php
            foreach(get_post_types('','names') as $post_type):?>
              <option value='<?php echo $post_type;?>'><?php echo $post_type;
            endforeach;?>
          </select><br>

          <div id='hide_bulk_action_post_items' class='atb_select_items'></div>
        </td>
      </tr>

      <tr style='background:#FFF'>
        <td><span class='atb_title'><span class="dashicons dashicons-plus-alt"></span>Hide New Post Button</span><br>Hide new post buttons. This visually hides the button, it does not remove functionality.</td>
        <td nowrap>
          <input type='hidden' name='option_name[]' value='hide_new_btn'>
          <select name='option_value[]' id='hide_new_btn' onchange="atb_change('atb_config',this.id,this.selectedIndex);">
            <option value='' disabled  <?php if($atb_setup_mode>0) echo 'selected';?>>Hide New Post Button
            <option value='1' <?php if($hide_new_btn==1) echo 'selected';?>>Enabled
            <option value='0' <?php if($hide_new_btn==0 && $atb_setup_mode==0) echo 'selected';?>>Disabled (WP Default)
          </select>
          <span id='hide_new_btn-sel' class='sel_status'><?php echo $not_set;?></span>
        </td>

        <td nowrap id='hide_new_btn_role_td' <?php if($hide_new_btn==0 && $atb_setup_mode==0) { ?>style='pointer-events:none;opacity:.3'<?php } ?>>
          <input type='hidden' name='option_name[]' value='hide_new_btn_role'>
          <input type='hidden' name='option_value[]' id='hide_new_btn_role'>
          <input type='hidden' name='option_name[]' value='hide_new_btn_role_exclude'>

          <select name='option_value[]'>
            <option value='0'>Target these
            <option value='1' <?php if($hide_new_btn_role_exclude==1) echo 'selected';?>>Exclude these
          </select>

          <select id='hide_new_btn_role_select' onchange="atb_multiselect('hide_new_btn','role',this.options[this.selectedIndex].value);">
            <option value='' selected disabled>Users
            <option value='*All'>All Users<?php echo atb_roles();?>
          </select><br>

          <div id='hide_new_btn_role_items' class='atb_select_items'></div>
        </td>

        <td nowrap id='hide_new_btn_post_td' <?php if($hide_new_btn==0 && $atb_setup_mode==0) { ?>style='pointer-events:none;opacity:.3'<?php } ?>>
          <input type='hidden' name='option_name[]' value='hide_new_btn_post'>
          <input type='hidden' name='option_value[]' id='hide_new_btn_post'>
          <input type='hidden' name='option_name[]' value='hide_new_btn_post_exclude'>

          <select name='option_value[]'>
            <option value='0'>Target these
            <option value='1' <?php if($hide_new_btn_post_exclude==1) echo 'selected';?>>Exclude these
          </select>

          <select id='hide_new_btn_post_select' onchange="atb_multiselect('hide_new_btn','post',this.options[this.selectedIndex].value);">
            <option value='' selected disabled>Posts
            <option value='*All'>All Posts<?php
            foreach(get_post_types('','names') as $post_type):?>
              <option value='<?php echo $post_type;?>'><?php echo $post_type;
            endforeach;?>
          </select><br>

          <div id='hide_new_btn_post_items' class='atb_select_items'></div>
        </td>
      </tr>

      <tr style='background:#F5F5F5'>
        <td><span class='atb_title'><span class="dashicons dashicons-admin-users"></span> Hide Roles</span><br>Select roles to hide in the user editor. Limit roles that have access to other roles.</td>
        <td nowrap>
          <input type='hidden' name='option_name[]' value='limit_usereditor_role'>
          <select name='option_value[]' id='limit_usereditor_role' onchange="atb_change('atb_config',this.id,this.selectedIndex);">
            <option value='' disabled  <?php if($atb_setup_mode>0) echo 'selected';?>>Hide Roles
            <option value='1' <?php if($limit_usereditor_role==1) echo 'selected';?>>Enabled (Recommended)
            <option value='0' <?php if($limit_usereditor_role==0 && $atb_setup_mode==0) echo 'selected';?>>Disabled (WP Default)
          </select>
          <span id='limit_usereditor_role-sel' class='sel_status'><?php echo $not_set;?></span>
        </td>

        <td nowrap id='limit_usereditor_role_role_td' <?php if($limit_usereditor_role==0 && $atb_setup_mode==0) { ?>style='pointer-events:none;opacity:.3'<?php } ?>>
          <input type='hidden' name='option_name[]' value='limit_usereditor_hiderole_role'>
          <input type='hidden' name='option_value[]' id='limit_usereditor_hiderole_role'>
          <input type='hidden' name='option_name[]' value='limit_usereditor_role_role_exclude'>

          <select name='option_value[]'>
            <option value='0'>Target these
            <option value='1' <?php if($limit_usereditor_role_role_exclude==1) echo 'selected';?>>Exclude these
          </select>

          <select id='limit_usereditor_role_role_select' onchange="atb_multiselect('limit_usereditor_role','role',this.options[this.selectedIndex].value);">
            <option value='' selected disabled>Users
            <option value='*All'>All Users<?php echo atb_roles();?>
          </select><br>

          <div id='limit_usereditor_role_role_items' class='atb_select_items'></div>
          <input type='hidden' name='option_name[]' value='limit_usereditor_role_role'>
          <input type='hidden' name='option_value[]' id='limit_usereditor_role_role'>
        </td>

        <td nowrap id='limit_usereditor_role_post_td' <?php if($limit_usereditor_role==0 && $atb_setup_mode==0) { ?>style='pointer-events:none;opacity:.3'<?php } ?>>
          <select id='limit_usereditor_hiderole_role_select' onchange="atb_multiselect('limit_usereditor_hiderole','role',this.options[this.selectedIndex].value);">
            <option value='' selected disabled>Hide These Roles on User Edit Screen
          <option value='*All'>All Roles<?php
            foreach(get_editable_roles() as $role_name=>$role_info):?>
              <option value='<?php echo $role_name;?>'><?php echo $role_name;
            endforeach;?>
          </select><br>

          <div id='limit_usereditor_hiderole_role_items' class='atb_select_items'></div>
        </td>
      </tr>

      <tr style='background:#FFF'>
        <td><span class='atb_title'><span class="dashicons dashicons-images-alt2"></span> Limit Media Size</span><br>Set the maximum size for new images uploaded via the media library.</td>
        <td nowrap>
          <input type='hidden' name='option_name[]' value='limit_img_size'>
          <select name='option_value[]' id='limit_img_size' onchange="atb_change('atb_config',this.id,this.selectedIndex);">
            <option value='' disabled  <?php if($atb_setup_mode>0) echo 'selected';?>>Hide Roles
            <option value='1' <?php if($limit_img_size==1) echo 'selected';?>>Enabled (Recommended)
            <option value='0' <?php if($limit_img_size==0 && $atb_setup_mode==0) echo 'selected';?>>Disabled (WP Default)
          </select>
          <span id='limit_img_size-sel' class='sel_status'><?php echo $not_set;?></span>
        </td>

        <td nowrap id='limit_img_size_role_td' <?php if($limit_img_size==0 && $atb_setup_mode==0) { ?>style='pointer-events:none;opacity:.3'<?php } ?>>
          <input type='hidden' name='option_name[]' value='limit_img_size_role'>
          <input type='hidden' name='option_value[]' id='limit_img_size_role'>
          <input type='hidden' name='option_name[]' value='limit_img_size_role_exclude'>

          <select name='option_value[]'>
            <option value='0'>Target these
            <option value='1' <?php if($limit_img_size_role_exclude==1) echo 'selected';?>>Exclude these
          </select>

          <select id='limit_img_size_role_select' onchange="atb_multiselect('limit_img_size','role',this.options[this.selectedIndex].value);">
            <option value='' selected disabled>Users
            <option value='*All'>All Users<?php echo atb_roles();?>
          </select><br>

          <div id='limit_img_size_role_items' class='atb_select_items'></div>
        </td>

        <td nowrap id='limit_img_size_post_td' <?php if($limit_img_size==0 && $atb_setup_mode==0) { ?>style='pointer-events:none;opacity:.3'<?php } ?>>
          <input type='hidden' name='option_name[]' value='limit_img_size_kb'>
          <select name='option_value[]'>
            <option value='' selected disabled>Max Image Size
            <option value='212'  <?php if($limit_img_size_kb==212) echo 'selected';?>>200kb
            <option value='512'  <?php if($limit_img_size_kb==512) echo 'selected';?>>500kb
            <option value='1024' <?php if($limit_img_size_kb==1024) echo 'selected';?>>1MB
            <option value='2024' <?php if($limit_img_size_kb==2024) echo 'selected';?>>2MB
            <option value='5024' <?php if($limit_img_size_kb==5024) echo 'selected';?>>5MB
            <option value='10024' <?php if($limit_img_size_kb==10024) echo 'selected';?>>10MB
          </select>
        </td>
      </tr>

      <tr style='background:#F5F5F5'>
        <td><span class='atb_title'><span class="dashicons dashicons-media-code"></span>Disable XML-RPC</span> <br>XML-RPC can be used to enable a brute force attack on your site.</a>.</td>
        <td nowrap colspan='3'>
          <input type='hidden' name='option_name[]' value='disable_xmlrpc'>
          <select name='option_value[]' id='disable_xmlrpc' onchange="atb_change('atb_config',this.id,this.selectedIndex);">
            <option value='' disabled  <?php if($atb_setup_mode>0) echo 'selected';?>>Disable XML-RPC
            <option value='1' <?php if($disable_xmlrpc==1) echo 'selected';?>>Disabled (Recommended)
            <option value='0' <?php if($disable_xmlrpc==0 && $atb_setup_mode==0) echo 'selected';?>>Enabled (WP Default)
          </select>
          <span id='disable_xmlrpc-sel' class='sel_status'><?php echo $not_set;?></span>
        </td>
      </tr>

      <tr style='background:#FFF'>
        <td><span class='atb_title'><span class="dashicons dashicons-media-code"></span>Admin Email Check</span> <br>Frequency of Admin Email Verification Check.</a>.</td>
        <td nowrap colspan='3'>
          <input type='hidden' name='option_name[]' value='admin_email_check'>
          <select name='option_value[]' id='admin_email_check' onchange="atb_change('atb_config',this.id,this.selectedIndex);">
            <option value='' disabled  <?php if($atb_setup_mode>0) echo 'selected';?>>Admin Email Check
            <option value='-1' <?php if($admin_email_check<0) echo 'selected';?>>Disabled
            <option value='6' <?php if($admin_email_check==6) echo 'selected';?>>Every 6 Months (WP Default)
            <option value='12' <?php if($admin_email_check==12) echo 'selected';?>>Every 12 Months
            <option value='24' <?php if($admin_email_check==24) echo 'selected';?>>Every 24 Months
          </select>
          <span id='admin_email_check-sel' class='sel_status'><?php echo $not_set;?></span>
        </td>
      </tr>

      <tr>
        <td>
          <input id='atb_save' type='submit' value='Save' onclick="atb_change('atb_config',2,2); return false;">
        </td>
      </tr>

    </table>
  </form>

  <div id='input_saved'>Saved</div>
</div>

<script type='text/javascript'>
  var terms_clicked=0;<?php 
  if($atb_version_type=='PRO') {$pro=get_option('atb_pro'); if($pro=='new') update_option('atb_pro','yes','no');} else $pro='no';
  if($pro=='new' || ($atb_version_type=='GPL' && isset($_GET['updated']) && function_exists('gzcompress'))) { ?>
    var atb_version_Interval=setInterval(function() {
      if(document.readyState==='complete') {clearInterval(atb_version_Interval);atb_expand('atb_pro');window.location.hash='#atb_pro';}
    },200);<?php 
  } ?>

  if('<?php echo $input_saved;?>'>0) {
    save_msg=atb_getE('input_saved');
    save_msg.style.opacity=1;
    save_msg.style.display='block';
    save_msg.innerHTML='Saved';
    setTimeout(function(){save_msg.style.opacity=0;},2000);
    setTimeout(function(){save_msg.style.display='none';},3000);
  }
  function atb_getE(e) {return document.getElementById(e);}

  var rolemultiselect_Interval=setInterval(function() {
    if(document.readyState==='complete') {
      clearInterval(rolemultiselect_Interval);

      var post_array='<?php echo $hide_new_btn_post?>'.split(',');
      if(post_array) for(var i=0; i<post_array.length; i++) if(post_array[i].length>1) atb_multiselect('hide_new_btn','post',post_array[i]);

      post_array='<?php echo $search_post_meta_post?>'.split(',');
      if(post_array) for(var i=0; i<post_array.length; i++) if(post_array[i].length>1) atb_multiselect('search_post_meta','post',post_array[i]);

      post_array='<?php echo $hide_bulk_action_post?>'.split(',');
      if(post_array) for(var i=0; i<post_array.length; i++) if(post_array[i].length>1) atb_multiselect('hide_bulk_action','post',post_array[i]);

      role_array='<?php echo $hide_new_btn_role;?>'.split(',');
      if(role_array) for(var i=0; i<role_array.length; i++) if(role_array[i].length>1) atb_multiselect('hide_new_btn','role',role_array[i]);

      role_array='<?php echo $search_post_meta_role;?>'.split(',');
      if(role_array) for(var i=0; i<role_array.length; i++) if(role_array[i].length>1) atb_multiselect('search_post_meta','role',role_array[i]);

      role_array='<?php echo $hide_bulk_action_role;?>'.split(',');
      if(role_array) for(var i=0; i<role_array.length; i++) if(role_array[i].length>1) atb_multiselect('hide_bulk_action','role',role_array[i]);

      role_array='<?php echo $limit_usereditor_role_role;?>'.split(',');
      if(role_array) for(var i=0; i<role_array.length; i++) if(role_array[i].length>1) atb_multiselect('limit_usereditor_role','role',role_array[i]);

      role_array='<?php echo $limit_usereditor_hiderole_role;?>'.split(',');
      if(role_array) for(var i=0; i<role_array.length; i++) if(role_array[i].length>1) atb_multiselect('limit_usereditor_hiderole','role',role_array[i]);

      role_array='<?php echo $limit_img_size_role;?>'.split(',');
      if(role_array) for(var i=0; i<role_array.length; i++) if(role_array[i].length>1) atb_multiselect('limit_img_size','role',role_array[i]);

      role_array='<?php echo $disable_notices_role;?>'.split(',');
      if(role_array) for(var i=0; i<role_array.length; i++) if(role_array[i].length>1) atb_multiselect('disable_notices','role',role_array[i]);

      role_array='<?php echo $page_hit_role;?>'.split(',');
      if(role_array) for(var i=0; i<role_array.length; i++) if(role_array[i].length>1) atb_multiselect('page_hit','role',role_array[i]);

      role_array='<?php echo $dual_auth_role;?>'.split(',');
      if(role_array) for(var i=0; i<role_array.length; i++) if(role_array[i].length>1) atb_multiselect('dual_auth','role',role_array[i]);

      role_array='<?php echo $hide_item_role;?>'.split(',');
      if(role_array) for(var i=0; i<role_array.length; i++) if(role_array[i].length>1) atb_multiselect('hide_item','role',role_array[i]);

      role_array='<?php echo $redirect_hp_role;?>'.split(',');
      if(role_array) for(var i=0; i<role_array.length; i++) if(role_array[i].length>1) atb_multiselect('redirect_hp','role',role_array[i]);

      role_array='<?php echo $redirect_db_role;?>'.split(',');
      if(role_array) for(var i=0; i<role_array.length; i++) if(role_array[i].length>1) atb_multiselect('redirect_db','role',role_array[i]);

      role_array='<?php echo $disable_locs_ctry;?>'.split(',');
      if(role_array) for(var i=0; i<role_array.length; i++) if(role_array[i].length>1) atb_multiselect('disable_locs','ctry',role_array[i]);
    }
  },500);

  function atb_multiselect(input_id,input_type,sel) {
    var input_field=atb_getE(input_id+'_'+input_type);
    var div_display=atb_getE(input_id+'_'+input_type+'_items');
    if(sel=='*All') {
      input_field.value=sel;
      div_display.innerHTML='<span class="atb_title">'+sel+'</span>';
      atb_getE(input_id+'_'+input_type+'_select').selectedIndex=0;
      return false;
    } else {
      input_field.value=input_field.value.replace('*All','');
      div_display.innerHTML=div_display.innerHTML.replace('<span class="atb_title">'+'*All','</span>');
    }
    var c=','; //comma
    if(input_field.value.indexOf(c+sel+c)<0) { //add value
      input_field.value=input_field.value+c+sel+c;
      sel_div='<div class="atb_select_item" title="delete" onclick="atb_multiselect(\''+input_id+'\',\''+input_type+'\',\''+sel+'\');" id="'+input_id+'_'+input_type+'-'+sel+'">'+sel+'</div>';
      if(!atb_getE(input_id+'_'+input_type+'-'+sel)) div_display.innerHTML=div_display.innerHTML+sel_div;
    } else {
      input_field.value=input_field.value.replace(c+sel+c,c);
      atb_getE(input_id+'_'+input_type+'-'+sel).style.display='none';
      atb_getE(input_id+'_'+input_type+'-'+sel).setAttribute('id','deleted_div');
    }
    input_field.value=input_field.value.replace(c+c,c);
    atb_getE(input_id+'_'+input_type+'_select').selectedIndex=0;
  }
  
  function atb_change(fid,sid,sel_index) {
    if(sel_index>0) if(atb_getE(sid+'-sel'))atb_getE(sid+'-sel').innerHTML='';
    
    if(sel_index==1) {
      if(atb_getE(sid+'_role_td'))atb_getE(sid+'_role_td').style.visibility='visible';
      if(atb_getE(sid+'_count_td'))atb_getE(sid+'_count_td').style.visibility='visible';
      if(atb_getE(sid+'_post_td'))atb_getE(sid+'_post_td').style.visibility='visible';
      if(atb_getE(sid+'_pagetype_td'))atb_getE(sid+'_pagetype_td').style.visibility='visible';
      if(atb_getE('atb_config').innerHTML.indexOf('Choose an option')>=0) return false;
    } else {
      if(atb_getE(sid+'_role_td'))atb_getE(sid+'_role_td').style.visibility='hidden';
      if(atb_getE(sid+'_count_td'))atb_getE(sid+'_count_td').style.visibility='hidden';
      if(atb_getE(sid+'_post_td'))atb_getE(sid+'_post_td').style.visibility='hidden';
      if(atb_getE(sid+'_pagetype_td'))atb_getE(sid+'_pagetype_td').style.visibility='hidden';
    }
    
    if(atb_getE('atb_config').innerHTML.indexOf('Choose an option')<0) atb_getE('atb_save').style.display='block'; else return false;
    atb_loading();
    setTimeout(function(){ if(atb_getE(fid)) atb_getE(fid).submit(); },2000);
  }
  
  function atb_loading() {
    if(atb_getE('atb_admin')) var atb_admin=atb_getE('atb_admin');
    if(atb_getE('atb_sum')) var atb_sum=atb_getE('atb_sum');
    if(atb_admin) {atb_admin.style.opacity='.3'; atb_admin.style.pointerEvents='none';}
    if(atb_sum) {atb_sum.style.opacity='.3'; atb_sum.style.pointerEvents='none';}
    
    if(atb_getE('atb_head')) var atb_head=atb_getE('atb_head');
    if(atb_head) atb_head.innerHTML=atb_head.innerHTML+"<progress id='loading' style='float:left;width:100%;' max='100'></progress>";
  }

  function atb_switchScreen(screen) {
    atb_getE('atb_phv').style.opacity='0';
    atb_getE('atb_admin').style.opacity='0';
    setTimeout(function(){
      atb_getE('atb_return').style.display='none';
      atb_getE('atb_phv').style.display='none';
      atb_getE('atb_admin').style.display='none';
      atb_getE('view_hits_btn').style.display='inline-block';
    },100);
    
    setTimeout(function(){
      atb_getE(screen).style.display='block';
      if(screen=='atb_phv')atb_getE('atb_return').style.display='block';
      setTimeout(function(){atb_getE(screen).style.opacity='1';},100);
    },101);
  }

  function atb_copy(content_id,button_id) {
    atb_getE(button_id).disabled=true;
    var contents=atb_getE(content_id).innerHTML;
    var contentv=atb_getE(content_id).style.display;
    
    if(contents.indexOf('&#9986;')>0) return false;
    var tmpEl;
    var copy_temp='copy_temp'+Math.floor((Math.random()*1000)+1);
    
    tmpEl=document.createElement(copy_temp);
    tmpEl.style.opacity=0;
    tmpEl.style.position="absolute";
    tmpEl.style.pointerEvents="none";
    tmpEl.style.zIndex=-1;
    tmpEl.innerHTML=contents;
    document.body.appendChild(tmpEl);

    var range=document.createRange();
    range.selectNode(tmpEl);
    
    var w=window.getSelection();
    if(w.rangeCount>0) w.removeAllRanges();
    w.addRange(range);

    if(!document.execCommand("copy")) {
      alert('Unable to auto-copy this content.\nPlease copy this content manually.');
      return false;
    }
    document.body.removeChild(tmpEl);
    
    var confirm="<div class='caps' style='position:absolute;background:#FFF;color:gray;margin-left:33%;padding:2em;border-radius:3px;border:1px solid #DDD;z-index:99'>&#9986; copied to clipboard!</div>";
    var opaque="<div style='opacity:.3;height:100%;width:100%;'>"+contents+"</div>";
    
    atb_getE(content_id).innerHTML=confirm+opaque;
    
    setTimeout(function(){
      atb_getE(content_id).innerHTML=contents;
      atb_getE(button_id).disabled=false;
    },2000);
  }

  function atb_expand(id) {
    if(!atb_getE(id)) return false;
    var target=atb_getE(id);
    if(target.style.opacity!=1) {
      target.style.display='block';
      setTimeout(function(){
        target.style.opacity='1';
        target.style.maxHeight='99em';
        target.style.padding=target.style.overflow='';
      },10);
    } else {
      target.style.maxHeight=target.style.opacity=target.style.padding='0';
      target.style.overflow='none';
      setTimeout(function(){target.style.display='none';},500);
    }
  }

  function append_diag(diag) {
    var d=atb_getE(diag).innerHTML;
    d=d.replace(/  /g,'');
    d=d.replace(/(\r\n|\r|\n)/g,'%0A');
    d=d.replace(/<\/?[^>]+(>|$)/g,'');
    return 'Type your inquiry here%0A%0A%0ADiagnostics follow:%0A-------------%0A'+d;
  }
</script>