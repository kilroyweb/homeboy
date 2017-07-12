<?php

namespace App\Support\Git;

class ApplicationVersion
{

    /*
    https://stackoverflow.com/questions/16334310/i-want-to-display-the-git-version-on-my-site
     */
    public static function get()
    {
        $tag = trim(exec('git describe --tags --abbrev=0'));

        $commitHash = trim(exec('git log --pretty="%h" -n1 HEAD'));

        $commitDate = new \DateTime(trim(exec('git log -n1 --pretty=%ci HEAD')));
        $commitDate->setTimezone(new \DateTimeZone('UTC'));

        $output = $tag.' ['.$commitHash.'] ('.$commitDate->format('Y-m-d H:m:s').')';
        return $output;
    }
}