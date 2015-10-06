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
 * Renderer
 *
 * @package    rss_plus
 * @copyright  2012 Shaun Daubney 
 * @sourcecode Daryl Hawes
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

namespace block_rss_plus\output;

class renderer extends \plugin_renderer_base {
    
    /**
     * Render an RSS item
     * 
     * @param \templatable $item
     * @return string
     */
    public function render_rss_item(\templatable $item) {
        $data = $item->export_for_template($this);
        
        return $this->render_from_template('block_rss_plus/item', $data);
    }
    
    /**
     * Instantiate and render an RSS item
     * 
     * @param \SimplePie_Item $item
     * @return string
     */
    public function rss_item(\SimplePie_Item $item) {
        $rss_item = new \block_rss_plus\rss_item($item);
        
        return $this->render($rss_item);
    }
    
    /**
     * Render an RSS feed
     * 
     * @param \templatable $feed
     * @return string
     */
    public function render_rss_feed(\templatable $feed) {
        $data = $feed->export_for_template($this);
        
        return $this->render_from_template('block_rss_plus/feed', $data);
    }
    
    /**
     * Instantiate and render an RSS feed
     * 
     * @param \moodle_simplepie $feed
     * @param string $title
     * @param int $maxentries
     * @return string
     */
    public function rss_feed(\moodle_simplepie $feed, $title, $maxentries = 5) {
        $rss_feed = new \block_rss_plus\rss_feed($feed, $title, $maxentries);
        
        return $this->render($rss_feed);
    }
}
