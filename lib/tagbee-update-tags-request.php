<?php

require_once("tagbee-request-interface.php");

class Tagbee_Update_Tags_Request implements Tagbee_Request_Interface
{
    use Tagbee_Trait;

    /**
     * @var array
     */
    protected $tags;

    public function __construct($tags)
    {
        $this->tags = $tags;
    }

    public function buildBody()
    {
        return [
            'version' => self::TAGBEE_API_VERSION,
            'tags' => $this->buildRequestTags()
        ];
    }
}