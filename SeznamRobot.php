<?php


class SeznamRobot
{
    public $url;
    
    public $input;
    
    public $userUrl;
    
    protected $html;
    
    protected $page = 0;
    
    protected $num = 0;
    
    public $pageLimit;
    
    protected $token;
    
    public $urlPosition;
    
    public $urlOutput;
    
    protected $pages = array();
    
    protected $urlChars = array(
        //','     =>  '%2C'
    );
    

    protected $blockedUrl = array(
        's.imedia.cz',
        'www.zbozi.cz',
        'www.obrazky.cz'
    );


    public function __construct($userUrl, $input)
    {
        $this->userUrl = $userUrl;
        $this->input = $input;
    }
    

    public function loader()
    {
        while ($this->page < $this->pageLimit) {
            $this->page += 1;

            $this->getPageUrl();
            
            if ($this->urlPosition == NULL) {
                $this->html = file_get_contents($this->url);
                
                // nejprve prohledam obsah stranky, zda tam vubec je dana url
                $position = strpos($this->html, $this->userUrl);
                if ($position === FALSE) { continue; }
                
                $this->loadPage();
                $this->findUserUrl();
            }
        }
    }

    
    /**
     * get form token
     * @param string $htmlFromResults
     */
    protected function getPaginationList($htmlFromResults)
    {
        $startTag = '<div id="paging"';
        $textPosition = strpos($htmlFromResults, $startTag);
        $textFromPagination = substr($htmlFromResults, $textPosition, strlen($htmlFromResults) - $textPosition);
        
        $hrefTags = '';
        preg_match_all('/<a href="\/\?[^>]+>/i', $textFromPagination, $hrefTags);
        array_pop($hrefTags[0]);
        
        $hrefTag = $hrefTags[0][0];
        $hrefData = explode(';', $hrefTag);
        $this->token = $hrefData[2];
    }

    
    /**
     * get url for pages
     */
    protected function getPageUrl()
    {
        $step = 10;
        $keywords = str_replace(' ', '+', $this->input);
        
        if ($this->page == 1) {
            $this->url = 'http://search.seznam.cz/?q=' . $keywords . '&sourceid=szn-HP';
        } else {
            $this->num += $step; 
            $this->url = 'http://search.seznam.cz/?q=' . $keywords . '&count=10&' . $this->token . '&from=' . $this->num;
        }
    }

    /**
     * find url by user
     */
    protected function findUserUrl()
    {
        $step = 10;
        $hrefData = $this->pages[$this->page]['href'];
        
        foreach ($hrefData as $id => $href) {
            $position = strpos($href, $this->userUrl);
            if ($position !== FALSE) {
                $this->urlPosition = (($this->page == 1) ? $id + 1 : (($this->page - 1) * $step) + $id + 1);
                $this->urlOutput = $href;
                break;
            }
        }
    }


    /**
     * save page content
     */
    protected function loadPage()
    {
        $startTag = '<div data-dot="results"';
        $startBody = strpos($this->html, $startTag);
        $htmlFromResults = substr($this->html, $startBody, strlen($this->html) - $startBody);
        
        $this->getDomains($htmlFromResults);
        $this->getPaginationList($htmlFromResults);
    }
    
    /**
     * save domains
     * @param string $htmlFromResults
     */
    protected function getDomains($htmlFromResults)
    {
        $hrefTags = '';
        preg_match_all('/<a href="http[^>]+>/i', $htmlFromResults, $hrefTags);
        
        // sanitize href tags by title
        foreach ($hrefTags[0] as $id => $hrefTag)
        {
            $a = new \SimpleXMLElement($hrefTag . '</a>');
            if (strpos($hrefTag, 'title=') !== FALSE) {
                $title = (array) $a['title'];
                $title = $title[0];
                if (strpos($title, 'http') === FALSE) {
                    unset($hrefTags[0][$id]);
                }
            } else {
                unset($hrefTags[0][$id]);
            }
        }
        
        $result = array();
        foreach ($hrefTags[0] as $id => $hrefTag)
        {
            // get href attribute
            $a = new \SimpleXMLElement($hrefTag . '</a>');
            $href = (array) $a['href'];
            $href = $href[0];

            // get domain name
            $hrefData = explode('/', $href);
            $domain = $hrefData[2];
            
            if (!in_array($domain, $this->blockedUrl)) {
                if (!in_array($href, $result)) {
                    $result[] = $href;
                }
            }
        }

        $this->pages[$this->page]['href'] = $result;
    }
    
    
    
    public function getUrl()
    {
        return $this->url;
    }
    
    public function getInput()
    {
        return $this->input;
    }

    public function getUrlPosition()
    {
        return $this->urlPosition;
    }
    
    public function getUrlOutput()
    {
        return $this->urlOutput;
    }
    
    public function getPageLimit()
    {
        return $this->pageLimit;
    }
    
    public function setPageLimit($count)
    {
        $this->pageLimit = $count;
    }
    
}
