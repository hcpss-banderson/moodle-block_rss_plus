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
 * A renderable RSS Feed
 *
 * @package    rss_plus
 * @copyright  2012 Shaun Daubney 
 * @sourcecode Daryl Hawes
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

namespace block_rss_plus;

class rss_feed implements \renderable, \templatable {
    
    /**
     * @var \moodle_simplepie
     */
    private $feed;
    
    /**
     * @var string
     */
    private $title;
    
    /**
     * @var array
     * @see \block_rss_plus\rss_item
     */
    private $items;
    
    /**
     * @param \moodle_simplepie $feed
     * @param string $title
     * @param int $maxentries
     */
    public function __construct(\moodle_simplepie $feed, $title, $maxentries = 5) {
        $this->feed         = $feed;
        $this->title        = $title;
        $this->maxentries   = $maxentries;
        
        foreach ($this->feed->get_items(0, $this->maxentries) as $item) {
            $this->items[] = new \block_rss_plus\rss_item($item);
        }        
    }
    
    /**
     * Export context for use in templates
     * 
     * @param \renderer_base $output
     * @return \stdClass
     */
    public function export_for_template(\renderer_base $output) {
        $data = new \stdClass();
        
        $data->items = array();
        foreach ($this->items as $item) {
            $data->items[] = $item->export_for_template($output);
        }
        
        return $data;
    }
}
