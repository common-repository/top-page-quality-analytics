<?php
/*
	Plugin Name: TOP Page Quality Analytics
	Plugin URI: https://www.janhvizdak.com/top-plugin.html
	Description: A plugin for improving SEO and understanding the Google Panda by connecting a blog with the TOP Website Quality Analytics application which measures the quality of landing pages.
	Version: 2.8.6
	Author: Jan Hvizdak
	Author URI: https://www.janhvizdak.com/
	Text Domain: top-analytics
*/


/*  Copyright 2013  Jan Hvizdak  (email : postmaster@aqua-fish.net)

	This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

	You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301,  USA
*/

	class top_analytics {

		const version		= "2.8.6";						//aktualna verzia
		const top_url		= "https://www.janhvizdak.com/top";			//adresa na top aplikaciu
		const my_url		= "https://www.janhvizdak.com/";				//adresa na moju stranku
		const my_name		= "Jan Hvizdak";					//autor
		const my_table		= "tracking_app";					//nazov tabulky
		const tracker_uri	= "https://www.janhvizdak.com/top/general.js";		//url pre trackera
		const page_cookies	= "/cookies.php";					//stranka, ako top pouziva cookies
		const page_security	= "/security.php";					//stranka, ako je to s bezpecnostou na top

		//css styly
		public function styles()
			{
				global $wpdb;

				//nalinkujeme jquery
				wp_enqueue_script("jquery");

				//vlozime css
				wp_register_style( 'top-tracking', plugins_url('style.css', __FILE__) );
				wp_enqueue_style( 'top-tracking' );

				return true;
			}

		//vytvorenie tabulky
		public function create_table()
			{
				global $wpdb, $top_analytics;

				$sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix . $top_analytics::my_table."` (
`id` bigint(11) unsigned NOT NULL,
UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
				$wpdb->query($sql);

				return true;
			}

		public function javascript()
			{
				//vlozime javascript
				wp_enqueue_script(
					'top-tracking',
					plugins_url('script.js', __FILE__),
					array('jquery')
						);

				return true;
			}

		//zistime, ci ideme cez ssh - ak nie, tracking bude funkcny
		public function is_ssl()
			{
				$secure_connection = false;
				if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443)
					{
						$secure_connection = true;
					}

				return $secure_connection;
			}

		//ziskame identifikator stranky pre TOP app
		public function get_identifier()
			{
				global $wpdb, $top_analytics;

				$sql = "SELECT id FROM ".$wpdb->prefix . $top_analytics::my_table." ;";
				$val = intval($wpdb->get_var($sql));

				if($val!=0)
					return $val;
						else
							return "";
			}

		//pridanie kodu do footra
		public function tracker()
			{
				global $top_analytics;

				$id = $top_analytics->get_identifier();

				if( ($id!='') && ($top_analytics->is_ssl()===false) )
					echo "<!--project TOP-->
<script type=\"text/javascript\">
var TOP_identifier = ".$id.";
</script>
<script type=\"text/javascript\" src=\"".$top_analytics::tracker_uri."\"></script>
<!--end project TOP-->";
			}

		//vypiseme pokec cez javascript
		public function msg($in)
			{
				echo "<script type=\"text/javascript\">
topAnalyticsObj.mesg('".$in."');
</script>";
			}

		//vrati ok, alebo nie ok, ak tracking bezi
		public function is_installed()
			{
				global $top_analytics;

				if($top_analytics->get_identifier() != '')
					return 1;
						else
							return 0;
			}
			
		//lokalizacia
		function analytics_init()
			{
				$plugin_dir = basename(dirname(__FILE__));
				load_plugin_textdomain( 'top-analytics', false, $plugin_dir );
			}

		//hlavna obrazovka
		public function main_screen()
			{
				global $wpdb, $top_analytics;

				$top_analytics->create_table();

				if($_POST['identifier_top']!='')
					{
						$val = intval((string)$_POST['identifier_top']);

						if( ($val!=0) && ((string)$val==(string)$_POST['identifier_top']) )
							{
								//vyprazdnime tabulku
								$sql = "TRUNCATE ".$wpdb->prefix . $top_analytics::my_table." ;";
								$wpdb->query($sql);

								$sql = "INSERT INTO ".$wpdb->prefix . $top_analytics::my_table." VALUES ( ".$val.") ;";
								$wpdb->query($sql);
							}
	
						$new_id = (string)$top_analytics->get_identifier();
						$post_id= (string)$_POST['identifier_top'];
						if( $new_id == $post_id )
							$top_analytics->msg('OK!');
								else
									$top_analytics->msg('Update has failed, ensure you are typing number, please!');
					}

				echo "<div id=\"top_tracking\">
";
				echo "<h1>
";

				_e( 'Welcome to TOP Page Quality Analytics plugin', 'top-analytics');

				echo " v. ".$top_analytics::version." ";
				
				_e( 'by', 'top-analytics');
				
				echo " <a href=\"".$top_analytics::my_url."\" title=\"";
				
				_e( 'Author&rsquo;s website', 'top-analytics');
				
				echo "\" target=\"_blank\">".$top_analytics::my_name."</a></h1>
<p>";

				_e( 'This plugin allows anyone connect his/her WordPress with Website Quality Analytics tool which analyses quality of landing pages, offers advice regarding content and search terms that produce low-quality visits.','top-analytics');

				echo "</p>
				
<p>";

				_e( 'All you need is an ID (identifier) which can be acquired after ', 'top-analytics');
				
				echo " <a href=\"".$top_analytics::top_url."\" title=\"";
				
				_e( 'Get your ID now!', 'top-analytics');
				
				echo "\" target=\"_blank\">";
				
				_e( 'clicking this link', 'top-analytics');
				
				echo "</a>. ";

				_e( 'Once you get ID for your website, put it into the box below in order to allow langing page quality analytics start monitoring which pages and search terms need your attention.', 'top-analytics');

				echo "</p>";
				
				echo "<form id=\"top_saver\" action=\"".str_replace("&","&amp;",$_SERVER['REQUEST_URI'])."\" method=\"post\" enctype=\"application/x-www-form-urlencoded\">
<p>";

				if($top_analytics->is_installed()==1)
					{
						_e( '<span id="running_ok">&#10004; Tracking is running! See your statistics by ', 'top-analytics');
						
						echo " <a href=\"".$top_analytics::top_url."\" target=\"_blank\" title=\"";
						
						_e( 'Login to see statistics and recommendations regarding quality of traffic', 'top-analytics');
						
						echo "\">";
						
						_e( 'clicking this link', 'top-analytics');
						
						echo "</a>!</span>";
					}
						else
							_e( '<span id="running_not">Tracking is not running, enter your ID below, please!</span>', 'top-analytics');
				
				echo "</p>";
				
				_e( 'Your TOP identifier:', 'top-analytics');

				echo " <input type=\"text\" name=\"identifier_top\" placeholder=\"i.e. 454224691\" title=\"";
				
				_e( 'Put your ID into this box and save it', 'top-analytics');
				
				echo "\" value=\"".$top_analytics->get_identifier()."\" required>  <input type=\"submit\" value=\"";
				
				_e( 'Save my ID', 'top-analytics');
				
				echo "\" title=\"";
				
				_e( 'Save my ID now and start tracking', 'top-analytics');
				
				echo "\" />";
				
				echo "<p>";
				
				_e( 'Don&rsquo;t have ID for this website yet?', 'top-analytics');

				echo "<a href=\"".$top_analytics::top_url."\" title=\"";
				
				_e( 'Get your ID now!', 'top-analytics');
				
				echo "\" target=\"_blank\">";
				
				_e( 'Click here to get one!', 'top-analytics');
				
				echo "</a></p>
</form>
";

				_e( '<p>Once you save your ID, your website&rsquo;s traffic is going to be analysed by our application and you&rsquo;ll be able to review quality of its traffic as soon as visitors start visiting it! Since analysis is done on our servers in order not to consume resources and space on yours, you can login to the TOP application by ', 'top-analytics');
				
				echo "<a href=\"".$top_analytics::top_url."\" title=\"";
				
				_e( 'Click to login to the application', 'top-analytics');
				
				echo "\" target=\"_blank\">";
				
				_e( 'clicking this link', 'top-analytics');
				
				echo "</a>.</p>
";

				echo "<hr /><h2>";
				
				_e( 'Why to monitor low quality traffic and why Google Analytics isn&rsquo;t sufficient for this purpose?', 'top-analytics');
				
				echo "</h2>";

				_e( '<p>User behaviour is now monitored by search engines including <a href="http://www.google.com" title="Open Google&rsquo;s search page" target="_blank">Google</a> and <a href="http://www.bing.com" target="_blank" title="Open Bing&rsquo;s search page">Bing</a>/<a href="http://www.yahoo.com" target="_blank" title="Go to Yahoo!">Yahoo!</a> and one of most important factors that affect your rankings is &ldquo;hitting the back button&rdquo;. Low quality traffic from seach engines might reduce your website&rsquo;s total traffic which naturally lowers your income if your website makes money. This in short is a significant part of Google&rsquo;s Panda algorithm.</p>', 'top-analytics');

				_e( '<p>If you&rsquo;ve noticed an overnight decrease or increase in traffic of your website from Google, it could be due to Panda algorithm.</p>', 'top-analytics');

				_e( '<p>What&rsquo;s more Google Analytics doesn&rsquo;t provide you with real length of visit as they measure length of visit until last click which in fact means whenever a visitor comes to your website, spends 10 minutes there reading your article and not making any click, and then leaving your website it&rsquo;s always shown as a zero second visit. It&rsquo;s only search engines that know exact length of visit in such a case. However with TOP you can find out real length of visits too!</p>', 'top-analytics');

				_e( '<p>It is not just time you need to know. In fact this is only the first step of improving your website and beating your competitors! Bear in mind ', 'top-analytics');
				
				echo "<span id=\"upp\">";
				
				_e( 'Search engines prefer sending their users to websites that are relevant to search terms!!!', 'top-analytics');
				
				echo "</span></p>";

				echo "<hr />";

				_e( '<h2>Read more about length of visit</h2>', 'top-analytics');

				echo "<ol>
	<li><a href=\"http://www.nichepursuits.com/how-important-is-time-on-site-for-ranking-in-google/\" target=\"_blank\" title=\"";
	
				_e( 'Article @ nichepursuits.com', 'top-analytics');
				
				echo "\">";
				
				_e( 'How Important is Time on Site for Ranking in Google', 'top-analytics');
				
				echo "</a></li>
	<li><a href=\"http://backlinko.com/google-ranking-factors\" target=\"_blank\" title=\"";
	
				_e( 'Article @ backlinko.com', 'top-analytics');
				
				echo "\">";
				
				_e( 'Google Ranking Factors: The Complete List', 'top-analytics');
				
				echo "</a> ";
				
				_e( '(see point 76)', 'top-analytics');
				
				echo "</li>
	<li><a href=\"http://rankexecutives.com/is-time-on-site-a-ranking-factor/\" target=\"_blank\" title=\"";
	
				_e( 'Article @ rankexecutives.com', 'top-analytics');
				
				echo "\">";
				
				_e( 'Is Time On Site A Ranking Factor', 'top-analytics');
				
				echo "</a></li>
	<li><a href=\"http://www.seobythehour.com/resources/top-seo-ranking-factors/\" target=\"_blank\" title=\"";
	
				_e( 'Article @ seobythehour.com', 'top-analytics');
				
				echo "\">";
				
				_e( 'Top SEO Ranking Factors', 'top-analytics');
				
				echo "</a></li>
</ol>

<hr />";

				_e( '<h2>Privacy &amp; SSL</h2>', 'top-analytics');

				_e( '<p>You should notify visitors of your website that cookies are being used to understand whether they&rsquo;re happy when browsing your website or whether they rather leave. Also there&rsquo;s a restriction within this plugin not to use tracking when visitors browse your website using SSL (https) as this is usually used for online shops and therefore privacy of your visitors is respected! All about how TOP uses/handles cookies and security can be found on following pages:', 'top-analytics');

				echo " <a href=\"".$top_analytics::top_url . $top_analytics::page_cookies."\" title=\"";
				
				_e( 'How TOP handles cookies', 'top-analytics');
				
				echo "\" target=\"_blank\">";
				
				_e( 'cookies', 'top-analytics');
				
				echo "</a> ";

				_e( 'and', 'top-analytics');

				echo " <a href=\"".$top_analytics::top_url . $top_analytics::page_security."\" title=\"";
				
				_e( 'Security of data at TOP', 'top-analytics');
				
				echo "\" target=\"_blank\">";
				
				_e( 'security', 'top-analytics');

				echo "</a>.</p>
</div>
";
			}

		//pridanie hlavnej obrazovky
		public function add_mgmt_page()
			{
				add_management_page('TOP Page Quality Analytics Management', 'TOP Page Quality Analytics', 8, 'top-analytics', 'top_analytics::main_screen');
			}
	}

	$top_analytics = new top_analytics;

	//pridame odkaz do menu
	if(function_exists('add_action'))
		{
			//lokalizacia
			add_action('plugins_loaded', 'top_analytics::analytics_init');
			
			//este styly najprv
			add_action('init', 'top_analytics::styles');
			add_action('init', 'top_analytics::javascript');		//a javascript tiez

			add_action('admin_menu', 'top_analytics::add_mgmt_page');	//admin panel

			add_action('wp_footer', 'top_analytics::tracker');		//pridame do footra
		}
?>
