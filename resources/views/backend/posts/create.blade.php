@extends('layouts.admin')

@section('content')

<div class="card shadow mb-4">
      <div class="card-header py-3 d-flex">
        <h6 class="m-0 font-weight-bold text-primary">Create Post</h6>
        <div class="ml-auto">
            <a href="{{ route('admin.posts.index') }}" class="btn btn-primary">
                <span class="icon text-white-50">
                    <i class="fa fa-home"></i>
                </span>
                <span class="text">Posts</span>
            </a>
        </div>
      </div>
      
    <div class="card-body">
    {!! Form::open(['route' => 'admin.posts.store', 'method' => 'post', 'files' => true]) !!}

        <div class="row">
            <div class="col-12">
                <div class="form-group">
                    {!! Form::label('title', 'Title') !!}
                    {!! Form::text('title', old('title'), ['class' => 'form-control']) !!}
                    @error('title')<span class="text-danger">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="form-group">
                    {!! Form::label('description', 'Description') !!}
                    {!! Form::textarea('description', old('description'), ['class' => 'form-control ckeditor']) !!}
                    @error('description')<span class="text-danger">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-4">
                {!! Form::label('category_id', 'Category') !!}
                {!! Form::select('category_id', ['' => '---'] + $categories->toArray(), old('category_id'), ['class' => 'form-control']) !!}
                @error('category_id')<span class="text-danger">{{ $message }}</span>@enderror
            </div>
            <div class="col-4">
                {!! Form::label('comment_able', 'Comment Ability') !!}
                {!! Form::select('comment_able', [ '1' => 'Yes','0' => 'No'], old('comment_able'), ['class' => 'form-control']) !!}
                @error('comment_able')<span class="text-danger">{{ $message }}</span>@enderror
            </div>
            <div class="col-4">
                {!! Form::label('status', 'Status') !!}
                {!! Form::select('status', ['1' => 'active', '0' => 'Inactive'], old('status'), ['class' => 'form-control']) !!}
                @error('status')<span class="text-danger">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="row pt-4">
            <div class="col-12">
                {!! Form::label('Sliders', 'images') !!}
                <br>
                <div class="file-loading">
                    {!! Form::file('images[]', ['id' => 'post-images', 'multiple' => 'multiple']) !!}
                    <span class="form-text text-muted">Image width should be 800px x 500px</span>
                    @error('images')<span class="text-danger">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>    
        
        <div class="form-group pt-4">
            {!! Form::submit('Submit', ['class' => 'btn btn-primary']) !!}
        </div>

    {!! Form::close() !!}

    </div>
        
</div>

@push('script')
<script src="https://cdn.ckeditor.com/4.16.1/standard/ckeditor.js"></script>

<script>
    CKEDITOR.replace( 'description' );

    $(function() {
        $("#post-images").fileinput({
            theme: "fas",
            maxFileCount: 5,
            allowedFileTypes: ['image'],
            showCancel: true,
            showRemove: false,
            showUpload: false,
            overwriteInitial: false,
        });
    });

</script>

@endpush
@endsection
