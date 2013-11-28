<?php

class Parser extends CI_Controller {

    public function index() {
        $this->load->database();
        $this->load->helper('form');
        $this->load->helper('url');
        $this->load->library('form_validation');

        $data['title'] = 'Awesome google parser';

        $this->form_validation->set_rules('url', 'url', 'required');
        $this->form_validation->set_rules('word', 'word', 'required');

        if($this->form_validation->run() === FALSE) {
            $this->load->view('templates/header', $data);
            $this->load->view('parser', $data);
            $this->load->view('templates/footer', $data);
        }
        else {
            require_once(APPPATH . 'libraries/simplephpdom/simple_html_dom.php');
            
            // parse google
            $str = $this->_parsePages($this->input->post('word'));

            $html = str_get_html($str);
            $urls = array();

            foreach ($html->find('div.s') as $element) {
                array_push($urls, urlencode(strip_tags((string)$element->children(0))));
            }
            foreach ($urls as $i => $url) {
                if(strpos($url, $this->input->post('url'))) {
                    echo 'Позиция в поиске: ' . ($i + 1);
                    $pos = $i + 1;
                    break;
                }
            }
            // write to db
            if(isset($pos)) {
                $this->load->model('parser_model');
                $this->parser_model->add_result($this->input->post('word'), $this->input->post('url'), $pos);
            }
            else {
                echo 'No result';
            }
        }
    }

    // google and xproxy parser
    // parse ALL xproxy rss
    public function _parsePages($query) {
        // Xproxy.com rss array
        $proxyRSS = array();
        $proxyXML = new SimpleXMLElement(file_get_contents('http://www.xroxy.com/proxyrss.xml'));
            
        foreach($proxyXML->channel->item as $item) {
            array_push($proxyRSS, $item->link);
        }
            
        // Random proxy url from rss
        $proxyURL = ($proxyRSS[array_rand($proxyRSS)]);
            
        $proxyHtml = file_get_html($proxyURL);
        $psoxyList = array();
        $p = array();
        foreach ($proxyHtml->find('div#content table td a') as $a) {
            $o = '';
            if($a->title == 'View this Proxy details') {
            $o .= substr($a->innertext,0,strpos($a->innertext,' <!--'));
            array_push($p, $o);
            }
            if(strpos($a->title, 'Select proxies with port number') !== false) {
                $o .= ':' . $a->innertext;
                $p[count($p) - 1] .= $o;
            }
        }
        //var_dump($p);
        // take random proxy
        //var_dump($psoxyList);
        $curlProxy = $p[array_rand($p)];
        //var_dump($curlProxy);
        
        // parse google
        $url='http://www.google.com/search?q=' . $query .
            '&num=100';
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        // ...
        //curl_setopt($curl, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
        //curl_setopt($curl, CURLOPT_PROXY, $curlProxy);
        curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        $html = curl_exec($curl);
        curl_close ($curl);
        return $html;
    }
}