<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * A block which displays Remote feeds in a more visual way
 * but based on the core RSS block. 
 *
 * @package    rss_plus
 * @copyright  2012 Shaun Daubney 
 * @sourcecode Daryl Hawes
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

 class block_rss_plus extends block_base {

    function init() {
        $this->title = get_string('feedstitle', 'block_rss_plus');
    }

    function preferred_width() {
        return 140;
    }

    function applicable_formats() {
        return array('all' => true, 'tag' => false);   // Needs work to make it work on tags MDL-11960
    }

	
	function specialization() {
        // After the block has been loaded we customize the block's title display
        if (!empty($this->config) && !empty($this->config->title)) {
            // There is a customized block title, display it
            $this->title = $this->config->title;
        } else {
            // No customized block title, use localized remote news feed string
            $this->title = get_string('remotenewsfeed', 'block_rss_plus');
        }
    }

    function get_content() {
        global $CFG, $DB;

        if ($this->content !== NULL) {
            return $this->content;
        }

        // initalise block content object
        $this->content = new stdClass;
        $this->content->text   = '';
        $this->content->footer = '';

        if (empty($this->instance)) {
            return $this->content;
        }

        if (!isset($this->config)) {
            // The block has yet to be configured - just display configure message in
            // the block if user has permission to configure it

            if (has_capability('block/rss_plus:manageanyfeeds', $this->context)) {
                $this->content->text = get_string('feedsconfigurenewinstance2', 'block_rss_plus');
            }

            return $this->content;
        }

        // How many feed items should we display?
        $maxentries = 5;
        if ( !empty($this->config->shownumentries) ) {
            $maxentries = intval($this->config->shownumentries);
        }elseif( isset($CFG->block_rss_plus_num_entries) ) {
            $maxentries = intval($CFG->block_rss_plus_num_entries);
        }


        /* ---------------------------------
         * Begin Normal Display of Block Content
         * --------------------------------- */
        $renderer = $this->page->get_renderer('block_rss_plus');
        $output = '';

        if (!empty($this->config->rssid)) {
            list($rss_ids_sql, $params) = $DB->get_in_or_equal($this->config->rssid);

            $rss_feeds = $DB->get_records_select('block_rss_plus', "id $rss_ids_sql", $params);

            $showtitle = false;
            if (count($rss_feeds) > 1) {
                // when many feeds show the title for each feed
                $showtitle = true;
            }

            foreach($rss_feeds as $feedrecord){
                require_once($CFG->libdir.'/simplepie/moodle_simplepie.php');

                $feed = new \moodle_simplepie($feedrecord->url, 1000);

                if(isset($CFG->block_rss_plus_timeout)){
                    $feed->set_cache_duration($CFG->block_rss_plus_timeout*60);
                }

                if(debugging() && $feed->error()){
                    return '<p>'. $feedrecord->url .' Failed with code: '.$feed->error().'</p>';
                }

                if(empty($feedrecord->preferredtitle)){
                    $feedtitle = $this->format_title($feed->get_title());
                } else {
                    $feedtitle = $this->format_title($feedrecord->preferredtitle);
                }
                
                $output .= $renderer->rss_feed($feed, $feedtitle, $maxentries);
            }
        }

        $this->content->text = $output;

        return $this->content;
    }


    function instance_allow_multiple() {
        return true;
    }

    function has_config() {
        return true;
    }

    function instance_allow_config() {
        return true;
    }

    /**
     * Strips a large title to size and adds ... if title too long
     *
     * @param string title to shorten
     * @param int max character length of title
     * @return string title s() quoted and shortened if necessary
     */
    function format_title($title,$max=64) {

        if (core_text::strlen($title) <= $max) {
            return s($title);
        } else {
            return s(core_text::substr($title,0,$max-3).'...');
        }
    }

    /**
     * cron - goes through all feeds and retrieves them with the cache
     * duration set to 0 in order to force the retrieval of the item and
     * refresh the cache
     *
     * @return boolean true if all feeds were retrieved succesfully
     */
    function cron() {
        global $CFG, $DB;
        require_once($CFG->libdir.'/simplepie/moodle_simplepie.php');

        // We are going to measure execution times
        $starttime =  microtime();

        // And we have one initial $status
        $status = true;

        // Fetch all site feeds.
        $rs = $DB->get_recordset('block_rss_plus');
        $counter = 0;
        mtrace('');
        foreach ($rs as $rec) {
            mtrace('    ' . $rec->url . ' ', '');
            // Fetch the rss feed, using standard simplepie caching
            // so feeds will be renewed only if cache has expired
            //@set_time_limit(60);//
			core_php_time_limit::raise(60);

            $feed =  new moodle_simplepie();
            // set timeout for longer than normal to be agressive at
            // fetching feeds if possible..
            $feed->set_timeout(40);
            $feed->set_cache_duration(0);
            $feed->set_feed_url($rec->url);
            $feed->init();

            if ($feed->error()) {
                mtrace ('error');
                mtrace ('SimplePie failed with error:'.$feed->error());
                $status = false;
            } else {
                mtrace ('ok');
            }
            $counter ++;
        }
        $rs->close();

        // Show times
        mtrace($counter . ' feeds refreshed (took ' . microtime_diff($starttime, microtime()) . ' seconds)');

        // And return $status
        return $status;
    }
}


