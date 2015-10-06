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
 * A renderable representation of an RSS Item
 *
 * @package    rss_plus
 * @copyright  2012 Shaun Daubney 
 * @sourcecode Daryl Hawes
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

namespace block_rss_plus;

class rss_item implements \renderable, \templatable {
    
    /**
     * @var stdClass
     */
    private $context;

    /**
     * @var \SimplePie_Item
     */
    private $item;
    
    /**
     * @global \moodle_page $PAGE
     * @param \SimplePie_Item $item
     */
    public function __construct(\SimplePie_Item $item) {
        global $PAGE;
        
        $this->item     = $item;
        $this->context  = $PAGE->context;
    }
    
    /**
     * Get the item title
     * 
     * @return string
     */
    private function get_title() {
        $title = $this->item->get_title();
        
        if (empty($title)) {
            $title = strip_tags($item->get_description());
            $title = core_text::substr($title, 0, 20) . '...';
        } else {
            $title = break_up_long_words($title, 30);
        }
       
       return $title;
    }
    
    /**
     * Get the item description
     * 
     * @return string
     */
    private function get_description() {
        $description = $this->item->get_description();
        $description = break_up_long_words($description, 30);
        $description = \core_text::substr(strip_tags($description), 0, 255) . '';
        
        $description = format_text(
            $description,
            FORMAT_HTML,
            array(
                'para' => false, 
                'context' => $this->context,
            )
        );
        
        return $description;
    }
    
    /**
     * Get the item link
     * 
     * @return string
     */
    private function get_link() {
        $link = $this->item->get_link();
        
        if (empty($link)) {
            $link = $this->item->get_id();
        } else {
            // URLs in our RSS cache will be escaped (correctly as theyre store 
            // in XML) \html_writer::link() will re-escape them. To prevent 
            // double escaping unescape here. This can by done using 
            // htmlspecialchars_decode() but moodle_url also has that effect
            $link = new \moodle_url($link);
        }
        
        $link = clean_param($link, PARAM_URL);
        
        return $link;
    }
    
    /**
     * Get the item thumbnails
     * 
     * @return array|null
     */
    private function get_thumbnails() {
        $thumbnails = array();
        
        if ($enclosure = $this->item->get_enclosure()) {
            foreach ((array) $enclosure->get_thumbnail(1) as $thumbnail) {
                $thumbnails[] = $thumbnail;
            }
        }
        
        return $thumbnails;
    }
    
    /**
     * Export context for use in templates
     * 
     * @param \renderer_base $output
     * @return \stdClass
     */
    public function export_for_template(\renderer_base $output) {
        $item = new \stdClass();
        $item->title        = $this->get_title();
        $item->description  = $this->get_description();
        $item->link         = $this->get_link();
        $item->thumbnails   = $this->get_thumbnails();
        
        return $item;
    }
}
