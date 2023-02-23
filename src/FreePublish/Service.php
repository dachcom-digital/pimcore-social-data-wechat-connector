<?php

namespace SocialData\Connector\WeChat\FreePublish;

use Garbetjie\WeChatClient\Service as BaseService;
use GuzzleHttp\Psr7\Request;

class Service extends BaseService
{
    public function paginateFreePublish ($offset = 0, $count = 20)
    {
        return $this->paginate($offset, $count);
    }

    private function paginate ($offset, $limit)
    {
        // Ensure the limit is within range.
        if ($limit < 1) {
            $limit = 1;
        } elseif ($limit > 20) {
            $limit = 20;
        }

        // Send query.
        $json = json_decode(
            $this->client->send(
                new Request(
                    'POST',
                    'https://api.weixin.qq.com/cgi-bin/freepublish/batchget',
                    [],
                    json_encode([
                        'offset' => $offset,
                        'count'  => $limit,
                        'no_content' => 1
                    ])
                )
            )->getBody()
        );

        // Ensure response formatting.
        if (! isset($json->total_count, $json->item)) {
            throw new Exception("bad response: expecting properties `total_count`, `item_count`, `item`");
        }

        return $json;
    }

}
