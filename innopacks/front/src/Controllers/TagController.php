<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use InnoShop\Common\Models\Tag;
use InnoShop\Common\Repositories\ArticleRepo;
use InnoShop\Common\Repositories\TagRepo;

class TagController extends Controller
{
    /**
     * @return RedirectResponse
     * @throws Exception
     */
    public function index(): RedirectResponse
    {
        return redirect()->to(front_route('articles.index'));
    }

    /**
     * @param  Tag  $tag
     * @return mixed
     * @throws Exception
     */
    public function show(Tag $tag): mixed
    {
        $tags     = TagRepo::getInstance()->list(['active' => true]);
        $articles = ArticleRepo::getInstance()->list(['active' => true, 'tag_id' => $tag->id]);

        $data = [
            'slug'     => $tag->slug,
            'tag'      => $tag,
            'tags'     => $tags,
            'articles' => $articles,
        ];

        return inno_view('tags.show', $data);
    }

    /**
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function slugShow(Request $request): mixed
    {
        $slug     = $request->slug;
        $tag      = TagRepo::getInstance()->builder(['active' => true])->where('slug', $slug)->firstOrFail();
        $tags     = TagRepo::getInstance()->list(['active' => true]);
        $articles = ArticleRepo::getInstance()->list(['active' => true, 'tag_id' => $tag->id]);

        $data = [
            'slug'     => $slug,
            'tag'      => $tag,
            'tags'     => $tags,
            'articles' => $articles,
        ];

        return inno_view('tags.show', $data);
    }
}
