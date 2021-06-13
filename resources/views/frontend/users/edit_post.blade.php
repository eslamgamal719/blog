@extends('layouts.app')
@section('content')

<div class="col-lg-9 col-12">
    <h3>Edit Post ({{ $post->title }})</h3>
        {!! Form::model($post, ['route' => ['users.post.update', $post->id], 'method' => 'put', 'files' => true]) !!}
            <div class="form-group" style="width: 870px;">
                {!! Form::label('title', 'Title') !!}
                {!! Form::text('title', old('title', $post->title), ['class' => 'form-control']) !!}
                @error('title')<span class="text-danger">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                {!! Form::label('description', 'Description') !!}
                {!! Form::textarea('description', old('description', $post->description), ['class' => 'form-control ckeditor']) !!}
                @error('description')<span class="text-danger">{{ $message }}</span>@enderror
            </div>

            <div class="row">
                <div class="col-4">
                    {!! Form::label('category_id', 'Category') !!}
                    {!! Form::select('category_id', ['' => '---'] + $categories->toArray(), old('category_id', $post->category_id), ['class' => 'form-control']) !!}
                    @error('category_id')<span class="text-danger">{{ $message }}</span>@enderror
                </div>
                <div class="col-4">
                    {!! Form::label('comment_able', 'Comment Ability') !!}
                    {!! Form::select('comment_able', [ '1' => 'Yes','0' => 'No'], old('comment_able', $post->comment_able), ['class' => 'form-control']) !!}
                    @error('comment_able')<span class="text-danger">{{ $message }}</span>@enderror
                </div>
                <div class="col-4">
                    {!! Form::label('status', 'Status') !!}
                    {!! Form::select('status', ['1' => 'active', '0' => 'Inactive'], old('status', $post->status), ['class' => 'form-control']) !!}
                    @error('status')<span class="text-danger">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="row pt-4">
                <div class="col-12">
                    <div class="file-loading">
                        {!! Form::file('images[]', ['id' => 'post-images', 'multiple' => 'multiple']) !!}
                    </div>
                </div>
            </div>    
            
            <div class="form-group pt-4">
                {!! Form::submit('Submit', ['class' => 'btn btn-primary']) !!}
            </div>

        {!! Form::close() !!}
</div>
<div class="col-lg-3 col-12 md-mt-40 sm-mt-40">
    @include('partial.frontend.users.sidebar')
</div>

    @push('js')
 
    <script>
       

        $(document).ready(function() {

            $("#post-images").fileinput({
             theme: "fa",
             maxFileCount: 5,
             allowedFileTypes: ['image'],
             showCancel: true,
             showRemove: false,
             showUpload: false,
             overwriteInitial: false,
             //to show or delete images
             initialPreview: [
                 @if ($post->media->count() > 0)
                     @foreach($post->media as $media)
                            "{{ asset('assets/posts/' . $media->file_name) }}",
                     @endforeach
                 @endif
             ],
             initialPreviewAsData: true,
             initialPreviewFileType: 'image',
             initialPreviewConfig:[
                @if ($post->media->count() > 0)
                     @foreach($post->media as $media)
                            {caption: "{{ $media->file_name }}", size: {{ $media->file_size }}, width: "120px", url:"{{ route('users.posts.media.destroy', [$media->id, '_token' => csrf_token()]) }}", key:"{{$media->id}}"},
                     @endforeach
                 @endif 
             ],
         });

        }); 
         
    </script>
    @endpush 
@endsection