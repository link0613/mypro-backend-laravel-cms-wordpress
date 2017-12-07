<?php

namespace RestBundle\Services;

use Doctrine\Common\Persistence\ObjectManager;
use GuzzleHttp\Client;
use SitemapPHP\Sitemap;

/**
 * Class SitemapGenerator
 * @package RestBundle\Services
 */
class SitemapGenerator
{
    /**
     * @var string $path
     */
    protected $path;

    /**
     * @var ObjectManager $em
     */
    protected $em;

    /**
     * SitemapGenerator constructor.
     * @param ObjectManager $em
     * @param $path
     */
    public function __construct(ObjectManager $em, $path)
    {
        $this->em = $em;
        $this->path = $path;
    }

    /**
     * @return bool
     */
    public function generate()
    {
        $domain = 'https://www.findmyprofession.com/generate-sitemap';
        $client = new Client();
        $res = $client->request('POST', $domain);

        if ($res->getStatusCode() === 200) {
            return true;
        }

        return false;
    }
}
