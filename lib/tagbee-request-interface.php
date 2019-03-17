<?php

interface Tagbee_Request_Interface
{
    const TAGBEE_API_VERSION = 1;

    public function buildBody();
}

trait Tagbee_Trait
{
    protected function buildRequestContent()
    {
        return [
            'third_party_id' => $this->contentId,
            'title' => $this->contentTitle,
            'body' => $this->contentBody,
            'category' => $this->contentCategory,
            'meta_description' => $this->contentMetaDescription,
            'meta_keywords' => $this->contentMetaKeywords
        ];
    }

    /**
     * Returns a string with delimiter separated Post's Categories.
     *
     * @param $post
     * @param $delimiter
     *
     * @return string
     */
    protected function createCategoriesString($post, $delimiter = ',')
    {
        $categories = array_map(function($c) {
            return $c->name;
        }, get_the_category($post));

        return implode($delimiter, $categories);
    }

    protected function buildRequestTags()
    {
        return array_map(function($tag) {
            return ['third_party_id' => $tag->term_id, 'tag' => $tag->name];
        }, $this->tags);
    }
}