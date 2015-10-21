<?php // (C) Copyright Bobbing Wide 2013, 2014


/**
 * Return a list of oik plugins
 * 
 * This is the currently supported list of oik-plugins
 * Any commented out lines are just to speed up processing 
 * 
 * Excluded from this list are bespoke plugins such as:
 * oik-adr, eedge, kate, olc, cookie-category, dtib-review, effort, get-ctrl-importer, calyx 
 */
function list_oik_plugins() {
  $plugins = "";
  //$plugins .= "bobbing ";  
  $plugins .= "oik ";    
  $plugins .= "oik-bwtrace ";
  //$plugins .= "smart-bbboing ";
  //$plugins .= "bbboing ";
  $plugins .= "oik-fields ";   
  $plugins .= "oik-plugins "; 
  $plugins .= "oik-shortcodes ";
  //$plugins .= "oik-presentation ";  
  $plugins .= "oik-types ";
  $plugins .= "oik-batch ";
  //$plugins .= "cookie-cat diy-oik ";
  //$plugins .= "oik-batchmove oik-bbpress ";
  //$plugins .= "oik-blogger-redirect oik-bob-bing-wide oik-bp-signup-email oik-business  ";
  //$plugins .= "oik-css oik-debug-filters oik-edd oik-email-signature oik-external-link-warning ";
  //$plugins .= "oik-fum oik-getimage oik-header  ";
  $plugins .= "oik-infusionsoft oik-moreoptions oik-ms oik-mshot oik-nivo-slider ";
  $plugins .= "oik-post-type-support oik-privacy-policy oik-rating ";
  $plugins .= "oik-responsive-menu oik-rwd oik-sc-help oik-sidebar ";
  // $plugins .= "oik-signup-user-notification
  $plugins .= "oik-squeeze oik-testimonials oik-themes oik-todo ";
  $plugins .= "oik-tos oik-tunes oik-user oik-video ";
  $plugins .= "oik-window-width oik-woo oik-working-feedback premium-plugins setup ";
  $plugins .= "uk-tides us-tides oik-i18n";
  return( $plugins );
}  
