<?php
/*
Plugin Name: FuseDesk for WordPress
Plugin URI: https://www.FuseDesk.com/?utm_campaign=WordPress-Plugin&utm_source=PluginURI
Description: Integrate your FuseDesk App with your WordPress site to connect up your Infusionsoft CRM to your FuseDesk helpdesk, membership site, iMember360, Memberium, Wishlist, WisP, WordPress site and more!
Version: 2.4
Author: Jeremy Shapiro
Author URI: https://www.FuseDesk.com/?utm_campaign=WordPress-Plugin&utm_source=AuthorURI
*/

/*
FuseDesk (WordPress Plugin)
Copyright (C) 2013-2016 Asandia, Corp.
*/

# tell wordpress to register our shortcodes
add_shortcode("fusedesk_newcase", "fusedesk_newcase");
add_shortcode("fusedesk_mycases", "fusedesk_mycases");

add_filter( 'plugin_action_links', 'fusedesk_plugin_action_links', 10, 2 );
register_activation_hook(__FILE__,     'activate_fusedesk');
register_deactivation_hook(__FILE__, 'deactivate_fusedesk');
register_uninstall_hook(__FILE__, 'uninstall_fusedesk');


if (is_admin())
{
    add_action('admin_init', 'admin_init_fusedesk');
    add_action('admin_menu', 'admin_menu_fusedesk');
}

function admin_menu_fusedesk()
{
    add_options_page('FuseDesk', 'FuseDesk', 'manage_options', 'fusedesk', 'options_page_fusedesk');

}

function fusedesk_plugin_action_links( $links, $file )
{
    if ( $file == plugin_basename( dirname(__FILE__).'/fusedesk.php' ) ) {
        $links[] = '<a href="options-general.php?page=fusedesk"><b>'.__('Settings').'</b></a>';
        if($appname = fusedesk_guessappname())
        {
            $links[] = '<a href="https://'.$appname.'.fusedesk.com/app/" target="_blank">FuseDesk '.__('Login').'</a>';
        }
    }
    return $links;
}

function options_page_fusedesk()
{
    include(dirname(__FILE__).'/options.php');
}

function deactivate_fusedesk() {
    # for now, deactivate shouldn't do anything
}

function uninstall_fusedesk() {
    delete_option('fusedesk_appname');
    delete_option('fusedesk_apiley');
    delete_option('fusedesk_defaultrep');
    delete_option('fusedesk_defaultdepartment');
}

function admin_init_fusedesk() {
    register_setting('fusedesk', 'fusedesk_appname');
    register_setting('fusedesk', 'fusedesk_apikey');
    register_setting('fusedesk', 'fusedesk_defaultrep');
    register_setting('fusedesk', 'fusedesk_defaultdepartment');
}

/*
 * Check to see if a partner install is online, i.e. a membership site
 */
function fusedesk_checkpartner($partner)
{
    switch($partner)
    {
        case "imember360":
            global $i4w;
            return is_a($i4w, 'infusionWP');
            break;

        case "wishlist":
            global $WishListMemberInstance;
            return isset($WishListMemberInstance);
            break;

        case "memberium":
            return (defined('MEMBERIUM_INSTALLED') and MEMBERIUM_INSTALLED) or class_exists('membershipcore');
            break;

        case "wisp":
            global $sb_wisp;
            return (isset($sb_wisp) and !is_null($sb_wisp));
            break;

        default:
            return false;
    }
}

/*
 * FuseDesk Integration Partners
 */

function fusedesk_partners()
{
    return array(
        'memberium'  => array(
            'name' => 'Memberium',
            'site' => 'https://www.fusedesk.com/memberium',
            'appname' => true,
            'contactid' => true,
            'contact' => true,
            'lasttested' => '2016-01-21',
            'knownissues' => array(),
        ),
        'imember360' => array(
            'name' => 'iMember360',
            'site' => 'http://iMember360.com/',
            'appname' => true,
            'contactid' => true,
            'contact' => true,
            'lasttested' => '2014-09-04',
            'knownissues' => array(),
        ),
        'wishlist'   => array(
            'name' => 'Wishlist',
            'site' => 'http://member.wishlistproducts.com/',
            'appname' => true,
            'contactid' => true,
            'contact' => true,
            'lasttested' => '2014-09-04',
            'knownissues' => array(),
        ),
        'wisp'      => array(
            'name'  => 'WisP',
            'site' => 'https://www.informationstreet.com/wisp-wordpress-membership-plugin/',
            'appname' => true,
            'contactid' => true,
            'contact' => true,
            'lasttested' => '2016-01-12',
            'knownissues' => array(
                'Protected content can still be found via search as WisP does not hide pages, but rather just hides the content'
            )
        )
    );
}

