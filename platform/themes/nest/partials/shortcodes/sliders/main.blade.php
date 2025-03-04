@if (is_plugin_active('simple-slider') && count($sliders) > 0 &&
    $sliders->loadMissing('metadata') && $slider->loadMissing('metadata'))
    @php
        $style = $slider->getMetaData('simple_slider_style', true);
    @endphp
    @if ($style == 'style-3')
        <section class="home-slider position-relative mt-30">
            <div class="container">
                <div class="row">
                    <div class="col-lg-9">
                        <div class="position-relative">
                            <div class="hero-slider-1 style-3 dot-style-1 dot-style-1-position-1">
                                @foreach($sliders as $slider)
                                    @if ($slider->link && !($shortcode->show_newsletter_form == 'yes' && is_plugin_active('newsletter')))
                                        <a href="{{ url($slider->link) }}">
                                    @endif

                                    <div class="single-hero-slider single-animation-wrap" @if (!$loop->first) style="display: none;" @endif>
                                        <div class="container">
                                            <div class="slider-1-height-3 slider-animated-1">
                                                {!! Theme::partial('shortcodes.sliders.content', compact('slider', 'shortcode')) !!}
                                                <div class="slider-img">
                                                    @include('plugins/simple-slider::includes.image')
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    @if ($slider->link && !($shortcode->show_newsletter_form == 'yes' && is_plugin_active('newsletter')))
                                        </a>
                                    @endif
                                @endforeach
                            </div>
                            <div class="slider-arrow hero-slider-1-arrow style-3"></div>
                        </div>
                    </div>
                    <div class="col-lg-3 d-md-none d-lg-block">
                        @if (is_plugin_active('ads'))
                            @foreach (get_ads_keys_from_shortcode($shortcode) as $key)
                                {!! display_ad($key, 'banner-' . ($loop->index + 1)) !!}
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </section>
    @elseif ($style == 'style-4')
        <section class="home-slider position-relative mb-30 mt-30">
            <div class="container">
                <div class="home-slide-cover mt-30">
                    {!! Theme::partial('shortcodes.sliders.grid', compact('sliders', 'shortcode') + ['class' => 'style-4']) !!}
                </div>
            </div>
        </section>
    @elseif ($style == 'style-2')
        @php
            $ads = [];

            if (is_plugin_active('ads')) {
                $ads = get_ads_keys_from_shortcode($shortcode);
            }
        @endphp
        <section class="home-slider style-2 position-relative mb-50" @if ($shortcode->cover_image) style="background-image: url({{ RvMedia::getImageUrl($shortcode->cover_image) }}) !important;" @endif>
            <div class="container">
                <div class="row">
                    <div @class(['col-lg-12', 'col-xl-8' => ! empty($ads)])>
                        <div class="home-slide-cover">
                            <div class="hero-slider-1 style-4 dot-style-1 dot-style-1-position-1">
                                @foreach($sliders as $slider)
                                    @if ($slider->link && !($shortcode->show_newsletter_form == 'yes' && is_plugin_active('newsletter')))
                                        <a href="{{ url($slider->link) }}">
                                    @endif

                                    @php
                                        $tabletImage = $slider->getMetaData('tablet_image', true) ?: $slider->image;
                                        $mobileImage = $slider->getMetaData('mobile_image', true) ?: $tabletImage;
                                    @endphp

                                    <div class="single-hero-slider single-animation-wrap" style="@if (!$loop->first) display: none; @endif"
                                         data-original-image="{{ RvMedia::getImageUrl($slider->image, null, false, RvMedia::getDefaultImage()) }}"
                                         @if ($tabletImage) data-tablet-image="{{ RvMedia::getImageUrl($tabletImage, null, false, RvMedia::getDefaultImage()) }}" @endif
                                         @if ($mobileImage) data-mobile-image="{{ RvMedia::getImageUrl($mobileImage, null, false, RvMedia::getDefaultImage()) }}" @endif
                                    >
                                        {!! Theme::partial('shortcodes.sliders.content', compact('slider', 'shortcode')) !!}
                                    </div>

                                    @if ($slider->link && !($shortcode->show_newsletter_form == 'yes' && is_plugin_active('newsletter')))
                                        </a>
                                    @endif
                                @endforeach
                            </div>
                            <div class="slider-arrow hero-slider-1-arrow"></div>
                        </div>
                    </div>
                    @if (! empty($ads))
                        <div class="col-lg-4 d-none d-xl-block">
                            @foreach ($ads as $key)
                                {!! display_ad($key) !!}
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </section>
    @elseif ($style == 'style-5')
        @php
            $ads = [];

            if (is_plugin_active('ads')) {
                $ads = get_ads_keys_from_shortcode($shortcode);
            }
        @endphp

        <section class="home-slider position-relative mb-30">
            <div class="container">
                <div class="row">
                    <div class="col-lg-2 d-none d-lg-flex">
                        @php
                            $categories = ! is_plugin_active('ecommerce') ? collect() : ProductCategoryHelper::getActiveTreeCategories();
                        @endphp
                        @if ($categories->isNotEmpty())
                            <div class="categories-dropdown-wrap style-2 font-heading mt-30">
                                <div class="d-flex categori-dropdown-inner">
                                    <ul>
                                        @foreach ($categories->take(10) as $category)
                                            <li>
                                                <a href="{{ $category->url }}">
                                                    @if ($categoryImage = $category->icon_image)
                                                        <img src="{{ RvMedia::getImageUrl($categoryImage) }}" alt="{{ $category->name }}" width="30" height="30">
                                                    @elseif ($categoryIcon = $category->icon)
                                                        <i class="{{ $categoryIcon }}"></i>
                                                    @endif {{ $category->name }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                @if ($categories->count() > 10)
                                <div class="more_slide_open" style="display: none">
                                    <div class="d-flex categori-dropdown-inner">
                                        <ul>
                                            @foreach ($categories->skip(10) as $category)
                                                <li>
                                                    <a href="{{ $category->url }}">
                                                        @if ($categoryImage = $category->icon_image)
                                                            <img src="{{ RvMedia::getImageUrl($categoryImage) }}" alt="{{ $category->name }}" width="30" height="30">
                                                        @elseif ($categoryIcon = $category->icon)
                                                            <i class="{{ $categoryIcon }}"></i>
                                                        @endif {{ $category->name }}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                                <div class="more_categories" data-text-show-more="{{ __('Show more...') }}" data-text-show-less="{{ __('Show less...') }}"><span class="icon"></span> <span class="heading-sm-1">{{ __('Show more...') }}</span></div>
                                @endif
                            </div>
                        @endif
                    </div>
                    <div @class(['col-xl-7' => ! empty($ads), 'col-xl-10' => empty($ads)])>
                        <div class="home-slide-cover mt-30">
                            <div class="hero-slider-1 style-5 dot-style-1 dot-style-1-position-2">
                                @foreach($sliders as $slider)
                                    @if ($slider->link && !($shortcode->show_newsletter_form == 'yes' && is_plugin_active('newsletter')))
                                        <a href="{{ url($slider->link) }}">
                                    @endif

                                    @php
                                        $tabletImage = $slider->getMetaData('tablet_image', true) ?: $slider->image;
                                        $mobileImage = $slider->getMetaData('mobile_image', true) ?: $tabletImage;
                                    @endphp

                                    <div class="single-hero-slider single-animation-wrap" style="@if (!$loop->first) display: none; @endif"
                                         data-original-image="{{ RvMedia::getImageUrl($slider->image, null, false, RvMedia::getDefaultImage()) }}"
                                         @if ($tabletImage) data-tablet-image="{{ RvMedia::getImageUrl($tabletImage, null, false, RvMedia::getDefaultImage()) }}" @endif
                                         @if ($mobileImage) data-mobile-image="{{ RvMedia::getImageUrl($mobileImage, null, false, RvMedia::getDefaultImage()) }}" @endif
                                    >
                                        {!! Theme::partial('shortcodes.sliders.content', compact('slider', 'shortcode')) !!}
                                    </div>

                                    @if ($slider->link && !($shortcode->show_newsletter_form == 'yes' && is_plugin_active('newsletter')))
                                        </a>
                                    @endif
                                @endforeach
                            </div>
                            <div class="slider-arrow hero-slider-1-arrow"></div>
                        </div>
                    </div>
                    @if (! empty($ads))
                        <div class="col-lg-3">
                            <div class="row mt-20">
                                @foreach ($ads as $key)
                                    <div class="col-md-6 col-lg-12 mt-10">
                                        {!! display_ad($key, 'banner-' . ($loop->index + 1)) !!}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    @else
        <section class="home-slider position-relative mb-30">
            <div class="home-slide-cover">
                {!! Theme::partial('shortcodes.sliders.grid', compact('sliders', 'shortcode') + ['class' => 'style-4', 'itemClass' => 'rectangle']) !!}
            </div>
        </section>
    @endif
@endif
