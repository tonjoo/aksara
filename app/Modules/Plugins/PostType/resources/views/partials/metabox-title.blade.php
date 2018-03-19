<div class="post-box title-field">
    <div class="title-field-wrap">
        {!! Form::text('post_title', $post->post_title, ['class'=>'form-control', 'placeholder' => __('plugin:post-type::default.title', ['post-type' => get_current_post_type_args('label.name')])  ]) !!}
    </div>
    @if($post->id)
        <div class="edit-slug-box">
            <strong>__('plugin:post-type::default.permalink'):</strong>
            <span class="sample-permalink">
                {{-- @todo --}}
                <a href="{{ get_post_permalink($post) }}">
                    <span id="editable-post-name">{{ get_post_permalink($post) }}</span>
                </a>
            <input name="post_slug" type="text" autocomplete="off" value="{{ $post->post_slug }}" id="new-post-slug">
            </span>
            ‎<span id="edit-slug-buttons">
                <button type="button" class="edit-slug btn btn-secondary">{{__('plugin:post-type::default.edit') }}</button>
            </span>
        </div>
    @else
        <div class="slug-wrap clearfix">
            <label>{{__('plugin:post-type::default.slug') }}</label>
            {!! Form::text('post_slug', '', ['class'=>'form-control', 'placeholder' => __('plugin:post-type::default.slug')]) !!}
        </div>
    @endif
</div>
