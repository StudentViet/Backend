@component('mail::message')
{!! $offter['content'] !!}
@if($offter['url'] != NULL)
@component('mail::button', ['url' => $offer['url'], 'color' => 'success'])
Lấy lại mật khẩu
@endcomponent
@endif

Thanks,<br>
{{ config('app.name') }}
@endcomponent
