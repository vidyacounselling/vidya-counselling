{!! '<'.'?xml version="1.0" encoding="UTF-8"?'.'>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"><url><loc>{{ url('/') }}</loc></url><url><loc>{{ url('/blog') }}</loc></url>@foreach($posts as $post)<url><loc>{{ $post->public_url }}</loc>@if($post->content_updated_at)<lastmod>{{ $post->content_updated_at->toIso8601String() }}</lastmod>@endif</url>@endforeach</urlset>
