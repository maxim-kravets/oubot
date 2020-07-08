<?php


namespace App\Dto;


use App\Entity\Category;

class Item
{
    private string $name;
    private ?Category $category;
    private string $text;
    private string $file_id;
    private int $file_type;
    private string $about_url;
    private bool $visible;

    public function __construct(
        string $name,
        ?Category $category,
        string $text,
        string $file_id,
        int $file_type,
        string $about_url,
        bool $visible
    ) {
        $this->name = $name;
        $this->category = $category;
        $this->text = $text;
        $this->file_id = $file_id;
        $this->file_type = $file_type;
        $this->about_url = $about_url;
        $this->visible = $visible;
    }


    public function getName(): string
    {
        return $this->name;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getFileId(): string
    {
        return $this->file_id;
    }

    public function getFileType(): int
    {
        return $this->file_type;
    }

    public function isVisible(): bool
    {
        return $this->visible;
    }

    public function getAboutUrl(): string
    {
        return $this->about_url;
    }

}