<?php

namespace Tests\Mocks;

/**
 * Create yaml-formatted strings containing episode information.
 *
 * @author Vincent Neubauer <v.neubauer@vonmaehlen.com>
 */
trait CreatesEpisodeYamlMocks
{
    public function createYamlMock(string $guid, string $username)
    {
        $yaml = "---
username: USERNAME_HERE
guid: GUID_HERE
title: 'Lorem Ipsum'
hosts:
  - Host01
  - Host02
topics:
  - name: 'Lorem ipsum dolor sit amet'
    community: true
    ad: false
    start: 00:07:10
    end: 00:22:52
    subtopics:
      - 'Lorem ipsum dolor sit amet, consectetur adipiscing elit'
      - 'Aliquam sollicitudin nisl nec sem fringilla placerat'
      - 'Vivamus dolor nisl, pharetra et risus sed, dapibus pulvinar metus'
        ";

        $yaml = str_replace('GUID_HERE', $guid ?: '7e7577b7-2844-4f84-8e57-a677ca67d1d3', $yaml);
        $yaml = str_replace('USERNAME_HERE', $username ?: 'johndoe', $yaml);

        return $yaml;
    }
}
