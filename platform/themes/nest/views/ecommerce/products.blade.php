@php
    $layout = theme_option('product_list_layout');

    $requestLayout = request()->input('layout');
    if ($requestLayout && in_array($requestLayout, array_keys(get_product_single_layouts()))) {
        $layout = $requestLayout;
    }

    $layout = ($layout && in_array($layout, array_keys(get_product_single_layouts()))) ? $layout : 'product-full-width';

    Theme::layout('full-width');

    Theme::asset()->usePath()->add('jquery-ui-css', 'css/plugins/jquery-ui.css');
    Theme::asset()->container('footer')->usePath()->add('jquery-ui-js', 'js/plugins/jquery-ui.js');
    Theme::asset()->container('footer')->usePath()->add('jquery-ui-touch-punch-js', 'js/plugins/jquery.ui.touch-punch.min.js');

    $products->loadMissing(['categories', 'categories.slugable']);
@endphp

<div class="container mb-30">
    <div class="row">
        @if ($layout === 'product-full-width')
            <div class="col-lg-12 m-auto mt-4">
                <a class="shop-filter-toggle mb-0" href="#">
                    <span class="fi-rs-filter mr-5"></span>
                    <span class="title">{{ __('Filters') }}</span>
                    <i class="fi-rs-angle-small-up angle-up"></i>
                    <i class="fi-rs-angle-small-down angle-down"></i>
                </a>
                <form action="{{ URL::current() }}" method="GET" id="products-filter-ajax">
                    @include(Theme::getThemeNamespace() . '::views.ecommerce.includes.filters')
                </form>
            </div>

            <div class="mt-4">
                <div class="products-listing position-relative">
                    @if (! empty($pageName))
                        <div class="ps-block__header">
                            <h1 class="h3">{{ $pageName }}</h1>
                        </div>

                        @if (! empty($pageDescription))
                            <div class="ps-block__content mb-4">
                                {!! BaseHelper::clean($pageDescription) !!}
                            </div>
                        @endif
                    @endif

                    @include(Theme::getThemeNamespace() . '::views.ecommerce.includes.product-items', compact('products'))
                </div>
            </div>
        @elseif($layout === 'product-left-sidebar')
            @if (! empty($pageName))
                <div class="ps-block__header">
                    <h1 class="h3">{{ $pageName }}</h1>
                </div>

                @if (! empty($pageDescription))
                    <div class="ps-block__content mb-4">
                        {!! BaseHelper::clean($pageDescription) !!}
                    </div>
                @endif
            @endif
            <div class="col-xl-3 primary-sidebar mt-4">
                <div class="widget-area">
                    @include(Theme::getThemeNamespace('views.ecommerce.includes.filters'))
                </div>
            </div>
            <div class="col-lg-9">
                <div class="mt-4">
                    <div class="products-listing position-relative bb-product-items-wrapper">
                        @include(Theme::getThemeNamespace() . '::views.ecommerce.includes.product-items', compact('products'))
                    </div>
                </div>
            </div>
        @elseif($layout === 'product-right-sidebar')
            @if (! empty($pageName))
                <div class="ps-block__header">
                    <h1 class="h3">{{ $pageName }}</h1>
                </div>

                @if (! empty($pageDescription))
                    <div class="ps-block__content mb-4">
                        {!! BaseHelper::clean($pageDescription) !!}
                    </div>
                @endif
            @endif
            <div class="col-lg-9">
                <div class="mt-4">
                    <div class="products-listing position-relative bb-product-items-wrapper">
                        @include(Theme::getThemeNamespace() . '::views.ecommerce.includes.product-items', compact('products'))
                    </div>
                </div>
            </div>
            <div class="col-xl-3 primary-sidebar mt-4">
                <div class="widget-area">
                    @include(Theme::getThemeNamespace('views.ecommerce.includes.filters'))
                </div>
            </div>
        @endif
    </div>
</div>
