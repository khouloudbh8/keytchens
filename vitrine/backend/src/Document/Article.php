<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

#[MongoDB\Document(collection: "articles")]
class Article
{
    #[MongoDB\Id]
    private ?string $id = null;

    #[MongoDB\Field(type: "string")]
    private string $slug;

    #[MongoDB\Field(type: "string")]
    private string $lang;

    #[MongoDB\Field(type: "string")]
    private string $translationKey;

    #[MongoDB\Field(type: "string")]
    private string $title;

    #[MongoDB\Field(type: "string")]
    private string $category;

    #[MongoDB\Field(type: "string")]
    private string $excerpt;

    #[MongoDB\Field(type: "string")]
    private string $date;

    #[MongoDB\Field(type: "string")]
    private string $image;

    #[MongoDB\Field(type: "string")]
    private string $content;

    #[MongoDB\Field(type: "string")]
    private string $metaDescription;

    #[MongoDB\Field(type: "string")]
    private string $status;

    public function getId(): ?string { return $this->id; }

    public function getSlug(): string { return $this->slug; }
    public function setSlug(string $slug): self { $this->slug = $slug; return $this; }

    public function getLang(): string { return $this->lang; }
    public function setLang(string $lang): self { $this->lang = $lang; return $this; }

    public function getTranslationKey(): string { return $this->translationKey; }
    public function setTranslationKey(string $translationKey): self { $this->translationKey = $translationKey; return $this; }

    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): self { $this->title = $title; return $this; }

    public function getCategory(): string { return $this->category; }
    public function setCategory(string $category): self { $this->category = $category; return $this; }

    public function getExcerpt(): string { return $this->excerpt; }
    public function setExcerpt(string $excerpt): self { $this->excerpt = $excerpt; return $this; }

    public function getDate(): string { return $this->date; }
    public function setDate(string $date): self { $this->date = $date; return $this; }

    public function getImage(): string { return $this->image; }
    public function setImage(string $image): self { $this->image = $image; return $this; }

    public function getContent(): string { return $this->content; }
    public function setContent(string $content): self { $this->content = $content; return $this; }

    public function getMetaDescription(): string { return $this->metaDescription; }
    public function setMetaDescription(string $metaDescription): self { $this->metaDescription = $metaDescription; return $this; }

    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): self { $this->status = $status; return $this; }
}