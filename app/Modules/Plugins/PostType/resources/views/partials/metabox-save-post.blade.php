<div class="card-box post-box">
    <div class="card-box__header">
        <h2>Terbitkan</h2>
    </div>
    <div class="card-box__body form-horizontal">
        <div class="form-group form-group--table">
            <label class="col-form-label">Status</label>
            <div class="col-form-input">
                {!! Form::select('post_status', status_post(), $post->post_status, array('class' => 'form-control form-sm')); !!}
            </div>
        </div>
    </div>
    <div class="card-box__footer">
        <div class="submit-row clearfix">
            <input type="submit" class="btn btn-md btn-primary alignright" value="Simpan">
        </div>
    </div>
</div>