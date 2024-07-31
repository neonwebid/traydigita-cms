<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\TwigExtensions;

use ArrayAccess\TrayDigita\App\Modules\Core\Abstracts\AbstractCoreTwigExtension;
use ArrayAccess\TrayDigita\App\Modules\Core\Depends\PostLoop;
use ArrayAccess\TrayDigita\App\Modules\Core\Entities\Admin;
use ArrayAccess\TrayDigita\App\Modules\Core\Entities\Post;
use ArrayAccess\TrayDigita\App\Modules\Core\TwigExtensions\Posts\PostWrapper;
use DateTimeInterface;
use Twig\TwigFunction;

class PostExtension extends AbstractCoreTwigExtension
{
    public function getFunctions(): array
    {
        $this->engine->enableAutoReload();
        return [
            new TwigFunction(
                'post_loop',
                fn () : PostLoop => $this->core->postLoop
            ),
            new TwigFunction(
                'have_posts',
                fn () : bool => $this->core->postLoop->havePosts()
            ),
            new TwigFunction(
                // alias for have_posts
                'have_post',
                fn () : bool => $this->core->postLoop->havePosts()
            ),
            new TwigFunction(
                'the_post',
                fn () : ?PostWrapper => PostWrapper::create($this->core->postLoop->thePost())
            ),
            new TwigFunction(
                'get_post',
                fn () : ?Post => $this->core->postLoop->post
            ),
            new TwigFunction(
                'the_title',
                fn () : string => $this->core->postLoop->post?->getTitle()??''
            ),
            new TwigFunction(
                'the_content',
                function () : string {
                    $content = $this->core->postLoop->post?->getContent()??'';
                    $newContent = $this->core->getManager()->dispatch('the_content', $content);
                    return is_string($newContent) ? $newContent : $content;
                }
            ),
            new TwigFunction(
                'the_excerpt',
                function ($length = null) : string {
                    $length = is_numeric($length) ? (int) $length : null;
                    $length = !is_int($length) ? 55 : $length;
                    $length = max($length, 0);
                    $excerptLength = $this->core->getManager()->dispatch('excerpt_length', $length);
                    $excerptLength = is_int($excerptLength) ? $excerptLength : $length;
                    $excerpt = $this->core->postLoop->post?->getExcerpt($excerptLength)??'';
                    $newExcerpt = $this->core->getManager()->dispatch('the_excerpt', $excerpt);
                    return is_string($newExcerpt) ? $newExcerpt : $excerpt;
                }
            ),
            new TwigFunction(
                'the_id',
                fn () : int => $this->core->postLoop->post?->getId()??0
            ),
            new TwigFunction(
                'the_author',
                fn () : ?Admin => $this->core->postLoop->post?->user
            ),
            new TwigFunction(
                'the_date',
                fn () : ?DateTimeInterface => $this->core->postLoop->post?->getPublishedAt()??null
            ),
            new TwigFunction(
                'is_single',
                fn () : bool => $this->core->postLoop->isSingle()
            ),
            new TwigFunction(
                'is_page',
                fn () : bool => $this->core->postLoop->isPage()
            ),
            new TwigFunction(
                'is_home',
                fn () : bool => $this->core->postLoop->isHome()
            ),
            new TwigFunction(
                'is_archive',
                fn () : bool => $this->core->postLoop->isArchive()
            ),
            new TwigFunction(
                'is_search',
                fn () : bool => $this->core->postLoop->isSearch()
            ),
            new TwigFunction(
                'is_404',
                fn () : bool => $this->core->postLoop->is404()
            ),
            new TwigFunction(
                'is_author',
                fn () : bool => $this->core->postLoop->isAuthor()
            ),
            new TwigFunction(
                'is_category',
                fn () : bool => $this->core->postLoop->isCategory()
            ),
            new TwigFunction(
                'is_tag',
                fn () : bool => $this->core->postLoop->isTag()
            ),
            new TwigFunction(
                'is_singular',
                fn () : bool => $this->core->postLoop->isSingular()
            ),
            new TwigFunction(
                'is_paged',
                fn () : bool => $this->core->postLoop->isPaged()
            ),
            new TwigFunction(
                'is_home',
                fn () : bool => $this->core->postLoop->isHome()
            ),
            new TwigFunction(
                'is_front_page',
                fn () : bool => $this->core->postLoop->isFrontPage()
            ),
            new TwigFunction(
                'is_search',
                fn () : bool => $this->core->postLoop->isSearch()
            ),
            new TwigFunction(
                'search_query',
                fn () : string => $this->core->postLoop->getSearchQuery()??''
            ),
            new TwigFunction(
                'posts_mode',
                fn () : string => $this->core->postLoop->getMode()
            ),
            new TwigFunction(
                'posts_page',
                fn () : int => $this->core->postLoop->page
            ),
        ];
    }
}
