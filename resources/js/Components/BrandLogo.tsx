import { cn } from '@/lib/utils';

export type JenisLogo = 'sekolah' | 'yayasan';

export const logoConfig = {
  yayasan: {
    src: '/assets/lentera-hati/logo-yayasan-islam-lenterahati.png',
    alt: 'Logo Yayasan Islam Lenterahati',
  },
  sekolah: {
    src: '/assets/lentera-hati/logo-sekolah-lenterahati.png',
    alt: 'Logo Lenterahati Islamic Boarding School',
  },
} satisfies Record<JenisLogo, { src: string; alt: string }>;

export function BrandLogo({
  className,
  jenis = 'sekolah',
}: {
  className?: string;
  jenis?: JenisLogo;
}) {
  const identitas = logoConfig[jenis];

  return (
    <span
      className={cn(
        'inline-flex shrink-0 items-center justify-center border-0 bg-transparent p-0',
        className,
      )}
    >
      <img
        src={identitas.src}
        alt={identitas.alt}
        className="block max-h-full max-w-full object-contain"
      />
    </span>
  );
}

export function DualBrandLogo({
  className,
  logoClassName,
}: {
  className?: string;
  logoClassName?: string;
}) {
  return (
    <div className={cn('flex items-center gap-1.5 shrink-0 border-0 bg-transparent p-0', className)}>
      <BrandLogo jenis="yayasan" className={logoClassName ?? 'h-8 w-8'} />
      <BrandLogo jenis="sekolah" className={logoClassName ?? 'h-8 w-8'} />
    </div>
  );
}
