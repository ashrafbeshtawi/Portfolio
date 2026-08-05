<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Profile;
use PHPUnit\Framework\TestCase;

final class ProfileTest extends TestCase
{
    public function testAboutParagraphsSplitsOnBlankLines(): void
    {
        $profile = new Profile()->setAboutText("First paragraph.\n\nSecond paragraph.");

        self::assertSame(['First paragraph.', 'Second paragraph.'], $profile->getAboutParagraphs());
    }

    public function testAboutParagraphsHandlesWindowsLineEndingsAndExtraBlankLines(): void
    {
        $profile = new Profile()->setAboutText("One.\r\n\r\n\r\nTwo.\r\n\r\nThree.");

        self::assertSame(['One.', 'Two.', 'Three.'], $profile->getAboutParagraphs());
    }

    public function testAboutParagraphsIsEmptyForNullOrBlankText(): void
    {
        self::assertSame([], new Profile()->getAboutParagraphs());
        self::assertSame([], new Profile()->setAboutText("   \n  ")->getAboutParagraphs());
    }

    public function testHighlightsListSplitsPerLineAndSkipsEmptyLines(): void
    {
        $profile = new Profile()->setHighlights("Berlin, Germany\nBackend-focused\n\n  Always learning  \n");

        self::assertSame(
            ['Berlin, Germany', 'Backend-focused', 'Always learning'],
            $profile->getHighlightsList(),
        );
    }

    public function testHighlightsListIsEmptyForNullHighlights(): void
    {
        self::assertSame([], new Profile()->getHighlightsList());
    }
}
