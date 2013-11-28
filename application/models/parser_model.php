<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of parsermodel
 *
 * @author mo0
 */
class Parser_model extends CI_Model {
    
    function add_result($q, $l, $pos) {
        $this->load->helper('date');
        $data = array(
            'query' => $q ,
            'url' => $l ,
            'position' => $pos,
            'date' => date('Y-m-d')
        );
        $this->db->insert('parseresults', $data); 
    }
        
}
