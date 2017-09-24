<?php
// modify post ordering
\Eventy::addAction('aksara.init_completed',function(){
    $postTypes = \Config::get('aksara.post_type');

    foreach ($postTypes as $postType => $args ) {
        $supports = $args['supports'];

        if( in_array('title',$supports) )
            add_meta_box('title',$postType,'render_metabox_title',false,'metabox',10);

        if( in_array('editor',$supports) )
            add_meta_box('editor',$postType,'render_metabox_editor',false,'metabox',10);

        add_meta_box('save-post',$postType,'render_metabox_save_post',false,'metabox-sidebar',10);

        if( in_array('thumbnail',$supports) )
            add_meta_box('thumbnail',$postType,'render_metabox_thumbnail',false,'metabox-sidebar',10);

        add_meta_box('taxonomy',$postType,'render_metabox_taxonomy',false,'metabox-sidebar',10);
    }

    add_meta_box('media','media','render_metabox_media',false,'metabox',10);
    add_meta_box('page-template','page','render_metabox_page_template','save_metabox_page_template','metabox',10);
});

function save_metabox_page_template($post)
{
    return $post;
}

function render_metabox_page_template($post)
{
    $pageTemplates = $pageTemplates = \Config::get('aksara.post-type.page-templates',[]);
    $pageTemplate = get_page_template($post);

    if( sizeof($pageTemplates) == 0 )
        return;

    echo view('plugin:post-type::partials.metabox-page-template',compact('pageTemplates','pageTemplate'))->render();
}

function render_metabox_media($post)
{
    echo view('plugin:post-type::partials.metabox-media',compact('post'))->render();
}


function render_metabox_thumbnail($post)
{
    aksara_media_uploader();
    echo view('plugin:post-type::partials.metabox-thumbnail',compact('post','taxonomies'))->render();
}

function render_metabox_taxonomy($post)
{
    $taxonomies = get_taxonomies($post->post_type);

    if( !$taxonomies )
        return;

    echo view('plugin:post-type::partials.metabox-taxonomy',compact('post','taxonomies'))->render();
}

function render_metabox_save_post($post)
{
    echo view('plugin:post-type::partials.metabox-save-post',compact('post'))->render();
}

function render_metabox_editor($post)
{
    aksara_admin_enqueue_script(url('assets/modules/Plugins/PostType/assets/plugins/tinymce/tinymce.min.js'));
    \Eventy::addAction('aksara.admin.footer',function(){
        ?>
        <script type="text/javascript">
            $(document).ready(function () {
                if ($("#texteditor").length > 0) {
                    tinymce.init({
                        selector: "textarea#texteditor",
                        theme: "modern",
                        height: 300,
                        menubar: false,
                        plugins: [
                            "advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker",
                            "searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
                            "save table contextmenu directionality emoticons template paste textcolor"
                        ],
                        toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | forecolor backcolor | code",
                        style_formats: [
                            {title: 'Header 1', format: 'h1'},
                            {title: 'Header 2', format: 'h2'},
                            {title: 'Header 3', format: 'h3'},
                            {title: 'Header 4', format: 'h4'},
                            {title: 'Header 5', format: 'h5'},
                            {title: 'Header 6', format: 'h6'},
                            {title: 'Code', icon: 'code', format: 'code'}
                        ]
                    });
                }
            });
        </script>
        <?php
    });

    echo view('plugin:post-type::partials.metabox-editor',compact('post'))->render();
}

function render_metabox_title($post)
{
    echo view('plugin:post-type::partials.metabox-title',compact('post'))->render();
}