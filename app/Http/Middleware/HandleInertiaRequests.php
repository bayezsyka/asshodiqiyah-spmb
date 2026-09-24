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
                'unitPendidikan' => $request->user()->unitPendidikan?->only(['id', 'nama']),
            ] : null],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'mainSiteUrl' => env('MAIN_SITE_URL', config('app.url')),
            'seo' => $this->metadataSeo($request),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function metadataSeo(Request $request): array
    {
        $urlAplikasi = rtrim((string) config('app.url'), '/');
        $urlSitusUtama = rtrim((string) env('MAIN_SITE_URL', config('app.url')), '/');
        $beranda = $this->halamanBolehDiindeks($request);

        if (! $beranda) {
            return [
                'title' => 'Portal SPMB Asshodiqiyah',
                'description' => 'Portal resmi penerimaan santri dan siswa baru Pondok Pesantren Asshodiqiyah Kaligawe.',
                'canonical' => $urlAplikasi.'/'.$request->path(),
                'robots' => 'noindex, nofollow, noarchive, nosnippet',
            ];
        }

        return [
            'title' => 'SPMB Asshodiqiyah | Pendaftaran Santri dan Siswa Baru',
            'description' => 'Portal resmi penerimaan santri dan siswa baru Pondok Pesantren Asshodiqiyah Kaligawe untuk SD IT, SMP IT, MTs, MA, dan SMK.',
            'canonical' => $urlAplikasi.'/',
            'robots' => 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1',
            'ogImage' => $urlAplikasi.'/assets/asshodiqiyah/hero-landing.png',
            'jsonLd' => [
                '@context' => 'https://schema.org',
                '@graph' => [
                    [
                        '@type' => 'WebSite',
                        '@id' => $urlAplikasi.'/#website',
                        'url' => $urlAplikasi.'/',
                        'name' => 'Portal SPMB Asshodiqiyah',
                        'inLanguage' => 'id-ID',
                    ],
                    [
                        '@type' => 'EducationalOrganization',
                        '@id' => $urlSitusUtama.'/#organization',
                        'name' => 'Pondok Pesantren Asshodiqiyah Kaligawe',
                        'url' => $urlSitusUtama.'/',
                        'logo' => $urlAplikasi.'/assets/asshodiqiyah/logo.webp',
                        'areaServed' => 'Kaligawe, Kota Semarang, Jawa Tengah, Indonesia',
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
