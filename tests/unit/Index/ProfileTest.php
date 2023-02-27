<?php

use PHPUnit\Framework\TestCase;

use KSamuel\FacetedSearch\Index\Factory;
use KSamuel\FacetedSearch\Index\Profile;

class ProfileTest extends TestCase
{
    public function testSetSortingTime(): void
    {
        $profile = new Profile();
        $profile->setSortingTime(12.8);
        $this->assertEquals(12.8, $profile->getSortingTime());
    }
}