/*
 * Guess the app name based on installed membership sites
 */
function fusedesk_guessappname()
{
    if($appname = get_option('fusedesk_appname'))
    {
        return $appname;
    } elseif (array_key_exists('appname', $_GET) and $_GET['appname']) {
        return $_GET['appname'];
    }

    foreach(fusedesk_partners() as $partnername => $partner)
    {
        if(fusedesk_checkpartner($partnername))
        {
            $appname = false;

            switch($partnername)
            {
                case "imember360":
                    global $i4w;
                    $appname = $i4w->API_NAME;
                    break;

                case "wishlist":
                    global $WishListMemberInstance;
                    $appname = $WishListMemberInstance->GetOption('ismachine');
                    break;

                case "memberium":
                    # $appname = memb_getAppName();
                    global $i2sdk;
                    return $i2sdk->isdk->getAppName();
                    break;

                case "wisp":
                    global $sb_wisp;
                    return $sb_wisp->settings->is_app_name;
                    break;

                default:
                    break;
            }

            if($appname)
            {
                return $appname;
            }
        }
    }

    return '';
}

/*
 * Return back the Infusionsoft ContactID for the logged in user (if we have one)
 */
function fusedesk_mycontactid()
{
    foreach(fusedesk_partners() as $partnername => $partner)
    {
        if(fusedesk_checkpartner($partnername))
        {
            switch($partnername)
            {
                case "imember360":
                    return i4w_get_contact_field('id');
                    break;

                case "wishlist":
                    // Only Wishlist 2.9+ stores the contactID...
                    global $WishListMemberInstance;
                    if($WishListMemberInstance->Version >= 2.9)
                    {
                        global $current_user;
                        return $WishListMemberInstance->Get_UserMeta($current_user->ID, "wlminfusionsoft_contactid");
                    }
                    break;

                case "memberium":
                    return memb_getContactId();
                    break;

                case "wisp":
                    global $sb_wisp, $current_user;
                    if($is_user = $sb_wisp->get_user($current_user->ID))
                    {
                        return $is_user['Id'];
                    }
                    break;

                default:
                    break;
            }
        }
    }

    return false;
}

/*
 * Return back the Email Address for the logged in user (if we have one!)
 */
function fusedesk_myemail()
{
    if(is_user_logged_in()) {
        global $current_user;
        get_currentuserinfo();
        return $current_user->user_email;
    }

    return false;
}

/*
 * Return back the logged in user's name
 */
function fusedesk_myname()
{
    if($myinfo = fusedesk_myinfo())
    {
        return trim($myinfo['firstname'].' '.$myinfo['lastname']);
    } else {
        return false;
    }
}

/*
 * Return back an array of info about the currently logged in user
 */
function fusedesk_myinfo()
{
    if(is_user_logged_in()) {
        global $current_user;
        get_currentuserinfo();
        return array(
            'firstname' => $current_user->user_firstname,
            'lastname' => $current_user->user_lastname,
            'email' => $current_user->user_email,
            );
    }

    return false;
}

/*
 * Display a form to create a new case
 */
