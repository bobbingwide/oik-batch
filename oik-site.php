
<?php // (C) Copyright Bobbing Wide 2014

// mixed parse_url ( string $url [, int $component = -1 ] )

/**
 * Parse the domain into 3 bits
 * 
 * Read WikiPedia to see how confusing domain naming can become.
 * This code attempts to return only three parts
 *  
 *   subdomain - e.g. www, ftp, 
 *   domain - actually anything in between the subdomain and the TLD
 *   TLD - the bits you pay differently for. e.g. .com, .org, .co.uk or .org.uk
 *
 * @param string $domain - a full or partial domain name
 * @returns array - parsed domain name
 * the TLD will either be null or prepended with a period. eg. .com or .co.uk
 *  
 */   
function parse_domain( $domain ) {
  $bits = explode( ".", $domain );
  $tld = null;
  while ( strlen( end( $bits )) <= 3 ) {
    $bit = array_pop( $bits ); 
    $tld = $tld . "." . $bit;  
  }
  
  $cb = count( $bits );
  
  switch ( $cb ) {
    case 0:
      gobang();
      
    case 1:
      $parsed = array( null, $bits[0], $tld );
      break;
    case 2:
      //if ( $tld ) {
        $parsed = array( $bits[0], $bits[1], $tld );
      //} else {
      //  $parsed = array( $bits[0], $bits[1], null );
      //}
      break;
        
    case 3:
    default:
      // The middle bit is a long one
     $subdomain = array_shift( $bits );
     $parsed = array( $subdomain, implode( ".", $bits ), $tld );
            
  } 
  return( $parsed );
}


/**
 *
 */
      

function oik_site_loaded( $argc, $argv ) {
  oik_require( "bobbfunc.inc" );
  require_once( ABSPATH . WPINC . "/feed.php" );
  $domain = bw_array_get( $argv, 1, "wp-a2z" );
  if ( false === strpos( $domain, "http:" ) ) {
    $bits = parse_domain( $domain );
    
    if ( !$bits[2] ) { 
      $bits[2] = bw_array_get( $argv, 2, ".com,.org,.co.uk" );
    } 
    $tlds = bw_as_array( $bits[2] );
    
    foreach ( $tlds as $tld ) { 
      $dom = $bits[1] . $tld ;
      $result = bob_domain_name_test( $dom );
      
      //$result = bw_get_url( bw_build_request( $domain, "feed" ), "domain_name" );
      if ( $result ) {
        bw_handle_feed( $dom );
      }
    }  
  } else { 
     e( "Not checking domain for $domain" );
     bw_handle_feed( $domain );
   
  }
}
  
  
  
function bw_handle_feed( $domain ) {
      $result = fetch_feed( bw_build_request( $domain, "feed" ) );
      e( $feed );
      bw_flush();
      parse_feed( $feed );
      echo PHP_EOL;
      bw_flush();
}


/**
 *
 */
function bob_domain_name_test( $domain ) {
  //e("This is the domain name test" );
  
  $recordexists = dns_check_record( $domain, "ANY");
  if ($recordexists)
  {
    e( "The domain name is registered: " . $domain ) ; 
    $result = true;  
  }
  else
  {
    e( "The domain name is not registered: " . $domain );
    $result = true;
  }   
  return( $result );
}


oik_site_loaded( $_SERVER['argc'], $_SERVER['argv'] );





function bw_build_request( $domain, $file=NULL )
{
  if ( strpos( $domain, ':' ) == 0  )
    $req = "http://".$domain;
  else
     $req = $domain; 
  $req = rtrim( $req, "/" );
  if ( $file <> NULL )
  {
    $req = rtrim( $req, "/" );
    $req .= "/". ltrim( $file, "/" );
  }
  return( $req);       
}



function bw_save_content_as( $content, $save_content_as )
{
  global $contents; 
  if ( $save_content_as <> NULL )
    $contents[ $save_content_as ] = $content;
}  
   

/* Note: The url is prefixed with http:// so we get the http data */            
/* The result is stored in $contents... so you can do something with it */
function bw_get_url( $url, $save_content_as = NULL )
{
  $ret = TRUE;
  
  /* Hide warnings for 404's */
  $er = error_reporting();
  //error_reporting( $er & ~E_WARNING ); 
  $content = file_get_contents( $url );
  //$er = error_reporting( $er ); 
  
  if ( $content === FALSE )
  {
    p( "bong:".  $url );
    $ret = FALSE;
  }  
  else  
  {
    p( "url: " .  $url . " bytes: " . strlen( $content) );
     
  }
  echo $content;
  bw_save_content_as( $content, $save_content_as );
      
  return( $ret );     
} 

