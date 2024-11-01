<?php
	/*
	Plugin Name: WP Like it? Tweet it!
	Plugin URI: http://pongsocket.com/tweet-it/
	Description: A tweeting widget for visitors to your blog or website
	Version: 0.8.2
	Author: Andy Graulund
	Author URI: http://pongsocket.com/
	License: MIT
	*/
	
	// Copyright (c) 2010 Andy Graulund

	// Permission is hereby granted, free of charge, to any person obtaining a copy
	// of this software and associated documentation files (the "Software"), to deal
	// in the Software without restriction, including without limitation the rights
	// to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
	// copies of the Software, and to permit persons to whom the Software is
	// furnished to do so, subject to the following conditions:

	// The above copyright notice and this permission notice shall be included in
	// all copies or substantial portions of the Software.

	// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
	// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
	// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
	// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
	// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
	// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
	// THE SOFTWARE.
	
	if ( !defined( 'WP_CONTENT_URL' ) )
		define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
	if ( !defined( 'WP_CONTENT_DIR' ) )
		define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
	if ( !defined( 'WP_PLUGIN_URL' ) )
		define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
	if ( !defined( 'WP_PLUGIN_DIR' ) )
		define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );
	if ( !defined( 'WP_LANG_DIR') )
		define( 'WP_LANG_DIR', WP_CONTENT_DIR . '/languages' );
		
	define("TWEET_IT_DEFAULT_TARGET", ".tweet-this a");

	function tweetItStyles(){
		$target   = get_option('tweetit_target');
		$settings = get_option('tweetit_settings');
		$gl       = get_option('tweetit_generatelink', true); // Are we generating the link?
		if($gl && !$target){ $target = TWEET_IT_DEFAULT_TARGET; }
		echo	  ($target ? "\t<script type=\"text/javascript\"> jQuery(document).ready(function(){ jQuery(\"" . addslashes($target) . "\").tweetIt(" . ($settings ? "{ " . $settings . " }" : "") . "); }); </script>" : "");
	}
	
	add_action("wp_head", "tweetItStyles");

	//wp_enqueue_script("jquery");
	wp_enqueue_script("tweet-it", "http://tweet-it.st.pongsocket.com/tweet-it.js", array("jquery"));
	
	// Create custom plugin settings menu
	add_action('admin_menu', 'tweetIt_create_menu');
	
	// Call register settings function
	add_action('admin_init', 'register_mysettings');
	
	// Create settings menu on the plugins page
	add_filter('plugin_action_links', 'tweetIt_filter_plugin_actions', 10, 2);

	function tweetIt_create_menu() {
		// Create new top-level menu
		//add_menu_page('WP Tweet It Plugin Settings', 'Tweet It Settings', 'administrator', __FILE__, 'tweetIt_settings_page');
		add_options_page("WP Tweet It Settings", "WP Tweet It Settings", 8, 'tweet-it', 'tweetIt_settings_page');
	}

	function register_mysettings() {
		// Register our settings
		register_setting('tweetIt-settings-group', 'tweetit_generatelink');
 		register_setting('tweetIt-settings-group', 'tweetit_linktext', 'wp_filter_nohtml_kses');
		register_setting('tweetIt-settings-group', 'tweetit_target');
		register_setting('tweetIt-settings-group', 'tweetit_settings');
	}

	function tweetIt_settings_page() {
		$gl = get_option('tweetit_generatelink', true); // Are we generating the link?
	?>
	<script type="text/javascript">
		var origTarget = "";
		function tweetIt_staticize(checkbox){
			var el = document.getElementById("tweetit_target");
			if(checkbox.checked){
				if(el.type == "text"){ origTarget = el.value; }
				el.type  = "hidden";
				el.value = <?php echo "\"" . addslashes(TWEET_IT_DEFAULT_TARGET) . "\""; ?>;
				document.getElementById("tweetit_static")    .style.display = "inline";
				document.getElementById("tweetit_linktext")  .style.display = "table-row";
				document.getElementById("tweetit_fieldguide").style.textDecoration = "line-through";
			} else {
				el.value = origTarget ? origTarget : el.value;
				el.type  = "text";
				document.getElementById("tweetit_static")    .style.display = "none";
				document.getElementById("tweetit_linktext")  .style.display = "none";
				document.getElementById("tweetit_fieldguide").style.textDecoration = "none";
			}
		}
	</script>
	<div class="wrap">
	<h2>WP &#8220;Like it? Tweet it!&#8221; Settings</h2>

	<form method="post" action="options.php">
		<?php settings_fields( 'tweetIt-settings-group' ); ?>
		<table class="form-table">
			<tr valign="top">
				<th scope="row">Generate &#8220;Tweet it&#8221; link</th>
				<td><input type="checkbox" name="tweetit_generatelink"<?php if($gl){ ?> checked="checked"<?php } ?> onclick="tweetIt_staticize(this)" /> <span class="description">If you check this, the plugin will automatically add a &#8220;tweet this&#8221; link to every post and static page, and all you have to do is style it in the theme.</span></td>
			</tr>
			<tr valign="top" id="tweetit_linktext"<?php if(!$gl){ ?> style="display:none"<?php } ?>>
				<th scope="row">&#8220;Tweet it&#8221; link text</th>
				<td><input type="text" name="tweetit_linktext" value="<?php echo esc_attr(get_option('tweetit_linktext', "Like it? Tweet it!")); ?>" /></td>
			</tr>
			<tr valign="top">
				<th scope="row">Tweet It initialization code</th>
				<td><code>$("</code><input class="code" <?php echo $gl ? "type=\"hidden\"" : "type=\"text\""; ?> name="tweetit_target" id="tweetit_target" value="<?php echo esc_attr(get_option('tweetit_target')); ?>" style="font-size:11px;" /><code id="tweetit_static"<?php if(!$gl){ ?> style="display:none"<?php } ?>><?php echo esc_attr(TWEET_IT_DEFAULT_TARGET); ?></code><code>").tweetIt({</code><input class="code" type="text" name="tweetit_settings" size="50" value="<?php echo esc_attr(get_option('tweetit_settings')); ?>" style="font-size:11px;" /><code>})</code></td>
			</tr>
			<tr valign="top">
				<th scope="row">&nbsp;</th>
				<td style="font-size: 75%;">
					<p>The above is pure JavaScript code and is <strong>not being checked</strong> for validity. In other words, it&#8217;s your own responsibility if you break your site by typing some silly JavaScript. If in doubt of how to fill in this, please be sure that you follow the set up guides found on the <a href="http://pongsocket.com/tweet-it">plugin website</a>.</p>
					<p id="tweetit_fieldguide"<?php if($gl){ ?> style="text-decoration:line-through;"<?php } ?>>The first field is the <strong>target element</strong> of Tweet It; this is a CSS selector to the element that you want to have open the &#8220;Tweet it!&#8221; box on click. Usually it&#8217;s a link element you put somewhere on the post and static page templates in your theme. This one is required.</p>
					<p>The second field is a <strong>list of user-defined options</strong> in JSON format that you can use to change the behaviour of the box. This one is optional.</p>
					<p>Examples:<br /><code>$("<strong style="color:#c00;">.tweet-this a</strong>").tweetIt({})</code><br /><code>$("<strong style="color:#c00;">#tweet-button</strong>").tweetIt({<strong style="color:#c00;"> animate: "fade", header: "Tweet my shit!" </strong>})</code></p>
				</td>
			</tr>
		</table>
		
		<p class="submit"><input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" /></p>
	</form>
	</div>
	<?php }
	
	/**
	 * Borrowed from the Sociable and WP bit.ly plugins, this adds a 'Settings' option to the
	 * entry on the WP Plugins page.
	 *
	 * @param $links array  The array of links displayed by the plugins page
	 * @param $file  string The current plugin being filtered.
	 */

	function tweetIt_filter_plugin_actions( $links, $file ) {
		static $tweetIt_plugin;

		if ( ! isset( $tweetIt_plugin ) )
			$tweetIt_plugin = plugin_basename( __FILE__ );

		if ( $file == $tweetIt_plugin ) {
			$settings_link = '<a href="' . admin_url( 'options-general.php?page=tweet-it' ) . '">' . __( 'Settings', 'tweetIt' ) . '</a>';
			array_unshift( $links, $settings_link );
		}

		return $links;
	}
	
	// IF WE ADD THE LINK FOR THEM
	if(get_option('tweetit_generatelink', true)){
		add_filter("the_content", "tweetIt_create_link");
		function tweetIt_create_link($content){
			if(is_feed() || is_search() || is_home() || is_archive() || is_category()){ return $content; } // None on any of these
			$content .= "\n<p class=\"tweet-this\"><a href=\"javascript://\">" . htmlspecialchars(get_option('tweetit_linktext', "Like it? Tweet it!")) . "</a></p>";
			return $content;
		}
	}
?>