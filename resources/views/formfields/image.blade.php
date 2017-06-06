@if(isset($dataTypeContent->{$row->field}))
    @if(strlen($dataTypeContent->{$row->field}) > 0)
        <a href="{{ route('voyager.images.deleteImage', ['id' => $dataTypeContent->id, 'db' => Request::segment(2), 'field' => $row->field]) }}" class="voyager-x"></a>
        <img src="{{ Voyager::image($dataTypeContent->{$row->field}) }}"
             style="width:200px; height:auto; clear:both; display:block; padding:2px; border:1px solid #ddd; margin-bottom:10px;">
    @endif
@endif
<input type="file" name="{{ $row->field }}">