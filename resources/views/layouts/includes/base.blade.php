<!DOCTYPE html>
<html lang="{{ setting('localeCode', 'en') }}" dir="auto">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="{{ setting('favicon') }}" />
    <title>{{ $title ?? '' }} - {{ setting('websiteName', env('APP_NAME')) }}</title>
    <link rel="stylesheet" href="{{ asset('css/ckeditor5.css') }}" />
</head>

<body>
    <div class="ck ck-content ck-editor__editable ck-rounded-corners ck-editor__editable_inline ck-blurred">
        {!! $content ?? '' !!}
    </div>
</body>

</html>
