@extends_backend('layouts.layout')

@section('breadcrumb')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.root') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('sample-supplier') }}">@lang('sample-master::supplier.labels.all_suppliers')</a></li>
    <li class="breadcrumb-item active">@lang('sample-master::supplier.labels.edit_supplier')</li>
</ol>
@endsection


@section('content')
<div class="content__head">
    <h2 class="page-title">@lang('sample-master::supplier.labels.edit_supplier')</h2>
</div>
<!-- /.content__head -->

<div class="content__body">
    <div class="row">
        <div class="col-lg-8 col-md-11">
            <div class="card-box">
                <div class="card-box__header">
                    <h2>@lang('sample-master::supplier.labels.edit_supplier')</h2>
                </div>
                <div class="card-box__body">
                    {!! Form::model($supplier, ['route' => ['sample-supplier-update', $supplier->id], 'class' => 'form-horizontal'])!!}
                    {{ method_field('PUT') }}
                        @include('sample-master::supplier._form')
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

