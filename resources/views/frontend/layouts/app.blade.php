@php use Illuminate\Support\Str; use App\Models\User; @endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'OpenSesame BD - Your Online Fashion Store')</title>
    <meta name="title" content="@yield('title', 'OpenSesame BD - Your Online Fashion Store')">
    <meta name="description"
          content="@yield('meta_description', 'Shop the latest fashion, accessories & more at OpenSesame BD. Fast delivery across Bangladesh.')">
    <meta name="robots" content="@yield('robots', 'index, follow')">
    <link rel="canonical" href="@yield('canonical', url()->current())">

    @yield('preload')

    {{-- Open Graph --}}
    <meta property="og:site_name" content="OpenSesame BD">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:title" content="@yield('og_title', 'OpenSesame BD - Your Online Fashion Store')">
    <meta property="og:description"
          content="@yield('og_description', 'Shop the latest fashion, accessories & more at OpenSesame BD. Fast delivery across Bangladesh.')">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="@yield('og_image', asset('images/og.webp'))">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('og_title', 'OpenSesame BD - Your Online Fashion Store')">
    <meta name="twitter:description"
          content="@yield('og_description', 'Shop the latest fashion, accessories & more at OpenSesame BD. Fast delivery across Bangladesh.')">
    <meta name="twitter:image" content="@yield('og_image', asset('images/og.webp'))">

    <link rel="icon" type="image/x-icon" href="{{ asset('images/favicon.png') }}">

    <!-- DNS prefetch for remaining CDNs (Font Awesome) -->
    <link rel="dns-prefetch" href="//cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>

    <!-- Poppins: self-hosted latin subsets (no third-party round-trip) -->
    <link rel="preload" as="font" type="font/woff2" href="{{ asset('fonts/poppins/poppins-400.woff2') }}" crossorigin>
    <link rel="preload" as="font" type="font/woff2" href="{{ asset('fonts/poppins/poppins-600.woff2') }}" crossorigin>
    <link rel="preload" as="font" type="font/woff2" href="{{ asset('fonts/poppins/poppins-700.woff2') }}" crossorigin>
    <link rel="preload" as="font" type="font/woff2" href="{{ asset('fonts/poppins/poppins-800.woff2') }}" crossorigin>

    <!-- Bootstrap 5 (critical: grid/utilities for the header; self-hosted) -->
    <link href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">

    <!-- Font Awesome (deferred â€” icons not needed for initial paint) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" media="print"
          onload="this.media='all'">
    <noscript>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    </noscript>

    <!-- Shop CSS: inlined (~48 KB raw / ~10 KB gzipped). Removes a render-blocking
         round-trip and prevents late style recalcs from shifting the layout. -->
    @php
        $shopCssFile = file_exists(public_path('frontend/css/shop.min.css'))
            ? public_path('frontend/css/shop.min.css')
            : public_path('frontend/css/shop.css');
    @endphp
    <style>{!! @file_get_contents($shopCssFile) !!}</style>

    {{-- â•â•â• CRITICAL CSS (above-the-fold) â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
         Covers header + category nav + hero so the page renders correctly
         before shop.css arrives. Keep in sync with shop.css if those
         selectors ever change.
    â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
    <style>
        /* Metric-matched local fallback: identical vertical metrics to Poppins so
           text laid out before the web font arrives does not reflow afterwards. */
        @font-face {
            font-family: 'Poppins Fallback';
            src: local('Segoe UI'), local('Arial'), local('Helvetica Neue');
            size-adjust: 105%;
            ascent-override: 100%;
            descent-override: 33.33%;
            line-gap-override: 0%
        }

        /* Self-hosted Poppins (latin subset) */
        @font-face {
            font-family: 'Poppins';
            font-style: normal;
            font-weight: 400;
            font-display: block;
            src: url('{{ asset('fonts/poppins/poppins-400.woff2') }}') format('woff2');
            unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD
        }

        @font-face {
            font-family: 'Poppins';
            font-style: normal;
            font-weight: 500;
            font-display: block;
            src: url('data:font/woff2;base64,d09GMgABAAAAAB5EAAwAAAAAP3AAAB3xAAEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAGx4cLgZgAIFUCucw0gQLgzYAATYCJAOGaAQgBYNIB4QLGzgyRUbuF6kFE0WpogTB/5cEDY6w8FewiRFiiIiwCNViu/tsr9/4UWMvAr0KA7RHNBNdUmOa90P/zMU5SgCu8gU+V5YdQeyqIxiyfoQksy3R8/vxm7PnfQWSiIeulsgkLZGk1bOECKHRCMVCMY/8Ozy/zf+jPddGT5kBBiBSksIlqrXBAK6KOdRFtXPV/00252v7LV/kXvw/F/Wq3V+yuRxsSdsZNTlZB2Z8qhgm+oJKAgQ8OBCnE+KkZeJ6aXAi79TZnw/e9fJx5P1/deU/WePAvfbZqkmVOk1BsmTYWYe9icMwPunGRRPAjojGYfiICUgF8Pc6y/ablshB8oyTaqFoolCXGZWhjuurvt6XQforr2wvatHrA+vQsASWj2QHAKurcilZPtQBVt4AQEVcUZ1rg0Wdoiipi46hNqjdeWYRKXPd30//G3rXtrTCjhKGA/C8r/kMFWBRpkxhJ07JdCBWJMAYIHQiGlpEYurVmgAeEgVVEEGN0s6nttavooDcjwlwfphUV+BLAu2zvrDNeYvOXhYowTvAjtWR6UvPVE/etGwD8AAge3MDsE0kIIAGrBIhjImR6mGHxF7JeYAVgv5fpaXNSMbzZR5sS0SkI7IQOQgkgobgISYWMTnxOUnI8VItwtFizYkJiFREJpP6p2oN4+F4b2wbWwqvzX7zsiH/cdUl5+23TdG31z7+Dfpr5UvpezX10o5/seINK+vpVRX1FxwzNN8uq82IApQYvltSvkdaRsdzfSYIrOybI2e0UNIHWaW2ekaoBy0mGJBZoGnlMwOx2klfM+MLUc4EmQTvYGm1G6fNnEZ7yJR6mJk+MOsDwg1O3Y0FgTE2zmm+QL/zkJoEGciOzqm+GNE1RDML2D7/C19vhDBNKzVxSeNkc5Syb6sMFzF2mSF6eAe0VPyjYtJ3zbk6BJmw7qTheiSf250Fcx+feA6J+XbMAfBl5yaNW7RxYhFQjN2GR3TKFtzqRKoz9y4g1dlX/V5mDkMow/vkdJmKYLd8tZYw0lrOkkN8EfIpsavPMK8XgOJZBlHs7BRtEmvyGOmrLVZtU70JNnC5gSU3mW8oMHDliuU0sgRTN/6saVziHxuMm3bzlWv5K1MpSqMLrN8rgJEOny/z1eEbeHafObBXBfJuUq1DR8OuwWYPonwLHaq3WYXPnsQnV7mq9Nn7fGI8uHdOQLSeHoYtW9jd2O5suVA0I8SXsj3o6OS/W/PEqmcl9x6Ceo9CMFJPnt2epGLzGHvgZWTI3UZwxrr6kaWZD4FCXJ/QxciagQHxL7lpkHd+MIxvUbOKW97aNFmu2ILvnozESqA3Ck+UUjU+yiqB0gL3Sp6c/gRjiuQ2BooKFD/Q3QZuwuBUQY/iV2oWc3Egu959yrZNxB3+KaLD768G1+D7RCFg8hqrGxZA6pGatHgQZaDCFyTyb1KRTzzBwdYPJ3slNQbokwuB3eJ46POJeO9VzjfJOpposdjUTE7NGRm47wMHxSTV0wwBcOhfUhz42//wVYQr3tMx1bEUrwV+x8t4ZK1K4CHGYIz9bie7NCkwyNmGTeMvhnsCP5yw3DTxLt7LtOJZ2LuFL8i4lcDBiLblkhTp+/N8+HylOOgxl9RMDCXwQynBwr23B3++plEqUKH0QktsqYtb0MFCHL1Q5y0Nl14pwZLBx5bsMx1OfchXcPIURwl8Lv9RiZE+qu28D9PbCk7z+U43j5HPujQUwu8RICMj5d59BqbbOdMeNq0GhUe/+ZyupT1M5WFcWzI1ZWE065reXu7ZOmtY3Tx5ls3Db6CzeVrfoNrEYsEWfKjcK58+TS/HtqUXD2I62/nyfgz0mwGHu5kXCaxBljJyXBwLKihJ5paMWFC57eWAQygYPmEZl06A4lCmLs40LbIekSyRYvoK5LN0Jcsz68XiCGVIuHO+G5s+M0+hebLBa+mM6gW/B7GR1YY7bB27wYQCPWgorp5U6W5A9L3nGVY0b4XXkquxBBnZ40EvNkiWLPsO69+sIjlqTTV8pd3RdpXF9TjzSTWZPL5DBur/N5lRO/gvZx+Slbu94k0B5iCyL9TDUAqk7IJwCDR60yLWuethMdx0uZ5rVp213CxAdIU2TulfzT/Ub6PlzHMVoiwtQnkJRbc4du2ahZl3UsZ+DHB5P9JYg0nWa/NxcAmygTsLSzMkZcvXrJJeEEuLfUAPnrBcJzYKOnxIgwelUmZ7c4twKL9c9AsdIqJURogupVFdHS+MNjomAGeuKpf5QuTAarLbLeHt0JwaWEvxeBDdCMI30aXdSE8xTPDhOpJVV9tqgRySkrjZYhm5bSZKQrDlYPDIZomxF/ZwEqwD/D9tTNfY49gtkePG7WVV5nFjTlFdHB69ZwchG/aWOrdUUEwWkDRIeaS8wXrdFJfTQxuaH3bjDxsG3Uyac8qQva0n9/w4DLB5C130TOpiV9a9r2RZUML6mkd24vTVpe/lDJRgGn++wtqN+oFr12UwM+Ib95NPkxU9qO0nuuTmNvBOkLqKRTl4iPMFVaCs6p/Vt6QnE3ECLRGA8GtM9SCrBhBldSdM+kAeWAwVUkoB9hVBKVzFndougMVdXxBuwZmyC52dNmMKZt4VB+5veInGcJ2/fEi+8BdGdsMwN8qV97ElARg6vA8+WtBUdIRNDiKv6DmHJA8J1eHx4c5wH8ps26aTBrzS0kmDKNsZMNFRaA1zjBCLu7U9rdyz3ei/L3n6ck74AI2SQXylrDfeahxTgUGp0cmB8Yf6fZtc+uZPKtdK6r036mSHDug2T556OMn2hfiy0ESLz4RsecVodGdNppaPo19E3d3n+R/k5DvtAF5F2cF8TVi8u/cVSmrZdh9YbebxNQDtDrJqq+VpYL9y17SRpQzbdXcQN2V4ayRR3FNQna4zywSjnKs3VuO6eO9AcCM/qMD42p7+9xUA8H0VP+4xyp2soB3JCviLjawvNeicYlHPpPm0ybKsejEPqaSP6dnW2+1kxKvN+XxyPAyFuhWYn/Cs834Gq9Us1y8Hmez/63GOGsnmS3Zz/TCj5tkUwurqiuR99x5xwzfHdnxSuvKbe4m44/vd3oRQ2nqDXU/CBo186nyxqeXcMINjTgMHTBfxfaz/ydXe8bkZdyGy7sjRPPDz4TMmobg+L4R+a7JXyCqRtSDYDj+gU56UIMNsr31FrKC+wC4jn/Y/f7WD4gfX6c68nihmjOCW2RvdcCkOfDE0b8b5PA7x5r7sbDHreWNZIHbHkrm3FPNuvR4YgW1TeedXVu9DXW8yOgqdB6ovz+L2V19CXmsyOsoGVlXHjEaFj4LsHTijg86OUkNqe2xLlaYLeXX1A84ibQ5NytG2NG7cgZgxxuOIbm7YrQWfcxYWQJVwvPPm354+nouePphaUDDv9geg0K/TcLkcEYt1Wo3Xiis2bw757nn3lhlNrKw4jJH1+mTSzrFyOo0GYyiw+MMAqvX6xW04scRPYLrYIrVPhXivJx+LFRoLaXQNukDOgiNNS5euzsyYcYeGBK3d5n1rT59aphXKQtfbsE6ClMcIRWFgxlq/9ORZH6apIjAXdregDu1pSKt1zw0A7I25ij15z6FIgH2cw1n57MmVsRV2sOYkWrkWT+YYtd+cuNUWv9VvLdGtwa/BTXbYak7YZk/aBqoo03c9d6ddg68DJtZ2JEgECcAcnQzzjI8uL450+hb96gT0nNb3tmzhgQuXWkcVo/6lPapEL0vAYmHEqXUJfjb/hg3b31EdQPvq733R4gdd+52aZo+4hcagcJl0tiSrOdy/vKOmqau9WiRaWV0v500wnY+1gNW/vK5WUFNK0JPLIeM3kQOQQ0InWd1ESeP65si6ZqKYw2JCHJLARLOlOzJUQEeg4D6nUj43ewFmj0H53/Wf2Kvse317I99/4XoJFgeUE1JNfomuXohuYkoosA/SNK1omGfyXpSSqUKBXigSygSlHNyzxhPXwXX7IaJS3hG24UBVCY1TTaE0SAXkpoZyWV2rBXuipzm8Gm6Yo3BfO68T8fkKlZAlkkM8gYoP5vQl9bWktsIV/wOKyz4yydxAVMgbiSQzmULRPV4JOdgQVNVGkkpbSYrLWMjeGnebT2cyyvuWiheVU6liFn50QsNmUpUCPk8hpDBUEAd8tAMiUISQd43fkEeA3ERGvbS5b7l/LfzGzf6iQbfW0dDijda5h8/wJOV8kYzFEinYTIFCBISrHv1vr9n6+H/jwMY4V6Ir4X+3BcAr3Xr7lwAa3unyVtT01ngiFFWRB5dW1BLEpA/I5C+IDQySvZrAdUwUwH/PN1ed0Bp2V6vI22qtAsLB+id2DEFusKlk5Sv8CqGxqL+qdEneonRxBpIkSW8i9u5YtSkw7HW/39G3Z9UmpyBngdSC9DcNqs27q7esfPFi7S4pkYdDqbA4Ga6YTczRYks+LQGvNuWWbmr/M7Tyz1CH+rmP4+eA3y3yinISWVeH4yeH5ghrSggqPIOl1IRhNlH+hyuZoWz66F+oDE/g02m7v9gP8t5zDByzVHjPNyKr1p10klj8BiajRS5ntDSU81lO0sl1yKrzDd7KAYtjoBYcHDqpsxz2eCwHTmq8D0MPO839Kzo6+9d0+Wf9YMRy60f7j58ssPdd77ODh0N21O3H9sc4OASg7wedRAcxv1TKgHXnfHtL3TxTlLXXHKeyrdmpsrrR1WJKBG1DIRgnPCUBLhIWgRHXMATGhn6z+xpsNl/DK+/8MTT6U8xvYHoeueywCXM+YwvGireAEesLm92XAX6K2yvw4KOvlB9/9QAOdf+C+BkBVsOG6Bj4OmyIioYBMvqYxXHMtb+SZfuAV2I/llrJq2woZ7SGop3JD4ul9VcZ4K6W2ptohmA6HPpR1Uwm22lv+P4mw/3Bh0FQvaxzxS1yhenJLs1+EfbkZesmXbn5NlLAG0eSjMmaLWX/JHiKZnXCueygL8hms8jkf6EgeBbtxbHMjC7GMyodomByN0zYuYt1eXQ5xKCbG0qENX3V0cZgPkQil94vQdSHmLx4Qz5DwSFR7I0UCZg7ZL9hgS3DdnBz6KZSo5RINUrlTfiGXtzWTZVKu6jCNv0NMDAUObQyaqjrNnQHAkxO6PZQ/7b5++f3gtFQ5xbSAVLfVvxWPFhn+czSd/Cf7f/0fmb53Np3aHbHbC+QDz0a/hf+9/Fw71HdUR24XsmtmjZDf3NkH0Ay96SjZrDGec8F7l6MH3X6WAXKG/ec0/eN4x7YdyrwcmHjq4Xt51aBcygP1gMeD+geNFaAvHpeb0Agglz4MVULqkUzXkVgC9tEvOXa98hYrbQYjZYWYTVUKlYvQw/xrWCsjgq++LcaXy5u43P7tFru8oBIxK4sHVfBM/BCxaoVWVZ09cuqoF316xQDr+MzRKq7V6avk6+sXu5INnY/hTB4gabSbNZUCaqyDd0p2hWVlcpOAa9P75mhmFyPfRzXMtOiDVVuZ0MU+GxwwygnIiGjkm3Ll3WnSpe7KpQ9AkGfXs/r6+GrFZ1cbpcqkLvAKIXoWkkvKJ2qnW6ZhuwQylltaHQSd+NQtaC+pBJ/TdWCk7ECHVydb0f9HLN/Wkek83ValW28ksjyhmrDdoPe+p9PB/hkSSWJ4pby/USJWMFmSxQSIjwDz75nyieBGu/+ta4yEQpGXSv4K7VKJKe7ULnT2TtgkBc/G7//g5ev4Bn44sSPh8HIw8HDmAF4GFdLojnSWN0LFMy6os8v5dSVvdHvpjHBQMlvTCovigxm4EXsMoGwT68T9/fylUrG+srpdNL+5QJlcaWssETLYGA00kI0WlJYrGGYLS4++OKfKgJLFBBwewvgu8qqwD8ywBhYrVh9gsj+FNiVhOOqFkyL4XgNARK2nj5NnwANlhpYwuVxdTkwClaB+7G1WLpqcSDnDhbHW5J94ad78mwKREYTRK5ClgN2RKjhPCsW81Nm8uy9W2/LEL2RJQSxo5AN7k6VZk/nCpwWuVqoLoXs0LqWdavtgIlEwdCAAVkKBfUQnp+wlwSQXXPgnc77yLX3kF0A/zPqLLKjeTc9rY5zyFZ4uHW43Xvdfx2Qk68k9weGvcPtVxZcSeHMAdCQSblFAe/cz5f07h88Oggu2f62B+1/N/4jzwdYdvTXElRejDPWTBZqlDxuFRrxEpEapQSxVhoBz5WES6pMoA39YCakJn6+oau4PI1SSI41xzjzUCW/gi998HYY6Pna9skA8JBaVZ45cGXz7fg0F1O7QKsZu2274piNT49qwFvkjKTZ2VkY/nPnvJlOELT+PeSJ4Tc59bFGyNg8J7DF4wlsVW6IAxzixMfTM/Q3Wag1KxRakxBgLPa7zIMoEyeR36zUK+hcakUNkdvQ1B5mqycH6WQ2k7Pk2tH3lOcEiRUcAfiA8fxs/IMbDOr1mzn7pxnlz4dzvp6kUCe/jj/7krHLU48nmJBICoGAQoIr64Oa02HPsyY1kxvhjSJnPR/Ha5YdKO49oqE8fSB6x1J4Jeyff/u9DqZoD9AYgtSFhuytBgWMmsXgyMiUo2wEO7kix67nksu1106/HGtKYqgnQauIwaDz6Wi0iN75BToCoBty0tLe9Tt4RuDHtw9ZjAN1dcZjdlht7WELh5KBwyYWzGqpaKPdLtqwWm61MDbO3qcbV0mtLGUVhVLH41HrKylMWZMqHqq/+1MBiSUTiVhSEhI6Uh/Pb5ApQBYfrZJhsMWzKJzZYcZG/3vEybFoLdFKHCksOkcknisqHCaCte3Wi1TT7LzYonkzB7arD+H142ZQ/o6DRHQwGIwV79gEkmclxQlbDGaxGJ0isV4B3m/WN1QoolpWLImRsj6ryd2dDcfnxJ6OjT0VmwPa7+qb9SDDVufSkLRFJSoSCafSF5M01RWqFGJXAcpDIosKCtaRwIGWXVOuWdf0rmnu3OfZ5/1FgsJTovmcBsWcuekp6xPitYst2QJqeeHdiKy8U0tFWJC2SU4pR7IzlEuykk+F36BkFWkobLacFm8iXUtJmUhK/DQ1n5aTNpaYeDK1aLsePB5+0Kh70Ak4SBRsvGgpoTE2L/Fj9roPAiiucXAdNekLAgHYam1vacsAoyVCMh4vpJWtFDVlvp2U9HZmWbe0DCR087a4zJkgi1+qNqDJZENxqYZMwmuMMNGjcWryUsK5wsL1ZGKnGA0TwFak42KT7aKttmdd5LSCpaae3D36slBjMDTaiuGaucP43S96HmNWDFQOVAFjN1mwc8wIV9zf8G98QTkLC2KVhm2hBlzORefXhHFN5kacmDhcPykgdnz0g+mHr+abezdHyDb1BEO3zMH25ff7wbitT33YudY9nqqYYlowH12fMXaLXbPfWdvpLgCnh050tujEXGby5W554UyHs39dR2f/2k7/7avuq3f84Ld1e7qGEsnePTt3ikQ7sYd56u49YLvsMKZLcHjMw2zOffewtarhCDYgtmWmF5vYZLWGMFyN73ud/Slf2dFsjfdtw3GwB+CQneWwzfB1/6bex89V62xZ2Wso+CIISJu6Az3iOhxRjiHiONurBfPjz38sSeWqmJIGi8Pqc0YbWtN/xOihpbU4dbqovDSXy5n9PK3wZZmQAUG5MB5A/dGUkKOUK8jJLxMUftiTKg26bAw3PlZbqU2txjMOEdANPVVph9CgcCoOOQuY0jYe1CVvywBsi5JXblPOYf8w0whzUxvktwN2GbIN3UlSn0sXUxiNR6eZG1MnoXQo9P3WpoDh92G3eHufWKNs/S+osL+LLy2VZmRmsIu7UExFWravFSL0FDBV6d4yWTFWx2BgdNIiNFpcVOzVFOlFtwT0PzPrwf91uF67/qu4MQQkfsiRwb3EJlwCQpdSqoFkrX3GJitFxA3KCfXhV5EDtIPFJBHz4PdRQQq651gP+PzXShrUY3yXoArGe03wczhb0XYKjSCPUnv7ZP2Pph+/XGDu857toSnthy32Y7W19gHdvE25jHra02deIJcy4F8NXLBUyvDvwC8F7MXRlBMRPK2Kbe8h0Ypp/CY6StDG4NMlSiYxgQfT+fleurCnfCCdyv0f0LRXyoeAa1RBTK0yigf2Dm7WmzdraIwZPNnLpuNKuKQUX1KCmxv+j5tJiTdTkkcSk0aSweJ9w5iEm/Rn4vmLq/PYnUP3BxnpBfMW1xiCpn2uFBcgqve7vv803TnZU/9KBWxD5dBq/uo1A6/GXPPHVyObf34xeHf6WFFemVRKprC/TK8loVSO9M0tDuBDm0P+vhAQ4XIQB9chq841eNuuBXPdNoUkY9GiDInC5mE/3Na83P2DWVOWuJl76xPFi7yUlLwXik9uuy+8GKS8/RtPCTIsYRnKT7guTdirylfMip9/Ubtanb9X/w5+2uc64KrYQHwO2GGxWOci9QIKQrKq4RZuZNIika7Imr9gy1vB9bVtXGaAPW6UPeky2+Nd54THtgI212pmgD2uyenhsaHZXJHMAHt8vHB6xlOpFKFtxIa/JbdigRlgj/uBPekrp8dLG4XN9QUzwB73jtPjTYlxtl2y8EY3ro8AYAbY44bZk06yPULOCQ9sIjbXNWaAPe6g02Of5b17BHfinSrAMv6lzPNRUiOzHZM0tPS/bc6ZGWCPO8metN3pMSiZAI35gBlgjzvv9Dhjs7EDBms91tTGraU7LTY1LK0dKOTBEr/QrVnwJbmwD1d+e+S5gCCOAGPR6ufaDzf+8m5KIEabh+q7/X0RKxqM1V45QJ72BXBscNQ62uZtihSoN33BsSG+zQLAF3ghOMC/Mlc6sqxB0oa53bXyx1/JxOwbfFDbRoLcmcCxIbXNQiCXzKzjj1g7Eyrzt+MTaeZso0g8cR+rzN/SzDwCmcDPB+d44ACgHs8nkGu3Zer8oT2iMt/MyyuC8WfKO7Myf8fPiFv6H3rhgfOTETXli4TS+JZFZwPmUNebfVkYYKpo3sR57pzxbz4i4inA2xfbA4APa8i3Zp8YXng5kcpUCi+SZvBbo/LCp6mS9XBD+6Xnff0KbTV9n+tRpuLK6XrZWY/R6Yryl1zn5dRLPfxCZdSfWJlW67+iImb0E1zCATP9GOWACQQfTTR+DWx6SbIcKk5BgtkXvpvcb3CFg0i1yo4GnVHwJV3BFAYEuSEnAdKOAvkZI808oAjCR++WVqxKhE6nh0aCDUO4r8KinKCFJQF1RGJDy78xqFwlyZCEfiIhO+FjtzBY+HpkZ247XjP6slleZwl6LS4n5bMijYlxQO5oyaTy6DocfobN//9ThCk4LTRhnSlBSHUQD1afJibDVKvMzNwEYYtrqrj6RVwE4uoz6ESCYPG4sdySQnkpMMhVnKxWlLwNMBPR/eUoLIq4ToKtSdn1DCbr5LO3IFEAX8vF1TmZua2+dDLKIN5P4rKZNAG5fkWsFHHuS6wRM4n4nJWTN7nrsaVGCfSvjEV7n3kMzdPAURULFpT+3/Jyz5x0ZIRIbupWmp6SdAYAqeNylEzzyr6XTEySkhxuyIyjv0d+OjKyrlMsSlKiYQCQOhiSlBqXyZ+Q8v0MUXg7me/FVHVslLEP8uF+llYf4TNRdVIa9kprU747qhOEq0IYbBwfKULnSO9iqal6Z8IBUxjgva3maRMtTLgs0YCrgBYOmFcrQtg0BwDPvMNcYTIdd4VbYLcrAo1NHynqihJH44qWiSwseKrATa3k7tOCpoRTtQrMHQitXi1Wi9BoqhesLjdeSUBGxXs53set+dcIQ65GNbc2TYiwcHCIxHQElOgQigwEsHRltRKF5vno12nTqAJsgnLfaCd2WjerxQh4uO+cI+iC3DUo4E2ATfBkaJViF6RNaf1sQSx3AT1KHfc7l1crYVVdhKYQHoRaD1tis2qwTsvgMloo3JcLHa/gzXwPVMIiQMTfF/1vINYcceaaZ74FFlpksXgJEiVJliJVmnQZMi2RJRvCUjly5cmHhFKgUJFiaBhYJXBK4REQkZCVoaCi7YeOgakcCxuEg4uHT0BIRExCSkZOQUlFTUNLR8/AyMTMwsrGzsHJpaJIg1Za5U27PbXaZhsccsbxZqz3pRV2+NkvNjVrrfd94yeHnfWbX/3umAumTLioUpWtqn2gxqRpd91y2x3P1PrIPfddUudH23zqY5+o98Ir63i4NWjSqNlRXn6+lJL0fh4C2j3XoUunbsv0uG5Ar6A+/V763rDPXHalOZ/72heu+o8hIaPecM2YNc55y9tGmrfRD4Xpkl4mC+dqYtqa3TgchBvaeeUc0iCew438r8hwcRCPLyUBAAA=') format('woff2');
            unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD
        }

        @font-face {
            font-family: 'Poppins';
            font-style: normal;
            font-weight: 600;
            font-display: block;
            src: url('{{ asset('fonts/poppins/poppins-600.woff2') }}') format('woff2');
            unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD
        }

        @font-face {
            font-family: 'Poppins';
            font-style: normal;
            font-weight: 700;
            font-display: block;
            src: url('{{ asset('fonts/poppins/poppins-700.woff2') }}') format('woff2');
            unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD
        }

        @font-face {
            font-family: 'Poppins';
            font-style: normal;
            font-weight: 800;
            font-display: block;
            src: url('{{ asset('fonts/poppins/poppins-800.woff2') }}') format('woff2');
            unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD
        }

        :root {
            --primary: #007d50;
            --primary-dark: #005138;
            --primary-mid: #007d50;
            --primary-light: #007d50;
            --primary-gradient: linear-gradient(135deg, #005138 0%, #007d50 55%, #007d50 100%);
            --primary-gradient-rev: linear-gradient(135deg, #007d50 0%, #007d50 55%, #005138 100%);
            --hero-gradient: linear-gradient(135deg, #005138 0%, #005138 40%, #007d50 75%, #007d50 100%);
            --secondary: #ffd032;
            --text-dark: #10211B;
            --text-body: #33413C;
            --text-muted: #767676;
            --bg-light: #F3FAF6;
            --bg-white: #fff;
            --border: #D8EDE3;
            --shadow: 0 2px 14px rgba(0, 81, 56, .08);
            --radius: 10px;
            --transition: all .25s ease
        }

        /* Reserve the glyph box for icon-font placeholders so layout does not
           reflow when the deferred Font Awesome stylesheet applies. */
        i.fas, i.far, i.fab, i.fal, i.fad {
            display: inline-block;
            width: 1em;
            font-style: normal;
            text-align: center
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0
        }

        body {
            font-family: 'Poppins', 'Poppins Fallback', system-ui, -apple-system, sans-serif;
            font-size: 15px;
            color: #33413C;
            background: #F3FAF6;
            overflow-x: hidden
        }

        a {
            text-decoration: none;
            color: inherit
        }

        a:visited {
            color: inherit
        }

        img {
            max-width: 100%
        }

        .top-notice-bar {
            background: #005138;
            color: rgba(255, 255, 255, .9);
            padding: 7px 0;
            font-size: 12.5px;
            letter-spacing: .2px
        }

        .top-notice-bar .container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px
        }

        .top-bar-left, .top-bar-right {
            display: flex;
            align-items: center;
            gap: 14px;
            white-space: nowrap
        }

        .top-notice-bar a {
            color: rgba(255, 255, 255, .9)
        }

        .top-notice-bar a:hover {
            color: #ffd032
        }

        .top-notice-bar i {
            color: #ffd032;
            margin-right: 3px
        }

        .top-bar-logout-btn {
            background: none;
            border: 0;
            padding: 0;
            color: rgba(255, 255, 255, .9);
            font: inherit;
            display: inline-flex;
            align-items: center;
            cursor: pointer
        }

        .top-bar-logout-btn:hover {
            color: #ffd032
        }

        .footer-link-button {
            background: none;
            border: 0;
            padding: 0;
            color: inherit;
            font: inherit;
            cursor: pointer;
            text-align: left
        }

        .footer-link-button:hover {
            color: var(--primary)
        }

        .top-bar-sep {
            color: rgba(255, 255, 255, .3)
        }

        .top-bar-social {
            display: flex;
            align-items: center;
            gap: 8px
        }

        .top-bar-social a {
            width: 22px;
            height: 22px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: rgba(255, 255, 255, .12);
            font-size: 11px
        }

        .top-bar-social a i {
            margin-right: 0;
            color: #fff
        }

        .top-bar-social a:hover {
            background: #ffd032
        }

        .top-bar-social a:hover i {
            color: #005138
        }

        .main-header {
            background: #fff;
            box-shadow: 0 3px 24px rgba(0, 81, 56, .12);
            z-index: 1000;
            border-bottom: 3px solid transparent;
            border-image: linear-gradient(135deg, #005138 0%, #007d50 55%, #007d50 100%) 1
        }

        .header-logo {
            width: 175px;
            height: auto;
            aspect-ratio: 175/78;
            max-width: 100%;
            object-fit: contain
        }

        .footer-logo {
            max-height: 50px;
            width: auto
        }

        .search-form .form-control.search-input {
            border: 2px solid #007d50;
            border-right: none;
            border-radius: 10px 0 0 10px;
            padding: 10px 16px;
            font-size: 14px;
            outline: none;
            box-shadow: none
        }

        .search-form .form-control.search-input:focus {
            border-color: #005138;
            box-shadow: none
        }

        .search-form .btn-search {
            background: linear-gradient(135deg, #005138 0%, #007d50 55%, #007d50 100%);
            color: #fff;
            border: 2px solid #007d50;
            border-left: none;
            border-radius: 0 10px 10px 0;
            padding: 10px 20px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer
        }

        .header-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px
        }

        .cart-btn {
            position: relative;
            display: inline-flex;
            align-items: center;
            background: linear-gradient(135deg, #005138 0%, #007d50 55%, #007d50 100%);
            color: #fff!important;
            padding: 8px 16px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 600;
            gap: 4px
        }

        .cart-badge {
            background: #fff;
            color: #005138;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 11px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-left: 2px
        }

        .whatsapp-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: #25d366;
            color: #fff;
            padding: 8px 14px;
            border-radius: 25px;
            font-size: 13px;
            font-weight: 600
        }

        .mobile-menu-btn {
            background: none;
            border: none;
            color: #005138;
            font-size: 20px;
            cursor: pointer;
            padding: 4px 6px
        }

        .category-nav {
            background: linear-gradient(135deg, #005138 0%, #005138 40%, #007d50 75%, #007d50 100%);
            border-top: 1px solid rgba(255, 255, 255, .12)
        }

        .category-nav-inner {
            display: flex;
            align-items: center;
            overflow-x: auto;
            gap: 0;
            scrollbar-width: none;
            padding: 0
        }

        .category-nav-inner::-webkit-scrollbar {
            display: none
        }

        .category-nav-link {
            color: #fff;
            padding: 12px 13px;
            font-size: 13.5px;
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            min-width: 0;
            border-bottom: 3px solid transparent;
            transition: all .25s ease
        }

        .category-nav-link:hover, .category-nav-link.active {
            color: #fff;
            background: rgba(255, 255, 255, .15);
            border-bottom-color: #ffd032
        }

        /* Browsers give :visited links extra specificity over the base
           `a:visited { color: inherit }` rule, which otherwise makes
           already-visited menu links render in the dark body text color
           (invisible against the green nav background). Force white back. */
        .category-nav-link:visited {
            color: #fff
        }

        .category-nav-item {
            position: relative;
            display: flex;
            align-items: stretch
        }

        .category-nav-caret {
            font-size: 10px;
            margin-left: 4px;
            transition: transform .25s ease
        }

        .category-nav-item:hover .category-nav-caret,
        .category-nav-item:focus-within .category-nav-caret {
            transform: rotate(180deg)
        }

        .category-nav-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            min-width: 210px;
            padding: 6px 0;
            background: #fff;
            border-radius: 0 0 var(--radius, 10px) var(--radius, 10px);
            box-shadow: 0 14px 30px rgba(0, 81, 56, .2);
            opacity: 0;
            visibility: hidden;
            transform: translateY(6px);
            transition: opacity .2s ease, transform .2s ease, visibility .2s;
            z-index: 1200
        }

        /* Opens on hover (and on keyboard focus), never on click. */
        .category-nav-item:hover .category-nav-dropdown,
        .category-nav-item:focus-within .category-nav-dropdown {
            opacity: 1;
            visibility: visible;
            transform: translateY(0)
        }

        .category-nav-dropdown-link {
            display: block;
            padding: 9px 18px;
            color: #10211B;
            font-size: 13.5px;
            font-weight: 500;
            white-space: nowrap
        }

        .category-nav-dropdown-link:hover,
        .category-nav-dropdown-link.active {
            background: var(--bg-light, #F3FAF6);
            color: var(--primary-dark, #005138)
        }

        .category-nav-dropdown-link:visited {
            color: #10211B
        }

        /* Desktop strip: keep every category on one line so the header height
           never changes (no wrap-triggered layout shift). Links shrink and
           ellipsis instead of wrapping, and dropdowns stay unclipped. */
        @media (min-width: 768px) {
            .category-nav-inner {
                overflow: visible;
                flex-wrap: nowrap
            }

            .category-nav-item {
                flex: 0 1 auto;
                min-width: 0
            }
        }

        /* Touch layouts cannot hover: the sub categories are always shown. */
        @media (max-width: 767px) {
            .category-nav-item {
                display: block
            }

            .category-nav-dropdown {
                position: static;
                min-width: 0;
                padding: 0;
                border-radius: 0;
                box-shadow: none;
                background: rgba(255, 255, 255, .07);
                opacity: 1;
                visibility: visible;
                transform: none
            }

            .category-nav-dropdown-link {
                color: #fff;
                padding: 10px 20px 10px 36px
            }

            .category-nav-dropdown-link:hover,
            .category-nav-dropdown-link.active {
                background: rgba(255, 255, 255, .14);
                color: #fff
            }

            .category-nav-dropdown-link:visited {
                color: #fff
            }

            .category-nav-item:last-child .category-nav-link {
                border-bottom: none
            }
        }

        .hero-swiper {
            width: 100%;
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, #005138 0%, #005138 40%, #007d50 75%, #007d50 100%)
        }

        .hero-swiper .swiper-wrapper {
            position: relative
        }

        .hero-swiper .swiper-slide {
            position: absolute;
            inset: 0;
            height: auto;
            opacity: 0;
            pointer-events: none;
            transition: opacity .6s ease;
            z-index: 0
        }

        .hero-swiper .swiper-slide:first-child {
            position: relative
        }

        .hero-swiper .swiper-slide.is-active {
            opacity: 1;
            pointer-events: auto;
            z-index: 1
        }

        .hero-swiper .swiper-slide img {
            width: 100%;
            height: auto;
            display: block
        }

        .hero-swiper .hero-slide-media {
            width: 100%;
            aspect-ratio: 770 / 430
        }

        .hero-swiper .hero-slide-media img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block
        }

        @media (min-width: 768px) {
            .hero-swiper .hero-slide-media {
                aspect-ratio: 1920 / 520
            }
        }

        /* â”€â”€ Mobile navigation drawer â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
        .mobile-nav-offcanvas {
            max-width: 320px
        }

        .mobile-nav-header {
            background: var(--primary-gradient);
            color: #fff;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between
        }

        .mobile-nav-header .offcanvas-title {
            font-size: 16px;
            font-weight: 700;
            margin: 0;
            color: #fff
        }

        .mobile-nav-body {
            padding: 0;
            display: flex;
            flex-direction: column
        }

        .mobile-nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 13px 20px;
            font-size: 15px;
            font-weight: 600;
            color: #10211B;
            border-bottom: 1px solid #EEF3F0
        }

        .mobile-nav-link:visited {
            color: #10211B
        }

        .mobile-nav-link:hover, .mobile-nav-link.active {
            background: var(--bg-light, #F3FAF6);
            color: var(--primary-dark, #005138)
        }

        .mobile-nav-link i {
            width: 18px;
            color: var(--primary, #007d50)
        }

        .mobile-nav-group-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            padding: 13px 20px;
            font-size: 15px;
            font-weight: 600;
            color: #10211B;
            background: none;
            border: none;
            border-bottom: 1px solid #EEF3F0;
            text-align: left;
            cursor: pointer
        }

        .mobile-nav-group-title i.mobile-nav-icon {
            width: 18px;
            color: var(--primary, #007d50)
        }

        .mobile-nav-group-label {
            display: flex;
            align-items: center;
            gap: 10px
        }

        .mobile-nav-sub {
            background: #F7FBF9
        }

        .mobile-nav-sub a {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 11px 20px 11px 38px;
            font-size: 14px;
            color: #33443E;
            border-bottom: 1px solid #EDF4F0
        }

        .mobile-nav-sub a:visited {
            color: #33443E
        }

        .mobile-nav-sub a:hover, .mobile-nav-sub a.active {
            color: var(--primary-dark, #005138);
            background: #EDF7F1
        }

        .mobile-nav-caret {
            font-size: 11px;
            color: #7A8B85;
            transition: transform .25s ease
        }

        .mobile-nav-group-title.open .mobile-nav-caret {
            transform: rotate(180deg)
        }

        /* â”€â”€ Filter accordion + variation filters â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
        .filter-accordion {
            margin-bottom: 6px
        }

        .filter-category {
            border-bottom: 1px solid #F0F4F2
        }

        .filter-category-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 6px
        }

        .filter-category-link {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            padding: 9px 0;
            font-size: 14px;
            color: #10211B;
            text-decoration: none
        }

        .filter-category-link:visited {
            color: #10211B
        }

        .filter-category-link small, .filter-subcategory-link small {
            color: #94A3A0;
            font-size: 12px
        }

        .filter-category-link.active {
            color: var(--primary-dark, #005138);
            font-weight: 700
        }

        .filter-category-toggle {
            background: none;
            border: none;
            padding: 6px 4px;
            color: #7A8B85;
            cursor: pointer
        }

        .filter-category-toggle i {
            transition: transform .25s ease
        }

        .filter-category-toggle.open i {
            transform: rotate(180deg)
        }

        .filter-subcategory-list {
            padding: 2px 0 8px
        }

        .filter-subcategory-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            padding: 7px 0 7px 14px;
            font-size: 13.5px;
            color: #4A5A55;
            text-decoration: none;
            border-left: 2px solid #E4EEE9
        }

        .filter-subcategory-link:hover, .filter-subcategory-link.active {
            color: var(--primary-dark, #005138);
            border-left-color: var(--primary, #007d50)
        }

        .filter-subcategory-link.active {
            font-weight: 700
        }

        .filter-variation-group {
            border-bottom: 1px solid #F0F4F2
        }

        .filter-variation-toggle {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            background: none;
            border: none;
            padding: 10px 0;
            font-size: 14px;
            font-weight: 600;
            color: #10211B;
            cursor: pointer
        }

        .filter-variation-toggle i {
            font-size: 11px;
            color: #7A8B85;
            transition: transform .25s ease
        }

        .filter-variation-toggle.open i {
            transform: rotate(180deg)
        }

        .filter-variation-values {
            display: flex;
            flex-direction: column;
            gap: 3px;
            padding: 0 0 8px
        }

        .filter-check {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0;
            padding: 4px 0;
            font-size: 13.5px;
            color: #37474F;
            cursor: pointer
        }

        .filter-check input {
            width: 15px;
            height: 15px;
            margin: 0;
            accent-color: var(--primary-dark, #005138)
        }

        /* â”€â”€ Category / brand page header â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
        .shop-page-header {
            background: #fff;
            padding-bottom: 18px
        }

        .shop-page-banner {
            width: 100%;
            overflow: hidden;
            margin-bottom: 18px
        }

        .shop-page-banner img {
            width: 100%;
            height: auto;
            display: block
        }

        .shop-page-header-inner {
            display: flex;
            align-items: center;
            gap: 16px
        }

        .shop-page-logo {
            width: 74px;
            height: 74px;
            padding: 6px;
            object-fit: contain;
            background: #fff;
            border: 1px solid #E7EFEA;
            border-radius: 10px
        }

        .shop-page-title {
            margin: 0;
            font-size: clamp(20px, 2.6vw, 26px);
            font-weight: 800;
            color: var(--primary-dark, #005138)
        }

        .shop-page-desc {
            margin: 4px 0 0;
            font-size: 14px;
            color: #6B7C76
        }

        /* â”€â”€ Homepage category block banner â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
        .home-category-banner {
            display: block;
            margin-bottom: 20px;
            border-radius: var(--radius, 10px);
            overflow: hidden
        }

        .home-category-banner img {
            width: 100%;
            height: auto;
            display: block
        }

        /* â”€â”€ Product detail brand chip â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
        .product-brand-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 10px;
            padding: 5px 12px 5px 5px;
            background: #fff;
            border: 1px solid #E7EFEA;
            border-radius: 30px;
            text-decoration: none
        }

        .product-brand-chip img {
            width: 28px;
            height: 28px;
            object-fit: contain
        }

        .product-brand-chip span {
            font-size: 13px;
            font-weight: 600;
            color: var(--primary-dark, #005138)
        }

        /* Bootstrap's own grid now loads before first paint, so no duplicated
           column rules here - they would override .row-cols-* and stretch the last row. */

        /* Section headings: pin the line box to Poppins' natural metrics so the
           grid below cannot slide when the web font is applied. */
        .section-title {
            line-height: 1.5
        }

        /* Reserve the 2-line title box in every product card so a name that wraps
           to one line cannot resize the grid row. */
        .product-card-name {
            min-height: 2.8em
        }

        /* Unlock-hidden-treasures strip (above the fold on mobile) */
        .treasure-section {
            padding: 18px 0 28px
        }

        .treasure-title {
            text-align: center;
            font-size: 16px;
            font-weight: 700;
            color: #10211B;
            margin: 0 0 14px
        }

        .treasure-strip {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 14px
        }

        .treasure-item {
            width: 128px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 9px;
            padding: 14px 8px 12px;
            background: #fff;
            border: 1px solid #D8EDE3;
            border-radius: 14px;
            box-shadow: 0 2px 14px rgba(0, 81, 56, .08);
            text-decoration: none
        }

        .treasure-icon {
            width: 72px;
            height: 72px;
            padding: 11px;
            border-radius: 50%;
            background: #F3FAF6;
            display: flex;
            align-items: center;
            justify-content: center
        }

        .treasure-icon img {
            width: 100%;
            height: 100%;
            object-fit: contain
        }

        .treasure-label {
            text-align: center;
            font-size: 12.5px;
            font-weight: 600;
            color: #10211B;
            line-height: 1.3
        }

        @media (max-width: 767.98px) {
            .treasure-section {
                padding: 14px 0 24px
            }

            .treasure-strip {
                gap: 6px;
                flex-wrap: nowrap;
                justify-content: space-between
            }

            .treasure-item {
                flex: 1 1 0;
                width: auto;
                min-width: 0;
                padding: 8px 4px;
                border-radius: 12px
            }

            .treasure-icon {
                width: clamp(40px, 13vw, 56px);
                height: clamp(40px, 13vw, 56px);
                padding: 6px
            }

            .treasure-label {
                display: none
            }
        }
    </style>

    @yield('extra_css')

    {{-- â”€â”€ Schema.org JSON-LD (sitewide: Organization + WebSite) â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
    @php
        use Spatie\SchemaOrg\Schema;
        echo Schema::organization()
            ->name('OpenSesame BD')
            ->url(url('/'))
            ->logo(Schema::imageObject()->url(asset('images/logo-web.png'))->width(175)->height(78))
            ->telephone('+8801826339988')
            ->contactPoint(
                Schema::contactPoint()
                    ->telephone('+8801826339988')
                    ->contactType('customer service')
                    ->availableLanguage(['Bengali', 'English'])
                    ->areaServed('BD')
            )
            ->toScript();
        echo Schema::webSite()
            ->name('OpenSesame BD')
            ->url(url('/'))
            ->potentialAction(
                Schema::searchAction()
                    ->target(url('/search?q={search_term_string}'))
                    ->setProperty('query-input', 'required name=search_term_string')
            )
            ->toScript();
    @endphp
    @yield('schema')

@include('frontend.partials.analytics')
</head>
@php
    $socialFacebook  = \App\Models\Setting::effective('social.facebook_url');
    $socialInstagram = \App\Models\Setting::effective('social.instagram_url');
    $socialYoutube   = \App\Models\Setting::effective('social.youtube_url');
    $socialTiktok    = \App\Models\Setting::effective('social.tiktok_url');
    $socialMessenger = \App\Models\Setting::effective('social.messenger_url');
    $socialWhatsapp  = \App\Models\Setting::effective('social.whatsapp_url');
@endphp
<body>

<!-- Top Notice Bar -->
<div class="top-notice-bar d-none d-lg-block">
    <div class="container">
        <div class="top-bar-left">
            <a href="tel:+8801826339988"><i class="fas fa-phone-alt" aria-hidden="true"></i> 01826339988</a>
            <span class="top-bar-sep">|</span>
            <a href="tel:+8801826339933"><i class="fas fa-phone-alt" aria-hidden="true"></i> 01826339933</a>
            <span class="top-bar-sep">|</span>
            <span><i class="fas fa-truck" aria-hidden="true"></i> Delivery Across Bangladesh</span>
        </div>
        <div class="top-bar-right">
            <div class="top-bar-social">
                @if($socialFacebook)
                    <a href="{{ $socialFacebook }}" target="_blank" rel="noopener" aria-label="Follow us on Facebook"><i class="fab fa-facebook-f" aria-hidden="true"></i></a>
                @endif
                @if($socialWhatsapp)
                    <a href="{{ $socialWhatsapp }}" target="_blank" rel="noopener" aria-label="Chat on WhatsApp"><i class="fab fa-whatsapp" aria-hidden="true"></i></a>
                @endif
                @if($socialMessenger)
                    <a href="{{ $socialMessenger }}" target="_blank" rel="noopener" aria-label="Message us on Messenger"><i class="fab fa-facebook-messenger" aria-hidden="true"></i></a>
                @endif
            </div>
            <span class="top-bar-sep">|</span>
            <a href="{{ route('shop.track-order') }}"><i class="fas fa-map-marker-alt" aria-hidden="true"></i> Track Order</a>
            <span class="top-bar-sep">|</span>
            @auth
                @php
                    $topbarUser = auth()->user();
                    $topbarAccountUrl = $topbarUser->hasAnyRole(['Super Admin', 'Admin'])
                        ? route('dashboard.index')
                        : route('customer.dashboard');
                @endphp
                <a href="{{ $topbarAccountUrl }}">
                    @if($topbarUser->photo)
                        <img src="{{ asset('storage' . \App\Models\User::IMAGE_PATH . $topbarUser->photo) }}"
                             loading="lazy" decoding="async"
                             style="width:16px;height:16px;border-radius:50%;object-fit:cover;vertical-align:-2px;margin-right:4px;"
                             alt="">
                    @else
                        <i class="fas fa-user-circle" aria-hidden="true"></i>
                    @endif
                    {{ Str::words($topbarUser->name, 1, '') }}
                </a>
                <span class="top-bar-sep">|</span>
                <form action="{{ route('logout') }}" method="POST" class="d-inline m-0">
                    @csrf
                    <button type="submit" class="top-bar-logout-btn">
                        <i class="fas fa-sign-out-alt" aria-hidden="true"></i> Logout
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}"><i class="fas fa-user" aria-hidden="true"></i> Login</a>
                <span class="top-bar-sep">/</span>
                <a href="{{ route('register') }}">Register</a>
            @endauth
        </div>
    </div>
</div>

<!-- Main Header -->
<header class="main-header sticky-top">
    <div class="container">
        <div class="row align-items-center py-1 py-lg-3">
            <!-- Logo -->
            <div class="col-4 col-md-2 col-lg-2">
                <a href="{{ route('shop.home') }}" class="logo-link">
                    <img src="{{ asset('images/logo-web.png') }}" alt="OpenSesame BD" class="header-logo" width="175" height="78" loading="eager" decoding="async">
                </a>
            </div>

            <!-- Search Bar (Desktop) -->
            <div class="col-md-8 col-lg-7 d-none d-md-block">
                <form action="{{ route('shop.search') }}" method="GET" class="search-form">
                    <div class="input-group">
                        <input type="text" class="form-control search-input" name="q" value="{{ request('q') }}"
                               placeholder="Search for products...">
                        <button class="btn btn-search" type="submit" aria-label="Search products">
                            <i class="fas fa-search" aria-hidden="true"></i> Search
                        </button>
                    </div>
                </form>
            </div>

            <!-- Header Right -->
            <div class="col-8 col-md-2 col-lg-3 text-end">
                <div class="header-actions">
                    <a href="{{ route('shop.cart') }}" class="cart-btn">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-badge" id="cart-count">{{ count(session('cart', [])) }}</span>
                        <span class="d-none d-lg-inline ms-1">Cart</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Mobile Search + Menu Toggle -->
        <div class="row d-md-none pb-2 align-items-center g-2">
            <div class="col-auto">
                <button class="mobile-menu-btn" type="button" data-bs-toggle="offcanvas"
                        data-bs-target="#mobileNavOffcanvas" aria-controls="mobileNavOffcanvas"
                        aria-label="Open menu">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            <div class="col">
                <form action="{{ route('shop.search') }}" method="GET" class="search-form">
                    <div class="input-group">
                        <input type="text" class="form-control search-input" name="q" value="{{ request('q') }}"
                               placeholder="Search products...">
                        <button class="btn btn-search" type="submit" aria-label="Search products">
                            <i class="fas fa-search" aria-hidden="true"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Category Navigation -->
    <nav class="category-nav">
        <div class="container">
            <div class="category-nav-inner">
                <a href="{{ route('shop.home') }}" class="category-nav-link {{ request()->routeIs('shop.home') ? 'active' : '' }}">
                    <i class="fas fa-home"></i> Home
                </a>
                @foreach($navCategories as $cat)
                    @php
                        $currentSlug = request()->route('slug');
                        $hasChildren = $cat->children->isNotEmpty();
                        $isActive    = $currentSlug == $cat->slug
                                    || ($hasChildren && $cat->children->contains('slug', $currentSlug));
                    @endphp
                    @if($hasChildren)
                        <div class="category-nav-item has-dropdown">
                            <a href="{{ route('shop.category', $cat->slug) }}"
                               class="category-nav-link {{ $isActive ? 'active' : '' }}">
                                {{ $cat->name }}<i class="fas fa-chevron-down category-nav-caret"></i>
                            </a>
                            <div class="category-nav-dropdown">
                                @foreach($cat->children as $child)
                                    <a href="{{ route('shop.category', $child->slug) }}"
                                       class="category-nav-dropdown-link {{ $currentSlug == $child->slug ? 'active' : '' }}">
                                        {{ $child->name }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <a href="{{ route('shop.category', $cat->slug) }}"
                           class="category-nav-link {{ $isActive ? 'active' : '' }}">
                            {{ $cat->name }}
                        </a>
                    @endif
                @endforeach
                <a href="{{ route('shop.products') }}" class="category-nav-link">
                    <i class="fas fa-th"></i> All Products
                </a>
            </div>
        </div>
    </nav>
</header>

{{-- ===== Mobile navigation drawer ===== --}}
<div class="offcanvas offcanvas-start mobile-nav-offcanvas" tabindex="-1" id="mobileNavOffcanvas" aria-labelledby="mobileNavOffcanvasLabel">
    <div class="mobile-nav-header">
        <h5 class="offcanvas-title" id="mobileNavOffcanvasLabel">
            <i class="fas fa-bars me-2"></i>Menu
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body mobile-nav-body">
        <a href="{{ route('shop.home') }}" class="mobile-nav-link {{ request()->routeIs('shop.home') ? 'active' : '' }}">
            <i class="fas fa-home"></i> Home
        </a>

        @if($navCategories->isNotEmpty())
            <button class="mobile-nav-group-title" type="button">
                <span class="mobile-nav-group-label"><i class="fas fa-th-large mobile-nav-icon"></i> Categories</span>
                <i class="fas fa-chevron-down mobile-nav-caret" aria-hidden="true"></i>
            </button>
            <div class="mobile-nav-sub" style="display:none;">
                @foreach($navCategories as $navCat)
                    <a href="{{ route('shop.category', $navCat->slug) }}"
                       class="{{ request()->route('slug') === $navCat->slug ? 'active' : '' }}">
                        <span>{{ $navCat->name }}</span>
                    </a>
                    @foreach($navCat->children as $navChild)
                        <a href="{{ route('shop.category', $navChild->slug) }}" style="padding-left:54px;"
                           class="{{ request()->route('slug') === $navChild->slug ? 'active' : '' }}">
                            <span>{{ $navChild->name }}</span>
                        </a>
                    @endforeach
                @endforeach
            </div>
        @endif

        @if(isset($navBrands) && $navBrands->isNotEmpty())
            <button class="mobile-nav-group-title" type="button">
                <span class="mobile-nav-group-label"><i class="fas fa-copyright mobile-nav-icon"></i> Shop by Brand</span>
                <i class="fas fa-chevron-down mobile-nav-caret" aria-hidden="true"></i>
            </button>
            <div class="mobile-nav-sub" style="display:none;">
                @foreach($navBrands as $navBrand)
                    <a href="{{ route('shop.brand', $navBrand->slug) }}"
                       class="{{ request()->routeIs('shop.brand') && request()->route('slug') === $navBrand->slug ? 'active' : '' }}">
                        <span>{{ $navBrand->name }}</span>
                    </a>
                @endforeach
            </div>
        @endif

        <a href="{{ route('shop.products') }}" class="mobile-nav-link {{ request()->routeIs('shop.products') ? 'active' : '' }}">
            <i class="fas fa-th"></i> All Products
        </a>
        <a href="{{ route('shop.offer') }}" class="mobile-nav-link {{ request()->routeIs('shop.offer') ? 'active' : '' }}">
            <i class="fas fa-tags"></i> Offers
        </a>
        <a href="{{ route('shop.track-order') }}" class="mobile-nav-link {{ request()->routeIs('shop.track-order') ? 'active' : '' }}">
            <i class="fas fa-truck"></i> Track Order
        </a>
        <a href="{{ route('shop.blogs') }}" class="mobile-nav-link {{ request()->routeIs('shop.blogs') ? 'active' : '' }}">
            <i class="fas fa-blog"></i> Blog
        </a>
        <a href="{{ route('shop.contact') }}" class="mobile-nav-link {{ request()->routeIs('shop.contact') ? 'active' : '' }}">
            <i class="fas fa-envelope"></i> Contact Us
        </a>

        @auth
            @php
                $mobileUser = auth()->user();
                $mobileAccountUrl = $mobileUser->hasAnyRole(['Super Admin', 'Admin'])
                    ? route('dashboard.index')
                    : route('customer.dashboard');
            @endphp
            <a href="{{ $mobileAccountUrl }}" class="mobile-nav-link">
                <i class="fas fa-user-circle"></i>
                {{ $mobileUser->hasAnyRole(['Super Admin', 'Admin']) ? 'Admin Panel' : 'My Account' }}
            </a>
            <form action="{{ route('logout') }}" method="POST" class="m-0">
                @csrf
                <button type="submit" class="mobile-nav-link border-0 bg-transparent w-100 text-start">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </form>
        @else
            <a href="{{ route('login') }}" class="mobile-nav-link"><i class="fas fa-user"></i> Login</a>
            <a href="{{ route('register') }}" class="mobile-nav-link"><i class="fas fa-user-plus"></i> Register</a>
        @endauth
    </div>
</div>

<!-- Flash Messages -->
@if(session('success'))
    <div class="alert-floating alert-success-floating" id="flash-success">
        <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="alert-floating alert-error-floating" id="flash-error">
        <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
    </div>
@endif

<!-- Page Content -->
<main>
    @yield('content')
</main>

<!-- Footer -->
<footer class="main-footer">
    <div class="container">
        <div class="row g-4 footer-top">
            <!-- Brand & Contact -->
            <div class="col-lg-4 col-md-12">
                <img src="{{ asset('images/logo-web-white.png') }}" alt="OpenSesame BD" class="footer-logo mb-3" width="175" height="71"
                     loading="lazy" onerror="this.style.display='none'">
                <p class="footer-desc">Shop your favourite products at the best prices. Quality products & fast delivery is our promise.</p>
                <ul class="footer-contact-block">
                    <li><i class="fas fa-map-marker-alt" aria-hidden="true"></i> <span>Dhaka, Bangladesh</span></li>
                    <li><i class="fas fa-headset" aria-hidden="true"></i> <a href="tel:+8801826339988">01826339988</a> <span style="opacity:.5;margin:0 4px;">/</span> <a href="tel:+8801826339933">01826339933</a></li>
                    <li><i class="fas fa-envelope" aria-hidden="true"></i> <a href="mailto:opensesamebd@gmail.com">opensesamebd@gmail.com</a></li>
                    <li><i class="fas fa-clock" aria-hidden="true"></i> <span>Open: 24/7, 365 Days</span></li>
                </ul>
                <div class="footer-social">
                    @if($socialFacebook)
                        <a href="{{ $socialFacebook }}" target="_blank" rel="noopener" aria-label="Follow us on Facebook"><i
                                class="fab fa-facebook-f" aria-hidden="true"></i></a>
                    @endif
                    @if($socialWhatsapp)
                        <a href="{{ $socialWhatsapp }}" target="_blank" rel="noopener" aria-label="Chat on WhatsApp"><i
                                class="fab fa-whatsapp" aria-hidden="true"></i></a>
                    @endif
                    @if($socialMessenger)
                        <a href="{{ $socialMessenger }}" target="_blank" rel="noopener" aria-label="Message us on Messenger"><i
                                class="fab fa-facebook-messenger" aria-hidden="true"></i></a>
                    @endif
                    @if($socialInstagram)
                        <a href="{{ $socialInstagram }}" target="_blank" rel="noopener" aria-label="Follow us on Instagram"><i
                                class="fab fa-instagram" aria-hidden="true"></i></a>
                    @endif
                    @if($socialYoutube)
                        <a href="{{ $socialYoutube }}" target="_blank" rel="noopener" aria-label="Subscribe on YouTube"><i
                                class="fab fa-youtube" aria-hidden="true"></i></a>
                    @endif
                    @if($socialTiktok)
                        <a href="{{ $socialTiktok }}" target="_blank" rel="noopener" aria-label="Follow us on TikTok"><i
                                class="fab fa-tiktok" aria-hidden="true"></i></a>
                    @endif
                </div>
            </div>

            <!-- Company -->
            <div class="col-lg-2 col-md-4">
                <h4 class="footer-heading">Company</h4>
                <ul class="footer-links">
                    <li><a href="{{ route('shop.home') }}">Home</a></li>
                    <li><a href="{{ route('shop.products') }}">All Products</a></li>
                    <li><a href="{{ route('shop.blogs') }}">Blog</a></li>
                    <li><a href="{{ route('shop.contact') }}">Contact Us</a></li>
                </ul>
            </div>

            <!-- Support -->
            <div class="col-lg-3 col-md-4">
                <h4 class="footer-heading">Support</h4>
                <ul class="footer-links">
                    <li><a href="{{ route('shop.track-order') }}">Track Order</a></li>
                    <li><a href="{{ route('shop.cart') }}">Shopping Cart</a></li>
                    <li><a href="{{ route('shop.checkout') }}">Checkout</a></li>
                    @forelse($footerPages as $fp)
                        <li><a href="{{ route('shop.page', $fp->slug) }}">{{ $fp->title }}</a></li>
                    @empty
                    @endforelse
                    @auth
                        @php
                            $footerUser = auth()->user();
                            $footerAccountUrl = $footerUser->hasAnyRole(['Super Admin', 'Admin'])
                                ? route('dashboard.index')
                                : route('customer.dashboard');
                        @endphp
                        <li><a href="{{ $footerAccountUrl }}">{{ $footerUser->hasAnyRole(['Super Admin', 'Admin']) ? 'Admin Panel' : 'My Account' }}</a></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST" class="m-0">
                                @csrf
                                <button type="submit" class="footer-link-button">Logout</button>
                            </form>
                        </li>
                    @else
                        <li><a href="{{ route('login') }}">Login / Register</a></li>
                    @endauth
                </ul>
            </div>

            <!-- Categories -->
            <div class="col-lg-3 col-md-4">
                <h4 class="footer-heading">Categories</h4>
                <ul class="footer-links">
                    @foreach($navCategories->take(6) as $cat)
                        <li><a href="{{ route('shop.category', $cat->slug) }}">{{ $cat->name }}</a></li>
                    @endforeach
                </ul>
            </div>
        </div>

        <!-- Trust Row -->
        <div class="footer-trust-row">
            <div class="footer-trust-item"><i class="fas fa-truck" aria-hidden="true"></i> Nationwide Delivery</div>
            <div class="footer-trust-item"><i class="fas fa-money-bill-wave" aria-hidden="true"></i> Cash on Delivery</div>
            <div class="footer-trust-item"><i class="fas fa-shield-alt" aria-hidden="true"></i> Secure Checkout</div>
            <div class="footer-trust-item"><i class="fas fa-medal" aria-hidden="true"></i> Authentic Products</div>
        </div>
    </div>

    <!-- Payment methods / SSL -->
    <div class="footer-ssl-wrap">
        <div class="container">
            <div class="footer-ssl">
                <picture>
                    <source media="(max-width: 767.98px)" srcset="{{ asset('images/ssl-mobile.jpg') }}">
                    <img src="{{ asset('images/ssl.jpg') }}"
                         alt="Accepted payment methods - verified by SSLCommerz"
                         width="1200" height="60" loading="lazy" decoding="async">
                </picture>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="container">
            <p class="footer-copyright">Â© {{ date('Y') }} <strong>OPEN SESAME.COM LIMITED</strong>. All rights reserved.</p>
        </div>
    </div>
</footer>

<!-- Scroll to Top -->
<button class="scroll-top-btn" id="scrollTop" aria-label="Scroll to top">
    <i class="fas fa-chevron-up" aria-hidden="true"></i>
</button>

<!-- Add-to-cart success popup -->
<style>
    /* Guarantee the popup card actions stay fully visible even if an older
       cached shop.min.css is still being served. */
    #cartAddedSimilar .product-card-footer-btns {
        flex-direction: column !important;
        gap: 4px !important;
    }
    #cartAddedSimilar .btn-add-to-cart,
    #cartAddedSimilar .btn-card-buy-now {
        width: 100% !important;
        min-width: 0 !important;
        white-space: normal !important;
    }
</style>
<div class="modal fade" id="cartAddedModal" tabindex="-1" aria-labelledby="cartAddedModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content cart-added-modal">
            <div class="modal-header border-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="cart-added-head">
                    <div class="cart-added-check"><i class="fas fa-check" aria-hidden="true"></i></div>
                    <h5 id="cartAddedModalLabel">Added to cart!</h5>
                    <p id="cartAddedProductName">Your item has been added to your cart.</p>
                </div>
                <div id="cartAddedSimilarWrap">
                    <h6 class="cart-added-subtitle">You may also like</h6>
                    <div class="row row-cols-2 row-cols-lg-4 g-3" id="cartAddedSimilar"></div>
                </div>
            </div>
            <div class="modal-footer cart-added-actions border-0">
                <button type="button" class="btn-continue-shopping" data-bs-dismiss="modal">
                    Continue Shopping
                </button>
                <a href="{{ route('shop.checkout') }}" class="btn-go-checkout">
                    Go to Checkout <i class="fas fa-arrow-right" aria-hidden="true"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Scripts (self-hosted â€” no CDN round-trip) -->
<script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>

<script>
    // â”€â”€â”€ Cart count badge â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    var cartCountEl = document.getElementById('cart-count');

    // â”€â”€â”€ Flash message auto-hide â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    setTimeout(function () {
        ['flash-success', 'flash-error'].forEach(function (id) {
            var el = document.getElementById(id);
            if (!el) return;
            el.style.transition = 'opacity 0.5s';
            el.style.opacity = '0';
            setTimeout(function () {
                if (el && el.parentNode) el.parentNode.removeChild(el);
            }, 500);
        });
    }, 4000);

    // â”€â”€â”€ Mobile navigation drawer â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    // The drawer itself is a Bootstrap offcanvas; these handlers only expand
    // its collapsible category / brand groups.
    document.addEventListener('click', function (event) {
        var title = event.target.closest('.mobile-nav-group-title');

        if (title) {
            var sub = title.nextElementSibling;

            if (sub && sub.classList.contains('mobile-nav-sub')) {
                var isOpen = sub.style.display !== 'none';
                sub.style.display = isOpen ? 'none' : 'block';
                title.classList.toggle('open', !isOpen);
            }
        }

        // â”€â”€â”€ Shop filter accordions (sidebar + mobile drawer) â”€â”€â”€â”€â”€â”€â”€
        var categoryToggle = event.target.closest('.filter-category-toggle');

        if (categoryToggle) {
            var list = categoryToggle.closest('.filter-category').querySelector('.filter-subcategory-list');

            if (list) {
                var isListOpen = list.style.display !== 'none';
                list.style.display = isListOpen ? 'none' : 'block';
                categoryToggle.classList.toggle('open', !isListOpen);
                categoryToggle.setAttribute('aria-expanded', !isListOpen ? 'true' : 'false');
            }
        }

        var variationToggle = event.target.closest('.filter-variation-toggle');

        if (variationToggle) {
            var values = variationToggle.parentElement.querySelector('.filter-variation-values');

            if (values) {
                var areValuesOpen = values.style.display !== 'none';
                values.style.display = areValuesOpen ? 'none' : 'flex';
                variationToggle.classList.toggle('open', !areValuesOpen);
                variationToggle.setAttribute('aria-expanded', !areValuesOpen ? 'true' : 'false');
            }
        }
    });

    // Ticking a variation checkbox re-runs the filter immediately.
    document.addEventListener('change', function (event) {
        if (event.target.classList.contains('filter-variation-input') && event.target.form) {
            event.target.form.submit();
        }
    });

    // â”€â”€â”€ Scroll to top â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    var scrollTopBtn = document.getElementById('scrollTop');
    if (scrollTopBtn) {
        window.addEventListener('scroll', function () {
            scrollTopBtn.classList.toggle('visible', window.scrollY > 300);
        });
        scrollTopBtn.addEventListener('click', function () {
            window.scrollTo({top: 0, behavior: 'smooth'});
        });
    }

    // â”€â”€â”€ Show toast notification â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    function showToast(msg, type) {
        type = type || 'success';
        var toast = document.createElement('div');
        toast.className = 'alert-floating alert-' + type + '-floating';
        toast.innerHTML = '<i class="fas fa-' + (type === 'success' ? 'check-circle' : 'exclamation-circle') + ' me-2"></i>' + msg;
        document.body.appendChild(toast);
        setTimeout(function () {
            toast.style.transition = 'opacity 0.5s';
            toast.style.opacity = '0';
            setTimeout(function () {
                if (toast && toast.parentNode) toast.parentNode.removeChild(toast);
            }, 500);
        }, 3000);
    }

    // â”€â”€â”€ Add-to-cart success popup â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    function showCartAddedPopup(data) {
        data = data || {};

        var nameEl = document.getElementById('cartAddedProductName');
        if (nameEl) {
            nameEl.textContent = data.product_name
                ? data.product_name + ' has been added to your cart.'
                : 'Your item has been added to your cart.';
        }

        var grid = document.getElementById('cartAddedSimilar');
        var wrap = document.getElementById('cartAddedSimilarWrap');
        if (grid && wrap) {
            if (data.similar_products_html) {
                grid.innerHTML = data.similar_products_html;
                wrap.style.display = '';
            } else {
                grid.innerHTML = '';
                wrap.style.display = 'none';
            }
        }

        var modalEl = document.getElementById('cartAddedModal');
        if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    // â”€â”€â”€ Add to cart via AJAX â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    function addToCart(productId, qty) {
        qty = qty || 1;
        var csrfMeta = document.querySelector('meta[name="csrf-token"]');
        if (!csrfMeta) {
            showToast('Page error, please refresh.', 'error');
            return;
        }
        fetch('{{ route("shop.cart.add") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfMeta.content
            },
            body: JSON.stringify({product_id: productId, quantity: qty})
        })
            .then(function (r) {
                return r.json();
            })
            .then(function (data) {
                if (data.success) {
                    if (cartCountEl) cartCountEl.textContent = data.cart_count;
                    showCartAddedPopup(data);
                    // â”€â”€ Meta Pixel + GA4: AddToCart â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
                    if (typeof ocTrack === 'function') {
                        ocTrack('AddToCart', {
                            content_ids:  [data.atc_content_id || productId],
                            content_name: data.product_name || '',
                            content_type: 'product',
                            num_items:    qty,
                            value:        Math.round((data.product_price || 0) * qty * 100) / 100,
                            currency:     'BDT'
                        }, data.atc_event_id || '', {
                            items: [{
                                item_id: data.atc_product_id || String(productId),
                                item_name: data.product_name || '',
                                item_variant: data.atc_variation_label || '',
                                price: Number(data.product_price || 0),
                                quantity: qty
                            }]
                        });
                    }
                } else {
                    showToast(data.message || 'Could not add item.', 'error');
                }
            })
            .catch(function () {
                showToast('Something went wrong, please try again.', 'error');
            });
    }
</script>

@yield('extra_js')

@stack('scripts')
</body>
</html>
