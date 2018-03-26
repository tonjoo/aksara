<?php
namespace Plugins\PostType\Http;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Plugins\PostType\Model\Taxonomy;
use Plugins\PostType\Model\Term;
use Plugins\PostType\Model\Post;
use Illuminate\Support\Facades\File;
use Plugins\PostType\Repository\PostRepositoryInterface;
use Plugins\PostType\Repository\AksaraQuery;

class PostController extends Controller
{
    public function __construct(PostRepositoryInterface $postRepository)
    {
        \Eventy::action('aksara.post-type.post-controller.construct');
        $this->postRepository = $postRepository;
    }

    public function index(Request $request)
    {

        $aksaraQueryArgs = [];
        $aksaraQueryArgs['post_type'] = get_current_post_type();

        //@TODO dipindah ke controller untuk quick edit, gaboleh di index
        if ($request->get('bapply')) {
            if ($request->input('apply')) {
                $apply = $request->input('apply');
                if ($apply == 'trash') {
                    if ($request->input('post_id')) {
                        $post_id = $request->input('post_id');
                        foreach ($post_id as $v) {
                            $this->trash($v);
                        }
                    }
                } elseif ($apply == 'restore') {
                    if ($request->input('post_id')) {
                        $post_id = $request->input('post_id');
                        foreach ($post_id as $v) {
                            $this->restore($v);
                        }
                    }
                } elseif ($apply == 'destroy') {
                    if ($request->input('post_id')) {
                        $post_id = $request->input('post_id');
                        foreach ($post_id as $v) {
                            $this->destroy($v);
                        }
                    }
                }
            }
        }

        if ($request->get('bapplyall')) {
            $this->destroy_all();
        }


        // Generate and filter query
        $aksaraQueryArgs = \Eventy::filter('aksara.post-type.'.get_current_post_type().'.index.query-args', $aksaraQueryArgs);
        $aksaraQuery = new AksaraQuery($aksaraQueryArgs);
        $aksaraQuery = \Eventy::filter('aksara.post-type.'.get_current_post_type().'.index.query', $aksaraQuery);
        $posts = $aksaraQuery->paginate(10);

        $taxonomies = get_taxonomies(get_current_post_type());

        // Data
        $data = \Eventy::filter('aksara.post-type.'.get_current_post_type().'.index.data', ['posts'=>$posts, 'viewData'=>[], 'aksaraQuery'=>$aksaraQuery]);
        $posts = $data['posts'];
        $viewData = $data['viewData'];

        // Table Column
        $cols = \Eventy::filter('aksara.post-type.'.get_current_post_type().'.index.table.column', [], get_current_post_type());

        return view('post-type::post.index', compact('posts', 'viewData', 'total', 'cols', 'taxonomies'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $post = new Post();
        $post->post_type = get_current_post_type();
        return view('post-type::post.create', compact('post'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $post = new Post();

        $data = $request->all();
        $data['post_type'] = get_current_post_type();
        $data['post_image'] = '';

        $validator = $post->validate($data);

        if ($validator->fails()) {
            foreach ($validator->messages()->toArray() as $v) {
                admin_notice('danger', $v[0]);
            }
            return back()->withInput();
        }

        $post->post_type = get_current_post_type();
        $post->post_author = \Auth::user()->id;
        $post->post_date = date('Y-m-d H:i:s');
        $post->post_modified = date('Y-m-d H:i:s');
        $post->post_status = $data['post_status'];
        $post->post_title = $request->input('post_title', '') == null ? "" : $request->input('post_title', '') ;
        $post->post_slug = $request->input('post_slug', '') == null ? "" : $request->input('post_slug', '') ;
        $post->post_content = $request->input('post_content', '') == null ? "" : $request->input('post_content', '') ;
        //@TODO dihapus kolom dan datanya..
        $post->post_image = $data['post_image'];
        $post->save();

        \Eventy::action('aksara.post-type.'.get_current_post_type().'.create', $post, $request);

        admin_notice('success', __('post-type::message.add-success-message'));

        return redirect()->route('admin.'.get_current_post_type_args('route').'.edit', ['id'=>$post->id]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $post = Post::find($id);
        // $metabox = \App::make('metabox');

        return view('post-type::post.edit', compact('post'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $post = Post::find($id);
        $data = $request->all();

        $data['post_type'] = get_current_post_type();

        $validator = $post->validate($data);
        if ($validator->fails()) {
            foreach ($validator->messages()->toArray() as $v) {
                admin_notice('danger', $v[0]);
            }
            return back()->withInput();
        }

        if (isset($data['post_status'])) {
            $post->post_status = $data['post_status'];
        }

        $post->post_title = $request->input('post_title', '') == null ? "" : $request->input('post_title', '') ;
        $post->post_slug = $request->input('post_slug', '') == null ? "" : $request->input('post_slug', '') ;
        $post->post_content = $request->input('post_content', '') == null ? "" : $request->input('post_content', '') ;
        $post->post_image = '';

        $post->post_author = \Auth::user()->id;
        $post->post_modified = date('Y-m-d H:i:s');
        $post->save();

        \Eventy::action('aksara.post-type.'.get_current_post_type().'.update', $post, $request);

        admin_notice('success', __('post-type::message.edit-success-message'));

        return redirect()->route('admin.'.get_current_post_type_args('route').'.edit', ['id'=>$post->id]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $post = Post::find($id);
        if ($post) {
            delete_post_meta($id);
            delete_post_term($id);
            $post->delete();
            admin_notice('success', __('post-type::message.delete-success-message'));
        } else {
            admin_notice('danger', 'Tidak ada data yang dihapus.');
        }

        \Eventy::action('aksara.post-type.'.get_current_post_type().'.destroy', $post, $id);

        return redirect()->route('admin.'.get_current_post_type_args('route').'.index', ['post_status' => 'trash']);
    }

    public function trash($id)
    {
        $post = Post::find($id);
        if (set_post_meta($id, 'trash_meta_status', $post->post_status, false)) {
            $post->update(['post_status' => 'trash']);
        }
        admin_notice('success', __('post-type::message.move-trash-message', ['trash' => status_post('trash')]));
        return redirect()->route('admin.'.get_current_post_type_args('route').'.index');
    }

    public function restore($id)
    {
        $post = Post::find($id);
        if (get_post_meta($id, 'trash_meta_status')) {
            $post->update(['post_status' => get_post_meta($id, 'trash_meta_status')]);
        }
        delete_post_meta($id, 'trash_meta_status');
        admin_notice('success', __('post-type::message.return-message'));
        return redirect()->route('admin.'.get_current_post_type_args('route').'.index', ['post_status' => $post->post_status]);
    }

    public function destroy_all()
    {
        $posts = Post::where('post_status', 'trash')->get();
        if ($posts->count()) {
            foreach ($posts as $v) {
                $this->destroy($v->id);
            }
            admin_notice('success', __('post-type::message.all-delete-success-message'));
        } else {
            admin_notice('danger', 'Data gagal dihapus.');
        }
        return redirect()->route('admin.'.get_current_post_type_args('route').'.index');
    }
}