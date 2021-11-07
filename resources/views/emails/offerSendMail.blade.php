@component('mail::message')
# Chào {{ $offer['name'] }}

bên {{ config('app.name') }} bọn mình vừa nhận được yêu cầu lấy lại mật khẩu từ phía bạn phải không ? <br/>
<small><b>Nếu không phải do bạn thực hiện hãy bỏ qua email này nhé.</b></small>
@component('mail::button', ['url' => $offer['url'], 'color' => 'success'])
Lấy lại mật khẩu
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
