<?php
/*
Plugin Name: Admin Toolbox
Plugin URI: https://richardlerma.com/plugins/
Description: Manage an array of administrative options improving user control and resource management.
Author: RLDD
Author URI: https://richardlerma.com/contact/
Version: 6.0.28
Text Domain: admin-toolbox
Copyright: (c) 2017-2024 - rldd.net - All Rights Reserved
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

global $atb_version; $atb_version='6.0.28';
if(!defined('ABSPATH')) exit;

function atb_error() {file_put_contents(dirname(__file__).'/install_log.txt', ob_get_contents());}
if(defined('WP_DEBUG') && true===WP_DEBUG) add_action('activated_plugin','atb_error');

function atb_adminMenu() {
  add_submenu_page('tools.php','Admin Toolbox','<span style="font-size:.8em;display:inline-block;margin:.5em -.5em -.3em;" class="dashicons dashicons-chart-bar"></span> Admin Toolbox','manage_options','admin-toolbox','atb_admin',1);
}
add_action('admin_menu','atb_adminMenu');

function atb_admin() {
  global $wpdb;
  global $atb_version;
  include_once('atb_admin.php');
  wp_enqueue_style('atb_style',plugins_url('assets/atb_min.css?v=0'.$atb_version,__FILE__));
}
add_shortcode('atb_admin','atb_admin');

function atb_upgrade_db() {
  $ug=get_option('atb_upgrade');
  if(!empty($ug)) return;
  
  global $atb_version;
  $old_config=atb_r("SHOW TABLES LIKE 'wp_atb_options';");
  if($old_config) {
    atb_r("INSERT INTO wp_options(option_name,option_value)
        SELECT CONCAT('atb_',option_name),option_value
        FROM wp_atb_options;
      ");
    atb_r("DROP TABLE IF EXISTS wp_atb_options;");
    update_option('atb_setup',$atb_version);
  }
  update_option('atb_upgrade',$atb_version);
}
add_action('init','atb_upgrade_db');

function atb_activate($upgrade) {
  global $wpdb,$atb_version;
  require_once(ABSPATH.basename(get_admin_url()).'/includes/upgrade.php');
  update_option('atb_db_version',$atb_version,'no');

  $sql="
    CREATE TABLE {$wpdb->prefix}atb_pagehits
    (hit_id INT NOT NULL AUTO_INCREMENT
    ,date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ,ip VARCHAR(191) NULL
    ,userid INT
    ,url VARCHAR(500)
    ,referrer VARCHAR(500)
    ,subsq INT DEFAULT NULL
    ,PRIMARY KEY  (hit_id)
    ,KEY date (date)
    ,KEY ip (ip)) ENGINE=InnoDB;";
  dbDelta($sql);

  if(function_exists('atb_pro_ping'))atb_pro_ping();
}
register_activation_hook(__FILE__,'atb_activate');

function atb_admin_notice() {
  if(!atb_is_path('cron,ajax,page=admin-toolbox')){
    require_once(ABSPATH.WPINC.'/pluggable.php'); 
    if(current_user_can('manage_options')) {
      $settings_url=get_admin_url(null,'admin.php?page=admin-toolbox');?>
      <div class="notice notice-success is-dismissible" style='margin:0;'>
        <p><?php _e("The <em>Admin Toolbox</em> plugin is active, but is not yet configured. Visit the <a href='$settings_url'>configuration page</a> to complete setup.",'Admin Toolbox');?>
      </div><?php
    }
  }
}

function atb_checkConfig($check=0) {
  $get_config=get_option('atb_setup');
  if($check>0) if(!$get_config) return false; else return true;
  if(!$get_config && atb_is_path('plugins.php,index.php')) add_action('admin_notices','atb_admin_notice');
}
add_action('admin_init','atb_checkConfig');

function atb_add_action_links($links) {
  $settings_url=get_admin_url(null,'admin.php?page=admin-toolbox');
  $support_url='https://richardlerma.com/plugins/';
  $links[]='<a href="'.$support_url.'">Support</a>';
  array_push($links,'<a href="'.$settings_url.'">Settings</a>');
  return $links;
}
add_filter('plugin_action_links_'.plugin_basename(__FILE__),'atb_add_action_links');

function atb_uninstall() {
  $keep=get_option('atb_page_hit_keep');
  if($keep>0) return;
  else {
    atb_r("DROP TABLE IF EXISTS wp_atb_options;");
    atb_r("DROP TABLE IF EXISTS wp_atb_pagehits;");
    atb_r("DELETE FROM wp_options WHERE option_name LIKE 'atb_%';");
    delete_option('atb_db_version');
  }
}
register_uninstall_hook(__FILE__,'atb_uninstall');

function atb_impact() {
  if(empty(get_option('atb_setup'))) return;
  if(get_option('atb_redirect_db')>0) atb_redirect_db();
  if(get_option('atb_redirect_hp')>0) atb_redirect_hp();

  function atb_admin_impact() {global $wpdb;include_once('atb_admin_impact.php');}
  add_action('admin_init','atb_admin_impact');

  function atb_login_impact() {global $wpdb;include_once('atb_login_impact.php');}
  add_action('login_enqueue_scripts','atb_login_impact');

  function atb_frontend_impact() {global $wpdb;include_once('atb_frontend_impact.php');}
  if(!is_admin()) atb_frontend_impact();
}
add_action('init','atb_impact');


function atb_cui($userid=0) {
  global $current_user;
  require_once(ABSPATH.WPINC.'/pluggable.php'); // If prior to pluggable loaded natively
  if(current_user_can('manage_options') && $userid>0) return $userid;
  $current_user=wp_get_current_user();
  $userid=$current_user->ID;
  return $userid;
}


function atb_cur($user_id=0) {
  $user_role='';
  require_once(ABSPATH."wp-includes/pluggable.php"); // If prior to pluggable loaded natively
  if(is_user_logged_in()) {
    if(is_multisite() && current_user_can('setup_network')) return array('super_admin');
    $user_id=atb_cui($user_id);
    $user_info=get_userdata($user_id);
    $user_role=$user_info->roles;
  }
  return $user_role;
}

function atb_r($q,$t=NULL) {
  global $wp_version;
  if(function_exists('r')) return r($q,$t);
  require_once(ABSPATH."wp-includes/pluggable.php"); // If prior to pluggable loaded natively
  if(version_compare('6.1',$wp_version)>0) require_once(ABSPATH.'wp-includes/wp-db.php');
  else require_once(ABSPATH.'wp-includes/class-wpdb.php');

  global $wpdb;
  if(!$wpdb) $wpdb=new wpdb(DB_USER,DB_PASSWORD,DB_NAME,DB_HOST);
  $prf=$wpdb->prefix;
  $s=str_replace(' wp_',' '.$prf,$q);
  $s=str_replace($prf.str_replace('wp_','',$prf),$prf,$s);
  if(is_multisite()) {
    $bprf=$wpdb->base_prefix;
    $s=str_replace(" {$prf}user"," {$bprf}user",$s);
  }
  $r=$wpdb->get_results($s,OBJECT);
  if($r) return $r;
}

function atb_is_path($pages) {
  $page_array=explode(',',$pages);
  $current_page=strtolower($_SERVER['REQUEST_URI']);
  foreach($page_array as $page) {
    if(strpos($current_page,strtolower($page))!==false) return true;
  }
  return false;
}


// PageHit Action
function atb_hit_page() {
  if(atb_is_path('toolbox&phv=1')!==false) return;
  global $current_user;
  $userid=0;
  $ip=sanitize_text_field($_SERVER['REMOTE_ADDR']);
  $url=sanitize_text_field(substr($_SERVER['REQUEST_URI'],0,250));
  if(!get_transient("atb_flag_$ip:".atb_cui())) if(is_user_logged_in()) $userid=atb_cui();
  $page=get_transient("atb_$ip:$userid");
  if($url!=$page) {
    $exclude_pages=atb_r("SELECT option_name,option_value FROM wp_options WHERE option_name LIKE 'atb_page_hit_page%';");
    if($exclude_pages) {
      foreach($exclude_pages as $row):
        if($row->option_name=='atb_page_hit_page_exclude') $page_hit_page_exclude=$row->option_value;
        if($row->option_name=='atb_page_hit_page_frag') $page_hit_page_frag=preg_split("/[\r\n,]+/",$row->option_value);
      endforeach;
      if(isset($page_hit_page_exclude)) if($page_hit_page_exclude>0 && !empty($page_hit_page_frag)) {
        $current_page=strtolower($_SERVER['REQUEST_URI']);
        foreach($page_hit_page_frag as $frag) if(!empty($frag)) {if(strpos($current_page,strtolower($frag))!==false) return;}
      }
    }
    if(isset($_SERVER['HTTP_REFERER'])) $referrer=sanitize_text_field(substr($_SERVER['HTTP_REFERER'],0,250)); else $referrer='';
    set_transient("atb_$ip:$userid",$url,3600);
    atb_r("INSERT INTO wp_atb_pagehits(ip,userid,url,referrer) VALUES ('$ip','$userid','$url','$referrer');");
  } else atb_r("UPDATE wp_atb_pagehits SET subsq=IFNULL(subsq,0)+1 WHERE ip='$ip' AND userid='$userid' AND url='$url' ORDER BY hit_id DESC LIMIT 1;");

  if(function_exists('atb_check_abuse')) {
    $abuse_score=get_transient("abu_$ip");
    if($abuse_score>5) {header("HTTP/1.1 401 Unauthorized");exit();}
  }
}


// Admin Post Search Meta
function atb_admin_post_search_meta($query) { 
  if(!is_admin()) return;
  if(!isset($_GET['search_meta']) || !isset($_GET['s'])) return;
  if(empty($_GET['search_meta']) || empty($_GET['s'])) return;
  if(isset($_GET['post_type'])) $post_type=sanitize_text_field($_GET['post_type']); else return;
  if(isset($query->query_vars['s'])) $term=$query->query_vars['s']; else return;
  $meta_keys=r("
    SELECT DISTINCT meta_key,MAX(LENGTH(meta_value))val_len
    FROM wp_postmeta pm 
    JOIN (SELECT * FROM wp_posts WHERE post_type='$post_type' AND post_status!='trash' ORDER BY ID DESC LIMIT 100)p ON p.ID=pm.post_id
    WHERE meta_value NOT LIKE 'field_%'
    AND meta_key NOT LIKE '%_edit_%'
    GROUP BY meta_key
    HAVING val_len>2");
  $meta[]='';
  if($meta_keys) foreach($meta_keys as $m) array_push($meta,$m->meta_key); else return;
  $query->query_vars['s']='';
  $meta_query=array('relation'=>'OR');
  foreach($meta as $m) {array_push($meta_query, array('key'=>$m,'value'=>$term,'compare'=>'LIKE'));}
  $query->set("meta_query",$meta_query);
}


// Max Image Size Err Msg
function atb_media_size_msg() { ?>
  <style type='text/css'>
    .upload-error-message::before{content:"\AOPTIMIZE THIS CONTENT FOR WEB:\A";white-space:pre;font-weight:bold}
    .upload-error-message::after{content:" Decrease the file size to less than <?php echo get_transient('limit_img_size_kb_'.atb_cui());?>KB by compressing the file, changing the file format or reducing the dimensions. Properly formatted and compressed images can save many bytes of data, decrease page loads times, increase SEO ranking, and improve overall user experience."}
  </style><?php
}

// Limit Media Library File Sizes
function atb_check_media_limit() {
  $limit_img_size=get_option('atb_limit_img_size');
  if($limit_img_size<1) return false;
  $limit_img_size_kb=get_transient('limit_img_size_kb_'.atb_cui());
  if(!empty($limit_img_size_kb)) if($limit_img_size_kb>0) return true;
  if(atb_is_path(basename(get_admin_url())) && !atb_is_path(basename(get_admin_url()).'/plugins.php')) {

    $user_roles=atb_cur();
    if(empty($user_roles)) $user_roles=array();
    $get_limit=atb_r("SELECT option_name,option_value FROM wp_options WHERE option_name LIKE 'atb_limit_img_size%';");
    if(!$get_limit) return;

    $limit_img_size=0;
    $limit_img_size_kb=0;
    
    if($get_limit):
      foreach($get_limit as $row):
        if($row->option_name=='atb_limit_img_size') $limit_img_size=$row->option_value;
        if($row->option_name=='atb_limit_img_size_role') $limit_img_size_role=explode(",",$row->option_value);
        if($row->option_name=='atb_limit_img_size_role_exclude') $limit_img_size_role_exclude=$row->option_value;
        if($row->option_name=='atb_limit_img_size_kb') $limit_img_size_kb=$row->option_value;
      endforeach;
    endif;
    
    if($limit_img_size==1 && $limit_img_size_kb>0) {
      if(($limit_img_size_role_exclude==0 && (count(array_intersect($limit_img_size_role,$user_roles))>0 || in_array('*All',$limit_img_size_role,true)))
       || ($limit_img_size_role_exclude==1 && (count(array_intersect($limit_img_size_role,$user_roles))==0 || in_array('*All',$limit_img_size_role,true)))) {
          set_transient('limit_img_size_kb_'.atb_cui(),$limit_img_size_kb,1100);
          return true;
      }
    }
  }
  return false;
}

function atb_media_limit($size) {
  if(atb_is_path('/wp-json')) return $size;
  if(!atb_check_media_limit()) return $size;
  add_action('admin_head','atb_media_size_msg');
  $kb=get_transient('limit_img_size_kb_'.atb_cui())*1000;
  return $kb;
}
add_filter('upload_size_limit','atb_media_limit');


// Remove other plugins' login features if logged in
//if(!atb_is_path('/wp-json')) if(atb_cui()>0) remove_all_actions('login_init'); 


// Configure Login Page
function atb_login_init() {
  if(atb_is_path('action=logout')) return;
  if(atb_is_path('action=lostpassword') || atb_is_path('2fa=1')) {
    if(is_user_logged_in()) {
      remove_all_actions('login_init'); // Remove other plugins' features
      add_action('login_init','atb_prompt_token');
    }
  } else add_action('login_init','atb_login_redirect');
}
add_action('wp_loaded','atb_login_init',0); // Renamed Login Page (AIOWPS)
add_action('wp_loaded','atb_login_init',999); // Standard wp-login.php


// Add Token on Login
function atb_create_token($login='') {
  $newcode=uniqid();
  if(!empty($login)) {$user=get_user_by('login',$login); $userid=$user->ID;} else $userid=atb_cui();
  set_transient('atb_token_'.$userid,strtoupper(substr($newcode,8,5)),300);
  $ip=sanitize_text_field($_SERVER['REMOTE_ADDR']);
  set_transient("atb_flag_$ip:".$userid,1,0);
}
add_action('wp_login','atb_create_token');


// Email Functions
function atb_mail_content_type() {return 'text/html';}
function atb_mail_from($email) {return get_bloginfo('admin_email');}
function atb_mail_name($name) {return get_bloginfo('name');}
function atb_html_mail($to,$subject,$message) {
  add_filter('wp_mail_content_type','atb_mail_content_type');
  add_filter('wp_mail_from','atb_mail_from');
  add_filter('wp_mail_from_name','atb_mail_name');
  if(empty($to)) $to=get_bloginfo('admin_email');
  wp_mail($to,$subject,$message);
  remove_filter('wp_mail_content_type','atb_mail_content_type');
}


function atb_email_token($target) {
  $ip=sanitize_text_field($_SERVER['REMOTE_ADDR']);
  $user_info=get_userdata(atb_cui());
  $title=get_bloginfo('name');
  $to=$user_info->user_email;
  if(!get_transient('atb_target_'.atb_cui())) set_transient('atb_target_'.atb_cui(),$target,3600);
  if(atb_is_path('rsd=2')) atb_create_token(); // regen token
  if(atb_is_path('rsd=1')) set_transient("atb_flag_$ip:".atb_cui(),1,0); // resend token
  if(get_transient("atb_flag_$ip:".atb_cui())==1) { // if token not yet sent
    if(strpos($to,'@')!==false) { // check for valid email
      if(!get_transient('atb_token_'.atb_cui())) atb_create_token();
      require_once(ABSPATH.WPINC.'/pluggable.php');
      $message=get_transient('atb_token_'.atb_cui())." is your $title verification code.";
      if(function_exists('atb_text')) $sent=atb_text($user_info->ID,$message);
      if(!$sent) {
        $subject=$title.' Verification Code';
        $name=$user_info->first_name;
        $intro="Dear ".$name."<br><br>&nbsp;";
        atb_html_mail($to,$subject,$intro.$message);
        $to=substr($to,0,3)."<span style='-webkit-filter:blur(2px);filter:blur(2px)'>81818</span>".substr($to,-6);
        set_transient('atb_prompt_mobile_'.atb_cui(),1,3600);
      } else $to=substr($sent,0,3)."<span style='-webkit-filter:blur(2px);filter:blur(2px)'> 010 10</span>".substr($sent,-2);
      set_transient("atb_flag_$ip:".atb_cui(),2,0);
      set_transient('atb_sent_to_'.atb_cui(),$to,3600);
    } else delete_transient("atb_flag_$ip:".atb_cui());
  }
  wp_redirect(wp_login_url()."?action=lostpassword&2fa=1#token"); exit;
}


function atb_dual_auth($ip_exclude) {
  if(strlen(trim($_SERVER['REMOTE_ADDR']))>6) $ip=trim($_SERVER['REMOTE_ADDR']); else $ip='na';
  if(get_transient("atb_flag_$ip:".atb_cui())!==false && atb_is_path(basename(get_admin_url()))) {
    if(function_exists('atb_check_abuse')) {
      $abuse_score=0; $abuse_score=atb_check_abuse($ip); 
      if($abuse_score>5) {
        atb_report_abuse($ip,'15','Attempt to use stolen credentials to access CMS.');
        wp_redirect(wp_login_url()."?action=lostpassword&2fa=1#token"); exit;
      }
    }
    $prev_ip=atb_r("SELECT group_concat(DISTINCT ip) as ip FROM wp_atb_pagehits WHERE userid=".atb_cui()." AND date>NOW()-INTERVAL 30 DAY;");
    if($prev_ip) foreach($prev_ip as $prev) $ip_exclude.=','.$prev->ip; else $ip_exclude='';
    if(isset($_POST['redirect_to'])) $uri=$_POST['redirect_to'];
    elseif(atb_is_path('ajax')) $uri=$_SERVER['HTTP_HOST'].'/'.basename(get_admin_url()).'/'; else $uri=$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    if(empty($ip_exclude) || (!empty($ip_exclude) && strpos($ip_exclude,$ip)===false)) atb_email_token($uri); else delete_transient("atb_flag_$ip:".atb_cui());
  }
}


// Show Token Entry Form
function atb_prompt_token() {
  if(atb_is_path('2fa=1')) {
    if(!empty($_POST['token'])) $usr_token=strtolower(sanitize_text_field($_POST['token'])); else $usr_token='';
    $ip=sanitize_text_field($_SERVER['REMOTE_ADDR']);
    if(!get_transient("atb_flag_$ip:".atb_cui())) atb_login_redirect();
    $atb_token=strtolower(get_transient('atb_token_'.atb_cui()));
    $error_msg='';
    if(strlen($atb_token)<1) $error_msg="<div id='login_error' style='margin:1em 0;font-weight:bold'>Code expired. <a href='".admin_url()."?rsd=2'>Generate a new code</a>.</div>";
    elseif(strlen($usr_token)>0 && $atb_token!=$usr_token) $error_msg="<div id='login_error' style='margin:1em 0;font-weight:bold'>Invalid Code</div>";

    if(strlen($error_msg)<1 && strlen($usr_token)>0 && $atb_token==$usr_token) {
      $target=get_transient('atb_target_'.atb_cui());
      if(get_transient('atb_prompt_mobile_'.atb_cui())!==false) if(strpos($target,'?')===false) $target.="?atb_mob=1"; else $target.="&atb_mob=1";
      delete_transient('atb_prompt_mobile_'.atb_cui());
      delete_transient('atb_target_'.atb_cui());
      delete_transient("atb_flag_$ip:".atb_cui());
      delete_transient('atb_token_'.atb_cui());
      delete_transient('atb_sent_to_'.atb_cui());
      atb_hit_page();
      wp_redirect(atb_login_redirect($target)); exit;
    } else {
      $sent_to=get_transient('atb_sent_to_'.atb_cui());
      if(function_exists('atb_sms_origin') && is_numeric(substr($sent_to,0,3)) && is_numeric(substr($sent_to,-2))) {
        $origin=atb_sms_origin();
        $check="If you previously opted out, you must first opt-in by texting RESUME to $origin.";
      } else $check="Check your spam folder for recent emails.";
      sleep(3); // Slow down brute force attempts ?>
      <title>Two Factor Authentication</title>
      <body class='login'>
        <style type='text/css'>
          #login{display:none!important}
          #auth{display:table;margin:auto}
          #auth input{padding:1em}
          #auth .resend{float:right;text-align:right}
          #auth .resend a{text-decoration:none}
          #auth input[type="submit"]{background:#02a0d2;color:#FFF;border:1px solid #02a0d2;-webkit-appearance:none}
          #auth input[type="submit"]:hover,#auth input[type="submit"]:active,#auth input[type="submit"]:focus{background:#fff;color:#000}
        </style>

        <form id='auth' method='post' accept-charset='UTF-8 ISO-8859-1'>
          <p class='message'>Two Factor Authentication<br><br>
            Please enter the code sent to <span style='white-space:nowrap;font-style:italic'><?php echo $sent_to;?></span></p>
          <?php echo $error_msg;?>
          <input type='text' id='token' name='token' autocomplete='off' autofocus required maxlength='6' placeholder='Verification Code'>
          <input type='submit' value='Verify'>
          <div class='resend'>
          <a href="<?php echo admin_url();?>?rsd=1" onclick="if(!confirm('If you have not received a code, allow up to 2 minutes for delivery. <?php echo $check;?>\n\nAre you sure you want to resend the code?')) return false;">resend code</a><br>
          <a href="<?php echo admin_url();?>?rsd=2" onclick="if(!confirm('<?php echo $check;?>\n\nA new code VOIDS previous codes sent to you. Are you sure you want to generate a new code?')) return false;">generate new code</a>
          </div>
        </form>
        <script type='text/javascript'>document.getElementById('token').focus();</script>
      </body><?php
    }
  }
}


// Redirect from Login Page
function atb_login_redirect($target='') {
  if(!empty($target)) {
    $http='http://';
    if(stripos($target,'http')===false) if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']!='off') $http='https://';
    return $http.$target;
  }

  if(is_user_logged_in()) {
    if(current_user_can('edit_posts')) if(!atb_is_path(basename(get_admin_url()))) {wp_redirect(admin_url()); exit;} else return;
    else wp_redirect(site_url());
  }
}


//Redirect to HTTPS
function atb_https_redirect() { 
  if(empty($_SERVER['HTTPS'])) {
    $redirect=get_option('atb_https_redirect');
    if($redirect<1) return;
    else {
      if(isset($_SERVER['SERVER_NAME'])) header("Location: https://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']);
      exit;
    }
  }
}
add_action('wp_loaded','atb_https_redirect',10);



// Compress PageHits
function atb_compress_hits() {
  ignore_user_abort(true);
  set_time_limit(300);
  $ct=0;
  set_transient('atb_compress_timeout',1,45);
  $d=atb_r("DELETE FROM wp_atb_pagehits WHERE date<NOW()-INTERVAL 24 MONTH;"); 
  if(!get_transient('atb_compress_max_days')) $max_days=180; else $max_days=get_transient('atb_compress_max_days');
  $status="<div style='font-size:.8em;color:#bbb'>";
  for($x=$max_days;$x>=30;) {
    $y=$x+10;
    $r=atb_r("DELETE pg FROM wp_atb_pagehits pg
    JOIN (
      SELECT MAX(hit_id) as hit_id
      FROM wp_atb_pagehits
      WHERE userid<1
      AND date <DATE_FORMAT(NOW(),'%Y-%m-%d')-INTERVAL $x DAY
      AND date>=DATE_FORMAT(NOW(),'%Y-%m-%d')-INTERVAL $y DAY
      GROUP BY LEFT(date,13),ip,userid,url,referrer
      HAVING COUNT(ip)>1
      ORDER BY hit_id ASC
      LIMIT 2000
    )mx ON mx.hit_id=pg.hit_id;");
    $ct=$ct+intval($r);
    if($x>30 && !get_transient('atb_compress_timeout')) {
      if($max_days>30) set_transient('atb_compress_max_days',$max_days-20);
      return $status."Range:$x-$max_days days</div>".number_format($ct)." hits compressed";
    }
    sleep(1);
    $x=$x-10;
  }

  if(function_exists('atb_delete_transients')) $status.=atb_delete_transients();
  delete_transient('atb_compress_max_days');
  if(!get_transient('atb_compress_optimized')) {
    set_transient('atb_compress_optimized',1,259000); //every 3 days
    atb_r("OPTIMIZE TABLE wp_atb_pagehits;");
    return $status."Fully optimized.</div>".number_format($ct)." hits compressed";
  } else return $status."Max compression achieved.</div>".number_format($ct)." hits compressed";
}


// PageHit Summary
function atb_view_summary($atb_version_type) {
  if(get_transient('limit_img_size_kb_'.atb_cui())!==false) delete_transient('limit_img_size_kb_'.atb_cui());

  $current_mth=current_time('Y-m-01');
  $last_gen_mth=get_option('atb_mt_max');
  if(!$last_gen_mth) $last_gen_mth=get_transient('atb_mt_max');
  if(!$last_gen_mth) $last_gen_mth=$current_mth;
  $mth=get_option('atb_mt');
  if(!$mth) $mth=get_transient('atb_mt'); // Find Historical Month Stats

  function atb_month_stat($params) {
    if(function_exists('atb_pro_ping')) $blocked_ips='AND ip NOT IN (SELECT blocked_ip FROM wp_aiowps_permanent_block)'; else $blocked_ips='';
    $query="
      SELECT DATE_FORMAT(CONCAT(month,'-01'),'%b %y') as month
      ,month as month_id,visitors
      ,ROUND(visitors/CASE WHEN month=DATE_FORMAT(NOW(),'%Y-%m') THEN DAY(NOW()) ELSE 30 END,0) as day_avg
      FROM (
        SELECT LEFT(date,7) as month,COUNT(DISTINCT ip) as visitors
        FROM wp_atb_pagehits
        WHERE date>NOW()-INTERVAL 400 DAY $params 
        $blocked_ips 
        AND url NOT LIKE '/wp-%'
        GROUP BY LEFT(date,7)
      )a
      ORDER BY DATE_FORMAT(CONCAT(month,'-01'),'%y-%m');";
    $mth=atb_r($query);
    return $mth;
  }

  // Create Historical Month Stats
  if(!$mth) { 
    $mth=atb_month_stat("AND date<'$current_mth'");
    update_option('atb_mt',$mth,false);
    update_option('atb_mt_max',$current_mth,false);
  }

  // Get Month Stats between last Gen Date and Month Begin
  if(!$mth || $current_mth>$last_gen_mth) {
    $mth_new=atb_month_stat("AND date>='$last_gen_mth' AND date<'$current_mth'");
    if($mth && $mth_new) { 
      $mth=array_merge($mth,$mth_new); // Append Historical with Current
      update_option('atb_mt',$mth,false);
      update_option('atb_mt_max',$current_mth,false);
      delete_transient('atb_mt');
      delete_transient('atb_mt_max');
    }
  }

  // Get Month Stats greater than Month Begin
  $mth_new=atb_month_stat("AND date>='$current_mth'");
  if(!$mth)$mth=$mth_new;
  elseif($mth_new)$mth=array_merge($mth,$mth_new); // Append Historical with Today


  $current_day=current_time('Y-m-d');
  $last_gen_day=get_option('atb_dy_max');
  if(!$last_gen_day) $last_gen_day=get_transient('atb_dy_max');
  if(!$last_gen_day) $last_gen_day=$current_day;
  $day=get_option('atb_dy');
  if(!$day) $day=get_transient('atb_dy');  // Find Historical Day Stats

  function atb_day_stat($params) {
    if(function_exists('atb_pro_ping')) $blocked_ips='AND ip NOT IN (SELECT blocked_ip FROM wp_aiowps_permanent_block)'; else $blocked_ips='';
    $query="
      SELECT DATE_FORMAT(date,'%a<br>%D') as day
      ,date as date_id
      ,DATE_FORMAT(CONCAT(date,'-01'),'%b %y') as month,visitors
      ,visitors
      FROM (
        SELECT LEFT(date,10) as date,COUNT(DISTINCT ip) as visitors
        FROM wp_atb_pagehits
        WHERE date>NOW()-INTERVAL 400 DAY $params 
        $blocked_ips 
        AND url NOT LIKE '/wp-%'
        GROUP BY LEFT(date,10)
      )a
      ORDER BY date;";
    $day=atb_r($query);
    return $day;
  }

  // Create Historical Day Stats
  if(!$day) {
    $day=atb_day_stat("AND date<'$current_day'");
    update_option('atb_dy',$day,false);
    update_option('atb_dy_max',$current_day,false);
  }

  // Get Day Stats between last Gen Date and Today
  if(!$day || $current_day>$last_gen_day) {
    $day_new=atb_day_stat("AND date>='$last_gen_day' AND date<'$current_day' ");
    if($day && $day_new){ // Append Historical with Current
      $day=array_merge($day,$day_new); 
      update_option('atb_dy',$day,false);
      update_option('atb_dy_max',$current_day,false);
      delete_transient('atb_dy');
      delete_transient('atb_dy_max');
    }
  }

  // Get Day Stats Today
  $day_new=atb_day_stat("AND date>='$current_day'");
  if(!$day) $day=$day_new;
  elseif($day_new)$day=array_merge($day,$day_new); // Append Historical with Today


  $max_visit_avg=0;
  $max_visit_tot=0;
  if($mth) foreach($mth as $mth_item) if($mth_item->day_avg>$max_visit_avg) {$max_visit_avg=$mth_item->day_avg; $max_visit_tot=$mth_item->visitors;} ?>

  <style type='text/css'>#atb_sum{float:left}#atb_sum .month{display:inline-block;height:3em;padding:0 .5em;cursor:pointer;text-align:center}#atb_sum .month .chart{width:100%;overflow:hidden;background:#0073AA;background:-webkit-linear-gradient(#0073AA,#FFF);background:-o-linear-gradient(#0073AA,#FFF);background:-moz-linear-gradient(#0073AA,#FFF);background:linear-gradient(#0073AA,#FFF)}#atb_sum .month .tot{font-size:.9em;color:#333}#atb_sum .month .days{display:none}#atb_sum .pointer{display:none;width:0px;height:0px;margin:auto;border-left:15px solid transparent;border-right:15px solid transparent;border-top:15px solid #a4c6dd}#atb_days{margin-top:1em;padding:0 .5em;opacity:0;font-size:.9em;-webkit-transition:all .25s;-moz-transition:all .25s;transition:all .25s}#atb_days .month{padding:0 .1em}#atb_days .month .chart{min-width:2em;background:#b71b8a;background:-webkit-linear-gradient(#b71b8a,#FFF);background:-o-linear-gradient(#b71b8a,#FFF);background:-moz-linear-gradient(#b71b8a,#FFF);background:linear-gradient(#b71b8a,#FFF)}.month .chart:hover{filter:brightness(80%)}.month .chart:hover~.pointer{display:block}</style>
  <div id='atb_sum' class='atb_control'>
    <div>
      <span class="dashicons dashicons-chart-bar"></span> <span style='color:#1177aa;font-weight:bold'>Traffic Summary</span>
      <a href='#!' style='float:right' onclick="if(confirm('Compressing the data file will retain hit count data, but granular data for user tracking and page views will be less accurate.\n\nYou may compress an unlimited number of times.')) {atb_loading(); window.location='<?php echo get_admin_url(null,'admin.php?page=admin-toolbox&compress=1');?>';} else return false;" title='Compress Data File'><span style='color:#d323a0'><?php if(!empty($_REQUEST['compress'])) echo atb_compress_hits();?></span>&nbsp;&nbsp;<span style='opacity:.5' class="spin dashicons dashicons-share-alt"></span></a>
    </div>
    <div style='width:100%;margin-top:4em;border-bottom:1px solid #cfe0e8;color:#0073AA;font-size:.8em'><?php echo number_format($max_visit_tot);?></div><?php
    if($mth) foreach($mth as $mth_item) { if($max_visit_avg==0) $max_visit_avg=.1; ?>
      <div class='month' onclick="atb_open_month('<?php echo $mth_item->month_id;?>');" title='<?php echo number_format($mth_item->day_avg).' visits/day';?>'>
        <div class='chart' style='height:<?php echo ($mth_item->day_avg/$max_visit_avg)*100;?>%'></div>
        <div class='cal'><?php echo $mth_item->month;?></div>
        <div class='tot'><?php echo number_format($mth_item->visitors);?></div>
        <div class='pointer' id='p<?php echo $mth_item->month_id;?>'></div>
        <div class='days' id='m<?php echo $mth_item->month_id;?>'><?php
          $max_day_visit_tot=0;
          if($day) foreach($day as $day_item) if($day_item->month==$mth_item->month && $day_item->visitors>$max_day_visit_tot) $max_day_visit_tot=$day_item->visitors;?>
          <div style='width:100%;border-bottom:1px solid #cfead7;color:#06bd3a;font-size:.8em'><?php echo number_format($max_day_visit_tot);?></div><?php
          if($day) foreach($day as $day_item) {
            if($day_item->month==$mth_item->month){
              if($max_day_visit_tot==0) $max_day_visit_tot=.1; ?>
              <div class='month' title='<?php echo number_format($day_item->visitors);?> visits' <?php if($atb_version_type=='PRO') { ?>onclick="atb_loading(); window.location.href='<?php echo get_admin_url(null,'admin.php?page=admin-toolbox');?>&phv=1&date=<?php echo $day_item->date_id;?>';"<?php } ?>>
                <div class='chart' style='height:<?php echo ($day_item->visitors/$max_day_visit_tot)*100;?>%'></div>
                <div class='cal'><?php echo $day_item->day;?></div>
              </div><?php
            }
          } ?>
        </div>
      </div><?php
      } ?>
    <div id='atb_days'></div>
    <?php if($atb_version_type=='PRO') { ?><input type='button' value='recent' id='view_hits_btn' style='float:right' class='atb_button' onclick="atb_loading(); window.location='<?php echo get_admin_url(null,'admin.php?page=admin-toolbox');?>&phv=1';"><?php } ?>
  </div>
  
  <script type='text/javascript'>
    var last_sel=false;
    function atb_open_month(month) {
      document.getElementById('atb_days').style.opacity='0';
      setTimeout(function(){
        document.getElementById('atb_days').innerHTML=document.getElementById('m'+month).innerHTML;
        document.getElementById('p'+month).style.display='block';
        document.getElementById('atb_days').style.opacity='1';
        document.getElementById('atb_days').style.filter='blur(5px)';
      },250);
      setTimeout(function(){
        document.getElementById('atb_days').style.filter='';
      },500);
      if(last_sel && last_sel!=month) document.getElementById('p'+last_sel).style.display='none';
      last_sel=month;
    }
  </script><?php
}

function atb_hide_item() {
  $hide_items=get_option('atb_hide_item_id');
  echo "<style>$hide_items{display:none!important}</style>";
}

function atb_hide_item_all() {
  $hide_items=get_option('atb_hide_item_all');
  echo "<style>$hide_items{display:none!important}</style>";
}

function atb_redirect_hp() {
  $redirect_hp=get_option('atb_redirect_hp');
  $redirect_hp_role=get_option('atb_redirect_hp_role');
  $redirect_hp_role=explode(",",$redirect_hp_role);
  $redirect_hp_role_exclude=get_option('atb_redirect_hp_role_exclude');
  $redirect_hp_page=trim(get_option('atb_redirect_hp_page'));
  $redirect_hp_page=preg_replace("/[\r\n,]+/",',',$redirect_hp_page);

  if($redirect_hp==1 && !empty($redirect_hp_page)) {
    $user_roles=atb_cur();
    if(!is_array($user_roles)) $user_roles=array_filter(array($user_roles));
    if(($redirect_hp_role_exclude==0 && (count(array_intersect($redirect_hp_role,$user_roles))>0 || in_array('*All',$redirect_hp_role,true)))
     || ($redirect_hp_role_exclude==1 && count(array_intersect($redirect_hp_role,$user_roles))==0 && !in_array('*All',$redirect_hp_role,true))) {
      if(atb_is_path($redirect_hp_page)) {header("Location: ".site_url()); exit();}
    }
  }
}

function atb_redirect_db() {
  $redirect_db=get_option('atb_redirect_db');
  $redirect_db_role=get_option('atb_redirect_db_role');
  $redirect_db_role=explode(",",$redirect_db_role);
  $redirect_db_role_exclude=get_option('atb_redirect_db_role_exclude');
  $redirect_db_page=get_option('atb_redirect_db_page');
  $redirect_db_page=preg_replace("/[\r\n,]+/",',',$redirect_db_page);

  if($redirect_db==1 && !empty($redirect_db_page)) {
    $user_roles=atb_cur();
    if(!is_array($user_roles)) $user_roles=array_filter(array($user_roles));
    if(($redirect_db_role_exclude==0 && (count(array_intersect($redirect_db_role,$user_roles))>0 || in_array('*All',$redirect_db_role,true)))
     || ($redirect_db_role_exclude==1 && count(array_intersect($redirect_db_role,$user_roles))==0 && !in_array('*All',$redirect_db_role,true))) {
      if(atb_is_path($redirect_db_page)) {header("Location: ".get_admin_url()); exit();}
    }
  }
}


function atb_admin_email_check() {
  $admin_email_check=get_option('atb_admin_email_check');
  if(!empty($admin_email_check)) {
    add_filter('admin_email_check_interval',function($admin_email_check) {
      if($admin_email_check<0) return false;
      else return ($admin_email_check*MONTH_IN_SECONDS);
    });
  } else return get_option('admin_email_lifespan');
}
add_filter('admin_email_check_interval','atb_admin_email_check');