function fusedesk_newcase($atts, $content)
{
    $atts = shortcode_atts(array(
        'department' => false,
        'rep'  => false,
        'hideknowndata' => false,
        'showtitle' => false,
        'titletext' => 'Briefly, what is this request about?',
        'buttontext' => 'Create Support Case',
        'nametext' => 'Your Name',
        'emailtext' => 'Your Email Address',
        'messagetext' => 'How can we help you?',
        'creatingtext' => 'Submitting Case...<br/><img src="'.plugins_url( 'ajax-loader-bar.gif' , __FILE__ ).'">',
        'successtext' => 'Thanks! Your case has been created. We will get back to you shortly.',
        'suggestionstext' => 'May we suggest one of the following posts?',
        'suggestionlimit' => 10,
        'suggestionplacement' => 'after',
        'suggestioncategories' => '',
        'casetagids' => '',
        'class' => false,
        'style' => false,
        'table' => false,
    ), $atts);

    if(!$atts['rep'])
    {
        $atts['rep'] = get_option('fusedesk_defaultrep');
    }

    if(!$atts['department'])
    {
        $atts['department'] = get_option('fusedesk_defaultdepartment');
    }

    $inputstyle = $atts['style'] ? ' style="'.$atts['style'].'"' : '';
    $inputclass= $atts['class'] ? " ".$atts['class'].'"' : '';

    $suggestionbox = '<div id="fusedesk-suggestions" style="display: none;" data-limit="'.$atts['suggestionlimit'].'" data-categories="'.$atts['suggestioncategories'].'"><span>'.$atts['suggestionstext'].'</span><ul style="list-style: none;"></ul></div>';

    # This atrocious one-long-line formatting is to prevent dang WP from adding line break after every input! ARG!
    $ret = '<form id="fusedesk-contact" action="#">'.
    '<input type="hidden" name="action" value="fusedesk_newcase">'.
    '<input type="hidden" name="repid" value="'.$atts['rep'].'">'.
    '<input type="hidden" name="depid" value="'.$atts['department'].'">'.
    '<input type="hidden" name="casetags" value="'.$atts['casetagids'].'">'.
    '<input type="hidden" name="opened_from" value="http'.(isset($_SERVER['HTTPS']) ? 's' : '').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'">';

    $ret .= ($atts['table']) ? '<table id="fusedesk-contact-table">':'';

    if($contactid = fusedesk_mycontactid())
    {
        $ret .= '<input type="hidden" name="contactid" id="fusedesk-contactid" value="'.$contactid.'">';
    }

    if($name = fusedesk_myname() and $atts['hideknowndata'])
    {
        $ret .= '<input type="hidden" name="openedby" id="fusedesk-contact-name" value="'.htmlentities($name).'">';
    } else {
        $ret .= (($atts['table']) ? '<tr><td>':'').
            $atts['nametext'].
            (($atts['table']) ? '</td><td>':': ').
            '<input type="text" name="openedby" id="fusedesk-contact-name" value="'.htmlentities($name).'" class="fusedesk-contactform'.$inputclass.'"'.$inputstyle.'>'.
            (($atts['table']) ? '</td></tr>':'').
            "\n";
    }

    if(($email = fusedesk_myemail()) and $atts['hideknowndata'])
    {
        $ret .= '<input type="hidden" name="email" id="fusedesk-contact-email" value="'.htmlentities($email).'">';
    } else {
        $ret .= (($atts['table']) ? '<tr><td>':'').
            $atts['emailtext'].
            (($atts['table']) ? '</td><td>':': ').
            '<input type="text" name="email" id="fusedesk-contact-email" value="'.htmlentities($email).'" class="fusedesk-contactform'.$inputclass.'"'.$inputstyle.'>'.
            (($atts['table']) ? '</td></tr>':'').
            "\n";
    }

    if($atts['showtitle'])
    {
        $ret .= (($atts['table']) ? '<tr><td>':'').
            $atts['titletext'].
            (($atts['table']) ? '</td><td>':'').
            '<input type="text" name="summary" id="fusedesk-title" value="" class="fusedesk-contactform'.$inputclass.'"'.$inputstyle.'>'.
            (($atts['table']) ? '</td></tr>':'').
            "\n";
    } else {
        $ret .= '<input type="hidden" name="summary" value="Support Request">';
    }

    $ret .= $content.
        (($atts['table']) ? '<tr><td colspan="2">':'').
        (($atts['suggestionplacement'] == 'before') ? $suggestionbox : '').
        $atts['messagetext'].
        '<textarea name="details" id="fusedesk-message" class="fusedesk-contactform'.$inputclass.'"'.$inputstyle.'></textarea>'.
        (($atts['suggestionplacement'] == 'after') ? $suggestionbox : '').
        (($atts['table']) ? '</td></tr><tr><td></td><td>':'').
        '<input type="button" id="fusedesk-contactform-submit" value="'.$atts['buttontext'].'">'.
        (($atts['table']) ? '</td></tr></table>':'').
        '</form>'.
        (($atts['suggestionplacement'] == 'end') ? $suggestionbox : '').
        '<div id="fusedesk-caseopened" style="display: none;">'.$atts['successtext'].'</div>'.
        '<div id="fusedesk-casecreating" style="display: none;">'.$atts['creatingtext'].'</div>';

    return $ret;
}

/*
 * Display the logged in user's cases
 * Content is discarded, so just use [fusedesk_cases /]
 */
function fusedesk_mycases($atts, $content)
{
    $atts = shortcode_atts(array(
        'columns' => 'casenum,date_updated,status,summary,',
        'display'  => 'all',
        'errornotloggedin' => "Please login to view your cases.",
        'errornocases'  => "Looks like you don't have any support cases!"
    ), $atts);

    $args = array();
    if($contactid = fusedesk_mycontactid())
    {
        $args['contactid'] = $contactid;

    } elseif ($email = fusedesk_myemail()) {
        $args['email'] = $email;

    } else {
        # No way to search!
        return $atts['errornotloggedin'];
    }

    if($cases = fusedesk_apicall('/cases/', $args))
    {
        if($cases->error)
        {
            # ToDo: allow options for how to report this error
            return $atts['errornocases']."<!-- FuseDesk Error: ".$cases->error." -->";
        }

        $ret = '';

        $columns = explode(',' , $atts['columns']);

        $friendlycols = array(
            'casenum'   => 'Case Number',

        );

        $ret .= "<table>";

        $ret .= "\n\t<thead><tr>";
        foreach($columns as $col)
        {
            $ret .= "\n\t\t<td>".(array_key_exists($col, $friendlycols) ? $friendlycols[$col] : ucwords(preg_replace('/\_/', ' ', $col)))."</td>";
        }
        $ret .= "\n\t</tr></thead>";

        foreach($cases as $case)
        {
            $ret .= "\n\t<tr>";
            foreach($columns as $col)
            {
                 $ret .= "\n\t\t<td>".(property_exists($case, $col) ? $case->$col : '')."</td>";
            }
            $ret .= "\n\t</tr>";

#            $ret .= $case->id;
        }

        $ret .= "\n</table>";

        return $ret;
    } else {
        return $atts['errornocases'];
    }
}

