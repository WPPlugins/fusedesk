<?php
    $apikeygood = false;
    $apibase = false;

    if(get_option('fusedesk_appname') and get_option('fusedesk_apikey'))
    {
        $apibase = 'https://'.get_option('fusedesk_appname').'.fusedesk.com/api/v1/';
        $ch = curl_init($apibase.'departments');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-FuseDesk-API-Key: '.get_option('fusedesk_apikey')));
        $response = json_decode(curl_exec($ch));
        if($response and !$response->error)
        {
            $apikeygood = true;
        } elseif ($error = curl_error($ch)) {
            ?><div id="message" class="error"><p>
                There was an error connecting to your FuseDesk App: <?php echo($error); ?>
            </p></div>
            <?php
        }
    }
?>

<div class="wrap" xmlns="http://www.w3.org/1999/html">
    <div id="icon-tools" class="icon32"></div>
    <h2>FuseDesk Options</h2>

    <form method="post" action="options.php">
        <?php wp_nonce_field('update-options'); ?>
        <?php settings_fields('fusedesk'); ?>

        <p>
            Please enter your Infusionsoft/FuseDesk <a href="#appname">application name</a> and your FuseDesk <a href="#apikey">API Key</a> below.
        </p>

        <?php
        if(!get_option('fusedesk_apikey') && (array_key_exists('apikey', $_GET)))
        {
            echo('<div id="message" class="updated"><p>Your API Key for FuseDesk has been auto-filled into the form below. Make sure to click the <b>'.translate('Save Changes').'</b> button below.</p></div>');
        }
        ?>

        <table class="form-table">

            <tr valign="top">
                <th scope="row"><a href="#appname">App Name</a>:</th>
                <td><code>https://<input type="text" name="fusedesk_appname" id="fusedesk_appname" value="<?php echo(fusedesk_guessappname()); ?>" />.FuseDesk.com</code>
                <?php if(!get_option('fusedesk_apikey')) { ?>
                    <input type="button" id="fusedesk_getkey" value="Get API Key" class="button-secondary"></td>
                <?php } ?>
            </tr>

            <tr valign="top">
                <th scope="row"><a href="#apikey">API Key</a>:</th>
                <td><input type="text" name="fusedesk_apikey" id="fusedesk_apikey" value="<?php echo(
                    get_option('fusedesk_apikey') ? get_option('fusedesk_apikey') :
                        (array_key_exists('apikey', $_GET) ? $_GET['apikey'] : '')
                    ); ?>" size="32" />
                    <input type="button" id="fusedesk_testbutton" value="Test" class="button-secondary">
                </td>
            </tr>
        </table>

        <?php if($apikeygood) { ?>

        <h2>Departments</h2>

        <p>
            The following departments are currently setup in your FuseDesk App. Pick your default department below and make
            note of the IDs of any other departments if you want to override and use that department on a new case form.
        </p>
        <ul>

            <?php

            if($response and !$response->error)
            {
                foreach($response as $dep)
                {
                    echo('<li><input name="fusedesk_defaultdepartment" value="'.$dep->departmentid.
                        '" type="radio" '.checked(get_option('fusedesk_defaultdepartment'), $dep->departmentid, false).
                        '> '.$dep->name.' ('.$dep->departmentid.')');
                }
            } else {
                echo("No departments found?");
            }
            ?>
        </ul>

        <h2>Support Reps</h2>

        <p>
            The following support reps are currently setup in your FuseDesk App. Pick your default rep below and make
            note of the IDs of any other reps if you want to override and use that rep on a new case form.
            <ul>
        </p>

        <?php
            curl_setopt($ch, CURLOPT_URL, $apibase.'reps');
            $response = json_decode(curl_exec($ch));
            if($response and !$response->error)
            {
                array_unshift($response, (object)array(
                    'userid' => '',
                    'firstname' => 'Assign to a random',
                    'lastname' => 'rep in the default department',
                    'active' => true
                ));
                foreach($response as $rep)
                {
                    # Show only acive reps...
                    if($rep->active)
                    {
                        echo('<li><input name="fusedesk_defaultrep" value="'.$rep->userid.
                            '" type="radio" '.checked(get_option('fusedesk_defaultrep'), $rep->userid, false).
                            '> '.$rep->firstname.' '.$rep->lastname.
                            ($rep->userid ? ' ('.$rep->userid.')':''));
                    }
                }
            } else {
                echo("No reps found?");
            }
        ?>
            </ul>



<?php
    } else {
        if(!array_key_exists('apikey', $_GET))
        {
?>

        <h2>Don't Have FuseDesk?</h2>

        <p>
            No problem! Grab your free, no credit card required, no time limit, fully functionally
            <a href="https://www.fusedesk.com/?utm_campaign=WordPress-Plugin&utm_source=WordPress-Plugin-Link">FuseDesk Demo app</a>
            today to handle all of your customer support cases with 100% Infusionsoft integration!
        </p>

            <p>
                <a href="https://www.fusedesk.com/?utm_campaign=WordPress-Plugin&utm_source=WordPress-Plugin-Button" class="button">Activate My FuseDesk App</a>
            </p>

<?php } } ?>

        <input type="hidden" name="action" value="update" />

        <p class="submit">
            <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
        </p>

    </form>


    <h2>Third-Party Integrations</h2>

    <p>
        FuseDesk plays very nicely with a number of great plugins. Simply install these for increased functionality.
    </p>

    <table>
        <thead>
        <tr>
            <td>Plugin</td>
            <td>Installed?</td>
            <td>Functionality</td>
        </tr>
        </thead>
        <?php foreach(fusedesk_partners() as $partnername => $partner) { ?>
    <tr>
        <td><a href="<?php echo($partner['site']); ?>" target="_blank"><?php echo($partner['name']); ?></a></td>
        <td><?php echo(fusedesk_checkpartner($partnername) ? "<span style=\"color:green\"><strong>Installed!</strong></span> :)":"Not Installed"); ?></td>
        <td>
            <?php
            if($partner['appname']) { echo("Pre-filled App Name. "); }
            if($partner['contactid']) { echo("Instant new case link. "); }
            if($partner['contact']) { echo("Pre-filled contact info. "); }
            ?>
            </td>
    </tr>
        <?php } ?>
    </table>

    <?php foreach(fusedesk_partners() as $partnername => $partner) {
        if(fusedesk_checkpartner($partnername) and $partner['knownissues']) { ?>
            <p><b>Known Issues for <?php echo($partner['name']); ?></b><ol>
                    <?php foreach($partner['knownissues'] as $issue) { ?>
                        <li><?php echo($issue); ?></li>
    <?php } ?>
                    </ol>
    <?php }} ?>

