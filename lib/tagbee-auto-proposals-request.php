<?php

require_once("tagbee-request-interface.php");

class Tagbee_Auto_Proposals_Request implements Tagbee_Request_Interface
{
    use Tagbee_Trait;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var int
     */
    protected $contentId;

    /**
     * @var string
     */
    protected $contentTitle;

    /**
     * @var string
     */
    protected $contentBody;

    /**
     * @var string
     */
    protected $contentCategory;

    /**
     * @var array
     */
    protected $tags;

    /**
     * @var string
     */
    protected $contentMetaDescription;

    /**
     * @var string
     */
    protected $contentMetaKeywords;

    public function __construct($data, $tags, $meta)
    {
        $this->id = !empty(trim($meta['tagbee_api_id'][0])) ? trim($meta['tagbee_api_id'][0]) : null;
        $this->contentId = $data->ID;
        $this->contentTitle = $data->post_title;
        $this->contentBody = $data->post_content;
        $this->contentCategory = $this->createCategoriesString($data);

        $this->tags = $tags;
        $this->contentMetaDescription = '';
        $this->contentMetaKeywords = '';
    }

    public function buildBody()
    {
        return [
            'id' => $this->id,
            'content' => $this->buildRequestContent(),
            'version' => self::TAGBEE_API_VERSION,
            'tags' => $this->buildRequestTags()
        ];
    }
}