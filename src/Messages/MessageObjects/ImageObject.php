<?php

namespace Maksb\WhatsappClient\Messages\MessageObjects;

use Maksb\WhatsappClient\Entity\Hydrator\ArrayHydrateInterface;

class ImageObject implements ArrayHydrateInterface
{
    public function __construct(
        private string $url,
        private string $caption = '',
    ) {
    }

    public function fromArray(array $data): ImageObject
    {
        $this->url = $data['url'];

        if (isset($data['caption'])) {
            $this->caption = $data['caption'];
        }

        return $this;
    }

    public function toArray(): array
    {
        $returnArray = [
            'url' => $this->url
        ];

        if ($this->getCaption()) {
            $returnArray['caption'] = $this->getCaption();
        }

        return $returnArray;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getCaption(): string
    {
        return $this->caption;
    }
}
