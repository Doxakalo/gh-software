<?php

namespace App\Service;

use App\Service\Config\ApplicationConfig;

class Application
{
    public $applicationConfig;

    public function __construct(ApplicationConfig $applicationConfig)
    {
        $this->applicationConfig = $applicationConfig;
    }

    public function getAllWebsiteLink($domain, array $ignoreExtensions = [], array $ignoreURlContains = [], $external = false)
    {
        $host = htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'localhost', ENT_QUOTES, 'UTF-8');
        $protocol = htmlspecialchars($_SERVER['REQUEST_SCHEME'] ?? 'localhost', ENT_QUOTES, 'UTF-8');

        $cmd = "wget --spider -r -nd --reject=".escapeshellarg(implode(',', $ignoreExtensions))." --max-redirect=30 ".escapeshellarg($protocol."://".$host)." 2>&1 | grep '^--' | awk '{ print $3 }' | sort -u";

        $output = shell_exec($cmd);

        $links = [];
        $allLinks = explode("\n", $output);

        $linksFinal = [];
        // REMOVED IGNORED LINKS
        foreach ($allLinks as $linkAbsolute) {
            $linkAbsolute = htmlspecialchars($linkAbsolute);
            if (!empty($linkAbsolute)) {
                $linkError = 0;

                if (parse_url($linkAbsolute, PHP_URL_HOST) === $host) {
                    $link = parse_url($linkAbsolute, PHP_URL_SCHEME) . "://" . parse_url($linkAbsolute, PHP_URL_HOST) . strtolower(parse_url($linkAbsolute, PHP_URL_PATH));


                    if (substr($link, -1) === "/") {
                        $link = substr($link, 0, -1);
                    }

                    foreach ($ignoreURlContains as $ignoreString) {
                        if (stripos($link, $ignoreString) !== false) {
                            $linkError = 1;
                        }
                    }

                    if ($linkError === 0) {
                        $links[] = $link;
                    }
                }
            }
        }

        foreach ($links as $l) {
            if (!in_array($l, $linksFinal)) {
                $linksFinal[] = $l;
            }
        }

        return $linksFinal;
    }
}