<?php if($apikeygood) { ?>
    <h2>Getting Started</h2>

    <p>
        Let customers open new cases! Add <code>[fusedesk_newcase /]</code> to your Contact Us page!
    </p>

    <p>
        Let customers see their cases! Add <code>[fusedesk_mycases /]</code> to a new page for logged in users only!
    </p>

    <h2>Advanced Usage</h2>

    <h3>New Case Form</h3>

    <p>
        To allow folks to open a new support case, use the <code>[fusedesk_newcase]</code> shortcode on a page. This will
        display a form.
    </p>

    <p>
        <code>[fusedesk_newcase]</code> works perfectly by itself, but also supports the following optional parameters:
    </p>

    <ul class="ul-disc">
        <li>department: The ID of the department to assign the case to. Defaults to your default department above.</li>
        <li>rep: The ID of the rep to assign the case to. Defaults to your default rep above.</li>
        <li>casetagids: Comma separated list of FuseDesk Case Tag IDs that you want to apply to new cases. Defaults to not applying any case tags.</li>
        <li>hideknowndata: Hide the name and/or email fields if we know who the user is (i.e. they're logged in.)
            Defaults to showing the fields pre-filled in</li>
        <li>nametext: The label for the name field. Defaults to "Your Name"</li>
        <li>emailtext: The label for the email field. Defaults to "Your Email Address"</li>
        <li>messagetext: The label for the message box. Defaults to 'How can we help you?</li>
        <li>buttontext: What to show on the button to create the case. Defaults to "Create Support Case"</li>
        <li>creatingtext: What to show while a case is being submitted. Defaults to 'Submitting Case..."</li>
        <li>successtext: What to show after the case is created. Defaults to "Thanks! Your case has been created. We will get back to you shortly."</li>
        <li>class: What CSS class to apply to our inputs</li>
        <li>style: What CSS style to apply to our inputs</li>
        <li>table: Display the inputs in a table instead of breaking with newlines. Note that if you include any
            additional content (see below) and you turn table output on, you'll need to put your additional content in
            table rows and cells with <code>&lt;tr&gt;</code> and <code>&lt;td&gt;</code> tags</li>
        <li>showtitle: Prompt for a case title. Defaults to false (i.e. don't ask for a case title)</li>
        <li>titletext: The prompt for the case title/subject. Defaults to "Briefly, what is this request about?"</li>
        <li>suggestionstext: The label for suggested posts. Defaults to "May we suggest one of the following posts?"</li>
        <li>suggestionlimit: How many posts to suggest. Defaults to 10.</li>
        <li>suggestioncategories: Comma separated list of category names/slugs you want to restrict suggestions to. Defaults to all categories.</li>
        <li>suggestionplacement: Where to place the suggested posts. Can be set to <code>before</code> (default), <code>after</code>, <code>end</code>, or <code>none</code> (to disable)</li>
    </ul>

    <p>
        Again, all of these parameters are optional but they allow you to override any of the defaults.
    </p>

    <p>
        An example to not show the name or email if present, changing the new case button text and change the name text:<br/>
        <code>[fusedesk_newcase hideknowndata="true" nametext="Primary Contact" buttontext="Get Help!" /]</code>
    </p>

    <p>
        Whatever content you put between <code>[fusedesk_newcase]</code> and <code>[/fusedesk_newcase]</code> tags will
        be added into the case details.
    </p>

    <p>
        For example, if you wanted to include some hidden data about the customer, you could put a hidden input inside,
        like <br/>
        <code>[fusedesk_newcase]&lt;input type="hidden" name="website" value="http://members.mysite.com/"&gt;[/fusedesk_newcase]</code>
    </p>

    <p>
        You could also add some additional options and fields, for example: <br/><code>[fusedesk_newcase]Order #:
        &lt;input type="text" name="order_number"&gt;[/fusedesk_newcase]</code>
    </p>

    <p>
        You could even go all out and add in a drop down, like:<br/>
        <code>[fusedesk_newcase]&lt;select name="cruise_type"&gt;
        &lt;option value="Bahamas"&gt;Bahamas&lt;option&gt;<br/>
        &lt;option value="Alaska"&gt;Alaska&lt;option&gt;<br/>
        &lt;option value="Mediterranean"&gt;Mediterranean&lt;option&gt;<br/>
        &lt;/select&gt;[/fusedesk_newcase]</code>
    </p>

    <p>
        If you name your additional fields with underscores, like cruise_type, operating_system, favorite_place_to_hike,
        etc..., we'll automatically translate those to title case, i.e. "Cruise Type", and include them all in the case details.
    </p>

    <p>
        <b>Note:</b> The examples above are using HTML input fields. These need to be added by editing your page in HTML mode.
    </p>

    <p>
        <b>Second Note:</b> Tf you include any additional content pre above and you turn table output on, you'll need to
        put your additional content in table rows and cells with <code>&lt;tr&gt;</code> and <code>&lt;td&gt;</code> tags
    </p>

    <p>
        To <b>style your form</b> and form elements, either pass in the <code>style</code> parameter or <code>class</code>
        parameter or add entries for each of our elements into your style sheet. Pre-set IDs and classes include:
    </p>
    <ul class="ul-disc">
        <li>.fusedesk-contactform: all form elements have this class</li>
        <li>#fusedesk-contact: the actual form</li>
        <li>#fusedesk-contact-table: the form table, if enabled</li>
        <li>#fusedesk-contact-email: the email address input field</li>
        <li>#fusedesk-contact-name: the name input field</li>
        <li>#fusedesk-title: the title input field</li>
        <li>#fusedesk-message: the textarea for the case details</li>
        <li>#fusedesk-contactform-submit: the submit button</li>
        <li>#fusedesk-suggestions: the div that holds the suggestions list</li>
    </ul>

    <h3>Displaying a Customer's Cases</h3>

    <p>
        To display a list of cases for the logged in user, use the <code>[fusedesk_mycases]</code> shortcode on a page.
        This will display a table listing all of their cases.
    </p>

    <p>
        <code>[fusedesk_mycases]</code> works wonders by itself, but also supports the following optional parameters:
    </p>

    <ul class="ul-disc">
        <li>columns: A comma delimted list of which columns to show. Choose from casenum, date_updated, date_opened,
            date_closed, status, summary, details. Defaults to "casenum,date_updated,status,summary"</li>
        <li>display: Which cases to show. Can be All, New, Active, Open, Closed. Defaults to All</li>
        <li>errornotloggedin: What message to show to customers who aren't logged in (since we won't be able to show
            cases to anyone who's not logged in. Defaults to "Please login to view your cases." It is highly recommended
            that you add only put this code on pages visible to logged in users only.</li>
        <li>errornocases: What message to show to customers where they don't have any cases. Defaults to "Looks like
            you don't have any support cases!"</li>
    </ul>

<?php } ?>

    <a name="apikey"></a><h2>Finding your API Key</h2>

    <?php if(!get_option('fusedesk_apikey')) { ?>
    <p>
        Getting your FuseDesk API Key couldn't be easier! Simply enter your App Name above, then click on the
        <b>Get API Key</b> button and follow the instructions.
    </p>
    <?php } else { ?>
    <p>
        To setup a FuseDesk API Key, you'll need to:
    <ol>
        <li>Log into your FuseDesk App as an admin</li>
        <li>Click on Preferences</li>
        <li>Click on API Keys</li>
        <li>Use an existing API Key or create a new one</li>
        <li>Copy the Key to your clipboard and paste it above.</li>
    </ol></p>
    <?php } ?>

</div>


<script type="text/javascript">
    function fusedesk_testkey(appname, apikey) {

    }

    jQuery(function() {
        jQuery('#fusedesk_testbutton').click(function() {

            if(!jQuery('#fusedesk_appname').val())
            {
                alert("Please enter your application name.");
                jQuery('#fusedesk_appname').focus();
                return false;
            } else if (!jQuery('#fusedesk_apikey').val()) {
                alert("Please enter your API Key");
                return false;
            }

            jQuery.post("https://" + jQuery('#fusedesk_appname').val() + ".fusedesk.com/api/v1/usage", {apikey: jQuery('#fusedesk_apikey').val()}, function(response) {
                if(response.error)
                {
                    alert("Uh oh, looks like that API Key isn't valid: " + response.error);
                } else {
                    alert("API Key and App name are good! Click <?php _e('Save Changes') ?> to save your changes!");
                }
            }).fail(function(response, textStatus, errorThrown) {
                        alert("Uh oh. Looks like that API Key isn't valid...");
                    });
        });

        jQuery('#fusedesk_appname').blur(function() {
            jQuery('#fusedesk_appname').val(
                    jQuery('#fusedesk_appname').val().
                            replace(/^.*\/\//,'').
                            replace(/\.(fusedesk|infusionsoft).*/ ,''));
            return true;
        });

        jQuery('#fusedesk_getkey').click(function() {
            if(jQuery('#fusedesk_appname').val()) {
                window.location = "https://" + jQuery('#fusedesk_appname').val() + ".fusedesk.com/account/apikeys/?newkey=<?php
                echo(urlencode(get_bloginfo('name'))); ?>&scopes=reps|departments|cases|cases|cases_create|cases_update&returnurl=<?php
                echo(urlencode(get_bloginfo('wpurl').'/wp-admin/options-general.php?page=fusedesk')); ?>";
            } else {
                alert("Please enter your application name.");
                jQuery('#fusedesk_appname').focus();
                return false;
            }
        });
    });
</script>