<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Purify;
use Tests\TestCase;

class PurifierTest extends TestCase
{
    #[Test]
    public function puckTest()
    {
        $actual = Purify::clean("<span class=\"fa-spin fa\">catgirl spinning around in the interblag</span>");
        $expected = '<span>catgirl spinning around in the interblag</span>';
        $this->assertEquals($expected, $actual);

        $actual = Purify::clean("<p class=\"fa-spin fa\">catgirl spinning around in the interblag</p>");
        $expected = '<p>catgirl spinning around in the interblag</p>';
        $this->assertEquals($expected, $actual);

        $actual = Purify::clean('<a class="navbar-brand d-flex align-items-center" href="https://pix.social" title="Logo"><img src="/img/pix-icon-color.svg" height="30px" class="px-2"><span class="font-weight-bold mb-0 d-none d-sm-block" style="font-size:20px;">pix</span></a>');
        $expected = '<a href="https://pix.social" title="Logo" rel="nofollow noreferrer noopener" target="_blank"><span>pix</span></a>';
        $this->assertEquals($expected, $actual);
    }
}