/*
   http://whois.domaintools.com/bobbingwide.com
*/
function bw_nslookup( $domain )
{
   $rc = system( "nslookup" . $domain );
   t( "rc from nslookup: ". $rc );
} 

function bw_get_content( $index ) 
{
   global $contents;
   return( $contents[ $index ] );
}


function bw_show_content( $index )
{
  global $contents;
  stag( "td" );
  t( "Contents of:" . $index ); 
  //stag( "pre" );
  
  t( $contents[ $index ] );
  //etag( "pre" );
  etag( "td" );
}  


function bw_gzdecode( $data )
{

  $g=tempnam('/tmp','ff');
  file_put_contents($g,$data);
  ob_start();
  readgzfile($g);
  $d=ob_get_clean();
  return $d;
}


/**
 * Parse the feed from the server 
 <?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0"
        xmlns:content="http://purl.org/rss/1.0/modules/content/"
        xmlns:wfw="http://wellformedweb.org/CommentAPI/"
        xmlns:dc="http://purl.org/dc/elements/1.1/"
        xmlns:atom="http://www.w3.org/2005/Atom"
        xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
        xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
        >

<channel>
        <title>[oik] plugins</title>
        <atom:link href="http://www.oik-plugins.com/feed/" rel="self" type="application/rss+xml" />
        <link>http://www.oik-plugins.com</link>
        <description>WordPress plugins and themes</description>
        <lastBuildDate>Mon, 02 Jun 2014 07:03:00 +0000</lastBuildDate>
        <language>en</language>
                <sy:updatePeriod>hourly</sy:updatePeriod>
                <sy:updateFrequency>1</sy:updateFrequency>
        <generator>http://wordpress.org/?v=3.9.1</generator>
        <item>
                <title>oik v2.3-alpha.0509 now available</title>
                <link>http://www.oik-plugins.com/2014/05/oik-v2-3-alpha-0509-now-available/?utm_source=rss&#038;utm_medium=rss&#038;u
tm_campaign=oik-v2-3-alpha-0509-now-available</link>
                <comments>http://www.oik-plugins.com/2014/05/oik-v2-3-alpha-0509-now-available/#comments</comments>
                <pubDate>Fri, 09 May 2014 14:15:02 +0000</pubDate>
                <dc:creator><![CDATA[Herb Miller]]></dc:creator>
                                <category><![CDATA[2.3]]></category>
                <category><![CDATA[News]]></category>
                <category><![CDATA[oik]]></category>

                <guid isPermaLink="false">http://www.oik-plugins.com/?p=11412</guid>
                <description><![CDATA[Version 2.3-alpha.0509 of the oik base plugin is now available from oik-plugins.com and oik-plu
gins.co.uk]]></description>
                <wfw:commentRss>http://www.oik-plugins.com/2014/05/oik-v2-3-alpha-0509-now-available/feed/</wfw:commentRss>
                <slash:comments>0</slash:comments>
                </item>
                <item>
                <title>oik-plugins supports WordPress 3.9 with oik version 2.2</title>
                <link>http://www.oik-plugins.com/2014/04/oik-plugins-supports-wordpress-3-9-oik-version-2-2/?utm_source=rss&#038;utm_
medium=rss&#038;utm_campaign=oik-plugins-supports-wordpress-3-9-oik-version-2-2</link>
                <comments>http://www.oik-plugins.com/2014/04/oik-plugins-supports-wordpress-3-9-oik-version-2-2/#comments</comments>
                <pubDate>Sun, 20 Apr 2014 09:52:46 +0000</pubDate>
                <dc:creator><![CDATA[Herb Miller]]></dc:creator>
                                <category><![CDATA[News]]></category>
                <category><![CDATA[oik]]></category>
                <category><![CDATA[WordPress 3.9]]></category>

                <guid isPermaLink="false">http://www.oik-plugins.com/?p=11270</guid>
                <description><![CDATA[3.9 was released on 16th April 2014. We spent most of the 17th April re-testing the compatibili
ty of our plugins with the final version. The readme.txt files in the plugin repository on have been updated to reflect this. The bas
e plugin was the only plugin that needed to be updated. oik version 2.2 now supports [&#8230;]]]></description>
                <wfw:commentRss>http://www.oik-plugins.com/2014/04/oik-plugins-supports-wordpress-3-9-oik-version-2-2/feed/</wfw:comm
entRss>
                <slash:comments>0</slash:comments>
                </item>
                <item>
                <title>Sponsoring WordCamp Sheffield &#8211; 26th April 2014</title>
                <link>http://www.oik-plugins.com/2014/04/sponsoring-wordcamp-sheffield-26th-april-2014/?utm_source=rss&#038;utm_mediu
m=rss&#038;utm_campaign=sponsoring-wordcamp-sheffield-26th-april-2014</link>
                <comments>http://www.oik-plugins.com/2014/04/sponsoring-wordcamp-sheffield-26th-april-2014/#comments</comments>
                <pubDate>Mon, 07 Apr 2014 09:50:31 +0000</pubDate>
                <dc:creator><![CDATA[Herb Miller]]></dc:creator>
                                <category><![CDATA[Events]]></category>
                <category><![CDATA[News]]></category>
                <category><![CDATA[2014]]></category>
                <category><![CDATA[Sheffield]]></category>
                <category><![CDATA[sponsor]]></category>
                <category><![CDATA[WordCamp]]></category>

                <guid isPermaLink="false">http://www.oik-plugins.com/?p=11165</guid>
                <description><![CDATA[oik-plugins are happy to be sponsoring WordCamp Sheffield, to be held at Mappin Hall, Sheffield
 on 26th April 2014. If you&#8217;re going, come and say hello!]]></description>
                <wfw:commentRss>http://www.oik-plugins.com/2014/04/sponsoring-wordcamp-sheffield-26th-april-2014/feed/</wfw:commentRs
s>
                <slash:comments>0</slash:comments>
                </item>
                <item>
                <title>Implement the missing &#091;sociable&#093; shortcode in functions.php</title>
                <link>http://www.oik-plugins.com/2014/04/implement-missing-sociable-shortcode-functions-php/?utm_source=rss&#038;utm_
medium=rss&#038;utm_campaign=implement-missing-sociable-shortcode-functions-php</link>
                <comments>http://www.oik-plugins.com/2014/04/implement-missing-sociable-shortcode-functions-php/#comments</comments>
                <pubDate>Tue, 01 Apr 2014 19:11:43 +0000</pubDate>
                <dc:creator><![CDATA[Herb Miller]]></dc:creator>
                                <category><![CDATA[Example]]></category>
                <category><![CDATA[shortcode]]></category>
                <category><![CDATA[power user]]></category>
                <category><![CDATA[sociable]]></category>

                <guid isPermaLink="false">http://www.oik-plugins.com/?p=11135</guid>
                <description><![CDATA[The WordPress plugin indicates that you can use the shortcode to insert the sociable links manu
ally. Unfortunately, it doesn&#8217;t actually register the shortcode. Here are a few lines of code that you can put into your theme&
#8217;s functions.php file to resolve this issue. Notes This code uses oik APIs. If sociable is not installed or [&#8230;]]]></descri
ption>
                <wfw:commentRss>http://www.oik-plugins.com/2014/04/implement-missing-sociable-shortcode-functions-php/feed/</wfw:comm
entRss>
                <slash:comments>0</slash:comments>
                </item>
                <item>
                <title>Weight / Country shipping &#8211; WooCommerce extension</title>
                <link>http://www.oik-plugins.com/2014/03/weight-country-shipping-woocommerce-extension-plugin/?utm_source=rss&#038;ut
m_medium=rss&#038;utm_campaign=weight-country-shipping-woocommerce-extension-plugin</link>
                <comments>http://www.oik-plugins.com/2014/03/weight-country-shipping-woocommerce-extension-plugin/#comments</comments
>
                <pubDate>Sun, 30 Mar 2014 12:35:21 +0000</pubDate>
                <dc:creator><![CDATA[Herb Miller]]></dc:creator>
                                <category><![CDATA[News]]></category>
                <category><![CDATA[plugins]]></category>
                <category><![CDATA[extension]]></category>
                <category><![CDATA[shipping]]></category>
                <category><![CDATA[WooCommerce]]></category>

                <guid isPermaLink="false">http://www.oik-plugins.com/?p=11107</guid>
                <description><![CDATA[If you&#8217;ve been using AWD-weightcountry-shipping for WooCommerce and want to upgrade your
installation to WooCommerce 2.1.x then the easiest way is to switch to the oik-weightcountry-shipping plugin.]]></description>
                <wfw:commentRss>http://www.oik-plugins.com/2014/03/weight-country-shipping-woocommerce-extension-plugin/feed/</wfw:co
mmentRss>
                <slash:comments>0</slash:comments>
                </item>
                <item>
                <title>oik version 2.2-alpha-0326 now available</title>
                <link>http://www.oik-plugins.com/2014/03/oik-version-2-2-alpha-0326-now-available/?utm_source=rss&#038;utm_medium=rss
&#038;utm_campaign=oik-version-2-2-alpha-0326-now-available</link>
                <comments>http://www.oik-plugins.com/2014/03/oik-version-2-2-alpha-0326-now-available/#comments</comments>
                <pubDate>Wed, 26 Mar 2014 22:27:26 +0000</pubDate>
                <dc:creator><![CDATA[Herb Miller]]></dc:creator>
                                <category><![CDATA[2.2]]></category>
                <category><![CDATA[News]]></category>
                <category><![CDATA[oik]]></category>
                <category><![CDATA[bw_emergency]]></category>
                <category><![CDATA[bw_link]]></category>
                <category><![CDATA[bw_mob]]></category>
                <category><![CDATA[bw_mobile]]></category>
                <category><![CDATA[bw_skype]]></category>
                <category><![CDATA[bw_tel]]></category>
                <category><![CDATA[bw_telephone]]></category>
                <category><![CDATA[TinyMCE 4]]></category>
                <category><![CDATA[WordPress 3.9]]></category>

                <guid isPermaLink="false">http://www.oik-plugins.com/?p=11004</guid>
                <description><![CDATA[Version 2.2-alpha.0326 of the oik base plugin is now available on oik-plugins.com. This version
 has been tested with WordPress 3.9-beta2 and TinyMCE version 4. Changes include: Support for TinyMCE version 4. Backwards compatibil
ity with TinyMCE in earlier versions of WordPress ( up to 3.8.1 ) Improvements to shortcodes: and and See also &#160;]]></description
>
                <wfw:commentRss>http://www.oik-plugins.com/2014/03/oik-version-2-2-alpha-0326-now-available/feed/</wfw:commentRss>
                <slash:comments>0</slash:comments>
                </item>
                <item>
                <title>Using bw_related to find posts in a category related to a CPT fieldref value</title>
                <link>http://www.oik-plugins.com/2014/03/using-bw_related-find-posts-category-related-cpt-fieldref-value/?utm_source=
rss&#038;utm_medium=rss&#038;utm_campaign=using-bw_related-find-posts-category-related-cpt-fieldref-value</link>
                <comments>http://www.oik-plugins.com/2014/03/using-bw_related-find-posts-category-related-cpt-fieldref-value/#comment
s</comments>
                <pubDate>Sat, 01 Mar 2014 14:27:19 +0000</pubDate>
                <dc:creator><![CDATA[Herb Miller]]></dc:creator>
                                <category><![CDATA[oik-fields]]></category>
                <category><![CDATA[bw_related]]></category>

                <guid isPermaLink="false">http://www.oik-plugins.com/?p=10932</guid>
                <description><![CDATA[In this post we demonstrate an example of the bw_related shortcode to find posts related to a p
articular field value through a matching category value. List posts which are in the category determined by the value of the field _o
ikp_slug associated to the post referenced by the current value of the _plugin_ref field for this [&#8230;]]]></description>
                <wfw:commentRss>http://www.oik-plugins.com/2014/03/using-bw_related-find-posts-category-related-cpt-fieldref-value/fe
ed/</wfw:commentRss>
                <slash:comments>0</slash:comments>
                </item>
                <item>
                <title>oik-nivo-slider v1.13 available</title>
                <link>http://www.oik-plugins.com/2014/02/oik-nivo-slider-v1-13-available/?utm_source=rss&#038;utm_medium=rss&#038;utm
_campaign=oik-nivo-slider-v1-13-available</link>
                <comments>http://www.oik-plugins.com/2014/02/oik-nivo-slider-v1-13-available/#comments</comments>
                <pubDate>Sat, 22 Feb 2014 08:10:31 +0000</pubDate>
                <dc:creator><![CDATA[Herb Miller]]></dc:creator>
                                <category><![CDATA[1.13]]></category>
                <category><![CDATA[News]]></category>
                <category><![CDATA[oik-nivo-slider]]></category>
                <category><![CDATA[nivo]]></category>

                <guid isPermaLink="false">http://www.oik-plugins.com/?p=10825</guid>
                <description><![CDATA[Version 1.13 contains a fix for when using the format= parameter and when multiple sliders are
being used to display the same content.]]></description>
                <wfw:commentRss>http://www.oik-plugins.com/2014/02/oik-nivo-slider-v1-13-available/feed/</wfw:commentRss>
                <slash:comments>0</slash:comments>
                </item>
                <item>
                <title>oik-nivo-slider v1.12 now with full HTML captions</title>
                <link>http://www.oik-plugins.com/2014/02/oik-nivo-slider-v1-12-now-full-html-captions/?utm_source=rss&#038;utm_medium
=rss&#038;utm_campaign=oik-nivo-slider-v1-12-now-full-html-captions</link>
                <comments>http://www.oik-plugins.com/2014/02/oik-nivo-slider-v1-12-now-full-html-captions/#comments</comments>
                <pubDate>Tue, 18 Feb 2014 21:47:14 +0000</pubDate>
                <dc:creator><![CDATA[Herb Miller]]></dc:creator>
                                <category><![CDATA[1.12]]></category>
                <category><![CDATA[News]]></category>
                <category><![CDATA[oik-nivo-slider]]></category>
                <category><![CDATA[HTML captions]]></category>
                <category><![CDATA[nivo]]></category>
                <category><![CDATA[[bw_css]]]></category>

                <guid isPermaLink="false">http://www.oik-plugins.com/?p=10724</guid>
                <description><![CDATA[Version 1.12 of the oik-nivo-slider WordPress plugin now allows you to define the content of th
e caption area. Here we display a slider of 5 plugins, showing the Description field ( _oikp_desc ) and the link to the plugin.]]></d
escription>
                <wfw:commentRss>http://www.oik-plugins.com/2014/02/oik-nivo-slider-v1-12-now-full-html-captions/feed/</wfw:commentRss
>
                <slash:comments>0</slash:comments>
                </item>
                <item>
                <title>oik-nivo-slider v1.11 now available</title>
                <link>http://www.oik-plugins.com/2014/02/oik-nivo-slider-v1-11-now-available/?utm_source=rss&#038;utm_medium=rss&#038
;utm_campaign=oik-nivo-slider-v1-11-now-available</link>
                <comments>http://www.oik-plugins.com/2014/02/oik-nivo-slider-v1-11-now-available/#comments</comments>
                <pubDate>Wed, 05 Feb 2014 21:52:34 +0000</pubDate>
                <dc:creator><![CDATA[Herb Miller]]></dc:creator>
                                <category><![CDATA[1.11]]></category>
                <category><![CDATA[News]]></category>
                <category><![CDATA[oik-nivo-slider]]></category>
                <category><![CDATA[CSS]]></category>
                <category><![CDATA[Fix]]></category>
                <category><![CDATA[nivo]]></category>
                <category><![CDATA[textwidget]]></category>

                <guid isPermaLink="false">http://www.oik-plugins.com/?p=10662</guid>
                <description><![CDATA[Version 1.11 of the oik-nivo-slider plugin for WordPress is now available from oik-plugins and
WordPress.org. This is a maintenance release including one fix. Version 1.11 change Adds some CSS styling to nivo-slider-32.css to co
rrectly display transitions in text widgets This overrides some styling in oik.css which was setting max-width to 100%, but nivo need
s max-width: [&#8230;]]]></description>
                <wfw:commentRss>http://www.oik-plugins.com/2014/02/oik-nivo-slider-v1-11-now-available/feed/</wfw:commentRss>
                <slash:comments>0</slash:comments>
                </item>
        </channel>
</rss>

      
*/

function parse_feed( $feed ) {


  $title = $feed->get_title();
  echo $title;
  
  /**

if ( ! is_wp_error( $rss ) ) : // Checks that the object is created correctly

    // Figure out how many total items there are, but limit it to 5. 
    $maxitems = $rss->get_item_quantity( 5 ); 

    // Build an array of all the items, starting with element 0 (first element).
    $rss_items = $rss->get_items( 0, $maxitems );

endif;
  */  



}      


