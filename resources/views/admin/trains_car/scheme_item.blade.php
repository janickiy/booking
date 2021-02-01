<div class="panel panel-default schemes-list__item">
    <div class="panel-body schemes-list__container">
        <div class="item">
            <button type="button" class="btn btn-xs btn-danger item__remove-btn"> <i class="fa fa-times"></i> </button>

            <input name="schemes[{{ $iteration or '' }}][key]" type="text" class="form-control item__key" value="{{ $key or '' }}" />
            <img width="100%" class="item__preview" src="@isset($schemeUrl) {{ Storage::url($schemeUrl) }} @endisset" />

            <input class="item__file-hidden" type="hidden" name="schemes[{{ $iteration or '' }}][file_path]" value="{{ $schemeUrl or '' }}" />
            <input name="schemes[{{ $iteration or '' }}][file]" type="file" class="form-control form-control-file item__file" />
        </div>
    </div>
</div>