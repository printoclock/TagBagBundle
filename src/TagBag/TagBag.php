<?php

declare(strict_types=1);

namespace Setono\TagBagBundle\TagBag;

use Setono\TagBagBundle\Renderer\RendererInterface;
use Setono\TagBagBundle\Tag\TagInterface;
use Webmozart\Assert\Assert;

final class TagBag implements TagBagInterface
{
    public const NAME = 'setono_tag_bag_tags';

    /**
     * @var RendererInterface
     */
    private $renderer;

    /**
     * @var string
     */
    private $storageKey;

    /**
     * @var array
     */
    private $tags = [];

    public function __construct(RendererInterface $renderer, string $storageKey = self::NAME)
    {
        $this->renderer = $renderer;
        $this->storageKey = $storageKey;
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function initialize(array &$tags): void
    {
        $this->tags = &$tags;
    }

    public function getStorageKey(): string
    {
        return $this->storageKey;
    }

    public function clear(): array
    {
        return $this->all();
    }

    public function add(TagInterface $tag, string $section = self::SECTION_BODY_END): void
    {
        Assert::true($this->renderer->supports($tag), sprintf('The tag %s is not supported by the given tag renderer', get_class($tag)));

        $renderedTag = $this->renderer->render($tag);

        $this->tags[$section][$tag->getKey()] = $renderedTag;
    }

    public function getSection(string $section, array $default = []): array
    {
        if (!$this->hasSection($section)) {
            return $default;
        }

        $return = $this->tags[$section];

        unset($this->tags[$section]);

        return $return;
    }

    public function all(): array
    {
        $tags = $this->tags;
        $this->tags = [];

        return $tags;
    }

    private function hasSection(string $section): bool
    {
        return array_key_exists($section, $this->tags) && $this->tags[$section];
    }
}