/*
 * Process FuseDesk AJAX case creation
 */
function fusedesk_ajax_newcase()
{
    foreach(array('depid', 'repid', 'summary', 'details', 'openedby', 'contactid', 'email', 'casetags') as $field)
    {
        if(array_key_exists($field, $_POST))
        {
            $args[$field] = $_POST[$field];
            # Let's keep email in the note text in case we can't link it up...
            if($field != 'email')
            {
                unset($_POST[$field]);
            }
        }
    }

    unset($_POST['action']);

    foreach(array_keys($_POST) as $key)
    {
        $args['details'] .= "\n".ucwords(preg_replace('/\_/', ' ',$key)).": ".$_POST[$key];
    }

    echo(json_encode(fusedesk_apicall("/cases/", $args, 'POST')));
    die();
}

/*
 * Process FuseDesk AJAX case search
 */
function fusedesk_ajax_search()
{
    if(!array_key_exists('q', $_GET))
    {
        echo(json_encode(array('error' => "Missing required parameter 'q'")));
        die();
    } elseif (!trim($_GET['q'])) {
        echo(json_encode(array('error' => "Empty search")));
        die();
    }

    $search_query = array(
        's' => trim($_GET['q']),
        'post_status' => 'publish'
    );

    // Are we filtering for only certain categories?
    if(array_key_exists('categories', $_GET) and $_GET['categories']) {
        // Do we need/want to sanitize this or will WP_Query handle that for us?
        $search_query['category_name'] = $_GET['categories'];
    }

    $query = new WP_Query($search_query);

    $results = array();

    $matches = 0;
    $limit = array_key_exists('limit', $_GET) ? 1*$_GET['limit'] : 10;

    if ( $query->have_posts() ) {
        while ( $query->have_posts() and ($matches++ < $limit)) {
            $query->the_post();
            $results[] = array(
                'title' => get_the_title(),
                'url' => get_permalink(),
                'preview' => get_the_excerpt(),
            );
        }
    }

    echo(json_encode(array(
        'query' => $search_query['s'],
        'count' => 1*$query->found_posts,
        'limit' => $limit,
        'results' => $results,
    )));
    die();
}

add_action('wp_ajax_fusedesk_newcase', 'fusedesk_ajax_newcase');        # for logged in users
add_action('wp_ajax_fusedesk_search', 'fusedesk_ajax_search');        # for logged in users
add_action('wp_ajax_nopriv_fusedesk_newcase', 'fusedesk_ajax_newcase'); # for non-logged in visitors
add_action('wp_ajax_nopriv_fusedesk_search', 'fusedesk_ajax_search'); # for non-logged in visitors

/*
 * This (and the subsequent add_action) loads up the needed JS to send ajax queries...
 */
function fusedesk_ajax_load_scripts()
{
    // load our javascript (jQuery)
    wp_enqueue_script( "fusedesk-ajax", plugin_dir_url( __FILE__ ) . 'fusedesk-ajax.js', array( 'jquery' ) );

    // setup the ajaxurl variable for above
    wp_localize_script( 'fusedesk-ajax', 'the_ajax_script', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
}
add_action('wp_print_scripts', 'fusedesk_ajax_load_scripts');


/*
 * Internal function used to make FuseDesk API Calls
 */
function fusedesk_apicall($url, $args = array(), $type = 'GET')
{
    if(!get_option('fusedesk_apikey') || !get_option('fusedesk_appname'))
    {
        return false;
    }

    $fullurl = 'https://'.get_option('fusedesk_appname').'.fusedesk.com/api/v1'.$url;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $fullurl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-FuseDesk-API-Key: '.get_option('fusedesk_apikey')));

    if($type == 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $args);
    } else {
        # If we're making a GET request, append the args to the end of the URL
        curl_setopt($ch, CURLOPT_URL, $fullurl.'?'.http_build_query($args));
    }
    return json_decode(curl_exec($ch));
}


?>