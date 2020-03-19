<?php
namespace Xpoint\Pages;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Xpoint\Pages\Models\Page;
use Xpoint\Pages\Models\PageMeta;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;
use Xpoint\Pages\Models\PageAnalytic;
use SingleQuote\FileManager\FileSystem;
use SingleQuote\FileManager\Controllers\FilesController;

class PagesController extends Controller
{
    /**
     * PagesController constructor.
     */
    public function __construct() {
        $this->authorizeResource(Page::class, 'page');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $pages = Page::default()->orderBy('order_id')
            ->select('id', 'is_visible', 'is_home', 'is_active', 'parent_id', 'name', 'alias')
            ->with(['parent' => function($query){
                $query->select('id', 'name');
            }])
            ->get();

        foreach($pages->where('is_active', 1) as $page){
            $page->groupedAnalytics = $this->renderAnalyticsIndex($page);
        }

        return view("xpoint-pages::pages.index")->with(compact('pages'));
    }

    /**
     * Group the analytics for the index page
     *
     * @param Page $page
     * @return \Illuminate\Support\Collection
     */
    private function renderAnalyticsIndex(Page $page) : \Illuminate\Support\Collection
    {
        $analytics = PageAnalytic::whereYear('created_at', date('Y'))->wherePageId($page->id)->get();

        return $analytics->unique('uuid')->groupBy(function($item){
            return (int) $item->created_at->format('m');
        });
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $pageTypes = $this->getPageTypes();

        $pages = Page::default()->whereDoesntHave('parent')->get();

        return view("xpoint-pages::pages.create")->with(compact('pages', 'pageTypes'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $page = Page::create([
            'parent_id' => $request->parent_id,
            'created_by' => $request->user()->id,
            'order_id' => Page::count(),
            'content' => $request->content,
            'styling' => $request->styling,
            'name' => $request->name,
            'category' => $request->category ?? 1,
            'alias' => $request->is_home ? '' : Str::slug($request->alias ? Str::after($request->alias, url('/')) : $request->name),
            'is_home' => $request->is_home ?? 0,
            'is_active' => $request->is_active ?? 0,
            'is_visible' => $request->is_visible ?? 0,
        ]);

        $this->storeMeta($request, $page);

        cache()->forget("xpoint-pages-$page->alias");
                
        return response("", 204);
    }
    
    /**
     * Store or update meta resource
     * 
     * @param Request $request
     * @param Page $page
     */
    private function storeMeta(Request $request, Page $page)
    {
        $meta = PageMeta::firstOrNew([
            'page_id' => $page->id
        ]);
        
        $metas = $request->meta;
        $metas['keywords'] = $request->auto_keywords ? (new PagesController)->getMetaString($request->content) : explode(',', $metas['keywords']);
        $metas['robots'] = explode(',', $metas['robots']);
        $metas['page_id'] = $page->id;
        
        $meta->fill($metas);
        
        $meta->save();
    }

    /**
     * Extract keywords from string
     *
     * @param Request   $request
     * @param string    $string
     * @return array
     */
    private function getMetaString(string $string = null) : array
    {
        $keyWords = [];

        preg_match_all("/[a-z0-9\-]{4,}/i", $string, $keyWords);


        if(is_array($keyWords) && count($keyWords[0])) {
            return $keyWords[0];
        } else {
            return [];
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Page  $page
     * @return \Illuminate\Http\Response
     */
    public function edit(Page $page)
    {
        $pageTypes = $this->getPageTypes();

        $pages = Page::default()->where('id', '!=', $page->id)
            ->whereDoesntHave('parent')
            ->get();

        \XpointPages::setActivePage($page);

        return view("xpoint-pages::pages.edit")->with(compact('page', 'pages', 'pageTypes'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Page  $page
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Page $page)
    {       
        $page->update([
            'parent_id' => $request->parent_id,
            'created_by' => $request->user()->id,
            'content' => $request->content,
            'styling' => $request->styling,
            'name' => $request->name,
            'category' => $request->category ?? 1,
            'alias' => $request->is_home ? '' : Str::slug($request->alias ? Str::after($request->alias, url('/')) : $request->name),
            'is_home' => $request->is_home ?? 0,
            'is_active' => $request->is_active ?? 0,
            'is_visible' => $request->is_visible ?? 0,
        ]);

        $this->storeMeta($request, $page);

        cache()->forget("xpoint-pages-$page->alias");
        
        return response("", 204);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Page  $page
     * @return \Illuminate\Http\Response
     */
    public function destroy(Page $page)
    {
        cache()->forget("xpoint-pages-$page->alias");

        $page->analytics()->delete();
        $page->heatmaps()->delete();
        $page->meta()->delete();

        Page::whereParentId($page->id)->update(['parent_id' => null, 'is_active' => false, 'is_visible' => false]);

        $page->delete();

        return response("", 204);
    }

    /**
     * Return the view for the cards
     *
     * @return \Illuminate\Http\Response
     */
    public static function dashboardRow()
    {
        $visitors = PageAnalytic::whereMonth('created_at', now())->remember(3600)->unique('uuid')->count();
        $visitorsPrevious = PageAnalytic::whereMonth('created_at', now()->subMonth())->remember(3600)->unique('uuid')->count();

        $percentageVisitors = $visitors && $visitorsPrevious ? number_format((1 - ($visitorsPrevious/$visitors)) * 100, 0) : 100;
        $activePages = Page::whereIsActive(1)->rememberCount(3600);
        $nonActivePages = Page::whereIsActive(0)->rememberCount(3600);

        return view('xpoint-pages::dashboard.index')->with(compact('visitors', 'percentageVisitors', 'activePages', 'nonActivePages', 'visitorsPrevious'));
    }

    /**
     * Update the resources for sortables
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function updateSorting(Request $request)
    {
        foreach($request->get('items', []) as $index => $page){
            Page::whereId($page)->update(['order_id' => $index]);
        }

        return response("", 204);
    }
    
    /**
     * Store the selected assets
     * 
     * @param Page $page
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadAssets(Page $page, Request $request)
    {
        $results = ['data' => []];
        
        foreach($request->file('files', []) as $file){
            
            $upload = FileSystem::driver('public')->upload($file, "uploads");
            
            $config = FileSystem::driver('public')->get($upload);
            
            $results['data'][] = [
                'name' => $config->filename,
                'type' => Str::before($config->mimetype, '/'),
                'src' => route('media', $config->basepath)
            ];
        }
        
        return response()->json($results);
    }

    /**
     * Get the uploaded assets
     * See docs https://grapesjs.com/docs/modules/Assets.html#configuration
     * 
     * @param Request $request
     * @param Page $page
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAssets(Request $request, Page $page)
    {
        $results = [];
        $files = FileSystem::driver('public')->allFiles("uploads");
        
        foreach($files as $file){
            $results[] = [
                'name' => $file->filename,
                'type' => Str::before($file->mimetype, '/'),
                'src' => route('media', $file->basepath),
            ];
        }
        
        return response()->json($results);
    }
    
    /**
     * Get the layout and update the page content
     *
     * @param Request $request
     * @param int $layout
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLayout(Request $request, string $layout)
    {
        $page = Page::firstOrNew([
            'id' => $request->page
        ]);

        if($layout !== $page->category){
            $page->content = null;
        }

        $page->category = $layout;

        $type = $request->get('type', 'pages');

        if (View::exists("xpoint-$type.$layout")) {
            $page->content = view("xpoint-$type.$layout")->withPage($page)->render();
        }

        return response()->json($page);
    }

    /**
     * Return the page plugins
     *
     * @return type
     */
    public function getPlugins()
    {
        return response()->json(\XpointPages::getPlugins());
    }

    /**
     * Return the bootstrap plugin
     *
     * @param Page $page
     * @return string
     */
    public function bootstrapPlugin(Page $page = null) : string
    {
        return 'grapesjs-blocks-bootstrap4';
    }

    /**
     * Return the maps plugin
     *
     * @return array
     */
    public function mapsPlugin(Page $page = null) : array
    {
        return [
            "name" => "map",
            "label" => "Google Maps",
            "category" => "Layout",
            "attributes" => [
                "class" => 'fa fa-map-o'
            ],
            "content" => [
                "type" => 'map',
                "style" => [
                    "height" => '350px'
                ],
                "mapType" => 'q', // q - Roadmap / w - Satellite
                "address" => 'Nederland',
                "zoom" => 1 // max 20
            ]
        ];
    }

    /**
     * Return the section plugin
     *
     * @return array
     */
    public function sectionPlugin(Page $page = null) : array
    {
        return [
            "name" => "section-block",
            "category" => "Layout",
            "label" => 'Section',
            "attributes" => [
                "class" => "fa fa-square"
            ],
            "content" => '<section style="height:100px;"></section>'
        ];
    }

    /**
     * Return the icon plugin
     *
     * @return array
     */
    public function iconPlugin(Page $page = null) : array
    {
        return [
            "name" => 'icon-block',
            "category" => "Layout",
            "label" => 'Material icon',
            "attributes" => [
                "class" => "fa fa-flag"
            ],
            "content" => '<i class="material-icons">announcement</i>'
        ];
    }

    /**
     * Return the blog post plugin
     *
     * @return array
     */
    public function blogPostsPlugin(Page $page = null) : array
    {
        if(!View::exists('xpoint-plugins.blog-posts')){
            return [];
        }

        return [
            "name" => 'blog-posts',
            "category" => "Plugins",
            "label" => __("Blog posts"),
            "attributes" => [
                "class" => "fa fa-list"
            ],
            "content" => view('xpoint-plugins.blog-posts')->withPage($page)->render()
        ];
    }
    
    /**
     * Return the case post plugin
     * 
     * @return array
     */
    public function casePostPlugin(Page $page = null) : array
    {
        if(!View::exists('xpoint-plugins.case-posts')){
            return [];
        }

        return [
            "name" => 'case-posts',
            "category" => "Plugins",
            "label" => __("Case posts"),
            "attributes" => [
                "class" => "fa fa-archive"
            ],
            "content" => view('xpoint-plugins.case-posts')->withPage($page)->render()
        ];
    }
    
    /**
     * Return the product post plugin
     * 
     * @return array
     */
    public function productPostPlugin(Page $page = null) : array
    {
        if(!View::exists('xpoint-plugins.product-posts')){
            return [];
        }
        
        return [
            "name" => 'product-posts',
            "category" => "Plugins",
            "label" => __("Product posts"),
            "attributes" => [
                "class" => "fa fa-shopping-cart"
            ],
            "content" => view('xpoint-plugins.product-posts')->withPage($page)->render()
        ];
    }
    
    /**
     * Get the page types
     * 
     * @return array
     */
    public function getPageTypes() : array
    {
        $files = \File::allFiles(resource_path('views/xpoint-pages'));

        $views = [];

        foreach($files as $file){
            $views[] = Str::before($file->getFileName(), '.blade.php');
        }

        return $views;
    }

    /**
     * Copy page
     * @param \Xpoint\Pages\Models\Page $page
     * @return \Illuminate\Http\RedirectResponse
     */
    public function duplicate(Request $request, Page $page)
    {
        $copy = $page->replicate();
        
        $copy->order_id = Page::count();
                
        $copy->is_active = false;
        
        $copy->push();
        
        $copyMeta = $page->meta->replicate();
        
        $copyMeta->page_id = $copy->id;
        
        $copyMeta->push();

        
        $page->childs->each(function($child) use($request){
            $this->duplicate($request, $child);
        });
        
        return back();
    }

}
