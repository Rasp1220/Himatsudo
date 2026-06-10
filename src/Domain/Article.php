<?php
declare(strict_types=1);

namespace Himatsudo\Domain;

final readonly class Article
{
    public function __construct(
        public int     $id,
        public string  $title,
        public string  $slug,
        public string  $status,
        public ?string $content,
        public ?string $blocks,
        public ?string $excerpt,
        public ?string $eyeCatchImage,
        public ?int    $categoryId,
        public ?string $categoryName,
        public ?string $categorySlug,
        public ?string $categoryType,
        public int     $authorId,
        public ?string $authorName,
        public ?string $youtubeUrl,
        public ?string $youtubeVideoId,
        public ?string $youtubeThumbnail,
        public ?string $publishedAt,
        public string  $createdAt,
        public string  $updatedAt,
    ) {}

    /** @param array<string, mixed> $row */
    public static function fromArray(array $row): self
    {
        return new self(
            id:               (int)    ($row['id']                ?? 0),
            title:            (string) ($row['title']             ?? ''),
            slug:             (string) ($row['slug']              ?? ''),
            status:           (string) ($row['status']            ?? 'draft'),
            content:          isset($row['content'])          ? (string) $row['content']          : null,
            blocks:           isset($row['blocks'])           ? (string) $row['blocks']           : null,
            excerpt:          isset($row['excerpt'])          ? (string) $row['excerpt']          : null,
            eyeCatchImage:    isset($row['eye_catch_image'])  ? (string) $row['eye_catch_image']  : null,
            categoryId:       isset($row['category_id'])      ? (int)    $row['category_id']      : null,
            categoryName:     isset($row['category_name'])    ? (string) $row['category_name']    : null,
            categorySlug:     isset($row['category_slug'])    ? (string) $row['category_slug']    : null,
            categoryType:     isset($row['category_type'])    ? (string) $row['category_type']    : null,
            authorId:         (int)    ($row['author_id']         ?? 0),
            authorName:       isset($row['author_name'])      ? (string) $row['author_name']      : null,
            youtubeUrl:       isset($row['youtube_url'])      ? (string) $row['youtube_url']      : null,
            youtubeVideoId:   isset($row['youtube_video_id']) ? (string) $row['youtube_video_id'] : null,
            youtubeThumbnail: isset($row['youtube_thumbnail'])? (string) $row['youtube_thumbnail']: null,
            publishedAt:      isset($row['published_at'])     ? (string) $row['published_at']     : null,
            createdAt:        (string) ($row['created_at']        ?? ''),
            updatedAt:        (string) ($row['updated_at']        ?? ''),
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id'               => $this->id,
            'title'            => $this->title,
            'slug'             => $this->slug,
            'status'           => $this->status,
            'content'          => $this->content,
            'blocks'           => $this->blocks,
            'excerpt'          => $this->excerpt,
            'eye_catch_image'  => $this->eyeCatchImage,
            'category_id'      => $this->categoryId,
            'category_name'    => $this->categoryName,
            'category_slug'    => $this->categorySlug,
            'category_type'    => $this->categoryType,
            'author_id'        => $this->authorId,
            'author_name'      => $this->authorName,
            'youtube_url'      => $this->youtubeUrl,
            'youtube_video_id' => $this->youtubeVideoId,
            'youtube_thumbnail'=> $this->youtubeThumbnail,
            'published_at'     => $this->publishedAt,
            'created_at'       => $this->createdAt,
            'updated_at'       => $this->updatedAt,
        ];
    }
}
