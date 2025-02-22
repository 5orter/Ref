@foreach($categories->chunk(5) as $chunk)
    <ul @if ($loop->last) class="end" @endif>
        @foreach($chunk as $category)
            @if($category->parent_id == 0)
                <li>
                    <a href="{{ route('public.single', $category->url) }}">
                        {{ $category->name }}
                    </a>
                </li>
            @endif
        @endforeach
    </ul>
@endforeach
