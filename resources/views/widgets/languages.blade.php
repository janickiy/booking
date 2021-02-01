<div class="footer__copy">{{ trans('frontend.language') }}:

    @foreach($languages as $language)
        <a href="{{ url('/lang/' . $language->locale) }}" class="footer__lang {{ $language->locale == config('app.locale') ? 'footer__lang--active' : '' }}">{{ $language->name }}</a>
    @endforeach

</div>