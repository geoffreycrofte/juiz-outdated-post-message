<?php
	// Prevent direct access to the plugin
	if ( ! defined('ABSPATH') ) {
		exit;
	}
	echo '
		<!-- '.JUIZ_ODPM_PLUGIN_NAME.' styles -->
		<style rel="stylesheet">
			#juiz-odpm .error { margin: 20px 20px 20px 48px; }
			#juiz-odpm .submit { margin-bottom: 3em }
			#juiz-odpm th .submit { font-weight:normal; margin-bottom: 0 }
			#juiz-odpm .error em { font-weight: normal; }
			#juiz-odpm .jodpm_info { max-width:800px; padding: 15px; margin-left: 48px; color: #888; line-height: 1.6;  background: #fff; box-shadow: 0 0 3px rgba(0,0,0,.1);}
			#juiz-odpm h3 { font-size: 1.65em; color: #444; font-weight:normal; }
			#juiz-odpm table + h3 { margin-top: 3em;}
			.juiz_odpm_section_intro {font-style: italic; color: #777; }
			#juiz-odpm form {padding-left:45px}
			#juiz-odpm th {font-weight:bold; padding-left:0}
			#juiz-odpm th em {font-weight:normal;font-style: italic; color: #777;}

			#juiz-odpm input[type="radio"] + label { display: inline-block; vertical-align: middle; margin-right: 20px;}

			.juiz_odpm_options_p { margin: .2em 5% .2em 0; }
			.juiz_odpm_styles_options label { vertical-align:top;}
			.juiz_odpm_styles_options input { vertical-align:8px;}

			.juiz_odpm_style_name { display:inline-block; margin: 4px 0 0 2px; color: #777;}

			.juiz_short_input {width: 70px;}
			.juiz_long_input {width: 100%; max-width: 450px;}
			textarea.juiz_long_input {height:6em}
			.juiz_bottom_links {margin-bottom:0;border-top: 1px solid #ddd; background: #fff; padding: 10px 45px; }
			.juiz_paypal, .juiz_twitter, .juiz_rate {display: inline-block; margin-right: 10px; padding: 3px 12px; text-decoration: none; border-radius: 3px;
				background-color: #e48e07; background-image: -webkit-linear-gradient(#e7a439, #e48e07); background-image: linear-gradient(to bottom, #e7a439, #e48e07); border-width:1px; border-style:solid; border-color: #e7a439 #e7a439 #ba7604; box-shadow: 0 1px 0 rgba(230, 192, 120, 0.5) inset; color: #FFFFFF; text-shadow: 0 1px 0 rgba(0, 0, 0, 0.1);}
			.juiz_twitter {background-color: #1094bf; background-image: -webkit-linear-gradient(#2aadd8, #1094bf); background-image: linear-gradient(to bottom, #2aadd8, #1094bf); border-color: #10a1d1 #10a1d1 #0e80a5; box-shadow: 0 1px 0 rgba(120, 203, 230, 0.5) inset;}
			.juiz_rate {background-color: #999; background-image: -webkit-linear-gradient(#888, #666); background-image: linear-gradient(to bottom, #888, #666); border-color: #777 #777 #444; box-shadow: 0 1px 0 rgba(180, 180, 180, 0.5) inset;}
			.juiz_paypal:hover { color: #fff; background: #e48e07;}
			.juiz_twitter:hover { color: #fff; background: #1094bf;}
			.juiz_rate:hover { color: #fff; background: #666;}

			.juiz_disabled th {color: #999;}

			.juiz_bottom_links em {display:block; margin-bottom: .5em; font-style:italic; color:#777;}
			.juiz_hide {display:none;}

			@media (max-width:640px) {
				#juiz-odpm .jsps_info { margin-left: 0; }
				.juiz_bottom_links { padding: 15px; }
				#juiz-odpm form { padding-left:0;}
				.juiz_bottom_links a { margin-bottom: 5px;}
				#juiz-odpm .juiz_sps_styles_options input[type="radio"] + label {margin-right:0}
				#jsps_style_5 {vertical-align: 21px;}
			}
			</style>
		<!-- end of '.JUIZ_ODPM_PLUGIN_NAME.' styles -->

		<!-- '.JUIZ_ODPM_PLUGIN_NAME.' scripts -->
		<script>
		</script>
		<!-- end of '.JUIZ_ODPM_PLUGIN_NAME.' scripts -->
	';
