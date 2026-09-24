<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Symfony\Component\HttpFoundation\Response;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function handle(Request $request, Closure $next): Response
    {
        $response = parent::handle($request, $next);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), geolocation=(), microphone=()');

        if (! $this->halamanBolehDiindeks($request) && ! $request->is('sitemap.xml') && ! $request->is('robots.txt')) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive, nosnippet');
        }

        return $response;
    }

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => ['user' => $request->user() ? [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'username' => $request->user()->username,
                'role' => $request->user()->peran?->value,
            ] : null],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'mainSiteUrl' => env('MAIN_SITE_URL', 'https://lenterahatiibs.com'),
            'spmbInfoUrl' => env('MAIN_SITE_URL', 'https://lenterahatiibs.com') . '/spmb',
            'ppdbInfoUrl' => env('MAIN_SITE_URL', 'https://lenterahatiibs.com') . '/spmb',
            'seo' => $this->metadataSeo($request),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function metadataSeo(Request $request): array
    {
        $urlAplikasi = rtrim((string) config('app.url'), '/');
        $urlSitusUtama = rtrim((string) env('MAIN_SITE_URL', 'https://lenterahatiibs.com'), '/');
        $beranda = $this->halamanBolehDiindeks($request);

        if (! $beranda) {
            return [
                'title' => 'Portal SPMB Lenterahati IBS',
                'description' => 'Portal resmi SPMB Lenterahati Islamic Boarding School.',
                'canonical' => $urlAplikasi.'/'.$request->path(),
                'robots' => 'noindex, nofollow, noarchive, nosnippet',
            ];
        }

        return [
            'title' => 'SPMB Lenterahati IBS | Pendaftaran Santri Baru',
            'description' => 'Portal resmi Seleksi Penerimaan Murid Baru Lenterahati Islamic Boarding School untuk memilih jenjang, mengirim pendaftaran, dan memperoleh informasi dari panitia.',
            'canonical' => $urlAplikasi.'/',
            'robots' => 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1',
            'ogImage' => $urlAplikasi.'/assets/lentera-hati/campus.webp',
            'jsonLd' => [
                '@context' => 'https://schema.org',
                '@graph' => [
                    [
                        '@type' => 'WebSite',
                        '@id' => $urlAplikasi.'/#website',
                        'url' => $urlAplikasi.'/',
                        'name' => 'Portal SPMB Lenterahati IBS',
                        'inLanguage' => 'id-ID',
                    ],
                    [
                        '@type' => 'EducationalOrganization',
                        '@id' => $urlSitusUtama.'/#organization',
                        'name' => 'Lenterahati Islamic Boarding School',
                        'url' => $urlSitusUtama.'/',
                        'logo' => $urlAplikasi.'/assets/lentera-hati/logo-sekolah-lenterahati.png',
                        'areaServed' => 'Lombok Barat, Nusa Tenggara Barat, Indonesia',
                    ],
                ],
            ],
        ];
    }

    private function halamanBolehDiindeks(Request $request): bool
    {
        return $request->path() === '/';
    }
}
