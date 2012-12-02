<?php

/*
Plugin Name: WP Autoresponder
Plugin URI: http://www.wpresponder.com
Description: Gather subscribers in newsletters, follow up with automated e-mails, provide subscription to all posts in your blog or individual categories.
Version: 5.2.9
Author: Raj Sekharan
Author URI: http://www.nodesman.com/
*/


//protect from multiple copies of the plugin. this can happen sometimes.

if (!defined("WPR_DEFS")) {
    define("WPR_DEFS", 1);

    $dir_name = basename(__DIR__);

    $plugindir = ABSPATH . '/' . PLUGINDIR . '/' . $dir_name;

    define("WPR_DIR", __DIR__);

    $controllerDir = WPR_DIR . "/controllers";
    $modelsDir = "$plugindir/models";
    $helpersDir = "$plugindir/helpers";

    define("WPR_VERSION", "5.3");
    define("WPR_PLUGIN_DIR", "$plugindir");


    $GLOBALS['WPR_PLUGIN_DIR'] = $plugindir;
    
    include_once __DIR__ . "/home.php";
    include_once __DIR__ . "/blog_series.php";
    include_once __DIR__ . "/forms.php";
    include_once __DIR__ . '/newmail.php';
    include_once __DIR__ . '/customizeblogemail.php';
    include_once __DIR__ . '/subscribers.php';
    include_once __DIR__ . '/wpr_deactivate.php';
    include_once __DIR__ . '/all_mailouts.php';
    include_once __DIR__ . '/actions.php';
    include_once __DIR__ . '/blogseries.lib.php';
    include_once __DIR__ . '/lib.php';
    include_once __DIR__ . '/conf/meta.php';
    include_once __DIR__ . '/lib/swift_required.php';
    include_once __DIR__ . '/lib/admin_notifications.php';
    include_once __DIR__ . '/lib/global.php';
    include_once __DIR__ . '/lib/custom_fields.php';
    include_once __DIR__ . '/lib/database_integrity_checker.php';
    include_once __DIR__ . '/lib/framework.php';
    include_once __DIR__ . '/lib/database_integrity_checker.php';
    include_once __DIR__ . '/lib/mail_functions.php';
    include_once __DIR__ . '/other/cron.php';
    include_once __DIR__ . '/other/firstrun.php';
    include_once __DIR__ . '/other/queue_management.php';
    include_once __DIR__ . '/other/notifications_and_tutorials.php';
    include_once __DIR__ . '/other/background.php';
    include_once __DIR__ . '/other/install.php';
    include_once __DIR__ . '/other/blog_crons.php';
    include_once __DIR__ . '/other/maintain.php';
    include_once __DIR__ . '/widget.php';

    include_once "$controllerDir/newsletters.php";
    include_once "$controllerDir/custom_fields.php";
    include_once "$controllerDir/importexport.php";
    include_once "$controllerDir/background_procs.php";
    include_once "$controllerDir/settings.php";
    include_once "$controllerDir/new-broadcast.php";
    include_once "$controllerDir/queue_management.php";
    include_once "$controllerDir/autoresponder.php";


    include_once "$modelsDir/subscriber.php";
    include_once "$modelsDir/newsletter.php";
    include_once "$modelsDir/autoresponder.php";

    include_once __DIR__ . '/conf/routes.php';
    include_once __DIR__ . '/conf/files.php';

    include_once __DIR__."/helpers/routing.php";
    include_once __DIR__."/helpers/paging.php";

    $GLOBALS['db_checker'] = new DatabaseChecker();
    $GLOBALS['wpr_globals'] = array();

	function _wpr_nag()
	{
		$address = get_option("wpr_address");		
		if (!$address && current_user_can("manage_newsletters"))  
		{
			add_action("admin_notices","no_address_error");	
		}
		
		add_action("admin_notices","_wpr_admin_notices_show");
		
	}
	
	add_action("plugins_loaded","_wpr_nag");
    add_action("admin_init","_wpr_admin_init");
	
	function no_address_error()
	{
            ?><div class="error fade"><p><strong>You must set your address in the  <a href="<?php echo admin_url( 'admin.php?page=_wpr/settings' ) ?>"> newsletter settings page</a>. It is a mandatory requirement for conformance with CAN-SPAM act guidelines (in USA).</strong></p></div><?php
	}
	
	function _wpr_no_newsletters($message)
	{
		
		global $wpdb;
	
		$query = "SELECT * FROM ".$wpdb->prefix."wpr_newsletters"; 
	
		$countOfNewsletters = $wpdb->get_results($query);
	
		$count = count($countOfNewsletters);
	
		unset($countOfNewsletters);
	
		if ($count ==0)
		{
	
			?>
<div class="wrap">
  <h2>No Newsletters Created Yet</h2>

<?php echo $message ?>, you must first create a newsletter. <br />
<br/>
<a href="admin.php?page=_wpr/newsletter&act=add" class="button">Create Newsletter</a>
</div>
<?php
	
			return true;
	
		}
	
		else
	
			return false;
		
	}
	
	
	function wpr_enqueue_post_page_scripts()
	{
		if (isset($_GET['post_type']) && $_GET['post_type'] == "page")
		{
			return;		
		}

        wp_enqueue_style("wpresponder-tabber", get_bloginfo("wpurl") . "/?wpr-file=tabber.css");
        wp_enqueue_script("wpresponder-tabber");
        wp_enqueue_script("wpresponder-addedit");
        wp_enqueue_script("wpresponder-ckeditor");
        wp_enqueue_script("jquery");
	}

	function wpr_enqueue_admin_scripts()
    {
        $directory = str_replace("wpresponder.php", "", __FILE__);
        $containingdirectory = basename($directory);
        $home_url = get_bloginfo("wpurl");
        if (current_user_can('manage_newsletters') && isset($_GET['page']) && preg_match("@^_wpr/@", $_GET['page'])) {
            wp_enqueue_script('post');
            wp_enqueue_script('editor');
            wp_enqueue_script('angularjs');
            wp_enqueue_script('word-count');
            wp_enqueue_script('wpresponder-uis', "$home_url/?wpr-file=jqui.js");
            add_thickbox();
            wp_enqueue_script('media-upload');
            wp_enqueue_script('quicktags');
            wp_enqueue_script('jquery');
            wp_enqueue_script('jqueryui-full');
            wp_enqueue_style('wpresponder-admin-ui-style', get_bloginfo('wpurl') . '/?wpr-file=admin-ui.css');

        }
        $url = (isset($_GET['page'])) ? $_GET['page'] : "";
        if (preg_match("@newmail\.php@", $url) || preg_match("@autoresponder\.php@", $url) || preg_match("@allmailouts\.php\&action=edit@", $url)) {
            wp_enqueue_script("wpresponder-ckeditor");
            wp_enqueue_script("jquery");
        }

    }

    function _wpr_admin_init()
    {
        $first_run = get_option("_wpr_firstrunv526");
        if ($first_run != "done")
        {
                _wpr_firstrunv526();
                add_option("_wpr_firstrunv526","done");
        }
    }
        
    function _wpr_load_plugin_textdomain()
    {
        $domain = 'wpr_autoresponder';
        $locale = apply_filters('plugin_locale', get_locale(), $domain);
        $plugindir = dirname(plugin_basename(__FILE__));
        load_textdomain($domain, WP_LANG_DIR.'/'.$plugindir.'/'.$domain.'-'.$locale.'.mo');
        load_plugin_textdomain($domain, FALSE, $plugindir.'/languages/');
    }
	
	function wpresponder_init_method() 
	{
		//load the scripts only for the administrator.
		global $current_user;
		global $db_checker;

        _wpr_load_plugin_textdomain();
        _wpr_add_required_blogseries_variables();

		if (_wpr_whether_optin_post_request())
            _wpr_optin();
		if (_wpr_whether_verify_subscription_request())
            _wpr_render_verify_email_address_page();
		if (_wpr_whether_confirm_subscription_request())
            _wpr_render_confirm_subscription();
		if (_wpr_whether_html_broadcast_view_frame_request())
             _wpr_render_broadcast_view_frame();
        if (_wpr_whether_file_request())
            _wpr_serve_file();
        if (_wpr_whether_confirmed_subscription_request())
            _wpr_render_confirmed_subscription_page();
        if (_wpr_whether_subscription_management_page_request())
            _wpr_render_subscription_management_page();
        if (_wpr_whether_template_html_request())
            _wpr_render_template_html();
        if (_wpr_whether_wpresponder_admin_page())
            Routing::init();
        if (_wpr_whether_admin_popup())
            _wpr_render_admin_screen_popup();

		_wpr_attach_cron_actions_to_functions();
        _wpr_register_wpresponder_scripts();
        _wpr_ensure_single_instance_of_cron_is_registered();
        _wpr_attach_to_non_wpresponder_email_delivery_filter();

        do_action("_wpr_init");
        add_action('admin_menu', 'wpr_admin_menu');
        add_action('admin_init','wpr_enqueue_admin_scripts');
        add_action('admin_menu', 'wpresponder_meta_box_add');
        add_action('edit_post', "wpr_edit_post_save");
		add_action('admin_action_edit','wpr_enqueue_post_page_scripts');
        add_action('load-post-new.php','wpr_enqueue_post_page_scripts');
        add_action('publish_post', "wpr_add_post_save");
	}

    function _wpr_attach_to_non_wpresponder_email_delivery_filter()
    {
        //TODO: This doesn't work. Write unit tests for this.
        add_filter("wp_mail", "_wpr_non_wpr_email_sent");
    }

    function _wpr_render_admin_screen_popup()
    {
        switch ($_GET['wpr-admin-action']) {
            case 'preview_email':
                include "preview_email.php";
                exit;
                break;
            case 'view_recipients':
                include("view_recipients.php");
                exit;
                break;
            case 'filter':
                include("filter.php");
                exit;
                break;
            case 'delete_mailout':
                include "delmailout.php";
                exit;
                break;
        }
    }

    function _wpr_whether_admin_popup()
    {
        return isset($_GET['wpr-admin-action']);
    }

    function _wpr_render_template_html()
    {
        include "templateproxy.php";
        exit;
    }

    function _wpr_whether_template_html_request()
    {
        return isset($_GET['wpr-template']);
    }

    function _wpr_render_subscription_management_page()
    {
        include "manage.php";
        exit;
    }

    function _wpr_whether_subscription_management_page_request()
    {
        return isset($_GET['wpr-manage']);
    }

    function _wpr_render_confirmed_subscription_page()
    {
        include "confirmed.php";
        exit;
    }

    function _wpr_whether_confirmed_subscription_request()
    {
        return isset($_GET['wpr-confirm']) && $_GET['wpr-confirm'] == 2;
    }

    function _wpr_register_wpresponder_scripts()
    {
        $containingdirectory = basename(__DIR__);
        $url = get_bloginfo("wpurl");
        wp_register_script("jqueryui-full", "$url/?wpr-file=jqui.js");
        wp_register_script("angularjs", "$url/?wpr-file=angular.js");
        wp_register_script("wpresponder-tabber", "$url/?wpr-file=tabber.js");
        wp_register_script("wpresponder-ckeditor", "/" . PLUGINDIR . "/" . $containingdirectory . "/ckeditor/ckeditor.js");
        wp_register_script("wpresponder-addedit", "/" . PLUGINDIR . "/" . $containingdirectory . "/script.js");
    }

    function _wpr_whether_wpresponder_admin_page()
    {
        return is_admin() && Routing::isWPRAdminPage() && !Routing::whetherLegacyURL($_GET['page']);
    }

    function _wpr_serve_file()
    {
        global $wpr_files;
        $name = $_GET['wpr-file'];
        if (preg_match("@.*\.js@", $wpr_files[$name]))
            header("Content-Type: text/javascript");
        else if (preg_match("@.*\.css@", $wpr_files[$name]))
            header("Content-Type: text/css");
        else if (preg_match("@.*\.png@", $wpr_files[$name]))
            header("Content-Type: image/png");
        $file_path = __DIR__ . "/{$wpr_files[$name]}";
        readfile($file_path);
        exit;
    }

    function _wpr_whether_file_request()
    {
        global $wpr_files;
        return isset($_GET['wpr-file']) && isset($wpr_files[$_GET['wpr-file']]);
    }

    function _wpr_render_broadcast_view_frame()
    {
        $vb = intval($_GET['wpr-vb']);
        if (isset($_GET['wpr-vb']) && $vb > 0) {
            require "broadcast_html_frame.php";
            exit;
        }
    }

    function _wpr_whether_html_broadcast_view_frame_request()
    {
        return isset($_GET['wpr-vb']);
    }

    function _wpr_whether_confirm_subscription_request()
    {
        return isset($_GET['wpr-confirm']) && $_GET['wpr-confirm'] != 2;
    }

    function _wpr_render_confirm_subscription()
    {
        include "confirm.php";
        exit;
    }

    function _wpr_whether_verify_subscription_request()
    {
        return isset($_GET['wpr-optin']) && $_GET['wpr-optin'] == 2;
    }

    function _wpr_render_verify_email_address_page()
    {
        require "verify.php";
        exit;
    }

    function _wpr_optin()
    {
        require "optin.php";
        exit;
    }

    function _wpr_whether_optin_post_request()
    {
        return isset($_GET['wpr-optin']) && $_GET['wpr-optin'] == 1;
    }

    function _wpr_add_required_blogseries_variables()
    {
        $activationDate = get_option("_wpr_NEWAGE_activation");
        if (empty($activationDate) || !$activationDate) {
            $timeNow = time();
            update_option("_wpr_NEWAGE_activation", $timeNow);
            /*
             * Because of the lack of tracking that was done in previous versions
             * of the blog category subscriptions, this version will deliver
             * blog posts to blog category subscribers ONLY after this date
             * This was done to prevent triggering a full delivery of all
             * blog posts in all categories to the respective category subscribers
             * on upgrade to this version.
             * I came up with the lousy name. Was a good idea at the time.
             */
        }
    }

    function _wpr_ensure_single_instance_of_cron_is_registered()
    {
        /*
         * The following code ensures that the WP Responder's crons are always scheduled no matter what
         * Sometimes the crons go missing from cron's registry. Only the great zeus knows why that happens.
         * The following code ensures that the crons are always scheduled immediately after they go missing.
         * It also unenqueues duplicate crons that get enqueued when the plugin is deactivated and then reactivated.
         */

        $last_run_esic = intval(_wpr_option_get("_wpr_ensure_single_instances_of_crons_last_run"));
        $timeSinceLast = time() - $last_run_esic;
        if ($timeSinceLast > WPR_ENSURE_SINGLE_INSTANCE_CHECK_PERIODICITY) {
            do_action("_wpr_ensure_single_instances_of_crons");
            $currentTime = time();
            _wpr_option_set("_wpr_ensure_single_instances_of_crons_last_run", $currentTime);
        }
    }

    add_action('widgets_init','wpr_widgets_init');
	add_action('init', "wpresponder_init_method",1);
	register_activation_hook(__FILE__,"wpresponder_install");
	register_deactivation_hook(__FILE__,"wpresponder_deactivate");
	$url = $_SERVER['REQUEST_URI'];	
	
	
	function wpr_widgets_init()
	{
		return register_widget("WP_Subscription_Form_Widget");
	}

    add_filter('cron_schedules','wpr_cronschedules');
}
	
